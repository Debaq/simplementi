<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-chalkboard me-2"></i>
            SimpleMenti
        </a>
        <div class="d-flex align-items-center">
            <span class="badge bg-warning text-dark badge-large me-2">
                <i class="fas fa-hashtag me-1"></i> <?php echo htmlspecialchars($codigo_sesion); ?>
            </span>
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