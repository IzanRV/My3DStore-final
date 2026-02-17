#!/bin/bash
# Script para iniciar el microservicio de IA 3D

echo "🚀 Iniciando microservicio de IA 3D..."

# Activar entorno virtual si existe
if [ -d "venv" ]; then
    source venv/bin/activate
    echo "✅ Entorno virtual activado"
fi

# Verificar que Python esté instalado
if ! command -v python &> /dev/null; then
    echo "❌ Python no está instalado"
    exit 1
fi

# Verificar dependencias
if ! python -c "import fastapi" &> /dev/null; then
    echo "⚠️  Dependencias no instaladas. Instalando..."
    pip install -r requirements.txt
fi

# Crear directorios necesarios
mkdir -p uploads output cache logs

# Iniciar servidor
echo "🌐 Servidor iniciando en http://localhost:8000"
echo "📚 Documentación disponible en http://localhost:8000/docs"
echo ""

python main.py
