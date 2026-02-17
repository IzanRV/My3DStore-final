# Dockerización de My3DStore

Este proyecto ha sido dockerizado para funcionar en contenedores Docker separados.

## Estructura de Contenedores

- **web**: Contenedor PHP/Apache que sirve la aplicación
- **db**: Contenedor MySQL para la base de datos
- **python**: Contenedor Python opcional para scripts de generación 3D (puede ejecutarse desde web)

## Requisitos Previos

- Docker
- Docker Compose

## Configuración Inicial

1. Copia el archivo `.env.example` a `.env`:
```bash
cp .env.example .env
```

2. Edita el archivo `.env` con tus configuraciones si es necesario.

3. Asegúrate de que los archivos de configuración existan:
   - `config/database.php` (se crea automáticamente desde `.example`)
   - `config/tripo3d.php` (si usas Tripo3D)
   - `config/meshy.php` (si usas Meshy)
   - `config/replicate.php` (si usas Replicate)

## Iniciar los Contenedores

```bash
# Construir e iniciar todos los contenedores
docker-compose up -d

# Ver logs
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f web
docker-compose logs -f db
```

## Acceder a la Aplicación

Una vez iniciados los contenedores, accede a:
- **Aplicación**: http://localhost:8080
- **Base de datos**: localhost:3306

## Comandos Útiles

```bash
# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes (¡CUIDADO! Esto elimina la base de datos)
docker-compose down -v

# Reconstruir contenedores
docker-compose up -d --build

# Ejecutar comandos en el contenedor web
docker-compose exec web bash

# Ejecutar comandos en el contenedor de base de datos
docker-compose exec db mysql -u tienda_user -p tienda_3d

# Ver estado de los contenedores
docker-compose ps
```

## Base de Datos

La base de datos se inicializa automáticamente con el esquema en `database/schema.sql` cuando se crea el contenedor por primera vez.

### Credenciales por defecto:
- Usuario: `tienda_user`
- Contraseña: `tienda_password` (configurable en `.env`)
- Base de datos: `tienda_3d`

### Usuario administrador por defecto:
- Email: `admin@tienda3d.com`
- Contraseña: `admin123`

## Scripts Python

Los scripts Python (`shape_e_generate.py` y `tripo3d_generate.py`) se ejecutan desde el contenedor `web` que tiene Python 3 instalado.

### Notas sobre Shap-E:
- Shap-E requiere GPU con CUDA para funcionar correctamente
- Si necesitas GPU, considera usar `nvidia-docker` o ejecutar los scripts en el host
- El contenedor Python está disponible pero puede no tener acceso a GPU

### Instalar dependencias Python en el contenedor:

```bash
# Entrar al contenedor web
docker-compose exec web bash

# Instalar dependencias (ejemplo para Tripo3D)
pip3 install tripo3d

# Para Shap-E (requiere GPU/CUDA, mejor en host)
# pip3 install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118
# pip3 install shap-e trimesh
```

## Solución de Problemas

### La aplicación no se conecta a la base de datos
- Verifica que el contenedor `db` esté corriendo: `docker-compose ps`
- Verifica las variables de entorno en `.env`
- Revisa los logs: `docker-compose logs db`

### Los scripts Python no funcionan
- Verifica que Python esté instalado: `docker-compose exec web python3 --version`
- Instala las dependencias necesarias en el contenedor
- Revisa los logs: `docker-compose logs web`

### Permisos de archivos
Si tienes problemas con permisos de archivos generados:
```bash
docker-compose exec web chown -R www-data:www-data /var/www/html/public/glb/generated
docker-compose exec web chown -R www-data:www-data /var/www/html/public/stl/generated
```

## Migración desde WAMP

1. Exporta tu base de datos desde WAMP:
```bash
mysqldump -u root -p tienda_3d > backup.sql
```

2. Inicia los contenedores Docker:
```bash
docker-compose up -d
```

3. Importa la base de datos:
```bash
docker-compose exec -T db mysql -u tienda_user -ptienda_password tienda_3d < backup.sql
```

O copia el archivo SQL a `database/schema.sql` y reinicia el contenedor.

## Desarrollo

Para desarrollo, los archivos se montan como volúmenes, por lo que los cambios se reflejan inmediatamente sin necesidad de reconstruir los contenedores.

## Producción

Para producción, considera:
- Usar variables de entorno seguras
- Configurar SSL/TLS
- Usar un servidor web reverse proxy (nginx)
- Configurar backups de la base de datos
- Optimizar las imágenes Docker
