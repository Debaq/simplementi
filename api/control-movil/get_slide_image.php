<?php
/**
 * API: Obtener imagen del slide actual
 *
 * Retorna la ruta de la imagen del slide actual según el pdf_sequence_index
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

header('Content-Type: application/json');

// Obtener parámetros
$pairCode = $_GET['pair_code'] ?? null;

if (!$pairCode) {
    echo json_encode([
        'success' => false,
        'error' => 'missing_params',
        'message' => 'Falta el código de emparejamiento'
    ]);
    exit;
}

// Validar código de emparejamiento
$linkData = validarCodigoEmparejamiento($pairCode);
if (!$linkData) {
    echo json_encode([
        'success' => false,
        'error' => 'invalid_code',
        'message' => 'Código de emparejamiento inválido o expirado'
    ]);
    exit;
}

// Verificar que esté vinculado o activo
if ($linkData['status'] !== 'paired' && $linkData['status'] !== 'active') {
    echo json_encode([
        'success' => false,
        'error' => 'not_paired',
        'message' => 'El dispositivo no está vinculado'
    ]);
    exit;
}

$sessionId = $linkData['session_id'] ?? null;
$presentationId = $linkData['presentation_id'] ?? null;

if (!$sessionId || !$presentationId) {
    echo json_encode([
        'success' => false,
        'error' => 'invalid_link_data',
        'message' => 'Datos de vinculación incompletos'
    ]);
    exit;
}

// Buscar archivo de sesión
$sessionFile = __DIR__ . '/../../data/respuestas/' . $presentationId . '/sesion_' . $sessionId . '.json';

if (!file_exists($sessionFile)) {
    echo json_encode([
        'success' => false,
        'error' => 'session_not_found',
        'message' => 'No se encontró la sesión'
    ]);
    exit;
}

// Leer datos de sesión
$sessionData = json_decode(file_get_contents($sessionFile), true);

// Leer datos de presentación
$presentationFile = __DIR__ . '/../../data/presentaciones/' . $presentationId . '.json';
if (!file_exists($presentationFile)) {
    echo json_encode([
        'success' => false,
        'error' => 'presentation_not_found',
        'message' => 'No se encontró la presentación'
    ]);
    exit;
}

$presentationData = json_decode(file_get_contents($presentationFile), true);

// Verificar si tiene PDF habilitado
if (empty($presentationData['pdf_enabled']) || empty($presentationData['pdf_images'])) {
    echo json_encode([
        'success' => false,
        'error' => 'no_pdf',
        'message' => 'La presentación no tiene PDF habilitado'
    ]);
    exit;
}

// Obtener índice actual
$currentIndex = $sessionData['pdf_sequence_index'] ?? 0;

// Si tiene secuencia, obtener el número de slide desde la secuencia
if (!empty($presentationData['pdf_sequence'])) {
    if (isset($presentationData['pdf_sequence'][$currentIndex])) {
        $currentItem = $presentationData['pdf_sequence'][$currentIndex];

        if ($currentItem['type'] === 'slide') {
            $slideNumber = $currentItem['number'];

            // Obtener imagen del slide
            if (isset($presentationData['pdf_images'][$slideNumber - 1])) {
                echo json_encode([
                    'success' => true,
                    'slide_image' => $presentationData['pdf_images'][$slideNumber - 1],
                    'slide_number' => $slideNumber,
                    'total_slides' => count($presentationData['pdf_images']),
                    'current_index' => $currentIndex,
                    'type' => 'slide'
                ]);
                exit;
            }
        } elseif ($currentItem['type'] === 'question') {
            // Es una pregunta, no un slide
            echo json_encode([
                'success' => true,
                'type' => 'question',
                'question_id' => $currentItem['id'],
                'current_index' => $currentIndex
            ]);
            exit;
        }
    }
} else {
    // No hay secuencia, usar el slide número del índice
    if (isset($presentationData['pdf_images'][$currentIndex])) {
        echo json_encode([
            'success' => true,
            'slide_image' => $presentationData['pdf_images'][$currentIndex],
            'slide_number' => $currentIndex + 1,
            'total_slides' => count($presentationData['pdf_images']),
            'type' => 'slide'
        ]);
        exit;
    }
}

// Si llegamos aquí, no se encontró el slide
echo json_encode([
    'success' => false,
    'error' => 'slide_not_found',
    'message' => 'No se encontró el slide actual'
]);
