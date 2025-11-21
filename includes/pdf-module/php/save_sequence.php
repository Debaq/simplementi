<?php
// Este archivo se encargará de guardar la secuencia de la presentación
// Implementación básica para el futuro

// Directorio para almacenar secuencias (ruta absoluta desde la raíz del proyecto)
$sequences_dir = '../../../data/sequences/';

// Asegurarse de que el directorio existe
if (!file_exists($sequences_dir)) {
    mkdir($sequences_dir, 0755, true);
}

// Variables de respuesta
$response = [
    'success' => false,
    'message' => '',
    'sequence_id' => ''
];

// Obtener datos de la solicitud
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data) {
    // Validar datos mínimos
    if (isset($data['pdf_id']) && isset($data['items']) && is_array($data['items'])) {
        // Generar ID único para la secuencia
        $sequence_id = uniqid('seq_');
        $data['sequence_id'] = $sequence_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Guardar en archivo JSON
        $file_name = $sequences_dir . $sequence_id . '.json';
        $json_content = json_encode($data, JSON_PRETTY_PRINT);
        
        if (file_put_contents($file_name, $json_content)) {
            $response['success'] = true;
            $response['message'] = 'Secuencia guardada correctamente.';
            $response['sequence_id'] = $sequence_id;
        } else {
            $response['message'] = 'Error al guardar la secuencia.';
        }
    } else {
        $response['message'] = 'Datos incorrectos o incompletos.';
    }
} else {
    $response['message'] = 'No se han recibido datos válidos.';
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);