"""Autenticación API v1 (/api/v1/auth/*)."""

from datetime import date

from fastapi import APIRouter, Depends, status
from pydantic import BaseModel, EmailStr, Field
from starlette.requests import Request

from app.api.deps import get_gamificacion_service
from app.security.rate_limit import login_rate_limiter, register_rate_limiter, client_ip
from app.security.rbac import SecurityContext, get_nino_security_context, get_security_context
from app.schemas.v1.schemas import NinoJuegoOutV1, NinoJuegoProgresoInV1, NinoJuegoProgresoOutV1
from app.services.auth_service import AuthService
from app.services.gamificacion_service import GamificacionService
from app.services.nino_auth_service import NinoAuthService
from database import get_db
from sqlalchemy.orm import Session

router = APIRouter(prefix="/auth", tags=["v1 — autenticación"])


class LoginInV1(BaseModel):
    email: EmailStr
    contrasena: str
    dispositivo: str | None = None
    mobile: bool = False


class RegisterInV1(BaseModel):
    nombre: str = Field(..., min_length=1, max_length=100)
    apellido_paterno: str = Field(..., min_length=1, max_length=100)
    apellido_materno: str | None = None
    email: EmailStr
    contrasena: str = Field(..., min_length=8)


class TokenPairOutV1(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int
    rol: str | None = None
    id_usuario: int | None = None
    nombre: str | None = None
    apellido_paterno: str | None = None
    email: str | None = None


class RefreshInV1(BaseModel):
    refresh_token: str


class RefreshOutV1(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int


class LogoutInV1(BaseModel):
    refresh_token: str


class MessageOutV1(BaseModel):
    message: str


class PasswordForgotInV1(BaseModel):
    email: EmailStr


class PasswordResetInV1(BaseModel):
    email: EmailStr
    token: str
    nueva_contrasena: str = Field(..., min_length=8)


class NinoAccesoInV1(BaseModel):
    codigo_vinculacion: str = Field(..., min_length=4, max_length=12)
    pin: str = Field(..., min_length=4, max_length=6)
    dispositivo: str | None = None


class NinoAccesoOutV1(BaseModel):
    requiere_configurar_pin: bool = False
    access_token: str | None = None
    refresh_token: str | None = None
    token_type: str = "bearer"
    expires_in: int | None = None
    nino_id: int
    nombre: str
    apellidos: str
    fecha_nacimiento: date
    sexo: str | None = None
    avatar_config: dict | None = None
    nivel_actual: int = 1
    puntos_totales: int = 0
    companion: str | None = None


class NinoRefreshInV1(BaseModel):
    refresh_token: str


class NinoLogoutInV1(BaseModel):
    refresh_token: str


class ParentMeOutV1(BaseModel):
    id_usuario: int
    nombre: str
    apellido_paterno: str
    apellido_materno: str | None = None
    email: str
    rol: str


class ParentProfileUpdateInV1(BaseModel):
    nombre: str | None = Field(None, min_length=1, max_length=100)
    apellido_paterno: str | None = Field(None, min_length=1, max_length=100)
    apellido_materno: str | None = Field(None, max_length=100)


class NinoProfileUpdateInV1(BaseModel):
    avatar_config: dict | None = None
    nombre: str | None = Field(None, min_length=1, max_length=100)
    apellidos: str | None = Field(None, min_length=1, max_length=100)


def get_auth_service(db: Session = Depends(get_db)) -> AuthService:
    return AuthService(db)


def get_nino_auth_service(db: Session = Depends(get_db)) -> NinoAuthService:
    return NinoAuthService(db)


@router.post("/login", response_model=TokenPairOutV1, dependencies=[Depends(login_rate_limiter)])
def login_v1(payload: LoginInV1, request: Request, service: AuthService = Depends(get_auth_service)):
    result = service.login(
        payload.email,
        payload.contrasena,
        ip=client_ip(request),
        dispositivo=payload.dispositivo,
        mobile=payload.mobile,
    )
    return TokenPairOutV1(**result)


@router.post("/register", status_code=status.HTTP_201_CREATED, dependencies=[Depends(register_rate_limiter)])
def register_v1(payload: RegisterInV1, request: Request, service: AuthService = Depends(get_auth_service)):
    user = service.register_padre(payload.model_dump(), ip=client_ip(request))
    return {
        "id_usuario": user.id_usuario,
        "email": user.email,
        "nombre": user.nombre,
        "rol": user.rol,
        "estado": user.estado,
    }


@router.post("/refresh", response_model=RefreshOutV1, dependencies=[Depends(login_rate_limiter)])
def refresh_v1(payload: RefreshInV1, request: Request, service: AuthService = Depends(get_auth_service)):
    return RefreshOutV1(**service.refresh(payload.refresh_token, ip=client_ip(request)))


@router.post("/logout", response_model=MessageOutV1)
def logout_v1(
    payload: LogoutInV1,
    request: Request,
    ctx: SecurityContext = Depends(get_security_context),
    service: AuthService = Depends(get_auth_service),
):
    service.logout(payload.refresh_token, access_jti=ctx.jti, user_id=ctx.user.id_usuario, ip=client_ip(request))
    return MessageOutV1(message="Sesión cerrada")


@router.get("/me", response_model=ParentMeOutV1)
def me_v1(
    ctx: SecurityContext = Depends(get_security_context),
    service: AuthService = Depends(get_auth_service),
):
    return ParentMeOutV1.model_validate(service.get_me(ctx.user.id_usuario))


@router.patch("/me", response_model=ParentMeOutV1)
def update_me_v1(
    payload: ParentProfileUpdateInV1,
    ctx: SecurityContext = Depends(get_security_context),
    service: AuthService = Depends(get_auth_service),
):
    return ParentMeOutV1.model_validate(
        service.update_profile(
            ctx.user.id_usuario,
            nombre=payload.nombre,
            apellido_paterno=payload.apellido_paterno,
            apellido_materno=payload.apellido_materno,
        )
    )


@router.post("/password/forgot", response_model=MessageOutV1)
def password_forgot_v1(
    payload: PasswordForgotInV1,
    request: Request,
    service: AuthService = Depends(get_auth_service),
):
    service.request_password_reset(payload.email, ip=client_ip(request))
    return MessageOutV1(
        message="Si el correo está registrado, recibirás un código de verificación en unos minutos.",
    )


@router.post("/password/reset", response_model=MessageOutV1)
def password_reset_v1(
    payload: PasswordResetInV1,
    request: Request,
    service: AuthService = Depends(get_auth_service),
):
    service.reset_password(payload.email, payload.token, payload.nueva_contrasena, ip=client_ip(request))
    return MessageOutV1(message="Contraseña actualizada")


@router.post("/nino/acceso", response_model=NinoAccesoOutV1, dependencies=[Depends(login_rate_limiter)])
def nino_acceso_v1(
    payload: NinoAccesoInV1,
    request: Request,
    service: NinoAuthService = Depends(get_nino_auth_service),
):
    return NinoAccesoOutV1.model_validate(
        service.acceso(
            payload.codigo_vinculacion,
            payload.pin,
            dispositivo=payload.dispositivo,
            ip=client_ip(request),
        )
    )


@router.get("/nino/me", response_model=NinoAccesoOutV1)
def nino_me_v1(
    ctx=Depends(get_nino_security_context),
    service: NinoAuthService = Depends(get_nino_auth_service),
):
    return NinoAccesoOutV1.model_validate({**service.get_me(ctx.nino_id), "requiere_configurar_pin": False})


@router.patch("/nino/me", response_model=NinoAccesoOutV1)
def nino_update_me_v1(
    payload: NinoProfileUpdateInV1,
    ctx=Depends(get_nino_security_context),
    service: NinoAuthService = Depends(get_nino_auth_service),
):
    return NinoAccesoOutV1.model_validate(
        {
            **service.update_profile(
                ctx.nino_id,
                avatar_config=payload.avatar_config,
                nombre=payload.nombre,
                apellidos=payload.apellidos,
            ),
            "requiere_configurar_pin": False,
        }
    )


@router.post("/nino/refresh", response_model=RefreshOutV1, dependencies=[Depends(login_rate_limiter)])
def nino_refresh_v1(
    payload: NinoRefreshInV1,
    request: Request,
    service: NinoAuthService = Depends(get_nino_auth_service),
):
    return RefreshOutV1(**service.refresh(payload.refresh_token, ip=client_ip(request)))


@router.post("/nino/logout", response_model=MessageOutV1)
def nino_logout_v1(
    payload: NinoLogoutInV1,
    request: Request,
    ctx=Depends(get_nino_security_context),
    service: NinoAuthService = Depends(get_nino_auth_service),
):
    service.logout(payload.refresh_token, access_jti=ctx.jti, ip=client_ip(request))
    return MessageOutV1(message="Sesión de niño cerrada")


@router.get("/nino/juegos", response_model=list[NinoJuegoOutV1])
def nino_list_juegos_v1(
    ctx=Depends(get_nino_security_context),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    return [NinoJuegoOutV1.model_validate(item) for item in service.list_juegos_nino(ctx.nino_id)]


@router.post("/nino/juegos/progreso", response_model=NinoJuegoProgresoOutV1)
def nino_save_juego_progreso_v1(
    payload: NinoJuegoProgresoInV1,
    ctx=Depends(get_nino_security_context),
    service: GamificacionService = Depends(get_gamificacion_service),
):
    return NinoJuegoProgresoOutV1.model_validate(
        service.guardar_progreso_juego(ctx.nino_id, payload.game_id, payload.score, payload.metadata)
    )
