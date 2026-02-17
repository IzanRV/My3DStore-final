# 🔧 Solución: Error "El sistema no puede encontrar el archivo especificado"

## Problema
El error indica que **Docker Desktop no está corriendo**.

## Solución Paso a Paso

### 1. Iniciar Docker Desktop

1. **Abre Docker Desktop:**
   - Presiona `Windows + S` y busca "Docker Desktop"
   - O busca "Docker Desktop" en el menú de inicio
   - Haz clic para abrirlo

2. **Espera a que Docker Desktop se inicie:**
   - Verás el ícono de Docker en la bandeja del sistema (abajo a la derecha)
   - Espera hasta que el ícono muestre "Docker Desktop is running"
   - Esto puede tardar 1-2 minutos

3. **Verifica que Docker esté corriendo:**
   Abre PowerShell y ejecuta:
   ```powershell
   docker info
   ```
   
   Si ves información sobre Docker (no errores), está funcionando ✅

### 2. Una vez que Docker Desktop esté corriendo

Ejecuta de nuevo:
```powershell
docker-compose up -d --build
```

O usa el script:
```powershell
.\start-docker.bat
```

## Verificación Rápida

Ejecuta esto en PowerShell para verificar:
```powershell
docker ps
```

- ✅ **Si funciona**: Verás una lista (puede estar vacía, está bien)
- ❌ **Si da error**: Docker Desktop no está corriendo

## Notas Importantes

- Docker Desktop debe estar **siempre corriendo** para usar Docker
- Si cierras Docker Desktop, los contenedores se detendrán
- Puedes configurar Docker Desktop para iniciarse automáticamente con Windows

## Configurar Inicio Automático

1. Abre Docker Desktop
2. Ve a Settings (⚙️)
3. General → "Start Docker Desktop when you log in"
4. Activa la opción

## Si Docker Desktop no se inicia

1. Reinicia Docker Desktop
2. Si sigue sin funcionar, reinicia Windows
3. Verifica que Docker Desktop esté instalado correctamente
