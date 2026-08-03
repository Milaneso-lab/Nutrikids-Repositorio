from __future__ import annotations

from datetime import date, datetime, timezone

from sqlalchemy import or_
from sqlalchemy.orm import Session

from app.core.exceptions import NotFoundError, ValidationError
from app.core.pagination import PaginationParams
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import CitaAsignarV1, CitaCreateV1, CitaEstadoV1
from models import Cita, Usuario


class CitaService:
    def __init__(self, db: Session):
        self.db = db
        self.repo = BaseRepository(db, Cita)

    def list_citas(
        self,
        params: PaginationParams,
        id_padre: int | None = None,
        id_nutriologo: int | None = None,
        estado: str | None = None,
    ):
        q = self.db.query(Cita)
        if id_padre:
            q = q.filter(Cita.id_padre == id_padre)
        if id_nutriologo:
            q = q.filter(
                or_(
                    Cita.id_nutriologo == id_nutriologo,
                    (Cita.estado == "pendiente") & (Cita.id_nutriologo.is_(None)),
                )
            )
        if estado:
            q = q.filter(Cita.estado == estado)
        q = q.order_by(Cita.id.desc())
        return self.repo.list_paginated(params, q)

    def get_cita(self, cita_id: int) -> Cita:
        row = self.db.query(Cita).filter(Cita.id == cita_id).first()
        if not row:
            raise NotFoundError("Cita no encontrada")
        return row

    def create_cita(self, payload: CitaCreateV1) -> Cita:
        padre = self.db.query(Usuario).filter(Usuario.id_usuario == payload.id_padre).first()
        if not padre or padre.rol != "padre":
            raise ValidationError(
                "id_padre debe ser un usuario con rol padre",
                details=[{"field": "id_padre", "issue": "usuario inválido"}],
            )
        if payload.fecha_preferida < date.today():
            raise ValidationError(
                "La fecha preferida debe ser hoy o futura",
                details=[{"field": "fecha_preferida", "issue": "fecha pasada"}],
            )
        now = datetime.now(timezone.utc).replace(tzinfo=None)
        row = Cita(
            id_padre=payload.id_padre,
            nino_id=payload.nino_id,
            fecha_preferida=payload.fecha_preferida,
            franja=payload.franja,
            telefono=payload.telefono,
            mensaje=payload.mensaje,
            estado="pendiente",
            created_at=now,
            updated_at=now,
        )
        return self.repo.add(row)

    def asignar_cita(self, cita_id: int, payload: CitaAsignarV1) -> Cita:
        row = self.get_cita(cita_id)
        nut = self.db.query(Usuario).filter(Usuario.id_usuario == payload.id_nutriologo).first()
        if not nut or nut.rol != "nutriologo":
            raise ValidationError("Debe indicar un nutriólogo válido")
        row.id_nutriologo = payload.id_nutriologo
        row.estado = "asignada"
        row.updated_at = datetime.now(timezone.utc).replace(tzinfo=None)
        return self.repo.commit_refresh(row)

    def tomar_cita(self, cita_id: int, nutriologo_id: int) -> Cita:
        row = self.get_cita(cita_id)
        if row.estado != "pendiente" or row.id_nutriologo is not None:
            raise ValidationError("Esta cita ya no está disponible para tomar")
        nut = self.db.query(Usuario).filter(Usuario.id_usuario == nutriologo_id).first()
        if not nut or nut.rol != "nutriologo":
            raise ValidationError("Nutriólogo inválido")
        row.id_nutriologo = nutriologo_id
        row.estado = "asignada"
        row.updated_at = datetime.now(timezone.utc).replace(tzinfo=None)
        return self.repo.commit_refresh(row)

    def cambiar_estado(self, cita_id: int, payload: CitaEstadoV1) -> Cita:
        row = self.get_cita(cita_id)
        row.estado = payload.estado
        row.updated_at = datetime.now(timezone.utc).replace(tzinfo=None)
        return self.repo.commit_refresh(row)
