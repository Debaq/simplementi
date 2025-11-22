<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP para manejar las sesiones del presentador
session_start();

// Verificar y crear directorios necesarios
$directorios = [
    'data',
    'data/presentaciones',
    'data/respuestas',
    'img'
];

foreach ($directorios as $directorio) {
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }
}

// Verificar si existe el índice de presentaciones
$index_file = 'data/index.json';
if (!file_exists($index_file)) {
    // Crear índice básico si no existe
    $index_data = [
        'presentaciones' => [
            [
                'id' => 'demo_test',
                'titulo' => 'Test de Demostración',
                'descripcion' => 'Un test simple para probar el sistema',
                'autor' => 'Admin SimpleMenti',
                'fecha_creacion' => date('Y-m-d\TH:i:s'),
                'protegido' => false,
                'num_preguntas' => 3,
                'categorias' => ['demo', 'prueba']
            ]
        ]
    ];
    
    file_put_contents($index_file, json_encode($index_data, JSON_PRETTY_PRINT));
    
    // Crear presentación demo
    $demo_file = 'data/presentaciones/demo_test.json';
    
    if (!file_exists($demo_file)) {
        $demo_data = [
            'id' => 'demo_test',
            'titulo' => 'Test de Demostración',
            'descripcion' => 'Un test simple para probar el sistema',
            'autor' => 'Admin SimpleMenti',
            'fecha_creacion' => date('Y-m-d\TH:i:s'),
            'protegido' => false,
            'configuracion' => [
                'mostrar_respuestas' => 'despues_pregunta',
                'tiempo_por_pregunta' => 0,
                'permitir_retroceder' => true,
                'mostrar_estadisticas' => true,
                'permitir_exportar' => true
            ],
            'preguntas' => [
                [
                    'id' => 1,
                    'tipo' => 'opcion_multiple',
                    'pregunta' => '¿Cuál es tu color favorito?',
                    'opciones' => ['Rojo', 'Verde', 'Azul', 'Amarillo'],
                    'respuesta_correcta' => ''
                ],
                [
                    'id' => 2,
                    'tipo' => 'opcion_multiple',
                    'pregunta' => '¿Cuál de estos animales es un mamífero?',
                    'opciones' => ['Cocodrilo', 'Delfín', 'Tortuga', 'Serpiente'],
                    'respuesta_correcta' => 'Delfín',
                    'explicacion' => 'Los delfines son mamíferos marinos que respiran aire'
                ],
                [
                    'id' => 3,
                    'tipo' => 'nube_palabras',
                    'pregunta' => 'Escribe una palabra que describa cómo te sientes hoy'
                ]
            ]
        ];
        
        file_put_contents($demo_file, json_encode($demo_data, JSON_PRETTY_PRINT));
    }
}

// Procesar parámetros de la URL
$test_id = isset($_GET['test']) ? $_GET['test'] : '';
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

// Si no hay parámetros, mostrar página de inicio
if (empty($test_id) && empty($codigo_sesion) && empty($accion)) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - tmeduca.org</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #fafafa;
            min-height: 100vh;
        }

        .hero-section {
            padding: 2rem 0 1.5rem;
        }

        .logo-container {
            background: white;
            border-radius: 12px;
            padding: 15px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: inline-block;
            margin-bottom: 10px;
            border-left: 4px solid #0066FF;
        }

        .logo-text {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin: 0;
        }

        .tagline {
            color: #6B7280;
            font-size: 1rem;
            font-weight: 400;
        }

        .main-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #E5E7EB;
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            padding: 15px;
            border: none;
        }

        .action-btn {
            background: #0066FF;
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background: #0052CC;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
            color: white;
        }

        .join-btn {
            background: #10B981;
        }

        .join-btn:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 20px 0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #9CA3AF;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #E5E7EB;
        }

        .join-section {
            background: #F9FAFB;
            border-radius: 12px;
            padding: 20px;
            border: 2px dashed #D1D5DB;
        }

        .code-input {
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
        }

        .code-input:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            outline: none;
        }

        .presentations-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #E5E7EB;
            overflow: hidden;
        }

        .presentation-item {
            border-radius: 10px;
            border: 1px solid #E5E7EB;
            transition: all 0.2s ease;
            height: 100%;
            background: white;
        }

        .presentation-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #0066FF;
        }

        .presentation-badge {
            background: #EFF6FF;
            color: #0066FF;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .start-btn {
            background: #0066FF;
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .start-btn:hover {
            background: #0052CC;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }

        .footer-custom {
            background: white;
            border-top: 1px solid #E5E7EB;
            margin-top: 40px;
            padding: 20px 0;
        }

        .footer-text {
            color: #6B7280;
            font-weight: 400;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .icon-box i {
            font-size: 1.2rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Hero Section -->
        <div class="row justify-content-center hero-section">
            <div class="col-md-8 text-center">
                <div class="logo-container">
                    <h1 class="logo-text">
                        <i class="fas fa-comments"></i> SimpleMenti
                    </h1>
                </div>
                <p class="tagline">Sistema interactivo para presentaciones y encuestas en tiempo real</p>
            </div>
        </div>

        <!-- Main Action Card -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-7 col-md-9">
                <div class="card main-card">
                    <div class="card-header-custom text-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-bars me-2"></i>
                            ¿Qué deseas hacer?
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <!-- Crear Presentación -->
                        <div class="text-center mb-3">
                            <div class="icon-box mx-auto">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <a href="admin_login.php" class="action-btn">
                                <i class="fas fa-plus-circle me-2"></i> Crear Presentación
                            </a>
                        </div>

                        <!-- Divider -->
                        <div class="divider">
                            <span>o</span>
                        </div>

                        <!-- Unirse como Participante -->
                        <div class="join-section">
                            <div class="text-center mb-2">
                                <div class="icon-box mx-auto">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Unirse como Participante</h6>
                                <p class="text-muted small mb-2">Ingresa el código de sesión</p>
                            </div>
                            <form action="" method="get">
                                <div class="mb-2">
                                    <input
                                        type="text"
                                        name="codigo"
                                        class="form-control code-input"
                                        placeholder="ABC123"
                                        autocomplete="off"
                                        maxlength="6"
                                        required>
                                </div>
                                <div class="d-grid">
                                    <button class="action-btn join-btn" type="submit">
                                        <i class="fas fa-sign-in-alt me-2"></i> Unirse
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presentaciones Disponibles -->
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card presentations-card">
                    <div class="card-header-custom">
                        <h5 class="mb-0 text-white text-center">
                            <i class="fas fa-list-ul me-2"></i> Presentaciones Disponibles
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <?php
                            // Cargar presentaciones desde el índice
                            $index_json = file_get_contents($index_file);
                            $index_data = json_decode($index_json, true);

                            foreach ($index_data['presentaciones'] as $presentacion) {
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card presentation-item">
                                    <div class="card-body p-3">
                                        <h6 class="card-title fw-bold mb-2">
                                            <i class="fas fa-file-presentation text-dark me-2"></i>
                                            <?php echo htmlspecialchars($presentacion['titulo']); ?>
                                        </h6>
                                        <p class="card-text text-muted small mb-2">
                                            <?php echo htmlspecialchars($presentacion['descripcion']); ?>
                                        </p>

                                        <div class="mb-2">
                                            <span class="presentation-badge">
                                                <i class="fas fa-question-circle me-1"></i>
                                                <?php echo $presentacion['num_preguntas']; ?> preguntas
                                            </span>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user-circle me-1"></i>
                                                <?php echo htmlspecialchars($presentacion['autor']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 p-2">
                                        <a href="?test=<?php echo urlencode($presentacion['id']); ?>"
                                           class="btn start-btn w-100 text-white">
                                            <i class="fas fa-play me-2"></i> Iniciar
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-custom text-center">
        <p class="footer-text mb-0">
            <i class="fas fa-heart me-2"></i>
            SimpleMenti &copy; <?php echo date('Y'); ?> - tmeduca.org
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} elseif (!empty($codigo_sesion)) {
    // Redireccionar a la página del participante
    header("Location: participante.php?codigo=$codigo_sesion");
    exit;
} elseif (!empty($test_id)) {
    // Verificar si existe la presentación
    $test_file = "data/presentaciones/$test_id.json";
    
    if (!file_exists($test_file)) {
        echo "Error: Presentación no encontrada.";
        exit;
    }
    
    // Leer datos de la presentación
    $test_json = file_get_contents($test_file);
    $test_data = json_decode($test_json, true);
    
    // Verificar si la presentación está protegida con contraseña
    if (isset($test_data['protegido']) && $test_data['protegido']) {
        // Si no hay sesión de autenticación, redirigir al login
        if (!isset($_SESSION['auth_test']) || $_SESSION['auth_test'] !== $test_id) {
            header("Location: login.php?test=$test_id");
            exit;
        }
    }
    
    // Generar código único para la sesión
    $codigo_nuevo = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 3)), 0, 6);
    
    // Crear directorio para las respuestas si no existe
    $resp_dir = "data/respuestas/$test_id";
    if (!file_exists($resp_dir)) {
        mkdir($resp_dir, 0755, true);
    }
    
    // Crear archivo de sesión
    $sesion_data = [
        'id_sesion' => $codigo_nuevo,
        'id_presentacion' => $test_id,
        'fecha_inicio' => date('Y-m-d\TH:i:s'),
        'fecha_fin' => null,
        'estado' => 'activa',
        'pregunta_actual' => 0, // Empezamos con 0 para mostrar la pantalla de QR inicial
        'participantes' => [],
        'estadisticas' => [
            'total_participantes' => 0,
            'preguntas_completadas' => 0,
            'preguntas_por_completar' => count($test_data['preguntas']),
            'porcentaje_respuestas_correctas' => 0,
            'tiempo_promedio_respuesta' => 0
        ]
    ];
    
    $sesion_file = "$resp_dir/sesion_$codigo_nuevo.json";
    file_put_contents($sesion_file, json_encode($sesion_data, JSON_PRETTY_PRINT));
    
    // Redireccionar a la página del presentador
    header("Location: presentador.php?codigo=$codigo_nuevo");
    exit;
}
?>