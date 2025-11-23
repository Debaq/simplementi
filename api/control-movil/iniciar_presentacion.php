<?php
/**
 * API: Iniciar Presentación desde Móvil
 *
 * Crea una sesión de presentación nueva y actualiza la vinculación
 * El PC detectará esto y redirigirá a presentador.php
 *
 * Método: POST
 * Body: { "pair_code": "XXXX-XXXX", "presentation_id": "demo_test" }
 */

session_start();

require_once __DIR__ . '/../helpers_proyeccion.php';

// Leer datos POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validar parámetros
if (!isset($data['pair_code']) || !isset($data['presentation_id'])) {
    returnError('missing_params', 'Faltan parámetros requeridos');
}

$pairCode = trim($data['pair_code']);
$presentationId = trim($data['presentation_id']);

// Validar que el usuario está autenticado
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('unauthorized', 'Debes estar autenticado');
}

// Validar código de emparejamiento
$linkData = validarCodigoEmparejamiento($pairCode);
if (!$linkData) {
    returnError('invalid_code', 'Código de emparejamiento inválido o expirado');
}

// Validar que la presentación existe
$presentationFile = __DIR__ . '/../../data/presentaciones/' . $presentationId . '.json';
if (!file_exists($presentationFile)) {
    returnError('invalid_presentation', 'La presentación no existe');
}

// Generar código de sesión único
$sessionId = generarCodigoSesion();

// Crear archivos de sesión
crearSesionPresentacion($sessionId, $presentationId);

// Actualizar vinculación con sesión activa
$linkData['status'] = 'active';
$linkData['session_id'] = $sessionId;
$linkData['presentation_id'] = $presentationId;
$linkData['mobile_device']['user_id'] = $_SESSION['user_id'] ?? 'guest';
$linkData['mobile_device']['paired_at'] = date('c');
$linkData['activated_at'] = date('c');

// Guardar vinculación actualizada
$linkFile = __DIR__ . '/../../data/projection_links/' . $pairCode . '.json';
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Retornar éxito
returnSuccess([
    'session_id' => $sessionId,
    'presentation_id' => $presentationId,
    'pair_code' => $pairCode
]);

/**
 * Genera un código de sesión único
 */
function generarCodigoSesion() {
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

/**
 * Crea los archivos de sesión para la presentación
 */
function crearSesionPresentacion($sessionId, $presentationId) {
    // Crear directorio de respuestas si no existe
    $responseDir = __DIR__ . '/../../data/respuestas';
    if (!file_exists($responseDir)) {
        mkdir($responseDir, 0755, true);
    }

    // Cargar datos de la presentación
    $presentationFile = __DIR__ . '/../../data/presentaciones/' . $presentationId . '.json';
    $presentationData = json_decode(file_get_contents($presentationFile), true);

    // Crear archivo de sesión
    $sessionData = [
        'codigo_sesion' => $sessionId,
        'presentacion_id' => $presentationId,
        'fecha_inicio' => date('c'),
        'estado' => 'activa',
        'pregunta_actual' => 0,
        'participantes' => [],
        'respuestas' => [],
        'configuracion' => $presentationData['configuracion'] ?? []
    ];

    $sessionFile = $responseDir . '/' . $sessionId . '.json';
    file_put_contents($sessionFile, json_encode($sessionData, JSON_PRETTY_PRINT));

    return true;
}
