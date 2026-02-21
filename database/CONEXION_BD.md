# Conexión a la base de datos

## Misma base para la web y MySQL Workbench

La **web** (localhost:8081) y **MySQL Workbench** deben usar la **misma** base de datos.

### Dónde está la base de datos

- En **Docker**, el contenedor `my3dstore_db` expone MySQL en el puerto **3308** del host (en `docker-compose.yml` está como `3308:3306`).
- El backend de la web (contenedor `my3dstore_backend`) se conecta al contenedor `db` por la red interna; ese `db` es el mismo MySQL que en tu PC ves como **localhost:3308**.

### Cómo conectar MySQL Workbench (misma base que la web)

| Parámetro   | Valor           |
|------------|------------------|
| **Host**   | `localhost`      |
| **Puerto** | **3308**         |
| **Usuario**| `tienda_user`    |
| **Contraseña** | La de `MYSQL_PASSWORD` en tu `.env` (p. ej. `tienda_password`) |
| **Esquema**| `tienda_3d`      |

Así verás y editarás exactamente los mismos datos que usa la web.

### Si ejecutas scripts PHP en tu PC (fuera de Docker)

Para que scripts como las migraciones usen la misma base (la del puerto 3308), define antes de ejecutar:

- `DB_HOST=127.0.0.1`
- `DB_PORT=3308`
- `DB_USER=tienda_user`
- `DB_PASS=tu_contraseña`
- `DB_NAME=tienda_3d`

En Windows (PowerShell), por ejemplo:

```powershell
$env:DB_HOST="127.0.0.1"; $env:DB_PORT="3308"; $env:DB_USER="tienda_user"; $env:DB_PASS="tienda_password"; $env:DB_NAME="tienda_3d"; php database/migrate_weight_to_dimensions.php
```
