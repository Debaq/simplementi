<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si hay un código de sesión y un ID de participante
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$participante_id = isset($_GET['participante']) ? $_GET['participante'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
}

if (empty($participante_id)) {
    // Si no se proporciona un ID de participante, intenta obtenerlo de la cookie
    if (isset($_COOKIE['participante_id'])) {
        $participante_id = $_COOKIE['participante_id'];
    } else {
        echo "Error: No se pudo identificar al participante.";
        exit;
    }
}

// Buscar la sesión en los archivos
$session_files = glob("data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada con código: " . htmlspecialchars($codigo_sesion);
    exit;
}

$session_file = $session_files[0];
$session_json = file_get_contents($session_file);

if ($session_json === false) {
    echo "Error: No se pudo leer el archivo de sesión.";
    exit;
}

$session_data = json_decode($session_json, true);

if ($session_data === null) {
    echo "Error: El archivo de sesión no tiene un formato JSON válido.";
    exit;
}

// Obtener información de la presentación
$test_id = $session_data['id_presentacion'];
$test_file = "data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Archivo de presentación no encontrado.";
    exit;
}

$test_json = file_get_contents($test_file);
if ($test_json === false) {
    echo "Error: No se pudo leer el archivo de presentación.";
    exit;
}

$test_data = json_decode($test_json, true);
if ($test_data === null) {
    echo "Error: El archivo de presentación no tiene un formato JSON válido.";
    exit;
}

// Buscar el participante
$participante = null;
foreach ($session_data['participantes'] as $p) {
    if ($p['id'] == $participante_id) {
        $participante = $p;
        break;
    }
}

if (!$participante) {
    echo "Error: Participante no encontrado.";
    exit;
}

// Calcular estadísticas
$total_preguntas = count($test_data['preguntas']);
$total_respondidas = count($participante['respuestas']);
$total_correctas = 0;
$total_incorrectas = 0;
$tiempo_total = 0;

// Mapear respuestas del participante para fácil acceso
$respuestas_map = [];
foreach ($participante['respuestas'] as $respuesta) {
    $respuestas_map[$respuesta['id_pregunta']] = $respuesta;
    
    if (isset($respuesta['tiempo_respuesta'])) {
        $tiempo_total += $respuesta['tiempo_respuesta'];
    }
}

// Para cada pregunta, verificar si la respuesta es correcta
$preguntas_con_respuestas = [];
foreach ($test_data['preguntas'] as $pregunta) {
    $respondida = isset($respuestas_map[$pregunta['id']]);
    $respuesta_dada = $respondida ? $respuestas_map[$pregunta['id']]['respuesta'] : null;
    $es_correcta = false;
    
    if ($respondida && isset($pregunta['respuesta_correcta'])) {
        // Manejo específico para preguntas de verdadero/falso
        if ($pregunta['tipo'] == 'verdadero_falso') {
            // Convertir la respuesta del participante (string 'true' o 'false') a booleano
            $respuesta_booleana = ($respuesta_dada === 'true');
            $es_correcta = ($respuesta_booleana === $pregunta['respuesta_correcta']);
        } else {
            // Comparación estándar para otros tipos de preguntas
            $es_correcta = ($respuesta_dada == $pregunta['respuesta_correcta']);
        }
        
        if ($es_correcta) {
            $total_correctas++;
        } else {
            $total_incorrectas++;
        }
    }
    
    $preguntas_con_respuestas[] = [
        'pregunta' => $pregunta,
        'respondida' => $respondida,
        'respuesta_dada' => $respuesta_dada,
        'es_correcta' => $es_correcta,
        'tiempo_respuesta' => $respondida && isset($respuestas_map[$pregunta['id']]['tiempo_respuesta']) ? 
                             $respuestas_map[$pregunta['id']]['tiempo_respuesta'] : null
    ];
}

// Calcular puntaje (una fórmula simple: 10 puntos por cada respuesta correcta)
$puntaje = $total_correctas * 10;
$porcentaje_acierto = $total_respondidas > 0 ? round(($total_correctas / $total_respondidas) * 100) : 0;
$tiempo_promedio = $total_respondidas > 0 ? round($tiempo_total / $total_respondidas, 1) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Tu Resumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .result-container {
            max-width: 768px;
            margin: 20px auto;
        }
        .score-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .score-card:hover {
            transform: translateY(-5px);
        }
        .question-card {
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        .question-card:hover {
            transform: translateY(-5px);
        }
        .correct-badge {
            background-color: #28a745;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .incorrect-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-gradient-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard me-2"></i> SimpleMenti
            </a>
        </div>
    </nav>

    <div class="container result-container">
        <div class="card shadow mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0 text-center">Tu Resumen</h3>
            </div>
            <div class="card-body">
                <h4 class="text-center mb-4"><?php echo htmlspecialchars($test_data['titulo']); ?></h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="score-card bg-primary text-white shadow">
                            <h2 class="display-3 fw-bold"><?php echo $puntaje; ?></h2>
                            <p class="lead">Puntos totales</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="score-card bg-info text-white shadow">
                            <h2 class="display-3 fw-bold"><?php echo $porcentaje_acierto; ?>%</h2>
                            <p class="lead">Porcentaje de acierto</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-left-primary shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                <h5 class="text-success"><?php echo $total_correctas; ?></h5>
                                <p class="text-muted mb-0">Respuestas correctas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-left-danger shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle text-danger mb-2" style="font-size: 2rem;"></i>
                                <h5 class="text-danger"><?php echo $total_incorrectas; ?></h5>
                                <p class="text-muted mb-0">Respuestas incorrectas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-left-warning shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-clock text-warning mb-2" style="font-size: 2rem;"></i>
                                <h5 class="text-warning"><?php echo $tiempo_promedio; ?>s</h5>
                                <p class="text-muted mb-0">Tiempo promedio</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h4 class="mt-5 mb-4">Detalle de tus respuestas</h4>
                
                <?php foreach ($preguntas_con_respuestas as $index => $item): ?>
                <div class="card question-card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pregunta <?php echo $index + 1; ?></h5>
                        <?php if ($item['respondida']): ?>
                            <?php if (isset($item['pregunta']['respuesta_correcta'])): ?>
                                <?php if ($item['es_correcta']): ?>
                                <div class="correct-badge">
                                    <i class="fas fa-check"></i>
                                </div>
                                <?php else: ?>
                                <div class="incorrect-badge">
                                    <i class="fas fa-times"></i>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="badge bg-info">Respondida</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="badge bg-secondary">No respondida</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3"><?php echo htmlspecialchars($item['pregunta']['pregunta']); ?></h5>
                        
                        <?php if ($item['respondida']): ?>
                        <div class="mb-3">
                            <strong>Tu respuesta:</strong>
                            <span class="ms-2 <?php echo (isset($item['pregunta']['respuesta_correcta']) && $item['es_correcta']) ? 'text-success' : (isset($item['pregunta']['respuesta_correcta']) ? 'text-danger' : ''); ?>">
                                <?php echo htmlspecialchars($item['respuesta_dada']); ?>
                            </span>
                            
                            <?php if ($item['tiempo_respuesta']): ?>
                            <small class="text-muted ms-2">
                                <i class="fas fa-clock me-1"></i> <?php echo $item['tiempo_respuesta']; ?>s
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($item['pregunta']['respuesta_correcta']) && !$item['es_correcta']): ?>
                        <div class="alert alert-success">
                            <strong>Respuesta correcta:</strong> 
                            <?php echo htmlspecialchars($item['pregunta']['respuesta_correcta']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$item['es_correcta'] && isset($item['pregunta']['explicacion'])): ?>
                        <div class="alert alert-info">
                            <strong>Explicación:</strong> 
                            <?php echo htmlspecialchars($item['pregunta']['explicacion']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="alert alert-warning">
                            No respondiste esta pregunta.
                            <?php if (isset($item['pregunta']['respuesta_correcta'])): ?>
                            <div class="mt-2">
                                <strong>Respuesta correcta:</strong> 
                                <?php echo htmlspecialchars($item['pregunta']['respuesta_correcta']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-4 text-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> Volver al inicio
                    </a>
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