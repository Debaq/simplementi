<?php
session_start();
require_once 'includes/funciones.php';
require_once 'includes/editar/gift_parser.php';

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

// Verificar que se haya enviado un archivo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: editar.php?id=' . ($_GET['id'] ?? ''));
    exit;
}

$id_presentacion = $_POST['id'] ?? '';
if (empty($id_presentacion)) {
    $_SESSION['error'] = 'ID de presentación no válido';
    header('Location: presentaciones.php');
    exit;
}

$archivo_presentacion = "data/presentaciones/$id_presentacion.json";

// Verificar que existe la presentación
if (!file_exists($archivo_presentacion)) {
    $_SESSION['error'] = 'Presentación no encontrada';
    header('Location: presentaciones.php');
    exit;
}

// Cargar presentación actual
$presentacion = json_decode(file_get_contents($archivo_presentacion), true);

// Verificar que se subió un archivo
if (!isset($_FILES['archivo_gift']) || $_FILES['archivo_gift']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Error al subir el archivo. Por favor intente nuevamente.';
    header('Location: editar.php?id=' . $id_presentacion);
    exit;
}

$archivo_temporal = $_FILES['archivo_gift']['tmp_name'];
$nombre_archivo = $_FILES['archivo_gift']['name'];

// Verificar extensión
$extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
if (!in_array($extension, ['txt', 'gift'])) {
    $_SESSION['error'] = 'El archivo debe ser .txt o .gift';
    header('Location: editar.php?id=' . $id_presentacion);
    exit;
}

// Leer contenido del archivo
$contenido = file_get_contents($archivo_temporal);

if ($contenido === false) {
    $_SESSION['error'] = 'No se pudo leer el archivo';
    header('Location: editar.php?id=' . $id_presentacion);
    exit;
}

// Parse el archivo GIFT
try {
    $preguntas_nuevas = GiftParser::parse($contenido);

    if (empty($preguntas_nuevas)) {
        $_SESSION['error'] = 'No se encontraron preguntas válidas en el archivo GIFT';
        header('Location: editar.php?id=' . $id_presentacion);
        exit;
    }

    // Validar preguntas
    $errores = GiftParser::validar($preguntas_nuevas);

    if (!empty($errores)) {
        $_SESSION['error'] = 'Errores en el archivo GIFT:<br>' . implode('<br>', $errores);
        header('Location: editar.php?id=' . $id_presentacion);
        exit;
    }

    // Obtener el siguiente ID disponible
    $max_id = 0;
    if (isset($presentacion['preguntas']) && is_array($presentacion['preguntas'])) {
        foreach ($presentacion['preguntas'] as $pregunta) {
            if (isset($pregunta['id']) && $pregunta['id'] > $max_id) {
                $max_id = $pregunta['id'];
            }
        }
    } else {
        $presentacion['preguntas'] = [];
    }

    // Agregar IDs a las preguntas nuevas
    foreach ($preguntas_nuevas as &$pregunta) {
        $max_id++;
        $pregunta['id'] = $max_id;

        // Agregar campos opcionales si no existen
        if (!isset($pregunta['imagen'])) {
            $pregunta['imagen'] = '';
        }
        if (!isset($pregunta['imagen_explicacion'])) {
            $pregunta['imagen_explicacion'] = '';
        }
    }

    // Agregar preguntas a la presentación
    $presentacion['preguntas'] = array_merge($presentacion['preguntas'], $preguntas_nuevas);

    // Guardar presentación
    if (file_put_contents($archivo_presentacion, json_encode($presentacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $cantidad = count($preguntas_nuevas);
        $_SESSION['mensaje'] = "Se importaron exitosamente $cantidad pregunta(s) desde el archivo GIFT";
    } else {
        $_SESSION['error'] = 'Error al guardar las preguntas';
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Error al procesar el archivo: ' . $e->getMessage();
}

header('Location: editar.php?id=' . $id_presentacion);
exit;
