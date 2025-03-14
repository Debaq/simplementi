<?php
header('Content-Type: application/json');

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$id_participante = isset($_GET['participante']) ? $_GET['participante'] : '';
$id_pregunta = isset($_GET['pregunta']) ? intval($_GET['pregunta']) : 0;

if (empty($codigo_sesion) || empty($id_participante) || $id_pregunta <= 0) {
    echo json_encode(['error' => 'Parámetros incorrectos']);
    exit;
}

// Leer los datos actuales
$respuestas_json = file_get_contents('../data/respuestas.json');
$respuestas_data = json_decode($respuestas_json, true);

// Buscar la sesión
$sesion = null;
foreach ($respuestas_data['sesiones'] as $s) {
    if ($s['codigo_sesion'] == $codigo_sesion) {
        $sesion = $s;
        break;
    }
}

if (!$sesion) {
    echo json_encode(['error' => 'Sesión no encontrada']);
    exit;
}

// Verificar si el participante existe y si ya respondió esta pregunta
$respondido = false;
foreach ($sesion['participantes'] as $participante) {
    if ($participante['id_participante'] == $id_participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $id_pregunta) {
                $respondido = true;
                break 2; // Salir de ambos bucles
            }
        }
    }
}

echo json_encode(['success' => true, 'respondido' => $respondido]);
?>