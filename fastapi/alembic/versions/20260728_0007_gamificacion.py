"""Gamificación: hábitos, retos, logros, puntos y recompensas (03_BaseDatos.md §6).

Revision ID: 20260728_0007
Revises: 20260728_0006
Create Date: 2026-07-28
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op
from sqlalchemy.dialects import postgresql

revision: str = "20260728_0007"
down_revision: Union[str, None] = "20260728_0006"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None

habito_categoria_enum = postgresql.ENUM(
    "alimentacion", "actividad", "sueno", "higiene", name="habito_categoria_enum", create_type=False
)
habito_frecuencia_enum = postgresql.ENUM(
    "diaria", "semanal", name="habito_frecuencia_enum", create_type=False
)
reto_tipo_enum = postgresql.ENUM(
    "individual", "semanal", "especial", name="reto_tipo_enum", create_type=False
)
recompensa_estado_enum = postgresql.ENUM(
    "pendiente", "entregada", name="recompensa_estado_enum", create_type=False
)

HABITOS_CATALOGO = [
    ("Beber agua", "Bebe al menos 6 vasos de agua al día", "alimentacion", "agua", 10),
    ("Comer verduras", "Incluye verduras en al menos una comida", "alimentacion", "verduras", 15),
    ("Actividad física", "30 minutos de actividad física", "actividad", "correr", 20),
    ("Dormir bien", "Acuéstate a la hora indicada", "sueno", "luna", 10),
    ("Lavarse las manos", "Lávate las manos antes de comer", "higiene", "manos", 5),
]

RETOS_CATALOGO = [
    (
        "Semana del agua",
        "Bebe agua todos los días durante una semana",
        "semanal",
        '{"habito":"beber_agua","dias_consecutivos":7}',
        50,
    ),
    (
        "Campeón de verduras",
        "Come verduras 5 días seguidos",
        "individual",
        '{"habito":"comer_verduras","dias_consecutivos":5}',
        40,
    ),
]

LOGROS_CATALOGO = [
    ("Primera semana", "Completaste tu primera semana de hábitos", "estrella", '{"dias_activos":7}'),
    ("Explorador saludable", "Registraste 10 hábitos completados", "medalla", '{"habitos_completados":10}'),
]

RECOMPENSAS_CATALOGO = [
    ("Elige el menú del sábado", "Puedes elegir una comida especial del sábado", 100, None),
    ("Sticker especial", "Un sticker exclusivo para tu avatar", 30, None),
]


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    for enum_type in (
        habito_categoria_enum,
        habito_frecuencia_enum,
        reto_tipo_enum,
        recompensa_estado_enum,
    ):
        enum_type.create(bind, checkfirst=True)

    if "habitos_catalogo" not in inspector.get_table_names():
        op.create_table(
            "habitos_catalogo",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nombre", sa.String(150), nullable=False),
            sa.Column("descripcion", sa.Text(), nullable=True),
            sa.Column("categoria", habito_categoria_enum, nullable=False),
            sa.Column("icono", sa.String(100), nullable=True),
            sa.Column("puntos_base", sa.Integer(), server_default="0", nullable=False),
            sa.Column("activo", sa.Boolean(), server_default=sa.text("true"), nullable=False),
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
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("habitos_catalogo_categoria_index", "habitos_catalogo", ["categoria"])
        op.create_index("habitos_catalogo_activo_index", "habitos_catalogo", ["activo"])

    if "nino_habitos" not in inspector.get_table_names():
        op.create_table(
            "nino_habitos",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("habito_id", sa.BigInteger(), nullable=False),
            sa.Column("frecuencia", habito_frecuencia_enum, nullable=False),
            sa.Column("asignado_por_id", sa.BigInteger(), nullable=True),
            sa.Column("activo", sa.Boolean(), server_default=sa.text("true"), nullable=False),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["asignado_por_id"], ["usuarios.id_usuario"], ondelete="SET NULL"),
            sa.ForeignKeyConstraint(["habito_id"], ["habitos_catalogo.id"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("nino_habitos_nino_id_index", "nino_habitos", ["nino_id"])
        op.create_index("nino_habitos_habito_id_index", "nino_habitos", ["habito_id"])

    if "habito_registros" not in inspector.get_table_names():
        op.create_table(
            "habito_registros",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nino_habito_id", sa.BigInteger(), nullable=False),
            sa.Column("fecha", sa.Date(), nullable=False),
            sa.Column("completado", sa.Boolean(), server_default=sa.text("false"), nullable=False),
            sa.Column(
                "registrado_en",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["nino_habito_id"], ["nino_habitos.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
            sa.UniqueConstraint("nino_habito_id", "fecha", name="habito_registros_nino_habito_fecha_unique"),
        )
        op.create_index("habito_registros_fecha_index", "habito_registros", ["fecha"])

    if "retos_catalogo" not in inspector.get_table_names():
        op.create_table(
            "retos_catalogo",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nombre", sa.String(150), nullable=False),
            sa.Column("descripcion", sa.Text(), nullable=True),
            sa.Column("tipo", reto_tipo_enum, nullable=False),
            sa.Column("condicion", postgresql.JSONB(), nullable=False),
            sa.Column("puntos_recompensa", sa.Integer(), server_default="0", nullable=False),
            sa.Column("activo", sa.Boolean(), server_default=sa.text("true"), nullable=False),
            sa.Column("fecha_inicio", sa.Date(), nullable=True),
            sa.Column("fecha_fin", sa.Date(), nullable=True),
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
            sa.PrimaryKeyConstraint("id"),
        )

    if "nino_retos" not in inspector.get_table_names():
        op.create_table(
            "nino_retos",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("reto_id", sa.BigInteger(), nullable=False),
            sa.Column("progreso", postgresql.JSONB(), nullable=True),
            sa.Column("completado", sa.Boolean(), server_default=sa.text("false"), nullable=False),
            sa.Column("completado_en", sa.DateTime(timezone=True), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["reto_id"], ["retos_catalogo.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("nino_retos_nino_id_index", "nino_retos", ["nino_id"])

    if "logros_catalogo" not in inspector.get_table_names():
        op.create_table(
            "logros_catalogo",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nombre", sa.String(150), nullable=False),
            sa.Column("descripcion", sa.Text(), nullable=True),
            sa.Column("icono", sa.String(100), nullable=True),
            sa.Column("criterio", postgresql.JSONB(), nullable=True),
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
            sa.PrimaryKeyConstraint("id"),
        )

    if "nino_logros" not in inspector.get_table_names():
        op.create_table(
            "nino_logros",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("logro_id", sa.BigInteger(), nullable=False),
            sa.Column(
                "obtenido_en",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["logro_id"], ["logros_catalogo.id"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
            sa.UniqueConstraint("nino_id", "logro_id", name="nino_logros_nino_logro_unique"),
        )

    if "nino_puntos" not in inspector.get_table_names():
        op.create_table(
            "nino_puntos",
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("puntos_totales", sa.Integer(), server_default="0", nullable=False),
            sa.Column("nivel_actual", sa.Integer(), server_default="1", nullable=False),
            sa.Column(
                "actualizado_en",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("nino_id"),
        )

    if "recompensas_catalogo" not in inspector.get_table_names():
        op.create_table(
            "recompensas_catalogo",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nombre", sa.String(150), nullable=False),
            sa.Column("descripcion", sa.Text(), nullable=True),
            sa.Column("costo_puntos", sa.Integer(), nullable=False),
            sa.Column("stock", sa.Integer(), nullable=True),
            sa.Column("activo", sa.Boolean(), server_default=sa.text("true"), nullable=False),
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
            sa.PrimaryKeyConstraint("id"),
        )

    if "nino_recompensas" not in inspector.get_table_names():
        op.create_table(
            "nino_recompensas",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nino_id", sa.BigInteger(), nullable=False),
            sa.Column("recompensa_id", sa.BigInteger(), nullable=False),
            sa.Column(
                "canjeado_en",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column(
                "estado",
                recompensa_estado_enum,
                server_default="pendiente",
                nullable=False,
            ),
            sa.ForeignKeyConstraint(["nino_id"], ["ninos.id"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["recompensa_id"], ["recompensas_catalogo.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("nino_recompensas_nino_id_index", "nino_recompensas", ["nino_id"])

    # Semilla de catálogos base
    for nombre, descripcion, categoria, icono, puntos in HABITOS_CATALOGO:
        op.execute(
            sa.text(
                "INSERT INTO habitos_catalogo (nombre, descripcion, categoria, icono, puntos_base) "
                "SELECT :nombre, :descripcion, CAST(:categoria AS habito_categoria_enum), :icono, :puntos "
                "WHERE NOT EXISTS (SELECT 1 FROM habitos_catalogo WHERE nombre = :nombre)"
            ).bindparams(
                nombre=nombre,
                descripcion=descripcion,
                categoria=categoria,
                icono=icono,
                puntos=puntos,
            )
        )

    for nombre, descripcion, tipo, condicion, puntos in RETOS_CATALOGO:
        op.execute(
            sa.text(
                "INSERT INTO retos_catalogo (nombre, descripcion, tipo, condicion, puntos_recompensa) "
                "SELECT :nombre, :descripcion, CAST(:tipo AS reto_tipo_enum), CAST(:condicion AS jsonb), :puntos "
                "WHERE NOT EXISTS (SELECT 1 FROM retos_catalogo WHERE nombre = :nombre)"
            ).bindparams(
                nombre=nombre,
                descripcion=descripcion,
                tipo=tipo,
                condicion=condicion,
                puntos=puntos,
            )
        )

    for nombre, descripcion, icono, criterio in LOGROS_CATALOGO:
        op.execute(
            sa.text(
                "INSERT INTO logros_catalogo (nombre, descripcion, icono, criterio) "
                "SELECT :nombre, :descripcion, :icono, CAST(:criterio AS jsonb) "
                "WHERE NOT EXISTS (SELECT 1 FROM logros_catalogo WHERE nombre = :nombre)"
            ).bindparams(
                nombre=nombre,
                descripcion=descripcion,
                icono=icono,
                criterio=criterio,
            )
        )

    for nombre, descripcion, costo, stock in RECOMPENSAS_CATALOGO:
        op.execute(
            sa.text(
                "INSERT INTO recompensas_catalogo (nombre, descripcion, costo_puntos, stock) "
                "SELECT :nombre, :descripcion, :costo, :stock "
                "WHERE NOT EXISTS (SELECT 1 FROM recompensas_catalogo WHERE nombre = :nombre)"
            ).bindparams(
                nombre=nombre,
                descripcion=descripcion,
                costo=costo,
                stock=stock,
            )
        )


def downgrade() -> None:
    op.drop_index("nino_recompensas_nino_id_index", table_name="nino_recompensas")
    op.drop_table("nino_recompensas")
    op.drop_table("recompensas_catalogo")
    op.drop_table("nino_puntos")
    op.drop_table("nino_logros")
    op.drop_table("logros_catalogo")
    op.drop_index("nino_retos_nino_id_index", table_name="nino_retos")
    op.drop_table("nino_retos")
    op.drop_table("retos_catalogo")
    op.drop_index("habito_registros_fecha_index", table_name="habito_registros")
    op.drop_table("habito_registros")
    op.drop_index("nino_habitos_habito_id_index", table_name="nino_habitos")
    op.drop_index("nino_habitos_nino_id_index", table_name="nino_habitos")
    op.drop_table("nino_habitos")
    op.drop_index("habitos_catalogo_activo_index", table_name="habitos_catalogo")
    op.drop_index("habitos_catalogo_categoria_index", table_name="habitos_catalogo")
    op.drop_table("habitos_catalogo")

    bind = op.get_bind()
    for enum_type in (
        recompensa_estado_enum,
        reto_tipo_enum,
        habito_frecuencia_enum,
        habito_categoria_enum,
    ):
        enum_type.drop(bind, checkfirst=True)
