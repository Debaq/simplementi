<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$participante_id = isset($_GET['participante']) ? $_GET['participante'] : '';

if (empty($codigo_sesion) || empty($participante_id)) {
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes']);
    exit;
}

// Buscar la sesión en los archivos
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de sesión']);
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    echo json_encode(['success' => false, 'error' => 'El archivo de sesión no tiene un formato JSON válido']);
    exit;
}

// Obtener información de la presentación
$test_id = $session_data['id_presentacion'];
$test_file = "../data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo json_encode(['success' => false, 'error' => 'Archivo de presentación no encontrado']);
    exit;
}

$test_json = file_get_contents($test_file);
if ($test_json === false) {
    echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de presentación']);
    exit;
}

$test_data = json_decode($test_json, true);
if ($test_data === null) {
    echo json_encode(['success' => false, 'error' => 'El archivo de presentación no tiene un formato JSON válido']);
    exit;
}

// Buscar el participante
$participante = null;
foreach ($session_data['participantes'] as $p) {
    if ($p['id'] == $participante_id) {
        $participante = $p;
        break;
    }
}

if (!$participante) {
    echo json_encode(['success' => false, 'error' => 'Participante no encontrado']);
    exit;
}

// Calcular estadísticas
$total_preguntas = count($test_data['preguntas']);
$total_respondidas = count($participante['respuestas']);
$total_correctas = 0;
$total_incorrectas = 0;
$tiempo_total = 0;

$respuestas_map = [];
foreach ($participante['respuestas'] as $respuesta) {
    $respuestas_map[$respuesta['id_pregunta']] = $respuesta;

    if (isset($respuesta['tiempo_respuesta'])) {
        $tiempo_total += $respuesta['tiempo_respuesta'];
    }
}

$preguntas_con_respuestas = [];
foreach ($test_data['preguntas'] as $pregunta) {
    $respondida = isset($respuestas_map[$pregunta['id']]);
    $respuesta_dada = $respondida ? $respuestas_map[$pregunta['id']]['respuesta'] : null;
    $es_correcta = false;

    if ($respondida && isset($pregunta['respuesta_correcta'])) {
        $tipo_pregunta = $pregunta['tipo'];

        if ($tipo_pregunta == 'verdadero_falso') {
            $respuesta_dada_boolean = ($respuesta_dada === 'true');
            $es_correcta = ($respuesta_dada_boolean === $pregunta['respuesta_correcta']);
        } else {
            $es_correcta = ($respuesta_dada == $pregunta['respuesta_correcta']);
        }

        if ($es_correcta) {
            $total_correctas++;
        } else {
            $total_incorrectas++;
        }
    }

    $preguntas_con_respuestas[] = [
        'pregunta' => $pregunta,
        'respondida' => $respondida,
        'respuesta_dada' => $respuesta_dada,
        'es_correcta' => $es_correcta,
        'tiempo_respuesta' => $respondida && isset($respuestas_map[$pregunta['id']]['tiempo_respuesta']) ?
                             $respuestas_map[$pregunta['id']]['tiempo_respuesta'] : null
    ];
}

$puntaje = $total_correctas * 10;
$porcentaje_acierto = $total_respondidas > 0 ? round(($total_correctas / $total_respondidas) * 100) : 0;
$tiempo_promedio = $total_respondidas > 0 ? round($tiempo_total / $total_respondidas, 1) : 0;

// Retornar resultados
echo json_encode([
    'success' => true,
    'participante' => [
        'nombre' => $participante['nombre'],
        'id' => $participante['id']
    ],
    'presentacion' => [
        'titulo' => $test_data['titulo'],
        'descripcion' => isset($test_data['descripcion']) ? $test_data['descripcion'] : ''
    ],
    'estadisticas' => [
        'puntaje' => $puntaje,
        'porcentaje_acierto' => $porcentaje_acierto,
        'total_correctas' => $total_correctas,
        'total_incorrectas' => $total_incorrectas,
        'total_respondidas' => $total_respondidas,
        'total_preguntas' => $total_preguntas,
        'tiempo_promedio' => $tiempo_promedio
    ],
    'preguntas' => $preguntas_con_respuestas
]);
?>
