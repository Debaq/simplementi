# Carpeta de Traducciones

Esta carpeta contiene los archivos JSON con las traducciones para SimpleMenti.

## Estructura de Archivos

- `es.json` - Traducciones en español (idioma por defecto)
- Puedes agregar más idiomas creando archivos con el código ISO 639-1: `en.json`, `fr.json`, etc.

## Formato de los Archivos

Los archivos deben estar en formato JSON válido:

```json
{
    "sección": {
        "clave": "Traducción"
    }
}
```

## Agregar un Nuevo Idioma

1. Crea un archivo con el código de idioma (2 letras minúsculas): `en.json`
2. Copia la estructura de `es.json`
3. Traduce todos los textos
4. El nuevo idioma aparecerá automáticamente en el selector

## Documentación Completa

Para más información, consulta: `/docs/SISTEMA_TRADUCCIONES.md`

## Códigos de Idioma ISO 639-1

- `es` - Español
- `en` - English
- `fr` - Français
- `de` - Deutsch
- `it` - Italiano
- `pt` - Português
- `ca` - Català
- `eu` - Euskara
