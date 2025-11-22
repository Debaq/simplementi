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
$anotaciones = isset($data['anotaciones']) ? $data['anotaciones'] : [];

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

// Buscar o crear participante
$participante_encontrado = false;
foreach ($session_data['participantes'] as &$participante) {
    if ($participante['id'] === $id_participante) {
        $participante_encontrado = true;

        // Inicializar array de anotaciones si no existe
        if (!isset($participante['anotaciones'])) {
            $participante['anotaciones'] = [];
        }

        // Buscar si ya existe una anotación para este slide
        $anotacion_encontrada = false;
        foreach ($participante['anotaciones'] as &$anotacion) {
            if ($anotacion['slide_number'] === $slide_number) {
                $anotacion['datos'] = $anotaciones;
                $anotacion['fecha_actualizacion'] = date('Y-m-d\TH:i:s');
                $anotacion_encontrada = true;
                break;
            }
        }

        // Si no existe, crear nueva anotación
        if (!$anotacion_encontrada) {
            $participante['anotaciones'][] = [
                'slide_number' => $slide_number,
                'datos' => $anotaciones,
                'fecha_creacion' => date('Y-m-d\TH:i:s'),
                'fecha_actualizacion' => date('Y-m-d\TH:i:s')
            ];
        }

        break;
    }
}

if (!$participante_encontrado) {
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
    'mensaje' => 'Anotaciones guardadas correctamente',
    'total_strokes' => count($anotaciones)
]);
?>
