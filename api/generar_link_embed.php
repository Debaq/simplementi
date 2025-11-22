<?php
/**
 * API para generar link limpio y código embed para compartir presentaciones
 */

// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar que se proporcionó un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Código de sesión no proporcionado'
    ]);
    exit;
}

// Buscar la sesión en los archivos
$session_files = glob("../data/respuestas/*/sesion_{$codigo_sesion}.json");

if (empty($session_files)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Sesión no encontrada'
    ]);
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'No se pudo leer el archivo de sesión'
    ]);
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'El archivo de sesión no tiene un formato JSON válido'
    ]);
    exit;
}

// Obtener información de la presentación
$test_id = $session_data['id_presentacion'];
$test_file = "../data/presentaciones/{$test_id}.json";

if (!file_exists($test_file)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Presentación no encontrada'
    ]);
    exit;
}

$test_json = file_get_contents($test_file);
$test_data = json_decode($test_json, true);

if ($test_data === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al leer la presentación'
    ]);
    exit;
}

// Obtener el dominio base del servidor
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = "{$protocol}://{$host}";

// Obtener la ruta base de la aplicación
$script_path = dirname($_SERVER['SCRIPT_NAME']);
if ($script_path !== '/') {
    $base_url .= rtrim($script_path, '/');
}
// Remover /api si está presente
$base_url = str_replace('/api', '', $base_url);

// Generar link limpio para participantes
$link_participante = "{$base_url}/participante.php?codigo={$codigo_sesion}";

// Generar link para el presentador
$link_presentador = "{$base_url}/presentador.php?codigo={$codigo_sesion}";

// Generar link para ver resultados
$link_resultados = "{$base_url}/resumen.php?codigo={$codigo_sesion}";

// Generar código embed HTML
$titulo_escapado = htmlspecialchars($test_data['titulo'], ENT_QUOTES, 'UTF-8');
$embed_html = '<iframe src="' . htmlspecialchars($link_participante, ENT_QUOTES, 'UTF-8') . '" width="100%" height="600" frameborder="0" allowfullscreen title="' . $titulo_escapado . '"></iframe>';

// Generar código embed responsive (Bootstrap)
$embed_responsive = '<div class="ratio ratio-16x9">
    <iframe src="' . htmlspecialchars($link_participante, ENT_QUOTES, 'UTF-8') . '" allowfullscreen title="' . $titulo_escapado . '"></iframe>
</div>';

// Preparar respuesta
$response = [
    'success' => true,
    'codigo_sesion' => $codigo_sesion,
    'titulo' => $test_data['titulo'],
    'descripcion' => $test_data['descripcion'] ?? '',
    'estado' => $session_data['estado'] ?? 'activa',
    'links' => [
        'participante' => $link_participante,
        'presentador' => $link_presentador,
        'resultados' => $link_resultados
    ],
    'embed' => [
        'html' => $embed_html,
        'responsive' => $embed_responsive
    ],
    'qr_api' => "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($link_participante)
];

echo json_encode($response, JSON_PRETTY_PRINT);
