"""Sesiones de niño: refresh_tokens.nino_id y usuario_id nullable.

Revision ID: 20260731_0011
Revises: 20260731_0010
Create Date: 2026-07-31
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260731_0011"
down_revision: Union[str, None] = "20260731_0010"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    op.alter_column("refresh_tokens", "usuario_id", existing_type=sa.BigInteger(), nullable=True)
    op.add_column("refresh_tokens", sa.Column("nino_id", sa.BigInteger(), nullable=True))
    op.create_foreign_key(
        "refresh_tokens_nino_id_foreign",
        "refresh_tokens",
        "ninos",
        ["nino_id"],
        ["id"],
        ondelete="CASCADE",
    )
    op.create_index("refresh_tokens_nino_id_index", "refresh_tokens", ["nino_id"])


def downgrade() -> None:
    op.drop_index("refresh_tokens_nino_id_index", table_name="refresh_tokens")
    op.drop_constraint("refresh_tokens_nino_id_foreign", "refresh_tokens", type_="foreignkey")
    op.drop_column("refresh_tokens", "nino_id")
    op.alter_column("refresh_tokens", "usuario_id", existing_type=sa.BigInteger(), nullable=False)
