#!/usr/bin/env bash
# Restauración de respaldos PostgreSQL de NutriKids (Linux / macOS / WSL).
# Uso: ./scripts/db/restore_postgres.sh <archivo.dump|archivo.sql|archivo.sql.gz> [--clean]
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Uso: $0 <archivo.dump|archivo.sql|archivo.sql.gz> [--clean]" >&2
  exit 1
fi

ARCHIVO="$1"
LIMPIAR="${2:-}"

if [[ ! -f "$ARCHIVO" ]]; then
  echo "No existe el archivo $ARCHIVO" >&2
  exit 1
fi

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
if [[ -f "$RAIZ/.env" ]]; then
  # shellcheck disable=SC1091
  set -a && source "$RAIZ/.env" && set +a
fi

USUARIO="${POSTGRES_USER:-nutrikids_user}"
BASE="${POSTGRES_DB:-nutrikids}"

echo "Restaurando $ARCHIVO en la base '$BASE'. Esto sobrescribe datos existentes."
read -r -p "Escribe 'si' para continuar: " CONFIRMA
[[ "$CONFIRMA" == "si" ]] || { echo "Cancelado."; exit 0; }

case "$ARCHIVO" in
  *.dump)
    ARGS=(-U "$USUARIO" -d "$BASE" --no-owner --no-privileges)
    [[ "$LIMPIAR" == "--clean" ]] && ARGS+=(--clean --if-exists)
    docker exec -i nutrikids_postgres pg_restore "${ARGS[@]}" < "$ARCHIVO"
    ;;
  *.sql.gz)
    gunzip -c "$ARCHIVO" | docker exec -i nutrikids_postgres psql -U "$USUARIO" -d "$BASE"
    ;;
  *.sql)
    docker exec -i nutrikids_postgres psql -U "$USUARIO" -d "$BASE" < "$ARCHIVO"
    ;;
  *)
    echo "Formato no reconocido: $ARCHIVO" >&2
    exit 1
    ;;
esac

echo "Restauración completada. Alinea el esquema con:"
echo "  docker compose exec fastapi alembic upgrade head"
echo "  docker compose exec laravel php artisan migrate --force"
