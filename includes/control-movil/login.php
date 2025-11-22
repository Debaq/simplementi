<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SimpleMenti - Control Móvil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/control-movil.css">
</head>
<body class="bg-gradient-primary">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-lg" style="max-width: 400px; width: 100%;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">SimpleMenti Control</h4>
                    <p class="text-muted small">Controla tu presentación desde tu móvil</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php
                    switch($_GET['error']) {
                        case 'invalid_credentials':
                            echo 'Email o contraseña incorrectos';
                            break;
                        case 'empty_fields':
                            echo 'Por favor completa todos los campos';
                            break;
                        default:
                            echo 'Error al iniciar sesión';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form action="api/control-movil/login.php" method="POST">
                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i> Email
                        </label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                               placeholder="tu@email.com" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i> Contraseña
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                               placeholder="••••••••" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Volver a inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
