<?php
header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo json_encode(['error' => 'Código de sesión no proporcionado']);
    exit;
}

// Leer los datos actuales
$respuestas_json = file_get_contents('../data/respuestas.json');
$respuestas_data = json_decode($respuestas_json, true);

// Buscar la sesión
$sesion = null;
foreach ($respuestas_data['sesiones'] as $s) {
    if ($s['codigo_sesion'] == $codigo_sesion) {
        $sesion = $s;
        break;
    }
}

if (!$sesion) {
    echo json_encode(['error' => 'Sesión no encontrada']);
    exit;
}

echo json_encode([
    'success' => true,
    'pregunta_actual' => $sesion['pregunta_actual'],
    'estado' => $sesion['estado']
]);
?>