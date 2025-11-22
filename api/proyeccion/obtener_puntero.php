<?php
/**
 * API: Obtener Posición del Puntero (para Presentador)
 *
 * Retorna la posición actual del puntero virtual si hay algún
 * dispositivo móvil vinculado con el puntero activo.
 *
 * Método: GET
 * Parámetros: session (código de sesión)
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

// Obtener parámetros
if (!isset($_GET['session'])) {
    returnError('missing_params', 'Falta el código de sesión');
}

$sessionId = trim($_GET['session']);

// Buscar archivos de vinculación para esta sesión
$linksDir = __DIR__ . '/../../data/projection_links';
$pointerData = null;

if (is_dir($linksDir)) {
    $files = scandir($linksDir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || !str_ends_with($file, '.json')) {
            continue;
        }

        $linkFile = $linksDir . '/' . $file;
        $linkData = json_decode(file_get_contents($linkFile), true);

        // Verificar que sea para esta sesión y esté vinculado
        if (isset($linkData['session']['session_id']) &&
            $linkData['session']['session_id'] === $sessionId &&
            $linkData['status'] === 'paired') {

            // Obtener datos del puntero si existe
            if (isset($linkData['state']['pointer'])) {
                $pointer = $linkData['state']['pointer'];

                // Verificar que no sea muy antiguo (>5 segundos = desconectado)
                $timestamp = $pointer['timestamp'] ?? 0;
                $age = microtime(true) - $timestamp;

                if ($age < 5) {
                    $pointerData = $pointer;
                    break;
                }
            }
        }
    }
}

// Retornar datos del puntero
if ($pointerData) {
    returnSuccess([
        'has_pointer' => true,
        'pointer' => $pointerData
    ]);
} else {
    returnSuccess([
        'has_pointer' => false,
        'pointer' => null
    ]);
}
