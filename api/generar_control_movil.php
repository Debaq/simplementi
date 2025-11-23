<?php
/**
 * API para generar acceso directo al control móvil
 * Crea sesión y código de emparejamiento, vincula todo y devuelve URL
 */

session_start();
require_once 'helpers_proyeccion.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'control_url' => null,
    'codigo_sesion' => null,
    'message' => ''
];

try {
    // Verificar autenticación
    if (!isset($_SESSION['admin_auth']) && !isset($_SESSION['user_id'])) {
        throw new Exception('No autenticado');
    }

    $test_id = isset($_GET['test']) ? $_GET['test'] : '';

    if (empty($test_id)) {
        throw new Exception('ID de presentación no proporcionado');
    }

    // Verificar si existe la presentación
    $test_file = "../data/presentaciones/$test_id.json";

    if (!file_exists($test_file)) {
        throw new Exception('Presentación no encontrada');
    }

    // Leer datos de la presentación
    $test_json = file_get_contents($test_file);
    $test_data = json_decode($test_json, true);

    if (!$test_data) {
        throw new Exception('Error al leer datos de la presentación');
    }

    // 1. Generar código de sesión (6 caracteres)
    $codigo_sesion = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 3)), 0, 6);

    // 2. Crear sesión
    $resp_dir = "../data/respuestas/$test_id";
    if (!file_exists($resp_dir)) {
        mkdir($resp_dir, 0755, true);
    }

    $sesion_data = [
        'id_sesion' => $codigo_sesion,
        'id_presentacion' => $test_id,
        'fecha_inicio' => date('Y-m-d\TH:i:s'),
        'fecha_fin' => null,
        'estado' => 'activa',
        'pregunta_actual' => 0,
        'participantes' => [],
        'estadisticas' => [
            'total_participantes' => 0,
            'preguntas_completadas' => 0,
            'preguntas_por_completar' => count($test_data['preguntas']),
            'porcentaje_respuestas_correctas' => 0,
            'tiempo_promedio_respuesta' => 0
        ]
    ];

    // Inicializar pdf_sequence_index si hay secuencia de PDF
    if (!empty($test_data['pdf_sequence'])) {
        $sesion_data['pdf_sequence_index'] = 0;
    }

    $sesion_file = "$resp_dir/sesion_$codigo_sesion.json";
    file_put_contents($sesion_file, json_encode($sesion_data, JSON_PRETTY_PRINT));

    // 3. Generar código de emparejamiento
    $pair_code = generarCodigoEmparejamiento();

    // 4. Crear vinculación directa (estado 'active' desde el inicio)
    $linkData = [
        'pair_code' => $pair_code,
        'status' => 'active',
        'created_at' => time(),
        'expires_at' => time() + 7200, // 2 horas
        'session_id' => $codigo_sesion,
        'presentation_id' => $test_id,
        'mobile_device' => [
            'user_id' => $_SESSION['user_id'] ?? $_SESSION['admin_user'] ?? 'admin',
            'paired_at' => date('c')
        ]
    ];

    guardarVinculacion($pair_code, $linkData);

    // 5. Construir URL del control móvil
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    // Obtener el directorio base de la aplicación
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    $base_path = rtrim(str_replace('/api', '', $script_dir), '/');

    $control_url = $protocol . '://' . $host . $base_path . '/control-movil.php?code=' . $pair_code;

    $response['success'] = true;
    $response['control_url'] = $control_url;
    $response['codigo_sesion'] = $codigo_sesion;
    $response['pair_code'] = $pair_code;
    $response['message'] = 'Control móvil generado correctamente';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
