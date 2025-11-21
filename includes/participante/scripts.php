<script>
    // Variables para el temporizador
    let tiempoInicio = new Date().getTime();
    let tiempoLimite = <?php echo $tiempo_limite; ?>;
    
    // Elementos DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Selección de opciones con tarjetas
        const opcionCards = document.querySelectorAll('.opcion-card');
        const opcionRadios = document.querySelectorAll('.opcion-radio');
        
        opcionCards.forEach((card) => {
            card.addEventListener('click', () => {
                // Limpiar selección previa
                opcionCards.forEach((c) => c.classList.remove('selected'));
                
                // Seleccionar la tarjeta actual
                card.classList.add('selected');
                
                // Marcar el radio button correspondiente
                const radio = card.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Mantener selección si se selecciona directamente el radio
        opcionRadios.forEach((radio) => {
            radio.addEventListener('change', () => {
                opcionCards.forEach((card) => {
                    if (card.contains(radio)) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            });
        });
        
        // Contador de caracteres para preguntas de texto
        const textInput = document.getElementById('respuesta-texto');
        const charCount = document.getElementById('char-count');
        
        if (textInput && charCount) {
            textInput.addEventListener('input', () => {
                charCount.textContent = textInput.value.length;
            });
        }
        
        // Temporizador para preguntas con tiempo límite
        const countdownElement = document.getElementById('countdown');
        if (countdownElement && tiempoLimite > 0) {
            let tiempoRestante = tiempoLimite;
            const intervalo = setInterval(() => {
                tiempoRestante--;
                countdownElement.textContent = tiempoRestante + 's';
                
                if (tiempoRestante <= 10) {
                    countdownElement.classList.add('text-danger');
                }
                
                if (tiempoRestante <= 0) {
                    clearInterval(intervalo);
                    // Enviar automáticamente el formulario si hay una respuesta seleccionada
                    const form = document.getElementById('form-respuesta');
                    const selectedRadio = document.querySelector('input[name="respuesta"]:checked');
                    const textInput = document.getElementById('respuesta-texto');
                    
                    if ((selectedRadio && selectedRadio.value) || 
                        (textInput && textInput.value.trim().length > 0)) {
                        form.submit();
                    }
                }
            }, 1000);
        }
        
        // Calcular tiempo de respuesta
        const formRespuesta = document.getElementById('form-respuesta');
        const tiempoRespuestaInput = document.getElementById('tiempo_respuesta');
        
        if (formRespuesta && tiempoRespuestaInput) {
            formRespuesta.addEventListener('submit', () => {
                const tiempoActual = new Date().getTime();
                const tiempoTranscurrido = Math.floor((tiempoActual - tiempoInicio) / 1000);
                tiempoRespuestaInput.value = tiempoTranscurrido;
            });
        }
        
        // Verificar cambios de pregunta o secuencia periódicamente
        <?php if ($tiene_secuencia_pdf): ?>
        // Modo secuencia: verificar cambios en sequence_index
        const currentSequenceIndex = <?php echo isset($sequence_index) ? $sequence_index : 0; ?>;

        function verificarCambiosSecuencia() {
            const timestamp = new Date().getTime();
            fetch('api/get_sequence_index.php?codigo=<?php echo $codigo_sesion; ?>&t=' + timestamp)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.sequence_index !== undefined) {
                    const newIndex = parseInt(data.sequence_index);
                    console.log('Índice de secuencia en servidor:', newIndex, 'Índice local:', currentSequenceIndex);

                    if (newIndex !== currentSequenceIndex) {
                        console.log('¡Detectado cambio en la secuencia! Recargando...');
                        window.location.href = 'participante.php?codigo=<?php echo $codigo_sesion; ?>&nocache=' + timestamp;
                    }
                } else {
                    console.error('Error en respuesta:', data.message);
                }
            })
            .catch(error => {
                console.error('Error verificando cambios de secuencia:', error);
            });
        }

        // Verificar cada 1.5 segundos
        setInterval(verificarCambiosSecuencia, 1500);

        <?php else: ?>
        // Modo antiguo: verificar cambios en pregunta_actual
        function verificarCambiosPregunta() {
            // Añadir un timestamp para evitar caché
            const timestamp = new Date().getTime();
            fetch('api/get_pregunta_actual.php?codigo=<?php echo $codigo_sesion; ?>&t=' + timestamp)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Pregunta actual en servidor:', data.pregunta_actual, 'Pregunta actual local:', <?php echo $pregunta_actual_index; ?>);

                    // Verificar si la sesión ha sido finalizada
                    if (data.estado === 'finalizada') {
                        console.log('¡Sesión finalizada! Redirigiendo al resumen...');
                        window.location.href = 'participante_resumen.php?codigo=<?php echo $codigo_sesion; ?>&participante=<?php echo $participante_id; ?>&nocache=' + timestamp;
                        return;
                    }

                    if (data.pregunta_actual !== <?php echo $pregunta_actual_index; ?>) {
                        console.log('¡Detectado cambio de pregunta! Recargando...');
                        // Si la pregunta cambió, forzar recarga sin caché
                        window.location.href = 'participante.php?codigo=<?php echo $codigo_sesion; ?>&nocache=' + timestamp;
                    }
                } else {
                    console.error('Error en respuesta:', data.error);
                }
            })
            .catch(error => {
                console.error('Error verificando cambios de pregunta:', error);
            });
        }

        // Verificar cada 2 segundos
        setInterval(verificarCambiosPregunta, 2000);
        <?php endif; ?>
    });
</script>