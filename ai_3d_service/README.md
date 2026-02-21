# Microservicio de IA para Generación de Modelos 3D

Microservicio profesional desarrollado en Python con FastAPI para generar modelos 3D usando inteligencia artificial.

## Características

- ✅ Generación 3D desde texto (usando Shap-E, Point-E, Meshy, Tripo3D)
- ✅ Generación 3D desde imágenes (usando TripoSR)
- ✅ Conversión automática de formatos (OBJ → STL/GLB)
- ✅ Sistema de cola de trabajos asíncronos
- ✅ API RESTful completa
- ✅ Integración con PHP
- ✅ Soporte para múltiples formatos (STL, OBJ, GLB)
- ✅ Previsualización de modelos 3D

## Requisitos

- Python 3.9+
- pip
- (Opcional) CUDA para aceleración GPU
- (Opcional) Redis para cola de trabajos

## Instalación

1. **Clonar o navegar al directorio del servicio:**

```bash
cd ai_3d_service
```

2. **Crear entorno virtual:**

```bash
python -m venv venv
source venv/bin/activate  # En Windows: venv\Scripts\activate
```

3. **Instalar dependencias:**

```bash
pip install -r requirements.txt
```

4. **Configurar variables de entorno:**

```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

5. **Instalar modelos de IA (opcional):**

Para usar los modelos reales, necesitas instalar las librerías correspondientes:

```bash
# Shap-E
pip install shap-e

# Point-E
pip install point-e

# TripoSR
# Seguir instrucciones en: https://github.com/VAST-AI-Research/TripoSR
```

## Uso

### Iniciar el servidor

```bash
python main.py
```

O usando uvicorn directamente:

```bash
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

El servicio estará disponible en: `http://localhost:8000`

### Documentación de la API

Una vez iniciado el servidor, puedes acceder a:
- **Swagger UI**: `http://localhost:8000/docs`
- **ReDoc**: `http://localhost:8000/redoc`

## Endpoints Principales

### Generación desde Texto

```bash
POST /api/v1/generate/text-to-3d
Content-Type: application/json

{
  "prompt": "a red sports car",
  "model_type": "shap-e",
  "output_format": "stl",
  "quality": "medium"
}
```

### Generación desde Imagen

```bash
POST /api/v1/generate/image-to-3d
Content-Type: multipart/form-data

files: [imagen1.jpg, imagen2.jpg]
model_type: triposr
output_format: stl
quality: medium
```

### Consultar Estado

```bash
GET /api/v1/generate/status/{job_id}
```

### Descargar Archivo

```bash
GET /api/v1/files/download/{job_id}
```

## Integración con PHP

El servicio incluye un cliente PHP (`includes/AI3DService.php`) que puedes usar así:

```php
require_once 'includes/AI3DService.php';

$service = new AI3DService('http://localhost:8000');

// Generar desde texto
$result = $service->generateFromText('a wooden chair', 'stl');

// Esperar a que termine
$status = $service->waitForJob($result['job_id']);

// Descargar archivo
$service->downloadFile($result['job_id'], '/path/to/save/model.stl');
```

## Estructura del Proyecto

```
ai_3d_service/
├── main.py                 # Punto de entrada
├── app/
│   ├── api/
│   │   └── v1/
│   │       └── endpoints/  # Endpoints de la API
│   ├── core/
│   │   ├── config.py       # Configuración
│   │   └── logging.py       # Logging
│   ├── models/
│   │   └── job.py          # Modelos de datos
│   └── services/
│       ├── ai_models/      # Servicios de modelos IA
│       ├── generation_service.py
│       ├── job_service.py
│       └── converter_service.py
├── uploads/                 # Imágenes subidas
├── output/                 # Modelos generados
├── cache/                  # Cache y jobs
└── requirements.txt
```

## Configuración

Edita el archivo `.env` para configurar:

- **HOST/PORT**: Dirección y puerto del servidor
- **ALLOWED_ORIGINS**: Dominios permitidos para CORS
- **REDIS**: Configuración de Redis (opcional)
- **STORAGE_TYPE**: Almacenamiento local o S3
- **API_KEY**: Clave de API para autenticación (opcional)

## Notas Importantes

1. **Modelos de IA**: Los servicios incluyen implementaciones placeholder. Para usar los modelos reales, necesitas:
   - Instalar las librerías correspondientes
   - Descargar los modelos pre-entrenados
   - Configurar las rutas en `.env`

2. **GPU**: Para mejor rendimiento, se recomienda usar GPU con CUDA. El servicio detecta automáticamente si CUDA está disponible.

3. **Producción**: Para producción, considera:
   - Usar un servidor WSGI como Gunicorn
   - Configurar Redis para cola de trabajos
   - Implementar autenticación con API keys
   - Configurar almacenamiento en S3
   - Usar un reverse proxy (nginx)

## Solución de Problemas

### El servicio no inicia

- Verifica que Python 3.9+ esté instalado
- Asegúrate de que todas las dependencias estén instaladas
- Revisa los logs en `logs/`

### Error al generar modelos

- Verifica que los modelos de IA estén instalados correctamente
- Revisa que haya suficiente espacio en disco
- Verifica los permisos de escritura en `output/` y `uploads/`

### Problemas de CORS

- Agrega tu dominio a `ALLOWED_ORIGINS` en `.env`
- Verifica que el servicio esté accesible desde tu aplicación PHP

## Licencia

Este proyecto está diseñado para uso en tu aplicación My3DStore.
