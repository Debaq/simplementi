<div class="card shadow">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-microphone me-2"></i>Grabaciones de audio por diapositiva
            </h6>
            <span class="badge bg-info">
                <?php
                $total_pages = isset($presentacion_data['pdf_pages']) ? $presentacion_data['pdf_pages'] : 0;
                $audios_count = !empty($presentacion_data['audios_grabados']) ? count($presentacion_data['audios_grabados']) : 0;
                echo "$audios_count / $total_pages grabadas";
                ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Instrucciones:</strong> Graba tu explicación para cada diapositiva. Los estudiantes podrán escuchar tus grabaciones cuando vean la presentación en modo asíncrono.
            <ul class="mt-2 mb-0">
                <li>Haz clic en <i class="fas fa-circle text-danger"></i> <strong>Grabar</strong> para iniciar la grabación</li>
                <li>Habla normalmente hacia tu micrófono</li>
                <li>Haz clic en <i class="fas fa-stop"></i> <strong>Detener</strong> cuando termines</li>
                <li>Puedes escuchar y volver a grabar cuantas veces quieras</li>
            </ul>
        </div>

        <?php if ($total_pages == 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No hay diapositivas disponibles. Por favor, sube un PDF primero en la pestaña de "Información básica".
            </div>
        <?php else: ?>
            <div id="slides-audio-container" class="row">
                <?php
                // Generar una tarjeta para cada página del PDF
                $pdf_folder = 'data/presentaciones/' . $id_presentacion;
                $audios_grabados = $presentacion_data['audios_grabados'] ?? [];

                for ($page = 1; $page <= $total_pages; $page++):
                    $slide_image = $pdf_folder . '/slide_' . $page . '.jpg';
                    $has_audio = isset($audios_grabados[$page]);
                    $audio_file = $has_audio ? $audios_grabados[$page] : null;
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 slide-audio-card" data-page="<?php echo $page; ?>">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Diapositiva <?php echo $page; ?></span>
                            <?php if ($has_audio): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Con audio</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="fas fa-times"></i> Sin audio</span>
                            <?php endif; ?>
                        </div>

                        <?php if (file_exists($slide_image)): ?>
                        <div class="card-img-top p-2 bg-light">
                            <img src="<?php echo $slide_image; ?>" class="img-fluid" alt="Slide <?php echo $page; ?>" style="max-height: 200px; width: 100%; object-fit: contain;">
                        </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <!-- Controles de grabación -->
                            <div class="recording-controls mb-2">
                                <button class="btn btn-danger btn-sm w-100 mb-2 btn-start-recording" data-page="<?php echo $page; ?>">
                                    <i class="fas fa-circle"></i> Grabar
                                </button>
                                <button class="btn btn-secondary btn-sm w-100 mb-2 btn-stop-recording" data-page="<?php echo $page; ?>" style="display: none;">
                                    <i class="fas fa-stop"></i> Detener
                                </button>
                                <div class="recording-timer text-center mb-2" style="display: none;">
                                    <span class="badge bg-danger">
                                        <i class="fas fa-circle" style="animation: pulse 1s infinite;"></i>
                                        <span class="timer-text">00:00</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Controles de reproducción -->
                            <?php if ($has_audio): ?>
                            <div class="playback-controls">
                                <audio class="w-100 mb-2" controls src="<?php echo $audio_file; ?>"></audio>
                                <button class="btn btn-outline-danger btn-sm w-100 btn-delete-audio" data-page="<?php echo $page; ?>">
                                    <i class="fas fa-trash"></i> Eliminar audio
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="playback-controls" style="display: none;">
                                <audio class="w-100 mb-2" controls></audio>
                                <button class="btn btn-outline-danger btn-sm w-100 btn-delete-audio" data-page="<?php echo $page; ?>">
                                    <i class="fas fa-trash"></i> Eliminar audio
                                </button>
                            </div>
                            <?php endif; ?>

                            <!-- Estado de carga -->
                            <div class="upload-status text-center" style="display: none;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Subiendo...</span>
                                </div>
                                <small class="d-block mt-1">Subiendo audio...</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.slide-audio-card {
    transition: transform 0.2s;
}

.slide-audio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const presentacionId = '<?php echo $id_presentacion; ?>';
    let mediaRecorder = null;
    let audioChunks = [];
    let currentRecordingPage = null;
    let startTime = null;
    let timerInterval = null;

    // Inicializar controles de grabación para cada diapositiva
    document.querySelectorAll('.btn-start-recording').forEach(btn => {
        btn.addEventListener('click', function() {
            const page = this.dataset.page;
            startRecording(page);
        });
    });

    document.querySelectorAll('.btn-stop-recording').forEach(btn => {
        btn.addEventListener('click', function() {
            const page = this.dataset.page;
            stopRecording(page);
        });
    });

    document.querySelectorAll('.btn-delete-audio').forEach(btn => {
        btn.addEventListener('click', function() {
            const page = this.dataset.page;
            if (confirm('¿Estás seguro de que deseas eliminar esta grabación?')) {
                deleteAudio(page);
            }
        });
    });

    async function startRecording(page) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            currentRecordingPage = page;

            const card = document.querySelector(`.slide-audio-card[data-page="${page}"]`);
            const btnStart = card.querySelector('.btn-start-recording');
            const btnStop = card.querySelector('.btn-stop-recording');
            const timer = card.querySelector('.recording-timer');

            btnStart.style.display = 'none';
            btnStop.style.display = 'block';
            timer.style.display = 'block';

            startTime = Date.now();
            updateTimer(page);
            timerInterval = setInterval(() => updateTimer(page), 1000);

            mediaRecorder.addEventListener('dataavailable', event => {
                audioChunks.push(event.data);
            });

            mediaRecorder.addEventListener('stop', () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                uploadAudio(page, audioBlob);
                stream.getTracks().forEach(track => track.stop());
            });

            mediaRecorder.start();
        } catch (error) {
            console.error('Error al acceder al micrófono:', error);
            alert('No se pudo acceder al micrófono. Por favor, verifica los permisos.');
        }
    }

    function stopRecording(page) {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            clearInterval(timerInterval);

            const card = document.querySelector(`.slide-audio-card[data-page="${page}"]`);
            const btnStart = card.querySelector('.btn-start-recording');
            const btnStop = card.querySelector('.btn-stop-recording');
            const timer = card.querySelector('.recording-timer');

            btnStop.style.display = 'none';
            timer.style.display = 'none';
            btnStart.style.display = 'block';
        }
    }

    function updateTimer(page) {
        const card = document.querySelector(`.slide-audio-card[data-page="${page}"]`);
        const timerText = card.querySelector('.timer-text');
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        timerText.textContent = `${minutes}:${seconds}`;
    }

    async function uploadAudio(page, audioBlob) {
        const card = document.querySelector(`.slide-audio-card[data-page="${page}"]`);
        const uploadStatus = card.querySelector('.upload-status');
        const recordingControls = card.querySelector('.recording-controls');

        recordingControls.style.display = 'none';
        uploadStatus.style.display = 'block';

        const formData = new FormData();
        formData.append('audio', audioBlob, `slide_${page}.webm`);
        formData.append('presentacion_id', presentacionId);
        formData.append('page', page);

        try {
            const response = await fetch('api/subir_audio.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Actualizar la interfaz con el nuevo audio
                const playbackControls = card.querySelector('.playback-controls');
                const audio = playbackControls.querySelector('audio');
                audio.src = result.audio_url;
                playbackControls.style.display = 'block';

                // Actualizar badge
                const badge = card.querySelector('.badge');
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="fas fa-check"></i> Con audio';

                alert('Audio grabado correctamente');
            } else {
                alert('Error al subir el audio: ' + (result.error || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al subir el audio');
        } finally {
            uploadStatus.style.display = 'none';
            recordingControls.style.display = 'block';
        }
    }

    async function deleteAudio(page) {
        const card = document.querySelector(`.slide-audio-card[data-page="${page}"]`);
        const uploadStatus = card.querySelector('.upload-status');

        uploadStatus.style.display = 'block';

        try {
            const response = await fetch('api/eliminar_audio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    presentacion_id: presentacionId,
                    page: page
                })
            });

            const result = await response.json();

            if (result.success) {
                // Actualizar la interfaz
                const playbackControls = card.querySelector('.playback-controls');
                playbackControls.style.display = 'none';

                // Actualizar badge
                const badge = card.querySelector('.badge');
                badge.className = 'badge bg-secondary';
                badge.innerHTML = '<i class="fas fa-times"></i> Sin audio';

                alert('Audio eliminado correctamente');
            } else {
                alert('Error al eliminar el audio: ' + (result.error || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar el audio');
        } finally {
            uploadStatus.style.display = 'none';
        }
    }
});
</script>
