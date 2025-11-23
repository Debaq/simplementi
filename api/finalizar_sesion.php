<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detectar si es petición JSON
$is_json_request = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
                    strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

// Verificar parámetros
if ($is_json_request) {
    header('Content-Type: application/json');
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $codigo_sesion = isset($data['codigo_sesion']) ? $data['codigo_sesion'] : '';
} else {
    $codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
}

if (empty($codigo_sesion)) {
    if ($is_json_request) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'missing_params',
            'message' => 'Código de sesión no proporcionado'
        ]);
    } else {
        echo "Error: Código de sesión no proporcionado.";
    }
    exit;
}

// Buscar la sesión en los archivos
$session_files = glob(__DIR__ . "/../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    if ($is_json_request) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'session_not_found',
            'message' => 'Sesión no encontrada'
        ]);
    } else {
        echo "Error: Sesión no encontrada.";
    }
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    if ($is_json_request) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'read_error',
            'message' => 'No se pudo leer el archivo de sesión'
        ]);
    } else {
        echo "Error: No se pudo leer el archivo de sesión.";
    }
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    if ($is_json_request) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'invalid_json',
            'message' => 'El archivo de sesión no tiene un formato JSON válido'
        ]);
    } else {
        echo "Error: El archivo de sesión no tiene un formato JSON válido.";
    }
    exit;
}

// Finalizar la sesión
$session_data['estado'] = 'finalizada';
$session_data['fecha_fin'] = date('Y-m-d\TH:i:s');

// Guardar los cambios
$result = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

if ($result === false) {
    if ($is_json_request) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'write_error',
            'message' => 'No se pudo actualizar el archivo de sesión'
        ]);
    } else {
        echo "Error: No se pudo actualizar el archivo de sesión.";
    }
    exit;
}

// Responder según el tipo de petición
if ($is_json_request) {
    echo json_encode([
        'success' => true,
        'message' => 'Sesión finalizada correctamente',
        'redirect' => 'resumen.php?codigo=' . $codigo_sesion
    ]);
} else {
    // Redireccionar a la página de resumen
    header("Location: ../resumen.php?codigo=$codigo_sesion");
}
exit;
