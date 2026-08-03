"""Migración: índices de claves foráneas, índices de consulta e integridad por CHECK.

No crea ni elimina tablas: sólo endurece el esquema existente.
Todas las operaciones son idempotentes para poder aplicarse sobre bases ya pobladas.
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op

revision: str = "20260730_0009"
down_revision: Union[str, None] = "20260728_0008"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


# Índices sobre columnas FK que PostgreSQL no crea automáticamente. Sin ellos, cada
# DELETE/UPDATE en la tabla padre fuerza un seq scan en la hija.
FK_INDEXES: list[tuple[str, str, str]] = [
    ("comentarios_id_usuario_index", "comentarios", "id_usuario"),
    ("discusiones_id_usuario_index", "discusiones", "id_usuario"),
    ("evaluaciones_nutriologo_id_index", "evaluaciones", "nutriologo_id"),
    ("evaluaciones_paciente_id_index", "evaluaciones", "paciente_id"),
    ("menus_nutriologo_id_index", "menus", "nutriologo_id"),
    ("menus_paciente_id_index", "menus", "paciente_id"),
    ("reportes_nutriologo_id_index", "reportes", "nutriologo_id"),
    ("reportes_paciente_id_index", "reportes", "paciente_id"),
    ("citas_id_nutriologo_index", "citas", "id_nutriologo"),
    ("citas_id_padre_index", "citas", "id_padre"),
    ("alertas_atendida_por_id_index", "alertas", "atendida_por_id"),
    ("alertas_nino_id_index", "alertas", "nino_id"),
    ("alergias_registrada_por_id_index", "alergias", "registrada_por_id"),
    ("notas_nutriologo_nutriologo_id_index", "notas_nutriologo", "nutriologo_id"),
    ("menus_semanales_creado_por_id_index", "menus_semanales", "creado_por_id"),
    ("nino_habitos_asignado_por_id_index", "nino_habitos", "asignado_por_id"),
    ("nino_retos_reto_id_index", "nino_retos", "reto_id"),
    ("nino_logros_logro_id_index", "nino_logros", "logro_id"),
    ("nino_recompensas_recompensa_id_index", "nino_recompensas", "recompensa_id"),
]

# Índices compuestos alineados con las consultas reales de los portales y la API.
QUERY_INDEXES: list[tuple[str, str, str]] = [
    ("citas_nutriologo_fecha_index", "citas", "id_nutriologo, fecha_preferida"),
    ("citas_padre_fecha_index", "citas", "id_padre, fecha_preferida DESC"),
    ("menus_nino_activo_index", "menus", "nino_id"),
    ("reportes_nino_creado_index", "reportes", "nino_id, created_at DESC"),
    ("security_audit_logs_created_at_index", "security_audit_logs", "created_at DESC"),
    ("login_attempts_email_created_index", "login_attempts", "email, created_at DESC"),
    ("habito_registros_nino_habito_fecha_index", "habito_registros", "nino_habito_id, fecha DESC"),
]

# Reglas de dominio que deben vivir en la base, no sólo en la capa de aplicación.
CHECK_CONSTRAINTS: list[tuple[str, str, str]] = [
    ("evaluaciones_peso_kg_check", "evaluaciones", "peso_kg IS NULL OR peso_kg > 0"),
    ("evaluaciones_talla_cm_check", "evaluaciones", "talla_cm IS NULL OR talla_cm > 0"),
    ("evaluaciones_imc_check", "evaluaciones", "imc IS NULL OR imc > 0"),
    (
        "evaluaciones_percentil_check",
        "evaluaciones",
        "percentil_oms IS NULL OR (percentil_oms >= 0 AND percentil_oms <= 100)",
    ),
    ("ninos_peso_actual_check", "ninos", "peso_actual_kg IS NULL OR peso_actual_kg > 0"),
    ("ninos_talla_actual_check", "ninos", "talla_actual_cm IS NULL OR talla_actual_cm > 0"),
    ("nino_puntos_totales_check", "nino_puntos", "puntos_totales >= 0"),
    ("nino_puntos_nivel_check", "nino_puntos", "nivel_actual >= 1"),
    ("citas_franja_check", "citas", "franja IN ('manana', 'tarde')"),
    (
        "citas_estado_check",
        "citas",
        "estado IN ('pendiente', 'asignada', 'confirmada', 'cancelada')",
    ),
]


def _tables(bind) -> set[str]:
    return set(sa.inspect(bind).get_table_names())


def upgrade() -> None:
    bind = op.get_bind()
    tables = _tables(bind)

    for name, table, columns in FK_INDEXES + QUERY_INDEXES:
        if table in tables:
            op.execute(f'CREATE INDEX IF NOT EXISTS "{name}" ON "{table}" ({columns})')

    for name, table, expression in CHECK_CONSTRAINTS:
        if table not in tables:
            continue
        # ADD CONSTRAINT no admite IF NOT EXISTS; se emula con DO/EXCEPTION.
        op.execute(
            f"""
            DO $$
            BEGIN
                ALTER TABLE "{table}" ADD CONSTRAINT "{name}" CHECK ({expression});
            EXCEPTION
                WHEN duplicate_object THEN NULL;
                WHEN check_violation THEN
                    RAISE WARNING 'Datos existentes violan {name}; constraint omitido';
            END $$;
            """
        )


def downgrade() -> None:
    bind = op.get_bind()
    tables = _tables(bind)

    for name, table, _expression in CHECK_CONSTRAINTS:
        if table in tables:
            op.execute(f'ALTER TABLE "{table}" DROP CONSTRAINT IF EXISTS "{name}"')

    for name, table, _columns in FK_INDEXES + QUERY_INDEXES:
        if table in tables:
            op.execute(f'DROP INDEX IF EXISTS "{name}"')
