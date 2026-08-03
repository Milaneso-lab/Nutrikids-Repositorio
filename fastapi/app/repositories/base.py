"""Repositorio base genérico."""

from __future__ import annotations

from typing import Generic, TypeVar

from sqlalchemy.orm import Query, Session

from app.core.pagination import PaginationParams

T = TypeVar("T")


class BaseRepository(Generic[T]):
    def __init__(self, db: Session, model: type[T]):
        self.db = db
        self.model = model

    def _base_query(self) -> Query[T]:
        return self.db.query(self.model)

    def get_by_id(self, entity_id: int) -> T | None:
        pk = getattr(self.model, "id", None) or getattr(self.model, "id_usuario", None)
        if pk is None:
            raise AttributeError(f"Modelo {self.model.__name__} sin columna id")
        col = pk.property.columns[0]
        return self._base_query().filter(col == entity_id).first()

    def list_paginated(self, params: PaginationParams, query: Query[T] | None = None) -> tuple[list[T], int]:
        q = query if query is not None else self._base_query()
        total = q.count()
        items = q.offset(params.offset).limit(params.per_page).all()
        return items, total

    def add(self, entity: T) -> T:
        self.db.add(entity)
        self.db.commit()
        self.db.refresh(entity)
        return entity

    def delete(self, entity: T) -> None:
        self.db.delete(entity)
        self.db.commit()

    def flush(self) -> None:
        self.db.flush()

    def commit_refresh(self, entity: T) -> T:
        self.db.commit()
        self.db.refresh(entity)
        return entity
