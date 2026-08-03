"""Citas: nino_id opcional y restricciones de comunidad.

Revision ID: 20260728_0006
Revises: 20260728_0005
Create Date: 2026-07-28
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260728_0006"
down_revision: Union[str, None] = "20260728_0005"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    citas_cols = {c["name"] for c in inspector.get_columns("citas")}
    if "nino_id" not in citas_cols:
        op.add_column("citas", sa.Column("nino_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "citas_nino_id_foreign",
            "citas",
            "ninos",
            ["nino_id"],
            ["id"],
            ondelete="SET NULL",
        )
        op.create_index("citas_nino_id_index", "citas", ["nino_id"])

    # Eliminar comentarios/discusiones huérfanos antes de NOT NULL (03_BaseDatos.md §5)
    op.execute(
        """
        DELETE FROM comentarios
        WHERE id_usuario IS NULL
        """
    )
    op.execute(
        """
        DELETE FROM discusiones
        WHERE id_usuario IS NULL
        """
    )

    op.alter_column("comentarios", "id_usuario", existing_type=sa.BigInteger(), nullable=False)
    op.alter_column("discusiones", "id_usuario", existing_type=sa.BigInteger(), nullable=False)


def downgrade() -> None:
    op.alter_column("discusiones", "id_usuario", existing_type=sa.BigInteger(), nullable=True)
    op.alter_column("comentarios", "id_usuario", existing_type=sa.BigInteger(), nullable=True)

    op.drop_index("citas_nino_id_index", table_name="citas")
    op.drop_constraint("citas_nino_id_foreign", "citas", type_="foreignkey")
    op.drop_column("citas", "nino_id")
