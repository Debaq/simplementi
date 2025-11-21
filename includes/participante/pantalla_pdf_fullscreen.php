<?php
/**
 * Pantalla fullscreen para mostrar slides del PDF al participante
 * Se sincroniza automáticamente con el presentador
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

    #slide-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.98);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
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

    /* Indicador de slide en esquina inferior derecha */
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
        animation: fadeIn 0.3s ease-out;
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
    <img id="slide-image"
         src="<?php echo htmlspecialchars($slide_data['path']); ?>"
         alt="Slide <?php echo $slide_number; ?>">
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

<script>
const codigo = '<?php echo $codigo_sesion; ?>';
let currentSequenceIndex = <?php echo $sequence_index; ?>;
const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

// Función para verificar cambios en la secuencia
function checkSequenceUpdate() {
    const loadingIndicator = document.getElementById('loading-indicator');
    loadingIndicator.classList.add('show');

    fetch(serverUrl + 'api/get_sequence_index.php?codigo=' + codigo)
        .then(response => response.json())
        .then(data => {
            loadingIndicator.classList.remove('show');

            if (data.success && data.sequence_index !== undefined) {
                const newIndex = parseInt(data.sequence_index);

                // Si cambió el índice, recargar la página
                if (newIndex !== currentSequenceIndex) {
                    // Mostrar mensaje de sincronización
                    const syncMessage = document.getElementById('sync-message');
                    syncMessage.classList.add('show');

                    // Esperar un momento y recargar
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

// Precargar todas las imágenes de los slides
function preloadAllSlides() {
    const slides = <?php echo json_encode($test_data['pdf_images']); ?>;

    slides.forEach((slide, index) => {
        const img = new Image();
        img.src = slide.path;
    });
}

// Verificar cambios cada 1.5 segundos
setInterval(checkSequenceUpdate, 1500);

// Precargar slides al cargar la página
window.addEventListener('load', function() {
    preloadAllSlides();
    console.log('Slides precargados para navegación fluida');
});

// Agregar soporte para fullscreen al hacer clic
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
</script>
