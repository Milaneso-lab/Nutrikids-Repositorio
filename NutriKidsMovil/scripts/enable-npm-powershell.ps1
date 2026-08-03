# Habilita npm y npx en PowerShell cuando ExecutionPolicy bloquea npm.ps1
# Ejecutar UNA VEZ (no requiere admin):
#   powershell -ExecutionPolicy Bypass -File .\scripts\enable-npm-powershell.ps1

$ErrorActionPreference = 'Stop'

$profileDir = Split-Path -Parent $PROFILE
if (-not (Test-Path $profileDir)) {
    New-Item -ItemType Directory -Path $profileDir -Force | Out-Null
}

$marker = '# NutriKids — npm/npx via .cmd (evita bloqueo de npm.ps1)'
$block = @"

$marker
function npm { npm.cmd `@args }
function npx { npx.cmd `@args }

"@

if (Test-Path $PROFILE) {
    $content = Get-Content $PROFILE -Raw -ErrorAction SilentlyContinue
    if ($content -and $content.Contains($marker)) {
        Write-Host 'Ya estaba configurado en tu perfil de PowerShell.' -ForegroundColor Green
    } else {
        Add-Content -Path $PROFILE -Value $block
        Write-Host "Anadido a: $PROFILE" -ForegroundColor Green
    }
} else {
    Set-Content -Path $PROFILE -Value $block.TrimStart()
    Write-Host "Perfil creado: $PROFILE" -ForegroundColor Green
}

Write-Host ''
Write-Host 'Cierra y abre PowerShell, luego en NutriKidsMovil:' -ForegroundColor Cyan
Write-Host '  npm install'
Write-Host '  npx expo start --lan'
Write-Host ''
Write-Host 'Alternativa inmediata (sin reiniciar terminal):' -ForegroundColor Yellow
Write-Host '  npm.cmd install'
Write-Host '  npx.cmd expo start --lan'
