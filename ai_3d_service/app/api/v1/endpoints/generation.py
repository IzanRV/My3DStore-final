"""
Endpoints para generación de modelos 3D
"""

from fastapi import APIRouter, File, UploadFile, HTTPException, BackgroundTasks, Form
from fastapi.responses import JSONResponse
from typing import List, Optional
from pydantic import BaseModel
import uuid
import os
from datetime import datetime

from app.core.config import settings
from app.services.generation_service import GenerationService
from app.services.job_service import JobService
from app.models.job import JobStatus

router = APIRouter()

class TextTo3DRequest(BaseModel):
    prompt: str
    model_type: Optional[str] = "shap-e"  # shap-e, point-e, triposr, meshy, tripo3d
    output_format: Optional[str] = "stl"  # stl, obj, glb
    quality: Optional[str] = "medium"  # low, medium, high

class ImageTo3DRequest(BaseModel):
    model_type: Optional[str] = "triposr"  # triposr para imágenes
    output_format: Optional[str] = "stl"
    quality: Optional[str] = "medium"

@router.post("/text-to-3d")
async def generate_from_text(
    request: TextTo3DRequest,
    background_tasks: BackgroundTasks
):
    """
    Genera un modelo 3D a partir de un prompt de texto
    """
    try:
        # Crear job
        job_id = str(uuid.uuid4())
        job_service = JobService()
        job = job_service.create_job(
            job_id=job_id,
            job_type="text_to_3d",
            input_data={"prompt": request.prompt},
            output_format=request.output_format
        )
        
        # Procesar en background
        generation_service = GenerationService()
        background_tasks.add_task(
            generation_service.generate_from_text,
            job_id=job_id,
            prompt=request.prompt,
            model_type=request.model_type,
            output_format=request.output_format,
            quality=request.quality
        )
        
        return JSONResponse({
            "success": True,
            "job_id": job_id,
            "status": "processing",
            "message": "Generación iniciada. Usa el job_id para consultar el estado."
        })
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/image-to-3d")
async def generate_from_image(
    background_tasks: BackgroundTasks,
    files: List[UploadFile] = File(...),
    model_type: str = Form("triposr"),
    output_format: str = Form("stl"),
    quality: str = Form("medium")
):
    """
    Genera un modelo 3D a partir de una o más imágenes
    """
    try:
        # Validar número de archivos
        if len(files) > settings.MAX_IMAGES:
            raise HTTPException(
                status_code=400,
                detail=f"Máximo {settings.MAX_IMAGES} imágenes permitidas"
            )
        
        # Validar tamaño de archivos
        for file in files:
            if file.size > settings.MAX_FILE_SIZE:
                raise HTTPException(
                    status_code=400,
                    detail=f"Archivo {file.filename} excede el tamaño máximo"
                )
        
        # Guardar imágenes temporalmente
        job_id = str(uuid.uuid4())
        image_paths = []
        
        for file in files:
            file_ext = os.path.splitext(file.filename)[1]
            image_path = os.path.join(
                settings.UPLOAD_DIR,
                f"{job_id}_{file.filename}"
            )
            with open(image_path, "wb") as f:
                content = await file.read()
                f.write(content)
            image_paths.append(image_path)
        
        # Crear job
        job_service = JobService()
        job = job_service.create_job(
            job_id=job_id,
            job_type="image_to_3d",
            input_data={"images": [os.path.basename(p) for p in image_paths]},
            output_format=output_format
        )
        
        # Procesar en background
        generation_service = GenerationService()
        background_tasks.add_task(
            generation_service.generate_from_images,
            job_id=job_id,
            image_paths=image_paths,
            model_type=model_type,
            output_format=output_format,
            quality=quality
        )
        
        return JSONResponse({
            "success": True,
            "job_id": job_id,
            "status": "processing",
            "message": "Generación iniciada. Usa el job_id para consultar el estado."
        })
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/status/{job_id}")
async def get_generation_status(job_id: str):
    """
    Consulta el estado de un job de generación
    """
    try:
        job_service = JobService()
        job = job_service.get_job(job_id)
        
        if not job:
            raise HTTPException(status_code=404, detail="Job no encontrado")
        
        response = {
            "job_id": job_id,
            "status": job["status"],
            "created_at": job["created_at"],
            "updated_at": job["updated_at"]
        }
        
        if job["status"] == JobStatus.COMPLETED:
            response["output_file"] = job.get("output_file")
            response["download_url"] = f"/api/v1/files/download/{job_id}"
        
        elif job["status"] == JobStatus.FAILED:
            response["error"] = job.get("error")
        
        return JSONResponse(response)
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
