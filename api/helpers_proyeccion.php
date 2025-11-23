<?php
/**
 * Funciones auxiliares para el sistema de control móvil
 */

/**
 * Genera un código de emparejamiento único (formato: XXXX-XXXX)
 * @return string
 */
function generarCodigoEmparejamiento() {
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sin caracteres ambiguos (0, O, 1, I)
    $codigo = '';

    for ($i = 0; $i < 8; $i++) {
        if ($i == 4) {
            $codigo .= '-';
        }
        $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $codigo;
}

/**
 * Genera un código QR en formato base64
 * @param string $data Datos a codificar en el QR
 * @return string QR en formato data:image/png;base64,...
 */
function generarQRBase64($data) {
    // Usamos la librería phpqrcode si está disponible
    // Si no, retornamos un placeholder

    $qrDir = __DIR__ . '/../vendor/phpqrcode';

    if (file_exists($qrDir . '/qrlib.php')) {
        require_once $qrDir . '/qrlib.php';

        // Generar QR en memoria
        ob_start();
        QRcode::png($data, false, QR_ECLEVEL_L, 5, 2);
        $imageData = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    // Fallback: usar API externa (goqr.me)
    $encodedData = urlencode($data);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedData}";

    $imageData = @file_get_contents($qrUrl);

    if ($imageData) {
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    // Si todo falla, retornar placeholder SVG
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#fff"/><text x="150" y="150" text-anchor="middle" font-size="14">QR Code</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Obtiene la URL base del servidor
 * @return string
 */
function getServerUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

/**
 * Obtiene el ID de presentación asociado a un código de sesión
 * @param string $sessionId Código de sesión
 * @return string|null ID de presentación o null si no existe
 */
function obtenerPresentacionId($sessionId) {
    // Buscar en archivos de sesiones
    $respuestasDir = __DIR__ . '/../data/respuestas';

    if (!is_dir($respuestasDir)) {
        return null;
    }

    // Iterar por cada presentación
    $presentaciones = scandir($respuestasDir);
    foreach ($presentaciones as $presentacion) {
        if ($presentacion === '.' || $presentacion === '..') {
            continue;
        }

        $sessionFile = $respuestasDir . '/' . $presentacion . '/sesion_' . $sessionId . '.json';

        if (file_exists($sessionFile)) {
            $sessionData = json_decode(file_get_contents($sessionFile), true);
            return $sessionData['id_presentacion'] ?? $presentacion;
        }
    }

    return null;
}

/**
 * Valida si un código de emparejamiento es válido y no ha expirado
 * @param string $pairCode Código de emparejamiento
 * @return array|false Array con datos del código o false si inválido/expirado
 */
function validarCodigoEmparejamiento($pairCode) {
    $linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';

    if (!file_exists($linkFile)) {
        return false;
    }

    $linkData = json_decode(file_get_contents($linkFile), true);

    // Verificar expiración
    $expiresAt = strtotime($linkData['expires_at']);
    if (time() > $expiresAt) {
        // Código expirado, eliminarlo
        @unlink($linkFile);
        return false;
    }

    return $linkData;
}

/**
 * Retorna una respuesta JSON de error
 * @param string $errorCode Código de error
 * @param string $message Mensaje descriptivo
 * @return void
 */
function returnError($errorCode, $message) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $errorCode,
        'message' => $message
    ]);
    exit;
}

/**
 * Retorna una respuesta JSON de éxito
 * @param array $data Datos a retornar
 * @return void
 */
function returnSuccess($data) {
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/**
 * Limpia códigos de emparejamiento expirados
 * Se ejecuta periódicamente para mantener limpio el directorio
 * @return int Número de archivos eliminados
 */
function limpiarCodigosExpirados() {
    $linksDir = __DIR__ . '/../data/projection_links';
    $eliminados = 0;

    if (!is_dir($linksDir)) {
        return 0;
    }

    $archivos = scandir($linksDir);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..' || !str_ends_with($archivo, '.json')) {
            continue;
        }

        $filePath = $linksDir . '/' . $archivo;
        $linkData = json_decode(file_get_contents($filePath), true);

        if (isset($linkData['expires_at'])) {
            $expiresAt = strtotime($linkData['expires_at']);
            if (time() > $expiresAt) {
                @unlink($filePath);
                $eliminados++;
            }
        }
    }

    return $eliminados;
}

/**
 * Actualiza el estado de una vinculación
 * @param string $pairCode Código de emparejamiento
 * @param string $newStatus Nuevo estado (waiting, paired, active)
 * @param array $additionalData Datos adicionales a actualizar
 * @return bool
 */
function actualizarEstadoVinculacion($pairCode, $newStatus, $additionalData = []) {
    $linkFile = __DIR__ . '/../data/projection_links/' . $pairCode . '.json';

    if (!file_exists($linkFile)) {
        return false;
    }

    $linkData = json_decode(file_get_contents($linkFile), true);
    $linkData['status'] = $newStatus;

    // Merge additional data
    foreach ($additionalData as $key => $value) {
        if (is_array($value) && isset($linkData[$key])) {
            $linkData[$key] = array_merge($linkData[$key], $value);
        } else {
            $linkData[$key] = $value;
        }
    }

    file_put_contents($linkFile, json_encode($linkData, JSON_PRETTY_PRINT));
    return true;
}
