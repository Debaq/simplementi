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

// Obtener la pregunta actual
$pregunta_actual = $test_data['preguntas'][$pregunta_actual_index - 1];

// Verificar si el participante ya respondió esta pregunta
$participante_id = isset($_COOKIE['participante_id']) ? $_COOKIE['participante_id'] : '';
if (empty($participante_id)) {
    $participante_id = 'p' . mt_rand(100000, 999999);
    setcookie('participante_id', $participante_id, time() + 86400 * 30, '/'); // 30 días
}

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

// Si hay PDF habilitado, mostrar la pantalla del PDF
if (!empty($test_data['pdf_enabled'])) {
    include('includes/participante/pantalla_pdf.php');
}

// Mostrar pregunta o mensaje de respuesta enviada
include('includes/participante/pantalla_pregunta.php');

// Incluir scripts
include('includes/participante/scripts.php');
?>
</body>
</html>