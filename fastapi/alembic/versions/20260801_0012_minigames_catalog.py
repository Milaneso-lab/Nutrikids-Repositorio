"""Catálogo de minijuegos en retos_catalogo.

Revision ID: 20260801_0012
Revises: 20260731_0011
Create Date: 2026-08-01
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260801_0012"
down_revision: Union[str, None] = "20260731_0011"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None

MINIGAMES = [
    (
        "Memoria de alimentos",
        "Encuentra las parejas de frutas y verduras",
        "especial",
        '{"kind":"minigame","game_id":"memory_foods","emoji":"🧠"}',
        20,
    ),
    (
        "Toca lo saludable",
        "Elige alimentos saludables lo más rápido que puedas",
        "especial",
        '{"kind":"minigame","game_id":"tap_healthy","emoji":"👆"}',
        20,
    ),
]


def upgrade() -> None:
    bind = op.get_bind()
    for nombre, descripcion, tipo, condicion, puntos in MINIGAMES:
        bind.execute(
            sa.text(
                "INSERT INTO retos_catalogo (nombre, descripcion, tipo, condicion, puntos_recompensa) "
                "SELECT :nombre, :descripcion, CAST(:tipo AS reto_tipo_enum), CAST(:condicion AS jsonb), :puntos "
                "WHERE NOT EXISTS (SELECT 1 FROM retos_catalogo WHERE nombre = :nombre)"
            ),
            {"nombre": nombre, "descripcion": descripcion, "tipo": tipo, "condicion": condicion, "puntos": puntos},
        )


def downgrade() -> None:
    bind = op.get_bind()
    for nombre, *_ in MINIGAMES:
        bind.execute(sa.text("DELETE FROM retos_catalogo WHERE nombre = :nombre"), {"nombre": nombre})
