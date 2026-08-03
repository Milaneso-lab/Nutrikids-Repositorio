"""RBAC normalizado: roles, permisos, rol_permiso y extensión de usuarios.

Revision ID: 20260728_0002
Revises: 20260728_0001
Create Date: 2026-07-28
"""

from typing import Sequence, Union

import sqlalchemy as sa
from alembic import op
from sqlalchemy.dialects import postgresql

revision: str = "20260728_0002"
down_revision: Union[str, None] = "20260728_0001"
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None

usuario_estado_enum = postgresql.ENUM(
    "activo",
    "suspendido",
    "pendiente_verificacion",
    name="usuario_estado_enum",
    create_type=False,
)

ROLES = [
    ("admin", "Administrador del sistema"),
    ("nutriologo", "Nutriólogo clínico"),
    ("padre", "Padre o tutor responsable"),
    ("nino", "Cuenta ligera de niño (app móvil)"),
]

PERMISOS = [
    ("pacientes.leer", "Consultar expedientes de niños"),
    ("pacientes.escribir", "Crear y editar niños"),
    ("evaluaciones.leer", "Consultar evaluaciones"),
    ("evaluaciones.escribir", "Registrar evaluaciones"),
    ("menus.leer", "Consultar menús"),
    ("menus.escribir", "Crear y editar menús"),
    ("reportes.leer", "Consultar reportes"),
    ("reportes.escribir", "Generar reportes"),
    ("citas.leer", "Consultar citas"),
    ("citas.asignar", "Tomar y gestionar citas"),
    ("citas.agendar", "Agendar citas"),
    ("contenido.moderar", "Moderar foro y comentarios"),
    ("usuarios.administrar", "Gestionar usuarios del sistema"),
    ("gamificacion.administrar", "Gestionar catálogos de gamificación"),
    ("gamificacion.participar", "Participar en hábitos y retos"),
]

ROL_PERMISOS = {
    "admin": [p[0] for p in PERMISOS],
    "nutriologo": [
        "pacientes.leer",
        "pacientes.escribir",
        "evaluaciones.leer",
        "evaluaciones.escribir",
        "menus.leer",
        "menus.escribir",
        "reportes.leer",
        "reportes.escribir",
        "citas.leer",
        "citas.asignar",
        "gamificacion.administrar",
    ],
    "padre": [
        "pacientes.leer",
        "evaluaciones.leer",
        "menus.leer",
        "reportes.leer",
        "citas.leer",
        "citas.agendar",
        "gamificacion.participar",
    ],
    "nino": ["gamificacion.participar"],
}


def upgrade() -> None:
    bind = op.get_bind()
    inspector = sa.inspect(bind)

    usuario_estado_enum.create(bind, checkfirst=True)

    if "roles" not in inspector.get_table_names():
        op.create_table(
            "roles",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("nombre", sa.String(50), nullable=False),
            sa.Column("descripcion", sa.String(255), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column(
                "updated_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.PrimaryKeyConstraint("id"),
            sa.UniqueConstraint("nombre"),
        )

    if "permisos" not in inspector.get_table_names():
        op.create_table(
            "permisos",
            sa.Column("id", sa.BigInteger(), autoincrement=True, nullable=False),
            sa.Column("clave", sa.String(100), nullable=False),
            sa.Column("descripcion", sa.String(255), nullable=True),
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.Column(
                "updated_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
            sa.PrimaryKeyConstraint("id"),
            sa.UniqueConstraint("clave"),
        )

    if "rol_permiso" not in inspector.get_table_names():
        op.create_table(
            "rol_permiso",
            sa.Column("rol_id", sa.BigInteger(), nullable=False),
            sa.Column("permiso_id", sa.BigInteger(), nullable=False),
            sa.ForeignKeyConstraint(["permiso_id"], ["permisos.id"], ondelete="CASCADE"),
            sa.ForeignKeyConstraint(["rol_id"], ["roles.id"], ondelete="CASCADE"),
            sa.PrimaryKeyConstraint("rol_id", "permiso_id"),
        )
        op.create_index("rol_permiso_permiso_id_index", "rol_permiso", ["permiso_id"])

    # Semilla roles y permisos
    for nombre, descripcion in ROLES:
        op.execute(
            sa.text(
                "INSERT INTO roles (nombre, descripcion) "
                "SELECT :nombre, :descripcion "
                "WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nombre = :nombre)"
            ).bindparams(nombre=nombre, descripcion=descripcion)
        )

    for clave, descripcion in PERMISOS:
        op.execute(
            sa.text(
                "INSERT INTO permisos (clave, descripcion) "
                "SELECT :clave, :descripcion "
                "WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE clave = :clave)"
            ).bindparams(clave=clave, descripcion=descripcion)
        )

    for rol_nombre, permisos in ROL_PERMISOS.items():
        for permiso_clave in permisos:
            op.execute(
                sa.text(
                    "INSERT INTO rol_permiso (rol_id, permiso_id) "
                    "SELECT r.id, p.id FROM roles r, permisos p "
                    "WHERE r.nombre = :rol AND p.clave = :permiso "
                    "AND NOT EXISTS ("
                    "  SELECT 1 FROM rol_permiso rp "
                    "  WHERE rp.rol_id = r.id AND rp.permiso_id = p.id"
                    ")"
                ).bindparams(rol=rol_nombre, permiso=permiso_clave)
            )

    # Extensión de usuarios (compatibilidad: se conserva columna `rol` string)
    usuario_cols = {c["name"] for c in inspector.get_columns("usuarios")}

    if "telefono" not in usuario_cols:
        op.add_column("usuarios", sa.Column("telefono", sa.String(30), nullable=True))
    if "rol_id" not in usuario_cols:
        op.add_column("usuarios", sa.Column("rol_id", sa.BigInteger(), nullable=True))
        op.create_foreign_key(
            "usuarios_rol_id_foreign",
            "usuarios",
            "roles",
            ["rol_id"],
            ["id"],
            ondelete="RESTRICT",
        )
        op.create_index("usuarios_rol_id_index", "usuarios", ["rol_id"])
    if "estado" not in usuario_cols:
        op.add_column(
            "usuarios",
            sa.Column(
                "estado",
                usuario_estado_enum,
                server_default="activo",
                nullable=False,
            ),
        )
        op.create_index("usuarios_estado_index", "usuarios", ["estado"])
    if "email_verificado_en" not in usuario_cols:
        op.add_column(
            "usuarios",
            sa.Column("email_verificado_en", sa.DateTime(timezone=True), nullable=True),
        )
    if "ultimo_login_en" not in usuario_cols:
        op.add_column(
            "usuarios",
            sa.Column("ultimo_login_en", sa.DateTime(timezone=True), nullable=True),
        )
    if "created_at" not in usuario_cols:
        op.add_column(
            "usuarios",
            sa.Column(
                "created_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
        )
    if "updated_at" not in usuario_cols:
        op.add_column(
            "usuarios",
            sa.Column(
                "updated_at",
                sa.DateTime(timezone=True),
                server_default=sa.text("CURRENT_TIMESTAMP"),
                nullable=False,
            ),
        )

    # Ampliar email a 150 según diseño objetivo
    op.execute("ALTER TABLE usuarios ALTER COLUMN email TYPE VARCHAR(150)")

    # Poblar rol_id desde columna legacy `rol`
    op.execute(
        """
        UPDATE usuarios u
        SET rol_id = r.id
        FROM roles r
        WHERE u.rol_id IS NULL AND u.rol = r.nombre
        """
    )
    op.execute(
        """
        UPDATE usuarios
        SET rol_id = (SELECT id FROM roles WHERE nombre = 'padre')
        WHERE rol_id IS NULL
        """
    )
    op.alter_column("usuarios", "rol_id", existing_type=sa.BigInteger(), nullable=False)

    # Usuario sistema para niños migrados sin padre conocido (T2.2)
    op.execute(
        sa.text(
            """
            INSERT INTO usuarios (
                nombre, apellido_paterno, apellido_materno, email, contrasena, rol, rol_id, estado
            )
            SELECT
                'Sistema', 'Migración', 'NutriKids',
                'sistema-migracion@nutrikids.internal',
                '$2b$12$placeholder.hash.no.login.allowed',
                'admin',
                (SELECT id FROM roles WHERE nombre = 'admin'),
                'suspendido'
            WHERE NOT EXISTS (
                SELECT 1 FROM usuarios WHERE email = 'sistema-migracion@nutrikids.internal'
            )
            """
        )
    )


def downgrade() -> None:
    op.drop_index("usuarios_estado_index", table_name="usuarios")
    op.drop_index("usuarios_rol_id_index", table_name="usuarios")
    op.drop_constraint("usuarios_rol_id_foreign", "usuarios", type_="foreignkey")
    for col in (
        "updated_at",
        "created_at",
        "ultimo_login_en",
        "email_verificado_en",
        "estado",
        "rol_id",
        "telefono",
    ):
        op.drop_column("usuarios", col)

    op.drop_index("rol_permiso_permiso_id_index", table_name="rol_permiso")
    op.drop_table("rol_permiso")
    op.drop_table("permisos")
    op.drop_table("roles")

    bind = op.get_bind()
    usuario_estado_enum.drop(bind, checkfirst=True)
