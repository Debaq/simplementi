<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si hay un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado.";
    exit;
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

// Verificar si el usuario está autenticado para presentaciones protegidas
if (isset($test_data['protegido']) && $test_data['protegido']) {
    if (!isset($_SESSION['auth_test']) || $_SESSION['auth_test'] !== $test_id) {
        header("Location: login.php?test=$test_id");
        exit;
    }
}

// Procesar datos para el resumen
$total_participantes = count($session_data['participantes']);
$preguntas = $test_data['preguntas'];

// Calcular estadísticas generales
$stats = [
    'total_participantes' => $total_participantes,
    'total_preguntas' => count($preguntas),
    'preguntas_respondidas' => 0,
    'total_respuestas' => 0,
    'respuestas_correctas' => 0,
    'porcentaje_correctas' => 0,
    'tiempo_promedio' => 0
];

$resultados_por_pregunta = [];

// Contar respuestas y calcular estadísticas
$total_tiempo = 0;
$total_conteo_respuestas = 0;

foreach ($preguntas as $pregunta) {
    $respuestas = [];
    $correctas = 0;
    $incorrectas = 0;
    $conteo_respuestas = 0;
    $tiempo_total_pregunta = 0;
    
    // Si es opción múltiple, inicializar contador para cada opción
    if ($pregunta['tipo'] == 'opcion_multiple' || $pregunta['tipo'] == 'verdadero_falso') {
        if ($pregunta['tipo'] == 'opcion_multiple') {
            foreach ($pregunta['opciones'] as $opcion) {
                $respuestas[$opcion] = 0;
            }
        } else {
            $respuestas['true'] = 0;
            $respuestas['false'] = 0;
        }
    }
    
    // Contar respuestas para esta pregunta
    foreach ($session_data['participantes'] as $participante) {
        foreach ($participante['respuestas'] as $respuesta) {
            if ($respuesta['id_pregunta'] == $pregunta['id']) {
                $conteo_respuestas++;
                $stats['total_respuestas']++;
                
                // Registrar tiempo si está disponible
                if (isset($respuesta['tiempo_respuesta'])) {
                    $tiempo_total_pregunta += $respuesta['tiempo_respuesta'];
                    $total_tiempo += $respuesta['tiempo_respuesta'];
                }
                
                // Contar respuestas según tipo
                if ($pregunta['tipo'] == 'opcion_multiple' || $pregunta['tipo'] == 'verdadero_falso') {
                    $valor_respuesta = $respuesta['respuesta'];
                    
                    // Si existe esta opción en nuestro contador, incrementarla
                    if (isset($respuestas[$valor_respuesta])) {
                        $respuestas[$valor_respuesta]++;
                    }
                    
                    // Verificar si es correcta
                    if (isset($pregunta['respuesta_correcta'])) {
                        if ($valor_respuesta == $pregunta['respuesta_correcta']) {
                            $correctas++;
                            $stats['respuestas_correctas']++;
                        } else {
                            $incorrectas++;
                        }
                    }
                } elseif ($pregunta['tipo'] == 'nube_palabras' || $pregunta['tipo'] == 'palabra_libre') {
                    $palabra = trim($respuesta['respuesta']);
                    if (!empty($palabra)) {
                        if (isset($respuestas[$palabra])) {
                            $respuestas[$palabra]++;
                        } else {
                            $respuestas[$palabra] = 1;
                        }
                    }
                }
            }
        }
    }
    
    // Si hay respuestas para esta pregunta, marcarla como respondida
    if ($conteo_respuestas > 0) {
        $stats['preguntas_respondidas']++;
    }
    
    // Calcular tiempo promedio para esta pregunta
    $tiempo_promedio_pregunta = $conteo_respuestas > 0 ? round($tiempo_total_pregunta / $conteo_respuestas, 1) : 0;
    
    // Ordenar respuestas para nubes de palabras
    if ($pregunta['tipo'] == 'nube_palabras' || $pregunta['tipo'] == 'palabra_libre') {
        arsort($respuestas);
        $respuestas = array_slice($respuestas, 0, 20, true);
    }
    
    // Almacenar resultados de esta pregunta
    $resultados_por_pregunta[] = [
        'id' => $pregunta['id'],
        'pregunta' => $pregunta['texto'] ?? $pregunta['pregunta'],
        'tipo' => $pregunta['tipo'],
        'respuestas' => $respuestas,
        'total_respuestas' => $conteo_respuestas,
        'correctas' => $correctas,
        'incorrectas' => $incorrectas,
        'tiempo_promedio' => $tiempo_promedio_pregunta,
        'respuesta_correcta' => $pregunta['respuesta_correcta'] ?? null
    ];
    
    $total_conteo_respuestas += $conteo_respuestas;
}

// Calcular estadísticas finales
if ($stats['total_respuestas'] > 0) {
    $stats['porcentaje_correctas'] = round(($stats['respuestas_correctas'] / $stats['total_respuestas']) * 100);
}

if ($total_conteo_respuestas > 0) {
    $stats['tiempo_promedio'] = round($total_tiempo / $total_conteo_respuestas, 1);
}

// Finalizar la sesión si se solicitó
if (isset($_GET['finalizar']) && $_GET['finalizar'] == 1) {
    $session_data['estado'] = 'finalizada';
    $session_data['fecha_fin'] = date('Y-m-d\TH:i:s');
    file_put_contents($session_file, json_encode($session_data, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Resumen de Resultados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .bg-gradient-success {
            background: linear-gradient(to right, #1cc88a, #169b6b);
        }
        .pregunta-card {
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .pregunta-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            height: 250px;
            position: relative;
        }
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .word-cloud {
            text-align: center;
            padding: 20px;
            line-height: 2.5;
        }
        .word-cloud span {
            display: inline-block;
            margin: 5px;
        }
        .btn-floating {
            position: fixed;
            right: 20px;
            bottom: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .estado-badge {
            font-size: 1.2rem;
            padding: 8px 15px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard me-2"></i>
                SimpleMenti
            </a>
            <div>
                <span class="badge estado-badge <?php echo $session_data['estado'] == 'activa' ? 'bg-success' : 'bg-secondary'; ?>">
                    <i class="fas <?php echo $session_data['estado'] == 'activa' ? 'fa-play-circle' : 'fa-check-circle'; ?> me-1"></i>
                    <?php echo $session_data['estado'] == 'activa' ? 'Activa' : 'Finalizada'; ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="mb-3"><?php echo htmlspecialchars($test_data['titulo']); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($test_data['descripcion']); ?></p>
                <div class="d-flex align-items-center text-muted mb-2">
                    <i class="fas fa-user me-2"></i>
                    <span><?php echo htmlspecialchars($test_data['autor'] ?? 'Admin'); ?></span>
                    <i class="fas fa-calendar ms-3 me-2"></i>
                    <span><?php echo date('d/m/Y', strtotime($session_data['fecha_inicio'])); ?></span>
                    <i class="fas fa-hashtag ms-3 me-2"></i>
                    <span>Código: <?php echo htmlspecialchars($codigo_sesion); ?></span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <button id="btn-exportar" class="btn btn-success">
                        <i class="fas fa-download me-2"></i> Exportar resultados
                    </button>
                    <a href="presentador.php?codigo=<?php echo $codigo_sesion; ?>" class="btn btn-primary">
                        <i class="fas fa-chalkboard me-2"></i> Volver a la presentación
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen general -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card stats-card h-100 border-left-primary shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <div class="col">
                                <h5 class="text-primary mb-0">Participantes</h5>
                                <h2 class="mb-0"><?php echo $stats['total_participantes']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card h-100 border-left-success shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="col">
                                <h5 class="text-success mb-0">Respuestas correctas</h5>
                                <h2 class="mb-0"><?php echo $stats['porcentaje_correctas']; ?>%</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card h-100 border-left-info shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </div>
                            <div class="col">
                                <h5 class="text-info mb-0">Tiempo promedio</h5>
                                <h2 class="mb-0"><?php echo $stats['tiempo_promedio']; ?>s</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card h-100 border-left-warning shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-warning"></i>
                            </div>
                            <div class="col">
                                <h5 class="text-warning mb-0">Completado</h5>
                                <h2 class="mb-0"><?php echo round(($stats['preguntas_respondidas'] / $stats['total_preguntas']) * 100); ?>%</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados detallados por pregunta -->
        <h2 class="mb-4">Resultados por pregunta</h2>
        
        <?php foreach ($resultados_por_pregunta as $index => $resultado): ?>
        <div class="card pregunta-card shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Pregunta <?php echo $index + 1; ?>: <?php echo htmlspecialchars($resultado['pregunta']); ?></h4>
                    <span class="badge bg-primary"><?php echo $resultado['total_respuestas']; ?> respuestas</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($resultado['tipo'] == 'opcion_multiple' || $resultado['tipo'] == 'verdadero_falso'): ?>
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <canvas id="chart-pregunta-<?php echo $resultado['id']; ?>"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Estadísticas</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Total respuestas
                                        <span class="badge bg-primary rounded-pill"><?php echo $resultado['total_respuestas']; ?></span>
                                    </li>
                                    <?php if (isset($resultado['respuesta_correcta'])): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Respuestas correctas
                                        <span class="badge bg-success rounded-pill"><?php echo $resultado['correctas']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Porcentaje correcto
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo $resultado['total_respuestas'] > 0 ? round(($resultado['correctas'] / $resultado['total_respuestas']) * 100) : 0; ?>%
                                        </span>
                                    </li>
                                    <?php endif; ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Tiempo promedio
                                        <span class="badge bg-warning text-dark rounded-pill"><?php echo $resultado['tiempo_promedio']; ?>s</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <?php if (isset($resultado['respuesta_correcta'])): ?>
                        <div class="alert alert-success">
                            <strong>Respuesta correcta:</strong> <?php echo htmlspecialchars($resultado['respuesta_correcta']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($resultado['tipo'] == 'nube_palabras' || $resultado['tipo'] == 'palabra_libre'): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Palabras más frecuentes</h5>
                                    <span class="badge bg-primary"><?php echo $resultado['total_respuestas']; ?> respuestas</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="word-cloud" id="word-cloud-<?php echo $resultado['id']; ?>">
                                    <!-- La nube de palabras se generará con JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if ($session_data['estado'] == 'activa'): ?>
        <div class="text-center mt-5">
            <a href="?codigo=<?php echo $codigo_sesion; ?>&finalizar=1" class="btn btn-danger btn-lg" 
               onclick="return confirm('¿Estás seguro de que deseas finalizar esta sesión? Esta acción no se puede deshacer.')">
                <i class="fas fa-power-off me-2"></i> Finalizar sesión
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Botón flotante para exportar resultados -->
    <button id="btn-exportar-flotante" class="btn btn-success btn-floating">
        <i class="fas fa-download"></i>
    </button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generar gráficos para preguntas de opción múltiple
            <?php foreach ($resultados_por_pregunta as $resultado): ?>
            <?php if ($resultado['tipo'] == 'opcion_multiple' || $resultado['tipo'] == 'verdadero_falso'): ?>
            generarGrafico(<?php echo $resultado['id']; ?>, <?php 
                echo json_encode(array_keys($resultado['respuestas'])); 
            ?>, <?php 
                echo json_encode(array_values($resultado['respuestas'])); 
            ?>, <?php 
                echo isset($resultado['respuesta_correcta']) ? "'" . addslashes($resultado['respuesta_correcta']) . "'" : 'null'; 
            ?>);
            <?php elseif ($resultado['tipo'] == 'nube_palabras' || $resultado['tipo'] == 'palabra_libre'): ?>
            generarNubePalabras(<?php echo $resultado['id']; ?>, <?php echo json_encode($resultado['respuestas']); ?>);
            <?php endif; ?>
            <?php endforeach; ?>
            
            // Generar gráficos
// Generar gráficos para preguntas de opción múltiple
function generarGrafico(id, labels, data, respuestaCorrecta) {
    const ctx = document.getElementById('chart-pregunta-' + id);
    if (!ctx) return;
    
    // Crear etiquetas numéricas para el gráfico
    const numerosEtiquetas = labels.map((_, index) => (index + 1).toString());
    
    // Determinar colores según si la respuesta es correcta
    const backgroundColors = labels.map(label => {
        if (label === respuestaCorrecta) {
            return 'rgba(75, 192, 192, 0.8)'; // Verde para respuestas correctas
        }
        return 'rgba(54, 162, 235, 0.8)'; // Azul para las demás
    });
    
    const borderColors = labels.map(label => {
        if (label === respuestaCorrecta) {
            return 'rgba(75, 192, 192, 1)';
        }
        return 'rgba(54, 162, 235, 1)';
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: numerosEtiquetas,
            datasets: [{
                label: 'Respuestas',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Agregar leyenda debajo del gráfico
    const container = ctx.parentElement;
    const leyendaDiv = document.createElement('div');
    leyendaDiv.className = 'mt-3 pt-3 border-top';
    
    const leyendaTitle = document.createElement('h6');
    leyendaTitle.textContent = 'Leyenda de opciones:';
    leyendaDiv.appendChild(leyendaTitle);
    
    const opciones = document.createElement('div');
    opciones.className = 'row';
    
    labels.forEach((label, index) => {
        const opcionCol = document.createElement('div');
        opcionCol.className = 'col-md-6 mb-2';
        
        const opcionContent = document.createElement('div');
        opcionContent.className = 'd-flex align-items-center';
        
        const badge = document.createElement('span');
        badge.className = `badge ${label === respuestaCorrecta ? 'bg-success' : 'bg-primary'} me-2`;
        badge.textContent = (index + 1);
        
        const text = document.createElement('span');
        text.textContent = label;
        
        opcionContent.appendChild(badge);
        opcionContent.appendChild(text);
        opcionCol.appendChild(opcionContent);
        opciones.appendChild(opcionCol);
    });
    
    leyendaDiv.appendChild(opciones);
    container.appendChild(leyendaDiv);
}
            
            // Generar nubes de palabras
            function generarNubePalabras(id, datos) {
                const container = document.getElementById('word-cloud-' + id);
                if (!container) return;
                
                // Ordenar por frecuencia
                const palabrasOrdenadas = Object.entries(datos).sort((a, b) => b[1] - a[1]);
                
                // Encontrar el valor máximo para escalar tamaños
                const valores = Object.values(datos);
                const maxValue = valores.length > 0 ? Math.max(...valores) : 0;
                
                // Crear elementos para cada palabra
                palabrasOrdenadas.forEach(([palabra, cantidad]) => {
                    const tamanio = Math.max(16, Math.min(60, (cantidad / maxValue) * 60 + 16));
                    const opacity = 0.5 + (cantidad / maxValue) * 0.5;
                    
                    const span = document.createElement('span');
                    span.textContent = palabra;
                    span.style.fontSize = `${tamanio}px`;
                    span.style.opacity = opacity;
                    span.style.margin = '10px';
                    span.style.display = 'inline-block';
                    span.style.color = getRandomColor();
                    
                    container.appendChild(span);
                });
            }
            
            function getRandomColor() {
                const colors = [
                    '#4e73df', // Azul
                    '#1cc88a', // Verde
                    '#f6c23e', // Amarillo
                    '#e74a3b', // Rojo
                    '#36b9cc', // Cyan
                    '#6f42c1'  // Púrpura
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            }
            
            // Exportar resultados a Excel
            function exportarResultados() {
                fetch('api/exportar_resultados.php?codigo=<?php echo $codigo_sesion; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Crear libro de Excel
                        const wb = XLSX.utils.book_new();
                        
                        // Crear hoja de resumen
                        const wsResumen = XLSX.utils.json_to_sheet(data.resumen);
                        XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");
                        
                        // Crear hoja de participantes
                        const wsParticipantes = XLSX.utils.json_to_sheet(data.participantes);
                        XLSX.utils.book_append_sheet(wb, wsParticipantes, "Participantes");
                        
                        // Crear hoja para cada pregunta
                        data.preguntas.forEach((pregunta, index) => {
                            const wsRespuestas = XLSX.utils.json_to_sheet(pregunta.respuestas);
                            XLSX.utils.book_append_sheet(wb, wsRespuestas, `Pregunta ${index + 1}`);
                        });
                        
                        // Generar el archivo y descargarlo
                        XLSX.writeFile(wb, `SimpleMenti_${data.codigo_sesion}_${new Date().toISOString().slice(0,10)}.xlsx`);
                    } else {
                        alert('Error al exportar resultados: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error exportando resultados:', error);
                    alert('Error al exportar resultados. Intente nuevamente.');
                });
            }
            
            // Botones de exportar
            document.getElementById('btn-exportar').addEventListener('click', exportarResultados);
            document.getElementById('btn-exportar-flotante').addEventListener('click', exportarResultados);
        });
    </script>
</body>
</html>