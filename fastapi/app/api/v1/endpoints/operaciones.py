from fastapi import APIRouter, Depends, Query, status

from app.api.deps import get_cita_service, get_comunidad_service, get_dashboard_service, get_gamificacion_service, get_pagination
from app.core.pagination import PaginatedResponse
from app.schemas.v1.schemas import (
    CitaAsignarV1,
    CitaCreateV1,
    CitaEstadoV1,
    CitaOutV1,
    ComentarioCreateV1,
    ComentarioOutV1,
    ContactoCreateV1,
    ContactoOutV1,
    DashboardStatsOutV1,
    DiscusionCreateV1,
    DiscusionOutV1,
    DiscusionUpdateV1,
    HabitoCatalogoOutV1,
    HabitoRegistroCreateV1,
    HabitoRegistroOutV1,
    LogroCatalogoOutV1,
    MessageOut,
    NinoHabitoCreateV1,
    NinoHabitoOutV1,
    NinoLogroOutV1,
    NinoPuntosOutV1,
    NinoRecompensaOutV1,
    NinoRetoOutV1,
    RecompensaCatalogoOutV1,
    RetoCatalogoOutV1,
)
from app.services.cita_service import CitaService
from app.services.comunidad_service import ComunidadService
from app.services.dashboard_service import DashboardService
from app.services.gamificacion_service import GamificacionService
from app.security.rate_limit import contact_rate_limiter
from app.security.rbac import SecurityContext, get_security_context, require_permission

router = APIRouter(tags=["v1 — citas, comunidad, gamificación, dashboard"])


# --- Citas ---


@router.get("/citas", response_model=PaginatedResponse[CitaOutV1])
def list_citas_v1(
    id_padre: int | None = Query(None),
    id_nutriologo: int | None = Query(None),
    estado: str | None = Query(None),
    pagination=Depends(get_pagination),
    service: CitaService = Depends(get_cita_service),
    ctx: SecurityContext = Depends(require_permission("citas.leer")),
):
    if ctx.user.rol == "padre":
        id_padre = ctx.user.id_usuario
    elif ctx.user.rol == "nutriologo":
        id_nutriologo = ctx.user.id_usuario
    items, total = service.list_citas(pagination, id_padre, id_nutriologo, estado)
    return PaginatedResponse.build([CitaOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/citas", response_model=CitaOutV1, status_code=status.HTTP_201_CREATED)
def create_cita_v1(
    payload: CitaCreateV1,
    service: CitaService = Depends(get_cita_service),
    ctx: SecurityContext = Depends(require_permission("citas.agendar")),
):
    if ctx.user.rol == "padre":
        payload = payload.model_copy(update={"id_padre": ctx.user.id_usuario})
    return CitaOutV1.model_validate(service.create_cita(payload))


@router.patch("/citas/{cita_id}/asignar", response_model=CitaOutV1)
def asignar_cita_v1(
    cita_id: int,
    payload: CitaAsignarV1,
    service: CitaService = Depends(get_cita_service),
    ctx: SecurityContext = Depends(require_permission("citas.asignar")),
):
    return CitaOutV1.model_validate(service.asignar_cita(cita_id, payload))


@router.post("/citas/{cita_id}/tomar", response_model=CitaOutV1)
def tomar_cita_v1(
    cita_id: int,
    nutriologo_id: int | None = Query(None, description="ID del nutriólogo (default: usuario actual)"),
    service: CitaService = Depends(get_cita_service),
    ctx: SecurityContext = Depends(require_permission("citas.asignar")),
):
    nid = nutriologo_id or ctx.user.id_usuario
    return CitaOutV1.model_validate(service.tomar_cita(cita_id, nid))


@router.patch("/citas/{cita_id}/estado", response_model=CitaOutV1)
def cambiar_estado_cita_v1(
    cita_id: int,
    payload: CitaEstadoV1,
    service: CitaService = Depends(get_cita_service),
    ctx: SecurityContext = Depends(require_permission("citas.asignar")),
):
    return CitaOutV1.model_validate(service.cambiar_estado(cita_id, payload))


# --- Comunidad ---


@router.post("/contactos", response_model=ContactoOutV1, status_code=status.HTTP_201_CREATED, dependencies=[Depends(contact_rate_limiter)])
def create_contacto_v1(payload: ContactoCreateV1, service: ComunidadService = Depends(get_comunidad_service)):
    return ContactoOutV1.model_validate(service.create_contacto(payload))


@router.get("/comentarios", response_model=PaginatedResponse[ComentarioOutV1])
def list_comentarios_v1(
    pagination=Depends(get_pagination),
    service: ComunidadService = Depends(get_comunidad_service),
):
    items, total = service.list_comentarios(pagination)
    return PaginatedResponse.build([ComentarioOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/comentarios", response_model=ComentarioOutV1, status_code=status.HTTP_201_CREATED)
def create_comentario_v1(
    payload: ComentarioCreateV1,
    service: ComunidadService = Depends(get_comunidad_service),
    ctx: SecurityContext = Depends(get_security_context),
):
    return ComentarioOutV1.model_validate(service.create_comentario(payload))


@router.get("/discusiones", response_model=PaginatedResponse[DiscusionOutV1])
def list_discusiones_v1(
    pagination=Depends(get_pagination),
    service: ComunidadService = Depends(get_comunidad_service),
):
    items, total = service.list_discusiones(pagination)
    return PaginatedResponse.build([DiscusionOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/discusiones", response_model=DiscusionOutV1, status_code=status.HTTP_201_CREATED)
def create_discusion_v1(payload: DiscusionCreateV1, service: ComunidadService = Depends(get_comunidad_service)):
    return DiscusionOutV1.model_validate(service.create_discusion(payload))


@router.put("/discusiones/{discusion_id}", response_model=DiscusionOutV1)
def update_discusion_v1(
    discusion_id: int,
    payload: DiscusionUpdateV1,
    service: ComunidadService = Depends(get_comunidad_service),
):
    return DiscusionOutV1.model_validate(service.update_discusion(discusion_id, payload))


@router.delete("/discusiones/{discusion_id}", response_model=MessageOut)
def delete_discusion_v1(discusion_id: int, service: ComunidadService = Depends(get_comunidad_service)):
    service.delete_discusion(discusion_id)
    return MessageOut(message="Discusión eliminada")


# --- Gamificación ---


@router.get("/habitos-catalogo", response_model=PaginatedResponse[HabitoCatalogoOutV1])
def list_habitos_catalogo_v1(
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_habitos_catalogo(pagination)
    return PaginatedResponse.build([HabitoCatalogoOutV1.model_validate(i) for i in items], total, pagination)


@router.get("/ninos/{nino_id}/habitos", response_model=PaginatedResponse[NinoHabitoOutV1])
def list_nino_habitos_v1(
    nino_id: int,
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_nino_habitos(nino_id, pagination)
    return PaginatedResponse.build([NinoHabitoOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/ninos/{nino_id}/habitos", response_model=NinoHabitoOutV1, status_code=status.HTTP_201_CREATED)
def assign_habito_v1(
    nino_id: int,
    payload: NinoHabitoCreateV1,
    service: GamificacionService = Depends(get_gamificacion_service),
):
    return NinoHabitoOutV1.model_validate(service.assign_habito(nino_id, payload))


@router.get("/ninos/{nino_id}/habitos/registros", response_model=PaginatedResponse[HabitoRegistroOutV1])
def list_habito_registros_v1(
    nino_id: int,
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_habito_registros(nino_id, pagination)
    return PaginatedResponse.build([HabitoRegistroOutV1.model_validate(i) for i in items], total, pagination)


@router.post(
    "/ninos/{nino_id}/habitos/{nino_habito_id}/registrar",
    response_model=HabitoRegistroOutV1,
    status_code=status.HTTP_201_CREATED,
)
def registrar_habito_v1(
    nino_id: int,
    nino_habito_id: int,
    payload: HabitoRegistroCreateV1,
    service: GamificacionService = Depends(get_gamificacion_service),
):
    return HabitoRegistroOutV1.model_validate(service.registrar_habito(nino_id, nino_habito_id, payload))


@router.get("/retos-catalogo", response_model=PaginatedResponse[RetoCatalogoOutV1])
def list_retos_catalogo_v1(
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_retos_catalogo(pagination)
    return PaginatedResponse.build([RetoCatalogoOutV1.model_validate(i) for i in items], total, pagination)


@router.get("/ninos/{nino_id}/retos", response_model=PaginatedResponse[NinoRetoOutV1])
def list_nino_retos_v1(
    nino_id: int,
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_nino_retos(nino_id, pagination)
    return PaginatedResponse.build([NinoRetoOutV1.model_validate(i) for i in items], total, pagination)


@router.get("/logros-catalogo", response_model=PaginatedResponse[LogroCatalogoOutV1])
def list_logros_catalogo_v1(
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_logros_catalogo(pagination)
    return PaginatedResponse.build([LogroCatalogoOutV1.model_validate(i) for i in items], total, pagination)


@router.get("/ninos/{nino_id}/logros", response_model=PaginatedResponse[NinoLogroOutV1])
def list_nino_logros_v1(
    nino_id: int,
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_nino_logros(nino_id, pagination)
    return PaginatedResponse.build([NinoLogroOutV1.model_validate(i) for i in items], total, pagination)


@router.get("/ninos/{nino_id}/puntos", response_model=NinoPuntosOutV1)
def get_nino_puntos_v1(nino_id: int, service: GamificacionService = Depends(get_gamificacion_service)):
    return NinoPuntosOutV1.model_validate(service.get_nino_puntos(nino_id))


@router.get("/recompensas-catalogo", response_model=PaginatedResponse[RecompensaCatalogoOutV1])
def list_recompensas_catalogo_v1(
    pagination=Depends(get_pagination),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    items, total = service.list_recompensas_catalogo(pagination)
    return PaginatedResponse.build([RecompensaCatalogoOutV1.model_validate(i) for i in items], total, pagination)


@router.post(
    "/ninos/{nino_id}/recompensas/{recompensa_id}/canjear",
    response_model=NinoRecompensaOutV1,
    status_code=status.HTTP_201_CREATED,
)
def canjear_recompensa_v1(
    nino_id: int,
    recompensa_id: int,
    service: GamificacionService = Depends(get_gamificacion_service),
):
    return NinoRecompensaOutV1.model_validate(service.canjear_recompensa(nino_id, recompensa_id))


# --- Dashboard ---


@router.get("/dashboard/estadisticas", response_model=DashboardStatsOutV1, summary="Estadísticas agregadas")
def dashboard_stats_v1(
    service: DashboardService = Depends(get_dashboard_service),
    ctx: SecurityContext = Depends(require_permission("usuarios.administrar")),
):
    return service.get_stats()
