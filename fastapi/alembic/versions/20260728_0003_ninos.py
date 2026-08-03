"""Consolidación pacientes → ninos y credenciales móviles.

Revision ID: 20260728_0003
Revises: 20260728_0002
Create Date: 2026-07-28

Conserva tablas legacy `pacientes` e `infantes` (no DROP) según 16_PlanModernizacion.md §Paso 5.
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op
from sqlalchemy.dialects import postgresql

revision: str = "20260728_0003"
down_revision: Union[str, None] = "20260728_0002"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None

nino_sexo_enum = postgresql.ENUM(
    "masculino",
    "femenino",
    "otro",
    name="nino_sexo_enum",
    create_type=False,
)


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)
    nino_sexo_enum.create(bind, checkfirst=True)

    if "ninos" not in inspector.get_table_names():
        op.create_table(
            "ninos",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("padre_id", sa.BigInteger(), nullable=False),
            sa.Column("nutriologo_asignado_id", sa.BigInteger(), nullable=True),
            sa.Column("nombre", sa.String(100), nullable=False),
            sa.Column("apellidos", sa.String(100), nullable=False),
            sa.Column("fecha_nacimiento", sa.Date(), nullable=False),
            sa.Column("sexo", nino_sexo_enum, nullable=False),
            sa.Column("peso_actual_kg", sa.Numeric(5, 2), nullable=True),
            sa.Column("talla_actual_cm", sa.Numeric(5, 2), nullable=True),
            sa.Column("avatar_config", postgresql.JSONB(), nullable=True),
            sa.Column("codigo_vinculacion", sa.String(12), nullable=True),
            sa.Column(
                "requiere_vinculacion_padre",
                sa.Boolean(),
                server_default=sa.text("false"),
                nullable=False,
            ),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column(
                "updated_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column("deleted_at", sa.DateTime(timezone=True), nullable=True),
            sa.ForeignKeyConstraint(
                ["nutriologo_asignado_id"],
                ["usuarios.id_usuario"],
                ondelete="SET NULL",
            ),
            sa.ForeignKeyConstraint(["padre_id"], ["usuarios.id_usuario"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
            sa.UniqueConstraint("codigo_vinculacion"),
        )
        op.create_index("ninos_padre_id_index", "ninos", ["padre_id"])
        op.create_index("ninos_nutriologo_asignado_id_index", "ninos", ["nutriologo_asignado_id"])
        op.execute(
            """
            CREATE UNIQUE INDEX ninos_codigo_vinculacion_partial_index
            ON ninos (codigo_vinculacion)
            WHERE codigo_vinculacion IS NOT NULL
            """
        )

    if "nino_credenciales" not in inspector.get_table_names():
        op.create_table(
            "nino_credenciales",
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("pin_hash", sa.String(255), nullable=False),
            sa.Column("dispositivo_id", sa.String(255), nullable=True),
            sa.Column("vinculado_en", sa.DateTime(timezone=True), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column(
                "updated_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("nino_id"),
        )

    # Migración de datos pacientes → ninos (preserva IDs para compatibilidad con FKs legacy)
    op.execute(
        """
        INSERT INTO ninos (
            id, padre_id, nutriologo_asignado_id, nombre, apellidos,
            fecha_nacimiento, sexo, requiere_vinculacion_padre, created_at, updated_at
        )
        SELECT
            p.id,
            (SELECT id_usuario FROM usuarios WHERE email = 'sistema-migracion@nutrikids.internal'),
            NULL,
            COALESCE(NULLIF(TRIM(p.nombre), ''), 'Sin nombre'),
            COALESCE(NULLIF(TRIM(p.apellidos), ''), 'Sin apellido'),
            COALESCE(p.fecha_nacimiento::date, CURRENT_DATE),
            'otro'::nino_sexo_enum,
            true,
            COALESCE(p.created_at, CURRENT_TIMESTAMP),
            COALESCE(p.updated_at, CURRENT_TIMESTAMP)
        FROM pacientes p
        WHERE NOT EXISTS (SELECT 1 FROM ninos n WHERE n.id = p.id)
        """
    )

    # Cache antropométrico desde última evaluación
    op.execute(
        """
        UPDATE ninos n
        SET
            peso_actual_kg = sub.peso_kg,
            talla_actual_cm = sub.talla_cm
        FROM (
            SELECT DISTINCT ON (e.paciente_id)
                e.paciente_id,
                NULLIF(REGEXP_REPLACE(e.peso, '[^0-9.]', '', 'g'), '')::numeric(5,2) AS peso_kg,
                NULLIF(REGEXP_REPLACE(e.talla, '[^0-9.]', '', 'g'), '')::numeric(5,2) AS talla_cm
            FROM evaluaciones e
            WHERE e.paciente_id IS NOT NULL
            ORDER BY e.paciente_id, e.created_at DESC
        ) sub
        WHERE n.id = sub.paciente_id
          AND (n.peso_actual_kg IS NULL OR n.talla_actual_cm IS NULL)
        """
    )

    # Tras INSERT con IDs explícitos desde pacientes, sincronizar la secuencia
    op.execute(
        """
        SELECT setval(
            pg_get_serial_sequence('ninos', 'id'),
            COALESCE((SELECT MAX(id) FROM ninos), 1),
            true
        )
        """
    )


def downgrade() -> None:
    op.drop_table("nino_credenciales")
    op.execute("DROP INDEX IF EXISTS ninos_codigo_vinculacion_partial_index")
    op.drop_index("ninos_nutriologo_asignado_id_index", table_name="ninos")
    op.drop_index("ninos_padre_id_index", table_name="ninos")
    op.drop_table("ninos")

    bind = op.get_bind()
    nino_sexo_enum.drop(bind, checkfirst=True)
