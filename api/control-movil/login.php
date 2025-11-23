<?php
/**
 * API: Login para Control Móvil
 *
 * Autentica al usuario mediante username o email
 */

session_start();

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../control-movil.php?error=invalid_request');
    exit;
}

// Obtener credenciales
$username_or_email = trim($_POST['email'] ?? ''); // El campo se llama 'email' pero acepta ambos
$password = $_POST['password'] ?? '';
$redirect_to = $_POST['redirect_to'] ?? 'control-movil.php';

// Validar campos vacíos
if (empty($username_or_email) || empty($password)) {
    header('Location: ../../control-movil.php?error=empty_fields');
    exit;
}

// Cargar usuarios
$users_file = __DIR__ . '/../../data/admin_users.json';

if (!file_exists($users_file)) {
    header('Location: ../../control-movil.php?error=no_users_file');
    exit;
}

$users_data = json_decode(file_get_contents($users_file), true);

if (!isset($users_data['usuarios']) || empty($users_data['usuarios'])) {
    header('Location: ../../control-movil.php?error=no_users');
    exit;
}

// Buscar usuario por username O email
$user_found = null;

foreach ($users_data['usuarios'] as $user) {
    // Comparar con username o email (case-insensitive)
    if (strcasecmp($user['usuario'], $username_or_email) === 0 ||
        strcasecmp($user['email'], $username_or_email) === 0) {
        $user_found = $user;
        break;
    }
}

// Si no se encontró el usuario
if (!$user_found) {
    header('Location: ../../control-movil.php?error=invalid_credentials');
    exit;
}

// Verificar contraseña
// Nota: Asumiendo que las contraseñas están hasheadas con password_hash
if (isset($user_found['password'])) {
    // Si la contraseña está hasheada
    if (password_verify($password, $user_found['password'])) {
        $password_valid = true;
    }
    // Fallback: comparación directa (para compatibilidad con contraseñas no hasheadas)
    elseif ($user_found['password'] === $password) {
        $password_valid = true;
    } else {
        $password_valid = false;
    }
} else {
    $password_valid = false;
}

// Si la contraseña no es válida
if (!$password_valid) {
    header('Location: ../../control-movil.php?error=invalid_credentials');
    exit;
}

// Autenticación exitosa - Guardar en sesión
$_SESSION['auth_test'] = $user_found['usuario'];
$_SESSION['user_id'] = $user_found['id'] ?? $user_found['usuario'];
$_SESSION['user_email'] = $user_found['email'];
$_SESSION['user_name'] = $user_found['nombre'] ?? $user_found['usuario'];
$_SESSION['user_role'] = $user_found['rol'] ?? 'editor';

// Redirigir a la página original
// Si $redirect_to es una ruta absoluta (empieza con /), usarla directamente
// Si es relativa, agregar el prefijo ../../
if (strpos($redirect_to, '/') === 0) {
    // Ruta absoluta
    header('Location: ' . $redirect_to);
} else {
    // Ruta relativa
    header('Location: ../../' . $redirect_to);
}
exit;
