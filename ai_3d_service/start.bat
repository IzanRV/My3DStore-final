@echo off
REM Script para iniciar el microservicio de IA 3D en Windows

echo Iniciando microservicio de IA 3D...

REM Verificar que Python esté instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo Python no está instalado
    exit /b 1
)

REM Crear entorno virtual si no existe
if not exist venv (
    echo Creando entorno virtual...
    python -m venv venv
)

REM Activar entorno virtual
call venv\Scripts\activate.bat

REM Verificar dependencias (en el venv)
python -c "import fastapi" >nul 2>&1
if errorlevel 1 (
    echo Instalando dependencias...
    pip install -r requirements.txt
)

REM Crear directorios necesarios
if not exist uploads mkdir uploads
if not exist output mkdir output
if not exist cache mkdir cache
if not exist logs mkdir logs

REM Iniciar servidor
echo Servidor iniciando en http://localhost:8000
echo Documentación disponible en http://localhost:8000/docs
echo.

python main.py
