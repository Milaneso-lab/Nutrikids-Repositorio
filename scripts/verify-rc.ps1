# Verificación Release Candidate — NutriKids
# Uso: .\scripts\verify-rc.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

Write-Host "=== NutriKids RC Verification ===" -ForegroundColor Cyan

function Test-Command($label, $scriptBlock) {
    Write-Host "`n>> $label" -ForegroundColor Yellow
    try {
        & $scriptBlock
        if ($LASTEXITCODE -ne 0 -and $null -ne $LASTEXITCODE) { throw "Exit code $LASTEXITCODE" }
        Write-Host "OK: $label" -ForegroundColor Green
    } catch {
        Write-Host "FAIL: $label — $_" -ForegroundColor Red
        exit 1
    }
}

Test-Command "Laravel PHPUnit" { php artisan test --parallel=1 2>$null; if (-not $?) { php artisan test } }

Test-Command "FastAPI unit tests" {
    Push-Location fastapi
    $env:NUTRIKIDS_ENABLE_DEV_SEED = "false"
    $env:NUTRIKIDS_SKIP_CREATE_ALL = "1"
    python -m pytest tests/unit/ -q
    Pop-Location
}

Test-Command "Mobile typecheck" {
    Push-Location NutriKidsMovil
    npm run typecheck
    Pop-Location
}

Test-Command "Mobile unit tests" {
    Push-Location NutriKidsMovil
    npm test -- --ci
    Pop-Location
}

Write-Host "`n=== RC verification completed successfully ===" -ForegroundColor Cyan
