"""Dependencias legacy — compatibilidad con routers /api/*."""

from fastapi import Depends

from app.security.rate_limit import RateLimiter
from app.security.rbac import SecurityContext, get_security_context, require_roles as _require_roles
from models import Usuario

InMemoryRateLimiter = RateLimiter


def get_current_user(ctx: SecurityContext = Depends(get_security_context)) -> Usuario:
    return ctx.user


def require_roles(*roles: str):
    def _inner(ctx: SecurityContext = Depends(_require_roles(*roles))) -> Usuario:
        return ctx.user

    return _inner


__all__ = ["InMemoryRateLimiter", "get_current_user", "require_roles", "get_security_context", "SecurityContext"]
