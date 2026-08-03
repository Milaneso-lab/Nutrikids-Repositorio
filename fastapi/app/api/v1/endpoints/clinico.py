from fastapi import APIRouter, Depends, Query, status

from app.api.deps import get_clinico_service, get_pagination
from app.core.pagination import PaginatedResponse
from app.schemas.v1.schemas import (
    AlertaCreateV1,
    AlertaOutV1,
    AlergiaCreateV1,
    AlergiaOutV1,
    EvaluacionCreateV1,
    EvaluacionOutV1,
    EvaluacionUpdateV1,
    MenuCreateV1,
    MenuItemCreateV1,
    MenuItemOutV1,
    MenuOutV1,
    MenuSemanalCreateV1,
    MenuSemanalOutV1,
    MenuUpdateV1,
    NotaNutriologoCreateV1,
    NotaNutriologoOutV1,
    ReporteCreateV1,
    ReporteOutV1,
    ReportePdfOutV1,
    ReporteUpdateV1,
)
from app.services.clinico_service import ClinicoService
from app.security.rbac import SecurityContext, get_security_context, require_permission

router = APIRouter(tags=["v1 — clínico"], dependencies=[Depends(get_security_context)])


@router.get("/evaluaciones", response_model=PaginatedResponse[EvaluacionOutV1])
def list_evaluaciones_v1(
    nino_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_evaluaciones(pagination, nino_id=nino_id)
    return PaginatedResponse.build([EvaluacionOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/evaluaciones", response_model=EvaluacionOutV1, status_code=status.HTTP_201_CREATED)
def create_evaluacion_v1(
    payload: EvaluacionCreateV1,
    service: ClinicoService = Depends(get_clinico_service),
    ctx: SecurityContext = Depends(require_permission("evaluaciones.escribir")),
):
    return EvaluacionOutV1.model_validate(service.create_evaluacion(payload))


@router.put("/evaluaciones/{eval_id}", response_model=EvaluacionOutV1)
def update_evaluacion_v1(
    eval_id: int,
    payload: EvaluacionUpdateV1,
    service: ClinicoService = Depends(get_clinico_service),
):
    return EvaluacionOutV1.model_validate(service.update_evaluacion(eval_id, payload))


@router.get("/alergias", response_model=PaginatedResponse[AlergiaOutV1])
def list_alergias_v1(
    nino_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_alergias(pagination, nino_id=nino_id)
    return PaginatedResponse.build([AlergiaOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/alergias", response_model=AlergiaOutV1, status_code=status.HTTP_201_CREATED)
def create_alergia_v1(payload: AlergiaCreateV1, service: ClinicoService = Depends(get_clinico_service)):
    return AlergiaOutV1.model_validate(service.create_alergia(payload))


@router.put("/alergias/{alergia_id}", response_model=AlergiaOutV1)
def update_alergia_v1(
    alergia_id: int,
    payload: AlergiaCreateV1,
    service: ClinicoService = Depends(get_clinico_service),
):
    return AlergiaOutV1.model_validate(service.update_alergia(alergia_id, payload))


@router.get("/alertas", response_model=PaginatedResponse[AlertaOutV1])
def list_alertas_v1(
    atendida: bool | None = Query(None),
    nino_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_alertas(pagination, atendida=atendida, nino_id=nino_id)
    return PaginatedResponse.build([AlertaOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/alertas", response_model=AlertaOutV1, status_code=status.HTTP_201_CREATED)
def create_alerta_v1(payload: AlertaCreateV1, service: ClinicoService = Depends(get_clinico_service)):
    return AlertaOutV1.model_validate(service.create_alerta(payload))


@router.post("/alertas/{alerta_id}/atender", response_model=AlertaOutV1)
def atender_alerta_v1(
    alerta_id: int,
    atendida_por_id: int | None = Query(None),
    service: ClinicoService = Depends(get_clinico_service),
):
    return AlertaOutV1.model_validate(service.atender_alerta(alerta_id, atendida_por_id))


@router.get("/notas-nutriologo", response_model=PaginatedResponse[NotaNutriologoOutV1])
def list_notas_v1(
    nino_id: int | None = Query(None),
    incluir_privadas: bool = Query(True),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_notas(pagination, nino_id=nino_id, incluir_privadas=incluir_privadas)
    return PaginatedResponse.build([NotaNutriologoOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/notas-nutriologo", response_model=NotaNutriologoOutV1, status_code=status.HTTP_201_CREATED)
def create_nota_v1(payload: NotaNutriologoCreateV1, service: ClinicoService = Depends(get_clinico_service)):
    return NotaNutriologoOutV1.model_validate(service.create_nota(payload))


@router.get("/menus", response_model=PaginatedResponse[MenuOutV1])
def list_menus_v1(
    nino_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_menus(pagination, nino_id=nino_id)
    return PaginatedResponse.build([MenuOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/menus", response_model=MenuOutV1, status_code=status.HTTP_201_CREATED)
def create_menu_v1(payload: MenuCreateV1, service: ClinicoService = Depends(get_clinico_service)):
    return MenuOutV1.model_validate(service.create_menu(payload))


@router.put("/menus/{menu_id}", response_model=MenuOutV1)
def update_menu_v1(menu_id: int, payload: MenuUpdateV1, service: ClinicoService = Depends(get_clinico_service)):
    return MenuOutV1.model_validate(service.update_menu(menu_id, payload))


@router.get("/menus/{menu_id}/items", response_model=list[MenuItemOutV1])
def list_menu_items_v1(menu_id: int, service: ClinicoService = Depends(get_clinico_service)):
    return [MenuItemOutV1.model_validate(i) for i in service.list_menu_items(menu_id)]


@router.post("/menus/{menu_id}/items", response_model=MenuItemOutV1, status_code=status.HTTP_201_CREATED)
def add_menu_item_v1(
    menu_id: int,
    payload: MenuItemCreateV1,
    service: ClinicoService = Depends(get_clinico_service),
):
    return MenuItemOutV1.model_validate(service.add_menu_item(menu_id, payload))


@router.get("/menus-semanales", response_model=PaginatedResponse[MenuSemanalOutV1])
def list_menus_semanales_v1(
    publico: bool | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_menus_semanales(pagination, publico=publico)
    return PaginatedResponse.build([MenuSemanalOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/menus-semanales", response_model=MenuSemanalOutV1, status_code=status.HTTP_201_CREATED)
def create_menu_semanal_v1(
    payload: MenuSemanalCreateV1,
    service: ClinicoService = Depends(get_clinico_service),
):
    return MenuSemanalOutV1.model_validate(service.create_menu_semanal(payload))


@router.get("/reportes", response_model=PaginatedResponse[ReporteOutV1])
def list_reportes_v1(
    nino_id: int | None = Query(None),
    pagination=Depends(get_pagination),
    service: ClinicoService = Depends(get_clinico_service),
):
    items, total = service.list_reportes(pagination, nino_id=nino_id)
    return PaginatedResponse.build([ReporteOutV1.model_validate(i) for i in items], total, pagination)


@router.post("/reportes", response_model=ReporteOutV1, status_code=status.HTTP_201_CREATED)
def create_reporte_v1(payload: ReporteCreateV1, service: ClinicoService = Depends(get_clinico_service)):
    return ReporteOutV1.model_validate(service.create_reporte(payload))


@router.put("/reportes/{reporte_id}", response_model=ReporteOutV1)
def update_reporte_v1(
    reporte_id: int,
    payload: ReporteUpdateV1,
    service: ClinicoService = Depends(get_clinico_service),
):
    return ReporteOutV1.model_validate(service.update_reporte(reporte_id, payload))


@router.get("/reportes/{reporte_id}/pdf", response_model=ReportePdfOutV1)
def get_reporte_pdf_v1(reporte_id: int, service: ClinicoService = Depends(get_clinico_service)):
    reporte = service.get_reporte(reporte_id)
    data = ReporteOutV1.model_validate(reporte)
    return ReportePdfOutV1(
        reporte_id=reporte_id,
        mensaje="PDF generado en Laravel/dompdf — este endpoint expone los datos para renderizado",
        datos=data,
    )
