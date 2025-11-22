<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$id_participante = isset($_GET['participante']) ? $_GET['participante'] : '';
$slide_number = isset($_GET['slide']) ? intval($_GET['slide']) : 0;

if (empty($codigo_sesion) || empty($id_participante) || $slide_number <= 0) {
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes']);
    exit;
}

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

// Buscar el participante y sus notas
foreach ($session_data['participantes'] as $participante) {
    if ($participante['id'] == $id_participante) {
        // Buscar notas para el slide específico
        if (isset($participante['notas'])) {
            foreach ($participante['notas'] as $nota) {
                if ($nota['slide_number'] === $slide_number) {
                    echo json_encode([
                        'success' => true,
                        'notas' => $nota['contenido'],
                        'timestamp' => $nota['timestamp']
                    ]);
                    exit;
                }
            }
        }

        // No hay notas para este slide
        echo json_encode([
            'success' => true,
            'notas' => '',
            'timestamp' => null
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Participante no encontrado']);
?>
