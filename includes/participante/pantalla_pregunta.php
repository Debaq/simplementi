<div class="container question-container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?php echo htmlspecialchars($test_data['titulo']); ?></h3>
                <span class="badge bg-light text-dark">
                    Pregunta <?php echo $pregunta_actual_index; ?> de <?php echo count($test_data['preguntas']); ?>
                </span>
            </div>
        </div>
        
        <div class="card-body p-4">
            <?php if ($respuesta_enviada == 1 || $ya_respondio): ?>
            <!-- Mostrar mensaje de respuesta enviada -->
            <div class="text-center p-4">
                <i class="fas fa-check-circle text-success mb-4" style="font-size: 3rem;"></i>
                <h4 class="text-success mb-3">¡Respuesta enviada!</h4>
                <p class="lead">Esperando la siguiente pregunta...</p>
                <div class="spinner-border text-primary mt-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Mostrar pregunta actual -->
            <?php if ($tiempo_limite > 0): ?>
            <div class="countdown-timer" id="countdown">
                <?php echo $tiempo_limite; ?>s
            </div>
            <?php endif; ?>
            
            <h4 class="mb-4"><?php echo htmlspecialchars($pregunta_actual['pregunta']); ?></h4>
            
            <?php if (isset($pregunta_actual['imagen'])): ?>
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($pregunta_actual['imagen']); ?>" 
                     class="img-fluid pregunta-imagen" alt="Imagen de la pregunta">
            </div>
            <?php endif; ?>
            
            <!-- Formulario según el tipo de pregunta -->
            <form id="form-respuesta" method="post" action="api/guardar_respuesta.php">
                <input type="hidden" name="codigo_sesion" value="<?php echo htmlspecialchars($codigo_sesion); ?>">
                <input type="hidden" name="id_pregunta" value="<?php echo $pregunta_actual['id']; ?>">
                <input type="hidden" name="id_participante" value="<?php echo $participante_id; ?>">
                <input type="hidden" id="tiempo_respuesta" name="tiempo_respuesta" value="0">
                
                <?php if ($pregunta_actual['tipo'] == 'opcion_multiple'): ?>
                <div class="row mb-4">
                    <?php foreach ($pregunta_actual['opciones'] as $index => $opcion): ?>
                    <div class="col-12 mb-3">
                        <div class="opcion-card card" data-value="<?php echo htmlspecialchars($opcion); ?>">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input opcion-radio" type="radio" name="respuesta" 
                                           id="opcion<?php echo $index; ?>" value="<?php echo htmlspecialchars($opcion); ?>" required>
                                    <label class="form-check-label w-100" for="opcion<?php echo $index; ?>">
                                        <?php echo htmlspecialchars($opcion); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php elseif ($pregunta_actual['tipo'] == 'verdadero_falso'): ?>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="opcion-card card" data-value="true">
                            <div class="card-body text-center py-4">
                                <div class="form-check">
                                    <input class="form-check-input opcion-radio" type="radio" name="respuesta" 
                                           id="opcion-verdadero" value="true" required>
                                    <label class="form-check-label w-100" for="opcion-verdadero">
                                        <i class="fas fa-check text-success me-2"></i> Verdadero
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="opcion-card card" data-value="false">
                            <div class="card-body text-center py-4">
                                <div class="form-check">
                                    <input class="form-check-input opcion-radio" type="radio" name="respuesta" 
                                           id="opcion-falso" value="false" required>
                                    <label class="form-check-label w-100" for="opcion-falso">
                                        <i class="fas fa-times text-danger me-2"></i> Falso
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($pregunta_actual['tipo'] == 'nube_palabras' || $pregunta_actual['tipo'] == 'palabra_libre'): ?>
                <div class="text-input-container mb-4">
                    <label for="respuesta-texto" class="form-label">Tu respuesta:</label>
                    <input type="text" class="form-control form-control-lg" id="respuesta-texto" 
                           name="respuesta" maxlength="100" required autocomplete="off"
                           placeholder="Escribe tu respuesta aquí...">
                    <div class="character-counter">
                        <span id="char-count">0</span>/100
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i> Enviar respuesta
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        
        <div class="card-footer text-center text-muted">
            <div class="d-flex justify-content-between">
                <small>Sesión: <?php echo htmlspecialchars($codigo_sesion); ?></small>
                <small>ID: <?php echo htmlspecialchars($participante_id); ?></small>
            </div>
        </div>
    </div>
</div>