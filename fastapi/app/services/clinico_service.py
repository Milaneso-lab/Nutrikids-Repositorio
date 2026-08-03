from __future__ import annotations

from datetime import date, datetime, timezone

from sqlalchemy.orm import Session

from app.core.exceptions import NotFoundError, ValidationError
from app.core.pagination import PaginationParams
from app.domain.utils import calcular_imc
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import (
    AlertaCreateV1,
    AlergiaCreateV1,
    EvaluacionCreateV1,
    EvaluacionUpdateV1,
    MenuCreateV1,
    MenuItemCreateV1,
    MenuSemanalCreateV1,
    MenuUpdateV1,
    NotaNutriologoCreateV1,
    ReporteCreateV1,
    ReporteUpdateV1,
)
from models import (
    Alerta,
    Alergia,
    Evaluacion,
    Menu,
    MenuItem,
    MenuSemanal,
    Nino,
    NotaNutriologo,
    Reporte,
)


class ClinicoService:
    def __init__(self, db: Session):
        self.db = db

    def _ensure_nino(self, nino_id: int) -> Nino:
        nino = self.db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
        if not nino:
            raise NotFoundError("Niño no encontrado")
        return nino

    # --- Evaluaciones ---

    def list_evaluaciones(self, params: PaginationParams, nino_id: int | None = None):
        q = self.db.query(Evaluacion)
        if nino_id:
            q = q.filter(Evaluacion.nino_id == nino_id)
        q = q.order_by(Evaluacion.fecha_evaluacion.desc(), Evaluacion.id.desc())
        repo = BaseRepository(self.db, Evaluacion)
        return repo.list_paginated(params, q)

    def get_evaluacion(self, eval_id: int) -> Evaluacion:
        row = self.db.query(Evaluacion).filter(Evaluacion.id == eval_id).first()
        if not row:
            raise NotFoundError("Evaluación no encontrada")
        return row

    def create_evaluacion(self, payload: EvaluacionCreateV1) -> Evaluacion:
        nino = self._ensure_nino(payload.nino_id)
        imc = calcular_imc(payload.peso_kg, payload.talla_cm)
        row = Evaluacion(
            nino_id=payload.nino_id,
            paciente_id=payload.nino_id,
            nutriologo_id=payload.nutriologo_id,
            peso_kg=payload.peso_kg,
            talla_cm=payload.talla_cm,
            peso=str(payload.peso_kg),
            talla=str(payload.talla_cm),
            imc=imc,
            percentil_oms=payload.percentil_oms,
            recomendaciones=payload.recomendaciones,
            fecha_evaluacion=payload.fecha_evaluacion or date.today(),
        )
        self.db.add(row)
        nino.peso_actual_kg = payload.peso_kg
        nino.talla_actual_cm = payload.talla_cm
        self.db.commit()
        self.db.refresh(row)
        return row

    def update_evaluacion(self, eval_id: int, payload: EvaluacionUpdateV1) -> Evaluacion:
        row = self.get_evaluacion(eval_id)
        data = payload.model_dump(exclude_unset=True)
        if "peso_kg" in data:
            row.peso_kg = data["peso_kg"]
            row.peso = str(data["peso_kg"])
        if "talla_cm" in data:
            row.talla_cm = data["talla_cm"]
            row.talla = str(data["talla_cm"])
        for key in ("percentil_oms", "recomendaciones", "fecha_evaluacion"):
            if key in data:
                setattr(row, key, data[key])
        if row.peso_kg and row.talla_cm:
            row.imc = calcular_imc(row.peso_kg, row.talla_cm)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Alergias ---

    def list_alergias(self, params: PaginationParams, nino_id: int | None = None):
        q = self.db.query(Alergia)
        if nino_id:
            q = q.filter(Alergia.nino_id == nino_id)
        q = q.order_by(Alergia.id.desc())
        return BaseRepository(self.db, Alergia).list_paginated(params, q)

    def create_alergia(self, payload: AlergiaCreateV1) -> Alergia:
        self._ensure_nino(payload.nino_id)
        row = Alergia(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def update_alergia(self, alergia_id: int, payload: AlergiaCreateV1) -> Alergia:
        row = self.db.query(Alergia).filter(Alergia.id == alergia_id).first()
        if not row:
            raise NotFoundError("Alergia no encontrada")
        for key, value in payload.model_dump().items():
            setattr(row, key, value)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Alertas ---

    def list_alertas(self, params: PaginationParams, atendida: bool | None = None, nino_id: int | None = None):
        q = self.db.query(Alerta)
        if atendida is not None:
            q = q.filter(Alerta.atendida == atendida)
        if nino_id:
            q = q.filter(Alerta.nino_id == nino_id)
        q = q.order_by(Alerta.id.desc())
        return BaseRepository(self.db, Alerta).list_paginated(params, q)

    def create_alerta(self, payload: AlertaCreateV1) -> Alerta:
        if payload.nino_id:
            self._ensure_nino(payload.nino_id)
        row = Alerta(**payload.model_dump(), atendida=False)
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def atender_alerta(self, alerta_id: int, atendida_por_id: int | None = None) -> Alerta:
        row = self.db.query(Alerta).filter(Alerta.id == alerta_id).first()
        if not row:
            raise NotFoundError("Alerta no encontrada")
        row.atendida = True
        row.atendida_por_id = atendida_por_id
        row.atendida_en = datetime.now(timezone.utc)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Notas nutriólogo ---

    def list_notas(self, params: PaginationParams, nino_id: int | None = None, incluir_privadas: bool = True):
        q = self.db.query(NotaNutriologo)
        if nino_id:
            q = q.filter(NotaNutriologo.nino_id == nino_id)
        if not incluir_privadas:
            q = q.filter(NotaNutriologo.privada.is_(False))
        q = q.order_by(NotaNutriologo.id.desc())
        return BaseRepository(self.db, NotaNutriologo).list_paginated(params, q)

    def create_nota(self, payload: NotaNutriologoCreateV1) -> NotaNutriologo:
        self._ensure_nino(payload.nino_id)
        row = NotaNutriologo(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Menús ---

    def list_menus(self, params: PaginationParams, nino_id: int | None = None):
        q = self.db.query(Menu)
        if nino_id:
            q = q.filter(Menu.nino_id == nino_id)
        q = q.order_by(Menu.id.desc())
        return BaseRepository(self.db, Menu).list_paginated(params, q)

    def get_menu(self, menu_id: int) -> Menu:
        row = self.db.query(Menu).filter(Menu.id == menu_id).first()
        if not row:
            raise NotFoundError("Menú no encontrado")
        return row

    def create_menu(self, payload: MenuCreateV1) -> Menu:
        self._ensure_nino(payload.nino_id)
        if payload.fecha_inicio and payload.fecha_fin and payload.fecha_fin < payload.fecha_inicio:
            raise ValidationError(
                "fecha_fin no puede ser anterior a fecha_inicio",
                details=[{"field": "fecha_fin", "issue": "rango de fechas inválido"}],
            )
        row = Menu(**payload.model_dump(), paciente_id=payload.nino_id)
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def update_menu(self, menu_id: int, payload: MenuUpdateV1) -> Menu:
        row = self.get_menu(menu_id)
        for key, value in payload.model_dump(exclude_unset=True).items():
            setattr(row, key, value)
        self.db.commit()
        self.db.refresh(row)
        return row

    def list_menu_items(self, menu_id: int):
        self.get_menu(menu_id)
        return self.db.query(MenuItem).filter(MenuItem.menu_id == menu_id).order_by(MenuItem.id).all()

    def add_menu_item(self, menu_id: int, payload: MenuItemCreateV1) -> MenuItem:
        self.get_menu(menu_id)
        row = MenuItem(menu_id=menu_id, **payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Menús semanales ---

    def list_menus_semanales(self, params: PaginationParams, publico: bool | None = None):
        q = self.db.query(MenuSemanal)
        if publico is not None:
            q = q.filter(MenuSemanal.publico == publico)
        q = q.order_by(MenuSemanal.id.desc())
        return BaseRepository(self.db, MenuSemanal).list_paginated(params, q)

    def create_menu_semanal(self, payload: MenuSemanalCreateV1) -> MenuSemanal:
        row = MenuSemanal(**payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Reportes ---

    def list_reportes(self, params: PaginationParams, nino_id: int | None = None):
        q = self.db.query(Reporte)
        if nino_id:
            q = q.filter(Reporte.nino_id == nino_id)
        q = q.order_by(Reporte.id.desc())
        return BaseRepository(self.db, Reporte).list_paginated(params, q)

    def get_reporte(self, reporte_id: int) -> Reporte:
        row = self.db.query(Reporte).filter(Reporte.id == reporte_id).first()
        if not row:
            raise NotFoundError("Reporte no encontrado")
        return row

    def create_reporte(self, payload: ReporteCreateV1) -> Reporte:
        self._ensure_nino(payload.nino_id)
        row = Reporte(**payload.model_dump(), paciente_id=payload.nino_id)
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def update_reporte(self, reporte_id: int, payload: ReporteUpdateV1) -> Reporte:
        row = self.get_reporte(reporte_id)
        for key, value in payload.model_dump(exclude_unset=True).items():
            setattr(row, key, value)
        self.db.commit()
        self.db.refresh(row)
        return row
