<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Deshabilitar caché para asegurar datos frescos
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo json_encode(['success' => false, 'error' => 'Código de sesión no proporcionado']);
    exit;
}

// Buscar el archivo de sesión
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

$session_file = $session_files[0];

try {
    $session_json = file_get_contents($session_file);
    if ($session_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de sesión']);
        exit;
    }
    
    $session_data = json_decode($session_json, true);
    if ($session_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo de sesión no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo de sesión: ' . $e->getMessage()]);
    exit;
}

// Devolver el conteo de participantes
echo json_encode([
    'success' => true,
    'total_participantes' => count($session_data['participantes'])
]);
?>