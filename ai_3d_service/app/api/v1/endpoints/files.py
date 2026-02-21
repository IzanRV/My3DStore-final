"""
Endpoints para descarga de archivos
"""

from fastapi import APIRouter, HTTPException
from fastapi.responses import FileResponse
import os

from app.core.config import settings
from app.services.job_service import JobService
from app.models.job import JobStatus

router = APIRouter()

@router.get("/download/{job_id}")
async def download_file(job_id: str):
    """
    Descarga el archivo 3D generado
    """
    try:
        job_service = JobService()
        job = job_service.get_job(job_id)
        
        if not job:
            raise HTTPException(status_code=404, detail="Job no encontrado")
        
        if job["status"] != JobStatus.COMPLETED:
            raise HTTPException(
                status_code=400,
                detail=f"Job no completado. Estado actual: {job['status']}"
            )
        
        output_file = job.get("output_file")
        if not output_file:
            raise HTTPException(status_code=404, detail="Archivo no encontrado")
        
        file_path = os.path.join(settings.OUTPUT_DIR, output_file)
        
        if not os.path.exists(file_path):
            raise HTTPException(status_code=404, detail="Archivo no existe en el servidor")
        
        return FileResponse(
            file_path,
            media_type="application/octet-stream",
            filename=output_file
        )
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/preview/{job_id}")
async def preview_file(job_id: str):
    """
    Obtiene información del archivo para previsualización
    """
    try:
        job_service = JobService()
        job = job_service.get_job(job_id)
        
        if not job:
            raise HTTPException(status_code=404, detail="Job no encontrado")
        
        if job["status"] != JobStatus.COMPLETED:
            raise HTTPException(
                status_code=400,
                detail=f"Job no completado. Estado actual: {job['status']}"
            )
        
        output_file = job.get("output_file")
        if not output_file:
            raise HTTPException(status_code=404, detail="Archivo no encontrado")
        
        file_path = os.path.join(settings.OUTPUT_DIR, output_file)
        
        if not os.path.exists(file_path):
            raise HTTPException(status_code=404, detail="Archivo no existe")
        
        file_size = os.path.getsize(file_path)
        file_ext = os.path.splitext(output_file)[1].lower()
        
        return {
            "job_id": job_id,
            "filename": output_file,
            "size": file_size,
            "format": file_ext,
            "download_url": f"/api/v1/files/download/{job_id}",
            "preview_url": f"/api/v1/files/preview/{job_id}" if file_ext in [".glb", ".obj"] else None
        }
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
