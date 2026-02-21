"""
Microservicio de IA para generación de modelos 3D
FastAPI application main entry point
"""

# Parche para Python 3.12+: pkgutil.find_loader fue eliminado; trimesh lo usa
import pkgutil
import importlib.util
if not hasattr(pkgutil, "find_loader"):
    def _find_loader(name):
        return importlib.util.find_spec(name)
    pkgutil.find_loader = _find_loader

from fastapi import FastAPI, File, UploadFile, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse, FileResponse
from pydantic import BaseModel
from typing import Optional, List
import uvicorn
import os
from datetime import datetime

from app.core.config import settings
from app.api.v1 import router as api_router
from app.core.logging import setup_logging

# Configurar logging
setup_logging()

# Crear aplicación FastAPI
app = FastAPI(
    title="AI 3D Model Generation Service",
    description="Microservicio para generación de modelos 3D usando IA",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc"
)

# Configurar CORS para permitir peticiones desde PHP
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Incluir routers
app.include_router(api_router, prefix="/api/v1")

@app.get("/")
async def root():
    """Endpoint raíz para verificar que el servicio está activo"""
    return {
        "service": "AI 3D Model Generation Service",
        "status": "active",
        "version": "1.0.0",
        "timestamp": datetime.now().isoformat()
    }

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat()
    }

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_level="info"
    )
