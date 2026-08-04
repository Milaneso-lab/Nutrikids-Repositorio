"""Servicio de autenticación: login, refresh, logout, registro, reset."""

from __future__ import annotations

import logging
from datetime import datetime, timedelta, timezone

from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.core.exceptions import ConflictError, NotFoundError, ValidationError
from app.infrastructure.mail import send_password_reset_email
from app.infrastructure.persistence.entities import LoginAttempt, PasswordHistory, RefreshToken
from app.security.audit import log_security_event
from app.security.crypto import (
    create_access_token,
    generate_password_reset_code,
    generate_refresh_token,
    hash_password,
    hash_token,
    validate_password_policy,
    verify_password,
)
from app.security.rbac import revoke_access_jti
from app.security.settings import security_settings
from models import PasswordResetToken, Usuario

logger = logging.getLogger("nutrikids.auth")


class AuthService:
    def __init__(self, db: Session):
        self.db = db

    def _is_locked_out(self, email: str) -> bool:
        since = datetime.now(timezone.utc) - timedelta(minutes=security_settings.login_lockout_minutes)
        fails = (
            self.db.query(LoginAttempt)
            .filter(
                LoginAttempt.email == email,
                LoginAttempt.exito.is_(False),
                LoginAttempt.created_at >= since,
            )
            .count()
        )
        return fails >= security_settings.login_max_attempts

    def _record_login_attempt(self, email: str, ip: str | None, success: bool) -> None:
        self.db.add(LoginAttempt(email=email, ip_address=ip, exito=success))
        self.db.commit()

    def login(
        self,
        email: str,
        contrasena: str,
        ip: str | None = None,
        dispositivo: str | None = None,
        mobile: bool = False,
    ) -> dict:
        if self._is_locked_out(email):
            log_security_event(self.db, "login_locked", recurso=email, ip_address=ip)
            raise ValidationError("Cuenta temporalmente bloqueada por intentos fallidos")

        email = email.strip().lower()
        user = self.db.query(Usuario).filter(Usuario.email == email).first()
        if not user or not verify_password(contrasena, user.contrasena):
            self._record_login_attempt(email, ip, False)
            log_security_event(self.db, "login_failed", recurso=email, ip_address=ip)
            raise ValidationError("Credenciales inválidas")

        estado = (user.estado or "activo").strip()
        if estado == "suspendido":
            log_security_event(self.db, "login_blocked_suspended", user.id_usuario, "auth/login", ip)
            raise ValidationError("Tu cuenta está suspendida. Contacta al administrador.")
        if estado == "pendiente_verificacion" and user.rol == "padre":
            user.estado = "activo"
            user.email_verificado_en = datetime.now(timezone.utc)

        self._record_login_attempt(email, ip, True)
        user.ultimo_login_en = datetime.now(timezone.utc)
        access, jti = create_access_token(user.id_usuario, user.rol)
        refresh_raw = generate_refresh_token()
        days = security_settings.refresh_token_days_mobile if mobile else security_settings.refresh_token_days_web
        refresh_row = RefreshToken(
            usuario_id=user.id_usuario,
            token_hash=hash_token(refresh_raw),
            dispositivo=dispositivo,
            expira_en=datetime.now(timezone.utc) + timedelta(days=days),
        )
        self.db.add(refresh_row)
        self.db.commit()

        log_security_event(self.db, "login_success", user.id_usuario, "auth/login", ip)
        return {
            "access_token": access,
            "refresh_token": refresh_raw,
            "token_type": "bearer",
            "expires_in": security_settings.access_token_minutes * 60,
            "rol": user.rol,
            "id_usuario": user.id_usuario,
            "nombre": user.nombre,
            "apellido_paterno": user.apellido_paterno,
            "email": user.email,
        }

    def refresh(self, refresh_token: str, ip: str | None = None) -> dict:
        token_hash = hash_token(refresh_token)
        row = (
            self.db.query(RefreshToken)
            .filter(RefreshToken.token_hash == token_hash)
            .first()
        )
        if not row:
            raise ValidationError("Refresh token inválido")

        now = datetime.now(timezone.utc)
        if row.revocado_en is not None:
            self._revoke_all_user_tokens(row.usuario_id)
            log_security_event(
                self.db,
                "refresh_reuse_detected",
                row.usuario_id,
                "auth/refresh",
                ip,
            )
            raise ValidationError("Sesión comprometida — inicie sesión de nuevo")

        exp = row.expira_en
        if exp.tzinfo is None:
            exp = exp.replace(tzinfo=timezone.utc)
        if exp < now:
            raise ValidationError("Refresh token expirado")

        user = self.db.query(Usuario).filter(Usuario.id_usuario == row.usuario_id).first()
        if not user:
            raise ValidationError("Usuario no encontrado")

        row.revocado_en = now
        refresh_raw = generate_refresh_token()
        days = (
            security_settings.refresh_token_days_mobile
            if row.dispositivo and "mobile" in (row.dispositivo or "").lower()
            else security_settings.refresh_token_days_web
        )
        new_row = RefreshToken(
            usuario_id=user.id_usuario,
            token_hash=hash_token(refresh_raw),
            dispositivo=row.dispositivo,
            expira_en=now + timedelta(days=days),
        )
        self.db.add(new_row)
        access, _ = create_access_token(user.id_usuario, user.rol)
        self.db.commit()

        log_security_event(self.db, "token_refreshed", user.id_usuario, "auth/refresh", ip)
        return {
            "access_token": access,
            "refresh_token": refresh_raw,
            "token_type": "bearer",
            "expires_in": security_settings.access_token_minutes * 60,
        }

    def logout(self, refresh_token: str, access_jti: str | None = None, user_id: int | None = None, ip: str | None = None) -> None:
        if access_jti:
            revoke_access_jti(access_jti)
        token_hash = hash_token(refresh_token)
        row = self.db.query(RefreshToken).filter(RefreshToken.token_hash == token_hash).first()
        if row:
            row.revocado_en = datetime.now(timezone.utc)
            user_id = row.usuario_id
            self.db.commit()
        log_security_event(self.db, "logout", user_id, "auth/logout", ip)

    def _revoke_all_user_tokens(self, usuario_id: int) -> None:
        now = datetime.now(timezone.utc)
        self.db.query(RefreshToken).filter(
            RefreshToken.usuario_id == usuario_id,
            RefreshToken.revocado_en.is_(None),
        ).update({RefreshToken.revocado_en: now})
        self.db.commit()

    def register_padre(self, data: dict, ip: str | None = None) -> Usuario:
        data = {**data, "email": str(data["email"]).strip().lower()}
        issues = validate_password_policy(data["contrasena"])
        if issues:
            raise ValidationError("Contraseña no cumple la política", details=[{"field": "contrasena", "issue": i} for i in issues])
        if self.db.query(Usuario).filter(Usuario.email == data["email"]).first():
            raise ConflictError("Email ya registrado")
        user = Usuario(
            nombre=data["nombre"],
            apellido_paterno=data["apellido_paterno"],
            apellido_materno=data.get("apellido_materno"),
            email=data["email"],
            contrasena=hash_password(data["contrasena"]),
            rol="padre",
            estado="activo",
        )
        self.db.add(user)
        try:
            self.db.commit()
        except IntegrityError:
            self.db.rollback()
            raise ConflictError("Email ya registrado")
        self.db.refresh(user)
        log_security_event(self.db, "register_padre", user.id_usuario, "auth/register", ip)
        return user

    def request_password_reset(self, email: str, ip: str | None = None) -> None:
        user = self.db.query(Usuario).filter(Usuario.email == email).first()
        if not user:
            return
        raw = generate_password_reset_code()
        token_h = hash_token(raw)
        row = self.db.query(PasswordResetToken).filter(PasswordResetToken.email == email).first()
        if row:
            row.token = token_h
            row.created_at = datetime.now(timezone.utc)
        else:
            self.db.add(PasswordResetToken(email=email, token=token_h, created_at=datetime.now(timezone.utc)))
        self.db.commit()
        log_security_event(self.db, "password_reset_requested", user.id_usuario, "auth/password/forgot", ip)
        try:
            send_password_reset_email(
                to_email=user.email,
                recipient_name=user.nombre,
                code=raw,
                expires_minutes=security_settings.password_reset_minutes,
            )
        except Exception:
            logger.exception("No se pudo enviar el correo de restablecimiento a %s", user.email)

    def reset_password(self, email: str, token: str, new_password: str, ip: str | None = None) -> None:
        issues = validate_password_policy(new_password)
        if issues:
            raise ValidationError("Contraseña no cumple la política")
        row = self.db.query(PasswordResetToken).filter(PasswordResetToken.email == email).first()
        if not row or row.token != hash_token(token):
            raise ValidationError("Token de restablecimiento inválido")
        created = row.created_at
        if created and created.replace(tzinfo=timezone.utc) + timedelta(minutes=security_settings.password_reset_minutes) < datetime.now(timezone.utc):
            raise ValidationError("Token expirado")
        user = self.db.query(Usuario).filter(Usuario.email == email).first()
        if not user:
            raise ValidationError("Usuario no encontrado")
        new_hash = hash_password(new_password)
        history = self.db.query(PasswordHistory).filter(PasswordHistory.usuario_id == user.id_usuario).order_by(PasswordHistory.id.desc()).limit(5).all()
        for h in history:
            if verify_password(new_password, h.contrasena_hash):
                raise ValidationError("No puede reutilizar una contraseña reciente")
        self.db.add(PasswordHistory(usuario_id=user.id_usuario, contrasena_hash=user.contrasena))
        user.contrasena = new_hash
        self.db.delete(row)
        self._revoke_all_user_tokens(user.id_usuario)
        self.db.commit()
        log_security_event(self.db, "password_reset_completed", user.id_usuario, "auth/password/reset", ip)

    def get_me(self, user_id: int) -> dict:
        user = self.db.query(Usuario).filter(Usuario.id_usuario == user_id).first()
        if not user:
            raise NotFoundError("Usuario no encontrado")
        return {
            "id_usuario": user.id_usuario,
            "nombre": user.nombre,
            "apellido_paterno": user.apellido_paterno,
            "apellido_materno": user.apellido_materno,
            "email": user.email,
            "rol": user.rol,
        }

    def update_profile(
        self,
        user_id: int,
        *,
        nombre: str | None = None,
        apellido_paterno: str | None = None,
        apellido_materno: str | None = None,
    ) -> dict:
        user = self.db.query(Usuario).filter(Usuario.id_usuario == user_id).first()
        if not user:
            raise NotFoundError("Usuario no encontrado")
        changed = False
        if nombre is not None:
            user.nombre = nombre.strip()
            changed = True
        if apellido_paterno is not None:
            user.apellido_paterno = apellido_paterno.strip()
            changed = True
        if apellido_materno is not None:
            user.apellido_materno = apellido_materno.strip() if apellido_materno.strip() else None
            changed = True
        if changed:
            self.db.commit()
            self.db.refresh(user)
            log_security_event(self.db, "profile_updated", user.id_usuario, "auth/me", None)
        return self.get_me(user_id)
