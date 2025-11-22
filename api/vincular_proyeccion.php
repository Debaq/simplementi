<?php
/**
 * API: Vincular Proyección
 *
 * Vincula un dispositivo móvil con una proyección/presentador
 * mediante el código QR escaneado.
 *
 * Llamado por: control-movil.php tras escanear QR
 * Método: POST
 * Requiere: Sesión autenticada (cookie PHPSESSID)
 */

require_once __DIR__ . '/helpers_proyeccion.php';

// Iniciar sesión
session_start();

// Validar autenticación
// En SimpleMenti, la sesión autenticada se guarda en $_SESSION['auth_test']
// Si no existe, el usuario no está autenticado
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('not_authenticated', 'Debes iniciar sesión para vincular un dispositivo');
}

// Obtener datos del POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    returnError('invalid_json', 'Los datos enviados no son JSON válido');
}

// Validar parámetros
if (!isset($data['qr_data']) || !isset($data['qr_data']['code'])) {
    returnError('missing_params', 'Faltan datos del QR en la solicitud');
}

$qrData = $data['qr_data'];
$pairCode = $qrData['code'];
$qrTimestamp = strtotime($qrData['timestamp']);

// Validar QR no expirado (30 segundos desde generación)
if (time() - $qrTimestamp > 30) {
    returnError('qr_expired', 'El código QR ha expirado. Genera uno nuevo desde el presentador.');
}

// Cargar archivo de vinculación
$linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';

if (!file_exists($linkFile)) {
    returnError('invalid_code', 'El código de emparejamiento no es válido');
}

$linkData = json_decode(file_get_contents($linkFile), true);

// Verificar que no esté ya emparejado
if ($linkData['status'] === 'paired') {
    returnError('already_paired', 'Esta proyección ya está vinculada a otro dispositivo');
}

// Verificar expiración del archivo
$fileExpiresAt = strtotime($linkData['expires_at']);
if (time() > $fileExpiresAt) {
    @unlink($linkFile);
    returnError('code_expired', 'El código de emparejamiento ha expirado');
}

// Obtener información del dispositivo móvil
$mobileDevice = [
    'session_token' => session_id(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'paired_at' => date('c')
];

// Actualizar vinculación
$linkData['status'] = 'paired';
$linkData['mobile_device'] = $mobileDevice;

// Si el usuario tiene email en sesión, guardarlo
if (isset($_SESSION['user_email'])) {
    $linkData['session']['created_by'] = $_SESSION['user_email'];
}

// Guardar archivo actualizado
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Obtener información de la sesión para retornar
$sessionId = $linkData['session']['session_id'];
$presentacionId = $linkData['session']['presentation_id'];

// Intentar obtener información de la sesión actual (slide actual, total, etc)
$sessionInfo = obtenerInfoSesion($sessionId, $presentacionId);

// Retornar éxito
returnSuccess([
    'pair_code' => $pairCode,
    'session' => array_merge($linkData['session'], $sessionInfo),
    'message' => 'Proyección vinculada correctamente'
]);

/**
 * Obtiene información actual de la sesión
 * @param string $sessionId
 * @param string $presentacionId
 * @return array
 */
function obtenerInfoSesion($sessionId, $presentacionId) {
    $sessionFile = __DIR__ . '/../data/respuestas/' . $presentacionId . '/sesion_' . $sessionId . '.json';

    if (!file_exists($sessionFile)) {
        return [
            'current_slide' => 0,
            'total_slides' => 0
        ];
    }

    $sessionData = json_decode(file_get_contents($sessionFile), true);

    // Cargar presentación para obtener total de slides
    $presentacionFile = __DIR__ . '/../data/presentaciones/' . $presentacionId . '.json';
    $totalSlides = 0;

    if (file_exists($presentacionFile)) {
        $presentacion = json_decode(file_get_contents($presentacionFile), true);

        if (isset($presentacion['pdf_sequence'])) {
            $totalSlides = count($presentacion['pdf_sequence']);
        } elseif (isset($presentacion['preguntas'])) {
            $totalSlides = count($presentacion['preguntas']);
        }
    }

    return [
        'current_slide' => $sessionData['pdf_sequence_index'] ?? $sessionData['pregunta_actual'] ?? 0,
        'total_slides' => $totalSlides,
        'participants_count' => isset($sessionData['participantes']) ? count($sessionData['participantes']) : 0
    ];
}
