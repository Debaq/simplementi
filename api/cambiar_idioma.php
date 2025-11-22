<?php
/**
 * API para cambiar el idioma de la aplicación
 *
 * Método: POST
 * Parámetros:
 *   - language: Código del idioma (ej: 'es', 'en')
 *
 * Respuesta JSON:
 *   - success: true/false
 *   - message: Mensaje de resultado
 *   - language: Idioma actual después del cambio
 */

session_start();

// Incluir el sistema de traducciones
require_once '../includes/Translation.php';

header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Obtener el idioma del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);
$language = isset($input['language']) ? $input['language'] : null;

// Validar que se haya enviado el idioma
if (empty($language)) {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetro "language" requerido'
    ]);
    exit;
}

// Validar formato del código de idioma (2 letras)
if (!preg_match('/^[a-z]{2}$/', $language)) {
    echo json_encode([
        'success' => false,
        'message' => 'Código de idioma inválido. Use 2 letras minúsculas (ej: es, en)'
    ]);
    exit;
}

// Intentar cambiar el idioma
$translation = Translation::getInstance();
$result = $translation->setLanguage($language);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Idioma cambiado correctamente',
        'language' => $translation->getCurrentLanguage()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Idioma no disponible. Verifique que el archivo de traducción exista.',
        'language' => $translation->getCurrentLanguage()
    ]);
}
