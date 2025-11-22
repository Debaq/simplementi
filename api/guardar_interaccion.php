<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Leer datos JSON del cuerpo de la petición
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Verificar parámetros
if (empty($data['codigo_sesion']) || empty($data['id_participante']) || empty($data['type'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes']);
    exit;
}

$codigo_sesion = $data['codigo_sesion'];
$id_participante = $data['id_participante'];
$nombre_participante = isset($data['nombre_participante']) ? $data['nombre_participante'] : 'Anónimo';
$type = $data['type'];
$interaction_data = isset($data['data']) ? $data['data'] : [];
$timestamp = isset($data['timestamp']) ? $data['timestamp'] : date('Y-m-d\TH:i:s');

// Buscar la sesión en los archivos
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

$session_file = $session_files[0];
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

// Inicializar array de interacciones si no existe
if (!isset($session_data['interacciones'])) {
    $session_data['interacciones'] = [];
}

// Crear registro de interacción
$interaccion = [
    'id' => uniqid(),
    'id_participante' => $id_participante,
    'nombre_participante' => $nombre_participante,
    'type' => $type,
    'data' => $interaction_data,
    'timestamp' => $timestamp
];

// Para "levantar mano", actualizar estado en lugar de agregar múltiples registros
if ($type === 'raise_hand') {
    // Buscar si ya existe un registro de mano levantada para este participante
    $found = false;
    foreach ($session_data['interacciones'] as &$int) {
        if ($int['type'] === 'raise_hand' && $int['id_participante'] === $id_participante) {
            $int['data'] = $interaction_data;
            $int['timestamp'] = $timestamp;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $session_data['interacciones'][] = $interaccion;
    }
} else {
    // Para otros tipos, siempre agregar
    $session_data['interacciones'][] = $interaccion;
}

// Guardar cambios
$result = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

if ($result === false) {
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo de sesión']);
    exit;
}

echo json_encode([
    'success' => true,
    'mensaje' => 'Interacción guardada correctamente',
    'type' => $type
]);
?>
