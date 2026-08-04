"""Activar cuentas padre pendientes tras rebuild de Postgres."""

from alembic import op

revision = "20260804_0014"
down_revision = "20260804_0013"
branch_labels = None
depends_on = None


def upgrade() -> None:
    op.execute(
        """
        UPDATE usuarios
        SET estado = 'activo'
        WHERE rol = 'padre'
          AND (estado IS NULL OR estado = 'pendiente_verificacion')
        """
    )


def downgrade() -> None:
    pass
