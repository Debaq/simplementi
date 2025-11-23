<?php
/**
 * API: Avanzar a la siguiente pregunta
 *
 * Método: POST
 * Body: { "codigo_sesion": "ABC123" }
 * Retorna: JSON con success y datos de la nueva pregunta
 */

header('Content-Type: application/json');

// Leer datos POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validar parámetros
if (!isset($data['codigo_sesion']) || empty($data['codigo_sesion'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'missing_params',
        'message' => 'Falta el código de sesión'
    ]);
    exit;
}

$codigo_sesion = trim($data['codigo_sesion']);

// Buscar el archivo de sesión
$session_files = glob(__DIR__ . "/../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'session_not_found',
        'message' => 'Sesión no encontrada'
    ]);
    exit;
}

$session_file = $session_files[0];

// Leer datos de sesión
$session_data = json_decode(file_get_contents($session_file), true);

if (!$session_data) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'invalid_json',
        'message' => 'Error al leer la sesión'
    ]);
    exit;
}

// Obtener información de la presentación
$test_id = $session_data['id_presentacion'];
$test_file = __DIR__ . "/../data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'presentation_not_found',
        'message' => 'Presentación no encontrada'
    ]);
    exit;
}

$presentation_data = json_decode(file_get_contents($test_file), true);
$total_preguntas = count($presentation_data['preguntas']);

// Avanzar a la siguiente pregunta
$pregunta_actual = $session_data['pregunta_actual'];
$nueva_pregunta = $pregunta_actual + 1;

// Validar que no exceda el total
if ($nueva_pregunta > $total_preguntas) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'end_of_questions',
        'message' => 'No hay más preguntas'
    ]);
    exit;
}

// Actualizar sesión
$session_data['pregunta_actual'] = $nueva_pregunta;

// Guardar cambios
$success = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

if (!$success) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'write_error',
        'message' => 'Error al guardar cambios'
    ]);
    exit;
}

// Retornar éxito
echo json_encode([
    'success' => true,
    'pregunta_actual' => $nueva_pregunta,
    'total_preguntas' => $total_preguntas,
    'message' => 'Pregunta avanzada correctamente'
]);
