# pgAdmin — NutriKids

`servers.json` se monta en el contenedor `nutrikids_pgadmin` y precarga el servidor
PostgreSQL del proyecto para que no haya que darlo de alta manualmente.

**No contiene contraseñas por diseño.** pgAdmin las pedirá en la primera conexión y
las guardará cifradas en el volumen `pgadmin_data`.

Si cambias `POSTGRES_USER` en `.env`, actualiza también el campo `Username` de este
archivo y reinicia el servicio:

```bash
docker compose up -d --force-recreate pgadmin
```
