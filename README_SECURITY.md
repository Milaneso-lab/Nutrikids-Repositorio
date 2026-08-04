# Reporte de Análisis e Implementaciones de Seguridad - NutriKids

Este archivo contiene el reporte del **Análisis de Seguridad de Puertos y Endpoints** y las **Implementaciones de Seguridad** realizadas en el proyecto integrador (PI) **NutriKids**. Con base en este documento se puede estructurar el reporte PDF definitivo de evidencias.

---

## 1. Análisis de Seguridad por Puertos (Servicios)

El sistema NutriKids está compuesto por cuatro servicios interconectados en Docker. A continuación se dictaminan sus necesidades de seguridad:

| Puerto | Servicio | Protocolo / Uso | Dictamen y Necesidades de Seguridad | Estado |
| :--- | :--- | :--- | :--- | :--- |
| **5432** | PostgreSQL | SQL (Base de Datos) | **Sensible / Crítico.** Almacena datos médicos, PII e inicios de sesión. **Nunca debe exponerse al exterior en producción.** Solo debe ser accesible a través de la red interna de Docker (`app-network`). Las contraseñas deben ser fuertes y guardarse cifradas en variables de entorno. | **Protegido** (Interno en Docker) |
| **8000** | FastAPI | HTTP (API Backend) | **Público / Expuesto.** Sirve como motor del sistema. Requiere autenticación JWT, políticas CORS estrictas para mitigar llamadas desde orígenes no autorizados, inyección de cabeceras de seguridad HTTP y protección contra fuerza bruta/DDoS (Rate Limiting). | **Implementado** (CORS, Headers, Rate Limiting) |
| **8080** | Laravel | HTTP (Dashboard Admin/Nutriólogo) | **Público / Expuesto.** Maneja la gestión administrativa. Requiere autenticación por sesión segura, protección estricta contra CSRF (Cross-Site Request Forgery) en formularios y políticas de cookies de sesión seguras. | **Protegido** (Middleware de Laravel) |
| **5000** | Flask | HTTP (Portal Público de Padres) | **Público / Expuesto.** Interfaz de cara a los usuarios finales. Requiere cabeceras de seguridad HTTP para mitigar clickjacking/XSS, y cookies de sesión seguras (`HttpOnly`, `SameSite=Lax`) para evitar el robo de tokens. | **Implementado** (Secure Cookies & Headers) |

---

## 2. Análisis y Auditoría de Endpoints (API de FastAPI)

Se realizó un análisis exhaustivo de cada endpoint disponible en la API para determinar su nivel de riesgo y aplicar las correspondientes medidas de mitigación:

### A. Gestión de Autenticación y Usuarios
*   `POST /api/auth/login`
    *   *Riesgo:* Alto. Vulnerable a ataques de fuerza bruta (adivinación de contraseñas).
    *   *Mitigación:* Se implementó un **limitador de tasa (Rate Limiter)** que restringe las peticiones por dirección IP a un máximo de 5 intentos por minuto.
*   `POST /api/auth/register`
    *   *Riesgo:* Medio-Alto. Vulnerable a spamming de creación de cuentas (DoS en BD).
    *   *Mitigación:* Se restringió el registro exclusivo para el rol `padre` y se implementó limitación de tasa (máximo 3 registros por minuto por IP).
*   `GET /api/usuarios` y `POST /api/usuarios`
    *   *Riesgo:* Alto. Acceso administrativo a datos de usuarios.
    *   *Mitigación:* Protegidos estrictamente por la dependencia `require_roles("admin")`.

### B. Formularios de Contacto
*   `POST /api/contactos`
    *   *Riesgo:* Medio. Vulnerable a spam de correo y saturación de base de datos.
    *   *Mitigación:* Implementación de Rate Limiting (máximo 3 envíos por minuto por IP).
*   `GET /api/contactos` **(VULNERABILIDAD CRÍTICA CORREGIDA)**
    *   *Riesgo:* **Crítico**. Anteriormente era un endpoint público que retornaba una lista con la información personal (nombres, teléfonos, correos y mensajes) de todas las personas que habían enviado un formulario de contacto.
    *   *Mitigación:* Se restringió el endpoint agregándole validación de rol. Ahora requiere estrictamente rol de `admin` o `nutriologo` mediante la cabecera `Authorization: Bearer <token>`.

### C. Foro y Comentarios Públicos
*   `GET /api/comentarios` y `GET /api/discusiones`
    *   *Riesgo:* Bajo. Lecturas públicas necesarias para la UI. No se requieren cambios.
*   `POST /api/comentarios`
    *   *Riesgo:* Bajo-Medio. Requiere inicio de sesión como padre. Se valida que el `id_usuario` en el cuerpo coincida con el usuario del token JWT para evitar suplantación de identidad.
*   `POST /api/discusiones`
    *   *Riesgo:* Bajo-Medio. Protegido por JWT (`get_current_user`). Solo el rol `padre` puede iniciar discusiones.
*   `PUT /api/discusiones/{id}` y `DELETE /api/discusiones/{id}`
    *   *Riesgo:* Medio. Modificación y eliminación de foros.
    *   *Mitigación:* Validación estricta de propiedad. Solo el creador de la discusión o un usuario `admin` puede editar o eliminarla.

### D. Datos Médicos y Pacientes (Acceso Dietistas)
*   `GET/POST/PUT /api/pacientes`
    *   *Riesgo:* Alto. Información médica infantil (PII altamente sensible).
    *   *Mitigación:* Protegidos estrictamente con la dependencia `require_roles("nutriologo", "admin")`.
*   `GET/POST/PUT /api/evaluaciones`, `GET/POST/PUT /api/menus`, `GET/POST /api/reportes`
    *   *Riesgo:* Alto. Planes alimenticios e historiales de salud.
    *   *Mitigación:* Protegidos estrictamente con la dependencia `require_roles("nutriologo", "admin")`.

### E. Citas / Agenda
*   `POST /api/citas`
    *   *Riesgo:* Medio. Petición de citas. Validado para que solo el rol `padre` las solicite, y la fecha preferida debe ser actual o futura.
*   `GET /api/citas`
    *   *Riesgo:* Medio-Alto. Fuga de datos de agenda médica.
    *   *Mitigación:* Los padres no pueden consumir este endpoint directamente. Los administradores ven todas las citas y los nutriólogos ven las citas que tienen asignadas o pendientes de tomar.
*   `PATCH /api/citas/{id}/asignar` y `PATCH /api/citas/{id}/estado`
    *   *Riesgo:* Alto. Modificación de citas. Protegido para `admin` exclusivamente.
*   `POST /api/citas/{id}/tomar`
    *   *Riesgo:* Alto. Protegido para `nutriologo` exclusivamente.

---

## 3. Detalle de las Implementaciones de Seguridad Realizadas

### A. Limitador de Tasa en Memoria (Rate Limiting)
Se diseñó un mecanismo de limitación de tasa basado en el algoritmo de ventana deslizante por IP en [fastapi/deps.py](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/fastapi/deps.py) sin dependencias de base de datos externas:
*   **Funcionamiento:** Recupera la IP del cliente (procesando cabeceras `X-Forwarded-For` para soporte detrás de proxies de Docker). Almacena las marcas de tiempo en un diccionario en memoria y bloquea peticiones subsecuentes cuando se excede el umbral devolviendo un error HTTP `429 Too Many Requests`.
*   **Aplicado en:**
    *   `POST /api/auth/login` (5 solicitudes / 60 segundos)
    *   `POST /api/auth/register` (3 solicitudes / 60 segundos)
    *   `POST /api/contactos` (3 solicitudes / 60 segundos)

### B. Protección de Endpoints Sensibles (RBAC)
*   **GET /api/contactos:** Modificado en [fastapi/routers/contactos.py](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/fastapi/routers/contactos.py) para inyectar la dependencia `require_roles("admin", "nutriologo")`. Intentar acceder sin token JWT o con rol de "padre" retornará `401 Unauthorized` o `403 Forbidden` respectivamente.

### C. CORS y Cabeceras de Seguridad HTTP en la API (FastAPI)
Configurado en [fastapi/main.py](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/fastapi/main.py):
*   **CORS Restringido:** Se configuró el middleware de CORS para aceptar únicamente solicitudes provenientes del portal público de padres (Flask en puerto 5000) y de los paneles de administración (Laravel en puerto 8080).
*   **Cabeceras de Seguridad:** Inyección automática en cada respuesta HTTP de:
    *   `X-Content-Type-Options: nosniff` (previene ataques de sniffing de tipos de archivos).
    *   `X-Frame-Options: DENY` (evita ataques de clickjacking ocultando la web dentro de iframes).
    *   `X-XSS-Protection: 1; mode=block` (activa filtros de scripting de los navegadores).
    *   `Referrer-Policy: strict-origin-when-cross-origin` (previene fugas de datos en URLs de referencia).

### D. Seguridad de Sesión y Cabeceras en el Portal Frontend (Flask)
Configurado en [flask/app.py](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/flask/app.py):
*   **Protección de Cookies de Sesión:** Se forzó la cabecera `SESSION_COOKIE_HTTPONLY = True` para evitar que scripts maliciosos de JavaScript accedan a la cookie de sesión del usuario. La directiva `SESSION_COOKIE_SAMESITE` se mantiene en `Lax` y se configuró soporte para habilitar `SESSION_COOKIE_SECURE` en entornos de producción con HTTPS.
*   **Cabeceras de Seguridad:** Se configuró un manejador `@app.after_request` para inyectar las mismas cabeceras de seguridad HTTP que en la API backend.

---

## 4. Direcciones de Endpoints Modificados para Evidencias (Capturas de Pantalla)

Para la elaboración del reporte con evidencias en PDF, utiliza las siguientes rutas y métodos de prueba (pueden ser consumidos con Postman, Insomnia o `curl`):

### 1. Evidencia de Rate Limiting (Bloqueo 429)
*   **Endpoint:** `POST http://localhost:8000/api/auth/login`
*   **Acción:** Realiza 6 peticiones consecutivas en menos de un minuto enviando un cuerpo JSON de prueba, por ejemplo:
    ```json
    {
      "email": "padre@example.com",
      "contrasena": "password123"
    }
    ```
*   **Resultado esperado (Captura de pantalla):** La petición número 6 debe retornar un código de estado HTTP **`429 Too Many Requests`** con el cuerpo:
    ```json
    {
      "detail": "Demasiadas solicitudes. Por favor, intente de nuevo más tarde."
    }
    ```

### 2. Evidencia de Protección contra Fuga de Datos (Bloqueo 401/403)
*   **Endpoint:** `GET http://localhost:8000/api/contactos`
*   **Acción 1:** Realiza una petición `GET` directa sin incluir cabeceras de autorización.
*   **Resultado esperado (Captura):** Código de estado HTTP **`401 Unauthorized`** con el cuerpo:
    ```json
    {
      "detail": "Token requerido"
    }
    ```
*   **Acción 2:** Realiza una petición `GET` incluyendo la cabecera `Authorization: Bearer <token_de_padre>` (generado al loguearse con un usuario con rol padre).
*   **Resultado esperado (Captura):** Código de estado HTTP **`403 Forbidden`** con el cuerpo:
    ```json
    {
      "detail": "No autorizado"
    }
    ```

### 3. Evidencia de Balanceo de Carga (Réplicas en Railway)
*   **Servicio:** `nutrikids-fastapi` en Railway (Settings → Scale → 2 réplicas).
*   **Acción:** Realiza varias peticiones consecutivas a `GET https://nutrikids-sitioweb.up.railway.app/health`.
*   **Resultado esperado (Captura):** En los logs de Railway (`Deployments` → `View Logs`, o `railway logs`), las peticiones aparecen servidas por distintos valores de la variable de entorno `RAILWAY_REPLICA_ID` — evidencia de que Railway está distribuyendo el tráfico entre réplicas (balanceo de carga nativo). Alternativa local equivalente: `docker compose -f docker-compose.yml -f docker-compose.infra.yml --profile gateway up -d` y observar `upstream=` en los logs de `nutrikids_gateway` (nginx `least_conn` entre `fastapi`/`fastapi-b`).

### 4. Evidencia de Monitoreo (Prometheus + Grafana en Railway)
*   **Servicios:** `nutrikids-prometheus` (privado, solo red interna) y `nutrikids-grafana` (dominio público) en el mismo proyecto Railway.
*   **Acción:** Abrir la URL pública de `nutrikids-grafana`, iniciar sesión y abrir el dashboard **NutriKids Overview**.
*   **Resultado esperado (Captura):** Métricas reales de la API (latencia, tasa de peticiones, códigos de estado) scrapeadas desde `nutrikids-fastapi.railway.internal:8000/metrics` vía Prometheus, sin exponer Prometheus públicamente. Alternativa local equivalente: `docker compose -f docker-compose.yml -f docker-compose.infra.yml --profile monitoring up -d` y abrir `http://localhost:3000`.

### 5. Evidencia de Cabeceras de Seguridad HTTP (Headers en Respuesta)
*   **Endpoint:** `GET http://localhost:8000/health` (FastAPI) o `GET http://localhost:5000/` (Flask)
*   **Acción:** Realiza una petición `GET` simple y revisa la sección **Headers** (Cabeceras) de la respuesta del servidor.
*   **Resultado esperado (Captura):** Visualización de las cabeceras de seguridad inyectadas:
    ```http
    X-Content-Type-Options: nosniff
    X-Frame-Options: DENY
    X-XSS-Protection: 1; mode=block
    Referrer-Policy: strict-origin-when-cross-origin
    ```

---

## 5. Seguridad y Monitoreo de la App Móvil (Expo / React Native)

La app móvil (`NutriKidsMovil/`) incorpora dos capas nuevas, ambas *self-contained* (sin servicios de terceros):

### A. Firewall de Red del Cliente
Implementado en [`NutriKidsMovil/src/core/security/networkFirewall.ts`](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/NutriKidsMovil/src/core/security/networkFirewall.ts) y conectado en el interceptor de peticiones de [`src/core/api/client.ts`](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/NutriKidsMovil/src/core/api/client.ts):
*   **Funcionamiento:** Antes de que cualquier petición HTTP salga del dispositivo, se valida el host contra una allowlist (backend propio en Railway, IPs privadas/emulador para desarrollo, túneles ngrok). Si el host no está autorizado, la petición se bloquea con un error y el intento queda registrado.
*   **Endurecimiento adicional:** `usesCleartextTraffic` en [`app.config.ts`](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/NutriKidsMovil/app.config.ts) ya no es `true` fijo — se deshabilita automáticamente cuando la app está compilada contra una URL de API HTTPS (Railway), permitiendo tráfico sin cifrar solo en el cliente de desarrollo local (LAN/emulador).

### B. Monitoreo de Diagnóstico en Dispositivo
Implementado en [`NutriKidsMovil/src/core/monitoring/diagnostics.ts`](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/NutriKidsMovil/src/core/monitoring/diagnostics.ts):
*   Buffer acotado (últimos 100 eventos) de errores de red y de eventos del firewall (host permitido/bloqueado), alimentado desde `client.ts` y `errorHandler.ts`. A diferencia de la implementación anterior (solo `console.error` en `__DEV__`), este registro también existe en builds de producción, disponible para diagnóstico/soporte.

### Verificación
*   Pruebas unitarias: [`src/core/security/__tests__/networkFirewall.test.ts`](file:///c:/Uni%20-%20Milaneso/NutriKids_dividido%20%281%29/NutriKids_dividido/NutriKids/NutriKidsMovil/src/core/security/__tests__/networkFirewall.test.ts) — `cd NutriKidsMovil && npm test`.
*   Evidencia manual: con la app corriendo, intentar forzar `EXPO_PUBLIC_API_BASE_URL` a un host fuera de la allowlist y confirmar que las peticiones son rechazadas por el firewall (log `[Firewall:BLOCKED]` en modo dev).
