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
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <h1 class="display-4 fw-bold text-primary">SimpleMenti</h1>
                <p class="lead">Sistema interactivo para presentaciones y encuestas en tiempo real</p>
            </div>
        </div>
        
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="text-center mb-0">¿Qué deseas hacer?</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-4">
                            <a href="admin_login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-chalkboard-teacher me-2"></i> Crear presentación
                            </a>
                            
                            <div class="text-center my-2">- o -</div>
                            
                            <div class="card card-hover">
                                <div class="card-body">
                                    <h5 class="card-title">Unirse como participante</h5>
                                    <form action="" method="get" class="mt-3">
                                        <div class="input-group mb-3">
                                            <input type="text" name="codigo" class="form-control form-control-lg" 
                                                placeholder="Ingrese código de sesión" autocomplete="off">
                                            <button class="btn btn-success btn-lg" type="submit">
                                                <i class="fas fa-sign-in-alt me-2"></i> Unirse
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-list me-2"></i> Presentaciones disponibles</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            // Cargar presentaciones desde el índice
                            $index_json = file_get_contents($index_file);
                            $index_data = json_decode($index_json, true);
                            
                            foreach ($index_data['presentaciones'] as $presentacion) {
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 card-hover">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($presentacion['titulo']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($presentacion['descripcion']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-question-circle"></i> <?php echo $presentacion['num_preguntas']; ?> preguntas
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($presentacion['autor']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="?test=<?php echo urlencode($presentacion['id']); ?>" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-play me-1"></i> Iniciar
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
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">SimpleMenti &copy; <?php echo date('Y'); ?> - tmeduca.org</p>
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