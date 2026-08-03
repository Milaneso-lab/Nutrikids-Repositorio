from __future__ import annotations

from datetime import date

from sqlalchemy.orm import Session

from app.schemas.v1.schemas import DashboardStatsOutV1
from models import Alerta, Cita, Evaluacion, HabitoRegistro, Nino, Usuario


class DashboardService:
    def __init__(self, db: Session):
        self.db = db

    def get_stats(self) -> DashboardStatsOutV1:
        hoy = date.today()
        return DashboardStatsOutV1(
            total_usuarios=self.db.query(Usuario).count(),
            total_ninos=self.db.query(Nino).filter(Nino.deleted_at.is_(None)).count(),
            total_evaluaciones=self.db.query(Evaluacion).count(),
            total_citas_pendientes=self.db.query(Cita).filter(Cita.estado == "pendiente").count(),
            total_habitos_registrados_hoy=self.db.query(HabitoRegistro)
            .filter(HabitoRegistro.fecha == hoy, HabitoRegistro.completado.is_(True))
            .count(),
            total_alertas_criticas=self.db.query(Alerta)
            .filter(Alerta.atendida.is_(False), Alerta.severidad == "critica")
            .count(),
        )
