"""Dominio clínico: evaluaciones, alergias, alertas, notas, menús y reportes.

Revision ID: 20260728_0005
Revises: 20260728_0004
Create Date: 2026-07-28

Conserva columnas legacy `paciente_id` en tablas clínicas para compatibilidad con código existente.
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op
from sqlalchemy.dialects import postgresql

revision: str = "20260728_0005"
down_revision: Union[str, None] = "20260728_0004"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None

alergia_tipo_enum = postgresql.ENUM(
    "alimentaria", "ambiental", "medicamento", "otra", name="alergia_tipo_enum", create_type=False
)
alergia_severidad_enum = postgresql.ENUM(
    "leve", "moderada", "grave", name="alergia_severidad_enum", create_type=False
)
alerta_severidad_enum = postgresql.ENUM(
    "info", "advertencia", "critica", name="alerta_severidad_enum", create_type=False
)
dia_semana_enum = postgresql.ENUM(
    "lun", "mar", "mie", "jue", "vie", "sab", "dom", name="dia_semana_enum", create_type=False
)
tipo_comida_enum = postgresql.ENUM(
    "desayuno", "colacion", "comida", "cena", name="tipo_comida_enum", create_type=False
)


def _column_names(inspector, table: str) -> set[str]:
    return {c["name"] for c in inspector.get_columns(table)}


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    for enum_type in (
        alergia_tipo_enum,
        alergia_severidad_enum,
        alerta_severidad_enum,
        dia_semana_enum,
        tipo_comida_enum,
    ):
        enum_type.create(bind, checkfirst=True)

    # --- evaluaciones ---
    eval_cols = _column_names(inspector, "evaluaciones")
    if "nino_id" not in eval_cols:
        op.add_column("evaluaciones", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "evaluaciones_nino_id_foreign",
            "evaluaciones",
            "ninos",
            ["nino_id"],
            ["id"],
            ondelete="CASCADE",
        )
        op.create_index("evaluaciones_nino_id_index", "evaluaciones", ["nino_id"])
    if "nutriologo_id" not in eval_cols:
        op.add_column("evaluaciones", sa.Column("nutriologo_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "evaluaciones_nutriologo_id_foreign",
            "evaluaciones",
            "usuarios",
            ["nutriologo_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    for col, col_type in (
        ("peso_kg", sa.Numeric(5, 2)),
        ("talla_cm", sa.Numeric(5, 2)),
        ("imc", sa.Numeric(4, 2)),
        ("percentil_oms", sa.Numeric(5, 2)),
    ):
        if col not in eval_cols:
            op.add_column("evaluaciones", sa.Column(col, col_type, nullable=True))
    if "fecha_evaluacion" not in eval_cols:
        op.add_column(
            "evaluaciones",
            sa.Column(
                "fecha_evaluacion",
                sa.Date(),
                server_default=sa.text("CURRENT_DATE"),
                nullable=False,
            ),
        )

    op.execute("UPDATE evaluaciones SET nino_id = paciente_id WHERE nino_id IS NULL AND paciente_id IS NOT NULL")
    op.execute(
        """
        UPDATE evaluaciones
        SET
            peso_kg = NULLIF(REGEXP_REPLACE(peso, '[^0-9.]', '', 'g'), '')::numeric(5,2),
            talla_cm = NULLIF(REGEXP_REPLACE(talla, '[^0-9.]', '', 'g'), '')::numeric(5,2)
        WHERE peso_kg IS NULL AND peso IS NOT NULL
        """
    )
    op.execute(
        """
        UPDATE evaluaciones
        SET imc = ROUND((peso_kg / POWER(talla_cm / 100.0, 2))::numeric, 2)
        WHERE imc IS NULL AND peso_kg IS NOT NULL AND talla_cm IS NOT NULL AND talla_cm > 0
        """
    )
    op.execute(
        """
        UPDATE evaluaciones
        SET fecha_evaluacion = created_at::date
        WHERE fecha_evaluacion IS NULL
        """
    )
    op.execute(
        "CREATE INDEX IF NOT EXISTS evaluaciones_nino_fecha_index ON evaluaciones (nino_id, fecha_evaluacion DESC)"
    )

    # --- alergias ---
    alergia_cols = _column_names(inspector, "alergias")
    if "nino_id" not in alergia_cols:
        op.add_column("alergias", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "alergias_nino_id_foreign", "alergias", "ninos", ["nino_id"], ["id"], ondelete="CASCADE"
        )
        op.create_index("alergias_nino_id_index", "alergias", ["nino_id"])
    if "tipo" not in alergia_cols:
        op.add_column("alergias", sa.Column("tipo", alergia_tipo_enum, nullable=True))
    if "descripcion" not in alergia_cols:
        op.add_column("alergias", sa.Column("descripcion", sa.String(255), nullable=True))
    if "severidad" not in alergia_cols:
        op.add_column("alergias", sa.Column("severidad", alergia_severidad_enum, nullable=True))
    if "registrada_por_id" not in alergia_cols:
        op.add_column("alergias", sa.Column("registrada_por_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "alergias_registrada_por_id_foreign",
            "alergias",
            "usuarios",
            ["registrada_por_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )

    # --- alertas ---
    alerta_cols = _column_names(inspector, "alertas")
    if "nino_id" not in alerta_cols:
        op.add_column("alertas", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "alertas_nino_id_foreign", "alertas", "ninos", ["nino_id"], ["id"], ondelete="CASCADE"
        )
    if "tipo" not in alerta_cols:
        op.add_column("alertas", sa.Column("tipo", sa.String(50), nullable=True))
    if "severidad" not in alerta_cols:
        op.add_column("alertas", sa.Column("severidad", alerta_severidad_enum, nullable=True))
    if "mensaje" not in alerta_cols:
        op.add_column("alertas", sa.Column("mensaje", sa.Text(), nullable=True))
    if "atendida" not in alerta_cols:
        op.add_column(
            "alertas",
            sa.Column("atendida", sa.Boolean(), server_default=sa.text("false"), nullable=False),
        )
    if "atendida_por_id" not in alerta_cols:
        op.add_column("alertas", sa.Column("atendida_por_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "alertas_atendida_por_id_foreign",
            "alertas",
            "usuarios",
            ["atendida_por_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    if "atendida_en" not in alerta_cols:
        op.add_column("alertas", sa.Column("atendida_en", sa.DateTime(timezone=True), nullable=True))
    op.execute(
        "CREATE INDEX IF NOT EXISTS alertas_atendida_severidad_index ON alertas (atendida, severidad)"
    )

    # --- notas_nutriologo ---
    nota_cols = _column_names(inspector, "notas_nutriologo")
    if "nino_id" not in nota_cols:
        op.add_column("notas_nutriologo", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "notas_nutriologo_nino_id_foreign",
            "notas_nutriologo",
            "ninos",
            ["nino_id"],
            ["id"],
            ondelete="CASCADE",
        )
        op.create_index("notas_nutriologo_nino_id_index", "notas_nutriologo", ["nino_id"])
    if "nutriologo_id" not in nota_cols:
        op.add_column("notas_nutriologo", sa.Column("nutriologo_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "notas_nutriologo_nutriologo_id_foreign",
            "notas_nutriologo",
            "usuarios",
            ["nutriologo_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    if "nota" not in nota_cols:
        op.add_column("notas_nutriologo", sa.Column("nota", sa.Text(), nullable=True))
    if "privada" not in nota_cols:
        op.add_column(
            "notas_nutriologo",
            sa.Column("privada", sa.Boolean(), server_default=sa.text("true"), nullable=False),
        )

    # --- menus ---
    menu_cols = _column_names(inspector, "menus")
    if "nino_id" not in menu_cols:
        op.add_column("menus", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "menus_nino_id_foreign", "menus", "ninos", ["nino_id"], ["id"], ondelete="CASCADE"
        )
        op.create_index("menus_nino_id_index", "menus", ["nino_id"])
    if "nutriologo_id" not in menu_cols:
        op.add_column("menus", sa.Column("nutriologo_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "menus_nutriologo_id_foreign",
            "menus",
            "usuarios",
            ["nutriologo_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    if "objetivo" not in menu_cols:
        op.add_column("menus", sa.Column("objetivo", sa.String(150), nullable=True))
    if "fecha_inicio" not in menu_cols:
        op.add_column("menus", sa.Column("fecha_inicio", sa.Date(), nullable=True))
    if "fecha_fin" not in menu_cols:
        op.add_column("menus", sa.Column("fecha_fin", sa.Date(), nullable=True))

    op.execute("UPDATE menus SET nino_id = paciente_id WHERE nino_id IS NULL AND paciente_id IS NOT NULL")

    if "menu_items" not in inspector.get_table_names():
        op.create_table(
            "menu_items",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("menu_id", sa.BigInteger(), nullable=False),
            sa.Column("dia_semana", dia_semana_enum, nullable=False),
            sa.Column("tipo_comida", tipo_comida_enum, nullable=False),
            sa.Column("descripcion", sa.Text(), nullable=False),
            sa.Column("calorias_aprox", sa.Integer(), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["menu_id"], ["menus.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("menu_items_menu_id_index", "menu_items", ["menu_id"])

    # Migrar descripción libre legacy a un ítem de menú genérico
    op.execute(
        """
        INSERT INTO menu_items (menu_id, dia_semana, tipo_comida, descripcion)
        SELECT m.id, 'lun'::dia_semana_enum, 'comida'::tipo_comida_enum, m.descripcion
        FROM menus m
        WHERE m.descripcion IS NOT NULL
          AND TRIM(m.descripcion) <> ''
          AND NOT EXISTS (SELECT 1 FROM menu_items mi WHERE mi.menu_id = m.id)
        """
    )

    # --- menus_semanales ---
    ms_cols = _column_names(inspector, "menus_semanales")
    if "nombre" not in ms_cols:
        op.add_column("menus_semanales", sa.Column("nombre", sa.String(150), nullable=True))
    if "descripcion" not in ms_cols:
        op.add_column("menus_semanales", sa.Column("descripcion", sa.Text(), nullable=True))
    if "creado_por_id" not in ms_cols:
        op.add_column("menus_semanales", sa.Column("creado_por_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "menus_semanales_creado_por_id_foreign",
            "menus_semanales",
            "usuarios",
            ["creado_por_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    if "publico" not in ms_cols:
        op.add_column(
            "menus_semanales",
            sa.Column("publico", sa.Boolean(), server_default=sa.text("false"), nullable=False),
        )

    # --- reportes ---
    rep_cols = _column_names(inspector, "reportes")
    if "nino_id" not in rep_cols:
        op.add_column("reportes", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "reportes_nino_id_foreign", "reportes", "ninos", ["nino_id"], ["id"], ondelete="CASCADE"
        )
        op.create_index("reportes_nino_id_index", "reportes", ["nino_id"])
    if "nutriologo_id" not in rep_cols:
        op.add_column("reportes", sa.Column("nutriologo_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "reportes_nutriologo_id_foreign",
            "reportes",
            "usuarios",
            ["nutriologo_id"],
            ["id_usuario"],
            ondelete="SET NULL",
        )
    if "pdf_generado_en" not in rep_cols:
        op.add_column("reportes", sa.Column("pdf_generado_en", sa.DateTime(timezone=True), nullable=True))

    op.execute("UPDATE reportes SET nino_id = paciente_id WHERE nino_id IS NULL AND paciente_id IS NOT NULL")


def downgrade() -> None:
    op.drop_index("reportes_nino_id_index", table_name="reportes")
    for col in ("pdf_generado_en", "nutriologo_id", "nino_id"):
        op.drop_column("reportes", col)

    for col in ("publico", "creado_por_id", "descripcion", "nombre"):
        op.drop_column("menus_semanales", col)

    op.drop_index("menu_items_menu_id_index", table_name="menu_items")
    op.drop_table("menu_items")

    op.drop_index("menus_nino_id_index", table_name="menus")
    for col in ("fecha_fin", "fecha_inicio", "objetivo", "nutriologo_id", "nino_id"):
        op.drop_column("menus", col)

    op.drop_index("notas_nutriologo_nino_id_index", table_name="notas_nutriologo")
    for col in ("privada", "nota", "nutriologo_id", "nino_id"):
        op.drop_column("notas_nutriologo", col)

    op.execute("DROP INDEX IF EXISTS alertas_atendida_severidad_index")
    for col in ("atendida_en", "atendida_por_id", "atendida", "mensaje", "severidad", "tipo", "nino_id"):
        op.drop_column("alertas", col)

    op.drop_index("alergias_nino_id_index", table_name="alergias")
    for col in ("registrada_por_id", "severidad", "descripcion", "tipo", "nino_id"):
        op.drop_column("alergias", col)

    op.execute("DROP INDEX IF EXISTS evaluaciones_nino_fecha_index")
    op.drop_index("evaluaciones_nino_id_index", table_name="evaluaciones")
    for col in (
        "fecha_evaluacion",
        "percentil_oms",
        "imc",
        "talla_cm",
        "peso_kg",
        "nutriologo_id",
        "nino_id",
    ):
        op.drop_column("evaluaciones", col)

    bind = op.get_bind()
    for enum_type in (
        tipo_comida_enum,
        dia_semana_enum,
        alerta_severidad_enum,
        alergia_severidad_enum,
        alergia_tipo_enum,
    ):
        enum_type.drop(bind, checkfirst=True)
