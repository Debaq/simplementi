<?php
/**
 * API: Actualizar Posición del Puntero Virtual
 *
 * Actualiza la posición del puntero láser virtual en la proyección.
 * Coordenadas normalizadas (0-1) para independencia de resolución.
 *
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

if (!isset($data['pair_code']) || !isset($data['x']) || !isset($data['y'])) {
    returnError('missing_params', 'Faltan parámetros requeridos');
}

$pairCode = $data['pair_code'];
$x = floatval($data['x']);
$y = floatval($data['y']);
$enabled = isset($data['enabled']) ? (bool)$data['enabled'] : true;

// Validar coordenadas (0-1)
if ($x < 0 || $x > 1 || $y < 0 || $y > 1) {
    returnError('invalid_coords', 'Las coordenadas deben estar entre 0 y 1');
}

// Validar código de emparejamiento
$linkFile = __DIR__ . '/../../data/projection_links/' . $pairCode . '.json';

if (!file_exists($linkFile)) {
    returnError('invalid_code', 'Código de emparejamiento no encontrado');
}

$linkData = json_decode(file_get_contents($linkFile), true);

// Verificar expiración (códigos pueden tener vida larga una vez vinculados)
if ($linkData['status'] !== 'paired') {
    returnError('not_paired', 'El dispositivo no está vinculado');
}

// Actualizar posición del puntero en el archivo de vinculación
$linkData['state'] = $linkData['state'] ?? [];
$linkData['state']['pointer'] = [
    'enabled' => $enabled,
    'x' => $x,
    'y' => $y,
    'timestamp' => microtime(true) // Timestamp con microsegundos para mejor sync
];
$linkData['state']['last_update'] = date('c');

// Guardar cambios
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Retornar éxito
returnSuccess([
    'message' => 'Puntero actualizado',
    'pointer' => $linkData['state']['pointer']
]);
