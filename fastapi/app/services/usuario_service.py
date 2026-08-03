from __future__ import annotations

from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.core.exceptions import ConflictError, NotFoundError, ValidationError
from app.core.pagination import PaginationParams
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import UsuarioCreateV1, UsuarioUpdateV1
from models import Usuario
from security import hash_password


class UsuarioService:
    def __init__(self, db: Session):
        self.db = db
        self.repo = BaseRepository(db, Usuario)

    def list_usuarios(self, params: PaginationParams, rol: str | None = None):
        q = self.db.query(Usuario)
        if rol:
            q = q.filter(Usuario.rol == rol)
        q = q.order_by(Usuario.id_usuario.desc())
        return self.repo.list_paginated(params, q)

    def get_usuario(self, user_id: int) -> Usuario:
        user = self.repo.get_by_id(user_id)
        if not user:
            raise NotFoundError("Usuario no encontrado")
        return user

    def create_usuario(self, payload: UsuarioCreateV1) -> Usuario:
        if self.db.query(Usuario).filter(Usuario.email == payload.email).first():
            raise ConflictError("Email ya registrado")
        user = Usuario(
            nombre=payload.nombre,
            apellido_paterno=payload.apellido_paterno,
            apellido_materno=payload.apellido_materno,
            email=payload.email,
            contrasena=hash_password(payload.contrasena),
            rol=payload.rol,
            telefono=payload.telefono,
            estado="activo",
        )
        return self.repo.add(user)

    def update_usuario(self, user_id: int, payload: UsuarioUpdateV1) -> Usuario:
        user = self.get_usuario(user_id)
        data = payload.model_dump(exclude_unset=True)
        for key, value in data.items():
            setattr(user, key, value)
        return self.repo.commit_refresh(user)

    def delete_usuario(self, user_id: int, solicitante_id: int | None = None) -> None:
        user = self.get_usuario(user_id)

        if solicitante_id is not None and user.id_usuario == solicitante_id:
            raise ValidationError(
                "No puedes eliminar tu propio usuario",
                details=[{"field": "user_id", "issue": "autoeliminación no permitida"}],
            )

        try:
            self.repo.delete(user)
        except IntegrityError as exc:
            self.db.rollback()
            raise ConflictError(
                "El usuario tiene información clínica asociada y no puede eliminarse"
            ) from exc

    def ensure_usuario_exists(self, user_id: int, rol: str | None = None) -> Usuario:
        user = self.get_usuario(user_id)
        if rol and user.rol != rol:
            raise ValidationError(
                f"El usuario {user_id} no tiene rol {rol}",
                details=[{"field": "usuario_id", "issue": f"rol esperado: {rol}"}],
            )
        return user
