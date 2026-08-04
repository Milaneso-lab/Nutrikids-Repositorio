# Despliegue automático en Railway

Cada push a la rama `main` puede redeployar Flask, FastAPI y Laravel en Railway.

## Opción A — GitHub Actions (recomendada, ya configurada)

Archivo: `.github/workflows/deploy-railway.yml`

### Configuración única (5 minutos)

1. Entra a **Railway** → tu proyecto NutriKids → **Settings** → **Tokens**.
2. Crea un **Project Token** y cópialo.
3. En **GitHub** → repositorio `Nutrikids-Repositorio` → **Settings** → **Secrets and variables** → **Actions**.
4. Crea el secret:
   - Nombre: `RAILWAY_TOKEN`
   - Valor: el token de Railway
5. (Opcional) En **Variables**, si tus servicios tienen otro nombre:
   - `RAILWAY_SERVICE_FLASK` → ej. `nutrikids-flask`
   - `RAILWAY_SERVICE_FASTAPI` → ej. `nutrikids-fastapi`
   - `RAILWAY_SERVICE_LARAVEL` → ej. `nutrikids-laravel`

### Qué ocurre después

- Cada `git push` a `main` ejecuta el workflow **Deploy Railway**.
- Se redeployan los 3 servicios con el código más reciente.
- FastAPI ejecuta `alembic upgrade head` al arrancar (migraciones BD).

### Verificar

- GitHub → pestaña **Actions** → workflow **Deploy Railway**
- Railway → cada servicio → **Deployments** (debe aparecer deploy nuevo)

## Opción B — Auto-deploy nativo de Railway

En cada servicio de Railway:

1. **Settings** → **Source** → conectar repo `Milaneso-lab/Nutrikids-Repositorio`
2. Rama: `main`
3. Activar **Auto Deploy**
4. Configurar Dockerfile:
   - Flask: `Dockerfile.flask` + config `railway.flask.toml`
   - FastAPI: `Dockerfile.fastapi` + `railway.fastapi.toml`
   - Laravel: `Dockerfile.laravel` + `railway.laravel.toml`

Si usas GitHub Actions, puedes **desactivar** Auto Deploy en Railway para evitar doble despliegue.

## URLs de producción

| Servicio | URL |
|----------|-----|
| Sitio web (Flask) | https://nutrikids-flask-production.up.railway.app |
| API (FastAPI) | https://nutrikids-sitioweb.up.railway.app |
| Admin (Laravel) | https://nutrikids-laravel-production.up.railway.app |

## Despliegue manual desde tu PC

```powershell
# Requiere: npm i -g @railway/cli  y  railway login
railway link
railway up --service nutrikids-flask
railway up --service nutrikids-fastapi
railway up --service nutrikids-laravel
```

## Si no ves los cambios en la web

1. Confirma que el push llegó a GitHub (`main` actualizado).
2. Revisa **Actions** → Deploy Railway (¿verde o error?).
3. En Railway → Flask → **Deployments** → abre logs del último build.
4. Fuerza redeploy: Railway → servicio → **Deploy** → **Redeploy**.
5. Limpia caché del navegador (Ctrl+F5) en Comentarios/Discusiones.
