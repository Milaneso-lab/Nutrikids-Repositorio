# Inicia Expo para celular (LAN + firewall + IP Wi-Fi)
# Uso: .\scripts\start-phone.ps1
#      .\scripts\start-phone.ps1 -Tunnel   # si LAN falla (red invitados / firewall)

param(
    [switch]$Tunnel,
    [switch]$Clear
)

$ErrorActionPreference = "Stop"
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

function Get-WifiIPv4 {
    $wifi = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
        Where-Object {
            $_.InterfaceAlias -match 'Wi-Fi|WLAN' -and
            $_.IPAddress -notmatch '^169\.254\.'
        } |
        Select-Object -First 1
    if ($wifi) {
        return $wifi.IPAddress
    }
    return $null
}

$lanIp = Get-WifiIPv4
if (-not $lanIp -and -not $Tunnel) {
    Write-Host "No se detectó IP Wi-Fi. Usa -Tunnel o conecta el PC al Wi-Fi." -ForegroundColor Yellow
    $Tunnel = $true
}

if (-not $Tunnel -and $lanIp) {
    Write-Host "IP Wi-Fi detectada: $lanIp" -ForegroundColor Cyan
    $env:REACT_NATIVE_PACKAGER_HOSTNAME = $lanIp

    # Regla firewall (requiere admin; ignorar si falla)
    try {
        $ruleName = "NutriKids Expo Metro 8081"
        $existing = netsh advfirewall firewall show rule name="$ruleName" 2>$null
        if (-not $existing) {
            netsh advfirewall firewall add rule name="$ruleName" dir=in action=allow protocol=TCP localport=8081 | Out-Null
            Write-Host "Regla firewall añadida para puerto 8081" -ForegroundColor Green
        }
    } catch {
        Write-Host "No se pudo abrir firewall (ejecuta PowerShell como admin si el celular no conecta)" -ForegroundColor Yellow
    }

    # Actualizar .env con IP actual (solo API; demo no la requiere)
    $envFile = Join-Path $projectRoot ".env"
    if (Test-Path $envFile) {
        $content = Get-Content $envFile -Raw
        $content = $content -replace 'EXPO_PUBLIC_API_BASE_URL=.*', "EXPO_PUBLIC_API_BASE_URL=http://${lanIp}:8000"
        Set-Content -Path $envFile -Value $content.TrimEnd() -NoNewline
        Add-Content -Path $envFile -Value "`n"
    }

    Write-Host ""
    Write-Host "=== Expo Go (celular) ===" -ForegroundColor Cyan
    Write-Host "1. Mismo Wi-Fi que este PC"
    Write-Host "2. Abre Expo Go y escanea el QR de exp://${lanIp}:8081"
    Write-Host "   (NO uses localhost en el teléfono)"
    Write-Host "3. Demo: demo@nutrikids.app / Demo1234"
    Write-Host ""
}

$expoArgs = @("start")
if ($Tunnel) {
    Write-Host "Modo TUNNEL (funciona aunque LAN esté bloqueada)" -ForegroundColor Cyan
    $expoArgs += "--tunnel"
} else {
    $expoArgs += "--lan"
}
if ($Clear) {
    $expoArgs += "--clear"
}

Write-Host "Iniciando: npx expo $($expoArgs -join ' ')" -ForegroundColor Green
& npx expo @expoArgs
