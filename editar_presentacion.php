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

// Verificar que se proporcionó un ID de presentación
$id_presentacion = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($id_presentacion)) {
    // Redirigir si no se proporcionó un ID
    header("Location: admin_panel.php?seccion=presentaciones");
    exit;
}

// Verificar si es una nueva presentación
$es_nueva = isset($_GET['nuevo']) && $_GET['nuevo'] == '1';

// Verificar si existe la presentación
$presentacion_file = "data/presentaciones/$id_presentacion.json";
if (!file_exists($presentacion_file)) {
    echo "Error: Presentación no encontrada.";
    exit;
}

// Leer datos de la presentación
$presentacion_json = file_get_contents($presentacion_file);
$presentacion_data = json_decode($presentacion_json, true);

if ($presentacion_data === null) {
    echo "Error: El archivo de la presentación no tiene un formato JSON válido.";
    exit;
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$pregunta_id = isset($_GET['pregunta_id']) ? (int)$_GET['pregunta_id'] : 0;

// Procesar acciones
if (!empty($accion)) {
    switch ($accion) {
        case 'eliminar_pregunta':
            if ($pregunta_id > 0) {
                // Buscar la pregunta en el array
                $pregunta_encontrada = false;
                $nuevas_preguntas = [];
                
                foreach ($presentacion_data['preguntas'] as $pregunta) {
                    if ($pregunta['id'] === $pregunta_id) {
                        $pregunta_encontrada = true;
                    } else {
                        $nuevas_preguntas[] = $pregunta;
                    }
                }
                
                if ($pregunta_encontrada) {
                    // Actualizar array de preguntas
                    $presentacion_data['preguntas'] = $nuevas_preguntas;
                    
                    // Guardar cambios
                    file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
                    
                    // Actualizar número de preguntas en el índice
                    actualizarNumeroPreguntas($id_presentacion, count($nuevas_preguntas));
                    
                    $mensaje = "Pregunta eliminada correctamente.";
                    $tipo_mensaje = "success";
                    
                    // Registrar la acción
                    registrarAccion($_SESSION['admin_user'], 'eliminar_pregunta');
                } else {
                    $mensaje = "La pregunta no fue encontrada.";
                    $tipo_mensaje = "danger";
                }
            }
            break;
            
        case 'mover_arriba':
        case 'mover_abajo':
            if ($pregunta_id > 0) {
                $indice_pregunta = -1;
                foreach ($presentacion_data['preguntas'] as $key => $pregunta) {
                    if ($pregunta['id'] === $pregunta_id) {
                        $indice_pregunta = $key;
                        break;
                    }
                }

                if ($indice_pregunta !== -1) {
                    $total_preguntas = count($presentacion_data['preguntas']);
                    $pregunta_a_mover = $presentacion_data['preguntas'][$indice_pregunta];
                    $movimiento_realizado = false;

                    if ($accion === 'mover_arriba' && $indice_pregunta > 0) {
                        // Mover la pregunta hacia arriba
                        array_splice($presentacion_data['preguntas'], $indice_pregunta, 1);
                        array_splice($presentacion_data['preguntas'], $indice_pregunta - 1, 0, [$pregunta_a_mover]);
                        $movimiento_realizado = true;
                    } elseif ($accion === 'mover_abajo' && $indice_pregunta < $total_preguntas - 1) {
                        // Mover la pregunta hacia abajo
                        array_splice($presentacion_data['preguntas'], $indice_pregunta, 1);
                        array_splice($presentacion_data['preguntas'], $indice_pregunta + 1, 0, [$pregunta_a_mover]);
                        $movimiento_realizado = true;
                    }

                    if ($movimiento_realizado) {
                        // Guardar cambios
                        file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
                        $mensaje = "Pregunta reordenada correctamente.";
                        $tipo_mensaje = "success";
                        registrarAccion($_SESSION['admin_user'], 'reordenar_pregunta');
                    }
                } else {
                    $mensaje = "La pregunta no fue encontrada.";
                    $tipo_mensaje = "danger";
                }
            }
            break;

        // Acciones para la secuencia PDF
        case 'mover_seq_arriba':
        case 'mover_seq_abajo':
            $seq_index = isset($_GET['seq_index']) ? (int)$_GET['seq_index'] : -1;

            if ($seq_index >= 0 && isset($presentacion_data['pdf_sequence'])) {
                $total_items = count($presentacion_data['pdf_sequence']);
                $item_a_mover = $presentacion_data['pdf_sequence'][$seq_index];
                $movimiento_realizado = false;

                if ($accion === 'mover_seq_arriba' && $seq_index > 0) {
                    // Mover elemento hacia arriba
                    array_splice($presentacion_data['pdf_sequence'], $seq_index, 1);
                    array_splice($presentacion_data['pdf_sequence'], $seq_index - 1, 0, [$item_a_mover]);
                    $movimiento_realizado = true;
                } elseif ($accion === 'mover_seq_abajo' && $seq_index < $total_items - 1) {
                    // Mover elemento hacia abajo
                    array_splice($presentacion_data['pdf_sequence'], $seq_index, 1);
                    array_splice($presentacion_data['pdf_sequence'], $seq_index + 1, 0, [$item_a_mover]);
                    $movimiento_realizado = true;
                }

                if ($movimiento_realizado) {
                    // Guardar cambios
                    file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
                    $mensaje = "Secuencia reordenada correctamente.";
                    $tipo_mensaje = "success";
                    registrarAccion($_SESSION['admin_user'], 'reordenar_secuencia');
                }
            }
            break;

        case 'eliminar_de_seq':
            $seq_index = isset($_GET['seq_index']) ? (int)$_GET['seq_index'] : -1;

            if ($seq_index >= 0 && isset($presentacion_data['pdf_sequence'])) {
                // Eliminar elemento de la secuencia
                array_splice($presentacion_data['pdf_sequence'], $seq_index, 1);

                // Guardar cambios
                file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
                $mensaje = "Elemento quitado de la secuencia.";
                $tipo_mensaje = "success";
                registrarAccion($_SESSION['admin_user'], 'eliminar_de_secuencia');
            }
            break;
    }
}

// Procesar el formulario de información básica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_info'])) {
    // Esta lógica se traslada a un archivo procesador específico
    include('includes/editar/procesar_info.php');
}

// Obtener categorías como texto
$categorias_texto = '';
if (isset($presentacion_data['categorias']) && is_array($presentacion_data['categorias'])) {
    $categorias_texto = implode(', ', $presentacion_data['categorias']);
}

// Obtener configuración
$config = $presentacion_data['configuracion'] ?? [
    'mostrar_respuestas' => 'despues_pregunta',
    'tiempo_por_pregunta' => 0,
    'permitir_retroceder' => true,
    'mostrar_estadisticas' => true,
    'permitir_exportar' => true
];

// Función auxiliar para actualizar el número de preguntas en el índice
function actualizarNumeroPreguntas($id_presentacion, $num_preguntas) {
    if (file_exists('data/index.json')) {
        $index_json = file_get_contents('data/index.json');
        $index_data = json_decode($index_json, true);
        
        foreach ($index_data['presentaciones'] as &$presentacion) {
            if ($presentacion['id'] === $id_presentacion) {
                $presentacion['num_preguntas'] = $num_preguntas;
                break;
            }
        }
        
        file_put_contents('data/index.json', json_encode($index_data, JSON_PRETTY_PRINT));
    }
}

// Incluir encabezado HTML
include('includes/editar/head.php');
?>

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
                    <a class="nav-link active" href="admin_panel.php?seccion=presentaciones">
                        <i class="fas fa-chalkboard me-1"></i> Presentaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_panel.php?seccion=usuarios">
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

<div class="container py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?php echo $es_nueva ? 'Configurar nueva presentación' : 'Editar presentación'; ?>
        </h1>
        <div>
            <a href="admin_panel.php?seccion=presentaciones" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="index.php?test=<?php echo urlencode($id_presentacion); ?>" class="btn btn-success" target="_blank">
                <i class="fas fa-play me-1"></i> Probar
            </a>
        </div>
    </div>
    
    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show mb-4">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($es_nueva): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4">
        <i class="fas fa-info-circle me-2"></i> La presentación ha sido creada correctamente. Ahora puede configurar los detalles y agregar preguntas.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Pestañas de navegación -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="info-tab" data-bs-toggle="pill" href="#info" role="tab">
                <i class="fas fa-info-circle me-1"></i> Información básica
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="preguntas-tab" data-bs-toggle="pill" href="#preguntas" role="tab">
                <i class="fas fa-question-circle me-1"></i> Preguntas (<span id="contador-preguntas"><?php echo count($presentacion_data['preguntas']); ?></span>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="agregar-tab" data-bs-toggle="pill" href="#agregar" role="tab">
                <i class="fas fa-plus-circle me-1"></i> Agregar pregunta
            </a>
        </li>
    </ul>
    
    <!-- Contenido de las pestañas -->
    <div class="tab-content">
        <!-- Pestaña de información básica -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
            <?php include('includes/editar/info_basica.php'); ?>
        </div>
        
        <!-- Pestaña de preguntas -->
        <div class="tab-pane fade" id="preguntas" role="tabpanel">
            <?php include('includes/editar/lista_preguntas.php'); ?>
        </div>
        
        <!-- Pestaña de agregar pregunta -->
        <div class="tab-pane fade" id="agregar" role="tabpanel">
            <?php include('includes/editar/form_agregar.php'); ?>
        </div>
    </div>
</div>

<!-- Scripts JavaScript -->
<?php include('includes/editar/scripts.php'); ?>

</body>
</html>