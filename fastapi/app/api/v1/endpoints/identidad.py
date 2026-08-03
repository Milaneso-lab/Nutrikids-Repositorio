from fastapi import APIRouter, Depends, Query, status

from app.api.deps import get_nino_service, get_pagination, get_usuario_service
from app.core.pagination import PaginatedResponse
from app.schemas.v1.schemas import (
    MessageOut,
    NinoCreateV1,
    NinoOutV1,
    NinoUpdateV1,
    UsuarioCreateV1,
    UsuarioOutV1,
    UsuarioUpdateV1,
    VincularDispositivoInV1,
    VincularDispositivoOutV1,
)
from app.security.rbac import SecurityContext, can_access_nino, require_permission
from app.services.nino_service import NinoService
from app.services.usuario_service import UsuarioService
from database import get_db
from sqlalchemy.orm import Session

router = APIRouter(tags=["v1 — usuarios y niños"])


@router.get("/usuarios", response_model=PaginatedResponse[UsuarioOutV1], summary="Listar usuarios")
def list_usuarios_v1(
    rol: str | None = Query(None),
    pagination=Depends(get_pagination),
    service: UsuarioService = Depends(get_usuario_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    items, total = service.list_usuarios(pagination, rol=rol)
    return PaginatedResponse.build([UsuarioOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/usuarios", response_model=UsuarioOutV1, status_code=status.HTTP_201_CREATED, summary="Crear usuario")
def create_usuario_v1(
    payload: UsuarioCreateV1,
    service: UsuarioService = Depends(get_usuario_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    return UsuarioOutV1.model_validate(service.create_usuario(payload))


@router.get("/usuarios/{user_id}", response_model=UsuarioOutV1, summary="Obtener usuario")
def get_usuario_v1(
    user_id: int,
    service: UsuarioService = Depends(get_usuario_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    return UsuarioOutV1.model_validate(service.get_usuario(user_id))


@router.put("/usuarios/{user_id}", response_model=UsuarioOutV1, summary="Actualizar usuario")
def update_usuario_v1(
    user_id: int,
    payload: UsuarioUpdateV1,
    service: UsuarioService = Depends(get_usuario_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    return UsuarioOutV1.model_validate(service.update_usuario(user_id, payload))


@router.delete(
    "/usuarios/{user_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Eliminar usuario",
)
def delete_usuario_v1(
    user_id: int,
    service: UsuarioService = Depends(get_usuario_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    service.delete_usuario(user_id, solicitante_id=ctx.user.id_usuario)
    return None


@router.get("/ninos", response_model=PaginatedResponse[NinoOutV1], summary="Listar niños")
def list_ninos_v1(
    padre_id: int | None = Query(None),
    nutriologo_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.leer")),
):
    if ctx.user.rol == "padre":
        padre_id = ctx.user.id_usuario
    elif ctx.user.rol == "nutriologo":
        nutriologo_id = ctx.user.id_usuario
    items, total = service.list_ninos(pagination, padre_id=padre_id, nutriologo_id=nutriologo_id)
    return PaginatedResponse.build([NinoOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/ninos", response_model=NinoOutV1, status_code=status.HTTP_201_CREATED, summary="Crear niño")
def create_nino_v1(
    payload: NinoCreateV1,
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.escribir")),
):
    if ctx.user.rol == "padre":
        payload = payload.model_copy(update={"padre_id": ctx.user.id_usuario})
    return NinoOutV1.model_validate(service.create_nino(payload))


@router.get("/ninos/{nino_id}", response_model=NinoOutV1, summary="Obtener niño")
def get_nino_v1(
    nino_id: int,
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.leer")),
    db: Session = Depends(get_db),
):
    can_access_nino(db, ctx, nino_id)
    return NinoOutV1.model_validate(service.get_nino(nino_id))


@router.put("/ninos/{nino_id}", response_model=NinoOutV1, summary="Actualizar niño")
def update_nino_v1(
    nino_id: int,
    payload: NinoUpdateV1,
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.escribir")),
    db: Session = Depends(get_db),
):
    can_access_nino(db, ctx, nino_id)
    return NinoOutV1.model_validate(service.update_nino(nino_id, payload))


@router.delete("/ninos/{nino_id}", response_model=MessageOut, summary="Eliminar niño (soft delete)")
def delete_nino_v1(
    nino_id: int,
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.escribir")),
    db: Session = Depends(get_db),
):
    can_access_nino(db, ctx, nino_id)
    service.soft_delete(nino_id)
    return MessageOut(message="Perfil del niño eliminado")


@router.post(
    "/ninos/{nino_id}/vincular-dispositivo",
    response_model=VincularDispositivoOutV1,
    summary="Generar código de vinculación móvil",
)
def vincular_dispositivo_v1(
    nino_id: int,
    payload: VincularDispositivoInV1,
    service: NinoService = Depends(get_nino_service),
    ctx: SecurityContext = Depends(require_permission("pacientes.escribir")),
    db: Session = Depends(get_db),
):
    can_access_nino(db, ctx, nino_id)
    nino = service.vincular_dispositivo(nino_id, payload.pin, payload.confirmar_pin)
    return VincularDispositivoOutV1(
        nino_id=nino.id,
        codigo_vinculacion=nino.codigo_vinculacion or "",
        pin_configurado=True,
    )
