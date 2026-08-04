# Levanta NutriKids con gateway HTTPS, balanceador y monitoreo para la exposición.
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
docker compose -f docker-compose.yml -f docker-compose.infra.yml --profile gateway --profile monitoring up -d --build
Write-Host ""
Write-Host "NutriKids — stack de exposición"
Write-Host "  Sitio (gateway HTTPS):  https://localhost:9443"
Write-Host "  API directa:            http://localhost:8000/docs"
Write-Host "  Flask directo:          http://localhost:5000"
Write-Host "  Laravel admin:          http://localhost:8080/admin/dashboard"
Write-Host "  Prometheus:             http://127.0.0.1:9090"
Write-Host "  Grafana:                http://127.0.0.1:3000  (admin / nutrikids_grafana)"
Pop-Location
