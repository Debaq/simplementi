<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si hay un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$respuesta_enviada = isset($_GET['respuesta_enviada']) ? $_GET['respuesta_enviada'] : 0;

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

// Verificar si el usuario ya tiene un ID de participante
$participante_id = isset($_COOKIE['participante_id']) ? $_COOKIE['participante_id'] : '';
if (empty($participante_id)) {
    $participante_id = 'p' . mt_rand(100000, 999999);
    setcookie('participante_id', $participante_id, time() + 86400 * 30, '/'); // 30 días
}

// Incluir el archivo de verificación
include('includes/participante/verificacion.php');

// Verificar si la sesión está finalizada y redirigir al resumen
if ($session_data['estado'] === 'finalizada') {
    // Verificar si el participante tiene respuestas registradas
    $participante_encontrado = false;
    foreach ($session_data['participantes'] as $participante) {
        if ($participante['id'] === $participante_id && !empty($participante['respuestas'])) {
            $participante_encontrado = true;
            break;
        }
    }
    
    // Si ha participado, redirigir al resumen
    if ($participante_encontrado) {
        header("Location: participante_resumen.php?codigo=$codigo_sesion&participante=$participante_id");
        exit;
    } else {
        // Si no ha participado, mostrar mensaje
        include('includes/participante/head.php');
        ?>
        <div class="container">
            <div class="completion-container">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h3 class="text-center mb-0">Sesión finalizada</h3>
                    </div>
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-info-circle text-info mb-4" style="font-size: 5rem;"></i>
                        <h4 class="mb-3">La sesión ha finalizado</h4>
                        <p class="lead">No se registraron tus respuestas en esta sesión.</p>
                        <a href="index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-home me-2"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
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

// Obtener la pregunta actual
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

// Mostrar pregunta o mensaje de respuesta enviada
include('includes/participante/pantalla_pregunta.php');

// Incluir scripts
include('includes/participante/scripts.php');
?>
</body>
</html>