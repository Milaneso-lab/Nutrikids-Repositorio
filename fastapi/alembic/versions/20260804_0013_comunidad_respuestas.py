"""Respuestas en comentarios y discusiones.

Revision ID: 20260804_0013
Revises: 20260801_0012
Create Date: 2026-08-04
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260804_0013"
down_revision: Union[str, None] = "20260801_0012"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)
    tables = set(inspector.get_table_names())

    if "comentarios" in tables:
        cols = {c["name"] for c in inspector.get_columns("comentarios")}
        if "id_comentario_padre" not in cols:
            op.add_column(
                "comentarios",
                sa.Column("id_comentario_padre", sa.BigInteger(), nullable=True),
            )
            op.create_foreign_key(
                "comentarios_id_comentario_padre_foreign",
                "comentarios",
                "comentarios",
                ["id_comentario_padre"],
                ["id_comentario"],
                ondelete="CASCADE",
            )
            op.create_index(
                "comentarios_id_comentario_padre_index",
                "comentarios",
                ["id_comentario_padre"],
            )

    if "respuestas_discusion" not in tables:
        op.create_table(
            "respuestas_discusion",
            sa.Column("id_respuesta", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("id_discusion", sa.BigInteger(), nullable=False),
            sa.Column("id_usuario", sa.BigInteger(), nullable=False),
            sa.Column("nombre", sa.String(length=50), nullable=False),
            sa.Column("apellido", sa.String(length=50), nullable=False),
            sa.Column("mensaje", sa.Text(), nullable=False),
            sa.Column("fecha_creacion", sa.DateTime(), server_default=sa.text("now()"), nullable=False),
            sa.ForeignKeyConstraint(["id_discusion"], ["discusiones.id_discusion"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["id_usuario"], ["usuarios.id_usuario"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id_respuesta"),
        )
        op.create_index("respuestas_discusion_id_discusion_index", "respuestas_discusion", ["id_discusion"])


def downgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)
    tables = set(inspector.get_table_names())

    if "respuestas_discusion" in tables:
        op.drop_index("respuestas_discusion_id_discusion_index", table_name="respuestas_discusion")
        op.drop_table("respuestas_discusion")

    if "comentarios" in tables:
        cols = {c["name"] for c in inspector.get_columns("comentarios")}
        if "id_comentario_padre" in cols:
            op.drop_index("comentarios_id_comentario_padre_index", table_name="comentarios")
            op.drop_constraint("comentarios_id_comentario_padre_foreign", "comentarios", type_="foreignkey")
            op.drop_column("comentarios", "id_comentario_padre")
