from datetime import date, datetime
from typing import Literal

from pydantic import BaseModel, EmailStr, Field


class LoginIn(BaseModel):
    email: EmailStr
    contrasena: str


class TokenOut(BaseModel):
    access_token: str
    token_type: str = "bearer"
    rol: str
    id_usuario: int
    nombre: str | None = None
    apellido_paterno: str | None = None
    email: str | None = None


class UsuarioIn(BaseModel):
    nombre: str
    apellido_paterno: str
    apellido_materno: str | None = None
    email: EmailStr
    contrasena: str
    rol: str = "padre"


class UsuarioOut(BaseModel):
    id_usuario: int
    nombre: str
    apellido_paterno: str
    apellido_materno: str | None
    email: EmailStr
    rol: str

    model_config = {"from_attributes": True}


class ContactoIn(BaseModel):
    nombre: str
    apellido: str
    email: EmailStr
    mensaje: str


class ContactoOut(ContactoIn):
    id_contacto: int
    fecha_creacion: datetime

    model_config = {"from_attributes": True}


class ComentarioIn(BaseModel):
    nombre: str
    apellido: str
    comentario: str
    id_usuario: int | None = None
    id_comentario_padre: int | None = None


class ComentarioOut(ComentarioIn):
    id_comentario: int
    fecha_comentario: datetime
    respuestas: list["ComentarioOut"] = []

    model_config = {"from_attributes": True}


class RespuestaDiscusionIn(BaseModel):
    mensaje: str


class RespuestaDiscusionOut(RespuestaDiscusionIn):
    id_respuesta: int
    id_discusion: int
    id_usuario: int
    nombre: str
    apellido: str
    fecha_creacion: datetime

    model_config = {"from_attributes": True}


class DiscusionIn(BaseModel):
    tema: str
    descripcion: str
    id_usuario: int | None = None


class DiscusionOut(DiscusionIn):
    id_discusion: int
    fecha_creacion: datetime
    respuestas: list[RespuestaDiscusionOut] = []

    model_config = {"from_attributes": True}


class PacienteIn(BaseModel):
    nombre: str | None = None
    apellidos: str | None = None
    fecha_nacimiento: datetime | None = None


class PacienteOut(PacienteIn):
    id: int

    model_config = {"from_attributes": True}


class EvaluacionIn(BaseModel):
    paciente_id: int | None = None
    peso: str | None = None
    talla: str | None = None
    recomendaciones: str | None = None


class EvaluacionOut(EvaluacionIn):
    id: int

    model_config = {"from_attributes": True}


class MenuIn(BaseModel):
    nombre: str | None = None
    paciente_id: int | None = None
    descripcion: str | None = None


class MenuOut(MenuIn):
    id: int

    model_config = {"from_attributes": True}


class ReporteIn(BaseModel):
    titulo: str | None = None
    paciente_id: int | None = None
    contenido: str | None = None


class ReporteOut(ReporteIn):
    id: int

    model_config = {"from_attributes": True}


class CitaCreateIn(BaseModel):
    fecha_preferida: date
    franja: Literal["manana", "tarde"] = "manana"
    telefono: str | None = Field(None, max_length=30)
    mensaje: str | None = Field(None, max_length=2000)


class CitaOut(BaseModel):
    id: int
    id_padre: int
    id_nutriologo: int | None
    fecha_preferida: date
    franja: str
    telefono: str | None
    mensaje: str | None
    estado: str
    created_at: datetime
    updated_at: datetime
    padre_nombre: str | None = None
    nutriologo_nombre: str | None = None

    model_config = {"from_attributes": True}


class CitaAsignarIn(BaseModel):
    id_nutriologo: int


class CitaEstadoIn(BaseModel):
    estado: Literal["pendiente", "asignada", "confirmada", "cancelada"]


ComentarioOut.model_rebuild()
