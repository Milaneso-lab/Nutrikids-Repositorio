<#
.SYNOPSIS
    Respaldo lógico de la base PostgreSQL de NutriKids (Windows / PowerShell).

.DESCRIPTION
    Ejecuta pg_dump dentro del contenedor nutrikids_postgres y guarda el volcado
    comprimido en el directorio destino. Las credenciales se leen del archivo .env
    del proyecto; nunca se pasan por línea de comandos.

.PARAMETER Destino
    Carpeta donde se guardará el respaldo. Por defecto ./backups

.PARAMETER Formato
    'custom' (por defecto, .dump restaurable con pg_restore y selectivo por tabla)
    o 'plain' (.sql legible, restaurable con psql).

.EXAMPLE
    ./scripts/db/backup.ps1
    ./scripts/db/backup.ps1 -Destino D:\respaldos -Formato plain
#>
[CmdletBinding()]
param(
    [string]$Destino = "./backups",
    [ValidateSet("custom", "plain")]
    [string]$Formato = "custom"
)

$ErrorActionPreference = "Stop"

$raiz = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$envFile = Join-Path $raiz ".env"

if (-not (Test-Path $envFile)) {
    throw "No se encontró $envFile. Copia .env.example a .env antes de respaldar."
}

function Get-EnvValue([string]$clave, [string]$porDefecto) {
    $linea = Select-String -Path $envFile -Pattern "^$clave=" | Select-Object -First 1
    if ($null -eq $linea) { return $porDefecto }
    return $linea.Line.Substring($clave.Length + 1).Trim('"').Trim()
}

$usuario = Get-EnvValue "POSTGRES_USER" "nutrikids_user"
$baseDatos = Get-EnvValue "POSTGRES_DB" "nutrikids"

if (-not (Test-Path $Destino)) {
    New-Item -ItemType Directory -Path $Destino -Force | Out-Null
}

$estampa = Get-Date -Format "yyyyMMdd_HHmmss"

if ($Formato -eq "custom") {
    $archivo = Join-Path $Destino "nutrikids_$estampa.dump"
    docker exec nutrikids_postgres pg_dump -U $usuario -d $baseDatos -Fc --no-owner --no-privileges |
        Set-Content -Path $archivo -Encoding Byte
} else {
    $archivo = Join-Path $Destino "nutrikids_$estampa.sql"
    docker exec nutrikids_postgres pg_dump -U $usuario -d $baseDatos --no-owner --no-privileges |
        Set-Content -Path $archivo -Encoding UTF8
}

if ($LASTEXITCODE -ne 0) {
    throw "pg_dump falló con código $LASTEXITCODE"
}

$tamanoMb = [math]::Round((Get-Item $archivo).Length / 1MB, 2)
Write-Host "Respaldo creado: $archivo ($tamanoMb MB)" -ForegroundColor Green
