"""Autenticación del niño: código de vinculación + PIN."""

from __future__ import annotations

import re
from datetime import datetime, timedelta, timezone

from sqlalchemy.orm import Session

from app.core.exceptions import NotFoundError, ValidationError
from app.infrastructure.persistence.entities import Nino, NinoCredenciales, NinoPuntos, RefreshToken
from app.security.audit import log_security_event
from app.security.crypto import (
    create_access_token,
    generate_refresh_token,
    hash_token,
    verify_password,
)
from app.security.settings import security_settings


def validate_nino_pin(pin: str) -> list[str]:
    if not re.fullmatch(r"\d{4,6}", pin or ""):
        return ["El PIN debe tener entre 4 y 6 dígitos numéricos"]
    return []


class NinoAuthService:
    def __init__(self, db: Session):
        self.db = db

    def _find_nino_by_codigo(self, codigo: str) -> Nino:
        normalized = codigo.strip().upper()
        nino = (
            self.db.query(Nino)
            .filter(Nino.codigo_vinculacion == normalized, Nino.deleted_at.is_(None))
            .first()
        )
        if not nino:
            raise ValidationError("Código de vinculación inválido")
        return nino

    def _build_profile_payload(self, nino: Nino) -> dict:
        puntos = self.db.query(NinoPuntos).filter(NinoPuntos.nino_id == nino.id).first()
        avatar = nino.avatar_config or {}
        return {
            "nino_id": nino.id,
            "nombre": nino.nombre,
            "apellidos": nino.apellidos,
            "fecha_nacimiento": nino.fecha_nacimiento,
            "sexo": nino.sexo,
            "avatar_config": nino.avatar_config,
            "nivel_actual": puntos.nivel_actual if puntos else 1,
            "puntos_totales": puntos.puntos_totales if puntos else 0,
            "companion": avatar.get("companion"),
        }

    def _issue_tokens(self, nino: Nino, dispositivo: str | None) -> dict:
        access, _ = create_access_token(nino.id, "nino", extra={"nino_id": nino.id, "tipo_sesion": "nino"})
        refresh_raw = generate_refresh_token()
        refresh_row = RefreshToken(
            usuario_id=None,
            nino_id=nino.id,
            token_hash=hash_token(refresh_raw),
            dispositivo=dispositivo,
            expira_en=datetime.now(timezone.utc) + timedelta(days=security_settings.refresh_token_days_mobile),
        )
        self.db.add(refresh_row)
        self.db.commit()
        return {
            "access_token": access,
            "refresh_token": refresh_raw,
            "token_type": "bearer",
            "expires_in": security_settings.access_token_minutes * 60,
        }

    def acceso(
        self,
        codigo_vinculacion: str,
        pin: str,
        dispositivo: str | None = None,
        ip: str | None = None,
    ) -> dict:
        pin_issues = validate_nino_pin(pin)
        if pin_issues:
            raise ValidationError("PIN no válido", details=[{"field": "pin", "issue": i} for i in pin_issues])

        nino = self._find_nino_by_codigo(codigo_vinculacion)
        cred = self.db.query(NinoCredenciales).filter(NinoCredenciales.nino_id == nino.id).first()

        if cred is None:
            raise ValidationError(
                "Tu papá o mamá debe configurar tu acceso primero",
                details=[{"field": "codigo_vinculacion", "issue": "acceso no configurado por el padre"}],
            )

        if not verify_password(pin, cred.pin_hash):
            log_security_event(self.db, "nino_login_failed", nino.padre_id, f"ninos/{nino.id}/acceso", ip)
            raise ValidationError("PIN incorrecto")

        cred.dispositivo_id = dispositivo or cred.dispositivo_id
        cred.vinculado_en = datetime.now(timezone.utc)
        log_security_event(self.db, "nino_login_success", nino.padre_id, f"ninos/{nino.id}/acceso", ip)

        self.db.commit()
        tokens = self._issue_tokens(nino, dispositivo)
        return {
            "requiere_configurar_pin": False,
            **tokens,
            **self._build_profile_payload(nino),
        }

    def get_me(self, nino_id: int) -> dict:
        nino = self.db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
        if not nino:
            raise NotFoundError("Niño no encontrado")
        return self._build_profile_payload(nino)

    def update_profile(
        self,
        nino_id: int,
        *,
        avatar_config: dict | None = None,
        nombre: str | None = None,
        apellidos: str | None = None,
    ) -> dict:
        nino = self.db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
        if not nino:
            raise NotFoundError("Niño no encontrado")
        changed = False
        if nombre is not None:
            nino.nombre = nombre.strip()
            changed = True
        if apellidos is not None:
            nino.apellidos = apellidos.strip()
            changed = True
        if avatar_config is not None:
            nino.avatar_config = avatar_config
            changed = True
        if changed:
            self.db.commit()
            self.db.refresh(nino)
        return self._build_profile_payload(nino)

    def refresh(self, refresh_token: str, ip: str | None = None) -> dict:
        token_hash = hash_token(refresh_token)
        row = self.db.query(RefreshToken).filter(RefreshToken.token_hash == token_hash).first()
        if not row or row.nino_id is None:
            raise ValidationError("Refresh token inválido")

        now = datetime.now(timezone.utc)
        if row.revocado_en is not None:
            raise ValidationError("Sesión comprometida — inicie sesión de nuevo")

        exp = row.expira_en
        if exp.tzinfo is None:
            exp = exp.replace(tzinfo=timezone.utc)
        if exp < now:
            raise ValidationError("Refresh token expirado")

        nino = self.db.query(Nino).filter(Nino.id == row.nino_id, Nino.deleted_at.is_(None)).first()
        if not nino:
            raise ValidationError("Perfil de niño no encontrado")

        row.revocado_en = now
        refresh_raw = generate_refresh_token()
        new_row = RefreshToken(
            usuario_id=None,
            nino_id=nino.id,
            token_hash=hash_token(refresh_raw),
            dispositivo=row.dispositivo,
            expira_en=now + timedelta(days=security_settings.refresh_token_days_mobile),
        )
        self.db.add(new_row)
        access, _ = create_access_token(nino.id, "nino", extra={"nino_id": nino.id, "tipo_sesion": "nino"})
        self.db.commit()

        log_security_event(self.db, "nino_token_refreshed", nino.padre_id, "auth/nino/refresh", ip)
        return {
            "access_token": access,
            "refresh_token": refresh_raw,
            "token_type": "bearer",
            "expires_in": security_settings.access_token_minutes * 60,
        }

    def logout(self, refresh_token: str, access_jti: str | None = None, ip: str | None = None) -> None:
        from app.security.rbac import revoke_access_jti

        if access_jti:
            revoke_access_jti(access_jti)
        token_hash = hash_token(refresh_token)
        row = self.db.query(RefreshToken).filter(RefreshToken.token_hash == token_hash).first()
        if row:
            row.revocado_en = datetime.now(timezone.utc)
            self.db.commit()
            if row.nino_id:
                nino = self.db.query(Nino).filter(Nino.id == row.nino_id).first()
                if nino:
                    log_security_event(self.db, "nino_logout", nino.padre_id, "auth/nino/logout", ip)
