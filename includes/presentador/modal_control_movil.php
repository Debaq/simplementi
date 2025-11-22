<!-- Modal de Control Móvil -->
<div class="modal fade" id="modalControlMovil" tabindex="-1" aria-labelledby="modalControlMovilLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Paso 1: Advertencia de Seguridad -->
            <div id="modal-advertencia" class="modal-step">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalControlMovilLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Advertencia de Seguridad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">Este QR te dará control TOTAL de la presentación</h6>
                        <p class="mb-0">Solo debes usarlo desde tu dispositivo personal.</p>
                    </div>

                    <ul class="list-unstyled mb-3">
                        <li class="mb-2">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <strong>NO</strong> proyectes esta pantalla
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <strong>NO</strong> compartas este código
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times-circle text-danger me-2"></i>
                            <strong>NO</strong> permitas que otros lo escaneen
                        </li>
                    </ul>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        El código QR expirará en <strong>30 segundos</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-generar-qr">
                        <i class="fas fa-check me-2"></i>
                        Entiendo, Generar QR
                    </button>
                </div>
            </div>

            <!-- Paso 2: QR Generado -->
            <div id="modal-qr" class="modal-step" style="display: none;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode me-2"></i>
                        Escanea desde tu Móvil
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-eye-slash me-2"></i>
                        <strong>Mantén esta pantalla privada</strong> - No la proyectes
                    </div>

                    <!-- QR Code -->
                    <div class="mb-3">
                        <img id="qr-image" src="" alt="QR Code" class="img-fluid" style="max-width: 300px; border: 3px solid #dee2e6; border-radius: 8px;">
                    </div>

                    <!-- Código alternativo -->
                    <div class="mb-3">
                        <p class="mb-1">O ingresa este código manualmente:</p>
                        <h3 class="text-monospace">
                            <span id="pair-code" class="badge bg-dark p-3" style="font-size: 1.5rem; letter-spacing: 3px;">----</span>
                        </h3>
                    </div>

                    <!-- Contador -->
                    <div class="progress mb-3" style="height: 30px;">
                        <div id="countdown-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                             role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <span id="countdown-text" class="fw-bold">Expira en: 30s</span>
                        </div>
                    </div>

                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Abre SimpleMenti en tu móvil y escanea este código
                    </small>

                    <!-- Estado de conexión -->
                    <div id="connection-status" class="alert alert-info" style="display: none;">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        <span id="status-text">Esperando conexión...</span>
                    </div>

                    <div id="connection-success" class="alert alert-success" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        ¡Conectado! Tu móvil ahora controla la presentación
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-regenerar-qr">
                        <i class="fas fa-redo me-2"></i>
                        Regenerar QR
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para el modal */
#modalControlMovil .modal-step {
    transition: opacity 0.3s ease;
}

#qr-image {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#pair-code {
    user-select: all;
    cursor: pointer;
}

#pair-code:hover {
    background-color: #495057 !important;
}

.text-monospace {
    font-family: 'Courier New', Courier, monospace;
}
</style>
