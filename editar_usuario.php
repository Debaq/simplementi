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

// Verificar que se proporcionó un nombre de usuario
$username = isset($_GET['usuario']) ? $_GET['usuario'] : '';
if (empty($username)) {
    // Redirigir si no se proporcionó un nombre de usuario
    header("Location: admin_panel.php?seccion=usuarios");
    exit;
}

// Verificar si existe el archivo de usuarios
$admin_file = "data/admin_users.json";
if (!file_exists($admin_file)) {
    echo "Error: Archivo de usuarios no encontrado.";
    exit;
}

// Leer datos de usuarios
$admin_json = file_get_contents($admin_file);
$admin_data = json_decode($admin_json, true);

// Buscar el usuario por nombre
$usuario_encontrado = false;
$datos_usuario = null;

foreach ($admin_data['usuarios'] as $usuario) {
    if ($usuario['usuario'] === $username) {
        $usuario_encontrado = true;
        $datos_usuario = $usuario;
        break;
    }
}

if (!$usuario_encontrado) {
    echo "Error: Usuario no encontrado.";
    exit;
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datos_actualizados = [
        'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
        'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
        'password' => isset($_POST['password']) ? $_POST['password'] : '',
        'rol' => isset($_POST['rol']) ? $_POST['rol'] : 'editor'
    ];
    
    // Validar campos básicos
    $errores = [];
    
    if (empty($datos_actualizados['nombre'])) {
        $errores[] = 'El nombre completo es obligatorio';
    }
    
    if (empty($datos_actualizados['email'])) {
        $errores[] = 'El correo electrónico es obligatorio';
    } elseif (!filter_var($datos_actualizados['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no tiene un formato válido';
    }
    
    if (!empty($datos_actualizados['password']) && strlen($datos_actualizados['password']) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($errores)) {
        // Actualizar usuario
        $resultado = actualizarUsuario($username, $datos_actualizados);
        
        if ($resultado['exito']) {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'success';
            
            // Actualizar datos locales para mostrar los cambios
            $datos_usuario['nombre'] = $datos_actualizados['nombre'];
            $datos_usuario['email'] = $datos_actualizados['email'];
            
            // Si es el usuario actual, actualizar la sesión
            if ($username === $_SESSION['admin_user']) {
                $_SESSION['admin_nombre'] = $datos_actualizados['nombre'];
            }
            
            // Actualizar rol solo si no es el usuario actual
            if ($username !== $_SESSION['admin_user']) {
                $datos_usuario['rol'] = $datos_actualizados['rol'];
            }
        } else {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'danger';
        }
    } else {
        $mensaje = 'Por favor, corrija los siguientes errores:<ul>';
        foreach ($errores as $error) {
            $mensaje .= '<li>' . $error . '</li>';
        }
        $mensaje .= '</ul>';
        $tipo_mensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard me-2"></i>
                SimpleMenti
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Panel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php?seccion=presentaciones">
                            <i class="fas fa-chalkboard me-1"></i> Presentaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_panel.php?seccion=usuarios">
                            <i class="fas fa-users me-1"></i> Usuarios
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_panel.php?accion=cerrar_sesion">
                            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4 form-container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Editar Usuario</h1>
            <a href="admin_panel.php?seccion=usuarios" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a usuarios
            </a>
        </div>
        
        <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show mb-4">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Información del usuario</h6>
                <span class="badge <?php echo $datos_usuario['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                    <?php echo $datos_usuario['activo'] ? 'Activo' : 'Bloqueado'; ?>
                </span>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuario" class="form-label">Nombre de usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                       value="<?php echo htmlspecialchars($datos_usuario['usuario']); ?>" readonly>
                            </div>
                            <div class="form-text">El nombre de usuario no se puede cambiar.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_creacion" class="form-label">Fecha de creación</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="text" class="form-control" id="fecha_creacion" name="fecha_creacion" 
                                       value="<?php echo date('d/m/Y H:i', strtotime($datos_usuario['fecha_creacion'])); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Deje en blanco para mantener la contraseña actual. Mínimo 6 caracteres.</div>
                    </div>
                    
                    <?php if ($username !== $_SESSION['admin_user']): ?>
                    <div class="mb-4">
                        <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="editor" <?php echo $datos_usuario['rol'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="admin" <?php echo $datos_usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                        <div class="form-text">
                            <strong>Editor:</strong> Puede crear y gestionar presentaciones.<br>
                            <strong>Administrador:</strong> Acceso completo al sistema, incluyendo gestión de usuarios.
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No puede cambiar su propio rol.
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="admin_panel.php?seccion=usuarios" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para mostrar/ocultar contraseña
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>