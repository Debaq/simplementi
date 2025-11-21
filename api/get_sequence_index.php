<?php
/**
 * API para obtener el índice actual de la secuencia
 * Usado por los participantes para sincronizar con el presentador
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'sequence_index' => null,
    'message' => ''
];

try {
    $codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

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

    // Obtener índice de secuencia (por defecto 0)
    $sequence_index = isset($session_data['pdf_sequence_index']) ? intval($session_data['pdf_sequence_index']) : 0;

    $response['success'] = true;
    $response['sequence_index'] = $sequence_index;
    $response['message'] = 'Índice obtenido correctamente';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
