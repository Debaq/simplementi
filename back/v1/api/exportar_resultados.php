<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo json_encode(['success' => false, 'error' => 'Código de sesión no proporcionado']);
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

// Preparar datos para Excel
$resumen = [
    [
        'Título' => $test_data['titulo'],
        'Descripción' => $test_data['descripcion'],
        'Autor' => $test_data['autor'] ?? 'Admin',
        'Fecha inicio' => $session_data['fecha_inicio'],
        'Fecha fin' => $session_data['fecha_fin'] ?? 'En curso',
        'Estado' => $session_data['estado'],
        'Total participantes' => count($session_data['participantes']),
        'Total preguntas' => count($test_data['preguntas'])
    ]
];

// Datos de participantes
$participantes = [];
foreach ($session_data['participantes'] as $participante) {
    $participantes[] = [
        'ID Participante' => $participante['id'],
        'Fecha unión' => $participante['fecha_union'] ?? 'N/A',
        'Total respuestas' => count($participante['respuestas']),
        'Última actividad' => $participante['respuestas'] ? $participante['respuestas'][count($participante['respuestas']) - 1]['tiempo_respuesta'] ?? 'N/A' : 'Sin actividad'
    ];
}

// Datos por pregunta
$preguntas = [];
foreach ($test_data['preguntas'] as $pregunta) {
    $respuestas_pregunta = [];
    
    // Procesar respuestas para esta pregunta
    foreach ($session_data['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $pregunta['id']) {
                $respuestas_pregunta[] = [
                    'ID Participante' => $participante['id'],
                    'Respuesta' => $respuesta['respuesta'],
                    'Tiempo respuesta (s)' => $respuesta['tiempo_respuesta'] ?? 'N/A',
                    'Correcta' => isset($pregunta['respuesta_correcta']) ? 
                                 ($respuesta['respuesta'] == $pregunta['respuesta_correcta'] ? 'Sí' : 'No') : 'N/A'
                ];
            }
        }
    }
    
    // Contador de frecuencia para opciones múltiples
    $conteo_opciones = [];
    if ($pregunta['tipo'] == 'opcion_multiple') {
        foreach ($pregunta['opciones'] as $opcion) {
            $conteo_opciones[$opcion] = 0;
        }
        
        foreach ($respuestas_pregunta as $respuesta) {
            if (isset($conteo_opciones[$respuesta['Respuesta']])) {
                $conteo_opciones[$respuesta['Respuesta']]++;
            }
        }
    }
    
    $preguntas[] = [
        'id' => $pregunta['id'],
        'pregunta' => $pregunta['pregunta'],
        'tipo' => $pregunta['tipo'],
        'respuesta_correcta' => $pregunta['respuesta_correcta'] ?? 'N/A',
        'total_respuestas' => count($respuestas_pregunta),
        'conteo_opciones' => $conteo_opciones,
        'respuestas' => $respuestas_pregunta
    ];
}

// Devolver datos estructurados
echo json_encode([
    'success' => true,
    'codigo_sesion' => $codigo_sesion,
    'titulo' => $test_data['titulo'],
    'resumen' => $resumen,
    'participantes' => $participantes,
    'preguntas' => $preguntas
]);
?>