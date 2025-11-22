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
$datos_presentacion = [
    'id' => '',
    'titulo' => '',
    'descripcion' => '',
    'categorias' => '',
    'protegido' => false,
    'password' => '',
    'mostrar_respuestas' => 'despues_pregunta',
    'tiempo_por_pregunta' => 0,
    'permitir_retroceder' => true,
    'mostrar_estadisticas' => true,
    'permitir_exportar' => true,
    'permitir_anotaciones' => false,
    'exportar_con_anotaciones' => false,
    'permitir_notas' => false,
    'permitir_marcadores' => false,
    'permitir_navegacion_libre' => false,
    'permitir_interacciones' => false
];

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datos_presentacion = [
        'id' => isset($_POST['id']) ? trim($_POST['id']) : '',
        'titulo' => isset($_POST['titulo']) ? trim($_POST['titulo']) : '',
        'descripcion' => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '',
        'categorias' => isset($_POST['categorias']) ? trim($_POST['categorias']) : '',
        'protegido' => isset($_POST['protegido']) && $_POST['protegido'] === '1',
        'password' => isset($_POST['password']) ? $_POST['password'] : '',
        'mostrar_respuestas' => isset($_POST['mostrar_respuestas']) ? $_POST['mostrar_respuestas'] : 'despues_pregunta',
        'tiempo_por_pregunta' => isset($_POST['tiempo_por_pregunta']) ? intval($_POST['tiempo_por_pregunta']) : 0,
        'permitir_retroceder' => isset($_POST['permitir_retroceder']) && $_POST['permitir_retroceder'] === '1',
        'mostrar_estadisticas' => isset($_POST['mostrar_estadisticas']) && $_POST['mostrar_estadisticas'] === '1',
        'permitir_exportar' => isset($_POST['permitir_exportar']) && $_POST['permitir_exportar'] === '1',
        'permitir_anotaciones' => isset($_POST['permitir_anotaciones']) && $_POST['permitir_anotaciones'] === '1',
        'exportar_con_anotaciones' => isset($_POST['exportar_con_anotaciones']) && $_POST['exportar_con_anotaciones'] === '1',
        'permitir_notas' => isset($_POST['permitir_notas']) && $_POST['permitir_notas'] === '1',
        'permitir_marcadores' => isset($_POST['permitir_marcadores']) && $_POST['permitir_marcadores'] === '1',
        'permitir_navegacion_libre' => isset($_POST['permitir_navegacion_libre']) && $_POST['permitir_navegacion_libre'] === '1',
        'permitir_interacciones' => isset($_POST['permitir_interacciones']) && $_POST['permitir_interacciones'] === '1'
    ];
    
    // Validar campos básicos
    $errores = [];
    
    if (empty($datos_presentacion['id'])) {
        $errores[] = 'El ID de la presentación es obligatorio';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $datos_presentacion['id'])) {
        $errores[] = 'El ID debe contener entre 3 y 30 caracteres alfanuméricos o guiones bajos';
    }
    
    if (empty($datos_presentacion['titulo'])) {
        $errores[] = 'El título es obligatorio';
    }
    
    if ($datos_presentacion['protegido'] && empty($datos_presentacion['password'])) {
        $errores[] = 'Si la presentación está protegida, debe especificar una contraseña';
    }
    
    if (empty($errores)) {
        // Crear el array de categorías a partir del texto
        $categorias_array = [];
        if (!empty($datos_presentacion['categorias'])) {
            $categorias_array = array_map('trim', explode(',', $datos_presentacion['categorias']));
        }
        
        // Crear la estructura de la presentación
        $presentacion = [
            'id' => $datos_presentacion['id'],
            'titulo' => $datos_presentacion['titulo'],
            'descripcion' => $datos_presentacion['descripcion'],
            'autor' => $_SESSION['admin_nombre'],
            'fecha_creacion' => date('Y-m-d\TH:i:s'),
            'protegido' => $datos_presentacion['protegido'],
            'num_preguntas' => 0,
            'categorias' => $categorias_array
        ];
        
        // Agregar contraseña solo si está protegida
        if ($datos_presentacion['protegido']) {
            $presentacion['password'] = $datos_presentacion['password'];
        }
        
        // Crear la estructura completa con configuración
        $estructura_presentacion = [
            'id' => $datos_presentacion['id'],
            'titulo' => $datos_presentacion['titulo'],
            'descripcion' => $datos_presentacion['descripcion'],
            'autor' => $_SESSION['admin_nombre'],
            'fecha_creacion' => date('Y-m-d\TH:i:s'),
            'protegido' => $datos_presentacion['protegido'],
            'configuracion' => [
                'mostrar_respuestas' => $datos_presentacion['mostrar_respuestas'],
                'tiempo_por_pregunta' => $datos_presentacion['tiempo_por_pregunta'],
                'permitir_retroceder' => $datos_presentacion['permitir_retroceder'],
                'mostrar_estadisticas' => $datos_presentacion['mostrar_estadisticas'],
                'permitir_exportar' => $datos_presentacion['permitir_exportar'],
                'permitir_anotaciones' => $datos_presentacion['permitir_anotaciones'],
                'exportar_con_anotaciones' => $datos_presentacion['exportar_con_anotaciones'],
                'permitir_notas' => $datos_presentacion['permitir_notas'],
                'permitir_marcadores' => $datos_presentacion['permitir_marcadores'],
                'permitir_navegacion_libre' => $datos_presentacion['permitir_navegacion_libre'],
                'permitir_interacciones' => $datos_presentacion['permitir_interacciones']
            ],
            'preguntas' => []
        ];
        
        // Agregar contraseña solo si está protegida
        if ($datos_presentacion['protegido']) {
            $estructura_presentacion['password'] = $datos_presentacion['password'];
        }
        
        // Verificar si existe el directorio de presentaciones
        if (!file_exists('data/presentaciones')) {
            mkdir('data/presentaciones', 0755, true);
        }
        
        // Verificar si ya existe una presentación con el mismo ID
        if (file_exists("data/presentaciones/{$datos_presentacion['id']}.json")) {
            $errores[] = 'Ya existe una presentación con ese ID';
        } else {
            // Guardar el archivo de la presentación
            $archivo_presentacion = "data/presentaciones/{$datos_presentacion['id']}.json";
            $resultado_guardado = file_put_contents($archivo_presentacion, json_encode($estructura_presentacion, JSON_PRETTY_PRINT));
            
            if ($resultado_guardado === false) {
                $errores[] = 'Error al guardar el archivo de la presentación';
            } else {
                // Actualizar índice de presentaciones
                if (!file_exists('data/index.json')) {
                    $index_data = [
                        'presentaciones' => []
                    ];
                } else {
                    $index_json = file_get_contents('data/index.json');
                    $index_data = json_decode($index_json, true);
                }
                
                // Agregar la nueva presentación al índice
                $index_data['presentaciones'][] = $presentacion;
                
                // Guardar el índice actualizado
                $resultado_indice = file_put_contents('data/index.json', json_encode($index_data, JSON_PRETTY_PRINT));
                
                if ($resultado_indice === false) {
                    $errores[] = 'Error al actualizar el índice de presentaciones';
                } else {
                    // Registrar la acción
                    registrarAccion($_SESSION['admin_user'], 'crear_presentacion');
                    
                    $mensaje = 'Presentación creada correctamente. Ahora puede agregar preguntas.';
                    $tipo_mensaje = 'success';
                    
                    // Redirigir a la página de edición
                    header("Location: editar_presentacion.php?id={$datos_presentacion['id']}&nuevo=1");
                    exit;
                }
            }
        }
    }
    
    if (!empty($errores)) {
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
    <title>SimpleMenti - Crear Presentación</title>
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

    <div class="container py-4 form-container">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Crear Nueva Presentación</h1>
            <a href="admin_panel.php?seccion=presentaciones" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a presentaciones
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
                <h6 class="m-0 font-weight-bold text-primary">Información básica de la presentación</h6>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="id" class="form-label">ID de la presentación <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" class="form-control" id="id" name="id" 
                                       value="<?php echo htmlspecialchars($datos_presentacion['id']); ?>" required
                                       pattern="[a-zA-Z0-9_]{3,30}">
                            </div>
                            <div class="form-text">Identificador único, use solo letras, números y guiones bajos.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                       value="<?php echo htmlspecialchars($datos_presentacion['titulo']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($datos_presentacion['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categorias" class="form-label">Categorías</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tags"></i></span>
                            <input type="text" class="form-control" id="categorias" name="categorias" 
                                   value="<?php echo htmlspecialchars($datos_presentacion['categorias']); ?>">
                        </div>
                        <div class="form-text">Separadas por comas, ej: educación, matemáticas, primaria</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="protegido" name="protegido" value="1" 
                                   <?php echo $datos_presentacion['protegido'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="protegido">Proteger con contraseña</label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="password-container" style="<?php echo $datos_presentacion['protegido'] ? '' : 'display: none;'; ?>">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   value="<?php echo htmlspecialchars($datos_presentacion['password']); ?>">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="mb-0">Configuración avanzada</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="mostrar_respuestas" class="form-label">Mostrar respuestas correctas</label>
                                <select class="form-select" id="mostrar_respuestas" name="mostrar_respuestas">
                                    <option value="nunca" <?php echo $datos_presentacion['mostrar_respuestas'] === 'nunca' ? 'selected' : ''; ?>>Nunca mostrar respuestas</option>
                                    <option value="despues_pregunta" <?php echo $datos_presentacion['mostrar_respuestas'] === 'despues_pregunta' ? 'selected' : ''; ?>>Después de cada pregunta</option>
                                    <option value="final" <?php echo $datos_presentacion['mostrar_respuestas'] === 'final' ? 'selected' : ''; ?>>Al final de la presentación</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tiempo_por_pregunta" class="form-label">Tiempo por pregunta (segundos)</label>
                                <input type="number" class="form-control" id="tiempo_por_pregunta" name="tiempo_por_pregunta" 
                                       value="<?php echo $datos_presentacion['tiempo_por_pregunta']; ?>" min="0" step="1">
                                <div class="form-text">0 = sin límite de tiempo</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_retroceder" name="permitir_retroceder" value="1"
                                               <?php echo $datos_presentacion['permitir_retroceder'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_retroceder">Permitir retroceder</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="mostrar_estadisticas" name="mostrar_estadisticas" value="1"
                                               <?php echo $datos_presentacion['mostrar_estadisticas'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="mostrar_estadisticas">Mostrar estadísticas</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_exportar" name="permitir_exportar" value="1"
                                               <?php echo $datos_presentacion['permitir_exportar'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_exportar">Permitir exportar</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="alert alert-primary mb-3">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <strong>Funcionalidades para Estudiantes:</strong> Configure qué herramientas estarán disponibles durante la presentación.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_anotaciones" name="permitir_anotaciones" value="1"
                                               <?php echo $datos_presentacion['permitir_anotaciones'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_anotaciones">
                                            <i class="fas fa-pencil-alt me-1"></i> <strong>Anotaciones</strong> - Dibujar sobre slides
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Lápiz, marcador, formas geométricas, texto (solo con PDF)</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="exportar_con_anotaciones" name="exportar_con_anotaciones" value="1"
                                               <?php echo $datos_presentacion['exportar_con_anotaciones'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="exportar_con_anotaciones">
                                            <i class="fas fa-file-pdf me-1"></i> <strong>Exportar PDF</strong> con anotaciones
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Los estudiantes pueden generar PDF en su dispositivo</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_notas" name="permitir_notas" value="1"
                                               <?php echo $datos_presentacion['permitir_notas'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_notas">
                                            <i class="fas fa-sticky-note me-1"></i> <strong>Notas textuales</strong> por slide
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Panel de notas debajo de cada diapositiva</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_marcadores" name="permitir_marcadores" value="1"
                                               <?php echo $datos_presentacion['permitir_marcadores'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_marcadores">
                                            <i class="fas fa-bookmark me-1"></i> <strong>Marcadores</strong> de slides importantes
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Marcar y categorizar slides clave (importante, revisar, duda)</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_navegacion_libre" name="permitir_navegacion_libre" value="1"
                                               <?php echo $datos_presentacion['permitir_navegacion_libre'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_navegacion_libre">
                                            <i class="fas fa-route me-1"></i> <strong>Navegación libre</strong> por slides
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Avanzar/retroceder sin depender del presentador (sin spoilers)</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="permitir_interacciones" name="permitir_interacciones" value="1"
                                               <?php echo $datos_presentacion['permitir_interacciones'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="permitir_interacciones">
                                            <i class="fas fa-comments me-1"></i> <strong>Interacciones</strong> en tiempo real
                                        </label>
                                    </div>
                                    <div class="form-text small mb-3">Levantar mano, preguntas, comprensión, reacciones</div>
                                </div>
                            </div>

                            <div class="alert alert-warning small mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Nota:</strong> Las anotaciones, notas y marcadores se almacenan en el dispositivo del estudiante (no en el servidor). El modo oscuro siempre está disponible como preferencia personal.
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo me-1"></i> Restablecer
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar y continuar
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
            
            // Función para mostrar/ocultar campo de contraseña
            const protegidoCheck = document.getElementById('protegido');
            const passwordContainer = document.getElementById('password-container');
            
            if (protegidoCheck && passwordContainer) {
                protegidoCheck.addEventListener('change', function() {
                    passwordContainer.style.display = this.checked ? 'block' : 'none';
                    
                    if (this.checked) {
                        passwordInput.setAttribute('required', 'required');
                    } else {
                        passwordInput.removeAttribute('required');
                    }
                });
            }
            
            // Generar ID a partir del título
            const tituloInput = document.getElementById('titulo');
            const idInput = document.getElementById('id');
            
            if (tituloInput && idInput && idInput.value === '') {
                tituloInput.addEventListener('blur', function() {
                    if (idInput.value === '') {
                        let idPropuesto = this.value.toLowerCase()
                            .replace(/\s+/g, '_')        // Espacios a guiones bajos
                            .replace(/[^a-z0-9_]/g, '')  // Eliminar caracteres no permitidos
                            .substring(0, 30);           // Limitar longitud
                        
                        idInput.value = idPropuesto;
                    }
                });
            }
        });
    </script>
</body>
</html>