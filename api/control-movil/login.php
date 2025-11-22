<?php
/**
 * API: Login para Control Móvil
 *
 * Autentica al usuario para acceder al control móvil
 */

session_start();

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../control-movil.php?error=invalid_request');
    exit;
}

// Obtener credenciales
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$redirect_to = $_POST['redirect_to'] ?? 'control-movil.php';

// Validar campos vacíos
if (empty($email) || empty($password)) {
    header('Location: ../../control-movil.php?error=empty_fields');
    exit;
}

// NOTA: SimpleMenti actualmente no tiene un sistema de usuarios con email/password
// Por ahora, vamos a usar una autenticación simple basada en códigos de presentación
// En un sistema real, aquí iría la validación contra una base de datos de usuarios

// Autenticación temporal: validar si el "email" es realmente un código de sesión válido
// y la "contraseña" es la contraseña de la presentación (si está protegida)

// Buscar presentaciones activas
$found = false;
$respuestasDir = __DIR__ . '/../../data/respuestas';

if (is_dir($respuestasDir)) {
    $presentaciones = scandir($respuestasDir);

    foreach ($presentaciones as $presentacion) {
        if ($presentacion === '.' || $presentacion === '..') {
            continue;
        }

        $presentacionDir = $respuestasDir . '/' . $presentacion;
        if (!is_dir($presentacionDir)) {
            continue;
        }

        // Buscar sesiones en esta presentación
        $archivos = scandir($presentacionDir);
        foreach ($archivos as $archivo) {
            if (strpos($archivo, 'sesion_') === 0 && str_ends_with($archivo, '.json')) {
                $sessionData = json_decode(file_get_contents($presentacionDir . '/' . $archivo), true);

                // Si el email coincide con el ID de sesión
                if ($sessionData && $sessionData['id_sesion'] === $email) {
                    // Verificar contraseña de la presentación si está protegida
                    $presentacionFile = __DIR__ . '/../../data/presentaciones/' . $presentacion . '.json';

                    if (file_exists($presentacionFile)) {
                        $presentacionData = json_decode(file_get_contents($presentacionFile), true);

                        // Si no está protegida o la contraseña coincide
                        if (!isset($presentacionData['protegido']) ||
                            !$presentacionData['protegido'] ||
                            (isset($presentacionData['password']) && $presentacionData['password'] === $password)) {

                            // Autenticación exitosa
                            $_SESSION['auth_test'] = $email;
                            $_SESSION['user_email'] = 'control_movil@' . $email;
                            $_SESSION['presentation_id'] = $presentacion;

                            $found = true;
                            break 2;
                        }
                    }
                }
            }
        }
    }
}

if ($found) {
    // Redirigir a la página original
    header('Location: ../../' . $redirect_to);
    exit;
} else {
    // Credenciales inválidas
    header('Location: ../../control-movil.php?error=invalid_credentials');
    exit;
}
