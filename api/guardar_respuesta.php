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

// Cargar configuración de la presentación para verificar intentos únicos
$test_id = $respuestas_data['id_presentacion'];
$test_file = "../data/presentaciones/$test_id.json";

$un_solo_intento = false;
if (file_exists($test_file)) {
    $test_json = file_get_contents($test_file);
    $test_data = json_decode($test_json, true);
    if ($test_data !== null) {
        $un_solo_intento = isset($test_data['configuracion']['un_solo_intento']) &&
                          $test_data['configuracion']['un_solo_intento'] === true;
    }
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
    // Obtener el nombre de la cookie
    $nombre_participante = isset($_COOKIE['participante_nombre']) ? $_COOKIE['participante_nombre'] : 'Anónimo';

    $respuestas_data['participantes'][] = [
        'id' => $id_participante,
        'nombre' => $nombre_participante, // Añadir el nombre
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
    // Actualizar el nombre del participante si existe en la cookie (por si cambió)
    if (isset($_COOKIE['participante_nombre'])) {
        $respuestas_data['participantes'][$participante_index]['nombre'] = $_COOKIE['participante_nombre'];
    }
    
    // Buscar si ya respondió esta pregunta
    $respuesta_index = -1;
    foreach ($respuestas_data['participantes'][$participante_index]['respuestas'] as $index => $resp) {
        if ($resp['id_pregunta'] == $id_pregunta) {
            $respuesta_index = $index;
            break;
        }
    }

    // Si ya respondió esta pregunta
    if ($respuesta_index !== -1) {
        // Verificar si está habilitado el modo de un solo intento
        if ($un_solo_intento) {
            // No permitir cambiar la respuesta
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Ya has respondido esta pregunta y no puedes cambiar tu respuesta.',
                'single_attempt' => true
            ]);
            exit;
        }

        // Si no hay restricción, actualizar la respuesta
        $respuestas_data['participantes'][$participante_index]['respuestas'][$respuesta_index]['respuesta'] = $respuesta;
        $respuestas_data['participantes'][$participante_index]['respuestas'][$respuesta_index]['tiempo_respuesta'] = $tiempo_respuesta;
    } else {
        // Si no ha respondido, agregar la respuesta
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

// Verificar si es una petición AJAX (modo asíncrono)
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// También detectar si el Content-Type es application/json o si se envió vía fetch
$is_fetch = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($is_ajax || $is_fetch) {
    // Responder con JSON para modo asíncrono
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Respuesta guardada correctamente',
        'id_pregunta' => $id_pregunta
    ]);
} else {
    // Redireccionar al participante de vuelta a la página con un parámetro de éxito (modo síncrono)
    header("Location: ../participante.php?codigo=$codigo_sesion&respuesta_enviada=1");
}
exit;
?>