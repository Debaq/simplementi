<!-- Pantalla de introducción con QR -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0 text-center"><?php echo htmlspecialchars($test_data['titulo']); ?></h3>
            </div>
            <div class="card-body p-5 text-center">
                <h4 class="mb-4">Escanea el código QR para unirte a la presentación</h4>
                
                <div class="qr-container mb-4" id="qr-code-main"></div>
                
                <div class="input-group mb-4">
                    <input type="text" id="join-url" class="form-control form-control-lg" 
                        value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/participante.php?codigo=$codigo_sesion"; ?>" readonly>
                    <button class="btn btn-outline-primary btn-lg" type="button" id="btn-copiar-url">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                </div>
                
                <div class="alert alert-info">
                    <strong>Código de la sesión:</strong> <?php echo $codigo_sesion; ?>
                </div>
                
                <p class="lead mt-4">
                    <span id="contador-participantes">0</span> participantes conectados
                </p>
                
                <button id="btn-comenzar" class="btn btn-success btn-lg mt-3">
                    <i class="fas fa-play me-2"></i> Comenzar presentación
                </button>
            </div>
        </div>
    </div>
</div>