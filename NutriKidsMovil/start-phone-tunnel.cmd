@echo off
cd /d "%~dp0"
echo NutriKids - Iniciando Expo en modo TUNNEL...
node scripts\start-phone.mjs --tunnel %*
