<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SimpleMenti - Control Activo</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SimpleMenti">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
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
        const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

        let currentState = null;
        let isOnline = navigator.onLine;
        let consecutiveErrors = 0;
        const MAX_CONSECUTIVE_ERRORS = 3;

        // Elementos DOM
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnDisconnect = document.getElementById('btn-disconnect');
        const slideIndicator = document.getElementById('slide-indicator');
        const participantsCount = document.getElementById('participants-count');
        const participantsList = document.getElementById('participants-list');
        const handsList = document.getElementById('hands-list');
        const questionsList = document.getElementById('questions-list');
        const interactionsCount = document.getElementById('interactions-count');

        // ============================================================
        // MONITOREO DE CONEXIÓN
        // ============================================================

        // Detectar cambios en conectividad
        window.addEventListener('online', () => {
            isOnline = true;
            consecutiveErrors = 0;
            actualizarEstadoConexion(true);
            obtenerEstado(); // Sincronizar inmediatamente
        });

        window.addEventListener('offline', () => {
            isOnline = false;
            actualizarEstadoConexion(false);
        });

        function actualizarEstadoConexion(conectado) {
            const badge = document.querySelector('.badge');
            if (conectado) {
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 8px;"></i> Conectado';
            } else {
                badge.className = 'badge bg-danger';
                badge.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 8px;"></i> Sin conexión';
            }
        }

        // ============================================================
        // VIBRATION FEEDBACK (Haptic)
        // ============================================================

        function vibrar(patron) {
            if ('vibrate' in navigator) {
                navigator.vibrate(patron);
            }
        }

        // Patrones de vibración
        const VIBRATION = {
            LIGHT: 10,      // Toque ligero
            MEDIUM: 20,     // Toque medio
            HEAVY: 50,      // Toque fuerte
            SUCCESS: [10, 50, 10],  // Patrón de éxito
            ERROR: [50, 100, 50, 100, 50]  // Patrón de error
        };

        // Navegación
        btnPrev.addEventListener('click', () => retroceder());
        btnNext.addEventListener('click', () => avanzar());

        // Desconectar
        btnDisconnect.addEventListener('click', () => {
            if (confirm('¿Deseas desconectar el control móvil?')) {
                window.location.href = 'control-movil.php';
            }
        });

        // Función para avanzar
        async function avanzar() {
            vibrar(VIBRATION.LIGHT); // Feedback al tocar
            btnNext.disabled = true;
            btnNext.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Avanzando...';

            try {
                const response = await fetch(serverUrl + 'api/control-movil/avanzar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ pair_code: pairCode })
                });

                const data = await response.json();

                if (data.success) {
                    vibrar(VIBRATION.SUCCESS); // Feedback de éxito
                    consecutiveErrors = 0;
                    // Actualizar UI
                    actualizarSlideIndicator(data.current_item);
                    await obtenerEstado(); // Refresh completo
                } else {
                    vibrar(VIBRATION.ERROR); // Feedback de error
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error al avanzar:', error);
                consecutiveErrors++;
                vibrar(VIBRATION.ERROR);

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    actualizarEstadoConexion(false);
                    alert('Error de conexión persistente. Verifica tu conexión a Internet.');
                } else {
                    alert('Error de conexión');
                }
            } finally {
                btnNext.disabled = false;
                btnNext.innerHTML = 'Siguiente <i class="fas fa-chevron-right ms-2"></i>';
            }
        }

        // Función para retroceder
        async function retroceder() {
            vibrar(VIBRATION.LIGHT); // Feedback al tocar
            btnPrev.disabled = true;
            btnPrev.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Retrocediendo...';

            try {
                const response = await fetch(serverUrl + 'api/control-movil/retroceder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ pair_code: pairCode })
                });

                const data = await response.json();

                if (data.success) {
                    vibrar(VIBRATION.SUCCESS); // Feedback de éxito
                    consecutiveErrors = 0;
                    // Actualizar UI
                    actualizarSlideIndicator(data.current_item);
                    await obtenerEstado(); // Refresh completo
                } else {
                    vibrar(VIBRATION.ERROR); // Feedback de error
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error al retroceder:', error);
                consecutiveErrors++;
                vibrar(VIBRATION.ERROR);

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    actualizarEstadoConexion(false);
                    alert('Error de conexión persistente. Verifica tu conexión a Internet.');
                } else {
                    alert('Error de conexión');
                }
            } finally {
                btnPrev.disabled = false;
                btnPrev.innerHTML = '<i class="fas fa-chevron-left me-2"></i> Anterior';
            }
        }

        // Función para obtener estado
        async function obtenerEstado() {
            try {
                const response = await fetch(serverUrl + 'api/control-movil/estado.php?pair_code=' + pairCode);
                const data = await response.json();

                if (data.success) {
                    currentState = data;
                    actualizarUI(data);
                    consecutiveErrors = 0;
                    if (!isOnline) {
                        isOnline = true;
                        actualizarEstadoConexion(true);
                    }
                } else {
                    console.error('Error al obtener estado:', data.message);
                    consecutiveErrors++;
                }
            } catch (error) {
                console.error('Error al obtener estado:', error);
                consecutiveErrors++;

                // Si hay muchos errores consecutivos, marcar como desconectado
                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    actualizarEstadoConexion(false);
                }
            }
        }

        // Actualizar UI con el estado
        function actualizarUI(data) {
            // Detectar si la presentación tiene diapositivas
            const hasPdf = data.session.presentation.pdf_enabled;
            const slidePreviewSection = document.querySelector('.bg-white.border-bottom.p-3:has(#slide-preview)');
            const navigationSection = document.querySelector('.bg-white.border-bottom.p-3:has(#btn-prev)');

            // Ocultar controles de diapositivas si no hay PDF
            if (!hasPdf) {
                if (slidePreviewSection) slidePreviewSection.style.display = 'none';
                if (navigationSection) navigationSection.style.display = 'none';
            } else {
                if (slidePreviewSection) slidePreviewSection.style.display = 'block';
                if (navigationSection) navigationSection.style.display = 'block';
            }

            // Actualizar slide indicator (solo si hay PDF)
            if (hasPdf) {
                actualizarSlideIndicator(data.session.current_item);
            }

            // Actualizar participantes
            participantsCount.textContent = data.session.participants_count;
            renderParticipantes(data.participants);

            // Actualizar interacciones
            const totalInteractions = data.interactions.hands_count + data.interactions.questions_count;
            interactionsCount.textContent = totalInteractions;
            renderManos(data.interactions.hands_raised);
            renderPreguntas(data.interactions.questions);

            // Deshabilitar botones según posición (solo si hay PDF)
            if (hasPdf) {
                btnPrev.disabled = (data.session.current_item.index === 0);
                btnNext.disabled = (data.session.current_item.index >= data.session.current_item.total - 1);
            }
        }

        // Actualizar indicador de slide
        function actualizarSlideIndicator(item) {
            const text = item.title || `${item.type} ${item.index + 1}/${item.total}`;
            slideIndicator.textContent = text;
        }

        // Renderizar lista de participantes
        function renderParticipantes(participants) {
            if (participants.length === 0) {
                participantsList.innerHTML = '<p class="text-muted text-center small">No hay participantes conectados</p>';
                return;
            }

            let html = '<div class="list-group">';
            participants.forEach(p => {
                html += `
                    <div class="list-group-item participant-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user text-primary me-2"></i>
                                <strong>${escapeHtml(p.nombre)}</strong>
                            </div>
                            <span class="badge bg-secondary">${p.respuestas_count} resp.</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            participantsList.innerHTML = html;
        }

        // Renderizar manos levantadas
        function renderManos(hands) {
            if (hands.length === 0) {
                handsList.innerHTML = '<p class="text-muted text-center small">No hay manos levantadas</p>';
                return;
            }

            let html = '';
            hands.forEach(hand => {
                const timeAgo = calcularTiempoAtras(hand.timestamp);
                html += `
                    <div class="interaction-item">
                        <i class="fas fa-hand-paper text-warning me-2"></i>
                        <strong>${escapeHtml(hand.participant_name)}</strong>
                        <br>
                        <small class="text-muted">${timeAgo}</small>
                    </div>
                `;
            });
            handsList.innerHTML = html;
        }

        // Renderizar preguntas
        function renderPreguntas(questions) {
            if (questions.length === 0) {
                questionsList.innerHTML = '<p class="text-muted text-center small">No hay preguntas</p>';
                return;
            }

            let html = '';
            questions.forEach(q => {
                const timeAgo = calcularTiempoAtras(q.timestamp);
                const participantName = q.anonymous ? 'Anónimo' : escapeHtml(q.participant_name);
                html += `
                    <div class="interaction-item">
                        <div class="d-flex justify-content-between">
                            <strong>${participantName}</strong>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        <p class="mb-0 mt-1">${escapeHtml(q.question)}</p>
                    </div>
                `;
            });
            questionsList.innerHTML = html;
        }

        // Utilidades
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function calcularTiempoAtras(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diff = Math.floor((now - time) / 1000); // segundos

            if (diff < 60) return 'Hace ' + diff + 's';
            if (diff < 3600) return 'Hace ' + Math.floor(diff / 60) + 'm';
            if (diff < 86400) return 'Hace ' + Math.floor(diff / 3600) + 'h';
            return 'Hace ' + Math.floor(diff / 86400) + 'd';
        }

        // ============================================================
        // PUNTERO VIRTUAL
        // ============================================================

        const pointerToggle = document.getElementById('pointer-toggle');
        const pointerTouchpad = document.getElementById('pointer-touchpad');

        let pointerEnabled = false;
        let lastPointerSend = 0;
        const POINTER_THROTTLE = 50; // ms (20 fps)

        // Toggle del puntero
        pointerToggle.addEventListener('change', async (e) => {
            pointerEnabled = e.target.checked;
            vibrar(pointerEnabled ? VIBRATION.SUCCESS : VIBRATION.MEDIUM); // Feedback al activar/desactivar

            // Mostrar/ocultar touchpad
            pointerTouchpad.style.display = pointerEnabled ? 'block' : 'none';

            // Enviar estado al servidor
            try {
                const response = await fetch(serverUrl + 'api/control-movil/toggle_puntero.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pair_code: pairCode,
                        enabled: pointerEnabled
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    console.error('Error al toggle puntero:', data.message);
                    vibrar(VIBRATION.ERROR);
                }
            } catch (error) {
                console.error('Error:', error);
                vibrar(VIBRATION.ERROR);
            }
        });

        // Touchpad para mover el puntero
        let touchStartX = 0;
        let touchStartY = 0;

        pointerTouchpad.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
            updatePointerPosition(touch.clientX, touch.clientY);
        });

        pointerTouchpad.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (e.touches.length > 0) {
                const touch = e.touches[0];
                updatePointerPosition(touch.clientX, touch.clientY);
            }
        });

        // También soportar mouse (para testing en desktop)
        let isMouseDown = false;

        pointerTouchpad.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            updatePointerPosition(e.clientX, e.clientY);
        });

        pointerTouchpad.addEventListener('mousemove', (e) => {
            if (isMouseDown) {
                updatePointerPosition(e.clientX, e.clientY);
            }
        });

        pointerTouchpad.addEventListener('mouseup', () => {
            isMouseDown = false;
        });

        pointerTouchpad.addEventListener('mouseleave', () => {
            isMouseDown = false;
        });

        // Actualizar posición del puntero
        function updatePointerPosition(clientX, clientY) {
            if (!pointerEnabled) return;

            // Calcular posición relativa al touchpad
            const rect = pointerTouchpad.getBoundingClientRect();
            const x = (clientX - rect.left) / rect.width;
            const y = (clientY - rect.top) / rect.height;

            // Validar rango
            if (x < 0 || x > 1 || y < 0 || y > 1) return;

            // Throttling: solo enviar cada POINTER_THROTTLE ms
            const now = Date.now();
            if (now - lastPointerSend < POINTER_THROTTLE) return;
            lastPointerSend = now;

            // Enviar al servidor
            sendPointerUpdate(x, y);
        }

        // Enviar actualización de puntero al servidor
        async function sendPointerUpdate(x, y) {
            try {
                await fetch(serverUrl + 'api/control-movil/actualizar_puntero.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pair_code: pairCode,
                        x: x,
                        y: y,
                        enabled: true
                    })
                });
                // No esperamos respuesta para mantener baja latencia
            } catch (error) {
                console.error('Error al actualizar puntero:', error);
            }
        }

        // ============================================================
        // INICIALIZACIÓN
        // ============================================================

        console.log('Control móvil inicializado', {pairCode, sessionId, presentationId});
        obtenerEstado();

        // Actualizar cada 3 segundos
        setInterval(obtenerEstado, 3000);

        // ============================================================
        // SERVICE WORKER (PWA)
        // ============================================================

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('[PWA] Service Worker registrado:', registration.scope);

                    // Detectar actualizaciones del Service Worker
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // Hay una nueva versión disponible
                                console.log('[PWA] Nueva versión disponible');

                                // Opcional: mostrar notificación al usuario
                                if (confirm('Hay una nueva versión disponible. ¿Actualizar ahora?')) {
                                    newWorker.postMessage({ type: 'SKIP_WAITING' });
                                    window.location.reload();
                                }
                            }
                        });
                    });
                } catch (error) {
                    console.log('[PWA] Error al registrar Service Worker:', error);
                }
            });

            // Recargar cuando se activa un nuevo Service Worker
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        }

        // Detectar instalación de PWA
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();

            // Guardar el evento para mostrarlo más tarde
            const installPrompt = e;

            // Opcional: mostrar botón de instalación personalizado
            console.log('[PWA] PWA puede ser instalada');

            // Mostrar prompt de instalación (opcional)
            setTimeout(() => {
                installPrompt.prompt();
                installPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] Usuario aceptó instalación');
                    }
                });
            }, 5000); // Después de 5 segundos de uso
        });

        // Detectar cuando la PWA está instalada
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] Aplicación instalada exitosamente');
        });
    </script>
</body>
</html>
