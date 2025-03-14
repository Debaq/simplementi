<?php
// Mostrar todos los errores
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

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents('../data/respuestas.json');
    if ($respuestas_json === false) {
        echo "Error: No se pudo leer el archivo respuestas.json";
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo "Error: El archivo respuestas.json no tiene un formato JSON válido";
        exit;
    }
} catch (Exception $e) {
    echo "Error al leer el archivo respuestas.json: " . $e->getMessage();
    exit;
}

// Buscar la sesión
$sesion_index = -1;
foreach ($respuestas_data['sesiones'] as $index => $sesion) {
    if ($sesion['codigo_sesion'] == $codigo_sesion) {
        $sesion_index = $index;
        break;
    }
}

if ($sesion_index === -1) {
    echo "Error: Sesión no encontrada con código '$codigo_sesion'";
    exit;
}

// Obtener información de la presentación para validar el número de pregunta
try {
    $preguntas_json = file_get_contents('../data/preguntas.json');
    if ($preguntas_json === false) {
        echo "Error: No se pudo leer el archivo preguntas.json";
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo "Error: El archivo preguntas.json no tiene un formato JSON válido";
        exit;
    }
} catch (Exception $e) {
    echo "Error al leer el archivo preguntas.json: " . $e->getMessage();
    exit;
}

$presentacion = null;
foreach ($preguntas_data['presentaciones'] as $p) {
    if ($p['id'] == $respuestas_data['sesiones'][$sesion_index]['id_presentacion']) {
        $presentacion = $p;
        break;
    }
}

if (!$presentacion) {
    echo "Error: Presentación no encontrada";
    exit;
}

// Validar número de pregunta
if ($nueva_pregunta < 1 || $nueva_pregunta > count($presentacion['preguntas'])) {
    echo "Error: Número de pregunta inválido. Debe estar entre 1 y " . count($presentacion['preguntas']);
    exit;
}

// Actualizar pregunta actual
$respuestas_data['sesiones'][$sesion_index]['pregunta_actual'] = $nueva_pregunta;

// Guardar los cambios
try {
    $success = file_put_contents('../data/respuestas.json', json_encode($respuestas_data, JSON_PRETTY_PRINT));
    if ($success === false) {
        echo "Error: No se pudo escribir en el archivo respuestas.json";
        exit;
    }
} catch (Exception $e) {
    echo "Error al escribir en el archivo respuestas.json: " . $e->getMessage();
    exit;
}

// Redireccionar de vuelta a la página del presentador
header("Location: ../presentador.php?codigo=$codigo_sesion");
exit;
?>
