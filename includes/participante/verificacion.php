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

// Identificar al participante
$participante_id = isset($_COOKIE['participante_id']) ? $_COOKIE['participante_id'] : '';
if (empty($participante_id)) {
    $participante_id = 'p' . mt_rand(100000, 999999);
    setcookie('participante_id', $participante_id, time() + 86400 * 30, '/'); // 30 días
}

// Obtener el índice de la pregunta actual
$pregunta_actual_index = $session_data['pregunta_actual'];

// Verificar si la sesión está finalizada y redirigir al resumen si es así
if ($session_data['estado'] === 'finalizada') {
    // Primero verificamos si el participante existe y ha respondido al menos una pregunta
    $participante_encontrado = false;
    foreach ($session_data['participantes'] as $participante) {
        if ($participante['id'] === $participante_id && !empty($participante['respuestas'])) {
            $participante_encontrado = true;
            break;
        }
    }
    
    // Solo redirigir al resumen si el participante ha respondido preguntas
    if ($participante_encontrado) {
        header("Location: participante_resumen.php?codigo=$codigo_sesion&participante=$participante_id");
        exit;
    }
    // Si el participante no respondió preguntas, permitir que continúe con la pantalla actual
    // (generalmente será la de pantalla_completado.php)
}