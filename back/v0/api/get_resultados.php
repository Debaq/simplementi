<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$id_pregunta = isset($_GET['pregunta']) ? intval($_GET['pregunta']) : 0;

if (empty($codigo_sesion) || $id_pregunta <= 0) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incorrectos']);
    exit;
}

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents('../data/respuestas.json');
    if ($respuestas_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo respuestas.json']);
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo respuestas.json no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo respuestas.json: ' . $e->getMessage()]);
    exit;
}

// Buscar la sesión
$sesion = null;
foreach ($respuestas_data['sesiones'] as $s) {
    if ($s['codigo_sesion'] == $codigo_sesion) {
        $sesion = $s;
        break;
    }
}

if (!$sesion) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

// Obtener información de la presentación
try {
    $preguntas_json = file_get_contents('../data/preguntas.json');
    if ($preguntas_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo preguntas.json']);
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo preguntas.json no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo preguntas.json: ' . $e->getMessage()]);
    exit;
}

$presentacion = null;
foreach ($preguntas_data['presentaciones'] as $p) {
    if ($p['id'] == $sesion['id_presentacion']) {
        $presentacion = $p;
        break;
    }
}

if (!$presentacion) {
    echo json_encode(['success' => false, 'error' => 'Presentación no encontrada']);
    exit;
}

// Encontrar la pregunta
$pregunta = null;
foreach ($presentacion['preguntas'] as $p) {
    if ($p['id'] == $id_pregunta) {
        $pregunta = $p;
        break;
    }
}

if (!$pregunta) {
    echo json_encode(['success' => false, 'error' => 'Pregunta no encontrada']);
    exit;
}

// Contabilizar las respuestas
$resultados = [];
$total_participantes = count($sesion['participantes']);

if ($pregunta['tipo'] == 'opcion_multiple') {
    // Inicializar contador para cada opción
    foreach ($pregunta['opciones'] as $opcion) {
        $resultados[$opcion] = 0;
    }
    
    // Contar respuestas
    foreach ($sesion['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $id_pregunta) {
                if (isset($resultados[$respuesta['respuesta']])) {
                    $resultados[$respuesta['respuesta']]++;
                }
            }
        }
    }
} else if ($pregunta['tipo'] == 'palabra_libre' || $pregunta['tipo'] == 'nube_palabras') {
    // Contar frecuencia de palabras
    foreach ($sesion['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $id_pregunta) {
                $palabra = trim($respuesta['respuesta']);
                if (!empty($palabra)) {
                    if (isset($resultados[$palabra])) {
                        $resultados[$palabra]++;
                    } else {
                        $resultados[$palabra] = 1;
                    }
                }
            }
        }
    }
    
    // Ordenar por frecuencia (mayor a menor)
    arsort($resultados);
    
    // Limitar a las 20 palabras más frecuentes
    $resultados = array_slice($resultados, 0, 20, true);
}

// Devolver los resultados
echo json_encode([
    'success' => true,
    'total_participantes' => $total_participantes,
    'participantes' => $sesion['participantes'],
    'resultados' => $resultados
]);
?>
