<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar datos
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nueva_pregunta = isset($_GET['pregunta']) ? intval($_GET['pregunta']) : 0;

if (empty($codigo_sesion) || $nueva_pregunta <= 0) {
    echo "Error: Parámetros incorrectos (código de sesión o número de pregunta)";
    exit;
}

// Buscar el archivo de sesión
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada con código: " . htmlspecialchars($codigo_sesion);
    exit;
}

$session_file = $session_files[0];

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents($session_file);
    if ($respuestas_json === false) {
        echo "Error: No se pudo leer el archivo de sesión: $session_file";
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo "Error: El archivo de sesión no tiene un formato JSON válido";
        exit;
    }
} catch (Exception $e) {
    echo "Error al leer el archivo de sesión: " . $e->getMessage();
    exit;
}

// Obtener información de la presentación
$test_id = $respuestas_data['id_presentacion'];
$test_file = "../data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Archivo de presentación no encontrado: $test_file";
    exit;
}

try {
    $preguntas_json = file_get_contents($test_file);
    if ($preguntas_json === false) {
        echo "Error: No se pudo leer el archivo de presentación";
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo "Error: El archivo de presentación no tiene un formato JSON válido";
        exit;
    }
} catch (Exception $e) {
    echo "Error al leer el archivo de presentación: " . $e->getMessage();
    exit;
}

// Validar número de pregunta
if ($nueva_pregunta < 0 || $nueva_pregunta > count($preguntas_data['preguntas'])) {
    echo "Error: Número de pregunta inválido. Debe estar entre 0 y " . count($preguntas_data['preguntas']);
    exit;
}

// Actualizar pregunta actual
$respuestas_data['pregunta_actual'] = $nueva_pregunta;

// Guardar los cambios
try {
    $success = file_put_contents($session_file, json_encode($respuestas_data, JSON_PRETTY_PRINT));
    if ($success === false) {
        echo "Error: No se pudo escribir en el archivo de sesión";
        exit;
    }
} catch (Exception $e) {
    echo "Error al escribir en el archivo de sesión: " . $e->getMessage();
    exit;
}

// Redireccionar de vuelta a la página del presentador
header("Location: ../presentador.php?codigo=$codigo_sesion");
exit;
?>