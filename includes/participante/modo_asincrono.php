<?php
/**
 * Modo asíncrono - Permite a los estudiantes ver la presentación sin el presentador
 * Reproduce automáticamente los audios y permite navegación libre
 */

// Obtener índice actual del estudiante desde el parámetro GET, o 0 por defecto
$async_index = isset($_GET['index']) ? intval($_GET['index']) : 0;

// Construir la secuencia completa: slides + preguntas intercaladas
$sequence = [];

if (!empty($test_data['pdf_sequence'])) {
    // Si ya hay una secuencia definida, usarla
    $sequence = $test_data['pdf_sequence'];
} else {
    // Construir secuencia simple: todos los slides seguidos de todas las preguntas
    if (!empty($test_data['pdf_pages'])) {
        for ($i = 1; $i <= $test_data['pdf_pages']; $i++) {
            $sequence[] = [
                'type' => 'slide',
                'number' => $i
            ];
        }
    }

    // Agregar todas las preguntas al final
    foreach ($test_data['preguntas'] as $pregunta) {
        $sequence[] = [
            'type' => 'question',
            'id' => $pregunta['id']
        ];
    }
}

// Validar índice
if ($async_index < 0 || $async_index >= count($sequence)) {
    $async_index = 0;
}

$current_item = $sequence[$async_index];
$total_items = count($sequence);

// Verificar si un solo intento está habilitado
$un_solo_intento = isset($test_data['configuracion']['un_solo_intento']) &&
                   $test_data['configuracion']['un_solo_intento'] === true;

// Obtener audios grabados
$audios_grabados = $test_data['audios_grabados'] ?? [];

// Incluir head
include('includes/participante/head.php');
?>

<style>
.async-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.async-header {
    background: linear-gradient(to right, #4e73df, #224abe);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.async-progress {
    background: white;
    border-radius: 10px;
    padding: 10px;
    margin-top: 10px;
}

.async-progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.async-progress-fill {
    height: 100%;
    background: linear-gradient(to right, #1cc88a, #169b6b);
    transition: width 0.3s ease;
}

.async-content {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    min-height: 500px;
}

.async-slide {
    text-align: center;
}

.async-slide img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.async-audio-player {
    margin-top: 20px;
    text-align: center;
}

.async-audio-player audio {
    width: 100%;
    max-width: 600px;
}

.async-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.async-btn {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 8px;
    transition: all 0.3s;
}

.async-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.async-question {
    padding: 20px 0;
}

.question-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: #2c3e50;
}

.answer-option {
    margin-bottom: 15px;
}

.answer-option input[type="radio"],
.answer-option input[type="checkbox"] {
    margin-right: 10px;
}

.answer-option label {
    font-size: 1.1rem;
    cursor: pointer;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    display: block;
    transition: all 0.3s;
}

.answer-option label:hover {
    background: #f8f9fa;
    border-color: #4e73df;
}

.answer-option input:checked + label {
    background: #e7f3ff;
    border-color: #4e73df;
    font-weight: bold;
}

.async-alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.async-alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.async-alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.async-alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}
</style>

<div class="async-container">
    <!-- Header con título y progreso -->
    <div class="async-header">
        <h2><i class="fas fa-graduation-cap me-2"></i><?php echo htmlspecialchars($test_data['titulo']); ?></h2>
        <p class="mb-0"><?php echo htmlspecialchars($test_data['descripcion'] ?? ''); ?></p>

        <div class="async-progress">
            <div class="d-flex justify-content-between mb-2">
                <span><strong>Progreso:</strong> <?php echo ($async_index + 1); ?> de <?php echo $total_items; ?></span>
                <span><?php echo round((($async_index + 1) / $total_items) * 100); ?>%</span>
            </div>
            <div class="async-progress-bar">
                <div class="async-progress-fill" style="width: <?php echo (($async_index + 1) / $total_items) * 100; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="async-content">
        <?php if ($current_item['type'] === 'slide'): ?>
            <!-- Mostrar slide con audio -->
            <?php
            $slide_number = $current_item['number'];
            $slide_path = "data/presentaciones/{$test_id}/slide_{$slide_number}.jpg";
            $has_audio = isset($audios_grabados[$slide_number]);
            $audio_path = $has_audio ? $audios_grabados[$slide_number] : null;
            ?>

            <div class="async-slide">
                <h3 class="mb-4">Diapositiva <?php echo $slide_number; ?> de <?php echo $test_data['pdf_pages']; ?></h3>

                <?php if (file_exists($slide_path)): ?>
                    <img src="<?php echo $slide_path; ?>" alt="Slide <?php echo $slide_number; ?>" class="slide-image">
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No se encontró la imagen de esta diapositiva.
                    </div>
                <?php endif; ?>

                <?php if ($has_audio): ?>
                    <div class="async-audio-player">
                        <audio id="slide-audio" controls autoplay>
                            <source src="<?php echo $audio_path; ?>" type="audio/webm">
                            Tu navegador no soporta el elemento de audio.
                        </audio>
                    </div>
                <?php else: ?>
                    <div class="async-alert async-alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta diapositiva no tiene audio. Haz clic en "Siguiente" cuando estés listo.
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_item['type'] === 'question'): ?>
            <!-- Mostrar pregunta -->
            <?php
            $question_id = $current_item['id'];
            $pregunta_actual = null;

            // Buscar la pregunta por ID
            foreach ($test_data['preguntas'] as $pregunta) {
                if ($pregunta['id'] === $question_id) {
                    $pregunta_actual = $pregunta;
                    break;
                }
            }

            if (!$pregunta_actual):
            ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error: Pregunta no encontrada.
                </div>
            <?php
            else:
                // Verificar si ya respondió
                $ya_respondio = false;
                $respuesta_dada = null;

                foreach ($session_data['participantes'] as $participante) {
                    if ($participante['id'] == $participante_id) {
                        foreach ($participante['respuestas'] as $respuesta) {
                            if ($respuesta['id_pregunta'] == $pregunta_actual['id']) {
                                $ya_respondio = true;
                                $respuesta_dada = $respuesta['respuesta'];
                                break 2;
                            }
                        }
                    }
                }
            ?>

            <div class="async-question">
                <div class="question-title">
                    <i class="fas fa-question-circle text-primary me-2"></i>
                    <?php echo htmlspecialchars($pregunta_actual['texto'] ?? $pregunta_actual['pregunta']); ?>
                </div>

                <?php if ($ya_respondio && $un_solo_intento): ?>
                    <div class="async-alert async-alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Ya respondiste esta pregunta.</strong>
                        <?php if ($pregunta_actual['tipo'] === 'opcion_multiple' || $pregunta_actual['tipo'] === 'verdadero_falso'): ?>
                            Tu respuesta: <strong><?php echo htmlspecialchars($respuesta_dada); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php elseif ($ya_respondio): ?>
                    <div class="async-alert async-alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Ya respondiste esta pregunta, pero puedes cambiar tu respuesta si lo deseas.
                    </div>
                <?php endif; ?>

                <form method="POST" action="api/guardar_respuesta.php" id="question-form">
                    <input type="hidden" name="codigo_sesion" value="<?php echo htmlspecialchars($codigo_sesion); ?>">
                    <input type="hidden" name="id_participante" value="<?php echo htmlspecialchars($participante_id); ?>">
                    <input type="hidden" name="id_pregunta" value="<?php echo $pregunta_actual['id']; ?>">
                    <input type="hidden" name="tiempo_respuesta" value="0" id="tiempo-respuesta">

                    <?php if ($pregunta_actual['tipo'] === 'opcion_multiple'): ?>
                        <!-- Opción múltiple -->
                        <?php foreach ($pregunta_actual['opciones'] as $index => $opcion): ?>
                            <div class="answer-option">
                                <input type="radio"
                                       name="respuesta"
                                       id="opcion-<?php echo $index; ?>"
                                       value="<?php echo htmlspecialchars($opcion); ?>"
                                       <?php echo ($ya_respondio && $respuesta_dada === $opcion) ? 'checked' : ''; ?>
                                       <?php echo ($ya_respondio && $un_solo_intento) ? 'disabled' : ''; ?>
                                       required>
                                <label for="opcion-<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($opcion); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    <?php elseif ($pregunta_actual['tipo'] === 'verdadero_falso'): ?>
                        <!-- Verdadero/Falso -->
                        <div class="answer-option">
                            <input type="radio"
                                   name="respuesta"
                                   id="opcion-true"
                                   value="true"
                                   <?php echo ($ya_respondio && $respuesta_dada === 'true') ? 'checked' : ''; ?>
                                   <?php echo ($ya_respondio && $un_solo_intento) ? 'disabled' : ''; ?>
                                   required>
                            <label for="opcion-true">
                                <i class="fas fa-check-circle text-success me-2"></i>Verdadero
                            </label>
                        </div>
                        <div class="answer-option">
                            <input type="radio"
                                   name="respuesta"
                                   id="opcion-false"
                                   value="false"
                                   <?php echo ($ya_respondio && $respuesta_dada === 'false') ? 'checked' : ''; ?>
                                   <?php echo ($ya_respondio && $un_solo_intento) ? 'disabled' : ''; ?>
                                   required>
                            <label for="opcion-false">
                                <i class="fas fa-times-circle text-danger me-2"></i>Falso
                            </label>
                        </div>

                    <?php elseif ($pregunta_actual['tipo'] === 'palabra_libre' || $pregunta_actual['tipo'] === 'nube_palabras'): ?>
                        <!-- Respuesta libre -->
                        <div class="mb-3">
                            <textarea class="form-control"
                                      name="respuesta"
                                      rows="4"
                                      placeholder="Escribe tu respuesta aquí..."
                                      <?php echo ($ya_respondio && $un_solo_intento) ? 'disabled' : ''; ?>
                                      required><?php echo $ya_respondio ? htmlspecialchars($respuesta_dada) : ''; ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if (!($ya_respondio && $un_solo_intento)): ?>
                        <button type="submit" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-paper-plane me-2"></i>
                            <?php echo $ya_respondio ? 'Actualizar respuesta' : 'Enviar respuesta'; ?>
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Controles de navegación -->
    <div class="async-controls">
        <a href="?codigo=<?php echo $codigo_sesion; ?>&index=<?php echo max(0, $async_index - 1); ?>"
           class="btn btn-secondary async-btn <?php echo $async_index === 0 ? 'disabled' : ''; ?>">
            <i class="fas fa-arrow-left me-2"></i>Anterior
        </a>

        <div class="text-center">
            <span class="badge bg-primary" style="font-size: 1rem;">
                <?php echo $async_index + 1; ?> / <?php echo $total_items; ?>
            </span>
        </div>

        <?php if ($async_index < $total_items - 1): ?>
            <a href="?codigo=<?php echo $codigo_sesion; ?>&index=<?php echo $async_index + 1; ?>"
               class="btn btn-primary async-btn">
                Siguiente<i class="fas fa-arrow-right ms-2"></i>
            </a>
        <?php else: ?>
            <a href="participante_resumen.php?codigo=<?php echo $codigo_sesion; ?>&participante=<?php echo $participante_id; ?>"
               class="btn btn-success async-btn">
                Ver resultados<i class="fas fa-check ms-2"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reproducción automática de audio al final
    const audio = document.getElementById('slide-audio');
    if (audio) {
        audio.addEventListener('ended', function() {
            // Sugerir avanzar automáticamente después del audio
            const nextBtn = document.querySelector('.async-controls .btn-primary');
            if (nextBtn && !nextBtn.classList.contains('disabled')) {
                // Opcional: descomentar para avance automático
                // setTimeout(() => {
                //     nextBtn.click();
                // }, 1000);
            }
        });
    }

    // Manejar envío de formulario de pregunta
    const questionForm = document.getElementById('question-form');
    if (questionForm) {
        questionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api/guardar_respuesta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Respuesta guardada correctamente');
                    // Avanzar a la siguiente
                    const nextBtn = document.querySelector('.async-controls .btn-primary, .async-controls .btn-success');
                    if (nextBtn) {
                        nextBtn.click();
                    }
                } else {
                    if (data.single_attempt) {
                        alert(data.error || 'Ya respondiste esta pregunta');
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo guardar la respuesta'));
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al enviar la respuesta');
            });
        });
    }
});
</script>

<?php
// Incluir scripts de participante
include('includes/participante/scripts.php');
?>
