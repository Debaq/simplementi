<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Incluir el archivo de funciones de administración
include('includes/admin/funciones.php');

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_panel.php");
    exit;
}

// Recoger datos del formulario
$id_presentacion = isset($_POST['id_presentacion']) ? $_POST['id_presentacion'] : '';
$tipo_pregunta = isset($_POST['tipo_pregunta']) ? $_POST['tipo_pregunta'] : '';
$pregunta_texto = isset($_POST['pregunta_texto']) ? trim($_POST['pregunta_texto']) : '';

// Verificar campos obligatorios
if (empty($id_presentacion) || empty($tipo_pregunta) || empty($pregunta_texto)) {
    // Redirigir con error
    header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=Faltan campos obligatorios#agregar");
    exit;
}

// Verificar si existe la presentación
$presentacion_file = "data/presentaciones/$id_presentacion.json";
if (!file_exists($presentacion_file)) {
    // Redirigir con error
    header("Location: admin_panel.php?seccion=presentaciones&error=1&mensaje=Presentación no encontrada");
    exit;
}

// Leer datos de la presentación
$presentacion_json = file_get_contents($presentacion_file);
$presentacion_data = json_decode($presentacion_json, true);

if ($presentacion_data === null) {
    // Redirigir con error
    header("Location: admin_panel.php?seccion=presentaciones&error=1&mensaje=Error al leer la presentación");
    exit;
}

// Generar ID para la nueva pregunta
$nuevo_id = 1;
if (!empty($presentacion_data['preguntas'])) {
    // Encontrar el ID más alto y sumar 1
    $ids = array_map(function($pregunta) {
        return $pregunta['id'];
    }, $presentacion_data['preguntas']);
    $nuevo_id = max($ids) + 1;
}

// Crear estructura base de la pregunta
$nueva_pregunta = [
    'id' => $nuevo_id,
    'tipo' => $tipo_pregunta,
    'pregunta' => $pregunta_texto
];

// Procesar imagen de la pregunta si existe
if (isset($_FILES['imagen_pregunta']) && $_FILES['imagen_pregunta']['error'] === UPLOAD_ERR_OK) {
    $resultado_imagen = procesarImagen($_FILES['imagen_pregunta'], $id_presentacion, $nuevo_id, 'pregunta');
    if ($resultado_imagen['exito']) {
        $nueva_pregunta['imagen'] = $resultado_imagen['ruta'];
    } else {
        // Redirigir con error
        header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=" . urlencode($resultado_imagen['mensaje']) . "#agregar");
        exit;
    }
}

// Completar datos según el tipo de pregunta
switch ($tipo_pregunta) {
    case 'opcion_multiple':
        // Recoger opciones
        $opciones = isset($_POST['opciones']) ? $_POST['opciones'] : [];

        // Verificar que haya opciones
        if (empty($opciones)) {
            header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=Debe proporcionar opciones para la pregunta#agregar");
            exit;
        }

        // Eliminar opciones vacías
        $opciones = array_filter($opciones, function($opcion) {
            return trim($opcion) !== '';
        });

        // Verificar que queden opciones después de filtrar
        if (empty($opciones)) {
            header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=Debe proporcionar al menos una opción válida#agregar");
            exit;
        }

        // Recoger respuesta correcta
        $respuesta_correcta_index = isset($_POST['respuesta_correcta_index']) ? (int)$_POST['respuesta_correcta_index'] : -1;

        // Agregar a la pregunta
        $nueva_pregunta['opciones'] = array_values($opciones); // Reindexar array

        // Si se seleccionó una respuesta correcta válida
        if ($respuesta_correcta_index >= 0 && $respuesta_correcta_index < count($opciones)) {
            $nueva_pregunta['respuesta_correcta'] = $opciones[$respuesta_correcta_index];
        }

        // Recoger feedbacks (opcional, uno por opción)
        if (isset($_POST['feedbacks']) && is_array($_POST['feedbacks'])) {
            $feedbacks_array = $_POST['feedbacks'];
            $feedbacks_map = [];

            // Asociar cada feedback con su opción correspondiente
            foreach ($nueva_pregunta['opciones'] as $index => $opcion) {
                if (isset($feedbacks_array[$index]) && !empty(trim($feedbacks_array[$index]))) {
                    $feedbacks_map[$opcion] = trim($feedbacks_array[$index]);
                }
            }

            // Solo agregar feedbacks si hay al menos uno
            if (!empty($feedbacks_map)) {
                $nueva_pregunta['feedbacks'] = $feedbacks_map;
            }
        }

        break;
        
    case 'verdadero_falso':
        // Opciones fijas
        $nueva_pregunta['opciones'] = ['Verdadero', 'Falso'];
        
        // Recoger respuesta correcta
        $respuesta_correcta = isset($_POST['respuesta_correcta']) ? $_POST['respuesta_correcta'] : '';
        
        if ($respuesta_correcta === 'true') {
            $nueva_pregunta['respuesta_correcta'] = 'Verdadero';
        } elseif ($respuesta_correcta === 'false') {
            $nueva_pregunta['respuesta_correcta'] = 'Falso';
        }
        
        break;
        
    case 'nube_palabras':
        // Recoger configuración de visualización en tiempo real
        $mostrar_tiempo_real = isset($_POST['mostrar_tiempo_real']) && $_POST['mostrar_tiempo_real'] === '1';
        $nueva_pregunta['mostrar_tiempo_real'] = $mostrar_tiempo_real;
        
        break;
        
    case 'palabra_libre':
        // No requiere configuración adicional
        break;
        
    default:
        // Tipo de pregunta no válido
        header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=Tipo de pregunta no válido#agregar");
        exit;
}

// Recoger explicación (opcional)
if (isset($_POST['explicacion']) && !empty($_POST['explicacion'])) {
    $nueva_pregunta['explicacion'] = trim($_POST['explicacion']);
    
    // Procesar imagen de la explicación si existe
    if (isset($_FILES['imagen_explicacion']) && $_FILES['imagen_explicacion']['error'] === UPLOAD_ERR_OK) {
        $resultado_imagen = procesarImagen($_FILES['imagen_explicacion'], $id_presentacion, $nuevo_id, 'explicacion');
        if ($resultado_imagen['exito']) {
            $nueva_pregunta['imagen_explicacion'] = $resultado_imagen['ruta'];
        } else {
            // Redirigir con error
            header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=" . urlencode($resultado_imagen['mensaje']) . "#agregar");
            exit;
        }
    }
}

// Agregar la nueva pregunta a la presentación
$presentacion_data['preguntas'][] = $nueva_pregunta;

// Guardar cambios
$result = file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));

if ($result === false) {
    // Redirigir con error
    header("Location: editar_presentacion.php?id=$id_presentacion&error=1&mensaje=Error al guardar la pregunta#agregar");
    exit;
}

// Actualizar número de preguntas en el índice
$num_preguntas = count($presentacion_data['preguntas']);
actualizarNumeroPreguntas($id_presentacion, $num_preguntas);

// Registrar la acción
registrarAccion($_SESSION['admin_user'], 'agregar_pregunta');

// Redirigir con éxito
header("Location: editar_presentacion.php?id=$id_presentacion&exito=1&mensaje=Pregunta agregada correctamente#preguntas");
exit;

// Función para procesar y guardar imágenes
function procesarImagen($archivo, $id_presentacion, $id_pregunta, $tipo) {
    // Verificar el tamaño (máximo 2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return [
            'exito' => false,
            'mensaje' => 'La imagen excede el tamaño máximo permitido (2MB)'
        ];
    }
    
    // Verificar el tipo de archivo
    $tipo_archivo = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($tipo_archivo, $tipos_permitidos)) {
        return [
            'exito' => false,
            'mensaje' => 'Tipo de archivo no permitido. Sólo se permiten imágenes JPG, JPEG, PNG y GIF'
        ];
    }
    
    // Crear directorio si no existe
    $directorio = "img/presentaciones/$id_presentacion";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }
    
    // Generar nombre único para la imagen
    $nombre_archivo = $id_pregunta . '_' . $tipo . '_' . time() . '.' . $tipo_archivo;
    $ruta_destino = $directorio . '/' . $nombre_archivo;
    
    // Mover el archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        return [
            'exito' => true,
            'ruta' => $ruta_destino
        ];
    } else {
        return [
            'exito' => false,
            'mensaje' => 'Error al guardar la imagen. Compruebe los permisos de escritura.'
        ];
    }
}

// Función auxiliar para actualizar el número de preguntas en el índice
function actualizarNumeroPreguntas($id_presentacion, $num_preguntas) {
    if (file_exists('data/index.json')) {
        $index_json = file_get_contents('data/index.json');
        $index_data = json_decode($index_json, true);
        
        foreach ($index_data['presentaciones'] as &$presentacion) {
            if ($presentacion['id'] === $id_presentacion) {
                $presentacion['num_preguntas'] = $num_preguntas;
                break;
            }
        }
        
        file_put_contents('data/index.json', json_encode($index_data, JSON_PRETTY_PRINT));
    }
}
?>