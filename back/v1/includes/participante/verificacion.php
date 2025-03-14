<?php
// Buscar la sesión en los archivos
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
    echo "Error: Archivo de presentación no encontrado.";
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

// Verificar si la presentación está activa
if ($session_data['estado'] !== 'activa') {
    echo "Esta sesión ha finalizado. Gracias por participar.";
    exit;
}

// Obtener el índice de la pregunta actual
$pregunta_actual_index = $session_data['pregunta_actual'];