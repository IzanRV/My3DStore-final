# Guía de Instalación en Windows

## Paso 1: Crear el Entorno Virtual

En PowerShell, ejecuta:

```powershell
cd ai_3d_service
python -m venv venv
```

Si tienes problemas de permisos, intenta ejecutar PowerShell como Administrador.

## Paso 2: Activar el Entorno Virtual

En PowerShell:

```powershell
.\venv\Scripts\Activate.ps1
```

Si obtienes un error de política de ejecución, ejecuta primero:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Luego intenta activar el entorno virtual de nuevo.

## Paso 3: Instalar Dependencias

Con el entorno virtual activado:

```powershell
pip install -r requirements.txt
```

## Paso 4: Configurar Variables de Entorno

Crea un archivo `.env` basado en `.env.example`:

```powershell
Copy-Item .env.example .env
```

Luego edita `.env` con tus configuraciones.

## Paso 5: Iniciar el Servidor

Opción 1 - Usar el script:
```powershell
.\start.bat
```

Opción 2 - Manualmente:
```powershell
python main.py
```

## Solución de Problemas

### Error: "source no se reconoce"
En Windows PowerShell, usa `.\venv\Scripts\Activate.ps1` en lugar de `source venv/bin/activate`.

### Error de Política de Ejecución
Si PowerShell bloquea la ejecución de scripts, ejecuta:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Error de Permisos
- Ejecuta PowerShell como Administrador
- O crea el entorno virtual en otra ubicación con permisos de escritura

### Verificar que Python está instalado
```powershell
python --version
```

Debería mostrar Python 3.9 o superior.
