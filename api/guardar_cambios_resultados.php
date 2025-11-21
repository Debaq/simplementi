<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit;
}

// Obtener datos del POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['codigo']) || !isset($data['cambios'])) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

$codigo = $data['codigo'];
$cambios = $data['cambios'];

// Buscar el archivo de la sesión
$path_sesion = '';
if (is_dir('../data/respuestas')) {
    $presentaciones_dirs = scandir('../data/respuestas');
    foreach ($presentaciones_dirs as $presentacion_dir) {
        if ($presentacion_dir === '.' || $presentacion_dir === '..') continue;

        $path_presentacion = '../data/respuestas/' . $presentacion_dir;
        if (is_dir($path_presentacion)) {
            $archivo_sesion = $path_presentacion . '/sesion_' . $codigo . '.json';
            if (file_exists($archivo_sesion)) {
                $path_sesion = $archivo_sesion;
                break;
            }
        }
    }
}

if (empty($path_sesion)) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'Sesión no encontrada']);
    exit;
}

// Cargar datos de la sesión
$sesion_json = file_get_contents($path_sesion);
$sesion_data = json_decode($sesion_json, true);

if (!$sesion_data) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'Error al leer datos de la sesión']);
    exit;
}

// Aplicar cambios
foreach ($cambios as $cambio) {
    $id_participante = $cambio['id'];
    $nuevas_correctas = isset($cambio['correctas']) ? intval($cambio['correctas']) : null;
    $nuevos_puntos = isset($cambio['puntos']) ? intval($cambio['puntos']) : null;

    // Buscar el participante en la sesión
    foreach ($sesion_data['participantes'] as &$participante) {
        if ($participante['id'] === $id_participante) {
            // Actualizar respuestas correctas si se proporcionó
            if ($nuevas_correctas !== null && isset($participante['respuestas'])) {
                $total_respuestas = count($participante['respuestas']);
                $correctas_actuales = 0;

                // Contar correctas actuales
                foreach ($participante['respuestas'] as $respuesta) {
                    if (isset($respuesta['correcta']) && $respuesta['correcta']) {
                        $correctas_actuales++;
                    }
                }

                // Si cambió el número de correctas, ajustar las respuestas
                if ($nuevas_correctas !== $correctas_actuales) {
                    $diferencia = $nuevas_correctas - $correctas_actuales;

                    if ($diferencia > 0) {
                        // Necesitamos marcar más respuestas como correctas
                        $marcadas = 0;
                        foreach ($participante['respuestas'] as &$respuesta) {
                            if ($marcadas >= $diferencia) break;
                            if (!isset($respuesta['correcta']) || !$respuesta['correcta']) {
                                $respuesta['correcta'] = true;
                                $respuesta['editado_manualmente'] = true;
                                $marcadas++;
                            }
                        }
                    } else {
                        // Necesitamos marcar más respuestas como incorrectas
                        $marcadas = 0;
                        foreach ($participante['respuestas'] as &$respuesta) {
                            if ($marcadas >= abs($diferencia)) break;
                            if (isset($respuesta['correcta']) && $respuesta['correcta']) {
                                $respuesta['correcta'] = false;
                                $respuesta['editado_manualmente'] = true;
                                $marcadas++;
                            }
                        }
                    }
                }
            }

            // Guardar información de puntos editados manualmente
            if ($nuevos_puntos !== null) {
                if (!isset($participante['datos_editados'])) {
                    $participante['datos_editados'] = [];
                }
                $participante['datos_editados']['puntos'] = $nuevos_puntos;
                $participante['datos_editados']['fecha_edicion'] = date('Y-m-d H:i:s');
                $participante['datos_editados']['editado_por'] = $_SESSION['admin_user'];
            }

            break;
        }
    }
}

// Recalcular estadísticas generales
$total_correctas = 0;
$total_respuestas = 0;
$tiempo_total = 0;
$respuestas_con_tiempo = 0;

foreach ($sesion_data['participantes'] as $participante) {
    if (isset($participante['respuestas'])) {
        foreach ($participante['respuestas'] as $respuesta) {
            $total_respuestas++;
            if (isset($respuesta['correcta']) && $respuesta['correcta']) {
                $total_correctas++;
            }
            if (isset($respuesta['tiempo_respuesta'])) {
                $tiempo_total += $respuesta['tiempo_respuesta'];
                $respuestas_con_tiempo++;
            }
        }
    }
}

$sesion_data['estadisticas']['porcentaje_respuestas_correctas'] = $total_respuestas > 0
    ? ($total_correctas / $total_respuestas) * 100
    : 0;

$sesion_data['estadisticas']['tiempo_promedio_respuesta'] = $respuestas_con_tiempo > 0
    ? $tiempo_total / $respuestas_con_tiempo
    : 0;

// Agregar metadatos de edición
if (!isset($sesion_data['historial_ediciones'])) {
    $sesion_data['historial_ediciones'] = [];
}

$sesion_data['historial_ediciones'][] = [
    'fecha' => date('Y-m-d H:i:s'),
    'usuario' => $_SESSION['admin_user'],
    'cambios_realizados' => count($cambios)
];

// Guardar los cambios
$sesion_json_actualizado = json_encode($sesion_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (file_put_contents($path_sesion, $sesion_json_actualizado) === false) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar cambios']);
    exit;
}

// Registrar en logs de administración
$log_entry = [
    'fecha' => date('Y-m-d H:i:s'),
    'usuario' => $_SESSION['admin_user'],
    'accion' => 'editar_resultados_sesion',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'detalles' => 'Sesión: ' . $codigo . ', Cambios: ' . count($cambios)
];

$logs_file = '../data/admin_logs.json';
if (file_exists($logs_file)) {
    $logs_json = file_get_contents($logs_file);
    $logs_data = json_decode($logs_json, true);
    if (!isset($logs_data['logs'])) {
        $logs_data['logs'] = [];
    }
} else {
    $logs_data = ['logs' => []];
}

$logs_data['logs'][] = $log_entry;
file_put_contents($logs_file, json_encode($logs_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Content-Type: application/json');
echo json_encode(['exito' => true, 'mensaje' => 'Cambios guardados correctamente']);
