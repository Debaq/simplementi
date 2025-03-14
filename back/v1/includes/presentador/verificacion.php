<?php
// Buscar información de la sesión
$session_files = glob("data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada con código: " . htmlspecialchars($codigo_sesion);
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    echo "Error: No se pudo leer el archivo de sesión.";
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    echo "Error: El archivo de sesión no tiene un formato JSON válido.";
    exit;
}

// Obtener información de la presentación
$test_id = $session_data['id_presentacion'];
$test_file = "data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Archivo de presentación no encontrado: $test_file";
    exit;
}

$test_json = file_get_contents($test_file);
if ($test_json === false) {
    echo "Error: No se pudo leer el archivo de presentación.";
    exit;
}

$test_data = json_decode($test_json, true);
if ($test_data === null) {
    echo "Error: El archivo de presentación no tiene un formato JSON válido.";
    exit;
}

// Verificar si el usuario está autenticado para presentaciones protegidas
if (isset($test_data['protegido']) && $test_data['protegido']) {
    if (!isset($_SESSION['auth_test']) || $_SESSION['auth_test'] !== $test_id) {
        header("Location: login.php?test=$test_id");
        exit;
    }
}

// Determinar qué mostrar según el estado de la presentación
$pregunta_actual_index = $session_data['pregunta_actual'];
$total_preguntas = count($test_data['preguntas']);

// Si es 0, mostrar pantalla de inicio con código QR
$show_intro = ($pregunta_actual_index === 0);

// Si no es pantalla de inicio, obtener la pregunta actual
$pregunta_actual = null;
if (!$show_intro && $pregunta_actual_index <= $total_preguntas) {
    $pregunta_actual = $test_data['preguntas'][$pregunta_actual_index - 1];
}

// Determinar si estamos mostrando una respuesta
$mostrar_respuesta = isset($_GET['mostrar_respuesta']) && $_GET['mostrar_respuesta'] == '1' && 
                    isset($pregunta_actual['respuesta_correcta']);