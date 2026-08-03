"""Configuración — delega a app.security.settings."""

from app.security.settings import SecuritySettings, security_settings

settings = security_settings

__all__ = ["Settings", "settings"]

Settings = SecuritySettings
