"""
Configuración del microservicio
"""

import os
from typing import List, Optional
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # Servidor
    HOST: str = os.getenv("HOST", "0.0.0.0")
    PORT: int = int(os.getenv("PORT", "8000"))
    DEBUG: bool = os.getenv("DEBUG", "False").lower() == "true"
    
    # CORS (frontend en Docker: puerto 8081; nombre de servicio 'frontend' para red interna)
    ALLOWED_ORIGINS: List[str] = [
        "http://localhost",
        "http://localhost:80",
        "http://localhost:8080",
        "http://localhost:8081",
        "http://127.0.0.1",
        "http://127.0.0.1:80",
        "http://127.0.0.1:8080",
        "http://127.0.0.1:8081",
        "http://frontend",
        "https://www.thingiverse.com",  # Si necesitas acceso desde otros dominios
    ]
    
    # Directorios
    BASE_DIR: str = os.path.dirname(os.path.dirname(os.path.dirname(__file__)))
    UPLOAD_DIR: str = os.path.join(BASE_DIR, "uploads")
    OUTPUT_DIR: str = os.path.join(BASE_DIR, "output")
    CACHE_DIR: str = os.path.join(BASE_DIR, "cache")
    
    # Redis (para cola de trabajos)
    REDIS_HOST: str = os.getenv("REDIS_HOST", "localhost")
    REDIS_PORT: int = int(os.getenv("REDIS_PORT", "6379"))
    REDIS_DB: int = int(os.getenv("REDIS_DB", "0"))
    
    # Modelos de IA
    TRIPOSR_MODEL_PATH: str = os.getenv("TRIPOSR_MODEL_PATH", "models/triposr")
    SHAPE_E_MODEL_PATH: str = os.getenv("SHAPE_E_MODEL_PATH", "models/shape-e")
    POINT_E_MODEL_PATH: str = os.getenv("POINT_E_MODEL_PATH", "models/point-e")

    # API Externa - Meshy.ai (https://www.meshy.ai/)
    MESHY_API_KEY: Optional[str] = os.getenv("MESHY_API_KEY", None)

    # API Externa - Tripo3D (https://www.tripo3d.ai/)
    TRIPO3D_API_KEY: Optional[str] = os.getenv("TRIPO3D_API_KEY", None)
    
    # Límites
    MAX_FILE_SIZE: int = 10 * 1024 * 1024  # 10MB
    MAX_IMAGES: int = 5
    TIMEOUT_SECONDS: int = 300  # 5 minutos
    
    # Almacenamiento
    STORAGE_TYPE: str = os.getenv("STORAGE_TYPE", "local")  # local o s3
    S3_BUCKET: str = os.getenv("S3_BUCKET", "")
    S3_REGION: str = os.getenv("S3_REGION", "us-east-1")
    AWS_ACCESS_KEY_ID: str = os.getenv("AWS_ACCESS_KEY_ID", "")
    AWS_SECRET_ACCESS_KEY: str = os.getenv("AWS_SECRET_ACCESS_KEY", "")
    
    # Seguridad
    API_KEY: Optional[str] = os.getenv("API_KEY", None)

    # Logging
    LOG_LEVEL: str = os.getenv("LOG_LEVEL", "INFO")

    class Config:
        env_file = ".env"
        case_sensitive = True

settings = Settings()

# Crear directorios necesarios
os.makedirs(settings.UPLOAD_DIR, exist_ok=True)
os.makedirs(settings.OUTPUT_DIR, exist_ok=True)
os.makedirs(settings.CACHE_DIR, exist_ok=True)
