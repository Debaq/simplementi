<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si hay un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

// Si no hay código, mostrar lista de presentaciones disponibles
if (empty($codigo_sesion)) {
    // Verificar que el archivo exista
    if (!file_exists('data/preguntas.json')) {
        echo "Error: El archivo preguntas.json no existe. Comprueba que la ruta 'data/preguntas.json' sea correcta y que tengas permisos.";
        exit;
    }
    
    $preguntas_json = file_get_contents('data/preguntas.json');
    if ($preguntas_json === false) {
        echo "Error: No se pudo leer el archivo preguntas.json. Comprueba los permisos.";
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo "Error: El archivo preguntas.json no tiene un formato JSON válido. Contenido:<br>";
        echo "<pre>" . htmlspecialchars($preguntas_json) . "</pre>";
        exit;
    }
    
    if (!isset($preguntas_data['presentaciones']) || !is_array($preguntas_data['presentaciones'])) {
        echo "Error: El archivo preguntas.json no contiene la estructura esperada. Falta la clave 'presentaciones' o no es un array.";
        echo "<pre>" . print_r($preguntas_data, true) . "</pre>";
        exit;
    }
    
    $presentaciones = $preguntas_data['presentaciones'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Presentador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Seleccione una presentación</h3>
            </div>
            <div class="card-body">
                <?php if (count($presentaciones) > 0): ?>
                <div class="list-group">
                    <?php foreach ($presentaciones as $presentacion): ?>
                    <a href="index.php?nombredeltest=<?php echo htmlspecialchars($presentacion['id']); ?>" class="list-group-item list-group-item-action">
                        <h5 class="mb-1"><?php echo htmlspecialchars($presentacion['titulo']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($presentacion['descripcion']); ?></p>
                        <small>ID: <?php echo htmlspecialchars($presentacion['id']); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    No hay presentaciones disponibles. Por favor, agregue algunas en el archivo preguntas.json.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
} else {
    echo "Buscando sesión con código: " . htmlspecialchars($codigo_sesion) . "<br>";
    
    // Buscar la sesión en el archivo de respuestas
    if (!file_exists('data/respuestas.json')) {
        echo "Error: El archivo respuestas.json no existe.";
        exit;
    }
    
    $respuestas_json = file_get_contents('data/respuestas.json');
    if ($respuestas_json === false) {
        echo "Error: No se pudo leer el archivo respuestas.json.";
        exit;
    }
    
    $respuestas_data = json_decode($respuestas_json, true);
    if ($respuestas_data === null) {
        echo "Error: El archivo respuestas.json no tiene un formato JSON válido.";
        echo "<pre>" . htmlspecialchars($respuestas_json) . "</pre>";
        exit;
    }
    
    $sesion = null;
    foreach ($respuestas_data['sesiones'] as $s) {
        if ($s['codigo_sesion'] == $codigo_sesion) {
            $sesion = $s;
            break;
        }
    }
    
    if (!$sesion) {
        echo "Error: Sesión no encontrada con código '" . htmlspecialchars($codigo_sesion) . "'.";
        echo "<pre>Sesiones disponibles: " . print_r($respuestas_data['sesiones'], true) . "</pre>";
        exit;
    }
    
    // Obtener información de la presentación
    if (!file_exists('data/preguntas.json')) {
        echo "Error: El archivo preguntas.json no existe.";
        exit;
    }
    
    $preguntas_json = file_get_contents('data/preguntas.json');
    if ($preguntas_json === false) {
        echo "Error: No se pudo leer el archivo preguntas.json.";
        exit;
    }
    
    $preguntas_data = json_decode($preguntas_json, true);
    if ($preguntas_data === null) {
        echo "Error: El archivo preguntas.json no tiene un formato JSON válido.";
        exit;
    }
    
    $presentacion = null;
    foreach ($preguntas_data['presentaciones'] as $p) {
        if ($p['id'] == $sesion['id_presentacion']) {
            $presentacion = $p;
            break;
        }
    }
    
    if (!$presentacion) {
        echo "Error: Presentación no encontrada con ID '" . htmlspecialchars($sesion['id_presentacion']) . "'.";
        exit;
    }
    
    $pregunta_actual_index = $sesion['pregunta_actual'] - 1;
    $total_preguntas = count($presentacion['preguntas']);
    if ($pregunta_actual_index < 0 || $pregunta_actual_index >= $total_preguntas) {
        echo "Error: Índice de pregunta actual inválido.";
        exit;
    }
    
    $pregunta_actual = $presentacion['preguntas'][$pregunta_actual_index];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Presentador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3><?php echo htmlspecialchars($presentacion['titulo']); ?></h3>
                        <span class="badge bg-warning text-dark">Código: <?php echo htmlspecialchars($codigo_sesion); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5>Pregunta <?php echo $sesion['pregunta_actual']; ?> de <?php echo $total_preguntas; ?></h5>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo ($sesion['pregunta_actual'] / $total_preguntas) * 100; ?>%" 
                                    aria-valuenow="<?php echo $sesion['pregunta_actual']; ?>" aria-valuemin="1" aria-valuemax="<?php echo $total_preguntas; ?>"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4><?php echo htmlspecialchars($pregunta_actual['pregunta']); ?></h4>
                        </div>
                        
                        <div id="resultados-container">
                            <canvas id="resultados-chart"></canvas>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button id="btn-anterior" class="btn btn-secondary" <?php echo $sesion['pregunta_actual'] <= 1 ? 'disabled' : ''; ?>>Anterior</button>
                            <button id="btn-siguiente" class="btn btn-primary" <?php echo $sesion['pregunta_actual'] >= $total_preguntas ? 'disabled' : ''; ?>>Siguiente</button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4>Link para participantes</h4>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" id="link-participantes" class="form-control" 
                                value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?codigo=$codigo_sesion"; ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="btn-copiar">Copiar</button>
                        </div>
                        <div class="mt-2">
                            <p>O comparta el código: <strong><?php echo htmlspecialchars($codigo_sesion); ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>Participantes <span id="contador-participantes" class="badge bg-light text-dark">0</span></h4>
                    </div>
                    <div class="card-body">
                        <ul id="lista-participantes" class="list-group">
                            <li class="list-group-item">Esperando participantes...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Datos importantes para el script
        const codigoSesion = '<?php echo $codigo_sesion; ?>';
        const preguntaActual = <?php echo $sesion['pregunta_actual']; ?>;
        const totalPreguntas = <?php echo $total_preguntas; ?>;
        const tipoPreguntaActual = '<?php echo $pregunta_actual['tipo']; ?>';
        
        // Elementos DOM
        const btnAnterior = document.getElementById('btn-anterior');
        const btnSiguiente = document.getElementById('btn-siguiente');
        const btnCopiar = document.getElementById('btn-copiar');
        const linkParticipantes = document.getElementById('link-participantes');
        const contadorParticipantes = document.getElementById('contador-participantes');
        const listaParticipantes = document.getElementById('lista-participantes');
        
        // Copiar link
        btnCopiar.addEventListener('click', () => {
            linkParticipantes.select();
            document.execCommand('copy');
            btnCopiar.textContent = 'Copiado';
            setTimeout(() => {
                btnCopiar.textContent = 'Copiar';
            }, 2000);
        });
        
        // Versión simplificada: actualización manual
        btnAnterior.addEventListener('click', () => {
            if (preguntaActual > 1) {
                window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual - 1);
            }
        });
        
        btnSiguiente.addEventListener('click', () => {
            if (preguntaActual < totalPreguntas) {
                window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual + 1);
            }
        });
        
        // Funciones simplificadas para mostrar resultados
        function actualizarResultados() {
            fetch('api/get_resultados.php?codigo=' + codigoSesion + '&pregunta=' + preguntaActual)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar contador de participantes
                        contadorParticipantes.textContent = data.total_participantes;
                        
                        // Actualizar lista de participantes
                        if (data.total_participantes > 0) {
                            listaParticipantes.innerHTML = '';
                            data.participantes.forEach(p => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item';
                                li.textContent = 'Participante ' + p.id_participante;
                                listaParticipantes.appendChild(li);
                            });
                        }
                        
                        // Actualizar gráfico según el tipo de pregunta
                        if (tipoPreguntaActual === 'opcion_multiple') {
                            actualizarGraficoOpcionMultiple(data.resultados);
                        } else {
                            actualizarNubePalabras(data.resultados);
                        }
                    } else {
                        console.error('Error al obtener resultados:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud fetch:', error);
                });
        }
        
        function actualizarGraficoOpcionMultiple(resultados) {
            const ctx = document.getElementById('resultados-chart').getContext('2d');
            
            if (window.myChart) {
                window.myChart.destroy();
            }
            
            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(resultados),
                    datasets: [{
                        label: 'Respuestas',
                        data: Object.values(resultados),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        function actualizarNubePalabras(resultados) {
            // Simulación simple de una nube de palabras
            const container = document.getElementById('resultados-container');
            container.innerHTML = '<div id="nube-palabras" class="p-3"></div>';
            
            const nubeDiv = document.getElementById('nube-palabras');
            for (const [palabra, cantidad] of Object.entries(resultados)) {
                const span = document.createElement('span');
                span.textContent = palabra;
                span.className = 'm-2 d-inline-block';
                span.style.fontSize = `${Math.max(16, cantidad * 5)}px`;
                nubeDiv.appendChild(span);
            }
        }
        
        // Iniciar actualización periódica
        actualizarResultados();
        setInterval(actualizarResultados, 3000);
    </script>
</body>
</html>
<?php
}
?>
