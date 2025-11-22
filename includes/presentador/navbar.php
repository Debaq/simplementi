<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-chalkboard me-2"></i>
            SimpleMenti
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark badge-large">
                <i class="fas fa-hashtag me-1"></i> <?php echo htmlspecialchars($codigo_sesion); ?>
            </span>

            <!-- Botón destacado de Control Móvil -->
            <button class="btn btn-success btn-sm pulse-animation" id="btn-conectar-movil" title="Controla desde tu móvil">
                <i class="fas fa-mobile-alt me-1"></i>
                <span class="d-none d-md-inline">Control Móvil</span>
            </button>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
                    <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i> Inicio</a></li>
                    <li><a class="dropdown-item" href="#" id="btn-fullscreen"><i class="fas fa-expand me-2"></i> Pantalla completa</a></li>
                    <?php if ($pregunta_actual_index > 0): ?>
                    <li><a class="dropdown-item" href="resumen.php?codigo=<?php echo $codigo_sesion; ?>"><i class="fas fa-chart-bar me-2"></i> Ver resumen</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" id="btn-finalizar"><i class="fas fa-power-off me-2"></i> Finalizar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
/* Animación sutil para el botón de Control Móvil */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
    }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

.pulse-animation:hover {
    animation: none;
}
</style>