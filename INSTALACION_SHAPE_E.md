# Instalación de Shap-E Local (100% Gratuito)

## ⚠️ IMPORTANTE: Requisito de Python

**Python 3.11 o 3.12 es REQUERIDO para soporte CUDA.**

Python 3.14 (y versiones muy nuevas) aún no tienen builds de PyTorch con soporte CUDA disponibles. Si tienes Python 3.14, necesitas instalar Python 3.12.

## Requisitos

- **GPU NVIDIA con CUDA** (RTX 4060 o superior recomendado)
- **Python 3.11 o 3.12** (NO Python 3.14)
- Al menos 6GB de VRAM recomendado
- Windows 10/11

## Pasos de Instalación

### 1. Instalar Python 3.12 (si no lo tienes)

1. Descarga Python 3.12 desde: https://www.python.org/downloads/
2. Durante la instalación, **marca la casilla "Add Python to PATH"**
3. Verifica la instalación:
   ```bash
   python --version
   # Debe mostrar: Python 3.12.x
   ```

### 2. Instalar PyTorch con soporte CUDA

Abre PowerShell o CMD y ejecuta:

```bash
# Para CUDA 12.1 (recomendado para RTX 4060)
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121

# O para CUDA 11.8 (si CUDA 12.1 no funciona)
pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu118
```

### 3. Verificar que PyTorch detecta tu GPU

```bash
python -c "import torch; print('PyTorch:', torch.__version__); print('CUDA disponible:', torch.cuda.is_available()); print('GPU:', torch.cuda.get_device_name(0) if torch.cuda.is_available() else 'No GPU detectada')"
```

**Debe mostrar:**
- `CUDA disponible: True`
- `GPU: NVIDIA GeForce RTX 4060` (o tu modelo de GPU)

Si muestra `CUDA disponible: False`, revisa:
- ¿Tienes Python 3.11 o 3.12? (no 3.14)
- ¿Instalaste PyTorch desde el índice de CUDA?
- ¿Tienes los drivers de NVIDIA actualizados?

### 4. Instalar Shap-E

```bash
pip install git+https://github.com/openai/shap-e.git
```

### 5. Instalar dependencias adicionales

```bash
pip install trimesh  # Para convertir PLY a GLB
```

### 6. Verificar instalación completa

```bash
python -c "from shap_e.diffusion.sample import sample_latents; print('Shap-E OK')"
```

## Uso

Una vez instalado, el script `scripts/shape_e_generate.py` estará listo para usar.

El controlador PHP (`controllers/ShapeEController.php`) detectará automáticamente Python 3.11/3.12 con CUDA y lo usará.

## Solución de Problemas

### Error: "CUDA disponible: False"

**Causa:** Python 3.14 no tiene builds de PyTorch con CUDA.

**Solución:**
1. Instala Python 3.12 desde python.org
2. Reinstala PyTorch con CUDA:
   ```bash
   pip install torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cu121
   ```
3. Reinstala Shap-E:
   ```bash
   pip install git+https://github.com/openai/shap-e.git
   ```

### Error: "No module named 'shap_e'"

**Solución:**
```bash
pip install git+https://github.com/openai/shap-e.git
```

### Error: "No module named 'trimesh'"

**Solución:**
```bash
pip install trimesh
```

### El sistema no encuentra Python 3.12

El controlador PHP busca Python en estas ubicaciones:
- `C:\Python312\python.exe`
- `C:\Program Files\Python312\python.exe`
- `C:\Users\[TU_USUARIO]\AppData\Local\Programs\Python\Python312\python.exe`
- `py -3.12`

Si Python está en otra ubicación, puedes:
1. Agregar Python al PATH del sistema
2. O crear un enlace simbólico en una de las rutas anteriores

## Notas

- La primera ejecución descargará los modelos de Shap-E (varios GB)
- La generación puede tardar 1-5 minutos dependiendo de tu GPU
- Los modelos se guardan en `public/glb/generated/`
- Con Python 3.14, Shap-E funcionará pero MUY LENTO (10-30 minutos por modelo) porque usará CPU

## Verificar tu versión de Python

```bash
python --version
```

Si muestra `Python 3.14.x`, necesitas instalar Python 3.12.