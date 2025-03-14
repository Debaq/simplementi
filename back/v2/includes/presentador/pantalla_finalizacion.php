<!-- Pantalla de finalización -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0 text-center">¡Presentación finalizada!</h3>
            </div>
            <div class="card-body p-5 text-center">
                <i class="fas fa-check-circle text-success mb-4" style="font-size: 5rem;"></i>
                
                <h4 class="mb-4">Todas las preguntas han sido presentadas</h4>
                
                <p class="lead mb-4">
                    <span id="contador-participantes-final">0</span> participantes conectados
                </p>
                
                <div class="d-grid gap-3">
                    <a href="resumen.php?codigo=<?php echo $codigo_sesion; ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-chart-bar me-2"></i> Ver resumen de resultados
                    </a>
                    
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home me-2"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>