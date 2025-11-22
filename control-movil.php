<?php
/**
 * Control Móvil - Interfaz de control de presentación desde dispositivo móvil
 *
 * Permite al docente controlar la presentación desde su smartphone/tablet
 * Requiere autenticación y vinculación con un código de emparejamiento
 */

session_start();

// Detectar si viene de escaneo de QR
$qr_data = isset($_GET['qr']) ? $_GET['qr'] : null;
$pair_code = isset($_GET['code']) ? $_GET['code'] : null;

// Si viene QR data, decodificar
if ($qr_data) {
    $decoded = json_decode(base64_decode($qr_data), true);
    if ($decoded && isset($decoded['code'])) {
        $pair_code = $decoded['code'];
    }
}

// Verificar si está autenticado
$is_authenticated = isset($_SESSION['auth_test']) || isset($_SESSION['user_id']);

// Si NO está autenticado, mostrar pantalla de login
if (!$is_authenticated) {
    include('includes/control-movil/login.php');
    exit;
}

// Si está autenticado pero no tiene código de emparejamiento, pedir escaneo
if (!$pair_code) {
    include('includes/control-movil/escanear_qr.php');
    exit;
}

// Si tiene código de emparejamiento, intentar vincular
require_once 'api/helpers_proyeccion.php';
$linkData = validarCodigoEmparejamiento($pair_code);

if (!$linkData) {
    // Código inválido o expirado
    include('includes/control-movil/codigo_invalido.php');
    exit;
}

// Verificar si ya está vinculado
if ($linkData['status'] === 'paired') {
    // Ya vinculado, cargar interfaz de control
    $session_id = $linkData['session']['session_id'];
    $presentation_id = $linkData['session']['presentation_id'];

    include('includes/control-movil/interfaz_control.php');
    exit;
}

// Si el código es válido pero no está vinculado aún, vincular ahora
// Esto se hace automáticamente via la API en el frontend
include('includes/control-movil/vinculando.php');
?>
