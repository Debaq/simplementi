<?php
/**
 * API: Verificar Estado de Vinculación
 *
 * Verifica el estado actual de un código de emparejamiento
 * Usado por el PC para saber cuándo redirigir a presentador.php
 *
 * Método: GET
 * Parámetros: code (código de emparejamiento)
 */

require_once __DIR__ . '/helpers_proyeccion.php';

// Validar parámetros
if (!isset($_GET['code'])) {
    returnError('missing_params', 'Falta el parámetro "code"');
}

$pairCode = trim($_GET['code']);

// Validar código
$linkData = validarCodigoEmparejamiento($pairCode);

if (!$linkData) {
    returnError('invalid_code', 'Código inválido o expirado');
}

// Retornar estado actual
returnSuccess([
    'status' => $linkData['status'],
    'session_id' => $linkData['session_id'] ?? null,
    'presentation_id' => $linkData['presentation_id'] ?? null,
    'paired_at' => $linkData['mobile_device']['paired_at'] ?? null,
    'expires_at' => $linkData['expires_at']
]);
