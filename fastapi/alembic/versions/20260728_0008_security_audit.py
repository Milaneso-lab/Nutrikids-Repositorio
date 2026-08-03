"""Migración: tablas de auditoría y seguridad operativa."""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op
from sqlalchemy.dialects import postgresql

revision: str = "20260728_0008"
down_revision: Union[str, None] = "20260728_0007"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    if "login_attempts" not in inspector.get_table_names():
        op.create_table(
            "login_attempts",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("email", sa.String(150), nullable=False),
            sa.Column("ip_address", sa.String(45), nullable=True),
            sa.Column("exito", sa.Boolean(), server_default=sa.text("false"), nullable=False),
            sa.Column("created_at", sa.DateTime(timezone=True), server_default=sa.text("CURRENT_TIMESTAMP"), nullable=False),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("login_attempts_email_index", "login_attempts", ["email"])

    if "security_audit_logs" not in inspector.get_table_names():
        op.create_table(
            "security_audit_logs",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("usuario_id", sa.BigInteger(), nullable=True),
            sa.Column("accion", sa.String(100), nullable=False),
            sa.Column("recurso", sa.String(150), nullable=True),
            sa.Column("ip_address", sa.String(45), nullable=True),
            sa.Column("detalles", postgresql.JSONB(), nullable=True),
            sa.Column("created_at", sa.DateTime(timezone=True), server_default=sa.text("CURRENT_TIMESTAMP"), nullable=False),
            sa.ForeignKeyConstraint(["usuario_id"], ["usuarios.id_usuario"], ondelete="SET NULL"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("security_audit_logs_accion_index", "security_audit_logs", ["accion"])
        op.create_index("security_audit_logs_usuario_id_index", "security_audit_logs", ["usuario_id"])

    if "password_history" not in inspector.get_table_names():
        op.create_table(
            "password_history",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("usuario_id", sa.BigInteger(), nullable=False),
            sa.Column("contrasena_hash", sa.String(255), nullable=False),
            sa.Column("created_at", sa.DateTime(timezone=True), server_default=sa.text("CURRENT_TIMESTAMP"), nullable=False),
            sa.ForeignKeyConstraint(["usuario_id"], ["usuarios.id_usuario"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("id"),
        )
        op.create_index("password_history_usuario_id_index", "password_history", ["usuario_id"])


def downgrade() -> None:
    op.drop_index("password_history_usuario_id_index", table_name="password_history")
    op.drop_table("password_history")
    op.drop_index("security_audit_logs_usuario_id_index", table_name="security_audit_logs")
    op.drop_index("security_audit_logs_accion_index", table_name="security_audit_logs")
    op.drop_table("security_audit_logs")
    op.drop_index("login_attempts_email_index", table_name="login_attempts")
    op.drop_table("login_attempts")
