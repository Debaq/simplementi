<!-- Pantalla de introducción con QR - Diseño moderno -->
<style>
    .startup-screen {
        margin-bottom: 20px;
    }

    .qr-section {
        background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 102, 255, 0.15);
    }

    .qr-wrapper {
        background: white;
        padding: 15px;
        border-radius: 10px;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .qr-container-enhanced {
        width: 280px;
        height: 280px;
        margin: 0 auto;
    }

    .session-code-badge {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        color: white;
        padding: 12px 25px;
        border-radius: 10px;
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 5px;
        display: inline-block;
        margin: 15px 0 10px;
        font-family: 'Courier New', monospace;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .url-copy-section {
        background: #F9FAFB;
        border-radius: 10px;
        padding: 15px;
        border: 1px solid #E5E7EB;
    }

    .participants-counter {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .participants-number {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .btn-start-presentation {
        background: #10B981;
        border: none;
        padding: 12px 30px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-start-presentation:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .presentation-title {
        font-size: 1.8rem;
        font-weight: bold;
        color: #1F2937;
        margin-bottom: 20px;
    }

    .instruction-text {
        font-size: 1rem;
        color: white;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .copy-btn-enhanced {
        background: #0066FF;
        border: none;
        transition: all 0.2s ease;
    }

    .copy-btn-enhanced:hover {
        background: #0052CC;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
    }

    .info-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 15px;
        border: 1px solid #E5E7EB;
    }
</style>

<div class="row justify-content-center startup-screen">
    <div class="col-lg-10">
        <!-- Título de la presentación -->
        <div class="text-center mb-3">
            <h2 class="presentation-title">
                <i class="fas fa-presentation me-2"></i>
                <?php echo htmlspecialchars($test_data['titulo']); ?>
            </h2>
        </div>

        <div class="row g-3">
            <!-- Sección del QR -->
            <div class="col-lg-6">
                <div class="qr-section text-center">
                    <h6 class="instruction-text">
                        <i class="fas fa-qrcode me-2"></i>
                        Escanea para unirte
                    </h6>

                    <div class="qr-wrapper">
                        <div class="qr-container-enhanced" id="qr-code-main"></div>
                    </div>

                    <div class="mt-2">
                        <div class="session-code-badge">
                            <?php echo $codigo_sesion; ?>
                        </div>
                        <p class="text-white-50 mt-1 mb-0">
                            <small><i class="fas fa-key me-1"></i>Código de sesión</small>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sección de información y controles -->
            <div class="col-lg-6">
                <!-- Contador de participantes -->
                <div class="participants-counter text-center mb-3">
                    <div class="participants-number" id="contador-participantes">0</div>
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Participantes conectados
                    </h6>
                </div>

                <!-- URL para copiar -->
                <div class="info-card url-copy-section">
                    <h6 class="mb-2">
                        <i class="fas fa-link me-2"></i>
                        Enlace de acceso
                    </h6>
                    <div class="input-group input-group-sm">
                        <input type="text" id="join-url" class="form-control"
                            value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/participante.php?codigo=$codigo_sesion"; ?>" readonly>
                        <button class="btn btn-primary copy-btn-enhanced" type="button" id="btn-copiar-url">
                            <i class="fas fa-copy me-1"></i> Copiar
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        Comparte este enlace
                    </small>
                </div>

                <!-- Instrucciones adicionales -->
                <div class="info-card">
                    <h6 class="mb-2">
                        <i class="fas fa-lightbulb me-2"></i>
                        Instrucciones
                    </h6>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-1">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Los participantes pueden escanear el QR o usar el código
                        </li>
                        <li class="mb-1">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Espera a que se conecten antes de comenzar
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Haz clic en "Comenzar" cuando estés listo
                        </li>
                    </ul>
                </div>

                <!-- Botón para comenzar -->
                <div class="text-center mt-3">
                    <button id="btn-comenzar" class="btn btn-success btn-start-presentation">
                        <i class="fas fa-play me-2"></i>
                        Comenzar Presentación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>