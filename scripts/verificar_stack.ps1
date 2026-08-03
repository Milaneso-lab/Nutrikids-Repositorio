# Verificación rápida tras `docker compose up -d` (PowerShell)
$ErrorActionPreference = "Continue"
Write-Host "Laravel (8080):" ; try { Invoke-RestMethod -Uri "http://localhost:8080/" -Method Get -TimeoutSec 15 | Out-Null; "  OK" } catch { "  Error: $_" }
Write-Host "FastAPI /health:" ; try { Invoke-RestMethod -Uri "http://localhost:8000/health" } catch { "  Error: $_" }
Write-Host "Flask /health/db:" ; try { Invoke-RestMethod -Uri "http://localhost:5000/health/db" } catch { "  Error: $_" }
Write-Host "Conteo tablas (requiere docker):" ; docker exec nutrikids_postgres psql -U nutrikids_user -d nutrikids -t -c "SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';"
Write-Host "Filas usuarios:" ; docker exec nutrikids_postgres psql -U nutrikids_user -d nutrikids -t -c "SELECT count(*) FROM usuarios;"
