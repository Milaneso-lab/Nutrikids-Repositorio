# Compila e instala la APK release en el dispositivo conectado por USB.
$ErrorActionPreference = "Stop"
$buildRoot = if ($env:NUTRIKIDS_BUILD_DIR) { $env:NUTRIKIDS_BUILD_DIR } else { "C:\NutriKidsMovil" }
$repoRoot = Split-Path -Parent $PSScriptRoot

Write-Host "Sincronizando código hacia $buildRoot ..."
robocopy "$repoRoot\src" "$buildRoot\src" /MIR /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
Copy-Item "$repoRoot\package.json" "$buildRoot\package.json" -Force
Copy-Item "$repoRoot\package-lock.json" "$buildRoot\package-lock.json" -Force
Copy-Item "$repoRoot\app.config.ts" "$buildRoot\app.config.ts" -Force
if (Test-Path "$repoRoot\.env") { Copy-Item "$repoRoot\.env" "$buildRoot\.env" -Force }

Push-Location $buildRoot
npm install --silent
$env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-17.0.20.8-hotspot"
$env:PATH = "$env:JAVA_HOME\bin;$env:PATH"

Write-Host "Compilando bundle Android ..."
npx expo export:embed --eager --platform android --dev false

Write-Host "Generando APK release ..."
Push-Location android
.\gradlew assembleRelease --no-daemon -q
Pop-Location

$apk = Join-Path $buildRoot "android\app\build\outputs\apk\release\app-release.apk"
$desktopApk = Join-Path $env:USERPROFILE "Desktop\NutriKids-release.apk"
Copy-Item $apk $desktopApk -Force

Write-Host "Instalando en dispositivo ..."
adb wait-for-device
adb install -r $apk
adb shell monkey -p com.nutrikids.movil -c android.intent.category.LAUNCHER 1 | Out-Null
Pop-Location

Write-Host "Listo: NutriKids instalada."
