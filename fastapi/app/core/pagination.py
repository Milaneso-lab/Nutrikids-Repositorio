import math
from typing import Generic, TypeVar

from pydantic import BaseModel, Field

T = TypeVar("T")


class PaginationParams(BaseModel):
    page: int = Field(1, ge=1, description="Número de página (1-indexed)")
    per_page: int = Field(20, ge=1, le=100, description="Elementos por página")

    @property
    def offset(self) -> int:
        return (self.page - 1) * self.per_page


class PageMeta(BaseModel):
    page: int
    per_page: int
    total: int
    total_pages: int


class PaginatedResponse(BaseModel, Generic[T]):
    data: list[T]
    meta: PageMeta

    @classmethod
    def build(cls, items: list[T], total: int, params: PaginationParams) -> "PaginatedResponse[T]":
        total_pages = max(1, math.ceil(total / params.per_page)) if total else 0
        return cls(
            data=items,
            meta=PageMeta(
                page=params.page,
                per_page=params.per_page,
                total=total,
                total_pages=total_pages if total else 0,
            ),
        )
