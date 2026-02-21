"""
Servicio para conversión de formatos 3D
"""

import os
import subprocess
import logging
from typing import Optional

logger = logging.getLogger(__name__)

class ConverterService:
    def __init__(self):
        self.supported_formats = {
            "obj": ["stl", "glb"],
            "stl": ["obj", "glb"],
            "glb": ["obj", "stl"]
        }
    
    def convert(
        self,
        input_file: str,
        output_format: str,
        output_dir: Optional[str] = None
    ) -> str:
        """
        Convierte un archivo 3D a otro formato
        
        Args:
            input_file: Ruta del archivo de entrada
            output_format: Formato de salida (stl, obj, glb)
            output_dir: Directorio de salida (opcional)
        
        Returns:
            Ruta del archivo convertido
        """
        if not os.path.exists(input_file):
            raise FileNotFoundError(f"Archivo no encontrado: {input_file}")
        
        input_ext = os.path.splitext(input_file)[1][1:].lower()
        
        if input_ext not in self.supported_formats:
            raise ValueError(f"Formato de entrada no soportado: {input_ext}")
        
        if output_format not in self.supported_formats[input_ext]:
            raise ValueError(
                f"No se puede convertir {input_ext} a {output_format}. "
                f"Formatos soportados: {self.supported_formats[input_ext]}"
            )
        
        # Generar nombre de archivo de salida
        base_name = os.path.splitext(os.path.basename(input_file))[0]
        if output_dir:
            os.makedirs(output_dir, exist_ok=True)
            output_file = os.path.join(output_dir, f"{base_name}.{output_format}")
        else:
            output_dir = os.path.dirname(input_file)
            output_file = os.path.join(output_dir, f"{base_name}.{output_format}")
        
        # Realizar conversión
        try:
            if output_format == "stl":
                self._convert_to_stl(input_file, output_file)
            elif output_format == "obj":
                self._convert_to_obj(input_file, output_file)
            elif output_format == "glb":
                self._convert_to_glb(input_file, output_file)
            
            logger.info(f"Archivo convertido: {input_file} -> {output_file}")
            return output_file
        
        except Exception as e:
            logger.error(f"Error en conversión: {str(e)}")
            raise
    
    def _convert_to_stl(self, input_file: str, output_file: str):
        """Convierte a STL usando trimesh o blender"""
        try:
            import trimesh
            
            mesh = trimesh.load(input_file)
            mesh.export(output_file, file_type='stl')
        
        except ImportError:
            # Fallback: usar blender si está disponible
            self._convert_with_blender(input_file, output_file, "stl")
    
    def _convert_to_obj(self, input_file: str, output_file: str):
        """Convierte a OBJ"""
        try:
            import trimesh
            
            mesh = trimesh.load(input_file)
            mesh.export(output_file, file_type='obj')
        
        except ImportError:
            self._convert_with_blender(input_file, output_file, "obj")
    
    def _convert_to_glb(self, input_file: str, output_file: str):
        """Convierte a GLB"""
        try:
            import trimesh
            
            mesh = trimesh.load(input_file)
            mesh.export(output_file, file_type='glb')
        
        except ImportError:
            self._convert_with_blender(input_file, output_file, "glb")
    
    def _convert_with_blender(self, input_file: str, output_file: str, format: str):
        """Convierte usando Blender (requiere Blender instalado)"""
        script = f"""
import bpy
import sys

# Limpiar escena
bpy.ops.object.select_all(action='SELECT')
bpy.ops.object.delete()

# Importar archivo
bpy.ops.import_scene.{self._get_blender_importer(input_file)}(filepath='{input_file}')

# Exportar
bpy.ops.export_scene.{self._get_blender_exporter(format)}(filepath='{output_file}')
"""
        
        # Ejecutar Blender en modo headless
        subprocess.run([
            "blender",
            "--background",
            "--python-expr",
            script
        ], check=True)
    
    def _get_blender_importer(self, file_path: str) -> str:
        """Obtiene el nombre del importador de Blender según extensión"""
        ext = os.path.splitext(file_path)[1].lower()
        importers = {
            ".obj": "obj",
            ".stl": "stl",
            ".glb": "gltf",
            ".gltf": "gltf"
        }
        return importers.get(ext, "obj")
    
    def _get_blender_exporter(self, format: str) -> str:
        """Obtiene el nombre del exportador de Blender según formato"""
        exporters = {
            "obj": "obj",
            "stl": "stl",
            "glb": "gltf"
        }
        return exporters.get(format, "obj")
