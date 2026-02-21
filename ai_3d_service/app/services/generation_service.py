"""
Servicio principal para generación de modelos 3D
"""

import os
import logging
from typing import List, Optional
import uuid

from app.core.config import settings
from app.services.job_service import JobService
from app.services.ai_models.triposr_service import TripoSRService
from app.services.ai_models.shape_e_service import ShapEService
from app.services.ai_models.point_e_service import PointEService
from app.services.ai_models.meshy_service import MeshyService
from app.services.ai_models.tripo3d_service import Tripo3DService
from app.services.converter_service import ConverterService
from app.models.job import JobStatus

logger = logging.getLogger(__name__)

class GenerationService:
    def __init__(self):
        self.job_service = JobService()
        self.converter_service = ConverterService()

        # Inicializar servicios de modelos IA (lazy loading)
        self.triposr_service = None
        self.shape_e_service = None
        self.point_e_service = None
        self.meshy_service = None
        self.tripo3d_service = None

    def generate_from_text(
        self,
        job_id: str,
        prompt: str,
        model_type: str = "shap-e",
        output_format: str = "stl",
        quality: str = "medium"
    ):
        """Genera modelo 3D desde texto."""
        try:
            logger.info(f"Generando modelo 3D desde texto: {prompt}")
            self.job_service.update_job(job_id, status=JobStatus.PROCESSING)
            
            # Seleccionar modelo según tipo
            # Priorizar APIs externas si están configuradas
            if model_type == "tripo3d" or (settings.TRIPO3D_API_KEY and model_type in ["shap-e", "point-e"]):
                model_service = self._get_tripo3d_service()
                logger.info(f"Usando Tripo3D para generación (API key: {settings.TRIPO3D_API_KEY[:10]}...)")
            elif model_type == "meshy" or (settings.MESHY_API_KEY and model_type in ["shap-e", "point-e"]):
                model_service = self._get_meshy_service()
                logger.info(f"Usando Meshy.ai para generación (API key: {settings.MESHY_API_KEY[:10]}...)")
            elif model_type == "shap-e":
                model_service = self._get_shape_e_service()
            elif model_type == "point-e":
                model_service = self._get_point_e_service()
            else:
                raise ValueError(f"Modelo no soportado: {model_type}")
            
            # Generar modelo
            output_file = model_service.generate_from_text(
                prompt=prompt,
                quality=quality,
                output_dir=settings.OUTPUT_DIR
            )
            
            # Convertir formato si es necesario
            if output_format != "obj":
                output_file = self.converter_service.convert(
                    input_file=output_file,
                    output_format=output_format,
                    output_dir=settings.OUTPUT_DIR
                )
            
            # Actualizar job
            self.job_service.update_job(
                job_id,
                status=JobStatus.COMPLETED,
                output_file=os.path.basename(output_file)
            )
            
            logger.info(f"Modelo generado exitosamente: {output_file}")
        
        except Exception as e:
            logger.error(f"Error generando modelo: {str(e)}", exc_info=True)
            self.job_service.update_job(
                job_id,
                status=JobStatus.FAILED,
                error=str(e)
            )
    
    def generate_from_images(
        self,
        job_id: str,
        image_paths: List[str],
        model_type: str = "triposr",
        output_format: str = "stl",
        quality: str = "medium"
    ):
        """Genera modelo 3D desde imágenes"""
        try:
            logger.info(f"Generando modelo 3D desde {len(image_paths)} imagen(es)")
            self.job_service.update_job(job_id, status=JobStatus.PROCESSING)
            
            # Usar TripoSR para imágenes
            if model_type != "triposr":
                logger.warning(f"Modelo {model_type} no soportado para imágenes, usando triposr")
            
            model_service = self._get_triposr_service()
            
            # Generar modelo
            output_file = model_service.generate_from_images(
                image_paths=image_paths,
                quality=quality,
                output_dir=settings.OUTPUT_DIR
            )
            
            # Convertir formato si es necesario
            if output_format != "obj":
                output_file = self.converter_service.convert(
                    input_file=output_file,
                    output_format=output_format,
                    output_dir=settings.OUTPUT_DIR
                )
            
            # Actualizar job
            self.job_service.update_job(
                job_id,
                status=JobStatus.COMPLETED,
                output_file=os.path.basename(output_file)
            )
            
            # Limpiar imágenes temporales
            for image_path in image_paths:
                if os.path.exists(image_path):
                    os.remove(image_path)
            
            logger.info(f"Modelo generado exitosamente: {output_file}")
        
        except Exception as e:
            logger.error(f"Error generando modelo: {str(e)}", exc_info=True)
            self.job_service.update_job(
                job_id,
                status=JobStatus.FAILED,
                error=str(e)
            )
    
    def _get_triposr_service(self):
        """Obtiene instancia de TripoSR (lazy loading)"""
        if self.triposr_service is None:
            self.triposr_service = TripoSRService()
        return self.triposr_service
    
    def _get_shape_e_service(self):
        """Obtiene instancia de Shap-E (lazy loading)"""
        if self.shape_e_service is None:
            self.shape_e_service = ShapEService()
        return self.shape_e_service
    
    def _get_point_e_service(self):
        """Obtiene instancia de Point-E (lazy loading)"""
        if self.point_e_service is None:
            self.point_e_service = PointEService()
        return self.point_e_service

    def _get_meshy_service(self):
        """Obtiene instancia de Meshy.ai (lazy loading)"""
        if self.meshy_service is None:
            self.meshy_service = MeshyService()
        return self.meshy_service

    def _get_tripo3d_service(self):
        """Obtiene instancia de Tripo3D (lazy loading)"""
        if self.tripo3d_service is None:
            self.tripo3d_service = Tripo3DService()
        return self.tripo3d_service
