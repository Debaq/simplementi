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

        /* Contenedor principal fullscreen */
        #presentation-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #000;
        }

        /* Slides del PDF */
        .slide-content {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
        }

        /* Preguntas */
        .question-content {
            max-width: 900px;
            width: 90%;
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
            transition: all 0.3s;
        }

        .option-item:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(10px);
        }

        /* Controles de navegación */
        .nav-controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            background: rgba(0,0,0,0.7);
            padding: 15px 25px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .nav-btn {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            color: #fff;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
        }

        .nav-btn:hover:not(:disabled) {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Contador de posición */
        .position-counter {
            position: fixed;
            top: 30px;
            right: 30px;
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        /* Botón de salir */
        .exit-btn {
            position: fixed;
            top: 30px;
            left: 30px;
            background: rgba(220,53,69,0.8);
            border: none;
            color: #fff;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .exit-btn:hover {
            background: rgba(220,53,69,1);
            transform: scale(1.05);
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
    </style>
</head>
<body>
    <div id="presentation-container">
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

    <!-- Contador de posición -->
    <div class="position-counter">
        <i class="fas fa-list-ol me-2"></i>
        <?php echo ($sequence_index + 1); ?> / <?php echo $total_items; ?>
    </div>

    <!-- Botón de salir -->
    <button class="exit-btn" onclick="exitPresentation()">
        <i class="fas fa-times me-2"></i> Salir
    </button>

    <!-- Controles de navegación -->
    <div class="nav-controls">
        <button class="nav-btn" id="prev-btn"
                <?php echo $sequence_index <= 0 ? 'disabled' : ''; ?>
                onclick="navigate(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div style="color: #fff; display: flex; align-items: center; padding: 0 15px;">
            <?php if ($item_data['type'] === 'slide'): ?>
                <i class="fas fa-file-pdf"></i>
            <?php else: ?>
                <i class="fas fa-question-circle"></i>
            <?php endif; ?>
        </div>

        <button class="nav-btn" id="next-btn"
                <?php echo $sequence_index >= $total_items - 1 ? 'disabled' : ''; ?>
                onclick="navigate(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <script>
        const codigo = '<?php echo $codigo_sesion; ?>';
        const currentIndex = <?php echo $sequence_index; ?>;
        const totalItems = <?php echo $total_items; ?>;

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

        // Entrar automáticamente en fullscreen al cargar
        window.addEventListener('load', function() {
            enterFullscreen();
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
