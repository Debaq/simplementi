<?php
/**
 * API para obtener el slide actual del PDF en una presentación
 * Devuelve la imagen del slide actual si hay PDF habilitado
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'pdf_enabled' => false,
    'slide_url' => null,
    'slide_number' => 0,
    'total_slides' => 0,
    'message' => ''
];

try {
    // Validar parámetros
    if (!isset($_GET['test']) || !isset($_GET['slide'])) {
        throw new Exception('Parámetros incompletos');
    }

    $test_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['test']);
    $slide_number = intval($_GET['slide']);

    // Cargar datos de la presentación
    $presentacion_file = "../data/presentaciones/{$test_id}.json";

    if (!file_exists($presentacion_file)) {
        throw new Exception('Presentación no encontrada');
    }

    $presentacion_json = file_get_contents($presentacion_file);
    $presentacion_data = json_decode($presentacion_json, true);

    if (!$presentacion_data) {
        throw new Exception('Error al leer datos de la presentación');
    }

    // Verificar si tiene PDF habilitado
    if (empty($presentacion_data['pdf_enabled']) || !isset($presentacion_data['pdf_images'])) {
        $response['message'] = 'PDF no habilitado';
        echo json_encode($response);
        exit;
    }

    $pdf_images = $presentacion_data['pdf_images'];
    $total_slides = count($pdf_images);

    // Validar número de slide
    if ($slide_number < 1 || $slide_number > $total_slides) {
        throw new Exception('Número de slide inválido');
    }

    // Obtener imagen del slide (índice 0-based)
    $slide_data = $pdf_images[$slide_number - 1];

    $response['success'] = true;
    $response['pdf_enabled'] = true;
    $response['slide_url'] = $slide_data['path'];
    $response['slide_number'] = $slide_number;
    $response['total_slides'] = $total_slides;
    $response['message'] = 'Slide obtenido correctamente';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
