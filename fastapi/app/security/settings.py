"""Configuración de seguridad (05_Seguridad.md)."""

from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict

_ROOT = Path(__file__).resolve().parent.parent


class SecuritySettings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=_ROOT / ".env",
        env_file_encoding="utf-8",
        env_prefix="NUTRIKIDS_",
        extra="ignore",
    )

    app_name: str = "NutriKids API"
    environment: str = "development"
    # Sin valor por defecto: un default publicado permitiria a cualquiera firmar
    # JWT validos. Se resuelve desde NUTRIKIDS_SECRET_KEY.
    secret_key: str = ""
    algorithm: str = "HS256"
    access_token_minutes: int = 15
    refresh_token_days_web: int = 30
    refresh_token_days_mobile: int = 90
    bcrypt_rounds: int = 12
    # Sin credenciales por defecto: se resuelve desde NUTRIKIDS_DATABASE_URL
    # (docker-compose la inyecta) o desde fastapi/.env.
    database_url: str = ""
    redis_url: str | None = None
    cors_origins: str = (
        "http://localhost:5000,http://127.0.0.1:5000,"
        "http://localhost:8080,http://127.0.0.1:8080,"
        "http://localhost:8081,http://127.0.0.1:8081,"
        "http://localhost:19006,http://127.0.0.1:19006"
    )
    password_min_length: int = 8
    password_require_upper: bool = True
    password_require_lower: bool = True
    password_require_digit: bool = True
    login_max_attempts: int = 5
    login_lockout_minutes: int = 15
    rate_limit_login_per_min: int = 5
    rate_limit_register_per_min: int = 3
    rate_limit_contact_per_min: int = 3
    rate_limit_global_per_min: int = 120
    password_reset_minutes: int = 30
    audit_log_enabled: bool = True

    @property
    def effective_login_rate_limit(self) -> int:
        if self.environment == "development":
            return max(self.rate_limit_login_per_min, 30)
        return max(self.rate_limit_login_per_min, 30)

    @property
    def effective_global_rate_limit(self) -> int:
        """La app móvil genera muchas peticiones legítimas por sesión."""
        if self.environment == "development":
            return max(self.rate_limit_global_per_min, 600)
        return max(self.rate_limit_global_per_min, 600)


security_settings = SecuritySettings()

if not security_settings.database_url:
    raise RuntimeError(
        "NUTRIKIDS_DATABASE_URL no está definida. Configúrala en fastapi/.env o en "
        "docker-compose.yml antes de iniciar la API (PostgreSQL es la única fuente de datos)."
    )

if len(security_settings.secret_key) < 32:
    raise RuntimeError(
        "NUTRIKIDS_SECRET_KEY debe estar definida con al menos 32 caracteres aleatorios. "
        "Es la clave de firma de los JWT: sin ella cualquiera podría emitir tokens válidos."
    )

# Compatibilidad con config.py legacy
settings = security_settings
