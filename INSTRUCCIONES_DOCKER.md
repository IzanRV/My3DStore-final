# Instrucciones para Iniciar My3DStore con Docker

## ⚠️ IMPORTANTE: Antes de empezar

**Docker Desktop debe estar ejecutándose** en Windows.

## Paso 1: Iniciar Docker Desktop

1. Abre Docker Desktop desde el menú de inicio
2. Espera a que Docker Desktop se inicie completamente (verás el ícono de Docker en la bandeja del sistema)
3. Verifica que Docker esté corriendo: deberías ver "Docker Desktop is running" en la interfaz

## Paso 2: Verificar Docker

Abre PowerShell y ejecuta:
```powershell
docker --version
docker-compose --version
```

Si ambos comandos funcionan, Docker está listo.

## Paso 3: Configurar el proyecto

1. Copia el archivo `.env.example` a `.env`:
```powershell
cp .env.example .env
```

2. (Opcional) Edita `.env` si necesitas cambiar las configuraciones por defecto

## Paso 4: Iniciar los contenedores

Ejecuta uno de estos comandos:

**Opción A - Script automático:**
```powershell
.\start-docker.bat
```

**Opción B - Manual:**
```powershell
docker-compose up -d --build
```

## Paso 5: Verificar que todo funciona

1. Espera unos segundos para que los contenedores se inicien
2. Verifica el estado:
```powershell
docker-compose ps
```

Deberías ver:
- `my3dstore_db` - Estado: Up
- `my3dstore_web` - Estado: Up

3. Accede a la aplicación: http://localhost:8080

## Solución de Problemas

### Error: "El sistema no puede encontrar el archivo especificado"
**Solución:** Docker Desktop no está corriendo. Inicia Docker Desktop y espera a que esté completamente iniciado.

### Error: "unable to get image"
**Solución:** 
1. Verifica que Docker Desktop esté corriendo
2. Verifica tu conexión a internet (Docker necesita descargar las imágenes)
3. Intenta de nuevo: `docker-compose up -d --build`

### Error: "port is already allocated"
**Solución:** El puerto 8080 o 3306 ya está en uso. Cambia el puerto en `.env`:
```
WEB_PORT=8081
MYSQL_PORT=3307
```

### Los contenedores no inician
**Solución:** Revisa los logs:
```powershell
docker-compose logs
```

### La aplicación no se conecta a la base de datos
**Solución:**
1. Verifica que el contenedor `db` esté corriendo: `docker-compose ps`
2. Espera unos segundos más (MySQL tarda en iniciar)
3. Revisa los logs: `docker-compose logs db`

## Comandos Útiles

```powershell
# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f web
docker-compose logs -f db

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes (¡CUIDADO! Elimina la base de datos)
docker-compose down -v

# Reconstruir contenedores
docker-compose up -d --build

# Ver estado de contenedores
docker-compose ps

# Entrar al contenedor web
docker-compose exec web bash

# Entrar a MySQL
docker-compose exec db mysql -u tienda_user -ptienda_password tienda_3d
```

## Verificar que Docker Desktop está corriendo

En PowerShell:
```powershell
docker info
```

Si ves información sobre Docker, está funcionando. Si ves un error sobre "cannot connect", Docker Desktop no está corriendo.
