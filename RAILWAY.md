# Desplegar My3DStore en Railway

## 1. Crear proyecto en Railway

1. Entra en [railway.app](https://railway.app) y crea un proyecto.
2. Añade un servicio **MySQL** (desde "New" → "Database" → "MySQL").
3. Añade un servicio desde **GitHub** (conecta tu repo `My3DStore-final` o el que uses).

## 2. Variables de entorno del servicio web

En el servicio que despliega el código (no en MySQL), configura:

| Variable | Valor | Notas |
|----------|--------|--------|
| `DB_HOST` | *(ver abajo)* | Host del servicio MySQL de Railway |
| `DB_PORT` | `3306` | Puerto MySQL |
| `DB_USER` | `root` | Usuario (o el que muestre Railway) |
| `DB_PASS` | *(contraseña)* | Contraseña que te da Railway para MySQL |
| `DB_NAME` | `railway` | Base de datos (por defecto suele ser `railway`) |
| `DOCKER_CONTAINER` | `1` o `true` | Para que la app use rutas base `/` |

En Railway, el servicio MySQL expone variables como `MYSQL_HOST`, `MYSQL_PORT`, etc. Si enlazas el servicio MySQL al servicio web, a veces se inyectan como `MYSQL_URL` o variables sueltas. Usa en el servicio **web** las que te indique Railway (p. ej. `MYSQL_HOST` → entonces en la app tendrías que usar `MYSQL_HOST` si cambias el código, o bien copiar el valor a `DB_HOST` en Variables).

Recomendación: en el panel del servicio MySQL, copia host, puerto, usuario, contraseña y nombre de BD y créalas en el servicio web como `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`.

## 3. Base de datos

Después del primer deploy, crea tablas en MySQL. Opciones:

- **Desde tu máquina:** Conéctate con MySQL Workbench o `mysql` usando el host/puerto público que Railway asigne al MySQL y ejecuta el contenido de `database/schema.sql` (y si usas migraciones, `database/add_orders_payment.php` desde un script local que apunte a esa BD).
- **Desde Railway:** En el servicio MySQL, Railway suele ofrecer "Connect" o un cliente; o ejecuta los SQL desde un one-off job si lo tienes.

Asegúrate de que existan las tablas que usa la app (`users`, `products`, `orders`, `order_items`, `cart_items`, etc.) según tu `schema.sql`.

## 4. Build y deploy

- **Root directory:** Deja el raíz del repo (donde está el `Dockerfile`).
- Railway detectará el `Dockerfile` y construirá la imagen.
- El servicio escuchará en el puerto que Railway asigne (por defecto el contenedor expone 80; Railway hace el proxy).

## 5. Dominio

En "Settings" del servicio web, en "Networking" → "Generate domain", Railway te dará una URL tipo `tu-app.up.railway.app`. Esa será la URL base de la app (con `/` como base; no hace falta `/My3DStore`).

## Resumen de variables (servicio web)

```
DB_HOST=<host del MySQL de Railway>
DB_PORT=3306
DB_USER=root
DB_PASS=<contraseña MySQL>
DB_NAME=railway
DOCKER_CONTAINER=1
```

Con `DOCKER_CONTAINER=1` la aplicación usará `/` como ruta base y los enlaces funcionarán en la URL que te asigne Railway.
