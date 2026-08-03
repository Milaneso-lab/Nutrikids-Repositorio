@echo off
cd /d "%~dp0"
if not exist .env copy .env.example .env
call npm.cmd install
call npx.cmd expo start --web --port 8082
