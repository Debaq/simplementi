<!-- QR code flotante siempre visible -->
<div id="qr-code-fixed">
    <div class="qr-container" id="qr-code-small"></div>
    <div class="mt-2 text-center">
        <span class="badge bg-primary"><?php echo $codigo_sesion; ?></span>
    </div>
</div>

<!-- Botón flotante para exportar resultados -->
<?php if ($pregunta_actual_index > 0): ?>
<button id="btn-exportar" class="btn btn-success btn-floating">
    <i class="fas fa-download"></i>
</button>
<?php endif; ?>