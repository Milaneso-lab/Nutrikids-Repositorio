"""Utilidades de seguridad: contraseñas, tokens JWT y refresh."""

from __future__ import annotations

import hashlib
import re
import secrets
import uuid
from datetime import datetime, timedelta, timezone
from typing import Any

import bcrypt
from jose import jwt

from app.security.settings import security_settings


def hash_password(password: str) -> str:
    rounds = max(12, security_settings.bcrypt_rounds)
    salt = bcrypt.gensalt(rounds=rounds)
    return bcrypt.hashpw(password.encode("utf-8"), salt).decode("utf-8")


def verify_password(plain_password: str, hashed_password: str) -> bool:
    try:
        return bcrypt.checkpw(
            plain_password.encode("utf-8"),
            hashed_password.encode("utf-8"),
        )
    except (ValueError, TypeError):
        return False


def validate_password_policy(password: str) -> list[str]:
    issues: list[str] = []
    if len(password) < security_settings.password_min_length:
        issues.append(f"mínimo {security_settings.password_min_length} caracteres")
    if security_settings.password_require_upper and not re.search(r"[A-Z]", password):
        issues.append("debe incluir al menos una mayúscula")
    if security_settings.password_require_lower and not re.search(r"[a-z]", password):
        issues.append("debe incluir al menos una minúscula")
    if security_settings.password_require_digit and not re.search(r"\d", password):
        issues.append("debe incluir al menos un dígito")
    return issues


def hash_token(raw_token: str) -> str:
    return hashlib.sha256(raw_token.encode("utf-8")).hexdigest()


def generate_refresh_token() -> str:
    return secrets.token_urlsafe(48)


def generate_reset_token() -> str:
    return secrets.token_urlsafe(32)


def generate_password_reset_code() -> str:
    """Código numérico de 6 dígitos para restablecer contraseña desde la app móvil."""
    return f"{secrets.randbelow(900_000) + 100_000:06d}"


def create_access_token(user_id: int, rol: str, extra: dict[str, Any] | None = None) -> tuple[str, str]:
    jti = str(uuid.uuid4())
    now = datetime.now(timezone.utc)
    expires = now + timedelta(minutes=security_settings.access_token_minutes)
    payload: dict[str, Any] = {
        "sub": str(user_id),
        "rol": rol,
        "jti": jti,
        "iat": now,
        "exp": expires,
    }
    if extra:
        payload.update(extra)
    token = jwt.encode(payload, security_settings.secret_key, algorithm=security_settings.algorithm)
    return token, jti


def decode_access_token(token: str) -> dict[str, Any]:
    return jwt.decode(
        token,
        security_settings.secret_key,
        algorithms=[security_settings.algorithm],
    )
