"""Auditoría de seguridad — sin registrar datos sensibles."""

from __future__ import annotations

import logging
from typing import Any

from sqlalchemy.orm import Session

from app.infrastructure.persistence.entities import SecurityAuditLog
from app.security.settings import security_settings

logger = logging.getLogger("nutrikids.security")

SENSITIVE_KEYS = {"contrasena", "password", "token", "refresh_token", "pin", "pin_hash", "secret"}


def _scrub(details: dict[str, Any] | None) -> dict[str, Any] | None:
    if not details:
        return None
    return {k: ("***" if k.lower() in SENSITIVE_KEYS else v) for k, v in details.items()}


def log_security_event(
    db: Session,
    accion: str,
    usuario_id: int | None = None,
    recurso: str | None = None,
    ip_address: str | None = None,
    detalles: dict[str, Any] | None = None,
) -> None:
    safe_details = _scrub(detalles)
    logger.info(
        "security_event action=%s user_id=%s resource=%s ip=%s",
        accion,
        usuario_id,
        recurso,
        ip_address,
    )
    if not security_settings.audit_log_enabled:
        return
    try:
        row = SecurityAuditLog(
            usuario_id=usuario_id,
            accion=accion,
            recurso=recurso,
            ip_address=ip_address,
            detalles=safe_details,
        )
        db.add(row)
        db.commit()
    except Exception:
        db.rollback()
        logger.exception("No se pudo persistir audit log")
