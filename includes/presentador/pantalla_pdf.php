<?php
/**
 * Pantalla para mostrar slides del PDF en el presentador
 * Solo se muestra si pdf_enabled está activado
 */

// Verificar si hay PDF habilitado
if (empty($test_data['pdf_enabled']) || !isset($test_data['pdf_images'])) {
    return;
}

// Verificar si hay secuencia configurada
$tiene_secuencia = isset($test_data['pdf_sequence']) && !empty($test_data['pdf_sequence']);
$total_items = $tiene_secuencia ? count($test_data['pdf_sequence']) : 0;
?>

<?php if ($tiene_secuencia): ?>
    <!-- Modo de Presentación Fullscreen -->
    <div class="card shadow-lg mb-4 border-success" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-center text-white p-5">
            <div class="mb-4">
                <i class="fas fa-desktop" style="font-size: 4rem;"></i>
            </div>
            <h2 class="mb-3">Presentación con PDF</h2>
            <p class="lead mb-4">
                Esta presentación tiene <?php echo $total_items; ?> elementos
                (slides y preguntas) organizados en secuencia.
            </p>
            <a href="presentacion_fullscreen.php?codigo=<?php echo $codigo_sesion; ?>&index=0"
               class="btn btn-light btn-lg px-5 py-3"
               style="font-size: 1.3rem;">
                <i class="fas fa-play me-2"></i>
                Iniciar Presentación Fullscreen
            </a>
            <div class="mt-4">
                <small>
                    <i class="fas fa-keyboard me-2"></i>
                    Usa las flechas del teclado para navegar • Presiona F para fullscreen • ESC para salir
                </small>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Vista antigua (si no hay secuencia configurada) -->
    <?php
    $pdf_images = $test_data['pdf_images'];
    $total_slides = count($pdf_images);
    $slide_actual = isset($respuestas_data['pdf_slide_actual']) ? intval($respuestas_data['pdf_slide_actual']) : 1;

    if ($slide_actual < 1 || $slide_actual > $total_slides) {
        $slide_actual = 1;
    }

    $current_slide_data = $pdf_images[$slide_actual - 1];
    ?>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>No hay secuencia configurada.</strong>
        Ve a la pestaña "Preguntas" en el editor para organizar los slides y preguntas.
    </div>

    <div class="card shadow-lg mb-4">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-file-pdf me-2"></i>
                PDF - Slide <?php echo $slide_actual; ?> de <?php echo $total_slides; ?>
            </h4>
            <div class="btn-group">
                <button class="btn btn-light btn-sm" id="prev-slide-btn"
                        <?php echo $slide_actual <= 1 ? 'disabled' : ''; ?>
                        onclick="cambiarSlide(<?php echo $slide_actual - 1; ?>)">
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
                <button class="btn btn-light btn-sm" id="next-slide-btn"
                        <?php echo $slide_actual >= $total_slides ? 'disabled' : ''; ?>
                        onclick="cambiarSlide(<?php echo $slide_actual + 1; ?>)">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 500px;">
            <img src="<?php echo htmlspecialchars($current_slide_data['path']); ?>"
                 class="img-fluid"
                 alt="Slide <?php echo $slide_actual; ?>"
                 style="max-height: 600px; max-width: 100%;">
    </div>
    <div class="card-footer">
        <div class="row align-items-center">
            <div class="col-md-8">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Los participantes ven este slide en sincronía. Usa los botones para navegar.
                </small>
            </div>
            <div class="col-md-4 text-end">
                <input type="range" class="form-range"
                       min="1" max="<?php echo $total_slides; ?>"
                       value="<?php echo $slide_actual; ?>"
                       id="slide-range"
                       onchange="cambiarSlide(this.value)">
                <small class="text-muted">Navegación rápida</small>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarSlide(nuevoSlide) {
    const codigo = '<?php echo $codigo_sesion; ?>';
    window.location.href = `api/cambiar_pdf_slide.php?codigo=${codigo}&slide=${nuevoSlide}`;
}

// Navegación con teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        const prevBtn = document.getElementById('prev-slide-btn');
        if (!prevBtn.disabled) {
            prevBtn.click();
        }
    } else if (e.key === 'ArrowRight') {
        const nextBtn = document.getElementById('next-slide-btn');
        if (!nextBtn.disabled) {
            nextBtn.click();
        }
    }
});
</script>

<style>
.card-body.bg-dark {
    background-color: #1a1a1a !important;
}
</style>
