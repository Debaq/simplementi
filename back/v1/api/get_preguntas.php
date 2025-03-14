<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo json_encode(['success' => false, 'error' => 'Código de sesión no proporcionado']);
    exit;
}

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents('../data/respuestas.json');
    if ($respuestas_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo respuestas.json']);
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo respuestas.json no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo respuestas.json: ' . $e->getMessage()]);
    exit;
}

// Buscar la sesión
$sesion = null;
foreach ($respuestas_data['sesiones'] as $s) {
    if ($s['codigo_sesion'] == $codigo_sesion) {
        $sesion = $s;
        break;
    }
}

if (!$sesion) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

echo json_encode([
    'success' => true,
    'pregunta_actual' => $sesion['pregunta_actual'],
    'estado' => $sesion['estado']
]);
?>
