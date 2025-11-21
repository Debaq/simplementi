<?php
/**
 * Pantalla para mostrar slides del PDF en el participante
 * Se muestra en sincronía con el presentador
 */

// Verificar si hay PDF habilitado
if (empty($test_data['pdf_enabled']) || !isset($test_data['pdf_images'])) {
    return;
}

$pdf_images = $test_data['pdf_images'];
$total_slides = count($pdf_images);

// Obtener slide actual desde la sesión
$slide_actual = isset($respuestas_data['pdf_slide_actual']) ? intval($respuestas_data['pdf_slide_actual']) : 1;

// Validar slide actual
if ($slide_actual < 1 || $slide_actual > $total_slides) {
    $slide_actual = 1;
}

$current_slide_data = $pdf_images[$slide_actual - 1];
?>

<!-- Pantalla de PDF para participante -->
<div class="card shadow-lg mb-4" id="pdf-viewer-card">
    <div class="card-header bg-gradient-primary text-white text-center">
        <h5 class="mb-0">
            <i class="fas fa-file-pdf me-2"></i>
            Slide <?php echo $slide_actual; ?> de <?php echo $total_slides; ?>
        </h5>
    </div>
    <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px;">
        <img src="<?php echo htmlspecialchars($current_slide_data['path']); ?>"
             class="img-fluid"
             alt="Slide <?php echo $slide_actual; ?>"
             id="pdf-slide-image"
             style="max-width: 100%; max-height: 600px;">
    </div>
    <div class="card-footer text-center">
        <small class="text-muted">
            <i class="fas fa-sync-alt me-1"></i>
            Slide sincronizado con el presentador
        </small>
    </div>
</div>

<style>
#pdf-viewer-card .card-body.bg-dark {
    background-color: #1a1a1a !important;
}
</style>

<script>
// Actualizar slide del PDF en tiempo real
(function() {
    let lastSlide = <?php echo $slide_actual; ?>;

    function checkPdfSlide() {
        // Verificar si el slide cambió
        fetch('api/get_pregunta_actual.php?codigo=<?php echo $codigo_sesion; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.pdf_slide && data.pdf_slide !== lastSlide) {
                    lastSlide = data.pdf_slide;
                    // Recargar la página para actualizar el slide
                    location.reload();
                }
            })
            .catch(error => console.error('Error al verificar slide:', error));
    }

    // Verificar cada 2 segundos
    setInterval(checkPdfSlide, 2000);
})();
</script>
