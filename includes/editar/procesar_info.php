<?php
// Este archivo se incluye desde editar_presentacion.php para procesar el formulario de información básica

// Recoger datos del formulario
$datos_actualizados = [
    'titulo' => isset($_POST['titulo']) ? trim($_POST['titulo']) : '',
    'descripcion' => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '',
    'categorias' => isset($_POST['categorias']) ? trim($_POST['categorias']) : '',
    'protegido' => isset($_POST['protegido']) && $_POST['protegido'] === '1',
    'password' => isset($_POST['password']) ? $_POST['password'] : '',
    'mostrar_respuestas' => isset($_POST['mostrar_respuestas']) ? $_POST['mostrar_respuestas'] : 'despues_pregunta',
    'tiempo_por_pregunta' => isset($_POST['tiempo_por_pregunta']) ? intval($_POST['tiempo_por_pregunta']) : 0,
    'permitir_retroceder' => isset($_POST['permitir_retroceder']) && $_POST['permitir_retroceder'] === '1',
    'mostrar_estadisticas' => isset($_POST['mostrar_estadisticas']) && $_POST['mostrar_estadisticas'] === '1',
    'permitir_exportar' => isset($_POST['permitir_exportar']) && $_POST['permitir_exportar'] === '1'
];

// Validar campos básicos
$errores = [];

if (empty($datos_actualizados['titulo'])) {
    $errores[] = 'El título es obligatorio';
}

if ($datos_actualizados['protegido'] && empty($datos_actualizados['password'])) {
    $errores[] = 'Si la presentación está protegida, debe especificar una contraseña';
}

if (empty($errores)) {
    // Crear el array de categorías a partir del texto
    $categorias_array = [];
    if (!empty($datos_actualizados['categorias'])) {
        $categorias_array = array_map('trim', explode(',', $datos_actualizados['categorias']));
    }
    
    // Actualizar datos de la presentación
    $presentacion_data['titulo'] = $datos_actualizados['titulo'];
    $presentacion_data['descripcion'] = $datos_actualizados['descripcion'];
    $presentacion_data['protegido'] = $datos_actualizados['protegido'];
    
    // Actualizar o eliminar contraseña según corresponda
    if ($datos_actualizados['protegido']) {
        $presentacion_data['password'] = $datos_actualizados['password'];
    } elseif (isset($presentacion_data['password'])) {
        unset($presentacion_data['password']);
    }
    
    // Actualizar configuración
    $presentacion_data['configuracion']['mostrar_respuestas'] = $datos_actualizados['mostrar_respuestas'];
    $presentacion_data['configuracion']['tiempo_por_pregunta'] = $datos_actualizados['tiempo_por_pregunta'];
    $presentacion_data['configuracion']['permitir_retroceder'] = $datos_actualizados['permitir_retroceder'];
    $presentacion_data['configuracion']['mostrar_estadisticas'] = $datos_actualizados['mostrar_estadisticas'];
    $presentacion_data['configuracion']['permitir_exportar'] = $datos_actualizados['permitir_exportar'];
    
    // Guardar cambios en el archivo de la presentación
    $result = file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        $mensaje = "Error al guardar los cambios. Compruebe los permisos de escritura.";
        $tipo_mensaje = "danger";
    } else {
        // Actualizar índice de presentaciones
        if (file_exists('data/index.json')) {
            $index_json = file_get_contents('data/index.json');
            $index_data = json_decode($index_json, true);
            
            foreach ($index_data['presentaciones'] as &$presentacion) {
                if ($presentacion['id'] === $id_presentacion) {
                    $presentacion['titulo'] = $datos_actualizados['titulo'];
                    $presentacion['descripcion'] = $datos_actualizados['descripcion'];
                    $presentacion['protegido'] = $datos_actualizados['protegido'];
                    $presentacion['categorias'] = $categorias_array;
                    break;
                }
            }
            
            file_put_contents('data/index.json', json_encode($index_data, JSON_PRETTY_PRINT));
        }
        
        // Registrar la acción
        registrarAccion($_SESSION['admin_user'], 'editar_presentacion');
        
        $mensaje = "Información básica actualizada correctamente.";
        $tipo_mensaje = "success";
    }
} else {
    $mensaje = 'Por favor, corrija los siguientes errores:<ul>';
    foreach ($errores as $error) {
        $mensaje .= '<li>' . $error . '</li>';
    }
    $mensaje .= '</ul>';
    $tipo_mensaje = 'danger';
}
?>