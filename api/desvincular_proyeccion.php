<?php
/**
 * API: Desvincular Proyección
 *
 * Desvincula un dispositivo móvil de la proyección/presentador.
 * Llamado desde el control móvil cuando el docente quiere desconectar.
 *
 * Método: POST
 * Requiere: Sesión autenticada
 */

require_once __DIR__ . '/helpers_proyeccion.php';

session_start();

// Validar autenticación
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('not_authenticated', 'Debes iniciar sesión');
}

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['pair_code'])) {
    returnError('missing_params', 'Falta el código de emparejamiento');
}

$pairCode = $data['pair_code'];
$linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';

if (!file_exists($linkFile)) {
    returnError('not_found', 'No se encontró la vinculación');
}

// Eliminar archivo de vinculación
@unlink($linkFile);

returnSuccess([
    'message' => 'Desvinculación exitosa'
]);
