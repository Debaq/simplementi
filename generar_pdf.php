<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la biblioteca TCPDF
require_once('lib/tcpdf/tcpdf.php');

// Verificar si hay un código de sesión y un ID de participante
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$participante_id = isset($_GET['participante']) ? $_GET['participante'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

if (empty($participante_id)) {
    // Si no se proporciona un ID de participante, intenta obtenerlo de la cookie
    if (isset($_COOKIE['participante_id'])) {
        $participante_id = $_COOKIE['participante_id'];
    } else {
        echo "Error: No se pudo identificar al participante.";
        exit;
    }
}

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

// Buscar el participante
$participante = null;
foreach ($session_data['participantes'] as $p) {
    if ($p['id'] == $participante_id) {
        $participante = $p;
        break;
    }
}

if (!$participante) {
    echo "Error: Participante no encontrado.";
    exit;
}

// Calcular estadísticas
$total_preguntas = count($test_data['preguntas']);
$total_respondidas = count($participante['respuestas']);
$total_correctas = 0;
$total_incorrectas = 0;
$tiempo_total = 0;

// Mapear respuestas del participante para fácil acceso
$respuestas_map = [];
foreach ($participante['respuestas'] as $respuesta) {
    $respuestas_map[$respuesta['id_pregunta']] = $respuesta;
    
    if (isset($respuesta['tiempo_respuesta'])) {
        $tiempo_total += $respuesta['tiempo_respuesta'];
    }
}

// Para cada pregunta, verificar si la respuesta es correcta
$preguntas_con_respuestas = [];
foreach ($test_data['preguntas'] as $pregunta) {
    $respondida = isset($respuestas_map[$pregunta['id']]);
    $respuesta_dada = $respondida ? $respuestas_map[$pregunta['id']]['respuesta'] : null;
    $es_correcta = false;
    
    if ($respondida && isset($pregunta['respuesta_correcta'])) {
        $tipo_pregunta = $pregunta['tipo'];

        if ($tipo_pregunta == 'verdadero_falso') {
            $respuesta_dada_boolean = ($respuesta_dada === 'true');
            $respuesta_correcta_boolean = ($pregunta['respuesta_correcta'] === 'Verdadero');
            $es_correcta = ($respuesta_dada_boolean === $respuesta_correcta_boolean);
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

// Calcular puntaje
$puntaje = $total_correctas * 10;
$porcentaje_acierto = $total_respondidas > 0 ? round(($total_correctas / $total_respondidas) * 100) : 0;
$tiempo_promedio = $total_respondidas > 0 ? round($tiempo_total / $total_respondidas, 1) : 0;

// Crear el documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SimpleMenti');
$pdf->SetTitle('Resumen de Resultados');
$pdf->SetSubject('Resumen de Resultados para ' . $participante['nombre']);

// Contenido del PDF
$pdf->AddPage();

$html = '<h1>Resumen de Resultados</h1>';
$html .= '<h2>' . htmlspecialchars($test_data['titulo']) . '</h2>';
$html .= '<h3>Participante: ' . htmlspecialchars($participante['nombre']) . '</h3>';

$html .= '<p><strong>Puntos totales:</strong> ' . $puntaje . '</p>';
$html .= '<p><strong>Porcentaje de acierto:</strong> ' . $porcentaje_acierto . '%</p>';
$html .= '<p><strong>Respuestas correctas:</strong> ' . $total_correctas . '</p>';
$html .= '<p><strong>Respuestas incorrectas:</strong> ' . $total_incorrectas . '</p>';
$html .= '<p><strong>Tiempo promedio:</strong> ' . $tiempo_promedio . 's</p>';

$html .= '<h2>Detalle de respuestas</h2>';

foreach ($preguntas_con_respuestas as $index => $item) {
    $html .= '<h4>Pregunta ' . ($index + 1) . ': ' . htmlspecialchars($item['pregunta']['pregunta']) . '</h4>';
    if ($item['respondida']) {
        $html .= '<p>Tu respuesta: ' . htmlspecialchars($item['respuesta_dada']) . '</p>';
        if (isset($item['pregunta']['respuesta_correcta'])) {
            if ($item['es_correcta']) {
                $html .= '<p><strong>Resultado:</strong> <span style="color:green;">Correcta</span></p>';
            } else {
                $html .= '<p><strong>Resultado:</strong> <span style="color:red;">Incorrecta</span></p>';
                $html .= '<p>Respuesta correcta: ' . htmlspecialchars($item['pregunta']['respuesta_correcta']) . '</p>';
            }
        }
        if (isset($item['pregunta']['explicacion'])) {
            $html .= '<p>Explicación: ' . htmlspecialchars($item['pregunta']['explicacion']) . '</p>';
        }
    } else {
        $html .= '<p>No respondida</p>';
        if (isset($item['pregunta']['respuesta_correcta'])) {
            $html .= '<p>Respuesta correcta: ' . htmlspecialchars($item['pregunta']['respuesta_correcta']) . '</p>';
        }
    }
    $html .= '<hr>';
}

$pdf->writeHTML($html, true, false, true, false, '');

// Cerrar y generar el PDF
$pdf->Output('resumen.pdf', 'I');

?>