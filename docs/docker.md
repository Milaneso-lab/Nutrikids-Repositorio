# NutriKids Docker Setup

Este proyecto dockeriza la aplicación NutriKids con tres servicios: Laravel (admin/nutriólogos), Flask (usuarios padres) y FastAPI (API backend).

## Servicios

- **PostgreSQL**: Base de datos compartida en `127.0.0.1:5432` (contenedor `nutrikids_postgres`)
- **pgAdmin**: Consola de administración de la base en http://localhost:5050
- **Redis**: Caché y rate limiting (sólo red interna)
- **Laravel**: Panel administrativo en http://localhost:8080
- **Flask**: Frontend para padres en http://localhost:5000
- **FastAPI**: API backend en http://localhost:8000

Detalle completo de la capa de datos, modelo entidad-relación y procedimientos
de respaldo: [`infraestructura-datos-postgresql.md`](infraestructura-datos-postgresql.md).

## Inicio rápido

1. Asegúrate de tener Docker y Docker Compose instalados.

2. Desde la raíz del proyecto, ejecuta:
   ```bash
   docker-compose up -d
   ```

3. Espera a que los contenedores se levanten. PostgreSQL debe estar healthy primero.

4. Accede a las aplicaciones:
   - Laravel: http://localhost:8080
   - Flask: http://localhost:5000
   - FastAPI docs: http://localhost:8000/docs
   - pgAdmin: http://localhost:5050 (credenciales `PGADMIN_DEFAULT_EMAIL` / `PGADMIN_DEFAULT_PASSWORD`)

## Estructura de la base de datos

La base de datos PostgreSQL contiene todas las tablas de ambos frameworks:

### Tablas principales
- `usuarios`: Usuarios de ambos sistemas (padres, admins, nutriólogos)
- `pacientes`: Pacientes gestionados por nutriólogos
- `evaluaciones`, `menus`, `reportes`: Datos de pacientes
- `contactos`, `comentarios`, `discusiones`: Interacciones de usuarios
- `citas`, `alertas`, `alergias`, `notas_nutriologo`, `menus_semanales`: Funcionalidades de Laravel
- `infantes`: Tabla adicional de Laravel

### Credenciales de BD

Todas provienen del archivo `.env` de la raíz; ninguna está escrita en el repositorio.

| Parámetro | Variable | Valor por defecto |
|-----------|----------|-------------------|
| Base de datos | `POSTGRES_DB` | `nutrikids` |
| Usuario | `POSTGRES_USER` | `nutrikids_user` |
| Contraseña | `POSTGRES_PASSWORD` | sin valor por defecto (obligatoria) |
| Host | — | `postgres` desde contenedores, `127.0.0.1:5432` desde el host |

## Verificación post-despliegue

Después de ejecutar `docker-compose up -d`, verifica que todo funcione:

1. **Verificar contenedores:**
   ```bash
   docker-compose ps
   ```

2. **Verificar logs:**
   ```bash
   docker-compose logs postgres  # Debe mostrar "database system is ready"
   docker-compose logs fastapi   # Debe crear tablas sin errores
   docker-compose logs laravel  # Debe iniciar Apache sin errores
   docker-compose logs flask    # Debe iniciar Flask sin errores
   ```

3. **Verificar base de datos:**
   ```bash
   python check_db.py
   ```
   Debe mostrar todas las tablas y 0 registros (ya que se creó desde 0).

4. **Probar endpoints:**
   - FastAPI health: `curl http://localhost:8000/health`
   - Laravel: `curl http://localhost:8080` (debe devolver HTML)
   - Flask: `curl http://localhost:5000` (debe devolver HTML)

5. **Acceder a las aplicaciones:**
   - Laravel (admin): http://localhost:8080
   - Flask (padres): http://localhost:5000
   - FastAPI docs: http://localhost:8000/docs

## Solución de problemas

- Si PostgreSQL no inicia: `docker-compose logs postgres`
- Si FastAPI falla: Verificar DATABASE_URL en docker-compose.yml
- Si Laravel falla: Verificar .env de Laravel y permisos de storage
- Si Flask falla: Verificar variables de entorno en docker-compose.yml

## Limpieza

Para detener y eliminar todo:
```bash
docker-compose down -v  # -v elimina volúmenes
```