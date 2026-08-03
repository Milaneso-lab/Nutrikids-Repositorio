from fastapi import Depends, Query
from sqlalchemy.orm import Session

from app.core.pagination import PaginationParams
from app.services.clinico_service import ClinicoService
from app.services.cita_service import CitaService
from app.services.comunidad_service import ComunidadService
from app.services.dashboard_service import DashboardService
from app.services.gamificacion_service import GamificacionService
from app.services.nino_service import NinoService
from app.services.usuario_service import UsuarioService
from database import get_db


def get_pagination(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
) -> PaginationParams:
    return PaginationParams(page=page, per_page=per_page)


def get_usuario_service(db: Session = Depends(get_db)) -> UsuarioService:
    return UsuarioService(db)


def get_nino_service(db: Session = Depends(get_db)) -> NinoService:
    return NinoService(db)


def get_clinico_service(db: Session = Depends(get_db)) -> ClinicoService:
    return ClinicoService(db)


def get_cita_service(db: Session = Depends(get_db)) -> CitaService:
    return CitaService(db)


def get_comunidad_service(db: Session = Depends(get_db)) -> ComunidadService:
    return ComunidadService(db)


def get_gamificacion_service(db: Session = Depends(get_db)) -> GamificacionService:
    return GamificacionService(db)


def get_dashboard_service(db: Session = Depends(get_db)) -> DashboardService:
    return DashboardService(db)
