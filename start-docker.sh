#!/bin/bash

echo "========================================"
echo "Iniciando My3DStore con Docker"
echo "========================================"
echo ""

# Verificar si existe .env
if [ ! -f .env ]; then
    echo "Creando archivo .env desde .env.example..."
    cp .env.example .env
    echo ""
    echo "IMPORTANTE: Revisa y edita el archivo .env con tus configuraciones"
    echo ""
    read -p "Presiona Enter para continuar..."
fi

echo "Construyendo e iniciando contenedores..."
docker-compose up -d --build

echo ""
echo "Esperando a que los servicios estén listos..."
sleep 5

echo ""
echo "========================================"
echo "Contenedores iniciados!"
echo "========================================"
echo ""
echo "Aplicación: http://localhost:8080"
echo "Base de datos: localhost:3306"
echo ""
echo "Para ver los logs: docker-compose logs -f"
echo "Para detener: docker-compose down"
echo ""
