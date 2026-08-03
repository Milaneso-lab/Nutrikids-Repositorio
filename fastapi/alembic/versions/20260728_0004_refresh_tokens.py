"""Refresh tokens para rotación JWT (03_BaseDatos.md §3.3).

Revision ID: 20260728_0004
Revises: 20260728_0003
Create Date: 2026-07-28
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260728_0004"
down_revision: Union[str, None] = "20260728_0003"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    if "refresh_tokens" not in inspector.get_table_names():
        op.create_table(
            "refresh_tokens",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("usuario_id", sa.BigInteger(), nullable=False),
            sa.Column("token_hash", sa.String(255), nullable=False),
            sa.Column("dispositivo", sa.String(255), nullable=True),
            sa.Column("expira_en", sa.DateTime(timezone=True), nullable=False),
            sa.Column("revocado_en", sa.DateTime(timezone=True), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.ForeignKeyConstraint(
                ["usuario_id"],
                ["usuarios.id_usuario"],
                ondelete="CASCADE",
            ),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("refresh_tokens_usuario_id_index", "refresh_tokens", ["usuario_id"])
        op.create_index("refresh_tokens_expira_en_index", "refresh_tokens", ["expira_en"])


def downgrade() -> None:
    op.drop_index("refresh_tokens_expira_en_index", table_name="refresh_tokens")
    op.drop_index("refresh_tokens_usuario_id_index", table_name="refresh_tokens")
    op.drop_table("refresh_tokens")
