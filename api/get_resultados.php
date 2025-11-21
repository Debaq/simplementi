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

// El $id_pregunta recibido es el índice 1-based.
$pregunta_index = $id_pregunta - 1;

if (!isset($preguntas_data['preguntas'][$pregunta_index])) {
    echo json_encode(['success' => false, 'error' => 'Pregunta no encontrada para el índice proporcionado.']);
    exit;
}

// Obtener la pregunta y su ID único real
$pregunta = $preguntas_data['preguntas'][$pregunta_index];
$id_pregunta_unica = $pregunta['id'];

// Contabilizar las respuestas
$resultados = [];
$total_participantes = count($respuestas_data['participantes']);
$total_respuestas = 0; // Este contará el número bruto de respuestas (ej. palabras en una nube)
$total_correctas = 0;
$total_tiempo = 0;

// Inicializar resultados para que todas las opciones aparezcan en el gráfico
if ($pregunta['tipo'] == 'opcion_multiple' && isset($pregunta['opciones'])) {
    foreach ($pregunta['opciones'] as $opcion) {
        $resultados[$opcion] = 0;
    }
} else if ($pregunta['tipo'] == 'verdadero_falso') {
    $resultados['true'] = 0;
    $resultados['false'] = 0;
}

// Contar respuestas y participantes que han respondido
$participantes_con_respuesta = [];
foreach ($respuestas_data['participantes'] as $participante) {
    foreach ($participante['respuestas'] as $respuesta) {
        // Usar el ID único de la pregunta para la comparación
        if (isset($respuesta['id_pregunta']) && $respuesta['id_pregunta'] == $id_pregunta_unica) {
            $total_respuestas++;

            // Contar participante solo una vez por pregunta
            if (!in_array($participante['id'], $participantes_con_respuesta)) {
                $participantes_con_respuesta[] = $participante['id'];
            }

            if (isset($respuesta['tiempo_respuesta'])) {
                $total_tiempo += $respuesta['tiempo_respuesta'];
            }
            
            // Conteo específico por tipo de pregunta
            if ($pregunta['tipo'] == 'nube_palabras' || $pregunta['tipo'] == 'palabra_libre') {
                $palabra = trim($respuesta['respuesta']);
                if (!empty($palabra)) {
                    if (!isset($resultados[$palabra])) $resultados[$palabra] = 0;
                    $resultados[$palabra]++;
                }
            } else { // Opcion multiple y V/F
                $respuesta_str = strval($respuesta['respuesta']);
                if (isset($resultados[$respuesta_str])) {
                    $resultados[$respuesta_str]++;
                }
            }

            // Contar correctas
            if (isset($pregunta['respuesta_correcta'])) {
                if ($pregunta['tipo'] == 'verdadero_falso') {
                    $respuesta_bool = filter_var($respuesta['respuesta'], FILTER_VALIDATE_BOOLEAN);
                    $pregunta_respuesta_correcta_bool = filter_var($pregunta['respuesta_correcta'], FILTER_VALIDATE_BOOLEAN);
                    if ($respuesta_bool === $pregunta_respuesta_correcta_bool) {
                        $total_correctas++;
                    }
                } else if ($respuesta['respuesta'] == $pregunta['respuesta_correcta']) {
                    $total_correctas++;
                }
            }
        }
    }
}

$participantes_que_respondieron = count($participantes_con_respuesta);

if ($pregunta['tipo'] == 'nube_palabras' || $pregunta['tipo'] == 'palabra_libre') {
    arsort($resultados);
    $resultados = array_slice($resultados, 0, 20, true);
}

// Calcular estadísticas
$estadisticas = [
    'total_respuestas' => $participantes_que_respondieron,
    'total_correctas' => $total_correctas,
    'porcentaje_correctas' => $total_respuestas > 0 ? round(($total_correctas / $total_respuestas) * 100) : 0,
    'tiempo_promedio' => $participantes_que_respondieron > 0 ? round($total_tiempo / $participantes_que_respondieron, 1) : 0,
    'total_pendientes' => $total_participantes - $participantes_que_respondieron
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