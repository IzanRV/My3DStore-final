@echo off
echo ========================================
echo Iniciando My3DStore con Docker
echo ========================================
echo.

REM Verificar si Docker está corriendo
docker info >nul 2>&1
if errorlevel 1 (
    echo ERROR: Docker Desktop no esta corriendo!
    echo.
    echo Por favor:
    echo 1. Abre Docker Desktop desde el menu de inicio
    echo 2. Espera a que Docker Desktop se inicie completamente
    echo 3. Vuelve a ejecutar este script
    echo.
    pause
    exit /b 1
)

echo Docker Desktop esta corriendo. Continuando...
echo.

REM Verificar si existe .env
if not exist .env (
    echo Creando archivo .env desde .env.example...
    copy .env.example .env
    echo.
    echo IMPORTANTE: Revisa y edita el archivo .env con tus configuraciones
    echo.
    pause
)

echo Construyendo e iniciando contenedores...
docker-compose up -d --build

echo.
echo Esperando a que los servicios esten listos...
timeout /t 5 /nobreak >nul

echo.
echo ========================================
echo Contenedores iniciados!
echo ========================================
echo.
echo Aplicacion: http://localhost:8080
echo Base de datos: localhost:3306
echo.
echo Para ver los logs: docker-compose logs -f
echo Para detener: docker-compose down
echo.
pause
