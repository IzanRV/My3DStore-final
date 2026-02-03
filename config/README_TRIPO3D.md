# Configuración de Tripo3D AI

## Cómo obtener tu API Key

1. Visita [Tripo3D Studio](https://studio.tripo3d.ai/)
2. Crea una cuenta o inicia sesión
3. Ve a la sección de API/Settings
4. Genera una nueva API Key
5. Copia la API Key

## Configurar la API Key

Edita el archivo `config/tripo3d.php` y reemplaza `'TU_API_KEY_AQUI'` con tu API Key real:

```php
'api_key' => 'tu_api_key_real_aqui',
```

## Nota importante

La API de Tripo3D puede requerir autenticación específica. Si el endpoint o la estructura de la API es diferente, necesitarás ajustar el controlador `TripoController.php` según la documentación oficial de Tripo3D.

## Documentación

Consulta la documentación oficial de Tripo3D:
- [Tripo3D API Documentation](https://www.tripo3d.ai/api)
- [Tripo3D Studio](https://studio.tripo3d.ai/)
