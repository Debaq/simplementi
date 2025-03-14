<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener datos de la solicitud
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_sesion = isset($_POST['codigo_sesion']) ? $_POST['codigo_sesion'] : '';
    $id_participante = isset($_POST['id_participante']) ? $_POST['id_participante'] : '';
    $id_pregunta = isset($_POST['id_pregunta']) ? intval($_POST['id_pregunta']) : 0;
    $respuesta = isset($_POST['respuesta']) ? $_POST['respuesta'] : '';
    $tiempo_respuesta = isset($_POST['tiempo_respuesta']) ? intval($_POST['tiempo_respuesta']) : 0;
} else {
    // También permitir GET para pruebas
    $codigo_sesion = isset($_GET['codigo_sesion']) ? $_GET['codigo_sesion'] : '';
    $id_participante = isset($_GET['id_participante']) ? $_GET['id_participante'] : '';
    $id_pregunta = isset($_GET['id_pregunta']) ? intval($_GET['id_pregunta']) : 0;
    $respuesta = isset($_GET['respuesta']) ? $_GET['respuesta'] : '';
    $tiempo_respuesta = isset($_GET['tiempo_respuesta']) ? intval($_GET['tiempo_respuesta']) : 0;
}

// Verificar datos
if (empty($codigo_sesion) || empty($id_participante) || $id_pregunta <= 0 || $respuesta === '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Faltan datos requeridos',
        'received' => [
            'codigo_sesion' => $codigo_sesion,
            'id_participante' => $id_participante,
            'id_pregunta' => $id_pregunta,
            'respuesta' => $respuesta
        ]
    ]);
    exit;
}

// Buscar el archivo de sesión
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Sesión no encontrada']);
    exit;
}

$session_file = $session_files[0];

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents($session_file);
    if ($respuestas_json === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se pudo leer el archivo de sesión']);
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'El archivo de sesión no tiene un formato JSON válido']);
        exit;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo de sesión: ' . $e->getMessage()]);
    exit;
}

// Buscar si el participante ya existe
$participante_index = -1;
foreach ($respuestas_data['participantes'] as $index => $participante) {
    if ($participante['id'] == $id_participante) {
        $participante_index = $index;
        break;
    }
}

// Si el participante no existe, crear uno nuevo
if ($participante_index === -1) {
    $respuestas_data['participantes'][] = [
        'id' => $id_participante,
        'fecha_union' => date('Y-m-d\TH:i:s'),
        'respuestas' => [
            [
                'id_pregunta' => $id_pregunta,
                'respuesta' => $respuesta,
                'tiempo_respuesta' => $tiempo_respuesta
            ]
        ]
    ];
} else {
    // Buscar si ya respondió esta pregunta
    $respuesta_index = -1;
    foreach ($respuestas_data['participantes'][$participante_index]['respuestas'] as $index => $resp) {
        if ($resp['id_pregunta'] == $id_pregunta) {
            $respuesta_index = $index;
            break;
        }
    }
    
    // Si ya respondió, actualizar la respuesta
    if ($respuesta_index !== -1) {
        $respuestas_data['participantes'][$participante_index]['respuestas'][$respuesta_index]['respuesta'] = $respuesta;
        $respuestas_data['participantes'][$participante_index]['respuestas'][$respuesta_index]['tiempo_respuesta'] = $tiempo_respuesta;
    } else {
        // Si no, agregar la respuesta
        $respuestas_data['participantes'][$participante_index]['respuestas'][] = [
            'id_pregunta' => $id_pregunta,
            'respuesta' => $respuesta,
            'tiempo_respuesta' => $tiempo_respuesta
        ];
    }
}

// Guardar los cambios
try {
    $success = file_put_contents($session_file, json_encode($respuestas_data, JSON_PRETTY_PRINT));
    if ($success === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se pudo escribir en el archivo de sesión']);
        exit;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error al escribir en el archivo de sesión: ' . $e->getMessage()]);
    exit;
}

// Redireccionar al participante de vuelta a la página con un parámetro de éxito
header("Location: ../participante.php?codigo=$codigo_sesion&respuesta_enviada=1");
exit;
?>