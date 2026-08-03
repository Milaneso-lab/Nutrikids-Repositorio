<#
.SYNOPSIS
    Restauración de un respaldo PostgreSQL de NutriKids (Windows / PowerShell).

.DESCRIPTION
    Restaura un archivo .dump (pg_restore) o .sql (psql) dentro del contenedor
    nutrikids_postgres. Requiere confirmación explícita porque sobrescribe datos.

.PARAMETER Archivo
    Ruta al respaldo a restaurar.

.PARAMETER Limpiar
    Elimina los objetos existentes antes de restaurar (pg_restore --clean).
    Sólo aplica a respaldos en formato custom.

.EXAMPLE
    ./scripts/db/restore.ps1 -Archivo ./backups/nutrikids_20260730_120000.dump -Limpiar
#>
[CmdletBinding(SupportsShouldProcess, ConfirmImpact = "High")]
param(
    [Parameter(Mandatory = $true)]
    [string]$Archivo,
    [switch]$Limpiar
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $Archivo)) {
    throw "No existe el archivo $Archivo"
}

$raiz = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$envFile = Join-Path $raiz ".env"

if (-not (Test-Path $envFile)) {
    throw "No se encontró $envFile."
}

function Get-EnvValue([string]$clave, [string]$porDefecto) {
    $linea = Select-String -Path $envFile -Pattern "^$clave=" | Select-Object -First 1
    if ($null -eq $linea) { return $porDefecto }
    return $linea.Line.Substring($clave.Length + 1).Trim('"').Trim()
}

$usuario = Get-EnvValue "POSTGRES_USER" "nutrikids_user"
$baseDatos = Get-EnvValue "POSTGRES_DB" "nutrikids"

if (-not $PSCmdlet.ShouldProcess("$baseDatos en nutrikids_postgres", "Restaurar $Archivo")) {
    return
}

$extension = [System.IO.Path]::GetExtension($Archivo).ToLower()

if ($extension -eq ".dump") {
    $argumentos = @("exec", "-i", "nutrikids_postgres", "pg_restore", "-U", $usuario, "-d", $baseDatos, "--no-owner", "--no-privileges")
    if ($Limpiar) { $argumentos += @("--clean", "--if-exists") }
    Get-Content $Archivo -Encoding Byte -Raw | docker @argumentos
} else {
    Get-Content $Archivo -Raw | docker exec -i nutrikids_postgres psql -U $usuario -d $baseDatos
}

Write-Host "Restauración completada desde $Archivo" -ForegroundColor Green
Write-Host "Ejecuta las migraciones para alinear el esquema:" -ForegroundColor Yellow
Write-Host "  docker compose exec fastapi alembic upgrade head"
Write-Host "  docker compose exec laravel php artisan migrate --force"
