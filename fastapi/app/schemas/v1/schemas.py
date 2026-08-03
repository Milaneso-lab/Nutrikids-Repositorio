"""DTOs Pydantic para API v1 (/api/v1/*)."""

from __future__ import annotations

from datetime import date, datetime
from decimal import Decimal
from typing import Any, Literal

from pydantic import BaseModel, ConfigDict, EmailStr, Field


# --- Comunes ---


class MessageOut(BaseModel):
    message: str


# --- Usuarios ---


class UsuarioCreateV1(BaseModel):
    nombre: str = Field(..., min_length=1, max_length=100)
    apellido_paterno: str = Field(..., min_length=1, max_length=100)
    apellido_materno: str | None = Field(None, max_length=100)
    email: EmailStr
    contrasena: str = Field(..., min_length=8, max_length=128)
    rol: Literal["admin", "nutriologo", "padre"] = "padre"
    telefono: str | None = Field(None, max_length=30)


class UsuarioUpdateV1(BaseModel):
    nombre: str | None = Field(None, min_length=1, max_length=100)
    apellido_paterno: str | None = Field(None, min_length=1, max_length=100)
    apellido_materno: str | None = Field(None, max_length=100)
    telefono: str | None = Field(None, max_length=30)
    estado: Literal["activo", "suspendido", "pendiente_verificacion"] | None = None


class UsuarioOutV1(BaseModel):
    id_usuario: int
    nombre: str
    apellido_paterno: str
    apellido_materno: str | None
    email: EmailStr
    rol: str
    telefono: str | None = None
    estado: str | None = None

    model_config = ConfigDict(from_attributes=True)


# --- Niños ---


class NinoCreateV1(BaseModel):
    padre_id: int
    nutriologo_asignado_id: int | None = None
    nombre: str = Field(..., min_length=1, max_length=100)
    apellidos: str = Field(..., min_length=1, max_length=100)
    fecha_nacimiento: date
    sexo: Literal["masculino", "femenino", "otro"]
    peso_actual_kg: Decimal | None = Field(None, ge=0, le=300)
    talla_actual_cm: Decimal | None = Field(None, ge=0, le=300)
    avatar_config: dict[str, Any] | None = None


class NinoUpdateV1(BaseModel):
    nutriologo_asignado_id: int | None = None
    nombre: str | None = Field(None, min_length=1, max_length=100)
    apellidos: str | None = Field(None, min_length=1, max_length=100)
    fecha_nacimiento: date | None = None
    sexo: Literal["masculino", "femenino", "otro"] | None = None
    peso_actual_kg: Decimal | None = Field(None, ge=0, le=300)
    talla_actual_cm: Decimal | None = Field(None, ge=0, le=300)
    avatar_config: dict[str, Any] | None = None


class NinoOutV1(BaseModel):
    id: int
    padre_id: int
    nutriologo_asignado_id: int | None
    nombre: str
    apellidos: str
    fecha_nacimiento: date
    sexo: str
    peso_actual_kg: Decimal | None
    talla_actual_cm: Decimal | None
    avatar_config: dict[str, Any] | None
    codigo_vinculacion: str | None
    requiere_vinculacion_padre: bool
    created_at: datetime | None = None
    updated_at: datetime | None = None

    model_config = ConfigDict(from_attributes=True)


class VincularDispositivoInV1(BaseModel):
    pin: str = Field(..., min_length=4, max_length=6)
    confirmar_pin: str = Field(..., min_length=4, max_length=6)


class VincularDispositivoOutV1(BaseModel):
    nino_id: int
    codigo_vinculacion: str
    pin_configurado: bool = True


# --- Evaluaciones ---


class EvaluacionCreateV1(BaseModel):
    nino_id: int
    nutriologo_id: int | None = None
    peso_kg: Decimal = Field(..., gt=0, le=300)
    talla_cm: Decimal = Field(..., gt=0, le=300)
    percentil_oms: Decimal | None = Field(None, ge=0, le=100)
    recomendaciones: str | None = None
    fecha_evaluacion: date | None = None


class EvaluacionUpdateV1(BaseModel):
    peso_kg: Decimal | None = Field(None, gt=0, le=300)
    talla_cm: Decimal | None = Field(None, gt=0, le=300)
    percentil_oms: Decimal | None = Field(None, ge=0, le=100)
    recomendaciones: str | None = None
    fecha_evaluacion: date | None = None


class EvaluacionOutV1(BaseModel):
    id: int
    nino_id: int | None
    nutriologo_id: int | None
    peso_kg: Decimal | None
    talla_cm: Decimal | None
    imc: Decimal | None
    percentil_oms: Decimal | None
    recomendaciones: str | None
    fecha_evaluacion: date | None
    created_at: datetime | None = None

    model_config = ConfigDict(from_attributes=True)


# --- Clínico auxiliar ---


class AlergiaCreateV1(BaseModel):
    nino_id: int
    tipo: Literal["alimentaria", "ambiental", "medicamento", "otra"]
    descripcion: str = Field(..., min_length=1, max_length=255)
    severidad: Literal["leve", "moderada", "grave"]
    registrada_por_id: int | None = None


class AlergiaOutV1(AlergiaCreateV1):
    id: int
    model_config = ConfigDict(from_attributes=True)


class AlertaCreateV1(BaseModel):
    nino_id: int | None = None
    tipo: str = Field(..., min_length=1, max_length=50)
    severidad: Literal["info", "advertencia", "critica"]
    mensaje: str = Field(..., min_length=1)


class AlertaOutV1(AlertaCreateV1):
    id: int
    atendida: bool
    atendida_por_id: int | None = None
    atendida_en: datetime | None = None
    created_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class NotaNutriologoCreateV1(BaseModel):
    nino_id: int
    nutriologo_id: int | None = None
    nota: str = Field(..., min_length=1)
    privada: bool = True


class NotaNutriologoOutV1(NotaNutriologoCreateV1):
    id: int
    created_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


# --- Menús ---


class MenuCreateV1(BaseModel):
    nino_id: int
    nutriologo_id: int | None = None
    nombre: str = Field(..., min_length=1, max_length=150)
    objetivo: str | None = Field(None, max_length=150)
    fecha_inicio: date | None = None
    fecha_fin: date | None = None
    descripcion: str | None = None


class MenuUpdateV1(BaseModel):
    nombre: str | None = Field(None, min_length=1, max_length=150)
    objetivo: str | None = None
    fecha_inicio: date | None = None
    fecha_fin: date | None = None
    descripcion: str | None = None


class MenuOutV1(BaseModel):
    id: int
    nino_id: int | None
    nutriologo_id: int | None
    nombre: str | None
    objetivo: str | None
    fecha_inicio: date | None
    fecha_fin: date | None
    descripcion: str | None
    created_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class MenuItemCreateV1(BaseModel):
    dia_semana: Literal["lun", "mar", "mie", "jue", "vie", "sab", "dom"]
    tipo_comida: Literal["desayuno", "colacion", "comida", "cena"]
    descripcion: str = Field(..., min_length=1)
    calorias_aprox: int | None = Field(None, ge=0)


class MenuItemOutV1(MenuItemCreateV1):
    id: int
    menu_id: int
    model_config = ConfigDict(from_attributes=True)


class MenuSemanalCreateV1(BaseModel):
    nombre: str = Field(..., min_length=1, max_length=150)
    descripcion: str | None = None
    creado_por_id: int | None = None
    publico: bool = False


class MenuSemanalOutV1(MenuSemanalCreateV1):
    id: int
    created_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


# --- Reportes ---


class ReporteCreateV1(BaseModel):
    nino_id: int
    nutriologo_id: int | None = None
    titulo: str = Field(..., min_length=1, max_length=150)
    contenido: str = Field(..., min_length=1)


class ReporteUpdateV1(BaseModel):
    titulo: str | None = Field(None, min_length=1, max_length=150)
    contenido: str | None = Field(None, min_length=1)


class ReporteOutV1(BaseModel):
    id: int
    nino_id: int | None
    nutriologo_id: int | None
    titulo: str | None
    contenido: str | None
    pdf_generado_en: datetime | None = None
    created_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class ReportePdfOutV1(BaseModel):
    reporte_id: int
    mensaje: str
    datos: ReporteOutV1


# --- Citas ---


class CitaCreateV1(BaseModel):
    id_padre: int
    nino_id: int | None = None
    fecha_preferida: date
    franja: Literal["manana", "tarde"] = "manana"
    telefono: str | None = Field(None, max_length=30)
    mensaje: str | None = Field(None, max_length=2000)


class CitaAsignarV1(BaseModel):
    id_nutriologo: int


class CitaEstadoV1(BaseModel):
    estado: Literal["pendiente", "asignada", "confirmada", "cancelada"]


class CitaOutV1(BaseModel):
    id: int
    id_padre: int
    id_nutriologo: int | None
    nino_id: int | None
    fecha_preferida: date
    franja: str
    telefono: str | None
    mensaje: str | None
    estado: str
    created_at: datetime | None = None
    updated_at: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


# --- Comunidad ---


class ContactoCreateV1(BaseModel):
    nombre: str = Field(..., min_length=1, max_length=50)
    apellido: str = Field(..., min_length=1, max_length=50)
    email: EmailStr
    mensaje: str = Field(..., min_length=1, max_length=5000)


class ContactoOutV1(ContactoCreateV1):
    id_contacto: int
    fecha_creacion: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class ComentarioCreateV1(BaseModel):
    id_usuario: int
    nombre: str = Field(..., min_length=1, max_length=50)
    apellido: str = Field(..., min_length=1, max_length=50)
    comentario: str = Field(..., min_length=1, max_length=5000)


class ComentarioOutV1(ComentarioCreateV1):
    id_comentario: int
    fecha_comentario: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class DiscusionCreateV1(BaseModel):
    id_usuario: int
    tema: str = Field(..., min_length=1, max_length=255)
    descripcion: str = Field(..., min_length=1, max_length=10000)


class DiscusionUpdateV1(BaseModel):
    tema: str | None = Field(None, min_length=1, max_length=255)
    descripcion: str | None = Field(None, min_length=1, max_length=10000)


class DiscusionOutV1(DiscusionCreateV1):
    id_discusion: int
    fecha_creacion: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


# --- Gamificación ---


class HabitoCatalogoOutV1(BaseModel):
    id: int
    nombre: str
    descripcion: str | None
    categoria: str
    icono: str | None
    puntos_base: int
    activo: bool
    model_config = ConfigDict(from_attributes=True)


class NinoHabitoCreateV1(BaseModel):
    habito_id: int
    frecuencia: Literal["diaria", "semanal"]
    asignado_por_id: int | None = None


class NinoHabitoOutV1(NinoHabitoCreateV1):
    id: int
    nino_id: int
    activo: bool
    model_config = ConfigDict(from_attributes=True)


class HabitoRegistroCreateV1(BaseModel):
    fecha: date | None = None
    completado: bool = True


class HabitoRegistroOutV1(BaseModel):
    id: int
    nino_habito_id: int
    fecha: date
    completado: bool
    registrado_en: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class RetoCatalogoOutV1(BaseModel):
    id: int
    nombre: str
    descripcion: str | None
    tipo: str
    condicion: dict[str, Any]
    puntos_recompensa: int
    activo: bool
    model_config = ConfigDict(from_attributes=True)


class NinoRetoOutV1(BaseModel):
    id: int
    nino_id: int
    reto_id: int
    progreso: dict[str, Any] | None
    completado: bool
    completado_en: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class LogroCatalogoOutV1(BaseModel):
    id: int
    nombre: str
    descripcion: str | None
    icono: str | None
    criterio: dict[str, Any] | None
    model_config = ConfigDict(from_attributes=True)


class NinoLogroOutV1(BaseModel):
    id: int
    nino_id: int
    logro_id: int
    obtenido_en: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class NinoPuntosOutV1(BaseModel):
    nino_id: int
    puntos_totales: int
    nivel_actual: int
    actualizado_en: datetime | None = None
    model_config = ConfigDict(from_attributes=True)


class RecompensaCatalogoOutV1(BaseModel):
    id: int
    nombre: str
    descripcion: str | None
    costo_puntos: int
    stock: int | None
    activo: bool
    model_config = ConfigDict(from_attributes=True)


class NinoRecompensaOutV1(BaseModel):
    id: int
    nino_id: int
    recompensa_id: int
    canjeado_en: datetime | None = None
    estado: str
    model_config = ConfigDict(from_attributes=True)


class NinoJuegoOutV1(BaseModel):
    game_id: str
    reto_id: int
    nombre: str
    descripcion: str | None
    emoji: str
    puntos_recompensa: int
    best_score: int = 0
    last_score: int = 0
    plays: int = 0


class NinoJuegoProgresoInV1(BaseModel):
    game_id: str = Field(min_length=2, max_length=64)
    score: int = Field(ge=0, le=999999)
    metadata: dict[str, Any] | None = None


class NinoJuegoProgresoOutV1(BaseModel):
    game_id: str
    best_score: int
    last_score: int
    plays: int
    puntos_ganados: int
    puntos_totales: int
    nuevo_record: bool


# --- Dashboard ---


class DashboardStatsOutV1(BaseModel):
    total_usuarios: int
    total_ninos: int
    total_evaluaciones: int
    total_citas_pendientes: int
    total_habitos_registrados_hoy: int
    total_alertas_criticas: int
