#!/usr/bin/env bash
# Respaldo lógico PostgreSQL NutriKids (05_Seguridad.md §9)
# Uso: ./scripts/backup_postgres.sh [directorio_destino]
# Genera dos artefactos: .dump (formato custom, restauración selectiva) y .sql.gz (legible).
set -euo pipefail

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [[ -f "$RAIZ/.env" ]]; then
  # shellcheck disable=SC1091
  set -a && source "$RAIZ/.env" && set +a
fi

DEST="${1:-./backups}"
mkdir -p "$DEST"
STAMP=$(date +%Y%m%d_%H%M%S)
USUARIO="${POSTGRES_USER:-nutrikids_user}"
BASE="${POSTGRES_DB:-nutrikids}"

DUMP="$DEST/nutrikids_${STAMP}.dump"
PLANO="$DEST/nutrikids_${STAMP}.sql.gz"

docker exec nutrikids_postgres pg_dump -U "$USUARIO" -d "$BASE" -Fc --no-owner --no-privileges > "$DUMP"
docker exec nutrikids_postgres pg_dump -U "$USUARIO" -d "$BASE" --no-owner --no-privileges | gzip > "$PLANO"

echo "Backup creado:"
echo "  $DUMP"
echo "  $PLANO"
echo "Restaurar con: ./scripts/db/restore_postgres.sh $DUMP --clean"
