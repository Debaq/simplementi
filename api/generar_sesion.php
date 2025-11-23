<?php
/**
 * API para generar un código de sesión sin redirigir
 * Devuelve el código en formato JSON
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'codigo_sesion' => null,
    'message' => ''
];

try {
    $test_id = isset($_GET['test']) ? $_GET['test'] : '';

    if (empty($test_id)) {
        throw new Exception('ID de presentación no proporcionado');
    }

    // Verificar si existe la presentación
    $test_file = "../data/presentaciones/$test_id.json";

    if (!file_exists($test_file)) {
        throw new Exception('Presentación no encontrada');
    }

    // Leer datos de la presentación
    $test_json = file_get_contents($test_file);
    $test_data = json_decode($test_json, true);

    if (!$test_data) {
        throw new Exception('Error al leer datos de la presentación');
    }

    // Generar código único para la sesión (6 caracteres)
    $codigo_nuevo = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 3)), 0, 6);

    // Crear directorio para las respuestas si no existe
    $resp_dir = "../data/respuestas/$test_id";
    if (!file_exists($resp_dir)) {
        mkdir($resp_dir, 0755, true);
    }

    // Crear archivo de sesión
    $sesion_data = [
        'id_sesion' => $codigo_nuevo,
        'id_presentacion' => $test_id,
        'fecha_inicio' => date('Y-m-d\TH:i:s'),
        'fecha_fin' => null,
        'estado' => 'activa',
        'pregunta_actual' => 0, // Empezamos con 0 para mostrar la pantalla de QR inicial
        'participantes' => [],
        'estadisticas' => [
            'total_participantes' => 0,
            'preguntas_completadas' => 0,
            'preguntas_por_completar' => count($test_data['preguntas']),
            'porcentaje_respuestas_correctas' => 0,
            'tiempo_promedio_respuesta' => 0
        ]
    ];

    // Inicializar pdf_sequence_index si hay secuencia de PDF
    if (!empty($test_data['pdf_sequence'])) {
        $sesion_data['pdf_sequence_index'] = 0;
    }

    $sesion_file = "$resp_dir/sesion_$codigo_nuevo.json";
    file_put_contents($sesion_file, json_encode($sesion_data, JSON_PRETTY_PRINT));

    $response['success'] = true;
    $response['codigo_sesion'] = $codigo_nuevo;
    $response['message'] = 'Sesión creada correctamente';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
