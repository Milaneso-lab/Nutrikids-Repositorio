"""RBAC y control de acceso por fila (05_Seguridad.md §2)."""

from __future__ import annotations

from dataclasses import dataclass, field

from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from jose import JWTError
from sqlalchemy.orm import Session

from app.infrastructure.persistence.entities import Nino, Permiso, RefreshToken, Role, RolPermiso
from app.security.audit import log_security_event
from app.security.crypto import decode_access_token
from app.security.settings import security_settings
from database import get_db
from models import Usuario

bearer_scheme = HTTPBearer(auto_error=False)

# Denylist JWT (logout) — memoria; con Redis se extiende en fase de despliegue
_jti_denylist: set[str] = set()


def revoke_access_jti(jti: str) -> None:
    _jti_denylist.add(jti)
    if security_settings.redis_url:
        try:
            import redis

            r = redis.from_url(security_settings.redis_url, decode_responses=True)
            ttl = security_settings.access_token_minutes * 60
            r.setex(f"jwt:deny:{jti}", ttl, "1")
        except Exception:
            pass


def is_jti_revoked(jti: str) -> bool:
    if jti in _jti_denylist:
        return True
    if security_settings.redis_url:
        try:
            import redis

            r = redis.from_url(security_settings.redis_url, decode_responses=True)
            return r.exists(f"jwt:deny:{jti}") == 1
        except Exception:
            pass
    return False


@dataclass
class SecurityContext:
    user: Usuario
    permissions: set[str] = field(default_factory=set)
    jti: str = ""


@dataclass
class NinoSecurityContext:
    nino_id: int
    jti: str = ""


def _load_permissions(db: Session, user: Usuario) -> set[str]:
    if user.rol_id:
        rows = (
            db.query(Permiso.clave)
            .join(RolPermiso, RolPermiso.permiso_id == Permiso.id)
            .filter(RolPermiso.rol_id == user.rol_id)
            .all()
        )
        if rows:
            return {r[0] for r in rows}
    role = db.query(Role).filter(Role.nombre == user.rol).first()
    if role:
        rows = (
            db.query(Permiso.clave)
            .join(RolPermiso, RolPermiso.permiso_id == Permiso.id)
            .filter(RolPermiso.rol_id == role.id)
            .all()
        )
        return {r[0] for r in rows}
    return set()


def get_security_context(
    credentials: HTTPAuthorizationCredentials | None = Depends(bearer_scheme),
    db: Session = Depends(get_db),
) -> SecurityContext:
    if not credentials:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token requerido")
    try:
        payload = decode_access_token(credentials.credentials)
        jti = payload.get("jti", "")
        if is_jti_revoked(jti):
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token revocado")
        user_id = int(payload["sub"])
    except (JWTError, TypeError, ValueError, KeyError):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido")

    user = db.query(Usuario).filter(Usuario.id_usuario == user_id).first()
    if not user:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Usuario no encontrado")
    if user.estado == "suspendido":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Cuenta suspendida")

    perms = _load_permissions(db, user)
    return SecurityContext(user=user, permissions=perms, jti=jti)


def get_nino_security_context(
    credentials: HTTPAuthorizationCredentials | None = Depends(bearer_scheme),
    db: Session = Depends(get_db),
) -> NinoSecurityContext:
    if not credentials:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token requerido")
    try:
        payload = decode_access_token(credentials.credentials)
        jti = payload.get("jti", "")
        if is_jti_revoked(jti):
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token revocado")
        if payload.get("rol") != "nino":
            raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Token de niño requerido")
        nino_id = int(payload.get("nino_id") or payload["sub"])
    except (JWTError, TypeError, ValueError, KeyError):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido")

    nino = db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
    if not nino:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Perfil de niño no encontrado")

    return NinoSecurityContext(nino_id=nino_id, jti=jti)


def require_permission(*permisos: str):
    def _checker(ctx: SecurityContext = Depends(get_security_context)) -> SecurityContext:
        if ctx.user.rol == "admin":
            return ctx
        if not any(p in ctx.permissions for p in permisos):
            raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Permiso denegado")
        return ctx

    return _checker


def require_roles(*roles: str):
    def _checker(ctx: SecurityContext = Depends(get_security_context)) -> SecurityContext:
        if ctx.user.rol not in roles:
            raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Rol no autorizado")
        return ctx

    return _checker


def can_access_nino(db: Session, ctx: SecurityContext, nino_id: int) -> Nino:
    nino = db.query(Nino).filter(Nino.id == nino_id, Nino.deleted_at.is_(None)).first()
    if not nino:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Niño no encontrado")
    if ctx.user.rol == "admin":
        return nino
    if ctx.user.rol == "padre" and nino.padre_id == ctx.user.id_usuario:
        return nino
    if ctx.user.rol == "nutriologo" and nino.nutriologo_asignado_id == ctx.user.id_usuario:
        return nino
    log_security_event(db, "access_denied_nino", ctx.user.id_usuario, f"ninos/{nino_id}")
    raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Niño no encontrado")
