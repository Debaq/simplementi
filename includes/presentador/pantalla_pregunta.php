<!-- Tarjeta de la pregunta actual -->
<div class="card shadow-lg mb-4">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Pregunta <?php echo $pregunta_actual_index; ?> de <?php echo $total_preguntas; ?></h4>
        <?php if (isset($test_data['configuracion']['tiempo_por_pregunta']) && $test_data['configuracion']['tiempo_por_pregunta'] > 0): ?>
        <div class="countdown-timer" id="countdown">
            <?php echo $test_data['configuracion']['tiempo_por_pregunta']; ?>s
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <h3 class="mb-4"><?php echo htmlspecialchars($pregunta_actual['pregunta']); ?></h3>
        
        <?php if (isset($pregunta_actual['imagen'])): ?>
        <div class="text-center mb-4">
            <img src="<?php echo htmlspecialchars($pregunta_actual['imagen']); ?>" 
                 class="img-fluid pregunta-imagen" alt="Imagen de la pregunta">
        </div>
        <?php endif; ?>
        
        <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' && isset($pregunta_actual['opciones']) && is_array($pregunta_actual['opciones'])): ?>
        <!-- Mostrar leyenda numerada de opciones para opción múltiple -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h5 class="mb-0">Opciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach($pregunta_actual['opciones'] as $index => $opcion): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                    <span><?php echo htmlspecialchars($opcion); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($pregunta_actual['tipo'] == 'verdadero_falso'): ?>
        <!-- Mostrar opciones para verdadero/falso -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h5 class="mb-0">Opciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">1</span>
                                    <span>Verdadero</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">2</span>
                                    <span>Falso</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Contenedor de resultados (gráfico o nube de palabras) -->
        <?php 
        // Determinar si se debe mostrar el contenedor basado en el tipo de pregunta
        $mostrar_contenedor = false;
        
        // Para nubes de palabras, verificar la propiedad específica de la pregunta
        if ($pregunta_actual['tipo'] == 'nube_palabras') {
            // Si existe mostrar_tiempo_real en la pregunta, usar ese valor
            if (isset($pregunta_actual['mostrar_tiempo_real'])) {
                $mostrar_contenedor = $pregunta_actual['mostrar_tiempo_real'];
            } else {
                // Si no, verificar si existe una configuración global para nubes de palabras
                if (isset($test_data['configuracion']['mostrar_nube_palabras_tiempo_real'])) {
                    $mostrar_contenedor = $test_data['configuracion']['mostrar_nube_palabras_tiempo_real'];
                } else {
                    // Si no hay configuración específica, usar la configuración general de gráficos
                    $mostrar_contenedor = isset($test_data['configuracion']['mostrar_grafico_durante_pregunta']) ? 
                                        $test_data['configuracion']['mostrar_grafico_durante_pregunta'] : false;
                }
            }
        } else {
            // Para otros tipos de preguntas, usar la configuración general
            $mostrar_contenedor = isset($test_data['configuracion']['mostrar_grafico_durante_pregunta']) ? 
                                $test_data['configuracion']['mostrar_grafico_durante_pregunta'] : false;
        }
        ?>
        
        <div id="resultados-container" class="mt-4" <?php if (!$mostrar_contenedor): ?>style="display: none;"<?php endif; ?>>
            <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' || $pregunta_actual['tipo'] == 'verdadero_falso'): ?>
            <canvas id="resultados-chart" height="250"></canvas>
            <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras'): ?>
            <div id="nube-palabras" class="p-3 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Las respuestas aparecerán aquí como una nube de palabras.
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($pregunta_actual['tipo'] == 'nube_palabras' && !$mostrar_contenedor): ?>
        <!-- Mensaje para nubes de palabras que no se muestran en tiempo real -->
        <div class="alert alert-info mt-3">
            <i class="fas fa-cloud me-2"></i> Las respuestas se están recolectando y se mostrarán en la pantalla de resultados.
            <div class="mt-2">
                <div class="progress">
                    <div id="progress-recoleccion" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <button id="btn-anterior" class="btn btn-secondary" <?php echo $pregunta_actual_index <= 1 ? 'disabled' : ''; ?>>
            <i class="fas fa-arrow-left me-2"></i> Anterior
        </button>
        
        <?php if (isset($pregunta_actual['respuesta_correcta']) && $test_data['configuracion']['mostrar_respuestas'] == 'despues_pregunta'): ?>
        <button id="btn-mostrar-respuesta" class="btn btn-info">
            <i class="fas fa-check-circle me-2"></i> Mostrar respuesta
        </button>
        <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras' && !$mostrar_contenedor): ?>
        <button id="btn-mostrar-resultados" class="btn btn-info">
            <i class="fas fa-cloud me-2"></i> Ver resultados
        </button>
        <?php endif; ?>
        
        <button id="btn-siguiente" class="btn btn-primary">
            <?php if ($pregunta_actual_index >= $total_preguntas): ?>
            <i class="fas fa-flag-checkered me-2"></i> Finalizar
            <?php else: ?>
            Siguiente <i class="fas fa-arrow-right ms-2"></i>
            <?php endif; ?>
        </button>
    </div>
</div>