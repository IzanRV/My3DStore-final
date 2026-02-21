"""
Endpoint para mejorar y traducir descripciones de productos al español.
Usado por el scraper de Printables para procesar descripciones antes de guardar.
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import Optional

router = APIRouter()


class ImproveDescriptionRequest(BaseModel):
    text: str


class ImproveDescriptionResponse(BaseModel):
    text: str


def _translate_to_spanish(text: str) -> Optional[str]:
    """Intenta traducir el texto al español. Devuelve None si falla o no está instalado deep-translator."""
    if not text or not text.strip():
        return text
    try:
        from deep_translator import GoogleTranslator
    except ImportError:
        return None
    try:
        translated = GoogleTranslator(source="auto", target="es").translate(text.strip())
        return translated if translated else None
    except Exception:
        return None


@router.post("/improve", response_model=ImproveDescriptionResponse)
async def improve_description(request: ImproveDescriptionRequest):
    """
    Mejora y traduce la descripción al español.
    Recibe texto (p. ej. en inglés) y devuelve versión en español.
    Si la traducción no está disponible, devuelve el texto original.
    """
    text = request.text
    if not text:
        return ImproveDescriptionResponse(text="")

    improved = _translate_to_spanish(text)
    if improved is None:
        improved = text

    return ImproveDescriptionResponse(text=improved)
