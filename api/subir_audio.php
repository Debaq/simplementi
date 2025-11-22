<?php
/**
 * API para subir archivos de audio de diapositivas
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

// Verificar parámetros requeridos
if (!isset($_POST['presentacion_id']) || !isset($_POST['page']) || !isset($_FILES['audio'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$presentacion_id = $_POST['presentacion_id'];
$page = intval($_POST['page']);
$audio_file = $_FILES['audio'];

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

// Verificar que el número de página es válido
if ($page < 1 || $page > ($presentacion_data['pdf_pages'] ?? 0)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Número de página inválido']);
    exit;
}

// Verificar que el archivo de audio es válido
if ($audio_file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Error al subir el archivo']);
    exit;
}

// Crear directorio de audios si no existe
$audios_dir = "../data/presentaciones/{$presentacion_id}/audios";
if (!file_exists($audios_dir)) {
    if (!mkdir($audios_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio de audios']);
        exit;
    }
}

// Convertir WebM a MP3 si es posible, o guardar como está
$audio_filename = "slide_{$page}.webm";
$audio_path = "{$audios_dir}/{$audio_filename}";

// Mover el archivo subido
if (!move_uploaded_file($audio_file['tmp_name'], $audio_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo de audio']);
    exit;
}

// Actualizar el JSON de la presentación
if (!isset($presentacion_data['audios_grabados'])) {
    $presentacion_data['audios_grabados'] = [];
}

$presentacion_data['audios_grabados'][$page] = "data/presentaciones/{$presentacion_id}/audios/{$audio_filename}";

// Guardar los cambios
if (file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la presentación']);
    exit;
}

// Responder con éxito
echo json_encode([
    'success' => true,
    'audio_url' => "data/presentaciones/{$presentacion_id}/audios/{$audio_filename}",
    'page' => $page
]);
