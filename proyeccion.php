<?php
/**
 * Modo Proyección Fullscreen
 *
 * Vista dedicada para proyectar en el aula sin controles.
 * Se sincroniza automáticamente con el control móvil del docente.
 */

session_start();

// Obtener pair_code de la URL
$pair_code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Si no hay código, mostrar pantalla de ingreso
if (empty($pair_code)) {
    include('includes/proyeccion/pantalla_ingreso.php');
    exit;
}

// Validar código
require_once 'api/helpers_proyeccion.php';
$linkData = validarCodigoEmparejamiento($pair_code);

if (!$linkData) {
    include('includes/proyeccion/codigo_invalido.php');
    exit;
}

// Obtener datos de la sesión
$session_id = $linkData['session']['session_id'] ?? '';
$presentation_id = $linkData['session']['presentation_id'] ?? '';

if (empty($session_id) || empty($presentation_id)) {
    die('Error: Datos de sesión incompletos');
}

// Cargar datos de la presentación
$presentationFile = 'data/presentaciones/' . $presentation_id . '.json';
if (!file_exists($presentationFile)) {
    die('Error: Presentación no encontrada');
}

$presentationData = json_decode(file_get_contents($presentationFile), true);

// Cargar datos de sesión
$sessionFile = 'data/respuestas/' . $presentation_id . '/sesion_' . $session_id . '.json';
if (!file_exists($sessionFile)) {
    die('Error: Sesión no encontrada');
}

$sessionData = json_decode(file_get_contents($sessionFile), true);

// Incluir vista de proyección
include('includes/proyeccion/vista_fullscreen.php');
?>
