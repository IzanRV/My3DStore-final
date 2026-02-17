@echo off
REM Script para iniciar el microservicio de IA 3D en Windows

echo Iniciando microservicio de IA 3D...

REM Activar entorno virtual si existe
if exist venv\Scripts\activate.bat (
    call venv\Scripts\activate.bat
    echo Entorno virtual activado
)

REM Verificar que Python esté instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo Python no está instalado
    exit /b 1
)

REM Verificar dependencias
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
