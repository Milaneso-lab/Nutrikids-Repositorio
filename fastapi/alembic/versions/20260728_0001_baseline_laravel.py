"""Baseline: esquema inicial creado por migraciones Laravel.

Revision ID: 20260728_0001
Revises:
Create Date: 2026-07-28

Las tablas usuarios, pacientes, evaluaciones, citas, contactos, comentarios,
discusiones, infantes, alertas, alergias, notas_nutriologo, menus_semanales,
sessions, cache y jobs fueron creadas por database/migrations/ de Laravel.
Alembic asume ese estado como punto de partida (ADR-003).
"""

from typing import Sequence, Union

from alembic import op

revision: str = "20260728_0001"
down_revision: Union[str, None] = None
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    # No-op: el esquema base ya existe vía `php artisan migrate`.
    pass


def downgrade() -> None:
    pass
