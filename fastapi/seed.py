"""Usuarios de demostración (misma convención que CredencialesSeeder en Laravel)."""

import os

from sqlalchemy.orm import Session

from database import SessionLocal
from models import Usuario
from security import hash_password

DEV_USERS = [
    {
        "nombre": "Administrador",
        "apellido_paterno": "Sistema",
        "apellido_materno": "NutriKids",
        "email": "admin@nutrikids.com",
        "contrasena": "admin1234",
        "rol": "admin",
    },
    {
        "nombre": "Sandra",
        "apellido_paterno": "Olmos",
        "apellido_materno": "García",
        "email": "nutriologo@nutrikids.com",
        "contrasena": "Nutri123*",
        "rol": "nutriologo",
    },
    {
        "nombre": "Carlos",
        "apellido_paterno": "Ramírez",
        "apellido_materno": "López",
        "email": "padre@nutrikids.com",
        "contrasena": "Padre123*",
        "rol": "padre",
    },
]


def activate_pending_padre_accounts() -> None:
    """Tras rebuild de Postgres, padres registrados quedan pendientes sin flujo de verificación."""
    import logging

    logger = logging.getLogger("nutrikids")
    db: Session = SessionLocal()
    try:
        pending = (
            db.query(Usuario)
            .filter(
                Usuario.rol == "padre",
                (Usuario.estado.is_(None)) | (Usuario.estado == "pendiente_verificacion"),
            )
            .all()
        )
        if not pending:
            return
        for user in pending:
            user.estado = "activo"
        db.commit()
        logger.info("Cuentas padre activadas al arranque: %s", len(pending))
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudieron activar cuentas padre pendientes: %s", exc)
    finally:
        db.close()


def seed_dev_users_if_missing() -> None:
    env = os.getenv("NUTRIKIDS_ENVIRONMENT", os.getenv("NUTRIKIDS_ENV", "development")).lower()
    enable = os.getenv("NUTRIKIDS_ENABLE_DEV_SEED", "true" if env == "development" else "false").lower()
    enable_explicit = enable in ("1", "true", "yes")

    if env in ("production", "staging") and not enable_explicit:
        db_probe: Session = SessionLocal()
        try:
            if db_probe.query(Usuario).count() > 0:
                return
        except Exception:
            return
        finally:
            db_probe.close()
    elif not enable_explicit:
        return

    import logging

    logger = logging.getLogger("nutrikids")
    if env in ("production", "staging"):
        logger.info("Base de datos vacía: aplicando credenciales de demostración (CredencialesSeeder)")

    db: Session = SessionLocal()
    try:
        for u in DEV_USERS:
            exists = db.query(Usuario).filter(Usuario.email == u["email"]).first()
            if exists:
                exists.nombre = u["nombre"]
                exists.apellido_paterno = u["apellido_paterno"]
                exists.apellido_materno = u["apellido_materno"]
                exists.rol = u["rol"]
                exists.contrasena = hash_password(u["contrasena"])
                if hasattr(exists, "estado"):
                    exists.estado = "activo"
            else:
                row = Usuario(
                    nombre=u["nombre"],
                    apellido_paterno=u["apellido_paterno"],
                    apellido_materno=u["apellido_materno"],
                    email=u["email"],
                    contrasena=hash_password(u["contrasena"]),
                    rol=u["rol"],
                    estado="activo",
                )
                db.add(row)
        db.commit()
    except Exception as exc:
        db.rollback()
        import logging

        logging.getLogger("nutrikids").warning("Dev seed skipped: %s", exc)
    finally:
        db.close()
