<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SimpleMenti - Control Activo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/control-movil.css">
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-mobile-alt me-2"></i>
                Control Activo
            </span>
            <button class="btn btn-sm btn-outline-light" id="btn-disconnect">
                <i class="fas fa-power-off me-1"></i>
                Desconectar
            </button>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid p-0">
        <!-- Info de sesión -->
        <div class="bg-white border-bottom p-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($presentation_id); ?></h6>
                    <small class="text-muted">Sesión: <?php echo htmlspecialchars($session_id); ?></small>
                </div>
                <div class="col-auto">
                    <span class="badge bg-success">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        Conectado
                    </span>
                </div>
            </div>
        </div>

        <!-- Preview de slide actual -->
        <div class="bg-white border-bottom p-3">
            <div class="text-center">
                <div id="slide-preview" class="border rounded p-3 bg-light" style="min-height: 200px;">
                    <p class="text-muted mb-0">
                        <i class="fas fa-image fa-3x"></i>
                    </p>
                    <p class="small text-muted mt-2">Cargando presentación...</p>
                </div>
                <div class="mt-2">
                    <span id="slide-indicator" class="badge bg-secondary">Slide 1 / 10</span>
                </div>
            </div>
        </div>

        <!-- Controles de navegación -->
        <div class="bg-white border-bottom p-3">
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-lg btn-outline-primary w-100" id="btn-prev">
                        <i class="fas fa-chevron-left me-2"></i>
                        Anterior
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-lg btn-primary w-100" id="btn-next">
                        Siguiente
                        <i class="fas fa-chevron-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs de funciones -->
        <ul class="nav nav-tabs bg-white" id="controlTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-participants" data-bs-toggle="tab" data-bs-target="#participants" type="button">
                    <i class="fas fa-users"></i>
                    <span class="d-none d-sm-inline ms-1">Participantes</span>
                    <span class="badge bg-primary ms-1" id="participants-count">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-interactions" data-bs-toggle="tab" data-bs-target="#interactions" type="button">
                    <i class="fas fa-hand-paper"></i>
                    <span class="d-none d-sm-inline ms-1">Interacciones</span>
                    <span class="badge bg-warning text-dark ms-1" id="interactions-count">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-pointer" data-bs-toggle="tab" data-bs-target="#pointer" type="button">
                    <i class="fas fa-mouse-pointer"></i>
                    <span class="d-none d-sm-inline ms-1">Puntero</span>
                </button>
            </li>
        </ul>

        <!-- Contenido de tabs -->
        <div class="tab-content" id="controlTabsContent">
            <!-- Tab de Participantes -->
            <div class="tab-pane fade show active" id="participants" role="tabpanel">
                <div class="bg-white p-3">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-users me-2"></i>
                        Participantes Conectados
                    </h6>
                    <div id="participants-list">
                        <p class="text-muted text-center">Cargando...</p>
                    </div>
                </div>
            </div>

            <!-- Tab de Interacciones -->
            <div class="tab-pane fade" id="interactions" role="tabpanel">
                <div class="bg-white p-3">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-hand-paper me-2"></i>
                        Manos Levantadas
                    </h6>
                    <div id="hands-list">
                        <p class="text-muted text-center small">No hay manos levantadas</p>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-comment-dots me-2"></i>
                        Preguntas
                    </h6>
                    <div id="questions-list">
                        <p class="text-muted text-center small">No hay preguntas</p>
                    </div>
                </div>
            </div>

            <!-- Tab de Puntero -->
            <div class="tab-pane fade" id="pointer" role="tabpanel">
                <div class="bg-white p-3">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-mouse-pointer me-2"></i>
                        Puntero Virtual
                    </h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="pointer-toggle">
                        <label class="form-check-label" for="pointer-toggle">
                            Activar Puntero
                        </label>
                    </div>

                    <div id="pointer-touchpad" class="border rounded bg-light text-center" style="height: 300px; display: none;">
                        <p class="text-muted pt-5">
                            <i class="fas fa-hand-point-up fa-2x mb-2"></i><br>
                            Desliza tu dedo aquí para mover el puntero
                        </p>
                    </div>

                    <p class="small text-muted mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        El puntero se mostrará en la proyección
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pairCode = '<?php echo htmlspecialchars($pair_code); ?>';
        const sessionId = '<?php echo htmlspecialchars($session_id); ?>';
        const presentationId = '<?php echo htmlspecialchars($presentation_id); ?>';

        // TODO: Implementar lógica de control (Fase 4)
        console.log('Control móvil inicializado', {pairCode, sessionId, presentationId});
    </script>
</body>
</html>
