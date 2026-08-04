"""Mensajes seguros para el usuario final (sin detalles técnicos del sistema)."""

from __future__ import annotations

import re
from typing import Any

GENERIC_ERROR = "No se pudo completar la acción. Inténtalo de nuevo."
GENERIC_SAVE_ERROR = "No se pudo guardar. Revisa los datos e inténtalo de nuevo."

_TECHNICAL = re.compile(
    r"HTTP|API|FastAPI|Flask|Laravel|Docker|SQL|SQLSTATE|Connection|migraci|"
    r"JSON|CSRF|token|Exception|Traceback|could not|proxy|NUTRIKIDS|"
    r"<html|502|503|504|404|500|422|23505|23503|23502|Unique violation|"
    r"foreign key|not-null|column .* does not exist",
    re.IGNORECASE,
)


def es_mensaje_usuario(mensaje: str | None) -> bool:
    if not mensaje:
        return False
    texto = str(mensaje).strip()
    if not texto or len(texto) > 500:
        return False
    return _TECHNICAL.search(texto) is None


def sanitizar_mensaje(mensaje: str | None, fallback: str = GENERIC_ERROR) -> str:
    if es_mensaje_usuario(mensaje):
        return str(mensaje).strip()
    return fallback


def mensaje_desde_api(body: dict[str, Any] | None, fallback: str = GENERIC_ERROR) -> str:
    if not isinstance(body, dict):
        return fallback

    error_block = body.get("error")
    if isinstance(error_block, dict):
        message = error_block.get("message")
        if message is not None:
            detalles = []
            for item in error_block.get("details") or []:
                if isinstance(item, dict) and item.get("issue"):
                    detalles.append(str(item["issue"]))
            if detalles:
                return sanitizar_mensaje("; ".join(detalles), sanitizar_mensaje(str(message), fallback))
            return sanitizar_mensaje(str(message), fallback)

    errors = body.get("errors")
    if isinstance(errors, list) and errors:
        partes = [str(e) for e in errors if es_mensaje_usuario(str(e))]
        if partes:
            return "; ".join(partes)

    detail = body.get("detail")
    if isinstance(detail, list):
        partes = []
        for item in detail:
            if isinstance(item, dict) and item.get("msg"):
                partes.append(str(item["msg"]))
            else:
                partes.append(str(item))
        candidato = "; ".join(partes) if partes else None
        return sanitizar_mensaje(candidato, fallback)

    if isinstance(detail, str):
        return sanitizar_mensaje(detail, fallback)

    message = body.get("message")
    if message is not None:
        return sanitizar_mensaje(str(message), fallback)

    return fallback
