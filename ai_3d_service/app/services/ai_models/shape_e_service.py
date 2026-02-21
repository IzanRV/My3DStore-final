"""
Servicio para Shap-E - Generación 3D desde texto
OpenAI Shap-E: https://github.com/openai/shap-e
"""

import os
import logging
import uuid
from typing import Optional

logger = logging.getLogger(__name__)

class ShapEService:
    """
    Servicio para generar modelos 3D desde texto usando Shap-E
    """
    
    def __init__(self):
        self.model_path = None
        self.device = "cuda" if self._check_cuda() else "cpu"
        self.model = None
        logger.info(f"Shap-E inicializado en dispositivo: {self.device}")
    
    def generate_from_text(
        self,
        prompt: str,
        quality: str = "medium",
        output_dir: str = "output"
    ) -> str:
        """
        Genera un modelo 3D desde un prompt de texto
        
        Args:
            prompt: Descripción textual del objeto 3D
            quality: Calidad (low, medium, high)
            output_dir: Directorio de salida
        
        Returns:
            Ruta del archivo OBJ generado
        """
        try:
            # Cargar modelo si no está cargado
            if self.model is None:
                self._load_model()
            
            logger.info(f"Generando modelo 3D desde texto: '{prompt}'")
            
            # Aquí iría la lógica real de Shap-E
            # Ejemplo:
            # from shap_e.diffusion.sample import sample_latents
            # from shap_e.diffusion.gaussian_diffusion import diffusion_from_config
            # from shap_e.models.download import load_model
            
            # Por ahora, generamos un placeholder
            output_file = self._generate_placeholder_mesh(
                prompt=prompt,
                quality=quality,
                output_dir=output_dir
            )
            
            return output_file
        
        except Exception as e:
            logger.error(f"Error en Shap-E: {str(e)}", exc_info=True)
            raise
    
    def _load_model(self):
        """Carga el modelo Shap-E"""
        try:
            # Implementación real:
            # from shap_e.models.download import load_model
            # self.model = load_model('transmitter', device=self.device)
            logger.info("Modelo Shap-E cargado (placeholder)")
            self.model = "loaded"
        except Exception as e:
            logger.error(f"Error cargando modelo Shap-E: {str(e)}")
            raise
    
    def _generate_placeholder_mesh(
        self,
        prompt: str,
        quality: str,
        output_dir: str
    ) -> str:
        """
        Genera un mesh basado en palabras clave del prompt.
        Detecta formas y colores para crear modelos básicos pero variados.
        """
        import trimesh
        import numpy as np

        prompt_lower = prompt.lower()
        mesh = None

        # Detectar forma según palabras clave
        if any(word in prompt_lower for word in ['esfera', 'bola', 'pelota', 'sphere', 'ball']):
            mesh = trimesh.creation.icosphere(subdivisions=3, radius=0.5)
            shape_name = "esfera"
        elif any(word in prompt_lower for word in ['cilindro', 'tubo', 'cylinder', 'tube', 'vaso', 'taza', 'cup']):
            mesh = trimesh.creation.cylinder(radius=0.3, height=0.8, sections=32)
            shape_name = "cilindro"
        elif any(word in prompt_lower for word in ['cono', 'cone', 'pirámide', 'pyramid']):
            mesh = trimesh.creation.cone(radius=0.4, height=0.8, sections=32)
            shape_name = "cono"
        elif any(word in prompt_lower for word in ['toroide', 'donut', 'rosquilla', 'anillo', 'torus', 'ring']):
            mesh = trimesh.creation.torus(major_radius=0.4, minor_radius=0.15, major_sections=32, minor_sections=16)
            shape_name = "toroide"
        elif any(word in prompt_lower for word in ['cápsula', 'capsule', 'píldora', 'pill']):
            mesh = trimesh.creation.capsule(radius=0.25, height=0.6)
            shape_name = "cápsula"
        elif any(word in prompt_lower for word in ['pato', 'duck', 'patito']):
            # Pato simplificado: cuerpo + cabeza
            body = trimesh.creation.icosphere(subdivisions=2, radius=0.35)
            body.apply_translation([0, 0, 0])
            head = trimesh.creation.icosphere(subdivisions=2, radius=0.2)
            head.apply_translation([0.35, 0.25, 0])
            beak = trimesh.creation.cone(radius=0.08, height=0.15, sections=16)
            beak.apply_translation([0.5, 0.25, 0])
            beak.apply_transform(trimesh.transformations.rotation_matrix(np.pi/2, [0, 1, 0]))
            mesh = trimesh.util.concatenate([body, head, beak])
            shape_name = "pato"
        elif any(word in prompt_lower for word in ['silla', 'chair', 'asiento', 'seat']):
            # Silla simplificada
            seat = trimesh.creation.box(extents=[0.5, 0.05, 0.5])
            seat.apply_translation([0, 0.4, 0])
            backrest = trimesh.creation.box(extents=[0.5, 0.5, 0.05])
            backrest.apply_translation([0, 0.65, -0.225])
            leg1 = trimesh.creation.cylinder(radius=0.03, height=0.4)
            leg1.apply_translation([0.2, 0.2, 0.2])
            leg2 = trimesh.creation.cylinder(radius=0.03, height=0.4)
            leg2.apply_translation([-0.2, 0.2, 0.2])
            leg3 = trimesh.creation.cylinder(radius=0.03, height=0.4)
            leg3.apply_translation([0.2, 0.2, -0.2])
            leg4 = trimesh.creation.cylinder(radius=0.03, height=0.4)
            leg4.apply_translation([-0.2, 0.2, -0.2])
            mesh = trimesh.util.concatenate([seat, backrest, leg1, leg2, leg3, leg4])
            shape_name = "silla"
        elif any(word in prompt_lower for word in ['mesa', 'table', 'escritorio', 'desk']):
            # Mesa simplificada
            top = trimesh.creation.box(extents=[0.8, 0.05, 0.5])
            top.apply_translation([0, 0.5, 0])
            leg1 = trimesh.creation.cylinder(radius=0.03, height=0.5)
            leg1.apply_translation([0.35, 0.25, 0.2])
            leg2 = trimesh.creation.cylinder(radius=0.03, height=0.5)
            leg2.apply_translation([-0.35, 0.25, 0.2])
            leg3 = trimesh.creation.cylinder(radius=0.03, height=0.5)
            leg3.apply_translation([0.35, 0.25, -0.2])
            leg4 = trimesh.creation.cylinder(radius=0.03, height=0.5)
            leg4.apply_translation([-0.35, 0.25, -0.2])
            mesh = trimesh.util.concatenate([top, leg1, leg2, leg3, leg4])
            shape_name = "mesa"
        elif any(word in prompt_lower for word in ['estrella', 'star']):
            # Estrella 5 puntas (extruida)
            from trimesh.path.polygons import star
            star_2d = star(5, 0.2, 0.5)
            mesh = trimesh.creation.extrude_polygon(star_2d, height=0.1)
            shape_name = "estrella"
        elif any(word in prompt_lower for word in ['corazón', 'corazon', 'heart']):
            # Corazón aproximado con esferas
            left = trimesh.creation.icosphere(subdivisions=2, radius=0.25)
            left.apply_translation([-0.15, 0.15, 0])
            right = trimesh.creation.icosphere(subdivisions=2, radius=0.25)
            right.apply_translation([0.15, 0.15, 0])
            bottom = trimesh.creation.cone(radius=0.35, height=0.5, sections=32)
            bottom.apply_translation([0, -0.1, 0])
            bottom.apply_transform(trimesh.transformations.rotation_matrix(np.pi, [1, 0, 0]))
            mesh = trimesh.util.concatenate([left, right, bottom])
            shape_name = "corazón"
        else:
            # Por defecto: cubo
            mesh = trimesh.creation.box(extents=[0.6, 0.6, 0.6])
            shape_name = "cubo"

        logger.info(f"Generando forma: {shape_name} para prompt: '{prompt}'")

        # Guardar como OBJ
        os.makedirs(output_dir, exist_ok=True)
        output_file = os.path.join(
            output_dir,
            f"shape_e_{uuid.uuid4().hex[:8]}.obj"
        )

        mesh.export(output_file, file_type='obj')
        logger.info(f"Mesh generado: {output_file} (forma: {shape_name})")

        return output_file
    
    def _check_cuda(self) -> bool:
        """Verifica si CUDA está disponible"""
        try:
            import torch
            return torch.cuda.is_available()
        except ImportError:
            return False
