<#
.SYNOPSIS
    Genera docs/diccionario-datos.md a partir del esquema real de PostgreSQL.

.DESCRIPTION
    Consulta los catalogos pg_* dentro del contenedor nutrikids_postgres y produce
    un diccionario de datos en Markdown. Al derivarse de la base viva, el documento
    nunca queda desactualizado respecto al esquema.

    Nota: este archivo se mantiene en ASCII puro porque Windows PowerShell 5.1 lee
    los scripts sin BOM como ANSI y corromperia los acentos de los literales.

.EXAMPLE
    ./scripts/db/generar-diccionario.ps1
#>
[CmdletBinding()]
param(
    [string]$Salida = "docs/diccionario-datos.md"
)

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$raiz = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$envFile = Join-Path $raiz ".env"

function Get-EnvValue([string]$clave, [string]$porDefecto) {
    if (-not (Test-Path $envFile)) { return $porDefecto }
    $linea = Select-String -Path $envFile -Pattern "^$clave=" | Select-Object -First 1
    if ($null -eq $linea) { return $porDefecto }
    return $linea.Line.Substring($clave.Length + 1).Trim('"').Trim()
}

$usuario = Get-EnvValue "POSTGRES_USER" "nutrikids_user"
$baseDatos = Get-EnvValue "POSTGRES_DB" "nutrikids"

# Una fila por linea de Markdown: psql las emite en orden y PowerShell las une abajo.
$consulta = @"
SELECT linea FROM (
  SELECT c.relname AS tabla, 0 AS orden, 0 AS col, '## ' || c.relname AS linea
  FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'public' AND c.relkind = 'r'
  UNION ALL
  SELECT c.relname, 1, 0, '| Columna | Tipo | Nulo | Predeterminado | Clave |'
  FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'public' AND c.relkind = 'r'
  UNION ALL
  SELECT c.relname, 2, 0, '|---------|------|------|----------------|-------|'
  FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'public' AND c.relkind = 'r'
  UNION ALL
  SELECT c.relname, 3, a.attnum,
    '| ' || a.attname ||
    ' | `' || format_type(a.atttypid, a.atttypmod) || '`' ||
    ' | ' || CASE WHEN a.attnotnull THEN 'no' ELSE 'si' END ||
    ' | ' || COALESCE('`' || replace(pg_get_expr(d.adbin, d.adrelid), '|', '/') || '`', '-') ||
    ' | ' || COALESCE(k.tipo, '') || ' |'
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
  JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
  LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = a.attnum
  LEFT JOIN LATERAL (
    SELECT string_agg(DISTINCT CASE con.contype
             WHEN 'p' THEN 'PK'
             WHEN 'f' THEN 'FK -> ' || con.confrelid::regclass::text
             WHEN 'u' THEN 'UNICO' END, ', ') AS tipo
    FROM pg_constraint con
    WHERE con.conrelid = c.oid AND a.attnum = ANY(con.conkey) AND con.contype IN ('p','f','u')
  ) k ON true
  WHERE n.nspname = 'public' AND c.relkind = 'r'
  UNION ALL
  SELECT c.relname, 4, 0, ''
  FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE n.nspname = 'public' AND c.relkind = 'r'
) s
ORDER BY tabla, orden, col;
"@

$lineas = $consulta | docker exec -i nutrikids_postgres psql -U $usuario -d $baseDatos -t -A -X

if ($LASTEXITCODE -ne 0) {
    throw "psql fallo con codigo $LASTEXITCODE"
}

$tablas = ($lineas | Where-Object { $_ -like '## *' }).Count
$fecha = Get-Date -Format "yyyy-MM-dd HH:mm"

$encabezado = @(
    "# Diccionario de datos - NutriKids",
    "",
    "> Documento **generado automaticamente** desde el esquema vivo de PostgreSQL.",
    "> No editar a mano: regenerar con ``./scripts/db/generar-diccionario.ps1``.",
    "",
    "- Base de datos: ``$baseDatos``",
    "- Motor: PostgreSQL 15",
    "- Tablas documentadas: $tablas",
    "- Generado: $fecha",
    "",
    "---",
    ""
)

$contenido = (($encabezado + $lineas) -join "`r`n")
$rutaSalida = Join-Path $raiz $Salida
[System.IO.File]::WriteAllText($rutaSalida, $contenido, (New-Object System.Text.UTF8Encoding($false)))

Write-Host "Diccionario generado en $rutaSalida ($tablas tablas)" -ForegroundColor Green
