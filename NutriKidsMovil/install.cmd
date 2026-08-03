@echo off
cd /d "%~dp0"
echo Instalando dependencias de NutriKidsMovil...
call npm.cmd install
if errorlevel 1 exit /b 1
echo.
echo Listo. Para iniciar Expo:
echo   npx.cmd expo start --lan
echo o en PowerShell (tras enable-npm-powershell.ps1):
echo   npx expo start --lan
