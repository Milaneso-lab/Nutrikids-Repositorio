from __future__ import annotations

from datetime import date, datetime, timezone

from sqlalchemy.orm import Session
from sqlalchemy.orm.attributes import flag_modified

from app.core.exceptions import ConflictError, NotFoundError, ValidationError
from app.core.pagination import PaginationParams
from app.repositories.base import BaseRepository
from app.schemas.v1.schemas import HabitoRegistroCreateV1, NinoHabitoCreateV1
from models import (
    HabitoCatalogo,
    HabitoRegistro,
    LogroCatalogo,
    Nino,
    NinoHabito,
    NinoLogro,
    NinoPuntos,
    NinoRecompensa,
    NinoReto,
    RecompensaCatalogo,
    RetoCatalogo,
)


class GamificacionService:
    def __init__(self, db: Session):
        self.db = db

    def _ensure_nino(self, nino_id: int) -> Nino:
        nino = self.db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
        if not nino:
            raise NotFoundError("Niño no encontrado")
        return nino

    def _get_or_create_puntos(self, nino_id: int) -> NinoPuntos:
        puntos = self.db.query(NinoPuntos).filter(NinoPuntos.nino_id == nino_id).first()
        if not puntos:
            puntos = NinoPuntos(nino_id=nino_id, puntos_totales=0, nivel_actual=1)
            self.db.add(puntos)
            self.db.flush()
        return puntos

    # --- Catálogos ---

    def list_habitos_catalogo(self, params: PaginationParams, activo: bool | None = True):
        q = self.db.query(HabitoCatalogo)
        if activo is not None:
            q = q.filter(HabitoCatalogo.activo == activo)
        q = q.order_by(HabitoCatalogo.id)
        return BaseRepository(self.db, HabitoCatalogo).list_paginated(params, q)

    def list_retos_catalogo(self, params: PaginationParams, activo: bool | None = True):
        q = self.db.query(RetoCatalogo)
        if activo is not None:
            q = q.filter(RetoCatalogo.activo == activo)
        q = q.order_by(RetoCatalogo.id)
        return BaseRepository(self.db, RetoCatalogo).list_paginated(params, q)

    def list_logros_catalogo(self, params: PaginationParams):
        q = self.db.query(LogroCatalogo).order_by(LogroCatalogo.id)
        return BaseRepository(self.db, LogroCatalogo).list_paginated(params, q)

    def list_recompensas_catalogo(self, params: PaginationParams, activo: bool | None = True):
        q = self.db.query(RecompensaCatalogo)
        if activo is not None:
            q = q.filter(RecompensaCatalogo.activo == activo)
        q = q.order_by(RecompensaCatalogo.id)
        return BaseRepository(self.db, RecompensaCatalogo).list_paginated(params, q)

    # --- Hábitos por niño ---

    def list_nino_habitos(self, nino_id: int, params: PaginationParams):
        self._ensure_nino(nino_id)
        q = self.db.query(NinoHabito).filter(NinoHabito.nino_id == nino_id, NinoHabito.activo.is_(True))
        return BaseRepository(self.db, NinoHabito).list_paginated(params, q)

    def list_habito_registros(self, nino_id: int, params: PaginationParams):
        self._ensure_nino(nino_id)
        q = (
            self.db.query(HabitoRegistro)
            .join(NinoHabito, HabitoRegistro.nino_habito_id == NinoHabito.id)
            .filter(NinoHabito.nino_id == nino_id)
            .order_by(HabitoRegistro.fecha.desc(), HabitoRegistro.id.desc())
        )
        return BaseRepository(self.db, HabitoRegistro).list_paginated(params, q)

    def assign_habito(self, nino_id: int, payload: NinoHabitoCreateV1) -> NinoHabito:
        self._ensure_nino(nino_id)
        habito = self.db.query(HabitoCatalogo).filter(HabitoCatalogo.id == payload.habito_id).first()
        if not habito or not habito.activo:
            raise NotFoundError("Hábito de catálogo no encontrado o inactivo")
        row = NinoHabito(nino_id=nino_id, **payload.model_dump())
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    def registrar_habito(self, nino_id: int, nino_habito_id: int, payload: HabitoRegistroCreateV1) -> HabitoRegistro:
        self._ensure_nino(nino_id)
        nh = (
            self.db.query(NinoHabito)
            .filter(NinoHabito.id == nino_habito_id, NinoHabito.nino_id == nino_id)
            .first()
        )
        if not nh:
            raise NotFoundError("Hábito asignado no encontrado para este niño")
        fecha = payload.fecha or date.today()
        existing = (
            self.db.query(HabitoRegistro)
            .filter(HabitoRegistro.nino_habito_id == nino_habito_id, HabitoRegistro.fecha == fecha)
            .first()
        )
        if existing:
            existing.completado = payload.completado
            self.db.commit()
            self.db.refresh(existing)
            registro = existing
        else:
            registro = HabitoRegistro(
                nino_habito_id=nino_habito_id,
                fecha=fecha,
                completado=payload.completado,
            )
            self.db.add(registro)
            self.db.commit()
            self.db.refresh(registro)

        if payload.completado:
            habito = self.db.query(HabitoCatalogo).filter(HabitoCatalogo.id == nh.habito_id).first()
            puntos = self._get_or_create_puntos(nino_id)
            puntos.puntos_totales += habito.puntos_base if habito else 0
            puntos.nivel_actual = max(1, puntos.puntos_totales // 100 + 1)
            puntos.actualizado_en = datetime.now(timezone.utc)
            self.db.commit()

        return registro

    # --- Retos / logros / puntos / recompensas ---

    def list_nino_retos(self, nino_id: int, params: PaginationParams):
        self._ensure_nino(nino_id)
        q = self.db.query(NinoReto).filter(NinoReto.nino_id == nino_id)
        return BaseRepository(self.db, NinoReto).list_paginated(params, q)

    def list_nino_logros(self, nino_id: int, params: PaginationParams):
        self._ensure_nino(nino_id)
        q = self.db.query(NinoLogro).filter(NinoLogro.nino_id == nino_id)
        return BaseRepository(self.db, NinoLogro).list_paginated(params, q)

    def get_nino_puntos(self, nino_id: int) -> NinoPuntos:
        self._ensure_nino(nino_id)
        return self._get_or_create_puntos(nino_id)

    def canjear_recompensa(self, nino_id: int, recompensa_id: int) -> NinoRecompensa:
        self._ensure_nino(nino_id)
        recompensa = (
            self.db.query(RecompensaCatalogo)
            .filter(RecompensaCatalogo.id == recompensa_id, RecompensaCatalogo.activo.is_(True))
            .first()
        )
        if not recompensa:
            raise NotFoundError("Recompensa no encontrada")
        if recompensa.stock is not None and recompensa.stock <= 0:
            raise ValidationError("Recompensa sin stock disponible")
        puntos = self._get_or_create_puntos(nino_id)
        if puntos.puntos_totales < recompensa.costo_puntos:
            raise ValidationError(
                "Puntos insuficientes para canjear",
                details=[{"field": "puntos_totales", "issue": f"requiere {recompensa.costo_puntos}"}],
            )
        puntos.puntos_totales -= recompensa.costo_puntos
        if recompensa.stock is not None:
            recompensa.stock -= 1
        row = NinoRecompensa(nino_id=nino_id, recompensa_id=recompensa_id, estado="pendiente")
        self.db.add(row)
        self.db.commit()
        self.db.refresh(row)
        return row

    # --- Minijuegos (retos tipo especial) ---

    def list_juegos_nino(self, nino_id: int) -> list[dict]:
        self._ensure_nino(nino_id)
        retos = (
            self.db.query(RetoCatalogo)
            .filter(RetoCatalogo.activo.is_(True), RetoCatalogo.condicion["kind"].astext == "minigame")
            .order_by(RetoCatalogo.id)
            .all()
        )
        progresos = {
            row.reto_id: row for row in self.db.query(NinoReto).filter(NinoReto.nino_id == nino_id).all()
        }
        items: list[dict] = []
        for reto in retos:
            condicion = reto.condicion or {}
            game_id = str(condicion.get("game_id") or "")
            if not game_id:
                continue
            row = progresos.get(reto.id)
            progreso = row.progreso if row and row.progreso else {}
            items.append(
                {
                    "game_id": game_id,
                    "reto_id": reto.id,
                    "nombre": reto.nombre,
                    "descripcion": reto.descripcion,
                    "emoji": str(condicion.get("emoji") or "🎮"),
                    "puntos_recompensa": reto.puntos_recompensa,
                    "best_score": int(progreso.get("best_score") or 0),
                    "last_score": int(progreso.get("last_score") or 0),
                    "plays": int(progreso.get("plays") or 0),
                }
            )
        return items

    def guardar_progreso_juego(self, nino_id: int, game_id: str, score: int, metadata: dict | None = None) -> dict:
        self._ensure_nino(nino_id)
        reto = (
            self.db.query(RetoCatalogo)
            .filter(
                RetoCatalogo.activo.is_(True),
                RetoCatalogo.condicion["kind"].astext == "minigame",
                RetoCatalogo.condicion["game_id"].astext == game_id,
            )
            .first()
        )
        if not reto:
            raise NotFoundError("Juego no encontrado")

        nino_reto = (
            self.db.query(NinoReto)
            .filter(NinoReto.nino_id == nino_id, NinoReto.reto_id == reto.id)
            .first()
        )
        prev = nino_reto.progreso if nino_reto and nino_reto.progreso else {}
        prev_best = int(prev.get("best_score") or 0)
        nuevo_record = score > prev_best
        best_score = max(prev_best, score)
        plays = int(prev.get("plays") or 0) + 1
        now = datetime.now(timezone.utc)

        progreso = {
            "game_id": game_id,
            "best_score": best_score,
            "last_score": score,
            "plays": plays,
            "updated_at": now.isoformat(),
            **(metadata or {}),
        }

        if nino_reto:
            nino_reto.progreso = progreso
            flag_modified(nino_reto, "progreso")
            if not nino_reto.completado and best_score > 0:
                nino_reto.completado = True
                nino_reto.completado_en = now
        else:
            nino_reto = NinoReto(
                nino_id=nino_id,
                reto_id=reto.id,
                progreso=progreso,
                completado=best_score > 0,
                completado_en=now if best_score > 0 else None,
            )
            self.db.add(nino_reto)

        puntos_ganados = 0
        if score > 0:
            puntos_ganados = 3
            if nuevo_record:
                puntos_ganados += max(2, min(reto.puntos_recompensa, score // 10 + 2))
            puntos = self._get_or_create_puntos(nino_id)
            puntos.puntos_totales += puntos_ganados
            puntos.nivel_actual = max(1, puntos.puntos_totales // 100 + 1)
            puntos.actualizado_en = now

        self.db.commit()
        puntos_row = self._get_or_create_puntos(nino_id)
        return {
            "game_id": game_id,
            "best_score": best_score,
            "last_score": score,
            "plays": plays,
            "puntos_ganados": puntos_ganados,
            "puntos_totales": puntos_row.puntos_totales,
            "nuevo_record": nuevo_record,
        }
