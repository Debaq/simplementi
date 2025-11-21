<?php
/**
 * API para cambiar el slide actual del PDF en una sesión
 */

// Mostrar errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar datos
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nuevo_slide = isset($_GET['slide']) ? intval($_GET['slide']) : 0;

if (empty($codigo_sesion) || $nuevo_slide <= 0) {
    echo "Error: Parámetros incorrectos";
    exit;
}

// Buscar el archivo de sesión
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada";
    exit;
}

$session_file = $session_files[0];

// Leer los datos actuales
try {
    $respuestas_json = file_get_contents($session_file);
    if ($respuestas_json === false) {
        echo "Error: No se pudo leer el archivo de sesión";
        exit;
    }

    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo "Error: Formato JSON inválido";
        exit;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Obtener información de la presentación para validar
$test_id = $respuestas_data['id_presentacion'];
$test_file = "../data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Presentación no encontrada";
    exit;
}

try {
    $presentacion_json = file_get_contents($test_file);
    $presentacion_data = json_decode($presentacion_json, true);

    // Verificar si tiene PDF habilitado
    if (!empty($presentacion_data['pdf_enabled']) && isset($presentacion_data['pdf_images'])) {
        $total_slides = count($presentacion_data['pdf_images']);

        // Validar número de slide
        if ($nuevo_slide < 1 || $nuevo_slide > $total_slides) {
            echo "Error: Número de slide inválido (1-$total_slides)";
            exit;
        }

        // Actualizar slide actual
        $respuestas_data['pdf_slide_actual'] = $nuevo_slide;

        // Guardar los cambios
        $success = file_put_contents($session_file, json_encode($respuestas_data, JSON_PRETTY_PRINT));
        if ($success === false) {
            echo "Error: No se pudo guardar el cambio";
            exit;
        }

        // Redireccionar
        header("Location: ../presentador.php?codigo=$codigo_sesion");
        exit;
    } else {
        echo "Error: PDF no habilitado en esta presentación";
        exit;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
