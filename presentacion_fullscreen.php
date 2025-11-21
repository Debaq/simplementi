<?php
/**
 * Modo de Presentación Fullscreen
 * Muestra slides del PDF y preguntas en pantalla completa
 * Sigue la secuencia configurada en pdf_sequence
 */

// Mostrar errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo_sesion)) {
    echo "Error: Código de sesión no proporcionado";
    exit;
}

// Buscar archivo de sesión
$session_files = glob("data/respuestas/*/sesion_$codigo_sesion.json");

if (empty($session_files)) {
    echo "Error: Sesión no encontrada";
    exit;
}

$session_file = $session_files[0];
$respuestas_json = file_get_contents($session_file);
$respuestas_data = json_decode($respuestas_json, true);

// Obtener datos de la presentación
$test_id = $respuestas_data['id_presentacion'];
$test_file = "data/presentaciones/$test_id.json";

if (!file_exists($test_file)) {
    echo "Error: Presentación no encontrada";
    exit;
}

$test_json = file_get_contents($test_file);
$test_data = json_decode($test_json, true);

// Verificar si hay PDF habilitado
$tiene_pdf = !empty($test_data['pdf_enabled']) && isset($test_data['pdf_sequence']);

if (!$tiene_pdf) {
    echo "Error: Esta presentación no tiene PDF habilitado o no tiene secuencia configurada";
    exit;
}

// Obtener índice actual de la secuencia
$sequence_index = isset($_GET['index']) ? intval($_GET['index']) : 0;
$sequence = $test_data['pdf_sequence'];
$total_items = count($sequence);

// Validar índice
if ($sequence_index < 0 || $sequence_index >= $total_items) {
    $sequence_index = 0;
}

$current_item = $sequence[$sequence_index];

// Preparar datos del item actual
$item_data = null;
$item_type = $current_item['type'];

if ($item_type === 'slide') {
    $slide_number = $current_item['number'];
    $item_data = [
        'type' => 'slide',
        'number' => $slide_number,
        'image' => $test_data['pdf_images'][$slide_number - 1]['path']
    ];
} elseif ($item_type === 'question') {
    $question_id = $current_item['id'];
    foreach ($test_data['preguntas'] as $q) {
        if ($q['id'] === $question_id) {
            $item_data = $q;
            $item_data['type'] = 'question';
            break;
        }
    }
}

if (!$item_data) {
    echo "Error: No se pudo cargar el contenido";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentación - <?php echo htmlspecialchars($test_data['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            overflow: hidden;
        }

        /* Layout principal de 2 columnas */
        #presentation-layout {
            display: flex;
            width: 100vw;
            height: 100vh;
        }

        /* Columna de contenido (slides/preguntas) */
        #content-area {
            flex: 1;
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            background: #000;
            position: relative;
        }

        /* Panel lateral negro */
        #sidebar-panel {
            width: 400px;
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            border-left: 1px solid #333;
        }

        /* Slides del PDF */
        .slide-content {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Preguntas */
        .question-content {
            max-width: 90%;
            width: 900px;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .question-content h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .question-options {
            margin-top: 30px;
        }

        .option-item {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            font-size: 1.3rem;
            border: 2px solid rgba(255,255,255,0.2);
        }

        /* Estilos del panel lateral */
        .sidebar-section {
            padding: 20px;
            border-bottom: 1px solid #333;
        }

        .sidebar-section:last-child {
            border-bottom: none;
        }

        .sidebar-section h3 {
            font-size: 1rem;
            color: #aaa;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: 600;
        }

        #qr-code-sidebar {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }

        #qr-code-sidebar canvas {
            border: 3px solid #fff;
            border-radius: 8px;
        }

        .session-code {
            background: #2a2a2a;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 2px;
            color: #4e73df;
        }

        .participant-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #2a2a2a;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .participant-item .badge {
            font-size: 0.75rem;
        }

        .stats-box {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .stats-box .stats-label {
            color: #aaa;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .stats-box .stats-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4e73df;
        }

        .progress-custom {
            height: 25px;
            border-radius: 8px;
            background: #2a2a2a;
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: width 0.3s ease;
        }

        /* Controles de navegación en sidebar */
        .nav-controls-sidebar {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 15px;
        }

        .nav-btn {
            flex: 1;
            background: #2a2a2a;
            border: 2px solid #444;
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .nav-btn:hover:not(:disabled) {
            background: #3a3a3a;
            border-color: #666;
        }

        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .exit-btn-sidebar {
            width: 100%;
            background: #dc3545;
            border: none;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .exit-btn-sidebar:hover {
            background: #c82333;
        }

        .position-counter-sidebar {
            text-align: center;
            font-size: 1.1rem;
            color: #aaa;
            padding: 10px;
            background: #2a2a2a;
            border-radius: 8px;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .slide-content, .question-content {
            animation: fadeIn 0.3s ease-out;
        }

        /* Loader de precarga */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        #preloader.hidden {
            display: none;
        }

        .loader-spinner {
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            margin-top: 20px;
            color: #fff;
            font-size: 1.2rem;
        }

        .loader-progress {
            margin-top: 10px;
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }

        /* Scrollbar personalizado */
        #sidebar-panel::-webkit-scrollbar {
            width: 8px;
        }

        #sidebar-panel::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        #sidebar-panel::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 4px;
        }

        #sidebar-panel::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        .participants-list {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Cargando presentación...</div>
        <div class="loader-progress" id="loader-progress">0 / 0</div>
    </div>

    <!-- Layout principal -->
    <div id="presentation-layout">
        <!-- Área de contenido (slides/preguntas) -->
        <div id="content-area">
            <?php if ($item_data['type'] === 'slide'): ?>
                <!-- Mostrar slide del PDF -->
                <img src="<?php echo htmlspecialchars($item_data['image']); ?>"
                     alt="Slide <?php echo $item_data['number']; ?>"
                     class="slide-content">

            <?php elseif ($item_data['type'] === 'question'): ?>
                <!-- Mostrar pregunta -->
                <div class="question-content">
                    <h1><?php echo htmlspecialchars($item_data['pregunta']); ?></h1>

                    <?php if ($item_data['tipo'] === 'opcion_multiple' && isset($item_data['opciones'])): ?>
                        <div class="question-options">
                            <?php foreach ($item_data['opciones'] as $index => $opcion): ?>
                                <div class="option-item">
                                    <strong><?php echo chr(65 + $index); ?>.</strong>
                                    <?php echo htmlspecialchars($opcion); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($item_data['tipo'] === 'verdadero_falso'): ?>
                        <div class="question-options">
                            <div class="option-item"><strong>A.</strong> Verdadero</div>
                            <div class="option-item"><strong>B.</strong> Falso</div>
                        </div>

                    <?php elseif ($item_data['tipo'] === 'palabra_libre'): ?>
                        <div class="alert alert-light mt-4">
                            <i class="fas fa-keyboard"></i> Los participantes escribirán su respuesta
                        </div>

                    <?php elseif ($item_data['tipo'] === 'nube_palabras'): ?>
                        <div class="alert alert-light mt-4">
                            <i class="fas fa-cloud"></i> Los participantes enviarán palabras
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel lateral -->
        <div id="sidebar-panel">
            <!-- Posición en la secuencia -->
            <div class="sidebar-section">
                <div class="position-counter-sidebar">
                    <i class="fas fa-list-ol me-2"></i>
                    <strong><?php echo ($sequence_index + 1); ?> / <?php echo $total_items; ?></strong>
                    <div style="font-size: 0.85rem; margin-top: 5px; color: #666;">
                        <?php if ($item_data['type'] === 'slide'): ?>
                            <i class="fas fa-file-pdf me-1"></i> Slide
                        <?php else: ?>
                            <i class="fas fa-question-circle me-1"></i> Pregunta
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($item_data['type'] === 'slide'): ?>
                <!-- Información de acceso para slides -->
                <div class="sidebar-section">
                    <h3><i class="fas fa-qrcode me-2"></i>Acceso a la Presentación</h3>
                    <div id="qr-code-sidebar"></div>
                    <div class="session-code">
                        <?php echo $codigo_sesion; ?>
                    </div>
                    <div style="text-align: center; margin-top: 10px; color: #aaa; font-size: 0.85rem;">
                        Escanea el código o usa el código de sesión
                    </div>
                </div>

            <?php else: ?>
                <!-- Información de participantes para preguntas -->
                <div class="sidebar-section">
                    <h3><i class="fas fa-users me-2"></i>Participantes</h3>
                    <div class="stats-box">
                        <div class="stats-label">Conectados</div>
                        <div class="stats-value" id="total-participantes-sidebar">0</div>
                    </div>
                    <div class="stats-box">
                        <div class="stats-label">Respuestas recibidas</div>
                        <div class="stats-value" id="total-respuestas-sidebar">0</div>
                    </div>

                    <div style="margin-top: 15px;">
                        <div style="color: #aaa; font-size: 0.85rem; margin-bottom: 8px;">
                            Progreso de respuestas
                        </div>
                        <div class="progress-custom">
                            <div class="progress-bar-custom" id="progress-bar-sidebar" style="width: 0%">
                                0%
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <div style="color: #aaa; font-size: 0.85rem; margin-bottom: 8px;">
                            Estado de participantes
                        </div>
                        <div class="participants-list" id="participants-list-sidebar">
                            <div style="text-align: center; color: #666; padding: 20px;">
                                Esperando participantes...
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Controles de navegación -->
            <div class="sidebar-section">
                <h3><i class="fas fa-gamepad me-2"></i>Controles</h3>
                <div class="nav-controls-sidebar">
                    <button class="nav-btn" id="prev-btn"
                            <?php echo $sequence_index <= 0 ? 'disabled' : ''; ?>
                            onclick="navigate(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-btn" id="next-btn"
                            <?php echo $sequence_index >= $total_items - 1 ? 'disabled' : ''; ?>
                            onclick="navigate(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div style="text-align: center; margin-top: 10px; color: #666; font-size: 0.85rem;">
                    <i class="fas fa-keyboard me-1"></i> Usa ← → o las flechas del teclado
                </div>
            </div>

            <!-- Botón de salir -->
            <div class="sidebar-section">
                <button class="exit-btn-sidebar" onclick="exitPresentation()">
                    <i class="fas fa-times me-2"></i> Salir de la Presentación
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        const codigo = '<?php echo $codigo_sesion; ?>';
        const currentIndex = <?php echo $sequence_index; ?>;
        const totalItems = <?php echo $total_items; ?>;
        const itemType = '<?php echo $item_data['type']; ?>';
        const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

        // Array con todas las imágenes para precargar
        const allImages = [
            <?php
            $image_paths = [];
            foreach ($sequence as $item) {
                if ($item['type'] === 'slide') {
                    $slide_num = $item['number'];
                    $image_path = $test_data['pdf_images'][$slide_num - 1]['path'];
                    $image_paths[] = '"' . addslashes($image_path) . '"';
                }
            }
            echo implode(",\n            ", $image_paths);
            ?>
        ];

        // Generar código QR si estamos en un slide
        if (itemType === 'slide') {
            document.addEventListener('DOMContentLoaded', function() {
                new QRCode(document.getElementById('qr-code-sidebar'), {
                    text: serverUrl + 'participante.php?codigo=' + codigo,
                    width: 180,
                    height: 180,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        }

        // Actualizar información de participantes para preguntas
        function updateParticipantInfo() {
            if (itemType !== 'question') return;

            fetch(serverUrl + 'api/get_resultados.php?codigo=' + codigo + '&pregunta=' + currentIndex)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar contadores
                        const totalParticipantes = data.total_participantes || 0;
                        const totalRespuestas = data.estadisticas.total_respuestas || 0;

                        document.getElementById('total-participantes-sidebar').textContent = totalParticipantes;
                        document.getElementById('total-respuestas-sidebar').textContent = totalRespuestas;

                        // Calcular y actualizar porcentaje
                        const porcentaje = totalParticipantes > 0 ?
                            Math.round((totalRespuestas / totalParticipantes) * 100) : 0;
                        const progressBar = document.getElementById('progress-bar-sidebar');
                        progressBar.style.width = porcentaje + '%';
                        progressBar.textContent = porcentaje + '%';

                        // Actualizar lista de participantes
                        const participantsList = document.getElementById('participants-list-sidebar');
                        if (data.participantes && data.participantes.length > 0) {
                            participantsList.innerHTML = '';
                            data.participantes.forEach(p => {
                                const respondido = p.respuestas.some(r => r.id_pregunta == currentIndex);
                                const div = document.createElement('div');
                                div.className = 'participant-item';
                                div.innerHTML = `
                                    <span>Participante ${p.id}</span>
                                    <span class="badge ${respondido ? 'bg-success' : 'bg-secondary'}">
                                        <i class="fas fa-${respondido ? 'check' : 'clock'}"></i>
                                    </span>
                                `;
                                participantsList.appendChild(div);
                            });
                        } else {
                            participantsList.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">Esperando participantes...</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error obteniendo info de participantes:', error);
                });
        }

        // Actualizar periódicamente si es una pregunta
        if (itemType === 'question') {
            updateParticipantInfo();
            setInterval(updateParticipantInfo, 2000);
        }

        // Precargar todas las imágenes
        function preloadImages() {
            return new Promise((resolve, reject) => {
                if (allImages.length === 0) {
                    resolve();
                    return;
                }

                let loadedCount = 0;
                const totalImages = allImages.length;
                const progressElement = document.getElementById('loader-progress');

                progressElement.textContent = `0 / ${totalImages} imágenes`;

                const imagePromises = allImages.map((src, index) => {
                    return new Promise((resolveImg) => {
                        const img = new Image();
                        img.onload = () => {
                            loadedCount++;
                            progressElement.textContent = `${loadedCount} / ${totalImages} imágenes`;
                            resolveImg();
                        };
                        img.onerror = () => {
                            console.warn('Error al cargar imagen:', src);
                            loadedCount++;
                            progressElement.textContent = `${loadedCount} / ${totalImages} imágenes`;
                            resolveImg(); // Continuar aunque falle
                        };
                        img.src = src;
                    });
                });

                Promise.all(imagePromises).then(() => {
                    console.log('Todas las imágenes precargadas');
                    resolve();
                }).catch(reject);
            });
        }

        // Ocultar preloader y mostrar presentación
        function hidePreloader() {
            const preloader = document.getElementById('preloader');
            preloader.classList.add('hidden');
        }

        // Navegación
        function navigate(direction) {
            const newIndex = currentIndex + direction;
            if (newIndex >= 0 && newIndex < totalItems) {
                window.location.href = `presentacion_fullscreen.php?codigo=${codigo}&index=${newIndex}`;
            }
        }

        // Salir de la presentación
        function exitPresentation() {
            if (confirm('¿Salir de la presentación?')) {
                window.location.href = `presentador.php?codigo=${codigo}`;
            }
        }

        // Entrar en modo fullscreen
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }

        // Navegación con teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                e.preventDefault();
                if (currentIndex > 0) navigate(-1);
            } else if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') {
                e.preventDefault();
                if (currentIndex < totalItems - 1) navigate(1);
            } else if (e.key === 'Escape') {
                exitPresentation();
            } else if (e.key === 'f' || e.key === 'F') {
                enterFullscreen();
            }
        });

        // Inicializar presentación
        window.addEventListener('load', async function() {
            try {
                // Primero precargar todas las imágenes
                await preloadImages();

                // Ocultar preloader
                hidePreloader();

                // Entrar en fullscreen
                enterFullscreen();
            } catch (error) {
                console.error('Error al inicializar presentación:', error);
                hidePreloader();
                enterFullscreen();
            }
        });

        // Actualizar participantes con el índice actual
        function syncParticipants() {
            fetch(`api/update_sequence_index.php?codigo=${codigo}&index=${currentIndex}`)
                .catch(err => console.error('Error al sincronizar:', err));
        }

        // Sincronizar al cargar
        syncParticipants();
    </script>
</body>
</html>
