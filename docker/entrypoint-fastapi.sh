#!/bin/bash
set -e
cd /app

echo "Ejecutando migraciones Alembic..."
alembic upgrade head

PORT="${PORT:-8000}"
echo "Iniciando FastAPI en puerto ${PORT}..."
exec uvicorn main:app --host 0.0.0.0 --port "${PORT}"
