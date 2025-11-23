<?php
/**
 * API: Listar Presentaciones Disponibles
 *
 * Retorna lista de presentaciones que pueden iniciarse
 * Usado en la interfaz pre-control para seleccionar presentación
 *
 * Método: GET
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

// Leer índice de presentaciones
$indexFile = __DIR__ . '/../../data/index.json';

if (!file_exists($indexFile)) {
    returnError('no_index', 'No hay presentaciones disponibles');
}

$indexData = json_decode(file_get_contents($indexFile), true);

if (!isset($indexData['presentaciones']) || empty($indexData['presentaciones'])) {
    returnError('no_presentations', 'No hay presentaciones disponibles');
}

// Formatear datos para el móvil
$presentaciones = [];
foreach ($indexData['presentaciones'] as $pres) {
    $presentaciones[] = [
        'id' => $pres['id'],
        'titulo' => $pres['titulo'],
        'descripcion' => $pres['descripcion'] ?? '',
        'autor' => $pres['autor'] ?? 'Desconocido',
        'num_preguntas' => $pres['num_preguntas'] ?? 0,
        'categorias' => $pres['categorias'] ?? []
    ];
}

returnSuccess([
    'presentaciones' => $presentaciones,
    'total' => count($presentaciones)
]);
