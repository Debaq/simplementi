<?php
/**
 * Pantalla fullscreen con anotaciones para estudiantes
 * Permite dibujar y escribir sobre las diapositivas
 */
?>

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

    #slide-container {
        width: 100vw;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        position: relative;
    }

    #slide-wrapper {
        position: relative;
        max-width: 100%;
        max-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #slide-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    #annotation-canvas {
        position: absolute;
        top: 0;
        left: 0;
        cursor: crosshair;
        touch-action: none;
    }

    /* Barra de herramientas */
    #toolbar {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        padding: 15px 20px;
        border-radius: 50px;
        display: flex;
        gap: 15px;
        align-items: center;
        backdrop-filter: blur(10px);
        z-index: 1000;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .tool-btn {
        width: 45px;
        height: 45px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 18px;
    }

    .tool-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .tool-btn.active {
        background: #4e73df;
        border-color: #4e73df;
    }

    .color-picker {
        display: flex;
        gap: 8px;
    }

    .color-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .color-btn:hover,
    .color-btn.active {
        border-color: #fff;
        transform: scale(1.1);
    }

    .size-selector {
        display: flex;
        gap: 8px;
        padding: 0 10px;
        border-left: 1px solid rgba(255, 255, 255, 0.2);
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }

    .size-btn {
        width: 35px;
        height: 35px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
    }

    .size-btn:hover,
    .size-btn.active {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
    }

    /* Indicador de carga */
    #loading-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.1);
        padding: 10px 20px;
        border-radius: 20px;
        display: none;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    #loading-indicator.show {
        display: flex;
    }

    .loading-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Indicador de slide */
    #slide-info {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #fff;
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    /* Controles de navegación */
    #navigation-controls {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        padding: 10px 20px;
        border-radius: 50px;
        display: flex;
        gap: 15px;
        align-items: center;
        backdrop-filter: blur(10px);
        z-index: 999;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    #navigation-controls button {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    #navigation-controls button:hover:not(:disabled) {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    #navigation-controls button:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    #sync-toggle {
        background: #4e73df;
        border-color: #4e73df;
        padding: 8px 20px;
        width: auto;
        border-radius: 20px;
        font-size: 14px;
    }

    #sync-toggle.synced {
        background: #1cc88a;
        border-color: #1cc88a;
    }

    /* Banner de desincronización */
    #desync-banner {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(231, 76, 60, 0.95);
        color: #fff;
        padding: 15px 30px;
        border-radius: 12px;
        display: none;
        z-index: 2001;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        text-align: center;
    }

    #desync-banner.show {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    #desync-banner strong {
        font-size: 16px;
    }

    #desync-banner small {
        display: block;
        margin-top: 5px;
        opacity: 0.9;
    }

    /* Panel de interacción */
    #interaction-panel {
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.9);
        padding: 15px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        backdrop-filter: blur(10px);
        z-index: 1000;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .interaction-btn {
        width: 50px;
        height: 50px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 20px;
        position: relative;
    }

    .interaction-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    .interaction-btn.active {
        background: #e74c3c;
        border-color: #e74c3c;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
        }
    }

    .interaction-btn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }

    /* Modal de pregunta */
    #question-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px;
        border-radius: 12px;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        min-width: 400px;
        max-width: 500px;
    }

    #question-modal.show {
        display: block;
    }

    #question-input {
        width: 100%;
        padding: 15px;
        border: 2px solid #4e73df;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
        resize: vertical;
        min-height: 100px;
    }

    /* Modal de reacciones */
    #reaction-picker {
        position: fixed;
        bottom: 90px;
        left: 20px;
        background: rgba(0, 0, 0, 0.95);
        padding: 15px;
        border-radius: 12px;
        display: none;
        z-index: 1001;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    #reaction-picker.show {
        display: flex;
        gap: 10px;
        animation: fadeIn 0.2s ease-out;
    }

    .reaction-option {
        font-size: 30px;
        cursor: pointer;
        transition: transform 0.2s;
        padding: 5px;
    }

    .reaction-option:hover {
        transform: scale(1.3);
    }

    /* Modal de texto */
    #text-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px;
        border-radius: 12px;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        min-width: 300px;
    }

    #text-modal.show {
        display: block;
    }

    #text-input {
        width: 100%;
        padding: 10px;
        border: 2px solid #4e73df;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 15px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }

    .modal-btn-primary {
        background: #4e73df;
        color: #fff;
    }

    .modal-btn-secondary {
        background: #858796;
        color: #fff;
    }

    /* Mensaje de sincronización */
    #sync-message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px 40px;
        border-radius: 12px;
        text-align: center;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    #sync-message.show {
        display: block;
    }

    #sync-message h3 {
        margin-bottom: 10px;
        color: #4e73df;
    }

    #sync-message p {
        margin: 0;
        color: #666;
    }
</style>

<div id="slide-container">
    <div id="slide-wrapper">
        <img id="slide-image"
             src="<?php echo htmlspecialchars($slide_data['path']); ?>"
             alt="Slide <?php echo $slide_number; ?>">
        <canvas id="annotation-canvas"></canvas>
    </div>
</div>

<!-- Barra de herramientas -->
<div id="toolbar">
    <button class="tool-btn active" id="tool-pen" title="Lápiz">
        <i class="fas fa-pen"></i>
    </button>
    <button class="tool-btn" id="tool-marker" title="Marcador">
        <i class="fas fa-highlighter"></i>
    </button>
    <button class="tool-btn" id="tool-text" title="Texto">
        <i class="fas fa-font"></i>
    </button>
    <button class="tool-btn" id="tool-eraser" title="Borrador">
        <i class="fas fa-eraser"></i>
    </button>

    <div class="size-selector">
        <button class="size-btn" data-size="2" title="Fino">S</button>
        <button class="size-btn active" data-size="4" title="Medio">M</button>
        <button class="size-btn" data-size="8" title="Grueso">L</button>
    </div>

    <div class="color-picker">
        <button class="color-btn active" style="background: #000;" data-color="#000000"></button>
        <button class="color-btn" style="background: #e74c3c;" data-color="#e74c3c"></button>
        <button class="color-btn" style="background: #3498db;" data-color="#3498db"></button>
        <button class="color-btn" style="background: #2ecc71;" data-color="#2ecc71"></button>
        <button class="color-btn" style="background: #f39c12;" data-color="#f39c12"></button>
    </div>

    <button class="tool-btn" id="tool-undo" title="Deshacer">
        <i class="fas fa-undo"></i>
    </button>
    <button class="tool-btn" id="tool-clear" title="Limpiar todo">
        <i class="fas fa-trash"></i>
    </button>
    <button class="tool-btn" id="tool-save" title="Guardar anotaciones">
        <i class="fas fa-save"></i>
    </button>
</div>

<!-- Controles de navegación -->
<div id="navigation-controls">
    <button id="nav-prev" title="Slide anterior">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button id="sync-toggle" class="synced" title="Sincronizado con presentador">
        <i class="fas fa-sync"></i> Sincronizado
    </button>
    <button id="nav-next" title="Siguiente slide">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<!-- Banner de desincronización -->
<div id="desync-banner">
    <strong><i class="fas fa-unlink me-2"></i>Desincronizado</strong>
    <small>Estás en el slide <span id="current-viewing"></span>, el profesor está en <span id="presenter-at"></span></small>
</div>

<!-- Panel de interacción -->
<div id="interaction-panel">
    <button class="interaction-btn" id="raise-hand-btn" title="Levantar mano">
        <i class="fas fa-hand-paper"></i>
    </button>
    <button class="interaction-btn" id="ask-question-btn" title="Hacer pregunta">
        <i class="fas fa-question-circle"></i>
    </button>
    <button class="interaction-btn" id="understanding-btn" title="Medidor de comprensión">
        <i class="fas fa-brain"></i>
    </button>
    <button class="interaction-btn" id="reaction-btn" title="Reacciones rápidas">
        <i class="fas fa-smile"></i>
    </button>
</div>

<!-- Picker de reacciones -->
<div id="reaction-picker">
    <span class="reaction-option" data-reaction="👍">👍</span>
    <span class="reaction-option" data-reaction="❤️">❤️</span>
    <span class="reaction-option" data-reaction="😮">😮</span>
    <span class="reaction-option" data-reaction="🤔">🤔</span>
    <span class="reaction-option" data-reaction="👏">👏</span>
    <span class="reaction-option" data-reaction="🎉">🎉</span>
</div>

<div id="slide-info">
    <i class="fas fa-file-pdf me-2"></i>
    Slide <span id="current-slide"><?php echo $slide_number; ?></span> / <span id="total-slides"><?php echo count($test_data['pdf_images']); ?></span>
</div>

<div id="loading-indicator">
    <div class="loading-spinner"></div>
    <span>Sincronizando...</span>
</div>

<div id="sync-message">
    <h3><i class="fas fa-sync-alt"></i> Sincronizando</h3>
    <p>Cargando nuevo contenido...</p>
</div>

<!-- Modal de texto -->
<div id="text-modal">
    <h4 style="margin-bottom: 15px;">Agregar texto</h4>
    <input type="text" id="text-input" placeholder="Escribe tu texto aquí...">
    <div class="modal-buttons">
        <button class="modal-btn modal-btn-secondary" id="text-cancel">Cancelar</button>
        <button class="modal-btn modal-btn-primary" id="text-ok">Agregar</button>
    </div>
</div>

<!-- Modal de pregunta -->
<div id="question-modal">
    <h4 style="margin-bottom: 15px;"><i class="fas fa-question-circle me-2"></i>Hacer una pregunta</h4>
    <textarea id="question-input" placeholder="Escribe tu pregunta aquí... (será enviada de forma anónima)"></textarea>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="question-anonymous" checked>
        <label class="form-check-label" for="question-anonymous">
            Enviar de forma anónima
        </label>
    </div>
    <div class="modal-buttons">
        <button class="modal-btn modal-btn-secondary" id="question-cancel">Cancelar</button>
        <button class="modal-btn modal-btn-primary" id="question-send">Enviar pregunta</button>
    </div>
</div>

<script>
const codigo = '<?php echo $codigo_sesion; ?>';
const participanteId = '<?php echo $participante_id; ?>';
const participanteNombre = '<?php echo isset($nombre_participante) ? addslashes($nombre_participante) : "Estudiante"; ?>';
let currentSequenceIndex = <?php echo $sequence_index; ?>;
let presenterSequenceIndex = <?php echo $sequence_index; ?>; // Índice del presentador
let viewingSequenceIndex = <?php echo $sequence_index; ?>; // Índice que está viendo el estudiante
const slideNumber = <?php echo $slide_number; ?>;
const totalSlides = <?php echo count($test_data['pdf_images']); ?>;
const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
const slideSequence = <?php echo json_encode($test_data['pdf_sequence']); ?>;

// Estado de sincronización
let isSynced = true;
let handRaised = false;
let currentUnderstanding = null; // 'confused' o 'understood'

// Variables de estado del canvas
let canvas, ctx;
let isDrawing = false;
let currentTool = 'pen';
let currentColor = '#000000';
let currentSize = 4;
let strokes = [];
let currentStroke = null;
let textPosition = null;

// Inicializar canvas
function initCanvas() {
    canvas = document.getElementById('annotation-canvas');
    ctx = canvas.getContext('2d');
    const img = document.getElementById('slide-image');

    // Esperar a que la imagen cargue
    img.onload = function() {
        resizeCanvas();
    };

    if (img.complete) {
        resizeCanvas();
    }
}

function resizeCanvas() {
    const img = document.getElementById('slide-image');
    canvas.width = img.width;
    canvas.height = img.height;

    // Posicionar el canvas sobre la imagen
    const rect = img.getBoundingClientRect();
    canvas.style.width = img.width + 'px';
    canvas.style.height = img.height + 'px';

    // Redibujar anotaciones existentes
    redrawAnnotations();
}

// Eventos del canvas
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

// Soporte táctil
canvas.addEventListener('touchstart', handleTouchStart);
canvas.addEventListener('touchmove', handleTouchMove);
canvas.addEventListener('touchend', handleTouchEnd);

function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchEnd(e) {
    e.preventDefault();
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
}

function getCanvasCoordinates(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    return {
        x: (e.clientX - rect.left) * scaleX,
        y: (e.clientY - rect.top) * scaleY
    };
}

function startDrawing(e) {
    if (currentTool === 'text') {
        const coords = getCanvasCoordinates(e);
        textPosition = coords;
        showTextModal();
        return;
    }

    isDrawing = true;
    const coords = getCanvasCoordinates(e);

    currentStroke = {
        tool: currentTool,
        color: currentColor,
        size: currentSize,
        points: [coords]
    };
}

function draw(e) {
    if (!isDrawing || currentTool === 'text') return;

    const coords = getCanvasCoordinates(e);
    currentStroke.points.push(coords);

    drawStroke(currentStroke);
}

function stopDrawing() {
    if (!isDrawing) return;

    isDrawing = false;

    if (currentStroke && currentStroke.points.length > 0) {
        strokes.push(currentStroke);
        currentStroke = null;
        autoSaveAnnotations();
    }
}

function drawStroke(stroke) {
    if (!stroke || stroke.points.length === 0) return;

    ctx.beginPath();
    ctx.strokeStyle = stroke.color;
    ctx.lineWidth = stroke.size;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    if (stroke.tool === 'eraser') {
        ctx.globalCompositeOperation = 'destination-out';
        ctx.lineWidth = stroke.size * 3;
    } else if (stroke.tool === 'marker') {
        ctx.globalAlpha = 0.5;
    } else {
        ctx.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = 1.0;
    }

    ctx.moveTo(stroke.points[0].x, stroke.points[0].y);

    for (let i = 1; i < stroke.points.length; i++) {
        ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
    }

    ctx.stroke();
    ctx.globalCompositeOperation = 'source-over';
    ctx.globalAlpha = 1.0;
}

function drawText(text, position, color, size) {
    ctx.font = `${size * 5}px Arial`;
    ctx.fillStyle = color;
    ctx.fillText(text, position.x, position.y);
}

function redrawAnnotations() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    strokes.forEach(stroke => {
        if (stroke.type === 'text') {
            drawText(stroke.text, stroke.position, stroke.color, stroke.size);
        } else {
            drawStroke(stroke);
        }
    });
}

// Herramientas
document.getElementById('tool-pen').addEventListener('click', () => setTool('pen'));
document.getElementById('tool-marker').addEventListener('click', () => setTool('marker'));
document.getElementById('tool-text').addEventListener('click', () => setTool('text'));
document.getElementById('tool-eraser').addEventListener('click', () => setTool('eraser'));

function setTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tool-' + tool).classList.add('active');

    canvas.style.cursor = tool === 'eraser' ? 'not-allowed' : 'crosshair';
}

// Colores
document.querySelectorAll('.color-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        currentColor = btn.dataset.color;
        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Tamaños
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        currentSize = parseInt(btn.dataset.size);
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Deshacer
document.getElementById('tool-undo').addEventListener('click', () => {
    if (strokes.length > 0) {
        strokes.pop();
        redrawAnnotations();
        autoSaveAnnotations();
    }
});

// Limpiar todo
document.getElementById('tool-clear').addEventListener('click', () => {
    if (confirm('¿Estás seguro de que quieres borrar todas las anotaciones de esta diapositiva?')) {
        strokes = [];
        redrawAnnotations();
        autoSaveAnnotations();
    }
});

// Guardar
document.getElementById('tool-save').addEventListener('click', saveAnnotations);

// Modal de texto
function showTextModal() {
    document.getElementById('text-modal').classList.add('show');
    document.getElementById('text-input').value = '';
    document.getElementById('text-input').focus();
}

function hideTextModal() {
    document.getElementById('text-modal').classList.remove('show');
    textPosition = null;
}

document.getElementById('text-ok').addEventListener('click', () => {
    const text = document.getElementById('text-input').value;
    if (text && textPosition) {
        const textStroke = {
            type: 'text',
            text: text,
            position: textPosition,
            color: currentColor,
            size: currentSize
        };
        strokes.push(textStroke);
        drawText(text, textPosition, currentColor, currentSize);
        autoSaveAnnotations();
    }
    hideTextModal();
});

document.getElementById('text-cancel').addEventListener('click', hideTextModal);

// Auto-guardar cada 5 segundos
let saveTimeout;
function autoSaveAnnotations() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(saveAnnotations, 5000);
}

// Guardar anotaciones
function saveAnnotations() {
    const annotationData = {
        codigo_sesion: codigo,
        id_participante: participanteId,
        slide_number: slideNumber,
        anotaciones: strokes
    };

    fetch(serverUrl + 'api/guardar_anotaciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(annotationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Anotaciones guardadas correctamente');
        } else {
            console.error('Error al guardar anotaciones:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al guardar anotaciones:', error);
    });
}

// Cargar anotaciones existentes
function loadAnnotations() {
    fetch(serverUrl + 'api/obtener_anotaciones.php?codigo=' + codigo + '&id_participante=' + participanteId + '&slide_number=' + slideNumber)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.anotaciones) {
                strokes = data.anotaciones;
                redrawAnnotations();
            }
        })
        .catch(error => {
            console.error('Error al cargar anotaciones:', error);
        });
}

// Verificar cambios en la secuencia (modo inteligente con sincronización)
function checkSequenceUpdate() {
    const loadingIndicator = document.getElementById('loading-indicator');
    loadingIndicator.classList.add('show');

    fetch(serverUrl + 'api/get_sequence_index.php?codigo=' + codigo)
        .then(response => response.json())
        .then(data => {
            loadingIndicator.classList.remove('show');

            if (data.success && data.sequence_index !== undefined) {
                const newIndex = parseInt(data.sequence_index);
                presenterSequenceIndex = newIndex;

                // Actualizar banner si está desincronizado
                updateSyncStatus();

                // Solo recargar si está sincronizado Y el índice cambió
                if (isSynced && newIndex !== currentSequenceIndex) {
                    // Guardar antes de cambiar de slide
                    saveAnnotations();

                    const syncMessage = document.getElementById('sync-message');
                    syncMessage.classList.add('show');

                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            }
        })
        .catch(error => {
            console.error('Error al verificar actualización:', error);
            loadingIndicator.classList.remove('show');
        });
}

// Actualizar estado de sincronización visual
function updateSyncStatus() {
    const desyncBanner = document.getElementById('desync-banner');
    const syncToggle = document.getElementById('sync-toggle');

    if (!isSynced && presenterSequenceIndex !== viewingSequenceIndex) {
        // Mostrar banner de desincronización
        desyncBanner.classList.add('show');
        document.getElementById('current-viewing').textContent = getSlideNumberFromIndex(viewingSequenceIndex);
        document.getElementById('presenter-at').textContent = getSlideNumberFromIndex(presenterSequenceIndex);

        syncToggle.classList.remove('synced');
        syncToggle.innerHTML = '<i class="fas fa-unlink"></i> Desincronizado';
    } else {
        desyncBanner.classList.remove('show');
        syncToggle.classList.add('synced');
        syncToggle.innerHTML = '<i class="fas fa-sync"></i> Sincronizado';
    }

    // Actualizar botones de navegación
    updateNavigationButtons();
}

// Obtener número de slide desde índice de secuencia
function getSlideNumberFromIndex(index) {
    if (index < 0 || index >= slideSequence.length) return '?';
    const item = slideSequence[index];
    return item.type === 'slide' ? item.number : `Pregunta ${item.id}`;
}

// Actualizar estado de botones de navegación
function updateNavigationButtons() {
    const prevBtn = document.getElementById('nav-prev');
    const nextBtn = document.getElementById('nav-next');

    // Puede retroceder si no está en el primer slide
    prevBtn.disabled = viewingSequenceIndex <= 0;

    // Puede avanzar solo hasta donde está el presentador
    nextBtn.disabled = viewingSequenceIndex >= presenterSequenceIndex;
}

// Navegación manual
function navigateTo(direction) {
    const targetIndex = viewingSequenceIndex + direction;

    // Validar límites
    if (targetIndex < 0 || targetIndex >= slideSequence.length) return;
    if (targetIndex > presenterSequenceIndex) return; // No puede adelantarse al profesor

    // Desincroni zar automáticamente
    if (isSynced) {
        isSynced = false;
    }

    // Guardar anotaciones actuales
    saveAnnotations();

    // Navegar al nuevo slide
    viewingSequenceIndex = targetIndex;
    const targetItem = slideSequence[targetIndex];

    // Construir URL para navegar
    let url = `participante.php?codigo=${codigo}`;
    window.location.href = url;
}

// Toggle de sincronización
document.getElementById('sync-toggle').addEventListener('click', () => {
    if (isSynced) {
        // Desincronizar
        isSynced = false;
        viewingSequenceIndex = currentSequenceIndex;
    } else {
        // Resincronizar - volver a seguir al presentador
        isSynced = true;

        // Si el presentador está en un slide diferente, navegar allí
        if (presenterSequenceIndex !== currentSequenceIndex) {
            saveAnnotations();
            window.location.reload();
        }
    }

    updateSyncStatus();
});

// Botones de navegación
document.getElementById('nav-prev').addEventListener('click', () => navigateTo(-1));
document.getElementById('nav-next').addEventListener('click', () => navigateTo(1));

// ========== INTERACCIONES ==========

// Levantar mano
document.getElementById('raise-hand-btn').addEventListener('click', () => {
    handRaised = !handRaised;
    const btn = document.getElementById('raise-hand-btn');

    if (handRaised) {
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }

    // Enviar estado al servidor
    sendInteraction('raise_hand', { raised: handRaised });
});

// Hacer pregunta
document.getElementById('ask-question-btn').addEventListener('click', () => {
    document.getElementById('question-modal').classList.add('show');
    document.getElementById('question-input').value = '';
    document.getElementById('question-input').focus();
});

document.getElementById('question-cancel').addEventListener('click', () => {
    document.getElementById('question-modal').classList.remove('show');
});

document.getElementById('question-send').addEventListener('click', () => {
    const question = document.getElementById('question-input').value.trim();
    const anonymous = document.getElementById('question-anonymous').checked;

    if (question) {
        sendInteraction('question', {
            question: question,
            anonymous: anonymous,
            slide_number: slideNumber
        });

        document.getElementById('question-modal').classList.remove('show');

        // Feedback visual
        const btn = document.getElementById('ask-question-btn');
        btn.style.background = '#1cc88a';
        setTimeout(() => {
            btn.style.background = '';
        }, 2000);
    }
});

// Medidor de comprensión
document.getElementById('understanding-btn').addEventListener('click', () => {
    // Ciclar entre: null -> confused -> understood -> null
    if (currentUnderstanding === null) {
        currentUnderstanding = 'confused';
    } else if (currentUnderstanding === 'confused') {
        currentUnderstanding = 'understood';
    } else {
        currentUnderstanding = null;
    }

    const btn = document.getElementById('understanding-btn');

    if (currentUnderstanding === 'confused') {
        btn.style.background = '#e74c3c';
        btn.innerHTML = '<i class="fas fa-frown"></i>';
    } else if (currentUnderstanding === 'understood') {
        btn.style.background = '#1cc88a';
        btn.innerHTML = '<i class="fas fa-smile"></i>';
    } else {
        btn.style.background = '';
        btn.innerHTML = '<i class="fas fa-brain"></i>';
    }

    sendInteraction('understanding', {
        level: currentUnderstanding,
        slide_number: slideNumber
    });
});

// Reacciones rápidas
document.getElementById('reaction-btn').addEventListener('click', () => {
    const picker = document.getElementById('reaction-picker');
    picker.classList.toggle('show');
});

document.querySelectorAll('.reaction-option').forEach(option => {
    option.addEventListener('click', () => {
        const reaction = option.dataset.reaction;

        sendInteraction('reaction', {
            reaction: reaction,
            slide_number: slideNumber
        });

        // Ocultar picker
        document.getElementById('reaction-picker').classList.remove('show');

        // Feedback visual
        const btn = document.getElementById('reaction-btn');
        btn.innerHTML = reaction;
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-smile"></i>';
        }, 3000);
    });
});

// Enviar interacción al servidor
function sendInteraction(type, data) {
    const interactionData = {
        codigo_sesion: codigo,
        id_participante: participanteId,
        nombre_participante: participanteNombre,
        type: type,
        data: data,
        timestamp: new Date().toISOString()
    };

    fetch(serverUrl + 'api/guardar_interaccion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(interactionData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Interacción enviada:', type);
        } else {
            console.error('Error al enviar interacción:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al enviar interacción:', error);
    });
}

// Precargar slides
function preloadAllSlides() {
    const slides = <?php echo json_encode($test_data['pdf_images']); ?>;

    slides.forEach((slide, index) => {
        const img = new Image();
        img.src = slide.path;
    });
}

// Inicializar
window.addEventListener('load', function() {
    initCanvas();
    loadAnnotations();
    preloadAllSlides();
    updateSyncStatus(); // Inicializar estado de sincronización
    console.log('Visor con anotaciones y navegación libre iniciado');
});

// Redimensionar canvas al cambiar tamaño de ventana
window.addEventListener('resize', resizeCanvas);

// Verificar cambios cada 1.5 segundos
setInterval(checkSequenceUpdate, 1500);

// Soporte para fullscreen
document.getElementById('slide-container').addEventListener('dblclick', function() {
    const elem = document.documentElement;

    if (!document.fullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
});

// Guardar antes de salir
window.addEventListener('beforeunload', () => {
    saveAnnotations();
});
</script>
