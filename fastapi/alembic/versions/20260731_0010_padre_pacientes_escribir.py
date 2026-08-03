"""Conceder pacientes.escribir al rol padre (app móvil CRUD de niños).

Revision ID: 20260731_0010
Revises: 20260730_0009
Create Date: 2026-07-31
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260731_0010"
down_revision: Union[str, None] = "20260730_0009"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    op.execute(
        sa.text(
            """
            INSERT INTO rol_permiso (rol_id, permiso_id)
            SELECT r.id, p.id
            FROM roles r, permisos p
            WHERE r.nombre = 'padre' AND p.clave = 'pacientes.escribir'
            AND NOT EXISTS (
                SELECT 1 FROM rol_permiso rp
                WHERE rp.rol_id = r.id AND rp.permiso_id = p.id
            )
            """
        )
    )


def downgrade() -> None:
    op.execute(
        sa.text(
            """
            DELETE FROM rol_permiso rp
            USING roles r, permisos p
            WHERE rp.rol_id = r.id AND rp.permiso_id = p.id
              AND r.nombre = 'padre' AND p.clave = 'pacientes.escribir'
            """
        )
    )
