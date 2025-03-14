<!-- Mostrar respuesta correcta -->
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-lg">
            <div class="card-header bg-gradient-success text-white">
                <h3 class="mb-0 text-center">Respuesta correcta</h3>
            </div>
            <div class="card-body p-4 text-center">
                <h4 class="mb-3"><?php echo htmlspecialchars($pregunta_actual['pregunta']); ?></h4>
                
                <div class="alert alert-success p-4 my-4">
                    <h3>
                        <?php 
                        if ($pregunta_actual['tipo'] == 'verdadero_falso') {
                            echo $pregunta_actual['respuesta_correcta'] ? 'Verdadero' : 'Falso';
                        } else {
                            echo htmlspecialchars($pregunta_actual['respuesta_correcta']); 
                        }
                        ?>
                    </h3>
                </div>
                
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