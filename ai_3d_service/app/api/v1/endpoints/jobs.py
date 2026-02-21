"""
Endpoints para gestión de jobs
"""

from fastapi import APIRouter, HTTPException
from fastapi.responses import JSONResponse
from typing import Optional

from app.services.job_service import JobService

router = APIRouter()

@router.get("/{job_id}")
async def get_job(job_id: str):
    """
    Obtiene información detallada de un job
    """
    try:
        job_service = JobService()
        job = job_service.get_job(job_id)
        
        if not job:
            raise HTTPException(status_code=404, detail="Job no encontrado")
        
        return JSONResponse(job)
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.delete("/{job_id}")
async def delete_job(job_id: str):
    """
    Elimina un job y sus archivos asociados
    """
    try:
        job_service = JobService()
        success = job_service.delete_job(job_id)
        
        if not success:
            raise HTTPException(status_code=404, detail="Job no encontrado")
        
        return JSONResponse({
            "success": True,
            "message": "Job eliminado correctamente"
        })
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/")
async def list_jobs(limit: Optional[int] = 20, offset: Optional[int] = 0):
    """
    Lista jobs recientes
    """
    try:
        job_service = JobService()
        jobs = job_service.list_jobs(limit=limit, offset=offset)
        
        return JSONResponse({
            "jobs": jobs,
            "total": len(jobs)
        })
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
