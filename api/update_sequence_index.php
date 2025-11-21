<?php
/**
 * API para actualizar el índice actual de la secuencia
 * Sincroniza el elemento actual con todos los participantes
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    $codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
    $sequence_index = isset($_GET['index']) ? intval($_GET['index']) : 0;

    if (empty($codigo_sesion)) {
        throw new Exception('Código de sesión no proporcionado');
    }

    // Buscar archivo de sesión
    $session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

    if (empty($session_files)) {
        throw new Exception('Sesión no encontrada');
    }

    $session_file = $session_files[0];
    $session_json = file_get_contents($session_file);
    $session_data = json_decode($session_json, true);

    if (!$session_data) {
        throw new Exception('Error al leer datos de sesión');
    }

    // Actualizar índice de secuencia
    $session_data['pdf_sequence_index'] = $sequence_index;

    // Guardar cambios
    $success = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

    if ($success === false) {
        throw new Exception('Error al guardar cambios');
    }

    $response['success'] = true;
    $response['message'] = 'Índice actualizado correctamente';
    $response['sequence_index'] = $sequence_index;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
