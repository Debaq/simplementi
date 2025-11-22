<?php
/**
 * API: Toggle Puntero Virtual
 *
 * Activa o desactiva el puntero virtual.
 * Método: POST
 * Requiere: Sesión autenticada + pair_code válido
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

session_start();

// Validar autenticación
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('not_authenticated', 'Debes iniciar sesión');
}

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['pair_code']) || !isset($data['enabled'])) {
    returnError('missing_params', 'Faltan parámetros requeridos');
}

$pairCode = $data['pair_code'];
$enabled = (bool)$data['enabled'];

// Validar código de emparejamiento
$linkFile = __DIR__ . '/../../data/projection_links/' . $pairCode . '.json';

if (!file_exists($linkFile)) {
    returnError('invalid_code', 'Código de emparejamiento no encontrado');
}

$linkData = json_decode(file_get_contents($linkFile), true);

if ($linkData['status'] !== 'paired') {
    returnError('not_paired', 'El dispositivo no está vinculado');
}

// Actualizar estado del puntero
$linkData['state'] = $linkData['state'] ?? [];
$linkData['state']['pointer'] = $linkData['state']['pointer'] ?? ['x' => 0.5, 'y' => 0.5];
$linkData['state']['pointer']['enabled'] = $enabled;
$linkData['state']['pointer']['timestamp'] = microtime(true);
$linkData['state']['last_update'] = date('c');

// Guardar cambios
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Retornar éxito
returnSuccess([
    'message' => $enabled ? 'Puntero activado' : 'Puntero desactivado',
    'pointer' => $linkData['state']['pointer']
]);
