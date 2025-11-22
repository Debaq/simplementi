<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si ya está autenticado como administrador
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    // Redirigir al panel de administración
    header("Location: admin_panel.php");
    exit;
}

// Verificar el archivo de usuarios administradores
$admin_file = "data/admin_users.json";

// Crear el archivo de usuarios administradores si no existe
if (!file_exists($admin_file)) {
    // Verificar que exista el directorio data
    if (!file_exists('data')) {
        mkdir('data', 0755, true);
    }
    
    // Crear usuario administrador por defecto
    $admin_data = [
        "usuarios" => [
            [
                "usuario" => "admin",
                "password" => password_hash("admin123", PASSWORD_DEFAULT),
                "nombre" => "Administrador",
                "email" => "admin@simplementi.local",
                "rol" => "admin",
                "activo" => true,
                "fecha_creacion" => date('Y-m-d\TH:i:s')
            ]
        ]
    ];
    
    file_put_contents($admin_file, json_encode($admin_data, JSON_PRETTY_PRINT));
}

$error_msg = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Leer usuarios administradores
    $admin_json = file_get_contents($admin_file);
    $admin_data = json_decode($admin_json, true);
    
    // Buscar el usuario
    $usuario_encontrado = null;
    foreach ($admin_data['usuarios'] as $usuario) {
        if ($usuario['usuario'] === $username) {
            $usuario_encontrado = $usuario;
            break;
        }
    }
    
    // Verificar credenciales
    if ($usuario_encontrado && $usuario_encontrado['activo'] && password_verify($password, $usuario_encontrado['password'])) {
        // Credenciales correctas, establecer sesión
        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_user'] = $usuario_encontrado['usuario'];
        $_SESSION['admin_nombre'] = $usuario_encontrado['nombre'];
        $_SESSION['admin_rol'] = $usuario_encontrado['rol'];
        
        // Registrar el inicio de sesión
        $log_file = "data/admin_logs.json";
        if (!file_exists($log_file)) {
            $log_data = [
                "logs" => []
            ];
        } else {
            $log_json = file_get_contents($log_file);
            $log_data = json_decode($log_json, true);
        }
        
        $log_data['logs'][] = [
            "usuario" => $usuario_encontrado['usuario'],
            "accion" => "login",
            "fecha" => date('Y-m-d\TH:i:s'),
            "ip" => $_SERVER['REMOTE_ADDR']
        ];
        
        file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT));
        
        // Redirigir al panel de administración
        header("Location: admin_panel.php");
        exit;
    } else {
        $error_msg = 'Usuario o contraseña incorrectos. Por favor, inténtelo nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Acceso Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h2 style="color: var(--primary-blue);">SimpleMenti</h2>
                <p class="text-muted">Sistema interactivo para presentaciones</p>
            </div>

            <div class="card auth-card">
                <div class="auth-header text-white">
                    <h3 class="text-center mb-0">
                        <i class="fas fa-user-shield me-2"></i> Acceso Administrativo
                    </h3>
                </div>
                <div class="auth-body">
                    <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_msg; ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3 auth-input-group">
                            <label for="username" class="form-label">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4 auth-input-group">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-modern">
                                <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
                <div class="auth-footer text-center">
                    <small class="text-muted">
                        Usuario predeterminado: admin | Contraseña: admin123
                    </small>
                </div>
            </div>
        </div>
    </div>

    <footer class="page-footer text-center">
        <p class="mb-0">SimpleMenti &copy; <?php echo date('Y'); ?> - tmeduca.org</p>
    </footer>
    
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