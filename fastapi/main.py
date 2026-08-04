import logging
import os

from fastapi import Depends, FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse, RedirectResponse
from sqlalchemy import text

from app.api.v1.router import api_v1_router
from app.core.handlers import register_exception_handlers
from app.security.rate_limit import (
    global_rate_limiter,
    rate_limit_json_response,
    should_skip_global_rate_limit,
)
from app.security.settings import security_settings
from config import settings
from database import Base, engine
from routers import auth, citas, comentarios, contactos, discusiones, evaluaciones, menus, pacientes, reportes, users
from prometheus_fastapi_instrumentator import Instrumentator
from seed import activate_pending_padre_accounts, ensure_rbac_roles, seed_dev_users_if_missing

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("nutrikids")

app = FastAPI(
    title=settings.app_name,
    description=(
        "API REST NutriKids — `/api/v1/*` contrato seguro (04_API.md, 05_Seguridad.md). "
        "JWT 15 min + refresh con rotación. RBAC por permisos."
    ),
    version="1.1.0",
    docs_url="/docs",
    redoc_url="/redoc",
    openapi_url="/openapi.json",
)

register_exception_handlers(app)

Instrumentator(
    should_group_status_codes=True,
    should_ignore_untemplated=True,
    excluded_handlers=["/metrics", "/health"],
).instrument(app).expose(app, endpoint="/metrics", include_in_schema=False)

allowed_origins = [o.strip() for o in security_settings.cors_origins.split(",") if o.strip()]
env_origins = os.getenv("NUTRIKIDS_CORS_ORIGINS", "")
if env_origins:
    allowed_origins.extend([o.strip() for o in env_origins.split(",") if o.strip()])

_dev_cors_regex = (
    r"https?://("
    r"localhost|127\.0\.0\.1|"
    r"192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|"
    r"172\.(?:1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}"
    r")(:\d+)?$"
)

if security_settings.environment == "development":
    app.add_middleware(
        CORSMiddleware,
        allow_origins=allowed_origins,
        allow_origin_regex=_dev_cors_regex,
        allow_credentials=True,
        allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
        allow_headers=["Authorization", "Content-Type", "X-Request-ID"],
    )
else:
    app.add_middleware(
        CORSMiddleware,
        allow_origins=allowed_origins,
        allow_credentials=True,
        allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
        allow_headers=["Authorization", "Content-Type", "X-Request-ID"],
    )


@app.middleware("http")
async def security_middleware(request: Request, call_next):
    if (
        request.url.path.startswith("/api/")
        and not should_skip_global_rate_limit(request)
        and global_rate_limiter.check(request)
    ):
        return rate_limit_json_response()
    response = await call_next(request)
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "1; mode=block"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    response.headers["Permissions-Policy"] = "geolocation=(), microphone=(), camera=()"
    if security_settings.environment != "development":
        response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
    response.headers["Content-Security-Policy"] = "default-src 'none'; frame-ancestors 'none'"
    return response


@app.on_event("startup")
def on_startup() -> None:
    skip = os.getenv("NUTRIKIDS_SKIP_CREATE_ALL", "").lower() in ("1", "true", "yes")
    if not skip:
        Base.metadata.create_all(bind=engine)
    seed_dev_users_if_missing()
    ensure_rbac_roles()
    activate_pending_padre_accounts()
    logger.info("NutriKids API started env=%s", security_settings.environment)


@app.get("/")
def root():
    return RedirectResponse(url="/docs", status_code=307)


@app.get("/health")
def health():
    db_status = "ok"
    try:
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
    except Exception as exc:
        logger.warning("Health check DB failed: %s", exc)
        db_status = "unavailable"

    overall = "ok" if db_status == "ok" else "degraded"
    payload = {
        "status": overall,
        "app": settings.app_name,
        "api_version": "v1",
        "security": "enabled",
        "database": db_status,
        "environment": security_settings.environment,
    }
    status_code = 200 if overall == "ok" else 503
    return JSONResponse(content=payload, status_code=status_code)


app.include_router(api_v1_router, prefix="/api/v1")

app.include_router(auth.router, prefix="/api", tags=["legacy — auth"], deprecated=True)
app.include_router(users.router, prefix="/api", tags=["legacy — usuarios"], deprecated=True)
app.include_router(contactos.router, prefix="/api", tags=["legacy — contactos"], deprecated=True)
app.include_router(comentarios.router, prefix="/api", tags=["legacy — comentarios"], deprecated=True)
app.include_router(discusiones.router, prefix="/api", tags=["legacy — discusiones"], deprecated=True)
app.include_router(pacientes.router, prefix="/api", tags=["legacy — pacientes"], deprecated=True)
app.include_router(evaluaciones.router, prefix="/api", tags=["legacy — evaluaciones"], deprecated=True)
app.include_router(menus.router, prefix="/api", tags=["legacy — menus"], deprecated=True)
app.include_router(reportes.router, prefix="/api", tags=["legacy — reportes"], deprecated=True)
app.include_router(citas.router, prefix="/api", tags=["legacy — citas"], deprecated=True)
