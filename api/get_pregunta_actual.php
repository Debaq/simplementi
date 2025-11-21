<?php
// Mostrar todos los errores para desarrollo pero capturarlos para devolver JSON
ini_set('display_errors', 0); // Desactivamos la salida de errores
error_reporting(E_ALL); // Pero seguimos reportándolos

// Deshabilitar caché para asegurar datos frescos
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json');

try {
    // Verificar parámetros
    $codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

    if (empty($codigo_sesion)) {
        throw new Exception('Código de sesión no proporcionado');
    }

    // Buscar el archivo de sesión
    $session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

    if (empty($session_files)) {
        throw new Exception('Sesión no encontrada');
    }

    $session_file = $session_files[0];
    $session_json = file_get_contents($session_file);
    
    if ($session_json === false) {
        throw new Exception('No se pudo leer el archivo de sesión');
    }
    
    $session_data = json_decode($session_json, true);
    
    if ($session_data === null) {
        throw new Exception('El archivo de sesión no tiene un formato JSON válido');
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'pregunta_actual' => $session_data['pregunta_actual'],
        'estado' => $session_data['estado'],
        'timestamp' => time() // Añadir timestamp para evitar caché
    ];

    // Agregar slide del PDF si está disponible
    if (isset($session_data['pdf_slide_actual'])) {
        $response['pdf_slide'] = $session_data['pdf_slide_actual'];
    }

    // Devolver los datos
    echo json_encode($response);
} catch (Exception $e) {
    // Devolver error en formato JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>