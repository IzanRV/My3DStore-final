#!/usr/bin/env python3
"""
Script para generar modelos 3D desde texto usando Shap-E (OpenAI) localmente
Requiere: GPU NVIDIA, PyTorch con CUDA, y el modelo Shap-E instalado
"""

import sys
import json
import os
import time
from pathlib import Path

# Agregar rutas comunes para diferentes versiones de Python
python_version = f"{sys.version_info.major}{sys.version_info.minor:02d}"
python_name = f"Python{python_version}"

# Rutas de site-packages del usuario (AppData\Roaming\Python)
user_site_packages_paths = [
    os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', python_name, 'site-packages'),
    os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', 'Python312', 'site-packages'),
    os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', 'Python311', 'site-packages'),
    os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', 'Python310', 'site-packages'),
    os.path.join(os.path.expanduser('~'), 'AppData', 'Roaming', 'Python', 'Python314', 'site-packages'),
]

# Rutas de site-packages del sistema (PRIMERO, más importante)
system_site_packages = os.path.join(os.path.dirname(sys.executable), 'Lib', 'site-packages')
if os.path.exists(system_site_packages) and system_site_packages not in sys.path:
    sys.path.insert(0, system_site_packages)

# También agregar la ruta de Local\Programs (donde se instala Python normalmente)
local_programs_python = os.path.join(os.path.expanduser('~'), 'AppData', 'Local', 'Programs', 'Python', python_name, 'Lib', 'site-packages')
if os.path.exists(local_programs_python) and local_programs_python not in sys.path:
    sys.path.insert(0, local_programs_python)

# Agregar todas las rutas de usuario encontradas (AppData\Roaming)
for path in user_site_packages_paths:
    if os.path.exists(path) and path not in sys.path:
        sys.path.insert(0, path)

try:
    import torch
except ImportError:
    print(json.dumps({
        'success': False,
        'error': 'PyTorch no está instalado. Instala con: pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118'
    }))
    sys.exit(1)

try:
    # Intentar importar Shap-E
    from shap_e.diffusion.sample import sample_latents
    from shap_e.diffusion.gaussian_diffusion import diffusion_from_config
    from shap_e.models.download import load_model, load_config
    from shap_e.util.notebooks import create_pan_cameras, decode_latent_images
    from shap_e.util import mesh
    import torch
except ImportError as e:
    print(json.dumps({
        'success': False,
        'error': f'Shap-E no está instalado. Error: {str(e)}. Instala con: pip install shap-e',
        'instructions': 'Para instalar Shap-E localmente: pip install shap-e'
    }))
    sys.exit(1)


def generate_from_text(prompt, output_dir='./output'):
    """Genera modelo 3D desde texto usando Shap-E localmente"""
    try:
        # Verificar si hay GPU disponible
        device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        
        if device.type == 'cpu':
            return {
                'success': False,
                'error': 'Se requiere GPU NVIDIA con CUDA para ejecutar Shap-E. CPU no es suficiente.\n\n' +
                         'SOLUCIÓN: Instala Python 3.11 o 3.12 y PyTorch con CUDA:\n' +
                         '1. Descarga Python 3.12 desde python.org\n' +
                         '2. Instala PyTorch: pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121\n' +
                         '3. Reinstala Shap-E: pip install git+https://github.com/openai/shap-e.git'
            }
        
        print(f"Usando GPU: {torch.cuda.get_device_name(0)}", file=sys.stderr)
        
        # Cargar modelo
        print("Cargando modelo Shap-E...", file=sys.stderr)
        xm = load_model('transmitter', device=device)
        model = load_model('text300M', device=device)
        
        # Configurar difusión
        diffusion = diffusion_from_config(load_config('diffusion'))
        
        # Generar latentes desde texto
        print(f"Generando modelo desde texto: {prompt}", file=sys.stderr)
        batch_size = 1
        guidance_scale = 15.0
        
        latents = sample_latents(
            batch_size=batch_size,
            model=model,
            diffusion=diffusion,
            guidance_scale=guidance_scale,
            model_kwargs=dict(texts=[prompt] * batch_size),
            progress=True,
            clip_denoised=True,
            use_fp16=True,
            use_karras=True,
            karras_steps=24,
            sigma_min=1e-3,
            sigma_max=160,
            s_churn=0,
        )
        
        # Decodificar a mesh
        print("Decodificando mesh...", file=sys.stderr)
        
        # Crear directorio de salida
        os.makedirs(output_dir, exist_ok=True)
        
        # Decodificar el latente a mesh usando el renderer del modelo
        # El método correcto es usar xm.renderer.decode_latent_mesh
        t = xm.renderer.decode_latent_mesh(latents[0]).tri_mesh()
        
        # Guardar como PLY primero
        timestamp = int(time.time())
        model_hash = abs(hash(prompt)) % 1000000
        ply_path = os.path.join(output_dir, f'shape_e_{model_hash}_{timestamp}.ply')
        
        with open(ply_path, 'wb') as f:
            mesh.write_ply(f, t.verts, t.faces)
        
        print(f"Mesh guardado en: {ply_path}", file=sys.stderr)
        
        # Convertir PLY a GLB (requiere trimesh)
        try:
            import trimesh
            print("Convirtiendo PLY a GLB...", file=sys.stderr)
            mesh_obj = trimesh.load(ply_path)
            glb_path = os.path.join(output_dir, f'shape_e_{model_hash}_{timestamp}.glb')
            mesh_obj.export(glb_path)
            
            print(f"GLB guardado en: {glb_path}", file=sys.stderr)
            
            return {
                'success': True,
                'glb_path': glb_path,
                'ply_path': ply_path,
                'message': 'Modelo generado exitosamente'
            }
        except ImportError:
            return {
                'success': True,
                'ply_path': ply_path,
                'note': 'Instala trimesh para convertir a GLB: pip install trimesh',
                'message': 'Modelo generado en formato PLY. Instala trimesh para convertir a GLB.'
            }
        except Exception as e:
            return {
                'success': True,
                'ply_path': ply_path,
                'error': f'Error al convertir a GLB: {str(e)}',
                'message': 'Modelo generado en formato PLY'
            }
            
    except Exception as e:
        import traceback
        error_trace = traceback.format_exc()
        print(f"Error completo: {error_trace}", file=sys.stderr)
        return {
            'success': False,
            'error': f'Error al generar modelo: {str(e)}',
            'traceback': error_trace
        }


def main():
    """Función principal"""
    try:
        input_data = json.loads(sys.stdin.read())
        prompt = input_data.get('prompt')
        output_dir = input_data.get('output_dir', './output')
        
        if not prompt:
            print(json.dumps({
                'success': False,
                'error': 'Prompt no proporcionado'
            }))
            sys.exit(1)
        
        result = generate_from_text(prompt, output_dir)
        print(json.dumps(result))
        
    except json.JSONDecodeError as e:
        print(json.dumps({
            'success': False,
            'error': f'Error al parsear JSON: {str(e)}'
        }))
        sys.exit(1)
    except Exception as e:
        import traceback
        print(json.dumps({
            'success': False,
            'error': f'Error inesperado: {str(e)}',
            'traceback': traceback.format_exc()
        }))
        sys.exit(1)


if __name__ == '__main__':
    main()
