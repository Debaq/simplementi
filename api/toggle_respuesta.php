<?php
/**
 * API: Mostrar/Ocultar Respuesta
 *
 * Método: POST
 * Body: { "codigo_sesion": "ABC123" }
 * Retorna: JSON con success y estado de mostrar_respuesta
 */

header('Content-Type: application/json');
session_start();

// Leer datos POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validar parámetros
if (!isset($data['codigo_sesion']) || empty($data['codigo_sesion'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'missing_params',
        'message' => 'Falta el código de sesión'
    ]);
    exit;
}

$codigo_sesion = trim($data['codigo_sesion']);

// El estado de mostrar_respuesta se maneja vía query string en presentador.php
// Necesitamos verificar el estado actual y togglearlo

// Buscar el archivo de sesión para validar que existe
$session_files = glob(__DIR__ . "/../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'session_not_found',
        'message' => 'Sesión no encontrada'
    ]);
    exit;
}

// Usamos una variable de sesión para trackear el estado
$session_key = 'mostrar_respuesta_' . $codigo_sesion;

// Toggle del estado
if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = false;
}

$_SESSION[$session_key] = !$_SESSION[$session_key];
$mostrar_respuesta = $_SESSION[$session_key];

// Retornar éxito
echo json_encode([
    'success' => true,
    'mostrar_respuesta' => $mostrar_respuesta,
    'message' => $mostrar_respuesta ? 'Mostrando respuesta' : 'Ocultando respuesta'
]);
