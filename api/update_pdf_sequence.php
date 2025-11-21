<?php
/**
 * API para actualizar la secuencia PDF sin recargar la página
 * Guarda el nuevo orden de slides y preguntas
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Obtener datos
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);

    if (!$data || !isset($data['presentation_id']) || !isset($data['sequence'])) {
        throw new Exception('Datos incompletos');
    }

    $presentation_id = preg_replace('/[^a-zA-Z0-9_]/', '', $data['presentation_id']);
    $new_sequence = $data['sequence'];

    // Validar que sea un array
    if (!is_array($new_sequence)) {
        throw new Exception('Secuencia inválida');
    }

    // Cargar presentación
    $presentation_file = "../data/presentaciones/{$presentation_id}.json";

    if (!file_exists($presentation_file)) {
        throw new Exception('Presentación no encontrada');
    }

    $presentation_json = file_get_contents($presentation_file);
    $presentation_data = json_decode($presentation_json, true);

    if (!$presentation_data) {
        throw new Exception('Error al leer presentación');
    }

    // Actualizar secuencia
    $presentation_data['pdf_sequence'] = $new_sequence;

    // Guardar cambios
    $result = file_put_contents($presentation_file, json_encode($presentation_data, JSON_PRETTY_PRINT));

    if ($result === false) {
        throw new Exception('Error al guardar cambios');
    }

    $response['success'] = true;
    $response['message'] = 'Secuencia actualizada correctamente';
    $response['total_items'] = count($new_sequence);

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
