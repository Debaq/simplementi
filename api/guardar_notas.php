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
if (empty($data['codigo_sesion']) || empty($data['id_participante']) || !isset($data['slide_number'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes']);
    exit;
}

$codigo_sesion = $data['codigo_sesion'];
$id_participante = $data['id_participante'];
$slide_number = intval($data['slide_number']);
$notas = isset($data['notas']) ? $data['notas'] : '';
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

// Buscar el participante
$found_participante = false;
foreach ($session_data['participantes'] as &$participante) {
    if ($participante['id'] == $id_participante) {
        $found_participante = true;

        // Inicializar array de notas si no existe
        if (!isset($participante['notas'])) {
            $participante['notas'] = [];
        }

        // Buscar si ya existe una nota para este slide
        $found_note = false;
        foreach ($participante['notas'] as &$nota) {
            if ($nota['slide_number'] === $slide_number) {
                $nota['contenido'] = $notas;
                $nota['timestamp'] = $timestamp;
                $found_note = true;
                break;
            }
        }

        // Si no existe, crear nueva nota
        if (!$found_note) {
            $participante['notas'][] = [
                'slide_number' => $slide_number,
                'contenido' => $notas,
                'timestamp' => $timestamp
            ];
        }

        break;
    }
}

if (!$found_participante) {
    echo json_encode(['success' => false, 'error' => 'Participante no encontrado']);
    exit;
}

// Guardar cambios
$result = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

if ($result === false) {
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo de sesión']);
    exit;
}

echo json_encode([
    'success' => true,
    'mensaje' => 'Notas guardadas correctamente',
    'slide_number' => $slide_number
]);
?>
