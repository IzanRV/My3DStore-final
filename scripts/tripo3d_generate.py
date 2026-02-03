#!/usr/bin/env python3
"""
Script intermedio para usar el SDK de Tripo3D desde PHP
Recibe parámetros JSON por stdin y devuelve resultados JSON por stdout
"""

import sys
import json
import asyncio
import os
from pathlib import Path

# Agregar rutas comunes donde puede estar instalado el SDK
# 1. Ruta del usuario actual
user_site_packages = os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', 'Python314', 'site-packages')
if os.path.exists(user_site_packages) and user_site_packages not in sys.path:
    sys.path.insert(0, user_site_packages)

# 2. Ruta del sistema (donde está Python)
system_site_packages = os.path.join(os.path.dirname(sys.executable), 'Lib', 'site-packages')
if os.path.exists(system_site_packages) and system_site_packages not in sys.path:
    sys.path.insert(0, system_site_packages)

# 3. Ruta específica conocida
known_path = r'C:\Users\izanr\AppData\Roaming\Python\Python314\site-packages'
if os.path.exists(known_path) and known_path not in sys.path:
    sys.path.insert(0, known_path)

try:
    from tripo3d import TripoClient, TaskStatus
except ImportError as e:
    error_msg = f'SDK de Tripo3D no instalado. Error: {str(e)}. Ejecuta: pip install tripo3d'
    print(json.dumps({
        'success': False,
        'error': error_msg,
        'debug': {
            'python_path': sys.executable,
            'sys_path': sys.path,
            'user_site_packages': user_site_packages,
            'exists': os.path.exists(user_site_packages)
        }
    }))
    sys.exit(1)


async def generate_from_text(api_key, prompt, mesh_quality='standard'):
    """Genera modelo 3D desde texto"""
    try:
        async with TripoClient(api_key=api_key) as client:
            # Generar modelo desde texto
            # Nota: text_to_model() solo acepta 'prompt', no acepta 'mesh_quality'
            task_id = await client.text_to_model(
                prompt=prompt
            )
            
            return {
                'success': True,
                'task_id': task_id,
                'type': 'text'
            }
    except Exception as e:
        return {
            'success': False,
            'error': f'Error al generar desde texto: {str(e)}'
        }


async def generate_from_image(api_key, image_path, mesh_quality='standard', generate_in_parts=False, privacy='public'):
    """Genera modelo 3D desde imagen"""
    try:
        if not os.path.exists(image_path):
            return {
                'success': False,
                'error': f'Imagen no encontrada: {image_path}'
            }
        
        async with TripoClient(api_key=api_key) as client:
            # Generar modelo desde imagen
            task_id = await client.image_to_model(
                image_path=image_path,
                mesh_quality=mesh_quality,
                generate_in_parts=generate_in_parts,
                privacy=privacy
            )
            
            return {
                'success': True,
                'task_id': task_id,
                'type': 'image'
            }
    except Exception as e:
        return {
            'success': False,
            'error': f'Error al generar desde imagen: {str(e)}'
        }


async def check_task_status(api_key, task_id):
    """Verifica el estado de una tarea"""
    try:
        async with TripoClient(api_key=api_key) as client:
            task = await client.get_task(task_id)
            
            status = task.status if hasattr(task, 'status') else 'unknown'
            progress = getattr(task, 'progress', 0)
            model_url = None
            
            # Intentar obtener URL del modelo
            if status == TaskStatus.SUCCESS or status == 'SUCCESS' or status == 'completed':
                if hasattr(task, 'model_url'):
                    model_url = task.model_url
                elif hasattr(task, 'output') and task.output:
                    if isinstance(task.output, dict) and 'model_url' in task.output:
                        model_url = task.output['model_url']
                    elif isinstance(task.output, str):
                        model_url = task.output
            
            return {
                'success': True,
                'status': str(status),
                'progress': progress,
                'model_url': model_url,
                'task_data': str(task) if hasattr(task, '__dict__') else None
            }
    except Exception as e:
        return {
            'success': False,
            'error': f'Error al verificar estado: {str(e)}'
        }


async def download_model(api_key, task_id, output_dir):
    """Descarga el modelo generado"""
    try:
        async with TripoClient(api_key=api_key) as client:
            task = await client.get_task(task_id)
            
            if task.status != TaskStatus.SUCCESS and task.status != 'SUCCESS' and task.status != 'completed':
                return {
                    'success': False,
                    'error': f'La tarea aún no está completa. Estado: {task.status}'
                }
            
            # Crear directorio de salida si no existe
            os.makedirs(output_dir, exist_ok=True)
            
            # Descargar modelo
            downloaded_files = await client.download_task_models(task, output_dir)
            
            # Buscar archivo GLB
            glb_path = None
            if isinstance(downloaded_files, dict):
                for key, path in downloaded_files.items():
                    if path and (path.endswith('.glb') or 'glb' in key.lower()):
                        glb_path = path
                        break
            elif isinstance(downloaded_files, list):
                for path in downloaded_files:
                    if path and path.endswith('.glb'):
                        glb_path = path
                        break
            
            if glb_path and os.path.exists(glb_path):
                return {
                    'success': True,
                    'glb_path': glb_path,
                    'files': downloaded_files
                }
            else:
                return {
                    'success': False,
                    'error': 'No se encontró archivo GLB en los archivos descargados',
                    'files': downloaded_files
                }
    except Exception as e:
        return {
            'success': False,
            'error': f'Error al descargar modelo: {str(e)}'
        }


def main():
    """Función principal"""
    try:
        # Leer entrada JSON desde stdin
        input_data = json.loads(sys.stdin.read())
        
        action = input_data.get('action')
        api_key = input_data.get('api_key')
        
        if not api_key:
            print(json.dumps({
                'success': False,
                'error': 'API key no proporcionada'
            }))
            sys.exit(1)
        
        # Ejecutar acción según el tipo
        if action == 'text-to-model':
            prompt = input_data.get('prompt')
            mesh_quality = input_data.get('mesh_quality', 'standard')
            
            if not prompt:
                print(json.dumps({
                    'success': False,
                    'error': 'Prompt no proporcionado'
                }))
                sys.exit(1)
            
            result = asyncio.run(generate_from_text(api_key, prompt, mesh_quality))
            print(json.dumps(result))
            
        elif action == 'image-to-model':
            image_path = input_data.get('image_path')
            mesh_quality = input_data.get('mesh_quality', 'standard')
            generate_in_parts = input_data.get('generate_in_parts', False)
            privacy = input_data.get('privacy', 'public')
            
            if not image_path:
                print(json.dumps({
                    'success': False,
                    'error': 'Ruta de imagen no proporcionada'
                }))
                sys.exit(1)
            
            result = asyncio.run(generate_from_image(
                api_key, image_path, mesh_quality, generate_in_parts, privacy
            ))
            print(json.dumps(result))
            
        elif action == 'check-status':
            task_id = input_data.get('task_id')
            
            if not task_id:
                print(json.dumps({
                    'success': False,
                    'error': 'Task ID no proporcionado'
                }))
                sys.exit(1)
            
            result = asyncio.run(check_task_status(api_key, task_id))
            print(json.dumps(result))
            
        elif action == 'download':
            task_id = input_data.get('task_id')
            output_dir = input_data.get('output_dir')
            
            if not task_id:
                print(json.dumps({
                    'success': False,
                    'error': 'Task ID no proporcionado'
                }))
                sys.exit(1)
            
            if not output_dir:
                print(json.dumps({
                    'success': False,
                    'error': 'Directorio de salida no proporcionado'
                }))
                sys.exit(1)
            
            result = asyncio.run(download_model(api_key, task_id, output_dir))
            print(json.dumps(result))
            
        else:
            print(json.dumps({
                'success': False,
                'error': f'Acción no válida: {action}'
            }))
            sys.exit(1)
            
    except json.JSONDecodeError as e:
        print(json.dumps({
            'success': False,
            'error': f'Error al parsear JSON: {str(e)}'
        }))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': f'Error inesperado: {str(e)}'
        }))
        sys.exit(1)


if __name__ == '__main__':
    main()
