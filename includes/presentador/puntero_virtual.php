<!-- Puntero Virtual Overlay -->
<div id="pointer-overlay" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 9999;
    display: none;
">
    <canvas id="pointer-canvas" width="1920" height="1080"></canvas>
</div>

<style>
#pointer-canvas {
    width: 100%;
    height: 100%;
}

/* Animación del puntero */
@keyframes pointer-pulse {
    0%, 100% {
        opacity: 0.8;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}
</style>

<script>
(function() {
    const overlay = document.getElementById('pointer-overlay');
    const canvas = document.getElementById('pointer-canvas');
    const ctx = canvas.getContext('2d');

    let currentPointer = null;
    let animationFrame = null;

    // Función para obtener posición del puntero
    async function obtenerPosicionPuntero() {
        try {
            const response = await fetch(serverUrl + 'api/proyeccion/obtener_puntero.php?session=' + codigoSesion);
            const data = await response.json();

            if (data.success && data.has_pointer && data.pointer.enabled) {
                currentPointer = data.pointer;

                // Mostrar overlay si no está visible
                if (overlay.style.display === 'none') {
                    overlay.style.display = 'block';
                    startAnimation();
                }
            } else {
                currentPointer = null;

                // Ocultar overlay
                if (overlay.style.display === 'block') {
                    overlay.style.display = 'none';
                    stopAnimation();
                }
            }
        } catch (error) {
            console.error('Error al obtener puntero:', error);
        }
    }

    // Ajustar tamaño del canvas a la ventana
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Dibujar el puntero
    function drawPointer() {
        if (!currentPointer) return;

        // Limpiar canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Calcular posición en píxeles
        const x = currentPointer.x * canvas.width;
        const y = currentPointer.y * canvas.height;

        // Dibujar círculo exterior (glow)
        const gradient = ctx.createRadialGradient(x, y, 0, x, y, 30);
        gradient.addColorStop(0, 'rgba(255, 59, 48, 0.8)');
        gradient.addColorStop(0.5, 'rgba(255, 59, 48, 0.4)');
        gradient.addColorStop(1, 'rgba(255, 59, 48, 0)');

        ctx.fillStyle = gradient;
        ctx.fillRect(x - 30, y - 30, 60, 60);

        // Dibujar círculo principal (rojo brillante)
        ctx.beginPath();
        ctx.arc(x, y, 12, 0, 2 * Math.PI);
        ctx.fillStyle = 'rgba(255, 59, 48, 0.9)';
        ctx.fill();

        // Borde blanco
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Punto central
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, 2 * Math.PI);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
        ctx.fill();
    }

    // Animación
    function animate() {
        drawPointer();
        animationFrame = requestAnimationFrame(animate);
    }

    function startAnimation() {
        if (!animationFrame) {
            animate();
        }
    }

    function stopAnimation() {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    // Polling cada 100ms para baja latencia
    setInterval(obtenerPosicionPuntero, 100);

    // Obtener inmediatamente
    obtenerPosicionPuntero();
})();
</script>
