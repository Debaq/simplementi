<?php
/**
 * Pantalla para mostrar slides del PDF en el presentador
 * Solo se muestra si pdf_enabled está activado
 */

// Verificar si hay PDF habilitado
if (empty($test_data['pdf_enabled']) || !isset($test_data['pdf_images'])) {
    return;
}

$pdf_images = $test_data['pdf_images'];
$total_slides = count($pdf_images);

// Obtener slide actual (por defecto 1)
$slide_actual = isset($respuestas_data['pdf_slide_actual']) ? intval($respuestas_data['pdf_slide_actual']) : 1;

// Validar slide actual
if ($slide_actual < 1 || $slide_actual > $total_slides) {
    $slide_actual = 1;
}

$current_slide_data = $pdf_images[$slide_actual - 1];
?>

<!-- Pantalla de PDF -->
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
