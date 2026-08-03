import logging

from fastapi import FastAPI, HTTPException, Request, status
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from sqlalchemy.exc import IntegrityError, OperationalError, SQLAlchemyError

from app.core.exceptions import AppException
from app.security.settings import security_settings

logger = logging.getLogger("nutrikids.errores")


def _respuesta(status_code: int, code: str, message: str, details: list[dict[str, str]] | None = None) -> JSONResponse:
    return JSONResponse(
        status_code=status_code,
        content={"error": {"code": code, "message": message, "details": details or []}},
    )


def _mensaje_integridad(exc: IntegrityError) -> tuple[str, str, int]:
    texto = str(getattr(exc, "orig", exc))

    if "unique constraint" in texto or "UniqueViolation" in texto:
        return "CONFLICT", "Ya existe un registro con esos datos.", status.HTTP_409_CONFLICT
    if "foreign key constraint" in texto or "ForeignKeyViolation" in texto:
        return (
            "CONFLICT",
            "El registro está vinculado a otra información y no puede modificarse o eliminarse.",
            status.HTTP_409_CONFLICT,
        )
    if "not-null constraint" in texto or "NotNullViolation" in texto:
        return "VALIDATION_ERROR", "Faltan datos obligatorios para guardar el registro.", status.HTTP_400_BAD_REQUEST
    if "check constraint" in texto or "CheckViolation" in texto:
        return "VALIDATION_ERROR", "Algún valor está fuera del rango permitido.", status.HTTP_400_BAD_REQUEST

    return "CONFLICT", "No se pudo guardar por un conflicto de integridad de datos.", status.HTTP_409_CONFLICT


def register_exception_handlers(app: FastAPI) -> None:
    @app.exception_handler(HTTPException)
    async def http_exception_handler(_request: Request, exc: HTTPException) -> JSONResponse:
        if exc.status_code == status.HTTP_429_TOO_MANY_REQUESTS:
            return _respuesta(
                status.HTTP_429_TOO_MANY_REQUESTS,
                "RATE_LIMITED",
                str(exc.detail) if isinstance(exc.detail, str) else "Demasiadas solicitudes. Intente más tarde.",
            )
        detail = exc.detail
        if isinstance(detail, dict):
            message = str(detail.get("message") or detail.get("detail") or "Solicitud rechazada")
        elif isinstance(detail, list):
            message = "Solicitud rechazada"
        else:
            message = str(detail)
        return _respuesta(exc.status_code, "HTTP_ERROR", message)

    @app.exception_handler(AppException)
    async def app_exception_handler(_request: Request, exc: AppException) -> JSONResponse:
        return JSONResponse(status_code=exc.status_code, content=exc.to_dict())

    @app.exception_handler(RequestValidationError)
    async def validation_exception_handler(_request: Request, exc: RequestValidationError) -> JSONResponse:
        details = []
        for err in exc.errors():
            loc = err.get("loc", ())
            field = ".".join(str(part) for part in loc if part != "body")
            details.append({"field": field or "body", "issue": err.get("msg", "valor inválido")})
        return _respuesta(status.HTTP_422_UNPROCESSABLE_ENTITY, "VALIDATION_ERROR", "Errores de validación", details)

    @app.exception_handler(IntegrityError)
    async def integrity_error_handler(request: Request, exc: IntegrityError) -> JSONResponse:
        code, mensaje, status_code = _mensaje_integridad(exc)
        logger.error(
            "Violación de integridad en %s %s: %s",
            request.method,
            request.url.path,
            getattr(exc, "orig", exc),
        )
        return _respuesta(status_code, code, mensaje)

    @app.exception_handler(OperationalError)
    async def operational_error_handler(request: Request, exc: OperationalError) -> JSONResponse:
        logger.error("Base de datos no disponible en %s %s: %s", request.method, request.url.path, exc)
        return _respuesta(
            status.HTTP_503_SERVICE_UNAVAILABLE,
            "DATABASE_UNAVAILABLE",
            "No hay conexión con la base de datos. Inténtalo de nuevo en unos segundos.",
        )

    @app.exception_handler(SQLAlchemyError)
    async def sqlalchemy_error_handler(request: Request, exc: SQLAlchemyError) -> JSONResponse:
        logger.exception("Error de base de datos en %s %s", request.method, request.url.path)
        return _respuesta(
            status.HTTP_500_INTERNAL_SERVER_ERROR,
            "DATABASE_ERROR",
            "No se pudo completar la operación en la base de datos.",
            _detalle_tecnico(exc),
        )

    # Red de seguridad: cualquier excepción no contemplada devuelve el mismo
    # contrato de error en lugar de una traza HTML sin cuerpo JSON.
    @app.exception_handler(Exception)
    async def unhandled_exception_handler(request: Request, exc: Exception) -> JSONResponse:
        logger.exception("Error no controlado en %s %s", request.method, request.url.path)
        return _respuesta(
            status.HTTP_500_INTERNAL_SERVER_ERROR,
            "INTERNAL_ERROR",
            "Ocurrió un error inesperado. El equipo técnico ha sido notificado.",
            _detalle_tecnico(exc),
        )


def _detalle_tecnico(exc: Exception) -> list[dict[str, str]]:
    """El detalle técnico sólo se expone fuera de producción."""
    if security_settings.environment == "production":
        return []

    return [{"field": "debug", "issue": f"{type(exc).__name__}: {str(exc)[:300]}"}]
