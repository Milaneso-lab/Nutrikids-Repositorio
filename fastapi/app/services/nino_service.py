from __future__ import annotations

from datetime import datetime, timezone

from sqlalchemy.orm import Session

from app.core.exceptions import ConflictError, NotFoundError, ValidationError
from app.core.pagination import PaginationParams
from app.domain.utils import generar_codigo_vinculacion, validar_edad_nino
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import NinoCreateV1, NinoUpdateV1
from app.security.crypto import hash_password
from app.services.nino_auth_service import validate_nino_pin
from models import Nino, NinoCredenciales, RefreshToken, Usuario


class NinoService:
    def __init__(self, db: Session):
        self.db = db
        self.repo = BaseRepository(db, Nino)

    def list_ninos(
        self,
        params: PaginationParams,
        padre_id: int | None = None,
        nutriologo_id: int | None = None,
    ):
        q = self.db.query(Nino).filter(Nino.deleted_at.is_(None))
        if padre_id is not None:
            q = q.filter(Nino.padre_id == padre_id)
        if nutriologo_id is not None:
            q = q.filter(Nino.nutriologo_asignado_id == nutriologo_id)
        q = q.order_by(Nino.id.desc())
        return self.repo.list_paginated(params, q)

    def get_nino(self, nino_id: int) -> Nino:
        nino = (
            self.db.query(Nino)
            .filter(Nino.id == nino_id, Nino.deleted_at.is_(None))
            .first()
        )
        if not nino:
            raise NotFoundError("Niño no encontrado")
        return nino

    def create_nino(self, payload: NinoCreateV1) -> Nino:
        if not self.db.query(Usuario).filter(Usuario.id_usuario == payload.padre_id).first():
            raise ValidationError("padre_id no existe", details=[{"field": "padre_id", "issue": "usuario no encontrado"}])
        try:
            validar_edad_nino(payload.fecha_nacimiento)
        except ValueError as exc:
            raise ValidationError(str(exc), details=[{"field": "fecha_nacimiento", "issue": str(exc)}]) from exc
        if payload.nutriologo_asignado_id:
            nut = self.db.query(Usuario).filter(Usuario.id_usuario == payload.nutriologo_asignado_id).first()
            if not nut or nut.rol != "nutriologo":
                raise ValidationError(
                    "nutriologo_asignado_id inválido",
                    details=[{"field": "nutriologo_asignado_id", "issue": "debe ser un nutriólogo existente"}],
                )
        nino = Nino(**payload.model_dump())
        return self.repo.add(nino)

    def update_nino(self, nino_id: int, payload: NinoUpdateV1) -> Nino:
        nino = self.get_nino(nino_id)
        data = payload.model_dump(exclude_unset=True)
        if "fecha_nacimiento" in data and data["fecha_nacimiento"]:
            try:
                validar_edad_nino(data["fecha_nacimiento"])
            except ValueError as exc:
                raise ValidationError(str(exc)) from exc
        for key, value in data.items():
            setattr(nino, key, value)
        return self.repo.commit_refresh(nino)

    def vincular_dispositivo(self, nino_id: int, pin: str, confirmar_pin: str) -> Nino:
        if pin != confirmar_pin:
            raise ValidationError(
                "Los PIN no coinciden",
                details=[{"field": "confirmar_pin", "issue": "debe coincidir con el PIN"}],
            )

        pin_issues = validate_nino_pin(pin)
        if pin_issues:
            raise ValidationError(
                "PIN no válido",
                details=[{"field": "pin", "issue": issue} for issue in pin_issues],
            )

        nino = self.get_nino(nino_id)
        now = datetime.now(timezone.utc)

        self.db.query(RefreshToken).filter(
            RefreshToken.nino_id == nino_id,
            RefreshToken.revocado_en.is_(None),
        ).update({RefreshToken.revocado_en: now}, synchronize_session=False)

        if nino.codigo_vinculacion:
            codigo_asignado = nino.codigo_vinculacion
        else:
            codigo_asignado = None
            for _ in range(5):
                codigo = generar_codigo_vinculacion()
                exists = (
                    self.db.query(Nino)
                    .filter(Nino.codigo_vinculacion == codigo, Nino.id != nino_id)
                    .first()
                )
                if not exists:
                    codigo_asignado = codigo
                    break

            if not codigo_asignado:
                raise ConflictError("No se pudo generar un código de vinculación único")

            nino.codigo_vinculacion = codigo_asignado
        nino.requiere_vinculacion_padre = False

        cred = self.db.query(NinoCredenciales).filter(NinoCredenciales.nino_id == nino_id).first()
        if cred:
            cred.pin_hash = hash_password(pin)
            cred.dispositivo_id = None
            cred.vinculado_en = None
        else:
            self.db.add(
                NinoCredenciales(
                    nino_id=nino_id,
                    pin_hash=hash_password(pin),
                )
            )

        return self.repo.commit_refresh(nino)

    def soft_delete(self, nino_id: int) -> Nino:
        nino = self.get_nino(nino_id)
        nino.deleted_at = datetime.now(timezone.utc)
        return self.repo.commit_refresh(nino)
