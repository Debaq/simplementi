<!-- Formulario para nube de palabras (inicialmente oculto) -->
<div id="form-nube_palabras" class="question-form" style="display: none;">
    <h5 class="mb-3">Nueva pregunta tipo nube de palabras</h5>
    <form action="guardar_pregunta.php" method="post">
        <input type="hidden" name="id_presentacion" value="<?php echo htmlspecialchars($id_presentacion); ?>">
        <input type="hidden" name="tipo_pregunta" value="nube_palabras">
        
        <div class="mb-3">
            <label for="pregunta_texto_np" class="form-label">Texto de la pregunta <span class="text-danger">*</span></label>
            <textarea class="form-control" id="pregunta_texto_np" name="pregunta_texto" rows="2" required></textarea>
        </div>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="mostrar_tiempo_real" name="mostrar_tiempo_real" value="1" checked>
                <label class="form-check-label" for="mostrar_tiempo_real">Mostrar resultados en tiempo real</label>
            </div>
            <div class="form-text">Si está activado, los participantes verán la nube de palabras actualizándose en tiempo real.</div>
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