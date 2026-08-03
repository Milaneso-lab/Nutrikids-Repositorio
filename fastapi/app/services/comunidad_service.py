from __future__ import annotations

from sqlalchemy.orm import Session

from app.core.exceptions import NotFoundError
from app.core.pagination import PaginationParams
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import (
    ComentarioCreateV1,
    ContactoCreateV1,
    DiscusionCreateV1,
    DiscusionUpdateV1,
)
from models import Comentario, Contacto, Discusion


class ComunidadService:
    def __init__(self, db: Session):
        self.db = db

    def create_contacto(self, payload: ContactoCreateV1) -> Contacto:
        row = Contacto(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def list_comentarios(self, params: PaginationParams):
        q = self.db.query(Comentario).order_by(Comentario.id_comentario.desc())
        return BaseRepository(self.db, Comentario).list_paginated(params, q)

    def create_comentario(self, payload: ComentarioCreateV1) -> Comentario:
        row = Comentario(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def list_discusiones(self, params: PaginationParams):
        q = self.db.query(Discusion).order_by(Discusion.id_discusion.desc())
        return BaseRepository(self.db, Discusion).list_paginated(params, q)

    def get_discusion(self, discusion_id: int) -> Discusion:
        row = self.db.query(Discusion).filter(Discusion.id_discusion == discusion_id).first()
        if not row:
            raise NotFoundError("Discusión no encontrada")
        return row

    def create_discusion(self, payload: DiscusionCreateV1) -> Discusion:
        row = Discusion(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def update_discusion(self, discusion_id: int, payload: DiscusionUpdateV1) -> Discusion:
        row = self.get_discusion(discusion_id)
        for key, value in payload.model_dump(exclude_unset=True).items():
            setattr(row, key, value)
        self.db.commit()
        self.db.refresh(row)
        return row

    def delete_discusion(self, discusion_id: int) -> None:
        row = self.get_discusion(discusion_id)
        self.db.delete(row)
        self.db.commit()
