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

// Verificar que se proporcionaron los parámetros necesarios
$id_presentacion = isset($_GET['id']) ? $_GET['id'] : '';
$pregunta_id = isset($_GET['pregunta_id']) ? (int)$_GET['pregunta_id'] : 0;

if (empty($id_presentacion) || $pregunta_id <= 0) {
    // Redirigir si falta algún parámetro
    header("Location: admin_panel.php?seccion=presentaciones");
    exit;
}

// Verificar si existe la presentación
$presentacion_file = "data/presentaciones/$id_presentacion.json";
if (!file_exists($presentacion_file)) {
    echo "Error: Presentación no encontrada.";
    exit;
}

// Leer datos de la presentación
$presentacion_json = file_get_contents($presentacion_file);
$presentacion_data = json_decode($presentacion_json, true);

if ($presentacion_data === null) {
    echo "Error: El archivo de la presentación no tiene un formato JSON válido.";
    exit;
}

// Buscar la pregunta por ID
$pregunta = null;
$indice_pregunta = -1;

foreach ($presentacion_data['preguntas'] as $index => $p) {
    if ($p['id'] === $pregunta_id) {
        $pregunta = $p;
        $indice_pregunta = $index;
        break;
    }
}

if ($pregunta === null) {
    echo "Error: Pregunta no encontrada.";
    exit;
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos comunes
    $pregunta_texto = isset($_POST['pregunta_texto']) ? trim($_POST['pregunta_texto']) : '';
    $explicacion = isset($_POST['explicacion']) ? trim($_POST['explicacion']) : '';
    
    // Validar campos básicos
    $errores = [];
    
    if (empty($pregunta_texto)) {
        $errores[] = 'El texto de la pregunta es obligatorio';
    }
    
    if (empty($errores)) {
        // Actualizar pregunta con datos comunes
        $pregunta['pregunta'] = $pregunta_texto;
        
        // Actualizar o eliminar explicación
        if (!empty($explicacion)) {
            $pregunta['explicacion'] = $explicacion;
        } elseif (isset($pregunta['explicacion'])) {
            unset($pregunta['explicacion']);
        }
        
        // Procesar imagen de la pregunta si se ha subido una nueva
        if (isset($_FILES['imagen_pregunta']) && $_FILES['imagen_pregunta']['error'] === UPLOAD_ERR_OK) {
            $resultado_imagen = procesarImagen($_FILES['imagen_pregunta'], $id_presentacion, $pregunta_id, 'pregunta');
            if ($resultado_imagen['exito']) {
                // Guardar la ruta de la nueva imagen
                $pregunta['imagen'] = $resultado_imagen['ruta'];
            } else {
                $errores[] = "Error al procesar la imagen de la pregunta: " . $resultado_imagen['mensaje'];
            }
        }
        
        // Procesar imagen de la explicación si se ha subido una nueva
        if (empty($errores) && isset($_FILES['imagen_explicacion']) && $_FILES['imagen_explicacion']['error'] === UPLOAD_ERR_OK) {
            $resultado_imagen = procesarImagen($_FILES['imagen_explicacion'], $id_presentacion, $pregunta_id, 'explicacion');
            if ($resultado_imagen['exito']) {
                // Guardar la ruta de la nueva imagen
                $pregunta['imagen_explicacion'] = $resultado_imagen['ruta'];
            } else {
                $errores[] = "Error al procesar la imagen de la explicación: " . $resultado_imagen['mensaje'];
            }
        }
        
        // Eliminar imágenes si se solicita
        if (isset($_POST['eliminar_imagen_pregunta']) && $_POST['eliminar_imagen_pregunta'] === '1') {
            if (isset($pregunta['imagen']) && file_exists($pregunta['imagen'])) {
                unlink($pregunta['imagen']);
            }
            unset($pregunta['imagen']);
        }
        
        if (isset($_POST['eliminar_imagen_explicacion']) && $_POST['eliminar_imagen_explicacion'] === '1') {
            if (isset($pregunta['imagen_explicacion']) && file_exists($pregunta['imagen_explicacion'])) {
                unlink($pregunta['imagen_explicacion']);
            }
            unset($pregunta['imagen_explicacion']);
        }
        
        // Procesar datos específicos según tipo de pregunta
        if (empty($errores)) {
            switch ($pregunta['tipo']) {
                case 'opcion_multiple':
                    // Recoger opciones
                    $opciones = isset($_POST['opciones']) ? $_POST['opciones'] : [];
                    
                    // Verificar que haya opciones
                    if (empty($opciones)) {
                        $errores[] = 'Debe proporcionar opciones para la pregunta';
                        break;
                    }
                    
                    // Eliminar opciones vacías
                    $opciones = array_filter($opciones, function($opcion) {
                        return trim($opcion) !== '';
                    });
                    
                    // Verificar que queden opciones después de filtrar
                    if (empty($opciones)) {
                        $errores[] = 'Debe proporcionar al menos una opción válida';
                        break;
                    }
                    
                    // Recoger respuesta correcta
                    $respuesta_correcta_index = isset($_POST['respuesta_correcta_index']) ? (int)$_POST['respuesta_correcta_index'] : -1;
                    
                    // Actualizar opciones
                    $pregunta['opciones'] = array_values($opciones); // Reindexar array
                    
                    // Actualizar respuesta correcta
                    if ($respuesta_correcta_index >= 0 && $respuesta_correcta_index < count($opciones)) {
                        $pregunta['respuesta_correcta'] = $opciones[$respuesta_correcta_index];
                    } elseif (isset($pregunta['respuesta_correcta'])) {
                        unset($pregunta['respuesta_correcta']);
                    }
                    
                    break;
                    
                case 'verdadero_falso':
                    // Recoger respuesta correcta
                    $respuesta_correcta = isset($_POST['respuesta_correcta']) ? $_POST['respuesta_correcta'] : '';
                    
                    if ($respuesta_correcta === 'true') {
                        $pregunta['respuesta_correcta'] = 'Verdadero';
                    } elseif ($respuesta_correcta === 'false') {
                        $pregunta['respuesta_correcta'] = 'Falso';
                    } elseif (isset($pregunta['respuesta_correcta'])) {
                        unset($pregunta['respuesta_correcta']);
                    }
                    
                    break;
                    
                case 'nube_palabras':
                    // Recoger configuración de visualización en tiempo real
                    $mostrar_tiempo_real = isset($_POST['mostrar_tiempo_real']) && $_POST['mostrar_tiempo_real'] === '1';
                    $pregunta['mostrar_tiempo_real'] = $mostrar_tiempo_real;
                    
                    break;
            }
        }
        
        if (empty($errores)) {
            // Actualizar la pregunta en el array
            $presentacion_data['preguntas'][$indice_pregunta] = $pregunta;
            
            // Guardar cambios
            $result = file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
            
            if ($result === false) {
                $mensaje = 'Error al guardar los cambios en la pregunta.';
                $tipo_mensaje = 'danger';
            } else {
                // Registrar la acción
                registrarAccion($_SESSION['admin_user'], 'editar_pregunta');
                
                $mensaje = 'Pregunta actualizada correctamente.';
                $tipo_mensaje = 'success';
            }
        }
    }
    
    if (!empty($errores)) {
        $mensaje = 'Por favor, corrija los siguientes errores:<ul>';
        foreach ($errores as $error) {
            $mensaje .= '<li>' . $error . '</li>';
        }
        $mensaje .= '</ul>';
        $tipo_mensaje = 'danger';
    }
}

// Normalizar datos para formulario según tipo
$opciones_seleccionadas = [];
$respuesta_correcta_index = -1;
$respuesta_correcta_verdadero_falso = '';

switch ($pregunta['tipo']) {
    case 'opcion_multiple':
        $opciones_seleccionadas = $pregunta['opciones'] ?? [];
        
        // Determinar índice de respuesta correcta
        if (isset($pregunta['respuesta_correcta'])) {
            foreach ($opciones_seleccionadas as $index => $opcion) {
                if ($opcion === $pregunta['respuesta_correcta']) {
                    $respuesta_correcta_index = $index;
                    break;
                }
            }
        }
        break;
        
    case 'verdadero_falso':
        if (isset($pregunta['respuesta_correcta'])) {
            if ($pregunta['respuesta_correcta'] === 'Verdadero') {
                $respuesta_correcta_verdadero_falso = 'true';
            } elseif ($pregunta['respuesta_correcta'] === 'Falso') {
                $respuesta_correcta_verdadero_falso = 'false';
            }
        }
        break;
}

// Obtener tipo de pregunta traducido
$tipo_pregunta_texto = '';
switch ($pregunta['tipo']) {
    case 'opcion_multiple':
        $tipo_pregunta_texto = 'Opción múltiple';
        break;
    case 'verdadero_falso':
        $tipo_pregunta_texto = 'Verdadero/Falso';
        break;
    case 'nube_palabras':
        $tipo_pregunta_texto = 'Nube de palabras';
        break;
    case 'palabra_libre':
        $tipo_pregunta_texto = 'Respuesta libre';
        break;
    default:
        $tipo_pregunta_texto = $pregunta['tipo'];
}

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