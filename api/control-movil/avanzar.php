<?php
/**
 * API: Avanzar Slide (Control Móvil)
 *
 * Avanza al siguiente slide/pregunta en la presentación.
 * Retorna JSON con el estado actualizado.
 *
 * Método: POST
 * Requiere: Sesión autenticada + pair_code válido
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

session_start();

// Validar autenticación
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('not_authenticated', 'Debes iniciar sesión');
}

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['pair_code'])) {
    returnError('missing_params', 'Falta el código de emparejamiento');
}

$pairCode = $data['pair_code'];

// Validar código de emparejamiento
$linkData = validarCodigoEmparejamiento($pairCode);
if (!$linkData) {
    returnError('invalid_code', 'Código de emparejamiento inválido o expirado');
}

// Verificar que esté vinculado
if ($linkData['status'] !== 'paired') {
    returnError('not_paired', 'El dispositivo no está vinculado');
}

$sessionId = $linkData['session']['session_id'];
$presentationId = $linkData['session']['presentation_id'];

// Buscar archivo de sesión
$sessionFile = __DIR__ . '/../../data/respuestas/' . $presentationId . '/sesion_' . $sessionId . '.json';

if (!file_exists($sessionFile)) {
    returnError('session_not_found', 'No se encontró la sesión');
}

// Leer datos de sesión
$sessionData = json_decode(file_get_contents($sessionFile), true);

// Leer datos de presentación para validar límites
$presentationFile = __DIR__ . '/../../data/presentaciones/' . $presentationId . '.json';
if (!file_exists($presentationFile)) {
    returnError('presentation_not_found', 'No se encontró la presentación');
}

$presentationData = json_decode(file_get_contents($presentationFile), true);

// Determinar si usa secuencia PDF o preguntas tradicionales
$usesPdfSequence = !empty($presentationData['pdf_enabled']) &&
                   isset($presentationData['pdf_sequence']) &&
                   !empty($presentationData['pdf_sequence']);

if ($usesPdfSequence) {
    // Modo PDF con secuencia
    $currentIndex = $sessionData['pdf_sequence_index'] ?? 0;
    $totalItems = count($presentationData['pdf_sequence']);

    // Avanzar
    $newIndex = min($currentIndex + 1, $totalItems - 1);

    // Actualizar
    $sessionData['pdf_sequence_index'] = $newIndex;

    // Obtener info del item actual
    $currentItem = $presentationData['pdf_sequence'][$newIndex];

    $itemInfo = [
        'type' => $currentItem['type'],
        'index' => $newIndex,
        'total' => $totalItems
    ];

    if ($currentItem['type'] === 'slide') {
        $itemInfo['slide_number'] = $currentItem['number'];
    } elseif ($currentItem['type'] === 'question') {
        $itemInfo['question_id'] = $currentItem['id'];
        // Buscar la pregunta en el array de preguntas
        foreach ($presentationData['preguntas'] as $q) {
            if ($q['id'] == $currentItem['id']) {
                $itemInfo['question_text'] = $q['pregunta'];
                break;
            }
        }
    }
} else {
    // Modo tradicional de preguntas
    $currentQuestion = $sessionData['pregunta_actual'] ?? 0;
    $totalQuestions = count($presentationData['preguntas']);

    // Avanzar (0 es intro, luego 1..n preguntas, n+1 es fin)
    $newQuestion = min($currentQuestion + 1, $totalQuestions + 1);

    // Actualizar
    $sessionData['pregunta_actual'] = $newQuestion;

    $itemInfo = [
        'type' => 'question',
        'index' => $newQuestion,
        'total' => $totalQuestions
    ];

    if ($newQuestion > 0 && $newQuestion <= $totalQuestions) {
        $question = $presentationData['preguntas'][$newQuestion - 1];
        $itemInfo['question_text'] = $question['pregunta'];
        $itemInfo['question_type'] = $question['tipo'];
    }
}

// Guardar cambios
file_put_contents($sessionFile, json_encode($sessionData, JSON_PRETTY_PRINT));

// Obtener conteo de participantes
$participantsCount = isset($sessionData['participantes']) ? count($sessionData['participantes']) : 0;

// Retornar éxito con info actualizada
returnSuccess([
    'message' => 'Avanzado correctamente',
    'current_item' => $itemInfo,
    'participants_count' => $participantsCount,
    'session_id' => $sessionId
]);
