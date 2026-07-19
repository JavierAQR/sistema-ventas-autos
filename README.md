# Guía de instalación — Sistema AutoVentas

## ⚠️ Nota sobre arquitectura

El proyecto está pensado a futuro como microservicios (por eso el contenedor
se llama `ms-prospectos`), pero **actualmente todo el backend es un monolito
Laravel** dentro de ese único contenedor: prospectos, vehículos, cotizaciones,
ventas y seguros viven ahí juntos. La separación en microservicios está en
espera. Esta guía refleja el estado actual (monolito).

## 1. Requisitos previos

Cada integrante del equipo debe tener instalado:

- **Docker Desktop** (incluye Docker Compose)
  - Windows: https://www.docker.com/products/docker-desktop
  - Requiere WSL2 habilitado (Docker Desktop lo pide automáticamente en la instalación)
- **Git**
- Al menos **8 GB de RAM libres** (Oracle Database consume bastante)
- Espacio en disco: ~10 GB libres (imagen de Oracle Free pesa varios GB)

## 2. Clonar el repositorio

```bash
git clone <URL_DEL_REPO>
cd sistema-ventas-autos
```

## 3. Archivo de entorno (.env)

El `.env` **no se sube a GitHub** por seguridad (contiene credenciales). Cada quien debe crear el suyo copiando el ejemplo:

```bash
cp backend/ms-prospectos/.env.example backend/ms-prospectos/.env
```

> ⚠️ Si el repo no tiene un `.env.example` todavía, súbelo (sin datos reales) para que el equipo sepa qué variables configurar. Avísame si quieres que te ayude a armarlo.

Variables clave que debe tener el `.env`:

```env
APP_NAME=AutoVentas
APP_ENV=local
APP_KEY=          # se genera en el paso 6
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=oracle
DB_HOST=oracle-db
DB_PORT=1521
DB_DATABASE=FREEPDB1
DB_USERNAME=tu_usuario
DB_PASSWORD=TuPasswordSeguro123
```

**Importante:** `DB_HOST=oracle-db` debe ser el **nombre del servicio** en `docker-compose.yml`, no `localhost` — dentro de la red de Docker, los contenedores se comunican por nombre de servicio, no por IP local.

## 4. Levantar los contenedores

Desde la raíz del proyecto (donde está el `docker-compose.yml`):

```bash
docker compose up -d --build
```

Esto va a:
- Construir la imagen del backend (Laravel + Oracle Instant Client) — tarda varios minutos la primera vez.
- Descargar la imagen de Oracle Database — pesada, puede tardar 10-20 min la primera vez según tu internet.
- Levantar frontend, nginx, n8n y la base de datos.

Verifica que todo esté corriendo:

```bash
docker ps
```

Deberías ver 5 contenedores: `auto_frontend_vue`, `auto_api_gateway`, `auto_ms_prospectos`, `auto_oracle_23ai`, `auto_n8n`.

## 5. Esperar a que Oracle esté listo

Oracle tarda en inicializarse la primera vez (2-5 minutos incluso después de que el contenedor aparezca "Up"). Verifica:

```bash
docker ps
```

Busca que la columna `STATUS` de `auto_oracle_23ai` diga **`(healthy)`**, no solo `Up`. Si dice `(health: starting)`, espera y vuelve a revisar antes de continuar.

## 6. Preparar el backend (primera vez)

Con Oracle ya healthy:

```bash
docker exec -it auto_ms_prospectos bash

# Dentro del contenedor:
php artisan key:generate
php artisan migrate
# Si hay seeders con datos de prueba:
php artisan db:seed

exit
```

## 7. Acceder al sistema

| Servicio | URL |
|---|---|
| Frontend (Vue) | http://localhost:5173 |
| API Gateway (nginx) | http://localhost:8000 |
| Backend directo | http://localhost:8001 |
| n8n | http://localhost:5678 |
| Oracle DB | localhost:1521 (usar cliente SQL para inspeccionar datos) |

El frontend consume la API vía `http://127.0.0.1:8001/api` (revisa tu `services/api.js` o el `baseURL` de axios en cada vista).

## 8. Comandos útiles del día a día

```bash
# Ver logs en vivo (backend)
docker logs -f auto_ms_prospectos

# Ver logs en vivo (frontend) -- útil si Vite no arranca o hay errores de npm
docker logs -f auto_frontend_vue

# Reiniciar el backend tras cambios que no recargan solos (ej: cambios en .env)
docker restart auto_ms_prospectos

# Reiniciar el frontend (normalmente NO hace falta, ver nota abajo)
docker restart auto_frontend_vue

# Entrar a la terminal de un contenedor
docker exec -it auto_ms_prospectos bash
docker exec -it auto_frontend_vue sh

# Correr un comando artisan sin entrar al contenedor
docker exec auto_ms_prospectos php artisan migrate

# Apagar todo
docker compose down

# Apagar todo y borrar volúmenes (⚠️ borra la base de datos)
docker compose down -v
```

**Nota sobre el frontend:** Vite tiene hot-reload — al guardar un `.vue` o `.js`, el navegador se actualiza solo sin reiniciar nada. Solo necesitas `docker restart auto_frontend_vue` en estos casos puntuales:
- Cambiaste el `.env` del frontend o alguna variable `VITE_*`.
- Instalaste una dependencia nueva (`package.json` cambió) y `npm install` no se disparó solo.
- El servidor de Vite quedó "colgado" o dejó de responder en el navegador.

Si instalaste una dependencia nueva y el restart no la toma, fuerza la reinstalación:
```bash
docker exec auto_frontend_vue npm install
docker restart auto_frontend_vue
```

## 9. Problemas comunes

**"No se conecta a la base de datos" / errores OCI8:**
Verifica que Oracle esté `(healthy)` antes de migrar (paso 5). Si sigue fallando, revisa que `DB_HOST=oracle-db` (no `localhost`) en el `.env`.

**Cambios en el código PHP no se reflejan:**
El volumen `./backend/ms-prospectos:/app` monta tu código local dentro del contenedor, así que los cambios deberían verse al instante (Laravel no necesita rebuild para cambios de código). Si no se reflejan, reinicia: `docker restart auto_ms_prospectos`.

**Cambios en `composer.json` o en el Dockerfile no se reflejan:**
Estos sí requieren rebuild:
```bash
docker compose up -d --build ms-prospectos
```

**Puerto ya en uso (5173, 8000, 8001, 1521, 5678):**
Alguna otra app en tu máquina usa ese puerto. Ciérrala o cambia el mapeo en tu copia local del `docker-compose.yml` (ej: `"5174:5173"`) — no lo subas así al repo, para no romper el archivo compartido.

**`npm install` muy lento o falla en frontend:**
```bash
docker compose down
docker compose up -d --build frontend-vue
```

**Error de permisos en Laravel (storage/logs):**
```bash
docker exec auto_ms_prospectos chmod -R 777 storage bootstrap/cache
```

## 10. Flujo de trabajo en equipo

- Cada quien trabaja en su propia rama y hace PR a `main` (o la rama principal que usen).
- Si alguien agrega una migración nueva, avisar al equipo para que corran `php artisan migrate` tras hacer `git pull`.
- El `.env` de cada quien puede tener credenciales/puertos distintos localmente — **nunca subirlo al repo** (debe estar en `.gitignore`).
- Si cambian el `docker-compose.yml` o algún Dockerfile, avisar al equipo para que hagan `docker compose up -d --build` de nuevo.