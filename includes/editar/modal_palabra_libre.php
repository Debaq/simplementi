<!-- Formulario para respuesta libre (inicialmente oculto) -->
<div id="form-palabra_libre" class="question-form" style="display: none;">
    <h5 class="mb-3">Nueva pregunta de respuesta libre</h5>
    <form action="guardar_pregunta.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_presentacion" value="<?php echo htmlspecialchars($id_presentacion); ?>">
        <input type="hidden" name="tipo_pregunta" value="palabra_libre">
        
        <div class="mb-3">
            <label for="pregunta_texto_pl" class="form-label">Texto de la pregunta <span class="text-danger">*</span></label>
            <textarea class="form-control" id="pregunta_texto_pl" name="pregunta_texto" rows="2" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="imagen_pregunta" class="form-label">Imagen para la pregunta (opcional)</label>
            <input type="file" class="form-control" id="imagen_pregunta" name="imagen_pregunta" accept="image/*">
            <div class="form-text">Sube una imagen que acompañe a la pregunta (máx. 2MB).</div>
        </div>
        
        <div class="mb-3">
            <label for="explicacion_pl" class="form-label">Explicación (opcional)</label>
            <textarea class="form-control" id="explicacion_pl" name="explicacion" rows="2"></textarea>
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