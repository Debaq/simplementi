<?php
// Mostrar todos los errores para desarrollo
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

// Buscar el archivo de sesión
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

$session_file = $session_files[0];

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents($session_file);
    if ($respuestas_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de sesión']);
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo de sesión no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo de sesión: ' . $e->getMessage()]);
    exit;
}

// Obtener información de la presentación
$test_id = $respuestas_data['id_presentacion'];
$test_file = "../data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo json_encode(['success' => false, 'error' => 'Archivo de presentación no encontrado']);
    exit;
}

try {
    $preguntas_json = file_get_contents($test_file);
    if ($preguntas_json === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de presentación']);
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo json_encode(['success' => false, 'error' => 'El archivo de presentación no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo de presentación: ' . $e->getMessage()]);
    exit;
}

// Encontrar la pregunta
$pregunta = null;
foreach ($preguntas_data['preguntas'] as $p) {
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
$total_participantes = count($respuestas_data['participantes']);
$total_respuestas = 0;
$total_correctas = 0;
$total_tiempo = 0;

if ($pregunta['tipo'] == 'opcion_multiple' || $pregunta['tipo'] == 'verdadero_falso') {
    // Inicializar contador para cada opción
    if ($pregunta['tipo'] == 'opcion_multiple' && isset($pregunta['opciones'])) {
        foreach ($pregunta['opciones'] as $opcion) {
            $resultados[$opcion] = 0;
        }
    } else if ($pregunta['tipo'] == 'verdadero_falso') {
        $resultados['true'] = 0;
        $resultados['false'] = 0;
    }
    
    // Contar respuestas
    foreach ($respuestas_data['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $id_pregunta) {
                $total_respuestas++;
                
                if (isset($respuesta['tiempo_respuesta'])) {
                    $total_tiempo += $respuesta['tiempo_respuesta'];
                }
                
                if (isset($resultados[$respuesta['respuesta']])) {
                    $resultados[$respuesta['respuesta']]++;
                }
                
                if (isset($pregunta['respuesta_correcta']) && $respuesta['respuesta'] == $pregunta['respuesta_correcta']) {
                    $total_correctas++;
                }
            }
        }
    }
} else if ($pregunta['tipo'] == 'nube_palabras' || $pregunta['tipo'] == 'palabra_libre') {
    // Contar frecuencia de palabras
    foreach ($respuestas_data['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $id_pregunta) {
                $total_respuestas++;
                
                if (isset($respuesta['tiempo_respuesta'])) {
                    $total_tiempo += $respuesta['tiempo_respuesta'];
                }
                
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

// Calcular estadísticas
$estadisticas = [
    'total_respuestas' => $total_respuestas,
    'total_correctas' => $total_correctas,
    'porcentaje_correctas' => $total_respuestas > 0 ? round(($total_correctas / $total_respuestas) * 100) : 0,
    'tiempo_promedio' => $total_respuestas > 0 ? round($total_tiempo / $total_respuestas, 1) : 0,
    'total_pendientes' => $total_participantes - $total_respuestas
];

// Devolver los resultados
echo json_encode([
    'success' => true,
    'total_participantes' => $total_participantes,
    'participantes' => $respuestas_data['participantes'],
    'resultados' => $resultados,
    'estadisticas' => $estadisticas
]);
?>