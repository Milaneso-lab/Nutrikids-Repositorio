"""Modelos SQLAlchemy alineados con migraciones Laravel + dominio API (padres / admin)."""

from __future__ import annotations

from datetime import date, datetime
from decimal import Decimal

from sqlalchemy import (
    BigInteger,
    Boolean,
    Date,
    DateTime,
    ForeignKey,
    Integer,
    Numeric,
    String,
    Text,
    event,
    func,
    text,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from database import Base

# Entidades del esquema objetivo (03_BaseDatos.md)
from app.infrastructure.persistence.entities import (  # noqa: E402
    HabitoCatalogo,
    HabitoRegistro,
    LogroCatalogo,
    LoginAttempt,
    MenuItem,
    Nino,
    NinoCredenciales,
    NinoHabito,
    NinoLogro,
    NinoPuntos,
    NinoRecompensa,
    NinoReto,
    PasswordHistory,
    Permiso,
    RefreshToken,
    RecompensaCatalogo,
    RetoCatalogo,
    Role,
    RolPermiso,
    SecurityAuditLog,
)


# --- Compartido: usuarios (Laravel auth + FastAPI JWT) ---


class Usuario(Base):
    __tablename__ = "usuarios"

    id_usuario: Mapped[int] = mapped_column(primary_key=True, index=True)
    nombre: Mapped[str] = mapped_column(String(100), nullable=False)
    apellido_paterno: Mapped[str] = mapped_column(String(100), nullable=False)
    apellido_materno: Mapped[str | None] = mapped_column(String(100), nullable=True)
    email: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    contrasena: Mapped[str] = mapped_column(String(255), nullable=False)
    rol: Mapped[str] = mapped_column(String(20), nullable=False, default="padre")
    telefono: Mapped[str | None] = mapped_column(String(30), nullable=True)
    rol_id: Mapped[int | None] = mapped_column(BigInteger, nullable=True)
    estado: Mapped[str | None] = mapped_column(String(30), nullable=True)
    email_verificado_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    ultimo_login_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)


# `usuarios` guarda el rol dos veces: `rol` (texto, que es lo que envían los
# clientes) y `rol_id` (clave foránea NOT NULL hacia `roles`, que usa el RBAC).
# Sin esta sincronización cualquier alta de usuario falla con NotNullViolation,
# porque ningún servicio informa `rol_id` explícitamente.
_ROL_POR_DEFECTO = "padre"
_mapa_roles: dict[str, int] = {}


def _obtener_mapa_roles(connection) -> dict[str, int]:
    global _mapa_roles
    if not _mapa_roles:
        try:
            filas = connection.execute(text("SELECT nombre, id FROM roles")).fetchall()
            _mapa_roles = {nombre: int(rol_id) for nombre, rol_id in filas}
        except Exception:  # noqa: BLE001 - la tabla puede no existir durante migraciones
            return {}
    return _mapa_roles


def _sincronizar_rol(connection, usuario: "Usuario") -> None:
    mapa = _obtener_mapa_roles(connection)
    if not mapa:
        return

    if usuario.rol and usuario.rol in mapa:
        if usuario.rol_id != mapa[usuario.rol]:
            usuario.rol_id = mapa[usuario.rol]
        return

    if usuario.rol_id is not None:
        for nombre, rol_id in mapa.items():
            if rol_id == usuario.rol_id:
                usuario.rol = nombre
                return

    if _ROL_POR_DEFECTO in mapa:
        usuario.rol = _ROL_POR_DEFECTO
        usuario.rol_id = mapa[_ROL_POR_DEFECTO]


@event.listens_for(Usuario, "before_insert")
def _usuario_before_insert(mapper, connection, target: Usuario) -> None:  # noqa: ARG001
    _sincronizar_rol(connection, target)


@event.listens_for(Usuario, "before_update")
def _usuario_before_update(mapper, connection, target: Usuario) -> None:  # noqa: ARG001
    _sincronizar_rol(connection, target)


# --- Público / padres (también usado desde Laravel en vistas) ---


class Contacto(Base):
    __tablename__ = "contactos"

    id_contacto: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(50), nullable=False)
    apellido: Mapped[str] = mapped_column(String(50), nullable=False)
    email: Mapped[str] = mapped_column(String(100), nullable=False)
    mensaje: Mapped[str] = mapped_column(Text, nullable=False)
    fecha_creacion: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())


class Comentario(Base):
    __tablename__ = "comentarios"

    id_comentario: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    id_usuario: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="CASCADE"),
        nullable=True,
    )
    id_comentario_padre: Mapped[int | None] = mapped_column(
        ForeignKey("comentarios.id_comentario", ondelete="CASCADE"),
        nullable=True,
    )
    nombre: Mapped[str] = mapped_column(String(50), nullable=False)
    apellido: Mapped[str] = mapped_column(String(50), nullable=False)
    comentario: Mapped[str] = mapped_column(Text, nullable=False)
    fecha_comentario: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())

    respuestas: Mapped[list["Comentario"]] = relationship(
        "Comentario",
        back_populates="padre",
        foreign_keys="Comentario.id_comentario_padre",
        cascade="all, delete-orphan",
    )
    padre: Mapped["Comentario | None"] = relationship(
        "Comentario",
        back_populates="respuestas",
        remote_side=[id_comentario],
        foreign_keys=[id_comentario_padre],
    )


class RespuestaDiscusion(Base):
    __tablename__ = "respuestas_discusion"

    id_respuesta: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    id_discusion: Mapped[int] = mapped_column(
        ForeignKey("discusiones.id_discusion", ondelete="CASCADE"),
        nullable=False,
    )
    id_usuario: Mapped[int] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="CASCADE"),
        nullable=False,
    )
    nombre: Mapped[str] = mapped_column(String(50), nullable=False)
    apellido: Mapped[str] = mapped_column(String(50), nullable=False)
    mensaje: Mapped[str] = mapped_column(Text, nullable=False)
    fecha_creacion: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())


class Discusion(Base):
    __tablename__ = "discusiones"

    id_discusion: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    id_usuario: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="CASCADE"),
        nullable=True,
    )
    tema: Mapped[str] = mapped_column(String(255), nullable=False)
    descripcion: Mapped[str] = mapped_column(Text, nullable=False)
    fecha_creacion: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())

    respuestas: Mapped[list["RespuestaDiscusion"]] = relationship(
        "RespuestaDiscusion",
        backref="discusion",
        cascade="all, delete-orphan",
        order_by="RespuestaDiscusion.fecha_creacion",
    )


# --- Clínico (nutriólogo / admin vía API + Laravel) ---


class Paciente(Base):
    __tablename__ = "pacientes"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nombre: Mapped[str | None] = mapped_column(String(100), nullable=True)
    apellidos: Mapped[str | None] = mapped_column(String(100), nullable=True)
    fecha_nacimiento: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Evaluacion(Base):
    __tablename__ = "evaluaciones"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    paciente_id: Mapped[int | None] = mapped_column(
        ForeignKey("pacientes.id", ondelete="SET NULL"),
        nullable=True,
    )
    nino_id: Mapped[int | None] = mapped_column(
        ForeignKey("ninos.id", ondelete="CASCADE"),
        nullable=True,
    )
    nutriologo_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"),
        nullable=True,
    )
    peso: Mapped[str | None] = mapped_column(String(20), nullable=True)
    talla: Mapped[str | None] = mapped_column(String(20), nullable=True)
    peso_kg: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), nullable=True)
    talla_cm: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), nullable=True)
    imc: Mapped[Decimal | None] = mapped_column(Numeric(4, 2), nullable=True)
    percentil_oms: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), nullable=True)
    fecha_evaluacion: Mapped[date | None] = mapped_column(Date, nullable=True)
    recomendaciones: Mapped[str | None] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Menu(Base):
    __tablename__ = "menus"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nombre: Mapped[str | None] = mapped_column(String(150), nullable=True)
    paciente_id: Mapped[int | None] = mapped_column(
        ForeignKey("pacientes.id", ondelete="SET NULL"),
        nullable=True,
    )
    nino_id: Mapped[int | None] = mapped_column(
        ForeignKey("ninos.id", ondelete="CASCADE"),
        nullable=True,
    )
    nutriologo_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"),
        nullable=True,
    )
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    objetivo: Mapped[str | None] = mapped_column(String(150), nullable=True)
    fecha_inicio: Mapped[date | None] = mapped_column(Date, nullable=True)
    fecha_fin: Mapped[date | None] = mapped_column(Date, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Reporte(Base):
    __tablename__ = "reportes"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    paciente_id: Mapped[int | None] = mapped_column(
        ForeignKey("pacientes.id", ondelete="SET NULL"),
        nullable=True,
    )
    nino_id: Mapped[int | None] = mapped_column(
        ForeignKey("ninos.id", ondelete="CASCADE"),
        nullable=True,
    )
    nutriologo_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"),
        nullable=True,
    )
    titulo: Mapped[str | None] = mapped_column(String(150), nullable=True)
    contenido: Mapped[str | None] = mapped_column(Text, nullable=True)
    pdf_generado_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


# --- Laravel: tablas extendidas (reservadas para futuras columnas de negocio) ---


class Infante(Base):
    __tablename__ = "infantes"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Cita(Base):
    __tablename__ = "citas"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    id_padre: Mapped[int] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="CASCADE"),
        nullable=False,
    )
    id_nutriologo: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"),
        nullable=True,
    )
    nino_id: Mapped[int | None] = mapped_column(
        ForeignKey("ninos.id", ondelete="SET NULL"),
        nullable=True,
    )
    fecha_preferida: Mapped[date] = mapped_column(Date, nullable=False)
    franja: Mapped[str] = mapped_column(String(20), nullable=False, default="manana")
    telefono: Mapped[str | None] = mapped_column(String(30), nullable=True)
    mensaje: Mapped[str | None] = mapped_column(Text, nullable=True)
    estado: Mapped[str] = mapped_column(String(20), nullable=False, default="pendiente")
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Alerta(Base):
    __tablename__ = "alertas"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nino_id: Mapped[int | None] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=True)
    tipo: Mapped[str | None] = mapped_column(String(50), nullable=True)
    severidad: Mapped[str | None] = mapped_column(String(20), nullable=True)
    mensaje: Mapped[str | None] = mapped_column(Text, nullable=True)
    atendida: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    atendida_por_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    atendida_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class Alergia(Base):
    __tablename__ = "alergias"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nino_id: Mapped[int | None] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=True)
    tipo: Mapped[str | None] = mapped_column(String(20), nullable=True)
    descripcion: Mapped[str | None] = mapped_column(String(255), nullable=True)
    severidad: Mapped[str | None] = mapped_column(String(20), nullable=True)
    registrada_por_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class NotaNutriologo(Base):
    __tablename__ = "notas_nutriologo"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nino_id: Mapped[int | None] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=True)
    nutriologo_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    nota: Mapped[str | None] = mapped_column(Text, nullable=True)
    privada: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


class MenuSemanal(Base):
    __tablename__ = "menus_semanales"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    nombre: Mapped[str | None] = mapped_column(String(150), nullable=True)
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    creado_por_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    publico: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now()
    )


# --- Infra Laravel (sesiones, caché, colas) ---


class PasswordResetToken(Base):
    __tablename__ = "password_reset_tokens"

    email: Mapped[str] = mapped_column(String(255), primary_key=True)
    token: Mapped[str] = mapped_column(String(255), nullable=False)
    created_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)


class CacheEntry(Base):
    __tablename__ = "cache"

    key: Mapped[str] = mapped_column(String(255), primary_key=True)
    value: Mapped[str] = mapped_column(Text, nullable=False)
    expiration: Mapped[int] = mapped_column(Integer, nullable=False)


class CacheLock(Base):
    __tablename__ = "cache_locks"

    key: Mapped[str] = mapped_column(String(255), primary_key=True)
    owner: Mapped[str] = mapped_column(String(255), nullable=False)
    expiration: Mapped[int] = mapped_column(Integer, nullable=False)


class Job(Base):
    __tablename__ = "jobs"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    queue: Mapped[str] = mapped_column(String(255), nullable=False)
    payload: Mapped[str] = mapped_column(Text, nullable=False)
    attempts: Mapped[int] = mapped_column(Integer, nullable=False)
    reserved_at: Mapped[int | None] = mapped_column(Integer, nullable=True)
    available_at: Mapped[int] = mapped_column(Integer, nullable=False)
    created_at: Mapped[int] = mapped_column(Integer, nullable=False)


class JobBatch(Base):
    __tablename__ = "job_batches"

    id: Mapped[str] = mapped_column(String(255), primary_key=True)
    name: Mapped[str] = mapped_column(String(255), nullable=False)
    total_jobs: Mapped[int] = mapped_column(Integer, nullable=False)
    pending_jobs: Mapped[int] = mapped_column(Integer, nullable=False)
    failed_jobs: Mapped[int] = mapped_column(Integer, nullable=False)
    failed_job_ids: Mapped[str] = mapped_column(Text, nullable=False)
    options: Mapped[str | None] = mapped_column(Text, nullable=True)
    cancelled_at: Mapped[int | None] = mapped_column(Integer, nullable=True)
    created_at: Mapped[int] = mapped_column(Integer, nullable=False)
    finished_at: Mapped[int | None] = mapped_column(Integer, nullable=True)


class FailedJob(Base):
    __tablename__ = "failed_jobs"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    uuid: Mapped[str] = mapped_column(String(255), unique=True, nullable=False)
    connection: Mapped[str] = mapped_column(Text, nullable=False)
    queue: Mapped[str] = mapped_column(Text, nullable=False)
    payload: Mapped[str] = mapped_column(Text, nullable=False)
    exception: Mapped[str] = mapped_column(Text, nullable=False)
    failed_at: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())


class Session(Base):
    __tablename__ = "sessions"

    id: Mapped[str] = mapped_column(String(255), primary_key=True)
    user_id: Mapped[int | None] = mapped_column(BigInteger, nullable=True, index=True)
    ip_address: Mapped[str | None] = mapped_column(String(45), nullable=True)
    user_agent: Mapped[str | None] = mapped_column(Text, nullable=True)
    payload: Mapped[str] = mapped_column(Text, nullable=False)
    last_activity: Mapped[int] = mapped_column(Integer, nullable=False, index=True)
