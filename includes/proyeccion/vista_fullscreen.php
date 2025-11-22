<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($presentationData['titulo'] ?? 'Presentación'); ?> - Proyección</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        #proyeccion-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #slide-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            text-align: center;
            padding: 2rem;
        }

        .slide-image {
            max-width: 100%;
            max-height: 100vh;
            object-fit: contain;
        }

        .question-display {
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 4rem;
            border-radius: 20px;
            max-width: 80%;
        }

        .question-display h1 {
            font-size: 3.5rem;
            margin-bottom: 2rem;
        }

        .question-display .options {
            font-size: 2rem;
            margin-top: 2rem;
        }

        .status-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.2rem;
            z-index: 100;
        }

        .connection-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .fade-transition {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div id="proyeccion-container">
        <div id="slide-content" class="fade-transition">
            <div class="text-muted">
                <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                <p>Cargando presentación...</p>
            </div>
        </div>
    </div>

    <!-- Barra de estado -->
    <div class="status-bar">
        <div class="connection-status">
            <span class="status-dot"></span>
            <span>Conectado</span>
        </div>
        <div>
            <span id="slide-indicator">Slide 1 / 10</span>
        </div>
        <div>
            <span id="participants-count">0 participantes</span>
        </div>
    </div>

    <!-- Incluir puntero virtual -->
    <?php include(__DIR__ . '/../presentador/puntero_virtual.php'); ?>

    <script>
        const pairCode = '<?php echo htmlspecialchars($pair_code); ?>';
        const sessionId = '<?php echo htmlspecialchars($session_id); ?>';
        const presentationId = '<?php echo htmlspecialchars($presentation_id); ?>';
        const presentationData = <?php echo json_encode($presentationData); ?>;
        const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
        const codigoSesion = sessionId; // Para compatibilidad con puntero_virtual.php

        let currentSlide = 0;
        let currentData = null;

        const slideContent = document.getElementById('slide-content');
        const slideIndicator = document.getElementById('slide-indicator');
        const participantsCount = document.getElementById('participants-count');

        // Obtener estado actual desde el control móvil
        async function obtenerEstado() {
            try {
                const response = await fetch(serverUrl + 'api/control-movil/estado.php?pair_code=' + pairCode);
                const data = await response.json();

                if (data.success) {
                    currentData = data;
                    actualizarVista(data);
                }
            } catch (error) {
                console.error('Error al obtener estado:', error);
            }
        }

        // Actualizar vista con el estado
        function actualizarVista(data) {
            const item = data.session.current_item;

            // Actualizar indicadores
            slideIndicator.textContent = `${item.title || 'Slide ' + (item.index + 1)} (${item.index + 1}/${item.total})`;
            participantsCount.textContent = `${data.session.participants_count} participante${data.session.participants_count !== 1 ? 's' : ''}`;

            // Renderizar contenido según tipo
            if (item.type === 'slide' && presentationData.pdf_images && presentationData.pdf_images[item.slide_number - 1]) {
                renderSlide(item.slide_number);
            } else if (item.type === 'question') {
                renderQuestion(item);
            } else if (item.type === 'intro') {
                renderIntro();
            } else {
                renderGeneric(item);
            }
        }

        // Renderizar slide de PDF
        function renderSlide(slideNumber) {
            const imagePath = 'img/pdf_images/' + presentationId + '/' + presentationData.pdf_images[slideNumber - 1];

            slideContent.innerHTML = `
                <img src="${imagePath}" class="slide-image" alt="Slide ${slideNumber}">
            `;
            slideContent.className = 'fade-transition';
        }

        // Renderizar pregunta
        function renderQuestion(item) {
            const questionData = presentationData.preguntas.find(q => q.id == item.question_id);

            if (!questionData) {
                renderGeneric(item);
                return;
            }

            let optionsHtml = '';
            if (questionData.opciones && questionData.opciones.length > 0) {
                optionsHtml = '<div class="options mt-4">';
                questionData.opciones.forEach((opcion, idx) => {
                    const letter = String.fromCharCode(65 + idx); // A, B, C, D...
                    optionsHtml += `<div class="mb-3"><strong>${letter}.</strong> ${opcion}</div>`;
                });
                optionsHtml += '</div>';
            }

            slideContent.innerHTML = `
                <div class="question-display">
                    <h1>${questionData.pregunta}</h1>
                    ${optionsHtml}
                </div>
            `;
            slideContent.className = 'fade-transition';
        }

        // Renderizar introducción
        function renderIntro() {
            slideContent.innerHTML = `
                <div class="text-center">
                    <h1 class="display-1 mb-4">${presentationData.titulo}</h1>
                    <p class="lead">${presentationData.descripcion || ''}</p>
                    <p class="mt-5 text-muted">
                        <i class="fas fa-qrcode fa-3x"></i>
                        <br>
                        Escanea el QR para unirte
                    </p>
                </div>
            `;
            slideContent.className = 'fade-transition';
        }

        // Renderizar genérico
        function renderGeneric(item) {
            slideContent.innerHTML = `
                <div class="text-center">
                    <h1>${item.title || 'Contenido no disponible'}</h1>
                </div>
            `;
            slideContent.className = 'fade-transition';
        }

        // Polling cada 2 segundos
        setInterval(obtenerEstado, 2000);

        // Cargar inmediatamente
        obtenerEstado();

        // Entrar en fullscreen al hacer clic
        document.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Error al entrar en fullscreen:', err);
                });
            }
        });

        // Presionar ESC para salir
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.fullscreenElement) {
                document.exitFullscreen();
            }
        });
    </script>
</body>
</html>
