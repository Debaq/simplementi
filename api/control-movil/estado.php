<?php
/**
 * API: Obtener Estado (Control Móvil)
 *
 * Retorna el estado completo de la sesión incluyendo:
 * - Slide/pregunta actual
 * - Participantes conectados
 * - Interacciones (manos, preguntas, comprensión)
 * - Estadísticas
 *
 * Método: GET
 * Requiere: Sesión autenticada + pair_code válido
 */

require_once __DIR__ . '/../helpers_proyeccion.php';

session_start();

// Validar autenticación
if (!isset($_SESSION['auth_test']) && !isset($_SESSION['user_id'])) {
    returnError('not_authenticated', 'Debes iniciar sesión');
}

// Obtener pair_code
if (!isset($_GET['pair_code'])) {
    returnError('missing_params', 'Falta el código de emparejamiento');
}

$pairCode = $_GET['pair_code'];

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

// Leer datos de presentación
$presentationFile = __DIR__ . '/../../data/presentaciones/' . $presentationId . '.json';
if (!file_exists($presentationFile)) {
    returnError('presentation_not_found', 'No se encontró la presentación');
}

$presentationData = json_decode(file_get_contents($presentationFile), true);

// Determinar modo de presentación
$usesPdfSequence = !empty($presentationData['pdf_enabled']) &&
                   isset($presentationData['pdf_sequence']) &&
                   !empty($presentationData['pdf_sequence']);

// Obtener info del item actual
if ($usesPdfSequence) {
    $currentIndex = $sessionData['pdf_sequence_index'] ?? 0;
    $totalItems = count($presentationData['pdf_sequence']);
    $currentItem = $presentationData['pdf_sequence'][$currentIndex];

    $itemInfo = [
        'type' => $currentItem['type'],
        'index' => $currentIndex,
        'total' => $totalItems,
        'mode' => 'pdf_sequence'
    ];

    if ($currentItem['type'] === 'slide') {
        $itemInfo['slide_number'] = $currentItem['number'];
        $itemInfo['title'] = 'Slide ' . $currentItem['number'];
    } elseif ($currentItem['type'] === 'question') {
        $itemInfo['question_id'] = $currentItem['id'];
        foreach ($presentationData['preguntas'] as $q) {
            if ($q['id'] == $currentItem['id']) {
                $itemInfo['question_text'] = $q['pregunta'];
                $itemInfo['question_type'] = $q['tipo'];
                $itemInfo['title'] = 'Pregunta ' . $currentItem['id'];
                break;
            }
        }
    }
} else {
    $currentQuestion = $sessionData['pregunta_actual'] ?? 0;
    $totalQuestions = count($presentationData['preguntas']);

    $itemInfo = [
        'type' => $currentQuestion == 0 ? 'intro' : 'question',
        'index' => $currentQuestion,
        'total' => $totalQuestions,
        'mode' => 'traditional'
    ];

    if ($currentQuestion > 0 && $currentQuestion <= $totalQuestions) {
        $question = $presentationData['preguntas'][$currentQuestion - 1];
        $itemInfo['question_text'] = $question['pregunta'];
        $itemInfo['question_type'] = $question['tipo'];
        $itemInfo['title'] = 'Pregunta ' . $currentQuestion;
    } elseif ($currentQuestion == 0) {
        $itemInfo['title'] = 'Introducción';
    } else {
        $itemInfo['title'] = 'Finalizado';
    }
}

// Procesar participantes
$participants = [];
if (isset($sessionData['participantes'])) {
    foreach ($sessionData['participantes'] as $participant) {
        $participants[] = [
            'id' => $participant['id'],
            'nombre' => $participant['nombre'],
            'fecha_union' => $participant['fecha_union'],
            'respuestas_count' => isset($participant['respuestas']) ? count($participant['respuestas']) : 0
        ];
    }
}

// Procesar interacciones
$handsRaised = [];
$questions = [];
$understanding = ['confused' => 0, 'understood' => 0];
$recentReactions = [];

if (isset($sessionData['interacciones'])) {
    foreach ($sessionData['interacciones'] as $interaction) {
        switch ($interaction['type']) {
            case 'raise_hand':
                if ($interaction['data']['raised']) {
                    $handsRaised[] = [
                        'id' => $interaction['id'],
                        'participant_id' => $interaction['id_participante'],
                        'participant_name' => $interaction['nombre_participante'],
                        'timestamp' => $interaction['timestamp']
                    ];
                }
                break;

            case 'question':
                $questions[] = [
                    'id' => $interaction['id'],
                    'participant_id' => $interaction['id_participante'],
                    'participant_name' => $interaction['nombre_participante'],
                    'question' => $interaction['data']['question'],
                    'anonymous' => $interaction['data']['anonymous'] ?? false,
                    'timestamp' => $interaction['timestamp'],
                    'slide_number' => $interaction['data']['slide_number'] ?? null
                ];
                break;

            case 'understanding':
                if ($interaction['data']['level'] === 'confused') {
                    $understanding['confused']++;
                } elseif ($interaction['data']['level'] === 'understood') {
                    $understanding['understood']++;
                }
                break;

            case 'reaction':
                $recentReactions[] = [
                    'reaction' => $interaction['data']['reaction'],
                    'timestamp' => $interaction['timestamp']
                ];
                break;
        }
    }
}

// Ordenar preguntas por timestamp (más recientes primero)
usort($questions, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

// Solo últimas 10 reacciones
$recentReactions = array_slice($recentReactions, -10);

// Información de la presentación
$presentationInfo = [
    'id' => $presentationId,
    'titulo' => $presentationData['titulo'] ?? 'Sin título',
    'descripcion' => $presentationData['descripcion'] ?? '',
    'pdf_enabled' => $presentationData['pdf_enabled'] ?? false
];

// Retornar estado completo
returnSuccess([
    'session' => [
        'session_id' => $sessionId,
        'presentation' => $presentationInfo,
        'current_item' => $itemInfo,
        'participants_count' => count($participants),
        'estado' => $sessionData['estado'] ?? 'activa'
    ],
    'participants' => $participants,
    'interactions' => [
        'hands_raised' => $handsRaised,
        'hands_count' => count($handsRaised),
        'questions' => $questions,
        'questions_count' => count($questions),
        'understanding' => $understanding,
        'recent_reactions' => $recentReactions
    ],
    'timestamp' => date('c')
]);
