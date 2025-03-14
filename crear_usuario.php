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

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$datos_usuario = [
    'usuario' => '',
    'nombre' => '',
    'email' => '',
    'rol' => 'editor'
];

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datos_usuario = [
        'usuario' => isset($_POST['usuario']) ? trim($_POST['usuario']) : '',
        'password' => isset($_POST['password']) ? $_POST['password'] : '',
        'nombre' => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
        'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
        'rol' => isset($_POST['rol']) ? $_POST['rol'] : 'editor'
    ];
    
    // Validar campos básicos
    $errores = [];
    
    if (empty($datos_usuario['usuario'])) {
        $errores[] = 'El nombre de usuario es obligatorio';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $datos_usuario['usuario'])) {
        $errores[] = 'El nombre de usuario debe contener entre 3 y 20 caracteres alfanuméricos o guiones bajos';
    }
    
    if (empty($datos_usuario['password'])) {
        $errores[] = 'La contraseña es obligatoria';
    } elseif (strlen($datos_usuario['password']) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($datos_usuario['nombre'])) {
        $errores[] = 'El nombre completo es obligatorio';
    }
    
    if (empty($datos_usuario['email'])) {
        $errores[] = 'El correo electrónico es obligatorio';
    } elseif (!filter_var($datos_usuario['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no tiene un formato válido';
    }
    
    if (empty($errores)) {
        // Crear usuario
        $resultado = crearUsuario($datos_usuario);
        
        if ($resultado['exito']) {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'success';
            
            // Limpiar formulario tras éxito
            $datos_usuario = [
                'usuario' => '',
                'nombre' => '',
                'email' => '',
                'rol' => 'editor'
            ];
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
    <title>SimpleMenti - Crear Usuario</title>
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
            <h1 class="h3 mb-0 text-gray-800">Crear Nuevo Usuario</h1>
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
        
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos del nuevo usuario</h6>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuario" class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                       value="<?php echo htmlspecialchars($datos_usuario['usuario']); ?>" required
                                       pattern="[a-zA-Z0-9_]{3,20}">
                            </div>
                            <div class="form-text">Entre 3 y 20 caracteres alfanuméricos o guiones bajos.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required
                                       minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 6 caracteres.</div>
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
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo me-1"></i> Restablecer
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar usuario
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