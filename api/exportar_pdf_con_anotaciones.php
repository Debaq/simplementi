<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la biblioteca TCPDF
require_once('../lib/tcpdf/tcpdf.php');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$participante_id = isset($_GET['participante']) ? $_GET['participante'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

if (empty($participante_id)) {
    if (isset($_COOKIE['participante_id'])) {
        $participante_id = $_COOKIE['participante_id'];
    } else {
        echo "Error: No se pudo identificar al participante.";
        exit;
    }
}

// Buscar la sesión en los archivos
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada.";
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
$test_file = "../data/presentaciones/$test_id.json";

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

// Crear clase extendida de TCPDF para dibujar anotaciones
class PDF_WITH_ANNOTATIONS extends TCPDF {
    public function drawAnnotations($annotations, $img_width, $img_height) {
        if (empty($annotations)) {
            return;
        }

        foreach ($annotations as $stroke) {
            if (isset($stroke['type']) && $stroke['type'] === 'text') {
                // Dibujar texto
                $this->SetTextColor(hexdec(substr($stroke['color'], 1, 2)),
                                   hexdec(substr($stroke['color'], 3, 2)),
                                   hexdec(substr($stroke['color'], 5, 2)));
                $this->SetFont('helvetica', '', $stroke['size'] * 3);
                $this->Text($stroke['position']['x'] * 0.264583,
                           $stroke['position']['y'] * 0.264583,
                           $stroke['text']);
            } else {
                // Dibujar línea/trazo
                if (empty($stroke['points']) || count($stroke['points']) < 2) {
                    continue;
                }

                // Convertir color hex a RGB
                $color = $stroke['color'];
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));

                // Configurar estilo de línea
                $this->SetDrawColor($r, $g, $b);
                $this->SetLineWidth($stroke['size'] * 0.264583 / 10);

                if (isset($stroke['tool']) && $stroke['tool'] === 'marker') {
                    $this->SetAlpha(0.5);
                }

                // Dibujar el trazo conectando todos los puntos
                for ($i = 0; $i < count($stroke['points']) - 1; $i++) {
                    $x1 = $stroke['points'][$i]['x'] * 0.264583;
                    $y1 = $stroke['points'][$i]['y'] * 0.264583;
                    $x2 = $stroke['points'][$i + 1]['x'] * 0.264583;
                    $y2 = $stroke['points'][$i + 1]['y'] * 0.264583;

                    $this->Line($x1, $y1, $x2, $y2);
                }

                if (isset($stroke['tool']) && $stroke['tool'] === 'marker') {
                    $this->SetAlpha(1);
                }
            }
        }
    }
}

// Crear el documento PDF
$pdf = new PDF_WITH_ANNOTATIONS('L', 'mm', 'A4', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('SimpleMenti');
$pdf->SetAuthor('SimpleMenti');
$pdf->SetTitle('Presentación con Anotaciones - ' . $test_data['titulo']);
$pdf->SetSubject('Diapositivas anotadas y resultados de ' . $participante['nombre']);

// Configuración
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false, 0);

// Agregar diapositivas con anotaciones
if (!empty($test_data['pdf_images'])) {
    foreach ($test_data['pdf_images'] as $index => $slide) {
        $slide_number = $index + 1;

        // Agregar nueva página
        $pdf->AddPage();

        // Obtener ruta de la imagen
        $image_path = '../' . $slide['path'];

        if (file_exists($image_path)) {
            // Calcular dimensiones para ajustar a la página
            $page_width = $pdf->getPageWidth() - 20;
            $page_height = $pdf->getPageHeight() - 20;

            // Insertar imagen de slide
            $pdf->Image($image_path, 10, 10, $page_width, $page_height, '', '', '', false, 300, '', false, false, 0);

            // Buscar anotaciones para esta diapositiva
            $anotaciones_slide = null;
            if (isset($participante['anotaciones'])) {
                foreach ($participante['anotaciones'] as $anotacion) {
                    if ($anotacion['slide_number'] === $slide_number) {
                        $anotaciones_slide = $anotacion['datos'];
                        break;
                    }
                }
            }

            // Dibujar anotaciones si existen
            if ($anotaciones_slide) {
                // Obtener dimensiones originales de la imagen
                list($img_width, $img_height) = getimagesize($image_path);
                $pdf->drawAnnotations($anotaciones_slide, $img_width, $img_height);
            }

            // Agregar número de slide
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Text($page_width - 10, $page_height + 5, 'Slide ' . $slide_number . ' / ' . count($test_data['pdf_images']));
        }
    }
}

// Agregar sección de resultados de evaluación
$pdf->AddPage();

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

// Generar HTML para resultados
$html = '<h1 style="color: #4e73df;">Resultados de Evaluación</h1>';
$html .= '<h2>' . htmlspecialchars($test_data['titulo']) . '</h2>';
$html .= '<h3>Participante: ' . htmlspecialchars($participante['nombre']) . '</h3>';
$html .= '<hr>';

$html .= '<table border="0" cellpadding="5" style="width: 100%;">';
$html .= '<tr>';
$html .= '<td style="background-color: #4e73df; color: white; padding: 10px;"><strong>Estadística</strong></td>';
$html .= '<td style="background-color: #4e73df; color: white; padding: 10px;"><strong>Valor</strong></td>';
$html .= '</tr>';
$html .= '<tr><td>Puntaje total</td><td>' . $puntaje . ' puntos</td></tr>';
$html .= '<tr><td>Porcentaje de acierto</td><td>' . $porcentaje_acierto . '%</td></tr>';
$html .= '<tr><td>Respuestas correctas</td><td style="color: green;"><strong>' . $total_correctas . '</strong></td></tr>';
$html .= '<tr><td>Respuestas incorrectas</td><td style="color: red;"><strong>' . $total_incorrectas . '</strong></td></tr>';
$html .= '<tr><td>Tiempo promedio por pregunta</td><td>' . $tiempo_promedio . ' segundos</td></tr>';
$html .= '</table>';

$html .= '<br><h2>Detalle de Respuestas</h2>';

foreach ($preguntas_con_respuestas as $index => $item) {
    $html .= '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">';
    $html .= '<h4 style="color: #4e73df;">Pregunta ' . ($index + 1) . '</h4>';
    $html .= '<p><strong>' . htmlspecialchars($item['pregunta']['pregunta']) . '</strong></p>';

    if ($item['respondida']) {
        $html .= '<p><strong>Tu respuesta:</strong> ' . htmlspecialchars($item['respuesta_dada']) . '</p>';

        if (isset($item['pregunta']['respuesta_correcta'])) {
            if ($item['es_correcta']) {
                $html .= '<p style="color: green;"><strong>✓ Correcta</strong></p>';
            } else {
                $html .= '<p style="color: red;"><strong>✗ Incorrecta</strong></p>';
                $html .= '<p><strong>Respuesta correcta:</strong> ' . htmlspecialchars($item['pregunta']['respuesta_correcta']) . '</p>';
            }
        }

        if (isset($item['pregunta']['explicacion'])) {
            $html .= '<p style="background-color: #f0f0f0; padding: 8px; border-left: 3px solid #4e73df;">';
            $html .= '<strong>Explicación:</strong> ' . htmlspecialchars($item['pregunta']['explicacion']);
            $html .= '</p>';
        }

        if (isset($item['pregunta']['feedback']) && !empty($item['pregunta']['feedback'])) {
            $html .= '<p style="background-color: #fff3cd; padding: 8px; border-left: 3px solid #ffc107;">';
            $html .= '<strong>Retroalimentación:</strong> ' . htmlspecialchars($item['pregunta']['feedback']);
            $html .= '</p>';
        }

        if ($item['tiempo_respuesta']) {
            $html .= '<p><small><strong>Tiempo de respuesta:</strong> ' . $item['tiempo_respuesta'] . ' segundos</small></p>';
        }
    } else {
        $html .= '<p style="color: gray;"><em>No respondida</em></p>';
        if (isset($item['pregunta']['respuesta_correcta'])) {
            $html .= '<p><strong>Respuesta correcta:</strong> ' . htmlspecialchars($item['pregunta']['respuesta_correcta']) . '</p>';
        }
    }

    $html .= '</div>';
}

$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 10, 'Generado con SimpleMenti - ' . date('d/m/Y H:i'), 0, false, 'C', 0, '', 0, false, 'T', 'M');

// Generar nombre de archivo
$filename = 'presentacion_anotada_' . $participante['nombre'] . '_' . date('Ymd_His') . '.pdf';
$filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);

// Salida del PDF
$pdf->Output($filename, 'D');
?>
