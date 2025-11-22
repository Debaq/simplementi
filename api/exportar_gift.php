<?php
/**
 * API para exportar preguntas en formato GIFT
 */

// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el exportador GIFT
require_once('../includes/editar/gift_exporter.php');

// Verificar que se proporcionó un ID de presentación
$id_presentacion = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id_presentacion)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de presentación no proporcionado']);
    exit;
}

// Verificar que existe el archivo de presentación
$presentacion_file = "../data/presentaciones/{$id_presentacion}.json";

if (!file_exists($presentacion_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'Presentación no encontrada']);
    exit;
}

// Leer el archivo de presentación
$presentacion_json = file_get_contents($presentacion_file);

if ($presentacion_json === false) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo leer el archivo de presentación']);
    exit;
}

$presentacion_data = json_decode($presentacion_json, true);

if ($presentacion_data === null) {
    http_response_code(500);
    echo json_encode(['error' => 'El archivo de presentación no tiene un formato JSON válido']);
    exit;
}

// Obtener las preguntas
$preguntas = $presentacion_data['preguntas'] ?? [];

if (empty($preguntas)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay preguntas para exportar']);
    exit;
}

// Generar nombre de archivo basado en el título de la presentación
$titulo_presentacion = $presentacion_data['titulo'] ?? $id_presentacion;
$nombre_archivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titulo_presentacion) . '.gift';

// Exportar las preguntas
GiftExporter::exportToFile($preguntas, $nombre_archivo);
