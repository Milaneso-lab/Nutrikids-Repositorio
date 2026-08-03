"""Configuración de correo (restablecimiento de contraseña)."""

from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict

_ROOT = Path(__file__).resolve().parent.parent.parent


class MailSettings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=_ROOT / ".env",
        env_file_encoding="utf-8",
        env_prefix="NUTRIKIDS_MAIL_",
        extra="ignore",
    )

    # log = imprime en consola (desarrollo) | smtp = envío real
    mailer: str = "log"
    smtp_host: str = ""
    smtp_port: int = 587
    smtp_user: str = ""
    smtp_password: str = ""
    from_address: str = "noreply@nutrikids.com"
    from_name: str = "NutriKids"
    use_tls: bool = True
    reset_deep_link_base: str = "nutrikids://reset-password"


mail_settings = MailSettings()
