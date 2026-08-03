@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env
echo Instalando dependencias...
call npm.cmd install
echo.
echo Iniciando NutriKids (Expo)...
echo - Movil: escanea el QR con Expo Go
echo - Web:   http://localhost:8081 despues de pulsar w, o ejecuta start-web.bat
echo.
call npm.cmd start
