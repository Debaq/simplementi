<?php
/**
 * Funciones para el panel de administración
 */

// Registrar acción en el log de administración
function registrarAccion($usuario, $accion) {
    $log_file = "data/admin_logs.json";
    
    if (!file_exists($log_file)) {
        $log_data = [
            "logs" => []
        ];
    } else {
        $log_json = file_get_contents($log_file);
        $log_data = json_decode($log_json, true);
        
        if (!isset($log_data['logs'])) {
            $log_data['logs'] = [];
        }
    }
    
    $log_data['logs'][] = [
        "usuario" => $usuario,
        "accion" => $accion,
        "fecha" => date('Y-m-d\TH:i:s'),
        "ip" => $_SERVER['REMOTE_ADDR']
    ];
    
    return file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));
}

// Eliminar una presentación
function eliminarPresentacion($id_presentacion) {
    // Verificar que el ID no esté vacío
    if (empty($id_presentacion)) {
        return [
            'exito' => false,
            'mensaje' => 'ID de presentación no válido'
        ];
    }
    
    // Verificar si existe el índice
    if (!file_exists('data/index.json')) {
        return [
            'exito' => false,
            'mensaje' => 'Archivo de índice no encontrado'
        ];
    }
    
    // Leer el índice
    $index_json = file_get_contents('data/index.json');
    $index_data = json_decode($index_json, true);
    
    // Buscar la presentación por ID
    $presentacion_encontrada = false;
    $nuevo_indice = [
        'presentaciones' => []
    ];
    
    foreach ($index_data['presentaciones'] as $presentacion) {
        if ($presentacion['id'] === $id_presentacion) {
            $presentacion_encontrada = true;
        } else {
            $nuevo_indice['presentaciones'][] = $presentacion;
        }
    }
    
    if (!$presentacion_encontrada) {
        return [
            'exito' => false,
            'mensaje' => 'Presentación no encontrada'
        ];
    }
    
    // Eliminar el archivo de la presentación
    $presentacion_file = "data/presentaciones/{$id_presentacion}.json";
    if (file_exists($presentacion_file)) {
        unlink($presentacion_file);
    }
    
    // Eliminar directorio de respuestas si existe
    $respuestas_dir = "data/respuestas/{$id_presentacion}";
    if (file_exists($respuestas_dir) && is_dir($respuestas_dir)) {
        // Eliminar archivos dentro del directorio
        $archivos = glob("{$respuestas_dir}/*");
        foreach ($archivos as $archivo) {
            unlink($archivo);
        }
        // Eliminar el directorio
        rmdir($respuestas_dir);
    }
    
    // Actualizar el índice
    $result = file_put_contents('data/index.json', json_encode($nuevo_indice, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        return [
            'exito' => false,
            'mensaje' => 'Error al actualizar el índice'
        ];
    }
    
    // Registrar la acción
    registrarAccion($_SESSION['admin_user'], 'eliminar_presentacion');
    
    return [
        'exito' => true,
        'mensaje' => 'Presentación eliminada correctamente'
    ];
}

// Cambiar estado de un usuario (activar/desactivar)
function cambiarEstadoUsuario($username, $activar = true) {
    // Verificar que el usuario no esté vacío
    if (empty($username)) {
        return [
            'exito' => false,
            'mensaje' => 'Nombre de usuario no válido'
        ];
    }
    
    // Verificar si existe el archivo de usuarios
    if (!file_exists('data/admin_users.json')) {
        return [
            'exito' => false,
            'mensaje' => 'Archivo de usuarios no encontrado'
        ];
    }
    
    // Leer los usuarios
    $admin_json = file_get_contents('data/admin_users.json');
    $admin_data = json_decode($admin_json, true);
    
    // Buscar el usuario por nombre
    $usuario_encontrado = false;
    
    foreach ($admin_data['usuarios'] as &$usuario) {
        if ($usuario['usuario'] === $username) {
            $usuario_encontrado = true;
            // No permitir bloquear al usuario actual
            if ($username === $_SESSION['admin_user']) {
                return [
                    'exito' => false,
                    'mensaje' => 'No puede cambiar el estado de su propio usuario'
                ];
            }
            $usuario['activo'] = $activar;
            break;
        }
    }
    
    if (!$usuario_encontrado) {
        return [
            'exito' => false,
            'mensaje' => 'Usuario no encontrado'
        ];
    }
    
    // Actualizar el archivo de usuarios
    $result = file_put_contents('data/admin_users.json', json_encode($admin_data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        return [
            'exito' => false,
            'mensaje' => 'Error al actualizar el archivo de usuarios'
        ];
    }
    
    // Registrar la acción
    registrarAccion($_SESSION['admin_user'], $activar ? 'desbloquear_usuario' : 'bloquear_usuario');
    
    return [
        'exito' => true,
        'mensaje' => 'Estado de usuario actualizado correctamente'
    ];
}

// Eliminar un usuario
function eliminarUsuario($username) {
    // Verificar que el usuario no esté vacío
    if (empty($username)) {
        return [
            'exito' => false,
            'mensaje' => 'Nombre de usuario no válido'
        ];
    }
    
    // Verificar si existe el archivo de usuarios
    if (!file_exists('data/admin_users.json')) {
        return [
            'exito' => false,
            'mensaje' => 'Archivo de usuarios no encontrado'
        ];
    }
    
    // Leer los usuarios
    $admin_json = file_get_contents('data/admin_users.json');
    $admin_data = json_decode($admin_json, true);
    
    // No permitir eliminar al usuario actual
    if ($username === $_SESSION['admin_user']) {
        return [
            'exito' => false,
            'mensaje' => 'No puede eliminar su propio usuario'
        ];
    }
    
    // Buscar el usuario por nombre
    $usuario_encontrado = false;
    $nuevos_usuarios = [
        'usuarios' => []
    ];
    
    foreach ($admin_data['usuarios'] as $usuario) {
        if ($usuario['usuario'] === $username) {
            $usuario_encontrado = true;
        } else {
            $nuevos_usuarios['usuarios'][] = $usuario;
        }
    }
    
    if (!$usuario_encontrado) {
        return [
            'exito' => false,
            'mensaje' => 'Usuario no encontrado'
        ];
    }
    
    // Asegurarnos de que queda al menos un usuario administrador
    $tiene_admin = false;
    foreach ($nuevos_usuarios['usuarios'] as $usuario) {
        if ($usuario['rol'] === 'admin' && $usuario['activo']) {
            $tiene_admin = true;
            break;
        }
    }
    
    if (!$tiene_admin) {
        return [
            'exito' => false,
            'mensaje' => 'No se puede eliminar el último administrador activo'
        ];
    }
    
    // Actualizar el archivo de usuarios
    $result = file_put_contents('data/admin_users.json', json_encode($nuevos_usuarios, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        return [
            'exito' => false,
            'mensaje' => 'Error al actualizar el archivo de usuarios'
        ];
    }
    
    // Registrar la acción
    registrarAccion($_SESSION['admin_user'], 'eliminar_usuario');
    
    return [
        'exito' => true,
        'mensaje' => 'Usuario eliminado correctamente'
    ];
}

// Función para crear un nuevo usuario
function crearUsuario($datos) {
    // Verificar campos obligatorios
    if (empty($datos['usuario']) || empty($datos['password']) || empty($datos['nombre']) || empty($datos['email'])) {
        return [
            'exito' => false,
            'mensaje' => 'Todos los campos son obligatorios'
        ];
    }
    
    // Verificar si existe el archivo de usuarios
    if (!file_exists('data/admin_users.json')) {
        // Crear archivo nuevo si no existe
        $admin_data = [
            'usuarios' => []
        ];
    } else {
        // Leer usuarios existentes
        $admin_json = file_get_contents('data/admin_users.json');
        $admin_data = json_decode($admin_json, true);
    }
    
    // Verificar si el usuario ya existe
    foreach ($admin_data['usuarios'] as $usuario) {
        if ($usuario['usuario'] === $datos['usuario']) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre de usuario ya está en uso'
            ];
        }
        
        if ($usuario['email'] === $datos['email']) {
            return [
                'exito' => false,
                'mensaje' => 'El correo electrónico ya está en uso'
            ];
        }
    }
    
    // Crear nuevo usuario
    $nuevo_usuario = [
        'usuario' => $datos['usuario'],
        'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'rol' => $datos['rol'] ?? 'editor',
        'activo' => true,
        'fecha_creacion' => date('Y-m-d\TH:i:s')
    ];
    
    // Agregar a la lista
    $admin_data['usuarios'][] = $nuevo_usuario;
    
    // Guardar cambios
    $result = file_put_contents('data/admin_users.json', json_encode($admin_data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        return [
            'exito' => false,
            'mensaje' => 'Error al guardar el nuevo usuario'
        ];
    }
    
    // Registrar la acción
    registrarAccion($_SESSION['admin_user'], 'crear_usuario');
    
    return [
        'exito' => true,
        'mensaje' => 'Usuario creado correctamente'
    ];
}

// Actualizar un usuario existente
function actualizarUsuario($username, $datos) {
    // Verificar campos obligatorios
    if (empty($username) || empty($datos['nombre']) || empty($datos['email'])) {
        return [
            'exito' => false,
            'mensaje' => 'Todos los campos son obligatorios'
        ];
    }
    
    // Verificar si existe el archivo de usuarios
    if (!file_exists('data/admin_users.json')) {
        return [
            'exito' => false,
            'mensaje' => 'Archivo de usuarios no encontrado'
        ];
    }
    
    // Leer usuarios existentes
    $admin_json = file_get_contents('data/admin_users.json');
    $admin_data = json_decode($admin_json, true);
    
    // Buscar usuario por nombre
    $usuario_encontrado = false;
    $email_ocupado = false;
    
    foreach ($admin_data['usuarios'] as &$usuario) {
        // Verificar si el email ya está en uso por otro usuario
        if ($usuario['email'] === $datos['email'] && $usuario['usuario'] !== $username) {
            $email_ocupado = true;
            break;
        }
        
        if ($usuario['usuario'] === $username) {
            $usuario_encontrado = true;
            
            // Actualizar datos
            $usuario['nombre'] = $datos['nombre'];
            $usuario['email'] = $datos['email'];
            
            // Actualizar rol si no es el usuario actual
            if ($username !== $_SESSION['admin_user'] && isset($datos['rol'])) {
                $usuario['rol'] = $datos['rol'];
            }
            
            // Actualizar contraseña si se proporciona una nueva
            if (!empty($datos['password'])) {
                $usuario['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            
            break;
        }
    }
    
    if ($email_ocupado) {
        return [
            'exito' => false,
            'mensaje' => 'El correo electrónico ya está en uso por otro usuario'
        ];
    }
    
    if (!$usuario_encontrado) {
        return [
            'exito' => false,
            'mensaje' => 'Usuario no encontrado'
        ];
    }
    
    // Guardar cambios
    $result = file_put_contents('data/admin_users.json', json_encode($admin_data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        return [
            'exito' => false,
            'mensaje' => 'Error al actualizar el usuario'
        ];
    }
    
    // Registrar la acción
    registrarAccion($_SESSION['admin_user'], 'editar_usuario');
    
    return [
        'exito' => true,
        'mensaje' => 'Usuario actualizado correctamente'
    ];
}