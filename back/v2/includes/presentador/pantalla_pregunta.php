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
        
        <div id="resultados-container" class="mt-4">
            <?php if ($pregunta_actual['tipo'] == 'opcion_multiple' || $pregunta_actual['tipo'] == 'verdadero_falso'): ?>
            <canvas id="resultados-chart" height="250"></canvas>
            <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras'): ?>
            <div id="nube-palabras" class="p-3 text-center"></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <button id="btn-anterior" class="btn btn-secondary" <?php echo $pregunta_actual_index <= 1 ? 'disabled' : ''; ?>>
            <i class="fas fa-arrow-left me-2"></i> Anterior
        </button>
        
        <?php if (isset($pregunta_actual['respuesta_correcta']) && $test_data['configuracion']['mostrar_respuestas'] == 'despues_pregunta'): ?>
        <button id="btn-mostrar-respuesta" class="btn btn-info">
            <i class="fas fa-check-circle me-2"></i> Mostrar respuesta
        </button>
        <?php endif; ?>
        
        <button id="btn-siguiente" class="btn btn-primary" <?php echo $pregunta_actual_index >= $total_preguntas ? 'disabled' : ''; ?>>
            <?php if ($pregunta_actual_index >= $total_preguntas): ?>
            <i class="fas fa-flag-checkered me-2"></i> Finalizar
            <?php else: ?>
            Siguiente <i class="fas fa-arrow-right ms-2"></i>
            <?php endif; ?>
        </button>
    </div>
</div>