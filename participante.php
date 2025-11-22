<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Manejo del nombre del participante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_participante'])) {
    $nombre_participante = trim($_POST['nombre_participante']);
    if (!empty($nombre_participante)) {
        // Sanitizar el nombre para seguridad
        $nombre_participante = htmlspecialchars($nombre_participante, ENT_QUOTES, 'UTF-8');
        // Establecer la cookie por 30 días
        setcookie('participante_nombre', $nombre_participante, time() + 86400 * 30, '/');
        // Redirigir para limpiar el POST
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Verificar si se necesita el nombre del participante
$nombre_participante = isset($_COOKIE['participante_nombre']) ? $_COOKIE['participante_nombre'] : null;

// Verificar si hay un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

// Si no tenemos el nombre, mostrar el formulario para ingresarlo
if (!$nombre_participante) {
    include('includes/participante/head.php');
    ?>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card text-center" style="width: 25rem;">
            <div class="card-body">
                <h1 class="card-title">¡Bienvenido!</h1>
                <p>Por favor, ingresa tu nombre para unirte a la sesión.</p>
                <form method="POST" action="">
                    <div class="mb-3">
                        <input type="text" name="nombre_participante" class="form-control" placeholder="Tu nombre" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Unirse</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    include('includes/participante/scripts.php');
    echo '</body></html>';
    exit; // Detener la ejecución para no mostrar el resto de la página
}


$respuesta_enviada = isset($_GET['respuesta_enviada']) ? $_GET['respuesta_enviada'] : 0;

// Incluir el archivo de verificación
include('includes/participante/verificacion.php');

// Verificar si está habilitado el modo asíncrono
$modo_asincrono = isset($test_data['configuracion']['modo_asincrono']) &&
                  $test_data['configuracion']['modo_asincrono'] === true &&
                  !empty($test_data['pdf_enabled']) &&
                  !empty($test_data['configuracion']['habilitar_audio']);

// Si está en modo asíncrono, usar el flujo asíncrono
if ($modo_asincrono) {
    include('includes/participante/modo_asincrono.php');
    exit;
}

// Verificar si estamos en la pantalla de espera inicial
if ($pregunta_actual_index === 0) {
    // Mostrar pantalla de espera
    include('includes/participante/pantalla_espera.php');
    exit;
}

// Verificar si se han completado todas las preguntas
if ($pregunta_actual_index > count($test_data['preguntas'])) {
    // Mostrar pantalla de finalización
    include('includes/participante/pantalla_completado.php');
    exit;
}

// Identificar al participante
$participante_id = isset($_COOKIE['participante_id']) ? $_COOKIE['participante_id'] : '';
if (empty($participante_id)) {
    $participante_id = 'p' . mt_rand(100000, 999999);
    setcookie('participante_id', $participante_id, time() + 86400 * 30, '/'); // 30 días
}

// Verificar si hay PDF con secuencia habilitado
$tiene_secuencia_pdf = !empty($test_data['pdf_enabled']) && isset($test_data['pdf_sequence']);

if ($tiene_secuencia_pdf) {
    // Modo secuencia: seguir la secuencia del presentador
    $sequence = $test_data['pdf_sequence'];
    $sequence_index = isset($session_data['pdf_sequence_index']) ? intval($session_data['pdf_sequence_index']) : 0;

    // Validar índice
    if ($sequence_index < 0 || $sequence_index >= count($sequence)) {
        $sequence_index = 0;
    }

    $current_item = $sequence[$sequence_index];

    // Incluir el header
    include('includes/participante/head.php');

    if ($current_item['type'] === 'slide') {
        // Mostrar solo el slide
        $slide_number = $current_item['number'];
        $slide_data = $test_data['pdf_images'][$slide_number - 1];

        // Verificar si se permiten anotaciones
        $permitir_anotaciones = isset($test_data['configuracion']['permitir_anotaciones']) &&
                                $test_data['configuracion']['permitir_anotaciones'];

        if ($permitir_anotaciones) {
            include('includes/participante/pantalla_pdf_anotaciones.php');
        } else {
            include('includes/participante/pantalla_pdf_fullscreen.php');
        }
    } elseif ($current_item['type'] === 'question') {
        // Mostrar pregunta
        $question_id = $current_item['id'];
        $pregunta_actual = null;

        // Buscar la pregunta por ID
        foreach ($test_data['preguntas'] as $pregunta) {
            if ($pregunta['id'] === $question_id) {
                $pregunta_actual = $pregunta;
                break;
            }
        }

        if (!$pregunta_actual) {
            echo "<div class='alert alert-danger'>Error: Pregunta no encontrada</div>";
            exit;
        }

        // Obtener índice de pregunta para mostrar "Pregunta X de Y"
        $pregunta_actual_index = array_search($pregunta_actual, $test_data['preguntas']) + 1;

        // Comprobar si ya respondió a esta pregunta
        $ya_respondio = false;
        foreach ($session_data['participantes'] as $participante) {
            if ($participante['id'] == $participante_id) {
                foreach ($participante['respuestas'] as $respuesta) {
                    if ($respuesta['id_pregunta'] == $pregunta_actual['id']) {
                        $ya_respondio = true;
                        break 2;
                    }
                }
            }
        }

        // Determinar si es una pregunta con tiempo límite
        $tiempo_limite = isset($test_data['configuracion']['tiempo_por_pregunta']) ?
                         intval($test_data['configuracion']['tiempo_por_pregunta']) : 0;

        include('includes/participante/pantalla_pregunta.php');
    }
} else {
    // Modo antiguo: mostrar pregunta basada en pregunta_actual_index
    $pregunta_actual = $test_data['preguntas'][$pregunta_actual_index - 1];

    // Comprobar si ya respondió a esta pregunta
    $ya_respondio = false;
    foreach ($session_data['participantes'] as $participante) {
        if ($participante['id'] == $participante_id) {
            foreach ($participante['respuestas'] as $respuesta) {
                if ($respuesta['id_pregunta'] == $pregunta_actual['id']) {
                    $ya_respondio = true;
                    break 2;
                }
            }
        }
    }

    // Determinar si es una pregunta con tiempo límite
    $tiempo_limite = isset($test_data['configuracion']['tiempo_por_pregunta']) ?
                     intval($test_data['configuracion']['tiempo_por_pregunta']) : 0;

    // Incluir el header
    include('includes/participante/head.php');

    // Si hay PDF habilitado (sin secuencia), mostrar la pantalla del PDF
    if (!empty($test_data['pdf_enabled'])) {
        include('includes/participante/pantalla_pdf.php');
    }

    // Mostrar pregunta o mensaje de respuesta enviada
    include('includes/participante/pantalla_pregunta.php');
}

// Incluir scripts
include('includes/participante/scripts.php');
?>
</body>
</html>