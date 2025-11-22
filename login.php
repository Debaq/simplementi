<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Obtener ID de la presentación
$test_id = isset($_GET['test']) ? $_GET['test'] : '';

if (empty($test_id)) {
    echo "Error: No se especificó una presentación.";
    exit;
}

// Verificar si existe la presentación
$test_file = "data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Presentación no encontrada.";
    exit;
}

// Leer datos de la presentación
$test_json = file_get_contents($test_file);
$test_data = json_decode($test_json, true);

// Verificar si la presentación está protegida
if (!isset($test_data['protegido']) || !$test_data['protegido']) {
    // Si no está protegida, redirigir directamente
    header("Location: index.php?test=$test_id");
    exit;
}

$error_msg = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($password === $test_data['password']) {
        // Contraseña correcta, establecer sesión
        $_SESSION['auth_test'] = $test_id;
        
        // Redirigir a la presentación
        header("Location: index.php?test=$test_id");
        exit;
    } else {
        $error_msg = 'Contraseña incorrecta. Por favor, inténtelo nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Acceso a Presentación</title>
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
            <div class="card auth-card">
                <div class="auth-header text-white">
                    <h3 class="text-center mb-0">
                        <i class="fas fa-lock me-2"></i> Acceso Protegido
                    </h3>
                </div>
                <div class="auth-body">
                    <h5 class="mb-3 text-center"><?php echo htmlspecialchars($test_data['titulo']); ?></h5>
                    <p class="text-muted text-center mb-4">Esta presentación requiere contraseña</p>

                    <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_msg; ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3 auth-input-group">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-modern">
                                <i class="fas fa-sign-in-alt me-2"></i> Acceder
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>