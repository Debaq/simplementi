<?php
/**
 * Lista de preguntas y secuencia con PDF
 * Muestra preguntas y slides del PDF en orden configurable
 * Con Drag & Drop y selector rápido
 */

// Inicializar secuencia si no existe
if (!empty($presentacion_data['pdf_enabled']) && isset($presentacion_data['pdf_images'])) {
    // Si hay PDF pero no hay secuencia, crearla automáticamente
    if (!isset($presentacion_data['pdf_sequence'])) {
        $presentacion_data['pdf_sequence'] = [];

        // Agregar todos los slides primero
        foreach ($presentacion_data['pdf_images'] as $index => $image) {
            $presentacion_data['pdf_sequence'][] = [
                'type' => 'slide',
                'number' => $index + 1
            ];
        }

        // Agregar todas las preguntas al final
        foreach ($presentacion_data['preguntas'] as $pregunta) {
            $presentacion_data['pdf_sequence'][] = [
                'type' => 'question',
                'id' => $pregunta['id']
            ];
        }

        // Guardar la secuencia inicial
        file_put_contents($presentacion_file, json_encode($presentacion_data, JSON_PRETTY_PRINT));
    }
}

// Determinar si hay PDF habilitado
$tiene_pdf = !empty($presentacion_data['pdf_enabled']) && isset($presentacion_data['pdf_images']);
$total_slides = $tiene_pdf ? count($presentacion_data['pdf_images']) : 0;
?>

<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <?php if ($tiene_pdf): ?>
                <i class="fas fa-layer-group me-2"></i> Secuencia: Slides y Preguntas
            <?php else: ?>
                Lista de preguntas
            <?php endif; ?>
        </h6>
        <div>
            <?php if ($tiene_pdf): ?>
                <span class="badge bg-info me-2"><?php echo $total_slides; ?> slides PDF</span>
            <?php endif; ?>
            <span class="badge bg-primary"><?php echo count($presentacion_data['preguntas']); ?> preguntas</span>
        </div>
    </div>
    <div class="card-body">
        <?php if ($tiene_pdf): ?>
            <!-- Vista con PDF: mostrar secuencia -->
            <div class="alert alert-success mb-3">
                <i class="fas fa-hand-pointer me-2"></i>
                <strong>3 formas de reordenar:</strong>
                <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                    <li><strong>Arrastrar:</strong> Toma el ícono <i class="fas fa-grip-vertical"></i> y arrastra el elemento</li>
                    <li><strong>Selector rápido:</strong> Usa el menú desplegable "Poner después de"</li>
                    <li><strong>Botones:</strong> Usa <i class="fas fa-arrow-up"></i> <i class="fas fa-arrow-down"></i> (más lento, recarga página)</li>
                </ul>
            </div>

            <?php if (empty($presentacion_data['pdf_sequence'])): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay secuencia configurada. Recarga la página para generar la secuencia automática.
                </div>
            <?php else: ?>
                <!-- Guardar estado -->
                <input type="hidden" id="presentation-id" value="<?php echo htmlspecialchars($id_presentacion); ?>">

                <div class="list-group" id="sequence-list">
                    <?php
                    $sequence = $presentacion_data['pdf_sequence'];
                    $total_items = count($sequence);

                    foreach ($sequence as $seq_index => $item):
                        $item_json = htmlspecialchars(json_encode($item));

                        if ($item['type'] === 'slide'):
                            $slide_number = $item['number'];
                            $slide_data = $presentacion_data['pdf_images'][$slide_number - 1];
                    ?>
                        <!-- Item de Slide PDF -->
                        <div class="list-group-item seq-item"
                             draggable="true"
                             data-index="<?php echo $seq_index; ?>"
                             data-item='<?php echo $item_json; ?>'
                             style="border-left: 4px solid #007bff; background-color: #e7f3ff; cursor: move;">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <!-- Grip para arrastrar -->
                                <div class="drag-handle" style="cursor: grab; font-size: 1.5rem; color: #007bff;">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>

                                <div class="d-flex align-items-center flex-grow-1 ms-3">
                                    <div class="me-3">
                                        <img src="<?php echo htmlspecialchars(isset($slide_data['thumb_path']) ? $slide_data['thumb_path'] : $slide_data['path']); ?>"
                                             alt="Slide <?php echo $slide_number; ?>"
                                             style="width: 80px; height: auto; border: 1px solid #ddd;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-pdf text-primary me-2"></i>
                                            <strong>Slide <?php echo $slide_number; ?></strong> del PDF
                                        </h6>
                                        <small class="text-muted">Diapositiva del documento</small>
                                    </div>
                                </div>

                                <!-- Selector rápido -->
                                <div class="me-2" style="min-width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <select class="form-select form-select-sm move-to-select" data-current-index="<?php echo $seq_index; ?>">
                                            <option value="-1">Al inicio</option>
                                            <?php for ($i = 0; $i < $total_items; $i++): ?>
                                                <?php if ($i !== $seq_index): ?>
                                                    <option value="<?php echo $i; ?>">Después de #<?php echo ($i + 1); ?></option>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </select>
                                        <button class="btn btn-outline-primary btn-sm move-to-btn" title="Mover">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Botones tradicionales -->
                                <div class="btn-group btn-group-sm">
                                    <?php if ($seq_index > 0): ?>
                                    <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_seq_arriba&seq_index=<?php echo $seq_index; ?>#preguntas"
                                       class="btn btn-outline-secondary" title="Mover arriba">
                                        <i class="fas fa-arrow-up"></i>
                                    </a>
                                    <?php endif; ?>

                                    <?php if ($seq_index < $total_items - 1): ?>
                                    <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_seq_abajo&seq_index=<?php echo $seq_index; ?>#preguntas"
                                       class="btn btn-outline-secondary" title="Mover abajo">
                                        <i class="fas fa-arrow-down"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php
                        elseif ($item['type'] === 'question'):
                            // Buscar la pregunta por ID
                            $question_id = $item['id'];
                            $pregunta = null;
                            foreach ($presentacion_data['preguntas'] as $q) {
                                if ($q['id'] === $question_id) {
                                    $pregunta = $q;
                                    break;
                                }
                            }

                            if ($pregunta):
                    ?>
                        <!-- Item de Pregunta -->
                        <div class="list-group-item seq-item"
                             draggable="true"
                             data-index="<?php echo $seq_index; ?>"
                             data-item='<?php echo $item_json; ?>'
                             style="border-left: 4px solid #dc3545; background-color: #ffe7e7; cursor: move;">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <!-- Grip para arrastrar -->
                                <div class="drag-handle" style="cursor: grab; font-size: 1.5rem; color: #dc3545;">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <span class="badge <?php
                                            switch ($pregunta['tipo']) {
                                                case 'opcion_multiple': echo 'bg-primary'; break;
                                                case 'verdadero_falso': echo 'bg-success'; break;
                                                case 'nube_palabras': echo 'bg-info'; break;
                                                case 'palabra_libre': echo 'bg-warning text-dark'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?> me-2">
                                            <?php
                                            switch ($pregunta['tipo']) {
                                                case 'opcion_multiple': echo 'Opción múltiple'; break;
                                                case 'verdadero_falso': echo 'Verdadero/Falso'; break;
                                                case 'nube_palabras': echo 'Nube de palabras'; break;
                                                case 'palabra_libre': echo 'Respuesta libre'; break;
                                                default: echo $pregunta['tipo'];
                                            }
                                            ?>
                                        </span>
                                        <i class="fas fa-question-circle text-danger me-2"></i>
                                        Pregunta
                                    </h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($pregunta['pregunta']); ?></p>
                                    <?php if (isset($pregunta['respuesta_correcta'])): ?>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Respuesta correcta: <?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>

                                <!-- Selector rápido -->
                                <div class="me-2" style="min-width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <select class="form-select form-select-sm move-to-select" data-current-index="<?php echo $seq_index; ?>">
                                            <option value="-1">Al inicio</option>
                                            <?php for ($i = 0; $i < $total_items; $i++): ?>
                                                <?php if ($i !== $seq_index): ?>
                                                    <option value="<?php echo $i; ?>">Después de #<?php echo ($i + 1); ?></option>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </select>
                                        <button class="btn btn-outline-primary btn-sm move-to-btn" title="Mover">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Botones tradicionales -->
                                <div class="btn-group btn-group-sm">
                                    <a href="editar_pregunta.php?id=<?php echo urlencode($id_presentacion); ?>&pregunta_id=<?php echo $pregunta['id']; ?>"
                                       class="btn btn-outline-primary" title="Editar pregunta">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if ($seq_index > 0): ?>
                                    <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_seq_arriba&seq_index=<?php echo $seq_index; ?>#preguntas"
                                       class="btn btn-outline-secondary" title="Mover arriba">
                                        <i class="fas fa-arrow-up"></i>
                                    </a>
                                    <?php endif; ?>

                                    <?php if ($seq_index < $total_items - 1): ?>
                                    <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=mover_seq_abajo&seq_index=<?php echo $seq_index; ?>#preguntas"
                                       class="btn btn-outline-secondary" title="Mover abajo">
                                        <i class="fas fa-arrow-down"></i>
                                    </a>
                                    <?php endif; ?>

                                    <a href="?id=<?php echo urlencode($id_presentacion); ?>&accion=eliminar_de_seq&seq_index=<?php echo $seq_index; ?>#preguntas"
                                       class="btn btn-outline-danger" title="Quitar de secuencia"
                                       onclick="return confirm('¿Quitar este elemento de la secuencia? (La pregunta no se eliminará)')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php
                            endif;
                        endif;
                    endforeach;
                    ?>
                </div>

                <!-- Indicador de guardado -->
                <div id="save-indicator" class="mt-3" style="display: none;">
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="save-message">Secuencia guardada</span>
                    </div>
                </div>

                <style>
                    .seq-item.dragging {
                        opacity: 0.5;
                    }
                    .seq-item.drag-over {
                        border-top: 3px solid #28a745 !important;
                    }
                    .drag-handle:active {
                        cursor: grabbing;
                    }
                </style>

                <script>
                // Drag & Drop functionality
                (function() {
                    const presentationId = document.getElementById('presentation-id').value;
                    const sequenceList = document.getElementById('sequence-list');
                    const items = sequenceList.querySelectorAll('.seq-item');
                    let draggedElement = null;

                    // Configurar drag & drop
                    items.forEach(item => {
                        item.addEventListener('dragstart', handleDragStart);
                        item.addEventListener('dragend', handleDragEnd);
                        item.addEventListener('dragover', handleDragOver);
                        item.addEventListener('drop', handleDrop);
                        item.addEventListener('dragleave', handleDragLeave);
                    });

                    function handleDragStart(e) {
                        draggedElement = this;
                        this.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/html', this.innerHTML);
                    }

                    function handleDragEnd(e) {
                        this.classList.remove('dragging');
                        items.forEach(item => item.classList.remove('drag-over'));
                    }

                    function handleDragOver(e) {
                        if (e.preventDefault) {
                            e.preventDefault();
                        }
                        e.dataTransfer.dropEffect = 'move';
                        return false;
                    }

                    function handleDrop(e) {
                        if (e.stopPropagation) {
                            e.stopPropagation();
                        }
                        this.classList.remove('drag-over');

                        if (draggedElement !== this) {
                            // Reordenar elementos
                            const allItems = Array.from(sequenceList.querySelectorAll('.seq-item'));
                            const draggedIndex = allItems.indexOf(draggedElement);
                            const targetIndex = allItems.indexOf(this);

                            if (draggedIndex < targetIndex) {
                                this.parentNode.insertBefore(draggedElement, this.nextSibling);
                            } else {
                                this.parentNode.insertBefore(draggedElement, this);
                            }

                            // Guardar nueva secuencia
                            saveSequence();
                        }

                        return false;
                    }

                    function handleDragLeave(e) {
                        this.classList.remove('drag-over');
                    }

                    items.forEach(item => {
                        item.addEventListener('dragenter', function(e) {
                            this.classList.add('drag-over');
                        });
                    });

                    // Selector rápido
                    document.querySelectorAll('.move-to-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const select = this.previousElementSibling;
                            const currentIndex = parseInt(select.dataset.currentIndex);
                            const targetIndex = parseInt(select.value);

                            if (targetIndex === -1) {
                                // Mover al inicio
                                const item = sequenceList.children[currentIndex];
                                sequenceList.insertBefore(item, sequenceList.firstChild);
                            } else {
                                // Mover después del target
                                const item = sequenceList.children[currentIndex];
                                const targetItem = sequenceList.children[targetIndex];
                                sequenceList.insertBefore(item, targetItem.nextSibling);
                            }

                            // Guardar y resetear select
                            saveSequence();
                            select.selectedIndex = 0;
                        });
                    });

                    // Guardar secuencia vía AJAX
                    function saveSequence() {
                        const items = Array.from(sequenceList.querySelectorAll('.seq-item'));
                        const sequence = items.map(item => JSON.parse(item.dataset.item));

                        fetch('api/update_pdf_sequence.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                presentation_id: presentationId,
                                sequence: sequence
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showSaveIndicator('Secuencia guardada correctamente');
                                // Actualizar índices
                                updateIndices();
                            } else {
                                showSaveIndicator('Error: ' + data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showSaveIndicator('Error al guardar', 'danger');
                        });
                    }

                    function updateIndices() {
                        const items = sequenceList.querySelectorAll('.seq-item');
                        items.forEach((item, index) => {
                            item.dataset.index = index;
                            const select = item.querySelector('.move-to-select');
                            if (select) {
                                select.dataset.currentIndex = index;
                            }
                        });
                    }

                    function showSaveIndicator(message, type = 'success') {
                        const indicator = document.getElementById('save-indicator');
                        const alert = indicator.querySelector('.alert');
                        const messageEl = document.getElementById('save-message');

                        alert.className = `alert alert-${type} mb-0`;
                        messageEl.textContent = message;
                        indicator.style.display = 'block';

                        setTimeout(() => {
                            indicator.style.display = 'none';
                        }, 3000);
                    }
                })();
                </script>
            <?php endif; ?>

        <?php else: ?>
            <!-- Vista sin PDF: lista normal de preguntas -->
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
                                        case 'opcion_multiple': echo 'bg-primary'; break;
                                        case 'verdadero_falso': echo 'bg-success'; break;
                                        case 'nube_palabras': echo 'bg-info'; break;
                                        case 'palabra_libre': echo 'bg-warning text-dark'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?> me-2">
                                    <?php
                                    switch ($pregunta['tipo']) {
                                        case 'opcion_multiple': echo 'Opción múltiple'; break;
                                        case 'verdadero_falso': echo 'Verdadero/Falso'; break;
                                        case 'nube_palabras': echo 'Nube de palabras'; break;
                                        case 'palabra_libre': echo 'Respuesta libre'; break;
                                        default: echo $pregunta['tipo'];
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
        <?php endif; ?>
    </div>
</div>
