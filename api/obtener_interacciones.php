<?php
// Mostrar todos los errores para desarrollo
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

// Obtener interacciones
$interacciones = isset($session_data['interacciones']) ? $session_data['interacciones'] : [];

// Filtrar y agrupar por tipo
$hands_raised = [];
$questions = [];
$understanding = [];
$reactions = [];

foreach ($interacciones as $interaccion) {
    switch ($interaccion['type']) {
        case 'raise_hand':
            if ($interaccion['data']['raised']) {
                $hands_raised[] = $interaccion;
            }
            break;
        case 'question':
            $questions[] = $interaccion;
            break;
        case 'understanding':
            if ($interaccion['data']['level'] !== null) {
                $understanding[] = $interaccion;
            }
            break;
        case 'reaction':
            $reactions[] = $interaccion;
            break;
    }
}

// Calcular estadísticas de comprensión
$understanding_stats = [
    'confused' => 0,
    'understood' => 0,
    'total' => 0
];

foreach ($understanding as $item) {
    if ($item['data']['level'] === 'confused') {
        $understanding_stats['confused']++;
    } elseif ($item['data']['level'] === 'understood') {
        $understanding_stats['understood']++;
    }
    $understanding_stats['total']++;
}

// Últimas reacciones (últimas 10)
$recent_reactions = array_slice(array_reverse($reactions), 0, 10);

// Preguntas sin responder (últimas 20)
$recent_questions = array_slice(array_reverse($questions), 0, 20);

echo json_encode([
    'success' => true,
    'hands_raised' => $hands_raised,
    'hands_raised_count' => count($hands_raised),
    'questions' => $recent_questions,
    'questions_count' => count($questions),
    'understanding_stats' => $understanding_stats,
    'recent_reactions' => $recent_reactions,
    'total_interactions' => count($interacciones)
]);
?>
