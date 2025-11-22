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

// --- LÓGICA DE PROCESAMIENTO ---

// Verificar que se proporcionaron los parámetros necesarios
$id_presentacion = isset($_GET['id']) ? $_GET['id'] : '';
$pregunta_id = isset($_GET['pregunta_id']) ? (int)$_GET['pregunta_id'] : 0;

if (empty($id_presentacion) || $pregunta_id <= 0) {
    header("Location: admin_panel.php?seccion=presentaciones");
    exit;
}

// Verificar si existe la presentación
$presentacion_file = "data/presentaciones/$id_presentacion.json";
if (!file_exists($presentacion_file)) {
    die("Error: Presentación no encontrada.");
}

$presentacion_data = json_decode(file_get_contents($presentacion_file), true);
if ($presentacion_data === null) {
    die("Error: El archivo de la presentación no tiene un formato JSON válido.");
}

// Buscar la pregunta por ID
$pregunta = null;
$indice_pregunta = -1;
foreach ($presentacion_data['preguntas'] as $index => $p) {
    if ($p['id'] === $pregunta_id) {
        $pregunta = $p;
        $indice_pregunta = $index;
        break;
    }
}

if ($pregunta === null) {
    die("Error: Pregunta no encontrada.");
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$errores = [];

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y validar datos
    $pregunta_texto = trim($_POST['pregunta_texto'] ?? '');
    if (empty($pregunta_texto)) {
        $errores[] = 'El texto de la pregunta es obligatorio.';
    }

    if (empty($errores)) {
        // Actualizar datos de la pregunta
        $pregunta['pregunta'] = $pregunta_texto;
        $pregunta['explicacion'] = trim($_POST['explicacion'] ?? '');

        // Lógica de actualización específica por tipo
        switch ($pregunta['tipo']) {
            case 'opcion_multiple':
                $opciones = array_values(array_filter(array_map('trim', $_POST['opciones'] ?? [])));
                $respuesta_correcta_index = isset($_POST['respuesta_correcta_index']) ? (int)$_POST['respuesta_correcta_index'] : -1;
                $pregunta['opciones'] = $opciones;
                if ($respuesta_correcta_index !== -1 && isset($opciones[$respuesta_correcta_index])) {
                    $pregunta['respuesta_correcta'] = $opciones[$respuesta_correcta_index];
                } else {
                    unset($pregunta['respuesta_correcta']);
                }

                // Procesar feedbacks (opcional, uno por opción)
                if (isset($_POST['feedbacks']) && is_array($_POST['feedbacks'])) {
                    $feedbacks_array = $_POST['feedbacks'];
                    $feedbacks_map = [];

                    // Asociar cada feedback con su opción correspondiente
                    foreach ($opciones as $index => $opcion) {
                        if (isset($feedbacks_array[$index]) && !empty(trim($feedbacks_array[$index]))) {
                            $feedbacks_map[$opcion] = trim($feedbacks_array[$index]);
                        }
                    }

                    // Solo agregar feedbacks si hay al menos uno, sino eliminar el campo
                    if (!empty($feedbacks_map)) {
                        $pregunta['feedbacks'] = $feedbacks_map;
                    } else {
                        unset($pregunta['feedbacks']);
                    }
                } else {
                    // Si no se enviaron feedbacks, eliminar el campo
                    unset($pregunta['feedbacks']);
                }
                break;
            case 'verdadero_falso':
                $respuesta_correcta = $_POST['respuesta_correcta'] ?? '';
                if ($respuesta_correcta === 'Verdadero' || $respuesta_correcta === 'Falso') {
                    $pregunta['respuesta_correcta'] = $respuesta_correcta;
                } else {
                    unset($pregunta['respuesta_correcta']);
                }
                break;
        }

        // Guardar los cambios
        $presentacion_data['preguntas'][$indice_pregunta] = $pregunta;
        if (file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT))) {
            registrarAccion($_SESSION['admin_user'], 'editar_pregunta');
            $mensaje = 'Pregunta actualizada correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al guardar los cambios.';
            $tipo_mensaje = 'danger';
        }
    }

    if (!empty($errores)) {
        $mensaje = 'Por favor, corrija los errores:<ul><li>' . implode('</li><li>', $errores) . '</li></ul>';
        $tipo_mensaje = 'danger';
    }
}

// Preparar datos para el formulario
$tipo_pregunta_texto = ucfirst(str_replace('_', ' ', $pregunta['tipo']));

// --- RENDERIZADO DE LA PÁGINA ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pregunta - SimpleMenti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_panel.php"><i class="fas fa-chalkboard me-2"></i> SimpleMenti Admin</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="editar_presentacion.php?id=<?php echo htmlspecialchars($id_presentacion); ?>">
                    <i class="fas fa-arrow-left me-1"></i> Volver a la presentación
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container py-5">
    <h1 class="mb-4">Editar Pregunta <span class="badge bg-secondary"><?php echo htmlspecialchars($tipo_pregunta_texto); ?></span></h1>

    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="pregunta_texto" class="form-label">Texto de la pregunta</label>
                    <textarea class="form-control" id="pregunta_texto" name="pregunta_texto" rows="3" required><?php echo htmlspecialchars($pregunta['pregunta']); ?></textarea>
                </div>

                <?php // Formulario específico por tipo de pregunta ?>
                <?php switch ($pregunta['tipo']):
                    case 'opcion_multiple': ?>
                        <h5>Opciones</h5>
                        <div class="form-text mb-2">El feedback es opcional y específico para cada opción. Cada participante verá solo el feedback de la opción que eligió.</div>
                        <div id="opciones-container">
                            <?php foreach ($pregunta['opciones'] as $index => $opcion):
                                $letra = chr(65 + $index);  // A, B, C, ...
                                $feedback_opcion = isset($pregunta['feedbacks'][$opcion]) ? $pregunta['feedbacks'][$opcion] : '';
                            ?>
                            <div class="opcion-item mb-3 p-3 border rounded">
                                <div class="row align-items-center mb-2">
                                    <div class="col-auto">
                                        <span class="badge bg-primary"><?php echo $letra; ?></span>
                                    </div>
                                    <div class="col">
                                        <input type="text" name="opciones[]" class="form-control" value="<?php echo htmlspecialchars($opcion); ?>" placeholder="Texto de la opción" required>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="<?php echo $index; ?>" id="correcta_edit_<?php echo $index; ?>" <?php echo ($opcion === ($pregunta['respuesta_correcta'] ?? '')) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="correcta_edit_<?php echo $index; ?>">Correcta</label>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.opcion-item').remove();">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-auto" style="width: 50px;"></div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm" name="feedbacks[]" value="<?php echo htmlspecialchars($feedback_opcion); ?>" placeholder="Feedback para esta opción (opcional)">
                                        <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="agregar-opcion"><i class="fas fa-plus me-1"></i> Agregar opción</button>
                        <hr>
                        <?php break; ?>

                    <?php case 'verdadero_falso': ?>
                        <h5>Respuesta Correcta</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="respuesta_correcta" id="verdadero" value="Verdadero" <?php echo (($pregunta['respuesta_correcta'] ?? '') === 'Verdadero') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="verdadero">Verdadero</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="respuesta_correcta" id="falso" value="Falso" <?php echo (($pregunta['respuesta_correcta'] ?? '') === 'Falso') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="falso">Falso</label>
                        </div>
                        <hr>
                        <?php break; ?>

                <?php endswitch; ?>

                <div class="mb-3">
                    <label for="explicacion" class="form-label">Explicación (Opcional)</label>
                    <textarea class="form-control" id="explicacion" name="explicacion" rows="2"><?php echo htmlspecialchars($pregunta['explicacion'] ?? ''); ?></textarea>
                    <div class="form-text">Aparecerá después de que los participantes hayan respondido.</div>
                </div>

                <div class="d-flex justify-content-between">
                     <a href="editar_presentacion.php?id=<?php echo htmlspecialchars($id_presentacion); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const agregarOpcionBtn = document.getElementById('agregar-opcion');
    if (agregarOpcionBtn) {
        agregarOpcionBtn.addEventListener('click', function() {
            const container = document.getElementById('opciones-container');
            const newIndex = container.querySelectorAll('.opcion-item').length;
            const nuevaLetra = String.fromCharCode(65 + newIndex); // A, B, C, ...

            const div = document.createElement('div');
            div.className = 'opcion-item mb-3 p-3 border rounded';
            div.innerHTML = `
                <div class="row align-items-center mb-2">
                    <div class="col-auto">
                        <span class="badge bg-primary">${nuevaLetra}</span>
                    </div>
                    <div class="col">
                        <input type="text" name="opciones[]" class="form-control" placeholder="Texto de la opción" required>
                    </div>
                    <div class="col-auto">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="${newIndex}" id="correcta_edit_${newIndex}">
                            <label class="form-check-label" for="correcta_edit_${newIndex}">Correcta</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.opcion-item').remove();">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-auto" style="width: 50px;"></div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm" name="feedbacks[]" placeholder="Feedback para esta opción (opcional)">
                        <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>