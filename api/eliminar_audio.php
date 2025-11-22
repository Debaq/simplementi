<?php
/**
 * API para eliminar archivos de audio de diapositivas
 */

// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

// Verificar parámetros requeridos
if (!isset($input['presentacion_id']) || !isset($input['page'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$presentacion_id = $input['presentacion_id'];
$page = intval($input['page']);

// Validar que la presentación existe
$presentacion_file = "../data/presentaciones/{$presentacion_id}.json";
if (!file_exists($presentacion_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Presentación no encontrada']);
    exit;
}

// Leer datos de la presentación
$presentacion_json = file_get_contents($presentacion_file);
$presentacion_data = json_decode($presentacion_json, true);

if ($presentacion_data === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al leer la presentación']);
    exit;
}

// Verificar que existe el audio para esta página
if (!isset($presentacion_data['audios_grabados'][$page])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Audio no encontrado']);
    exit;
}

// Obtener la ruta del archivo de audio
$audio_path = "../" . $presentacion_data['audios_grabados'][$page];

// Eliminar el archivo de audio si existe
if (file_exists($audio_path)) {
    if (!unlink($audio_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el archivo de audio']);
        exit;
    }
}

// Actualizar el JSON de la presentación
unset($presentacion_data['audios_grabados'][$page]);

// Guardar los cambios
if (file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la presentación']);
    exit;
}

// Responder con éxito
echo json_encode([
    'success' => true,
    'page' => $page
]);
