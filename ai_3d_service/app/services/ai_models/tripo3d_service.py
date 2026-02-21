"""
Servicio para Tripo3D - Generación 3D desde texto usando API externa
https://platform.tripo3d.ai/docs/
"""

import os
import logging
import httpx
import time
import uuid
from typing import Optional

from app.core.config import settings

logger = logging.getLogger(__name__)


class Tripo3DService:
    """
    Servicio para generar modelos 3D usando la API de Tripo3D
    Documentación: https://platform.tripo3d.ai/docs/generation
    """

    BASE_URL = "https://api.tripo3d.ai"

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key or getattr(settings, 'TRIPO3D_API_KEY', None)
        if not self.api_key:
            logger.warning("TRIPO3D_API_KEY no configurada. La generación real no funcionará.")
        else:
            logger.info(f"Tripo3DService inicializado con API key: {self.api_key[:10]}...")

    def generate_from_text(
        self,
        prompt: str,
        quality: str = "medium",
        output_dir: str = "output"
    ) -> str:
        """
        Genera un modelo 3D desde un prompt de texto usando Tripo3D

        Args:
            prompt: Descripción textual del objeto 3D (máx 1024 caracteres)
            quality: Calidad (low, medium, high)
            output_dir: Directorio de salida

        Returns:
            Ruta del archivo generado
        """
        if not self.api_key:
            raise ValueError("TRIPO3D_API_KEY no está configurada")

        try:
            logger.info(f"Generando modelo 3D con Tripo3D: '{prompt}'")

            # Paso 1: Crear tarea de generación
            task_id = self._create_text_to_3d_task(prompt, quality)
            logger.info(f"Tarea creada en Tripo3D: {task_id}")

            # Paso 2: Esperar a que complete (polling)
            result = self._wait_for_task(task_id)

            # Paso 3: Descargar el modelo
            output_file = self._download_model(result, output_dir)

            return output_file

        except Exception as e:
            logger.error(f"Error en Tripo3DService: {str(e)}", exc_info=True)
            raise

    def _create_text_to_3d_task(self, prompt: str, quality: str) -> str:
        """Crea una tarea de text-to-3d en Tripo3D"""

        # Mapear calidad a modelo
        model_version = "v2.0-20240919"
        if quality == "high":
            model_version = "v2.5-20250123"

        payload = {
            "type": "text_to_model",
            "prompt": prompt[:1024],  # Límite de 1024 caracteres
            "model_version": model_version
        }

        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }

        with httpx.Client(timeout=60.0) as client:
            response = client.post(
                f"{self.BASE_URL}/v2/openapi/task",
                json=payload,
                headers=headers
            )

            if response.status_code not in [200, 201, 202]:
                error_detail = response.text
                logger.error(f"Error de Tripo3D: {error_detail}")
                raise Exception(f"Error de Tripo3D: {response.status_code} - {error_detail}")

            data = response.json()

            # Verificar respuesta exitosa
            if data.get("code") != 0:
                raise Exception(f"Error de Tripo3D: {data.get('message', 'Error desconocido')}")

            return data.get("data", {}).get("task_id")

    def _wait_for_task(self, task_id: str, max_wait: int = 300) -> dict:
        """Espera a que la tarea se complete"""

        headers = {
            "Authorization": f"Bearer {self.api_key}"
        }

        start_time = time.time()

        with httpx.Client(timeout=30.0) as client:
            while True:
                if time.time() - start_time > max_wait:
                    raise TimeoutError("Tiempo de espera excedido para Tripo3D")

                response = client.get(
                    f"{self.BASE_URL}/v2/openapi/task/{task_id}",
                    headers=headers
                )

                if response.status_code != 200:
                    raise Exception(f"Error consultando tarea: {response.status_code}")

                data = response.json()

                if data.get("code") != 0:
                    raise Exception(f"Error de Tripo3D: {data.get('message')}")

                task_data = data.get("data", {})
                status = task_data.get("status")

                logger.info(f"Estado de tarea Tripo3D: {status}")

                if status == "success":
                    return task_data
                elif status == "failed":
                    raise Exception(f"Tarea falló: {task_data.get('error', 'Error desconocido')}")
                elif status in ["queued", "running"]:
                    time.sleep(5)  # Esperar 5 segundos antes de volver a consultar
                else:
                    time.sleep(3)

    def _download_model(self, task_result: dict, output_dir: str) -> str:
        """Descarga el modelo generado"""

        os.makedirs(output_dir, exist_ok=True)

        # Tripo3D devuelve el modelo en output.model
        output_data = task_result.get("output", {})
        model_url = output_data.get("model")

        if not model_url:
            # Intentar con pbr_model o rendered_image
            model_url = output_data.get("pbr_model") or output_data.get("base_model")

        if not model_url:
            raise Exception("No se encontró URL del modelo en la respuesta de Tripo3D")

        # Determinar extensión del archivo
        file_extension = "glb"
        if ".obj" in model_url:
            file_extension = "obj"
        elif ".fbx" in model_url:
            file_extension = "fbx"
        elif ".stl" in model_url:
            file_extension = "stl"

        # Descargar el archivo
        output_file = os.path.join(
            output_dir,
            f"tripo3d_{uuid.uuid4().hex[:8]}.{file_extension}"
        )

        headers = {
            "Authorization": f"Bearer {self.api_key}"
        }

        with httpx.Client(timeout=120.0) as client:
            response = client.get(model_url, headers=headers)

            if response.status_code != 200:
                raise Exception(f"Error descargando modelo: {response.status_code}")

            with open(output_file, "wb") as f:
                f.write(response.content)

        logger.info(f"Modelo descargado: {output_file}")
        return output_file


# Singleton para reutilizar la instancia
_tripo3d_service: Optional[Tripo3DService] = None


def get_tripo3d_service() -> Tripo3DService:
    global _tripo3d_service
    if _tripo3d_service is None:
        _tripo3d_service = Tripo3DService()
    return _tripo3d_service
