"""Compatibilidad legacy — delegación al módulo de seguridad centralizado."""

from typing import Any

from app.security.crypto import create_access_token as _create_access_token
from app.security.crypto import hash_password, verify_password
from app.security.settings import security_settings as settings

__all__ = ["hash_password", "verify_password", "create_access_token", "settings"]


def create_access_token(subject: str, extra_claims: dict[str, Any] | None = None) -> str:
    rol = (extra_claims or {}).get("rol", "padre")
    token, _ = _create_access_token(int(subject), rol, extra_claims)
    return token
