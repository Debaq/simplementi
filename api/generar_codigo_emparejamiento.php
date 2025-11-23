<?php
/**
 * API: Generar Código de Emparejamiento
 *
 * Genera un código único y QR para vincular un dispositivo móvil
 * con el PC para iniciar presentación sincronizada.
 *
 * Llamado por: index.php cuando se solicita generar QR
 * Método: GET
 * Parámetros: ninguno (la sesión se crea después desde el móvil)
 */

require_once __DIR__ . '/helpers_proyeccion.php';

// Limpiar códigos expirados periódicamente (10% de probabilidad)
if (random_int(1, 10) === 1) {
    limpiarCodigosExpirados();
}

// Generar código de emparejamiento
$pairCode = generarCodigoEmparejamiento();

// Obtener ruta base del servidor
$serverUrl = getServerUrl();
$basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Obtener path base
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Crear URL completa para el QR (apunta a control-movil.php con el código)
$qrUrl = $serverUrl . $basePath . '/control-movil.php?code=' . urlencode($pairCode);

// Crear archivo de vinculación (sin sesión todavía)
$linkData = [
    'pair_code' => $pairCode,
    'created_at' => date('c'),
    'expires_at' => date('c', time() + 120), // Expira en 2 minutos (tiempo para login)
    'status' => 'waiting',  // waiting → paired → active
    'qr_url' => $qrUrl,

    // Se llenan después
    'presentation_id' => null,
    'session_id' => null,

    'mobile_device' => [
        'user_id' => null,
        'paired_at' => null
    ],

    'pc_device' => [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]
];

// Guardar archivo
$linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';
file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));

// Generar imagen QR con la URL completa (no JSON)
$qrImage = generarQRBase64($qrUrl);

// Retornar respuesta
returnSuccess([
    'pair_code' => $pairCode,
    'qr_url' => $qrUrl,
    'qr_image' => $qrImage,
    'expires_in' => 120,
    'status' => 'waiting'
]);
