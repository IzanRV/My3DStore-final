"""
Servicio para Meshy.ai - Generación 3D desde texto usando API externa
https://docs.meshy.ai/
"""

import os
import logging
import httpx
import time
import uuid
from typing import Optional

from app.core.config import settings

logger = logging.getLogger(__name__)

class MeshyService:
    """
    Servicio para generar modelos 3D usando la API de Meshy.ai
    """

    BASE_URL = "https://api.meshy.ai"

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key or settings.MESHY_API_KEY
        if not self.api_key:
            logger.warning("MESHY_API_KEY no configurada. La generación real no funcionará.")
        else:
            logger.info(f"MeshyService inicializado con API key: {self.api_key[:10]}...")

    def generate_from_text(
        self,
        prompt: str,
        quality: str = "medium",
        output_dir: str = "output"
    ) -> str:
        """
        Genera un modelo 3D desde un prompt de texto usando Meshy.ai

        Args:
            prompt: Descripción textual del objeto 3D
            quality: Calidad (low, medium, high)
            output_dir: Directorio de salida

        Returns:
            Ruta del archivo generado
        """
        if not self.api_key:
            raise ValueError("MESHY_API_KEY no está configurada")

        try:
            logger.info(f"Generando modelo 3D con Meshy.ai: '{prompt}'")

            # Paso 1: Crear tarea de generación
            task_id = self._create_text_to_3d_task(prompt, quality)
            logger.info(f"Tarea creada en Meshy.ai: {task_id}")

            # Paso 2: Esperar a que complete (polling)
            result = self._wait_for_task(task_id)

            # Paso 3: Descargar el modelo
            output_file = self._download_model(result, output_dir)

            return output_file

        except Exception as e:
            logger.error(f"Error en MeshyService: {str(e)}", exc_info=True)
            raise

    def _create_text_to_3d_task(self, prompt: str, quality: str) -> str:
        """Crea una tarea de text-to-3d en Meshy.ai"""

        # Mapear calidad a configuración de Meshy
        art_style = "realistic"

        payload = {
            "mode": "preview",  # preview es más rápido, production es mejor calidad
            "prompt": prompt,
            "art_style": art_style,
            "negative_prompt": "low quality, blurry, distorted"
        }

        # Si es alta calidad, usar modo production
        if quality == "high":
            payload["mode"] = "refine"

        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }

        with httpx.Client(timeout=60.0) as client:
            response = client.post(
                f"{self.BASE_URL}/v2/text-to-3d",
                json=payload,
                headers=headers
            )

            if response.status_code != 200 and response.status_code != 202:
                error_detail = response.text
                logger.error(f"Error de Meshy.ai: {error_detail}")
                raise Exception(f"Error de Meshy.ai: {response.status_code} - {error_detail}")

            data = response.json()
            return data.get("result") or data.get("id")

    def _wait_for_task(self, task_id: str, max_wait: int = 300) -> dict:
        """Espera a que la tarea se complete"""

        headers = {
            "Authorization": f"Bearer {self.api_key}"
        }

        start_time = time.time()

        with httpx.Client(timeout=30.0) as client:
            while True:
                if time.time() - start_time > max_wait:
                    raise TimeoutError("Tiempo de espera excedido para Meshy.ai")

                response = client.get(
                    f"{self.BASE_URL}/v2/text-to-3d/{task_id}",
                    headers=headers
                )

                if response.status_code != 200:
                    raise Exception(f"Error consultando tarea: {response.status_code}")

                data = response.json()
                status = data.get("status")

                logger.info(f"Estado de tarea Meshy.ai: {status}")

                if status == "SUCCEEDED":
                    return data
                elif status == "FAILED":
                    raise Exception(f"Tarea falló: {data.get('error', 'Error desconocido')}")
                elif status in ["PENDING", "IN_PROGRESS"]:
                    time.sleep(5)  # Esperar 5 segundos antes de volver a consultar
                else:
                    time.sleep(3)

    def _download_model(self, task_result: dict, output_dir: str) -> str:
        """Descarga el modelo generado"""

        os.makedirs(output_dir, exist_ok=True)

        # Meshy devuelve URLs para diferentes formatos
        model_urls = task_result.get("model_urls", {})

        # Preferir GLB, luego OBJ, luego FBX
        download_url = None
        file_extension = "glb"

        if model_urls.get("glb"):
            download_url = model_urls["glb"]
            file_extension = "glb"
        elif model_urls.get("obj"):
            download_url = model_urls["obj"]
            file_extension = "obj"
        elif model_urls.get("fbx"):
            download_url = model_urls["fbx"]
            file_extension = "fbx"

        if not download_url:
            raise Exception("No se encontró URL de descarga en la respuesta de Meshy.ai")

        # Descargar el archivo
        output_file = os.path.join(
            output_dir,
            f"meshy_{uuid.uuid4().hex[:8]}.{file_extension}"
        )

        with httpx.Client(timeout=120.0) as client:
            response = client.get(download_url)

            if response.status_code != 200:
                raise Exception(f"Error descargando modelo: {response.status_code}")

            with open(output_file, "wb") as f:
                f.write(response.content)

        logger.info(f"Modelo descargado: {output_file}")
        return output_file


# Singleton para reutilizar la instancia
_meshy_service: Optional[MeshyService] = None

def get_meshy_service() -> MeshyService:
    global _meshy_service
    if _meshy_service is None:
        _meshy_service = MeshyService()
    return _meshy_service
