<?php
/**
 * Control Móvil - Interfaz de control de presentación desde dispositivo móvil
 *
 * Flujo:
 * 1. Escanear QR → Login (si es necesario)
 * 2. Pre-control → Seleccionar presentación
 * 3. Iniciar → Control completo activo
 */

session_start();

require_once 'api/helpers_proyeccion.php';

// Detectar código de emparejamiento
$pair_code = isset($_GET['code']) ? trim($_GET['code']) : null;

// Si no hay código, error
if (!$pair_code) {
    include('includes/control-movil/codigo_invalido.php');
    exit;
}

// Validar código de emparejamiento
$linkData = validarCodigoEmparejamiento($pair_code);

if (!$linkData) {
    // Código inválido o expirado
    include('includes/control-movil/codigo_invalido.php');
    exit;
}

// Verificar autenticación
$is_authenticated = isset($_SESSION['auth_test']) || isset($_SESSION['user_id']);

// Si NO está autenticado, mostrar login
if (!$is_authenticated) {
    include('includes/control-movil/login.php');
    exit;
}

// Usuario autenticado - Manejar estados
$status = $linkData['status'];

switch ($status) {
    case 'waiting':
        // Móvil acaba de escanear, actualizar a 'paired'
        actualizarEstadoVinculacion($pair_code, 'paired', [
            'mobile_device' => [
                'user_id' => $_SESSION['user_id'] ?? 'guest',
                'paired_at' => date('c')
            ]
        ]);

        // Mostrar interfaz pre-control (seleccionar presentación)
        include('includes/control-movil/pre_control.php');
        break;

    case 'paired':
        // Móvil conectado pero aún no ha iniciado presentación
        // Mostrar interfaz pre-control
        include('includes/control-movil/pre_control.php');
        break;

    case 'active':
        // Sesión activa - mostrar control completo
        $session_id = $linkData['session_id'];
        $presentation_id = $linkData['presentation_id'];

        include('includes/control-movil/interfaz_control.php');
        break;

    default:
        // Estado desconocido
        include('includes/control-movil/codigo_invalido.php');
        break;
}
