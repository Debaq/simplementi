<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SimpleMenti - Seleccionar Presentación</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/control-movil.css">

    <style>
        .presentation-card {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .presentation-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .presentation-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .presentation-card .check-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
            color: #667eea;
        }

        .presentation-card.selected .check-icon {
            display: block;
        }

        .success-animation {
            display: none;
        }

        .success-animation.show {
            display: flex;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-mobile-alt me-2"></i>
                Control SimpleMenti
            </span>
            <span class="badge bg-success">
                <i class="fas fa-check-circle me-1"></i>
                Conectado
            </span>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid p-3">
        <div id="loading-state" class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted">Cargando presentaciones...</p>
        </div>

        <div id="content-state" style="display: none;">
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Vinculación exitosa</strong><br>
                <small>Selecciona una presentación para iniciar</small>
            </div>

            <h6 class="fw-bold mb-3">Presentaciones Disponibles:</h6>

            <div id="presentations-list"></div>

            <button id="btn-iniciar" class="btn btn-primary btn-lg w-100 mt-4" disabled>
                <i class="fas fa-play me-2"></i>
                Iniciar Presentación
            </button>
        </div>

        <div id="success-state" class="success-animation">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold">¡Iniciando Presentación!</h4>
                <p class="text-muted">Redirigiendo al control...</p>
                <div class="spinner-border text-primary mt-3" role="status"></div>
            </div>
        </div>

        <div id="error-state" style="display: none;">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error</strong><br>
                <span id="error-message"></span>
            </div>
            <button class="btn btn-primary" onclick="location.reload()">
                Reintentar
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pairCode = '<?php echo htmlspecialchars($pair_code); ?>';
        const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

        let selectedPresentation = null;

        // Cargar presentaciones
        async function cargarPresentaciones() {
            try {
                const response = await fetch(serverUrl + 'api/control-movil/listar_presentaciones.php');
                const data = await response.json();

                if (data.success) {
                    mostrarPresentaciones(data.presentaciones);
                    document.getElementById('loading-state').style.display = 'none';
                    document.getElementById('content-state').style.display = 'block';
                } else {
                    mostrarError(data.message || 'No se pudieron cargar las presentaciones');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión');
            }
        }

        function mostrarPresentaciones(presentaciones) {
            const container = document.getElementById('presentations-list');
            container.innerHTML = '';

            presentaciones.forEach(pres => {
                const card = document.createElement('div');
                card.className = 'card presentation-card mb-3 position-relative';
                card.innerHTML = `
                    <div class="card-body">
                        <i class="fas fa-check-circle check-icon fa-2x"></i>
                        <h5 class="card-title fw-bold">${escapeHtml(pres.titulo)}</h5>
                        <p class="card-text text-muted mb-2">${escapeHtml(pres.descripcion || 'Sin descripción')}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-question-circle me-1"></i>
                                ${pres.num_preguntas} pregunta${pres.num_preguntas !== 1 ? 's' : ''}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                ${escapeHtml(pres.autor)}
                            </small>
                        </div>
                    </div>
                `;

                card.addEventListener('click', () => seleccionarPresentacion(pres.id, card));
                container.appendChild(card);
            });
        }

        function seleccionarPresentacion(presentationId, cardElement) {
            // Deseleccionar todas
            document.querySelectorAll('.presentation-card').forEach(c => c.classList.remove('selected'));

            // Seleccionar esta
            cardElement.classList.add('selected');
            selectedPresentation = presentationId;

            // Habilitar botón
            document.getElementById('btn-iniciar').disabled = false;

            // Vibración ligera
            if ('vibrate' in navigator) {
                navigator.vibrate(10);
            }
        }

        async function iniciarPresentacion() {
            if (!selectedPresentation) {
                alert('Selecciona una presentación');
                return;
            }

            const btnIniciar = document.getElementById('btn-iniciar');
            btnIniciar.disabled = true;
            btnIniciar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Iniciando...';

            // Vibración de confirmación
            if ('vibrate' in navigator) {
                navigator.vibrate([10, 50, 10]);
            }

            try {
                const response = await fetch(serverUrl + 'api/control-movil/iniciar_presentacion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pair_code: pairCode,
                        presentation_id: selectedPresentation
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Mostrar éxito
                    document.getElementById('content-state').style.display = 'none';
                    document.getElementById('success-state').classList.add('show');

                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    mostrarError(data.message || 'Error al iniciar presentación');
                    btnIniciar.disabled = false;
                    btnIniciar.innerHTML = '<i class="fas fa-play me-2"></i> Iniciar Presentación';
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión');
                btnIniciar.disabled = false;
                btnIniciar.innerHTML = '<i class="fas fa-play me-2"></i> Iniciar Presentación';
            }
        }

        function mostrarError(mensaje) {
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('content-state').style.display = 'none';
            document.getElementById('error-state').style.display = 'block';
            document.getElementById('error-message').textContent = mensaje;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Event listener
        document.getElementById('btn-iniciar').addEventListener('click', iniciarPresentacion);

        // Cargar al inicio
        cargarPresentaciones();
    </script>
</body>
</html>
