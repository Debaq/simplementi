<!-- Pantalla de introducción con QR - Diseño mejorado -->
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    .startup-screen {
        animation: fadeInUp 0.6s ease-out;
    }

    .qr-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
        position: relative;
        overflow: hidden;
    }

    .qr-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
            45deg,
            transparent,
            rgba(255, 255, 255, 0.1),
            transparent
        );
        animation: shimmer 3s infinite;
    }

    .qr-wrapper {
        background: white;
        padding: 30px;
        border-radius: 15px;
        display: inline-block;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 1;
    }

    .qr-container-enhanced {
        width: 280px;
        height: 280px;
        margin: 0 auto;
    }

    .session-code-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 20px 40px;
        border-radius: 50px;
        font-size: 2rem;
        font-weight: bold;
        letter-spacing: 8px;
        box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
        display: inline-block;
        margin: 20px 0;
        font-family: 'Courier New', monospace;
    }

    .url-copy-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .url-copy-section:hover {
        border-color: #4e73df;
        background: #ffffff;
    }

    .participants-counter {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
    }

    .participants-number {
        font-size: 3.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .btn-start-presentation {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        padding: 20px 50px;
        font-size: 1.3rem;
        font-weight: bold;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(56, 239, 125, 0.4);
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
    }

    .btn-start-presentation:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(56, 239, 125, 0.5);
        background: linear-gradient(135deg, #0d7964 0%, #2dd462 100%);
    }

    .presentation-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2d3748;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .instruction-text {
        font-size: 1.2rem;
        color: white;
        margin-bottom: 30px;
        font-weight: 500;
    }

    .copy-btn-enhanced {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .copy-btn-enhanced:hover {
        background: linear-gradient(135deg, #224abe 0%, #1a3a8f 100%);
        transform: translateX(-3px);
    }

    .info-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
</style>

<div class="row justify-content-center startup-screen">
    <div class="col-lg-10">
        <!-- Título de la presentación -->
        <div class="text-center mb-4">
            <h1 class="presentation-title">
                <i class="fas fa-presentation text-primary me-3"></i>
                <?php echo htmlspecialchars($test_data['titulo']); ?>
            </h1>
        </div>

        <div class="row g-4">
            <!-- Sección del QR -->
            <div class="col-lg-6">
                <div class="qr-section text-center">
                    <h3 class="instruction-text">
                        <i class="fas fa-qrcode me-2"></i>
                        Escanea para unirte
                    </h3>

                    <div class="qr-wrapper">
                        <div class="qr-container-enhanced" id="qr-code-main"></div>
                    </div>

                    <div class="mt-4">
                        <div class="session-code-badge">
                            <?php echo $codigo_sesion; ?>
                        </div>
                        <p class="text-white mt-2 mb-0">
                            <small><i class="fas fa-key me-2"></i>Código de sesión</small>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sección de información y controles -->
            <div class="col-lg-6">
                <!-- Contador de participantes -->
                <div class="participants-counter text-center mb-4">
                    <div class="participants-number" id="contador-participantes">0</div>
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Participantes conectados
                    </h4>
                </div>

                <!-- URL para copiar -->
                <div class="info-card url-copy-section">
                    <h5 class="mb-3">
                        <i class="fas fa-link me-2 text-primary"></i>
                        Enlace de acceso
                    </h5>
                    <div class="input-group">
                        <input type="text" id="join-url" class="form-control form-control-lg"
                            value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/participante.php?codigo=$codigo_sesion"; ?>" readonly>
                        <button class="btn btn-primary btn-lg copy-btn-enhanced" type="button" id="btn-copiar-url">
                            <i class="fas fa-copy me-2"></i> Copiar
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Comparte este enlace con los participantes
                    </small>
                </div>

                <!-- Instrucciones adicionales -->
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Instrucciones
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Los participantes pueden escanear el QR o usar el código
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Espera a que se conecten antes de comenzar
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Haz clic en "Comenzar" cuando estés listo
                        </li>
                    </ul>
                </div>

                <!-- Botón para comenzar -->
                <div class="text-center mt-4">
                    <button id="btn-comenzar" class="btn btn-success btn-lg btn-start-presentation">
                        <i class="fas fa-rocket me-2"></i>
                        Comenzar Presentación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>