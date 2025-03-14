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

// Obtener datos del usuario actual
$usuario_actual = $_SESSION['admin_user'];
$nombre_actual = $_SESSION['admin_nombre'];
$rol_actual = $_SESSION['admin_rol'];

// Determinar qué sección mostrar (presentaciones, usuarios, logs)
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'presentaciones';

// Verificar si se ha enviado una acción
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if (!empty($accion)) {
    // Incluir el archivo de funciones de administración
    include('includes/admin/funciones.php');
    
    switch ($accion) {
        case 'eliminar_presentacion':
            $id_presentacion = isset($_GET['id']) ? $_GET['id'] : '';
            if (!empty($id_presentacion)) {
                $resultado = eliminarPresentacion($id_presentacion);
                if ($resultado['exito']) {
                    $mensaje = "Presentación eliminada correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al eliminar presentación: " . $resultado['mensaje'];
                    $tipo_mensaje = "danger";
                }
            }
            break;
        
        case 'bloquear_usuario':
            $username = isset($_GET['usuario']) ? $_GET['usuario'] : '';
            if (!empty($username)) {
                $resultado = cambiarEstadoUsuario($username, false);
                if ($resultado['exito']) {
                    $mensaje = "Usuario bloqueado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al bloquear usuario: " . $resultado['mensaje'];
                    $tipo_mensaje = "danger";
                }
            }
            break;
            
        case 'desbloquear_usuario':
            $username = isset($_GET['usuario']) ? $_GET['usuario'] : '';
            if (!empty($username)) {
                $resultado = cambiarEstadoUsuario($username, true);
                if ($resultado['exito']) {
                    $mensaje = "Usuario desbloqueado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al desbloquear usuario: " . $resultado['mensaje'];
                    $tipo_mensaje = "danger";
                }
            }
            break;
            
        case 'eliminar_usuario':
            $username = isset($_GET['usuario']) ? $_GET['usuario'] : '';
            if (!empty($username)) {
                $resultado = eliminarUsuario($username);
                if ($resultado['exito']) {
                    $mensaje = "Usuario eliminado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al eliminar usuario: " . $resultado['mensaje'];
                    $tipo_mensaje = "danger";
                }
            }
            break;
            
        case 'cerrar_sesion':
            // Cerrar sesión
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit;
            break;
    }
}

// Cargar datos según la sección activa
if ($seccion === 'presentaciones') {
    // Cargar presentaciones
    if (file_exists('data/index.json')) {
        $index_json = file_get_contents('data/index.json');
        $index_data = json_decode($index_json, true);
        $presentaciones = $index_data['presentaciones'];
    } else {
        $presentaciones = [];
    }
} elseif ($seccion === 'usuarios') {
    // Cargar usuarios administradores
    if (file_exists('data/admin_users.json')) {
        $admin_json = file_get_contents('data/admin_users.json');
        $admin_data = json_decode($admin_json, true);
        $usuarios = $admin_data['usuarios'];
    } else {
        $usuarios = [];
    }
} elseif ($seccion === 'logs') {
    // Cargar logs de administración
    if (file_exists('data/admin_logs.json')) {
        $logs_json = file_get_contents('data/admin_logs.json');
        $logs_data = json_decode($logs_json, true);
        $logs = $logs_data['logs'];
        // Ordenar logs del más reciente al más antiguo
        usort($logs, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
    } else {
        $logs = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .bg-gradient-success {
            background: linear-gradient(to right, #1cc88a, #169b6b);
        }
        .bg-gradient-warning {
            background: linear-gradient(to right, #f6c23e, #dda20a);
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
        }
        .sidebar .nav-item .nav-link {
            display: block;
            width: 100%;
            padding: 0.8rem 1.25rem;
            color: #3a3b45;
        }
        .sidebar .nav-item .nav-link:hover {
            background-color: #f8f9fa;
        }
        .sidebar .nav-item .nav-link.active {
            color: #4e73df;
            font-weight: bold;
        }
        .sidebar .nav-item .nav-link i {
            margin-right: 0.5rem;
        }
        .content-wrapper {
            margin-left: 0;
            padding: 1.5rem;
        }
        .card-header {
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .text-xs {
            font-size: 0.75rem;
        }
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
                position: fixed;
                top: 56px;
                bottom: 0;
                left: 0;
                z-index: 1000;
            }
            .content-wrapper {
                margin-left: 250px;
            }
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($nombre_actual); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i> Ir a inicio</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?accion=cerrar_sesion"><i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-white sidebar d-none d-md-block">
            <div class="sidebar-heading text-muted">
                Administración
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $seccion === 'presentaciones' ? 'active' : ''; ?>" href="?seccion=presentaciones">
                        <i class="fas fa-fw fa-chalkboard"></i> Presentaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $seccion === 'usuarios' ? 'active' : ''; ?>" href="?seccion=usuarios">
                        <i class="fas fa-fw fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $seccion === 'logs' ? 'active' : ''; ?>" href="?seccion=logs">
                        <i class="fas fa-fw fa-list"></i> Registro de actividad
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crear_presentacion.php">
                        <i class="fas fa-fw fa-plus-circle"></i> Nueva presentación
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crear_usuario.php">
                        <i class="fas fa-fw fa-user-plus"></i> Nuevo usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="?accion=cerrar_sesion">
                        <i class="fas fa-fw fa-sign-out-alt"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content area -->
        <div class="content-wrapper">
            <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show mb-4">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($seccion === 'presentaciones'): ?>
            <!-- Presentaciones -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestión de Presentaciones</h1>
                <a href="crear_presentacion.php" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
                    <i class="fas fa-plus-circle fa-sm text-white-50 me-1"></i> Nueva Presentación
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Presentaciones disponibles</h6>
                    <span class="badge bg-primary"><?php echo count($presentaciones); ?> presentaciones</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Autor</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($presentaciones)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay presentaciones disponibles.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($presentaciones as $presentacion): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($presentacion['id']); ?></code></td>
                                    <td><?php echo htmlspecialchars($presentacion['titulo']); ?></td>
                                    <td class="text-truncate"><?php echo htmlspecialchars($presentacion['descripcion']); ?></td>
                                    <td><?php echo htmlspecialchars($presentacion['autor']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($presentacion['fecha_creacion'])); ?></td>
                                    <td>
                                        <?php if ($presentacion['protegido']): ?>
                                        <span class="badge bg-warning text-dark">Protegida</span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Pública</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar_presentacion.php?id=<?php echo urlencode($presentacion['id']); ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?seccion=presentaciones&accion=eliminar_presentacion&id=<?php echo urlencode($presentacion['id']); ?>" 
                                               class="btn btn-danger" title="Eliminar"
                                               onclick="return confirm('¿Está seguro de eliminar esta presentación? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="index.php?test=<?php echo urlencode($presentacion['id']); ?>" 
                                               class="btn btn-success" title="Ver presentación" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php elseif ($seccion === 'usuarios'): ?>
            <!-- Usuarios -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
                <a href="crear_usuario.php" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
                    <i class="fas fa-user-plus fa-sm text-white-50 me-1"></i> Nuevo Usuario
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Usuarios registrados</h6>
                    <span class="badge bg-primary"><?php echo count($usuarios); ?> usuarios</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha de creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($usuario['usuario']); ?></code></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td>
                                        <?php if ($usuario['rol'] === 'admin'): ?>
                                        <span class="badge bg-danger">Administrador</span>
                                        <?php else: ?>
                                        <span class="badge bg-info">Editor</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Bloqueado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar_usuario.php?usuario=<?php echo urlencode($usuario['usuario']); ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($usuario['usuario'] !== $usuario_actual): ?>
                                            <?php if ($usuario['activo']): ?>
                                            <a href="?seccion=usuarios&accion=bloquear_usuario&usuario=<?php echo urlencode($usuario['usuario']); ?>" 
                                               class="btn btn-warning" title="Bloquear"
                                               onclick="return confirm('¿Está seguro de bloquear este usuario?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="?seccion=usuarios&accion=desbloquear_usuario&usuario=<?php echo urlencode($usuario['usuario']); ?>" 
                                               class="btn btn-success" title="Desbloquear"
                                               onclick="return confirm('¿Está seguro de desbloquear este usuario?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <a href="?seccion=usuarios&accion=eliminar_usuario&usuario=<?php echo urlencode($usuario['usuario']); ?>" 
                                               class="btn btn-danger" title="Eliminar"
                                               onclick="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php elseif ($seccion === 'logs'): ?>
            <!-- Logs de actividad -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Registro de Actividad</h1>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Registro de acciones administrativas</h6>
                    <span class="badge bg-primary"><?php echo count($logs); ?> registros</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay registros de actividad.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($log['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['usuario']); ?></td>
                                    <td>
                                        <?php 
                                        switch($log['accion']) {
                                            case 'login':
                                                echo '<span class="badge bg-success">Inicio de sesión</span>';
                                                break;
                                            case 'logout':
                                                echo '<span class="badge bg-secondary">Cierre de sesión</span>';
                                                break;
                                            case 'crear_presentacion':
                                                echo '<span class="badge bg-primary">Crear presentación</span>';
                                                break;
                                            case 'editar_presentacion':
                                                echo '<span class="badge bg-info">Editar presentación</span>';
                                                break;
                                            case 'eliminar_presentacion':
                                                echo '<span class="badge bg-danger">Eliminar presentación</span>';
                                                break;
                                            case 'crear_usuario':
                                                echo '<span class="badge bg-primary">Crear usuario</span>';
                                                break;
                                            case 'editar_usuario':
                                                echo '<span class="badge bg-info">Editar usuario</span>';
                                                break;
                                            case 'bloquear_usuario':
                                                echo '<span class="badge bg-warning text-dark">Bloquear usuario</span>';
                                                break;
                                            case 'desbloquear_usuario':
                                                echo '<span class="badge bg-success">Desbloquear usuario</span>';
                                                break;
                                            case 'eliminar_usuario':
                                                echo '<span class="badge bg-danger">Eliminar usuario</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-dark">' . htmlspecialchars($log['accion']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>