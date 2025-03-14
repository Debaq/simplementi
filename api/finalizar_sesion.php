<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar parámetros
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

// Buscar la sesión en los archivos
$session_files = glob("../data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada.";
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    echo "Error: No se pudo leer el archivo de sesión.";
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    echo "Error: El archivo de sesión no tiene un formato JSON válido.";
    exit;
}

// Finalizar la sesión
$session_data['estado'] = 'finalizada';
$session_data['fecha_fin'] = date('Y-m-d\TH:i:s');

// Guardar los cambios
$result = file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));

if ($result === false) {
    echo "Error: No se pudo actualizar el archivo de sesión.";
    exit;
}

// Redireccionar a la página de resumen
header("Location: ../resumen.php?codigo=$codigo_sesion");
exit;
?>  