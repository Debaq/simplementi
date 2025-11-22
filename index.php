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
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        body {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
        }

        .hero-section {
            animation: fadeInDown 0.8s ease-out;
            padding: 3rem 0;
        }

        .logo-container {
            background: white;
            border-radius: 30px;
            padding: 30px 50px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            display: inline-block;
            margin-bottom: 20px;
        }

        .logo-text {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .tagline {
            color: white;
            font-size: 1.3rem;
            font-weight: 500;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .main-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: none;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border: none;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .join-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            box-shadow: 0 10px 30px rgba(56, 239, 125, 0.4);
        }

        .join-btn:hover {
            box-shadow: 0 15px 40px rgba(56, 239, 125, 0.6);
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 30px 0;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            color: #6c757d;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
        }

        .join-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 30px;
            border: 3px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .join-section:hover {
            border-color: #11998e;
            background: white;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.2);
        }

        .code-input {
            border: 3px solid #e9ecef;
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-align: center;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.25rem rgba(17, 153, 142, 0.25);
        }

        .presentations-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: none;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .presentation-item {
            border-radius: 15px;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }

        .presentation-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        .presentation-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }

        .start-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .start-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(245, 87, 108, 0.4);
        }

        .footer-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 60px;
            padding: 25px 0;
        }

        .footer-text {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 500;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
        }

        .icon-box i {
            font-size: 1.8rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-5">
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
        <div class="row justify-content-center mb-5">
            <div class="col-lg-7 col-md-9">
                <div class="card main-card">
                    <div class="card-header-custom text-center">
                        <h3 class="mb-0 text-white fw-bold">
                            <i class="fas fa-rocket me-2"></i>
                            ¿Qué deseas hacer?
                        </h3>
                    </div>
                    <div class="card-body p-5">
                        <!-- Crear Presentación -->
                        <div class="text-center mb-4">
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
                            <div class="text-center mb-3">
                                <div class="icon-box mx-auto" style="animation-delay: 1s;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Unirse como Participante</h5>
                                <p class="text-muted">Ingresa el código de sesión que te proporcionó el presentador</p>
                            </div>
                            <form action="" method="get">
                                <div class="input-group mb-3">
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
                                        <i class="fas fa-sign-in-alt me-2"></i> Unirse Ahora
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
                        <h4 class="mb-0 text-white fw-bold text-center">
                            <i class="fas fa-list-ul me-2"></i> Presentaciones Disponibles
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <?php
                            // Cargar presentaciones desde el índice
                            $index_json = file_get_contents($index_file);
                            $index_data = json_decode($index_json, true);

                            foreach ($index_data['presentaciones'] as $presentacion) {
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card presentation-item">
                                    <div class="card-body p-4">
                                        <h5 class="card-title fw-bold mb-3">
                                            <i class="fas fa-file-presentation text-primary me-2"></i>
                                            <?php echo htmlspecialchars($presentacion['titulo']); ?>
                                        </h5>
                                        <p class="card-text text-muted mb-3">
                                            <?php echo htmlspecialchars($presentacion['descripcion']); ?>
                                        </p>

                                        <div class="d-flex gap-2 flex-wrap mb-3">
                                            <span class="presentation-badge">
                                                <i class="fas fa-question-circle me-1"></i>
                                                <?php echo $presentacion['num_preguntas']; ?> preguntas
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-user-circle me-1"></i>
                                                Por <?php echo htmlspecialchars($presentacion['autor']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 p-3">
                                        <a href="?test=<?php echo urlencode($presentacion['id']); ?>"
                                           class="btn start-btn w-100 text-white">
                                            <i class="fas fa-play me-2"></i> Iniciar Presentación
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