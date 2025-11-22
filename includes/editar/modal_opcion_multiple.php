<!-- Formulario para opción múltiple (inicialmente oculto) -->
<div id="form-opcion_multiple" class="question-form" style="display: none;">
    <h5 class="mb-3">Nueva pregunta de opción múltiple</h5>
    <form action="guardar_pregunta.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_presentacion" value="<?php echo htmlspecialchars($id_presentacion); ?>">
        <input type="hidden" name="tipo_pregunta" value="opcion_multiple">
        
        <div class="mb-3">
            <label for="pregunta_texto_om" class="form-label">Texto de la pregunta <span class="text-danger">*</span></label>
            <textarea class="form-control" id="pregunta_texto_om" name="pregunta_texto" rows="2" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="imagen_pregunta" class="form-label">Imagen para la pregunta (opcional)</label>
            <input type="file" class="form-control" id="imagen_pregunta" name="imagen_pregunta" accept="image/*">
            <div class="form-text">Sube una imagen que acompañe a la pregunta (máx. 2MB).</div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Opciones <span class="text-danger">*</span></label>
            <div class="form-text mb-2">Seleccione la respuesta correcta con el botón circular. El feedback es opcional y específico para cada opción.</div>
            <div id="opciones-container">
                <div class="opcion-item mb-3 p-3 border rounded">
                    <div class="row align-items-center mb-2">
                        <div class="col-auto">
                            <span class="badge bg-primary">A</span>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" name="opciones[]" placeholder="Texto de la opción" required>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="0" id="correcta_0">
                                <label class="form-check-label" for="correcta_0">Correcta</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto" style="width: 50px;"></div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="feedbacks[]" placeholder="Feedback para esta opción (opcional)">
                            <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                        </div>
                    </div>
                </div>
                <div class="opcion-item mb-3 p-3 border rounded">
                    <div class="row align-items-center mb-2">
                        <div class="col-auto">
                            <span class="badge bg-primary">B</span>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" name="opciones[]" placeholder="Texto de la opción" required>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="1" id="correcta_1">
                                <label class="form-check-label" for="correcta_1">Correcta</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto" style="width: 50px;"></div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="feedbacks[]" placeholder="Feedback para esta opción (opcional)">
                            <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                        </div>
                    </div>
                </div>
                <div class="opcion-item mb-3 p-3 border rounded">
                    <div class="row align-items-center mb-2">
                        <div class="col-auto">
                            <span class="badge bg-primary">C</span>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" name="opciones[]" placeholder="Texto de la opción" required>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="2" id="correcta_2">
                                <label class="form-check-label" for="correcta_2">Correcta</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto" style="width: 50px;"></div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="feedbacks[]" placeholder="Feedback para esta opción (opcional)">
                            <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                        </div>
                    </div>
                </div>
                <div class="opcion-item mb-3 p-3 border rounded">
                    <div class="row align-items-center mb-2">
                        <div class="col-auto">
                            <span class="badge bg-primary">D</span>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" name="opciones[]" placeholder="Texto de la opción" required>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="respuesta_correcta_index" value="3" id="correcta_3">
                                <label class="form-check-label" for="correcta_3">Correcta</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto" style="width: 50px;"></div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="feedbacks[]" placeholder="Feedback para esta opción (opcional)">
                            <small class="text-muted">Este mensaje se mostrará a quien elija esta opción</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <button type="button" id="agregar-opcion" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> Agregar opción
                </button>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="explicacion_om" class="form-label">Explicación (opcional)</label>
            <textarea class="form-control" id="explicacion_om" name="explicacion" rows="2"></textarea>
            <div class="form-text">Se mostrará después de que los participantes respondan.</div>
        </div>
        
        <div class="mb-3">
            <label for="imagen_explicacion" class="form-label">Imagen para la explicación (opcional)</label>
            <input type="file" class="form-control" id="imagen_explicacion" name="imagen_explicacion" accept="image/*">
            <div class="form-text">Sube una imagen para acompañar la explicación (máx. 2MB).</div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="button" class="btn btn-secondary cancel-question">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Guardar pregunta
            </button>
        </div>
    </form>
</div>