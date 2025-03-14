<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Lista de preguntas</h6>
        <span class="badge bg-primary"><?php echo count($presentacion_data['preguntas']); ?> preguntas</span>
    </div>
    <div class="card-body">
        <?php if (empty($presentacion_data['preguntas'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> No hay preguntas en esta presentación. 
            Use la pestaña "Agregar pregunta" para crear nuevas preguntas.
        </div>
        <?php else: ?>
        <div class="list-group">
            <?php foreach ($presentacion_data['preguntas'] as $index => $pregunta): ?>
            <div class="list-group-item question-card">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <span class="badge <?php 
                                switch ($pregunta['tipo']) {
                                    case 'opcion_multiple':
                                        echo 'bg-primary';
                                        break;
                                    case 'verdadero_falso':
                                        echo 'bg-success';
                                        break;
                                    case 'nube_palabras':
                                        echo 'bg-info';
                                        break;
                                    case 'palabra_libre':
                                        echo 'bg-warning text-dark';
                                        break;
                                    default:
                                        echo 'bg-secondary';
                                }
                            ?> me-2">
                                <?php 
                                switch ($pregunta['tipo']) {
                                    case 'opcion_multiple':
                                        echo 'Opción múltiple';
                                        break;
                                    case 'verdadero_falso':
                                        echo 'Verdadero/Falso';
                                        break;
                                    case 'nube_palabras':
                                        echo 'Nube de palabras';
                                        break;
                                    case 'palabra_libre':
                                        echo 'Respuesta libre';
                                        break;
                                    default:
                                        echo $pregunta['tipo'];
                                }
                                ?>
                            </span>
                            Pregunta <?php echo $index + 1; ?>
                        </h5>
                        <p class="mb-1"><?php echo htmlspecialchars($pregunta['pregunta']); ?></p>
                        
                        <?php if (isset($pregunta['respuesta_correcta'])): ?>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i> 
                            Respuesta correcta: <?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="editar_pregunta.php?id=<?php echo urlencode($id_presentacion); ?>&pregunta_id=<?php echo $pregunta['id']; ?>" 
                           class="btn btn-outline-primary" title="Editar pregunta">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <?php if ($index > 0): ?>
                        <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_arriba&pregunta_id=<?php echo $pregunta['id']; ?>#preguntas" 
                           class="btn btn-outline-secondary" title="Mover arriba">
                            <i class="fas fa-arrow-up"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($index < count($presentacion_data['preguntas']) - 1): ?>
                        <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_abajo&pregunta_id=<?php echo $pregunta['id']; ?>#preguntas" 
                           class="btn btn-outline-secondary" title="Mover abajo">
                            <i class="fas fa-arrow-down"></i>
                        </a>
                        <?php endif; ?>
                        
                        <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=eliminar_pregunta&pregunta_id=<?php echo $pregunta['id']; ?>#preguntas" 
                           class="btn btn-outline-danger" title="Eliminar pregunta"
                           onclick="return confirm('¿Está seguro de eliminar esta pregunta? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>