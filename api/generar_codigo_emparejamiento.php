<?php
/**
 * API: Generar Código de Emparejamiento
 *
 * Genera un código único y QR para vincular un dispositivo móvil
 * con la sesión de presentación actual.
 *
 * Llamado por: presentador.php cuando el docente solicita control móvil
 * Método: GET
 * Parámetros: session (código de sesión)
 */

require_once __DIR__ . '/helpers_proyeccion.php';

// Limpiar códigos expirados periódicamente (10% de probabilidad)
if (random_int(1, 10) === 1) {
    limpiarCodigosExpirados();
}

// Validar parámetros
if (!isset($_GET['session'])) {
    returnError('missing_params', 'Falta el parámetro "session"');
}

$sessionId = trim($_GET['session']);

// Validar que la sesión existe
$presentacionId = obtenerPresentacionId($sessionId);
if (!$presentacionId) {
    returnError('invalid_session', 'La sesión especificada no existe');
}

// Generar código de emparejamiento
$pairCode = generarCodigoEmparejamiento();

// Crear datos del QR
$qrData = [
    'type' => 'projection_pair',
    'code' => $pairCode,
    'session_id' => $sessionId,
    'timestamp' => date('c'),
    'server_url' => getServerUrl()
];

// Crear archivo de vinculación
$linkData = [
    'pair_code' => $pairCode,
    'created_at' => date('c'),
    'expires_at' => date('c', time() + 30), // Expira en 30 segundos
    'status' => 'waiting',
    'qr_data' => $qrData,
    'session' => [
        'session_id' => $sessionId,
        'presentation_id' => $presentacionId
    ]
];

// Guardar archivo
$linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Generar imagen QR
$qrImage = generarQRBase64(json_encode($qrData));

// Retornar respuesta
returnSuccess([
    'pair_code' => $pairCode,
    'qr_data' => $qrData,
    'qr_image' => $qrImage,
    'expires_in' => 30,
    'session_id' => $sessionId,
    'presentation_id' => $presentacionId
]);
