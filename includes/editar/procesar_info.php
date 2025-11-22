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
    'permitir_exportar' => isset($_POST['permitir_exportar']) && $_POST['permitir_exportar'] === '1',
    'permitir_anotaciones' => isset($_POST['permitir_anotaciones']) && $_POST['permitir_anotaciones'] === '1',
    'exportar_con_anotaciones' => isset($_POST['exportar_con_anotaciones']) && $_POST['exportar_con_anotaciones'] === '1',
    'permitir_notas' => isset($_POST['permitir_notas']) && $_POST['permitir_notas'] === '1',
    'permitir_marcadores' => isset($_POST['permitir_marcadores']) && $_POST['permitir_marcadores'] === '1',
    'permitir_navegacion_libre' => isset($_POST['permitir_navegacion_libre']) && $_POST['permitir_navegacion_libre'] === '1',
    'permitir_interacciones' => isset($_POST['permitir_interacciones']) && $_POST['permitir_interacciones'] === '1',
    'usar_pdf' => isset($_POST['usar_pdf']) && $_POST['usar_pdf'] === '1',
    'habilitar_audio' => isset($_POST['habilitar_audio']) && $_POST['habilitar_audio'] === '1',
    'modo_asincrono' => isset($_POST['modo_asincrono']) && $_POST['modo_asincrono'] === '1',
    'un_solo_intento' => isset($_POST['un_solo_intento']) && $_POST['un_solo_intento'] === '1'
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
    $presentacion_data['configuracion']['permitir_anotaciones'] = $datos_actualizados['permitir_anotaciones'];
    $presentacion_data['configuracion']['exportar_con_anotaciones'] = $datos_actualizados['exportar_con_anotaciones'];
    $presentacion_data['configuracion']['permitir_notas'] = $datos_actualizados['permitir_notas'];
    $presentacion_data['configuracion']['permitir_marcadores'] = $datos_actualizados['permitir_marcadores'];
    $presentacion_data['configuracion']['permitir_navegacion_libre'] = $datos_actualizados['permitir_navegacion_libre'];
    $presentacion_data['configuracion']['permitir_interacciones'] = $datos_actualizados['permitir_interacciones'];

    // Configuraciones de audio (BETA)
    $presentacion_data['configuracion']['habilitar_audio'] = $datos_actualizados['habilitar_audio'];
    $presentacion_data['configuracion']['modo_asincrono'] = $datos_actualizados['modo_asincrono'];
    $presentacion_data['configuracion']['un_solo_intento'] = $datos_actualizados['un_solo_intento'];

    // Manejar datos del PDF (BETA)
    if ($datos_actualizados['usar_pdf']) {
        // Si se activó el PDF, verificar si hay datos nuevos
        if (isset($_POST['pdf_data'])) {
            $pdf_data = json_decode($_POST['pdf_data'], true);
            if ($pdf_data) {
                $presentacion_data['pdf_enabled'] = true;
                $presentacion_data['pdf_file'] = $pdf_data['name'];
                $presentacion_data['pdf_pages'] = $pdf_data['pages'];
                $presentacion_data['pdf_images'] = $pdf_data['images'];
                $presentacion_data['pdf_directory'] = $pdf_data['directory'];
                $presentacion_data['pdf_updated_at'] = $pdf_data['created_at'];
            }
        } elseif (!isset($presentacion_data['pdf_enabled'])) {
            // Si se activó pero no hay datos, marcar como pendiente
            $presentacion_data['pdf_enabled'] = true;
        }
    } else {
        // Si se desactivó el PDF, mantener los datos pero deshabilitar
        $presentacion_data['pdf_enabled'] = false;
    }

    // Manejar eliminación de PDF
    if (isset($_POST['remove_pdf']) && $_POST['remove_pdf'] === '1') {
        $presentacion_data['pdf_enabled'] = false;
        unset($presentacion_data['pdf_file']);
        unset($presentacion_data['pdf_pages']);
        unset($presentacion_data['pdf_images']);
        unset($presentacion_data['pdf_directory']);
        unset($presentacion_data['pdf_updated_at']);

        // Eliminar archivos físicos
        $pdf_dir = 'data/uploads/pdfs/' . $id_presentacion;
        if (file_exists($pdf_dir)) {
            $files = glob($pdf_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($pdf_dir);
        }
    }

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