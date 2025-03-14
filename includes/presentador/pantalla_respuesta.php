<!-- Mostrar respuesta correcta -->
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-lg">
            <div class="card-header bg-gradient-success text-white">
                <h3 class="mb-0 text-center">Respuesta correcta</h3>
            </div>
            <div class="card-body p-4 text-center">
                <h4 class="mb-3"><?php echo htmlspecialchars($pregunta_actual['pregunta']); ?></h4>
                
                <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' && isset($pregunta_actual['opciones']) && is_array($pregunta_actual['opciones'])): ?>
                <!-- Mostrar gráfico de respuestas para opción múltiple -->
                <div class="mb-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Distribución de respuestas</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="respuesta-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Mostrar todas las opciones con la correcta resaltada -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="list-group">
                            <?php foreach($pregunta_actual['opciones'] as $index => $opcion): ?>
                            <div class="list-group-item <?php echo ($opcion == $pregunta_actual['respuesta_correcta']) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex align-items-center">
                                    <span class="badge <?php echo ($opcion == $pregunta_actual['respuesta_correcta']) ? 'bg-success' : 'bg-secondary'; ?> me-2"><?php echo $index + 1; ?></span>
                                    <span><?php echo htmlspecialchars($opcion); ?></span>
                                    <?php if ($opcion == $pregunta_actual['respuesta_correcta']): ?>
                                    <span class="ms-auto"><i class="fas fa-check-circle text-success"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($pregunta_actual['tipo'] == 'verdadero_falso'): ?>
                <!-- Mostrar gráfico de respuestas para verdadero/falso -->
                <div class="mb-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Distribución de respuestas</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="respuesta-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Mostrar opciones de verdadero/falso con la correcta resaltada -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="list-group">
                            <div class="list-group-item <?php echo ($pregunta_actual['respuesta_correcta'] === 'true' || $pregunta_actual['respuesta_correcta'] === true) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex align-items-center">
                                    <span class="badge <?php echo ($pregunta_actual['respuesta_correcta'] === 'true' || $pregunta_actual['respuesta_correcta'] === true) ? 'bg-success' : 'bg-secondary'; ?> me-2">1</span>
                                    <span>Verdadero</span>
                                    <?php if ($pregunta_actual['respuesta_correcta'] === 'true' || $pregunta_actual['respuesta_correcta'] === true): ?>
                                    <span class="ms-auto"><i class="fas fa-check-circle text-success"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="list-group-item <?php echo ($pregunta_actual['respuesta_correcta'] === 'false' || $pregunta_actual['respuesta_correcta'] === false) ? 'list-group-item-success' : ''; ?>">
                                <div class="d-flex align-items-center">
                                    <span class="badge <?php echo ($pregunta_actual['respuesta_correcta'] === 'false' || $pregunta_actual['respuesta_correcta'] === false) ? 'bg-success' : 'bg-secondary'; ?> me-2">2</span>
                                    <span>Falso</span>
                                    <?php if ($pregunta_actual['respuesta_correcta'] === 'false' || $pregunta_actual['respuesta_correcta'] === false): ?>
                                    <span class="ms-auto"><i class="fas fa-check-circle text-success"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras'): ?>
                <!-- Mostrar nube de palabras -->
                <div class="mb-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Respuestas de los participantes</h5>
                        </div>
                        <div class="card-body">
                            <div id="nube-palabras-respuesta" class="p-3 text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-spinner fa-spin me-2"></i> Cargando respuestas...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Para otros tipos de preguntas -->
                <div class="alert alert-success p-4 my-4">
                    <h3>
                        <?php echo isset($pregunta_actual['respuesta_correcta']) ? htmlspecialchars($pregunta_actual['respuesta_correcta']) : 'No hay respuesta única correcta'; ?>
                    </h3>
                </div>
                <?php endif; ?>
                
                <?php if (isset($pregunta_actual['explicacion'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Explicación</h5>
                    </div>
                    <div class="card-body">
                        <p class="lead"><?php echo htmlspecialchars($pregunta_actual['explicacion']); ?></p>
                        
                        <?php if (isset($pregunta_actual['imagen_explicacion'])): ?>
                        <img src="<?php echo htmlspecialchars($pregunta_actual['imagen_explicacion']); ?>" 
                             class="img-fluid pregunta-imagen mt-3" alt="Imagen explicativa">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <button id="btn-siguiente-despues-respuesta" class="btn btn-primary btn-lg">
                        <?php if ($pregunta_actual_index >= $total_preguntas): ?>
                        <i class="fas fa-flag-checkered me-2"></i> Finalizar y ver resumen
                        <?php else: ?>
                        <i class="fas fa-arrow-right me-2"></i> Siguiente pregunta
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para generar el gráfico o nube de palabras -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' || $pregunta_actual['tipo'] == 'verdadero_falso'): ?>
    // Código para gráficos de opción múltiple o verdadero/falso
    const respuestaChartElement = document.getElementById('respuesta-chart');
    if (respuestaChartElement) {
        // Obtener los datos de las opciones y resultados
        <?php
        // Obtener los resultados de la pregunta actual
        $resultados = [];
        $total_respuestas = 0;
        
        // Contar respuestas para esta pregunta en todos los participantes
        foreach ($session_data['participantes'] as $participante) {
            foreach ($participante['respuestas'] as $respuesta) {
                if ($respuesta['id_pregunta'] == $pregunta_actual['id']) {
                    $valor_respuesta = $respuesta['respuesta'];
                    if (!isset($resultados[$valor_respuesta])) {
                        $resultados[$valor_respuesta] = 0;
                    }
                    $resultados[$valor_respuesta]++;
                    $total_respuestas++;
                }
            }
        }
        ?>
        
        // Preparar datos para el gráfico
        <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' && isset($pregunta_actual['opciones']) && is_array($pregunta_actual['opciones'])): ?>
        const opciones = <?php echo json_encode($pregunta_actual['opciones']); ?>;
        const resultados = <?php echo json_encode($resultados); ?>;
        const respuestaCorrecta = <?php echo isset($pregunta_actual['respuesta_correcta']) ? "'" . addslashes($pregunta_actual['respuesta_correcta']) . "'" : 'null'; ?>;
        
        let labels = [];
        let valores = [];
        
        // Para preguntas con opciones predefinidas
        opciones.forEach((opcion, index) => {
            const numeroOpcion = (index + 1).toString();
            const valor = resultados[opcion] || 0;
            labels.push(numeroOpcion);
            valores.push(valor);
        });
        
        // Determinar colores según si la respuesta es correcta
        const backgroundColors = opciones.map(opcion => {
            if (opcion === respuestaCorrecta) {
                return 'rgba(75, 192, 192, 0.8)'; // Verde para respuestas correctas
            }
            return 'rgba(54, 162, 235, 0.8)'; // Azul para las demás
        });
        
        const borderColors = opciones.map(opcion => {
            if (opcion === respuestaCorrecta) {
                return 'rgba(75, 192, 192, 1)';
            }
            return 'rgba(54, 162, 235, 1)';
        });
        
        <?php elseif ($pregunta_actual['tipo'] == 'verdadero_falso'): ?>
        // Para preguntas de verdadero/falso
        const resultados = <?php echo json_encode($resultados); ?>;
        const respuestaCorrecta = <?php echo isset($pregunta_actual['respuesta_correcta']) ? 
            ($pregunta_actual['respuesta_correcta'] === true || $pregunta_actual['respuesta_correcta'] === 'true' ? "'true'" : "'false'") : 'null'; ?>;
        
        // Número de respuestas para cada opción
        const valorVerdadero = resultados['true'] || 0;
        const valorFalso = resultados['false'] || 0;
        
        const labels = ['1', '2'];
        const valores = [valorVerdadero, valorFalso];
        
        // Colores según la respuesta correcta
        const backgroundColors = [
            (respuestaCorrecta === 'true') ? 'rgba(75, 192, 192, 0.8)' : 'rgba(54, 162, 235, 0.8)',
            (respuestaCorrecta === 'false') ? 'rgba(75, 192, 192, 0.8)' : 'rgba(54, 162, 235, 0.8)'
        ];
        
        const borderColors = [
            (respuestaCorrecta === 'true') ? 'rgba(75, 192, 192, 1)' : 'rgba(54, 162, 235, 1)',
            (respuestaCorrecta === 'false') ? 'rgba(75, 192, 192, 1)' : 'rgba(54, 162, 235, 1)'
        ];
        <?php endif; ?>
        
        // Crear el gráfico
        new Chart(respuestaChartElement, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Respuestas',
                    data: valores,
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
    }
    <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras'): ?>
    // Código para la nube de palabras
    const nubeContainer = document.getElementById('nube-palabras-respuesta');
    if (nubeContainer) {
        <?php
        // Obtener las respuestas para la nube de palabras
        $palabras = [];
        
        // Contar frecuencia de cada palabra
        foreach ($session_data['participantes'] as $participante) {
            foreach ($participante['respuestas'] as $respuesta) {
                if ($respuesta['id_pregunta'] == $pregunta_actual['id']) {
                    $palabra = trim($respuesta['respuesta']);
                    if (!empty($palabra)) {
                        if (!isset($palabras[$palabra])) {
                            $palabras[$palabra] = 0;
                        }
                        $palabras[$palabra]++;
                    }
                }
            }
        }
        
        // Ordenar por frecuencia y limitar a 20 palabras más frecuentes
        arsort($palabras);
        $palabras = array_slice($palabras, 0, 20, true);
        ?>
        
        const palabras = <?php echo json_encode($palabras); ?>;
        
        // Vaciar el contenedor
        nubeContainer.innerHTML = '';
        
        // Si no hay palabras, mostrar mensaje
        if (Object.keys(palabras).length === 0) {
            nubeContainer.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i> No hay respuestas para mostrar</div>';
            return;
        }
        
        // Encontrar el valor máximo para escalar tamaños
        const maxValue = Math.max(...Object.values(palabras));
        
        // Crear elementos para cada palabra
        for (const [palabra, cantidad] of Object.entries(palabras)) {
            const tamanio = Math.max(16, Math.min(60, (cantidad / maxValue) * 60 + 16));
            const opacity = 0.5 + (cantidad / maxValue) * 0.5;
            
            const span = document.createElement('span');
            span.textContent = palabra;
            span.style.fontSize = `${tamanio}px`;
            span.style.opacity = opacity;
            span.style.margin = '10px';
            span.style.display = 'inline-block';
            span.style.color = getRandomColor();
            
            nubeContainer.appendChild(span);
        }
    }
    
    // Función para colores aleatorios
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
    <?php endif; ?>
});
</script>