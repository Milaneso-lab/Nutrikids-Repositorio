import os

# La configuración de seguridad aborta el arranque si faltan las credenciales.
# Se fijan valores de prueba antes de importar la app; no se conecta a la base
# real porque los tests unitarios no ejecutan consultas.
os.environ.setdefault(
    "NUTRIKIDS_DATABASE_URL",
    "postgresql+psycopg2://test:test@127.0.0.1:5432/nutrikids_test",
)
os.environ.setdefault("NUTRIKIDS_SECRET_KEY", "clave-solo-para-tests-no-usar-en-ningun-entorno-real")
os.environ.setdefault("NUTRIKIDS_SKIP_CREATE_ALL", "1")
os.environ.setdefault("NUTRIKIDS_ENABLE_DEV_SEED", "false")

import pytest
from fastapi.testclient import TestClient

from main import app


@pytest.fixture
def client():
    return TestClient(app)
