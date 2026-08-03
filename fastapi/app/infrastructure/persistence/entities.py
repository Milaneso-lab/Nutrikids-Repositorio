"""Entidades SQLAlchemy del esquema objetivo (03_BaseDatos.md)."""

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
    UniqueConstraint,
    func,
)
from sqlalchemy.dialects.postgresql import JSONB
from sqlalchemy.orm import Mapped, mapped_column

from database import Base


class Role(Base):
    __tablename__ = "roles"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(50), unique=True, nullable=False)
    descripcion: Mapped[str | None] = mapped_column(String(255), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class Nino(Base):
    __tablename__ = "ninos"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    padre_id: Mapped[int] = mapped_column(ForeignKey("usuarios.id_usuario", ondelete="CASCADE"), nullable=False)
    nutriologo_asignado_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    nombre: Mapped[str] = mapped_column(String(100), nullable=False)
    apellidos: Mapped[str] = mapped_column(String(100), nullable=False)
    fecha_nacimiento: Mapped[date] = mapped_column(Date, nullable=False)
    sexo: Mapped[str] = mapped_column(String(20), nullable=False)
    peso_actual_kg: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), nullable=True)
    talla_actual_cm: Mapped[Decimal | None] = mapped_column(Numeric(5, 2), nullable=True)
    avatar_config: Mapped[dict | None] = mapped_column(JSONB, nullable=True)
    codigo_vinculacion: Mapped[str | None] = mapped_column(String(12), unique=True, nullable=True)
    requiere_vinculacion_padre: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    deleted_at: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)


class NinoCredenciales(Base):
    __tablename__ = "nino_credenciales"

    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), primary_key=True)
    pin_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    dispositivo_id: Mapped[str | None] = mapped_column(String(255), nullable=True)
    vinculado_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class MenuItem(Base):
    __tablename__ = "menu_items"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    menu_id: Mapped[int] = mapped_column(ForeignKey("menus.id", ondelete="CASCADE"), nullable=False)
    dia_semana: Mapped[str] = mapped_column(String(10), nullable=False)
    tipo_comida: Mapped[str] = mapped_column(String(20), nullable=False)
    descripcion: Mapped[str] = mapped_column(Text, nullable=False)
    calorias_aprox: Mapped[int | None] = mapped_column(Integer, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class HabitoCatalogo(Base):
    __tablename__ = "habitos_catalogo"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(150), nullable=False)
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    categoria: Mapped[str] = mapped_column(String(20), nullable=False)
    icono: Mapped[str | None] = mapped_column(String(100), nullable=True)
    puntos_base: Mapped[int] = mapped_column(Integer, default=0, nullable=False)
    activo: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class NinoHabito(Base):
    __tablename__ = "nino_habitos"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=False)
    habito_id: Mapped[int] = mapped_column(ForeignKey("habitos_catalogo.id", ondelete="CASCADE"), nullable=False)
    frecuencia: Mapped[str] = mapped_column(String(20), nullable=False)
    asignado_por_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    activo: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class HabitoRegistro(Base):
    __tablename__ = "habito_registros"
    __table_args__ = (UniqueConstraint("nino_habito_id", "fecha", name="habito_registros_nino_habito_fecha_unique"),)

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nino_habito_id: Mapped[int] = mapped_column(ForeignKey("nino_habitos.id", ondelete="CASCADE"), nullable=False)
    fecha: Mapped[date] = mapped_column(Date, nullable=False)
    completado: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    registrado_en: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class RetoCatalogo(Base):
    __tablename__ = "retos_catalogo"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(150), nullable=False)
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    tipo: Mapped[str] = mapped_column(String(20), nullable=False)
    condicion: Mapped[dict] = mapped_column(JSONB, nullable=False)
    puntos_recompensa: Mapped[int] = mapped_column(Integer, default=0, nullable=False)
    activo: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    fecha_inicio: Mapped[date | None] = mapped_column(Date, nullable=True)
    fecha_fin: Mapped[date | None] = mapped_column(Date, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class NinoReto(Base):
    __tablename__ = "nino_retos"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=False)
    reto_id: Mapped[int] = mapped_column(ForeignKey("retos_catalogo.id", ondelete="CASCADE"), nullable=False)
    progreso: Mapped[dict | None] = mapped_column(JSONB, nullable=True)
    completado: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    completado_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class LogroCatalogo(Base):
    __tablename__ = "logros_catalogo"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(150), nullable=False)
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    icono: Mapped[str | None] = mapped_column(String(100), nullable=True)
    criterio: Mapped[dict | None] = mapped_column(JSONB, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class NinoLogro(Base):
    __tablename__ = "nino_logros"
    __table_args__ = (UniqueConstraint("nino_id", "logro_id", name="nino_logros_nino_logro_unique"),)

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=False)
    logro_id: Mapped[int] = mapped_column(ForeignKey("logros_catalogo.id", ondelete="CASCADE"), nullable=False)
    obtenido_en: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class NinoPuntos(Base):
    __tablename__ = "nino_puntos"

    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), primary_key=True)
    puntos_totales: Mapped[int] = mapped_column(Integer, default=0, nullable=False)
    nivel_actual: Mapped[int] = mapped_column(Integer, default=1, nullable=False)
    actualizado_en: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class RecompensaCatalogo(Base):
    __tablename__ = "recompensas_catalogo"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nombre: Mapped[str] = mapped_column(String(150), nullable=False)
    descripcion: Mapped[str | None] = mapped_column(Text, nullable=True)
    costo_puntos: Mapped[int] = mapped_column(Integer, nullable=False)
    stock: Mapped[int | None] = mapped_column(Integer, nullable=True)
    activo: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    updated_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class NinoRecompensa(Base):
    __tablename__ = "nino_recompensas"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    nino_id: Mapped[int] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=False)
    recompensa_id: Mapped[int] = mapped_column(ForeignKey("recompensas_catalogo.id", ondelete="CASCADE"), nullable=False)
    canjeado_en: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
    estado: Mapped[str] = mapped_column(String(20), default="pendiente", nullable=False)


class RefreshToken(Base):
    __tablename__ = "refresh_tokens"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    usuario_id: Mapped[int | None] = mapped_column(ForeignKey("usuarios.id_usuario", ondelete="CASCADE"), nullable=True)
    nino_id: Mapped[int | None] = mapped_column(ForeignKey("ninos.id", ondelete="CASCADE"), nullable=True)
    token_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    dispositivo: Mapped[str | None] = mapped_column(String(255), nullable=True)
    expira_en: Mapped[datetime] = mapped_column(DateTime(timezone=True), nullable=False)
    revocado_en: Mapped[datetime | None] = mapped_column(DateTime(timezone=True), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class Permiso(Base):
    __tablename__ = "permisos"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    clave: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    descripcion: Mapped[str | None] = mapped_column(String(255), nullable=True)


class RolPermiso(Base):
    __tablename__ = "rol_permiso"

    rol_id: Mapped[int] = mapped_column(ForeignKey("roles.id", ondelete="CASCADE"), primary_key=True)
    permiso_id: Mapped[int] = mapped_column(ForeignKey("permisos.id", ondelete="CASCADE"), primary_key=True)


class LoginAttempt(Base):
    __tablename__ = "login_attempts"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    email: Mapped[str] = mapped_column(String(150), nullable=False, index=True)
    ip_address: Mapped[str | None] = mapped_column(String(45), nullable=True)
    exito: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class SecurityAuditLog(Base):
    __tablename__ = "security_audit_logs"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    usuario_id: Mapped[int | None] = mapped_column(
        ForeignKey("usuarios.id_usuario", ondelete="SET NULL"), nullable=True
    )
    accion: Mapped[str] = mapped_column(String(100), nullable=False)
    recurso: Mapped[str | None] = mapped_column(String(150), nullable=True)
    ip_address: Mapped[str | None] = mapped_column(String(45), nullable=True)
    detalles: Mapped[dict | None] = mapped_column(JSONB, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())


class PasswordHistory(Base):
    __tablename__ = "password_history"

    id: Mapped[int] = mapped_column(BigInteger, primary_key=True, autoincrement=True)
    usuario_id: Mapped[int] = mapped_column(ForeignKey("usuarios.id_usuario", ondelete="CASCADE"), nullable=False)
    contrasena_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), server_default=func.now())
