<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Datos importantes para el script
    const codigoSesion = '<?php echo $codigo_sesion; ?>';
    const preguntaActual = <?php echo $pregunta_actual_index; ?>;
    const totalPreguntas = <?php echo $total_preguntas; ?>;
    const mostrarRespuestas = '<?php echo isset($test_data['configuracion']['mostrar_respuestas']) ? $test_data['configuracion']['mostrar_respuestas'] : 'despues_pregunta'; ?>';
    const tiempoPregunta = <?php echo isset($test_data['configuracion']['tiempo_por_pregunta']) ? $test_data['configuracion']['tiempo_por_pregunta'] : 0; ?>;
    const tipoPreguntaActual = <?php echo isset($pregunta_actual['tipo']) ? "'" . $pregunta_actual['tipo'] . "'" : 'null'; ?>;
    const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    
    // Elementos DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Generar códigos QR
        if (document.getElementById('qr-code-main')) {
            new QRCode(document.getElementById('qr-code-main'), {
                text: serverUrl + 'participante.php?codigo=' + codigoSesion,
                width: 280,
                height: 280,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        
        if (document.getElementById('qr-code-small')) {
            new QRCode(document.getElementById('qr-code-small'), {
                text: serverUrl + 'participante.php?codigo=' + codigoSesion,
                width: 120,
                height: 120,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        }
        
        // Funcionalidad de copiar URL
        const btnCopiarUrl = document.getElementById('btn-copiar-url');
        if (btnCopiarUrl) {
            btnCopiarUrl.addEventListener('click', () => {
                const joinUrl = document.getElementById('join-url');
                joinUrl.select();
                document.execCommand('copy');
                btnCopiarUrl.innerHTML = '<i class="fas fa-check"></i> Copiado';
                setTimeout(() => {
                    btnCopiarUrl.innerHTML = '<i class="fas fa-copy"></i> Copiar';
                }, 2000);
            });
        }
        
        // Comenzar presentación
        const btnComenzar = document.getElementById('btn-comenzar');
        if (btnComenzar) {
            btnComenzar.addEventListener('click', () => {
                window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=1';
            });
        }
        
        // Navegación de preguntas
        const btnAnterior = document.getElementById('btn-anterior');
        if (btnAnterior) {
            btnAnterior.addEventListener('click', () => {
                if (preguntaActual > 1) {
                    window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual - 1);
                }
            });
        }
        
        const btnSiguiente = document.getElementById('btn-siguiente');
        if (btnSiguiente) {
            btnSiguiente.addEventListener('click', () => {
                if (preguntaActual < totalPreguntas) {
                    if (mostrarRespuestas === 'despues_pregunta' && document.getElementById('btn-mostrar-respuesta')) {
                        // Si debe mostrar respuesta primero, redirigir a la misma pregunta con parámetro
                        window.location.href = 'presentador.php?codigo=' + codigoSesion + '&mostrar_respuesta=1';
                    } else {
                        // Pasar a la siguiente pregunta
                        window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual + 1);
                    }
                } else {
                    // Si es la última pregunta, finalizar
                    window.location.href = 'resumen.php?codigo=' + codigoSesion;
                }
            });
        }
        
        // Botón de mostrar respuesta
        const btnMostrarRespuesta = document.getElementById('btn-mostrar-respuesta');
        if (btnMostrarRespuesta) {
            btnMostrarRespuesta.addEventListener('click', () => {
                window.location.href = 'presentador.php?codigo=' + codigoSesion + '&mostrar_respuesta=1';
            });
        }
        
        // Botón de siguiente después de mostrar respuesta
        const btnSiguienteDespuesRespuesta = document.getElementById('btn-siguiente-despues-respuesta');
        if (btnSiguienteDespuesRespuesta) {
            btnSiguienteDespuesRespuesta.addEventListener('click', () => {
                if (preguntaActual < totalPreguntas) {
                    window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual + 1);
                } else {
                    window.location.href = 'resumen.php?codigo=' + codigoSesion;
                }
            });
        }
        
        // Funcionalidad para exportar resultados
        const btnExportar = document.getElementById('btn-exportar');
        if (btnExportar) {
            btnExportar.addEventListener('click', () => {
                exportarResultados();
            });
        }
        
        // Pantalla completa
        const btnFullscreen = document.getElementById('btn-fullscreen');
        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });
        }
        
        // Finalizar sesión
        const btnFinalizar = document.getElementById('btn-finalizar');
        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', () => {
                if (confirm('¿Estás seguro de que deseas finalizar esta sesión? Se guardará un registro de los resultados.')) {
                    window.location.href = 'api/finalizar_sesion.php?codigo=' + codigoSesion;
                }
            });
        }
        

// Agregar a scripts.php después de las otras definiciones de eventos de botones

// Botón para mostrar resultados de nubes de palabras
const btnMostrarResultados = document.getElementById('btn-mostrar-resultados');
if (btnMostrarResultados) {
    btnMostrarResultados.addEventListener('click', () => {
        window.location.href = 'presentador.php?codigo=' + codigoSesion + '&mostrar_respuesta=1';
    });
}

// Actualizar barra de progreso para recolección de respuestas en nubes de palabras
function actualizarProgresoNube() {
    const progressElement = document.getElementById('progress-recoleccion');
    if (progressElement && tipoPreguntaActual === 'nube_palabras') {
        fetch('api/get_resultados.php?codigo=' + codigoSesion + '&pregunta=' + preguntaActual)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const totalParticipantes = data.total_participantes || 0;
                const totalRespuestas = data.estadisticas.total_respuestas || 0;
                
                // Actualizar también el contador de respuestas general
                const totalRespuestasEl = document.getElementById('total-respuestas');
                if (totalRespuestasEl) {
                    totalRespuestasEl.textContent = totalRespuestas;
                }
                
                if (totalParticipantes > 0) {
                    const porcentaje = Math.min(100, Math.round((totalRespuestas / totalParticipantes) * 100));
                    progressElement.style.width = porcentaje + '%';
                    progressElement.setAttribute('aria-valuenow', porcentaje);
                    progressElement.textContent = totalRespuestas + ' respuestas';
                    
                    // Cambiar color según la cantidad de respuestas
                    if (porcentaje < 30) {
                        progressElement.classList.remove('bg-success', 'bg-warning');
                        progressElement.classList.add('bg-primary');
                    } else if (porcentaje < 70) {
                        progressElement.classList.remove('bg-success', 'bg-primary');
                        progressElement.classList.add('bg-warning');
                    } else {
                        progressElement.classList.remove('bg-primary', 'bg-warning');
                        progressElement.classList.add('bg-success');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error obteniendo progreso de nube:', error);
        });
    }
}

        // Temporizador para preguntas con tiempo límite
        const countdownElement = document.getElementById('countdown');
        if (countdownElement && tiempoPregunta > 0) {
            let tiempoRestante = tiempoPregunta;
            const intervalo = setInterval(() => {
                tiempoRestante--;
                countdownElement.textContent = tiempoRestante + 's';
                
                if (tiempoRestante <= 10) {
                    countdownElement.classList.add('text-danger');
                }
                
                if (tiempoRestante <= 0) {
                    clearInterval(intervalo);
                    // Opcional: avanzar automáticamente a la siguiente pregunta
                    // window.location.href = 'api/cambiar_pregunta.php?codigo=' + codigoSesion + '&pregunta=' + (preguntaActual + 1);
                }
            }, 1000);
        }
        
        // Actualizar resultados en tiempo real
        let chart;
        
        function actualizarResultados() {
            fetch(serverUrl + 'api/get_resultados.php?codigo=' + codigoSesion + '&pregunta=' + preguntaActual)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador de participantes
                    const elementos = [
                        document.getElementById('contador-participantes'),
                        document.getElementById('contador-participantes-card'),
                        document.getElementById('contador-participantes-final')
                    ];
                    
                    elementos.forEach(elemento => {
                        if (elemento) elemento.textContent = data.total_participantes;
                    });
                    
                    // Actualizar estadísticas
                    const totalRespuestas = document.getElementById('total-respuestas');
                    if (totalRespuestas) totalRespuestas.textContent = data.estadisticas.total_respuestas || 0;
                    
                    const porcentajeCorrectas = document.getElementById('porcentaje-correctas');
                    if (porcentajeCorrectas) porcentajeCorrectas.textContent = (data.estadisticas.porcentaje_correctas || 0) + '%';
                    
                    const tiempoPromedio = document.getElementById('tiempo-promedio');
                    if (tiempoPromedio) tiempoPromedio.textContent = (data.estadisticas.tiempo_promedio || 0) + 's';
                    
                    const totalPendientes = document.getElementById('total-pendientes');
                    if (totalPendientes) totalPendientes.textContent = data.estadisticas.total_pendientes || 0;
                    
                    // Actualizar lista de participantes
                    const listaParticipantes = document.getElementById('lista-participantes');
                    if (listaParticipantes && data.participantes.length > 0) {
                        listaParticipantes.innerHTML = '';
                        data.participantes.forEach(p => {
                            const respondido = p.respuestas.some(r => r.id_pregunta == preguntaActual);
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center';
                            li.innerHTML = `Participante <strong>${p.id}</strong>`;
                            
                            if (respondido) {
                                const span = document.createElement('span');
                                span.className = 'badge bg-success';
                                span.innerHTML = '<i class="fas fa-check"></i>';
                                li.appendChild(span);
                            } else {
                                const span = document.createElement('span');
                                span.className = 'badge bg-secondary';
                                span.innerHTML = '<i class="fas fa-clock"></i>';
                                li.appendChild(span);
                            }
                            
                            listaParticipantes.appendChild(li);
                        });
                    } else if (listaParticipantes) {
                        listaParticipantes.innerHTML = '<li class="list-group-item text-center text-muted">Esperando participantes...</li>';
                    }
                    
                    // Actualizar gráfico según el tipo de pregunta
                    if (tipoPreguntaActual === 'opcion_multiple' || tipoPreguntaActual === 'verdadero_falso') {
                        actualizarGraficoOpcionMultiple(data.resultados);
                    } else if (tipoPreguntaActual === 'nube_palabras') {
                        actualizarNubePalabras(data.resultados);
                    }
                }
            })
            .catch(error => {
                console.error('Error obteniendo resultados:', error);
            });
        }
        
        function actualizarGraficoOpcionMultiple(resultados) {
            const ctx = document.getElementById('resultados-chart');
            if (!ctx) return;
            
            // Preparar datos según el tipo de pregunta
            let labels = [];
            let valores = [];
            let backgroundColors = [];
            let borderColors = [];
            
            if (tipoPreguntaActual === 'opcion_multiple') {
                // Verificar si existen opciones en la pregunta actual
                const opciones = <?php echo isset($pregunta_actual['opciones']) && is_array($pregunta_actual['opciones']) ? json_encode($pregunta_actual['opciones']) : 'null'; ?>;
                
                if (opciones && opciones.length > 0) {
                    // Para preguntas con opciones predefinidas, usamos el índice + 1 como etiqueta
                    opciones.forEach((opcion, index) => {
                        const numeroOpcion = (index + 1).toString();
                        const valor = resultados[opcion] || 0;
                        labels.push(numeroOpcion);
                        valores.push(valor);
                    });
                    
                    // Determinar colores según si la respuesta es correcta
                    const respuestaCorrecta = <?php echo isset($pregunta_actual['respuesta_correcta']) ? "'" . addslashes($pregunta_actual['respuesta_correcta']) . "'" : 'null'; ?>;
                    
                    if (respuestaCorrecta) {
                        backgroundColors = opciones.map(opcion => {
                            if (opcion === respuestaCorrecta) {
                                return 'rgba(75, 192, 192, 0.8)'; // Verde para respuestas correctas
                            }
                            return 'rgba(54, 162, 235, 0.8)'; // Azul para las demás
                        });
                        
                        borderColors = opciones.map(opcion => {
                            if (opcion === respuestaCorrecta) {
                                return 'rgba(75, 192, 192, 1)';
                            }
                            return 'rgba(54, 162, 235, 1)';
                        });
                    } else {
                        // Si no hay respuesta correcta, todos son azules
                        backgroundColors = Array(opciones.length).fill('rgba(54, 162, 235, 0.8)');
                        borderColors = Array(opciones.length).fill('rgba(54, 162, 235, 1)');
                    }
                }
            } else if (tipoPreguntaActual === 'verdadero_falso') {
                // Para preguntas de verdadero/falso
                const valorVerdadero = resultados['true'] || 0;
                const valorFalso = resultados['false'] || 0;
                
                labels = ['1', '2'];
                valores = [valorVerdadero, valorFalso];
                
                // Determinar la respuesta correcta
                const respuestaCorrecta = <?php echo isset($pregunta_actual['respuesta_correcta']) ? 
                    ($pregunta_actual['respuesta_correcta'] === true || $pregunta_actual['respuesta_correcta'] === 'true' ? "'true'" : "'false'") : 'null'; ?>;
                
                // Colores según la respuesta correcta
                backgroundColors = [
                    (respuestaCorrecta === 'true') ? 'rgba(75, 192, 192, 0.8)' : 'rgba(54, 162, 235, 0.8)',
                    (respuestaCorrecta === 'false') ? 'rgba(75, 192, 192, 0.8)' : 'rgba(54, 162, 235, 0.8)'
                ];
                
                borderColors = [
                    (respuestaCorrecta === 'true') ? 'rgba(75, 192, 192, 1)' : 'rgba(54, 162, 235, 1)',
                    (respuestaCorrecta === 'false') ? 'rgba(75, 192, 192, 1)' : 'rgba(54, 162, 235, 1)'
                ];
            } else {
                // Para otras preguntas, usar datos originales
                labels = Object.keys(resultados).map((key, index) => (index + 1).toString());
                valores = Object.values(resultados);
                backgroundColors = Array(valores.length).fill('rgba(54, 162, 235, 0.8)');
                borderColors = Array(valores.length).fill('rgba(54, 162, 235, 1)');
            }
            
            if (chart) {
                chart.destroy();
            }
            
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Respuestas',
                        data: valores,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        function actualizarNubePalabras(resultados) {
            const container = document.getElementById('nube-palabras');
            if (!container) return;
            
            container.innerHTML = '';
            
            // Si no hay resultados, mostrar mensaje
            if (Object.keys(resultados).length === 0) {
                container.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> Esperando respuestas de los participantes...</div>';
                return;
            }
            
            // Ordenar por frecuencia
            const palabrasOrdenadas = Object.entries(resultados)
                .sort((a, b) => b[1] - a[1]);
            
            // Encontrar el valor máximo para escalar tamaños
            const maxValue = Math.max(...Object.values(resultados));
            
            palabrasOrdenadas.forEach(([palabra, cantidad]) => {
                const tamanio = Math.max(16, Math.min(60, (cantidad / maxValue) * 60 + 16));
                const opacity = 0.5 + (cantidad / maxValue) * 0.5;
                
                const span = document.createElement('span');
                span.textContent = palabra;
                span.style.fontSize = `${tamanio}px`;
                span.style.opacity = opacity;
                span.style.margin = '10px';
                span.style.display = 'inline-block';
                span.style.color = getRandomColor();
                
                container.appendChild(span);
            });
        }
        
        function getRandomColor() {
            const colors = [
                '#4e73df', // Azul
                '#1cc88a', // Verde
                '#f6c23e', // Amarillo
                '#e74a3b', // Rojo
                '#36b9cc', // Cyan
                '#6f42c1'  // Púrpura
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        
        function exportarResultados() {
            fetch('api/exportar_resultados.php?codigo=' + codigoSesion)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Crear libro de Excel
                    const wb = XLSX.utils.book_new();
                    
                    // Crear hoja de resumen
                    const wsResumen = XLSX.utils.json_to_sheet(data.resumen);
                    XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");
                    
                    // Crear hoja de participantes
                    const wsParticipantes = XLSX.utils.json_to_sheet(data.participantes);
                    XLSX.utils.book_append_sheet(wb, wsParticipantes, "Participantes");
                    
                    // Crear hoja para cada pregunta
                    data.preguntas.forEach((pregunta, index) => {
                        const wsRespuestas = XLSX.utils.json_to_sheet(pregunta.respuestas);
                        XLSX.utils.book_append_sheet(wb, wsRespuestas, `Pregunta ${index + 1}`);
                    });
                    
                    // Generar el archivo y descargarlo
                    XLSX.writeFile(wb, `SimpleMenti_${codigoSesion}_${new Date().toISOString().slice(0,10)}.xlsx`);
                } else {
                    alert('Error al exportar resultados: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error exportando resultados:', error);
                alert('Error al exportar resultados. Intente nuevamente.');
            });
        }
        
        // Iniciar actualización periódica si no es la pantalla de intro
 // Modificar esta parte en scripts.php en la sección de "Iniciar actualización periódica"

// Iniciar actualización periódica si no es la pantalla de intro
if (preguntaActual > 0) {
    actualizarResultados();
    
    // Si es una nube de palabras que no se muestra en tiempo real, actualizar solo la barra de progreso
    if (tipoPreguntaActual === 'nube_palabras' && document.getElementById('progress-recoleccion')) {
        actualizarProgresoNube();
        setInterval(actualizarProgresoNube, 2000);
    } else {
        // Para otros tipos, actualizar todo
        setInterval(actualizarResultados, 3000);
    }
} else {
    // En la pantalla de intro, actualizar el contador de participantes
    function actualizarContadorParticipantes() {
        fetch('api/get_conteo_participantes.php?codigo=' + codigoSesion + '&t=' + new Date().getTime())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contadorParticipantes = document.getElementById('contador-participantes');
                if (contadorParticipantes) {
                    contadorParticipantes.textContent = data.total_participantes;
                }
            } else {
                console.error('Error obteniendo conteo de participantes:', data.error);
            }
        })
        .catch(error => {
            console.error('Error en fetch de conteo de participantes:', error);
        });
    }
    
    // Actualizar inmediatamente y luego cada 2 segundos
    actualizarContadorParticipantes();
    setInterval(actualizarContadorParticipantes, 2000);
}

    // ============================================================
    // CONTROL MÓVIL
    // ============================================================

    const btnConectarMovil = document.getElementById('btn-conectar-movil');
    const modalControlMovil = new bootstrap.Modal(document.getElementById('modalControlMovil'));
    const btnGenerarQR = document.getElementById('btn-generar-qr');
    const btnRegenerarQR = document.getElementById('btn-regenerar-qr');

    let countdownInterval = null;
    let currentPairCode = null;

    // Abrir modal al hacer clic en el botón
    if (btnConectarMovil) {
        btnConectarMovil.addEventListener('click', (e) => {
            e.preventDefault();
            mostrarAdvertencia();
            modalControlMovil.show();
        });
    }

    // Generar QR cuando se acepta la advertencia
    if (btnGenerarQR) {
        btnGenerarQR.addEventListener('click', () => {
            generarCodigoEmparejamiento();
        });
    }

    // Regenerar QR
    if (btnRegenerarQR) {
        btnRegenerarQR.addEventListener('click', () => {
            generarCodigoEmparejamiento();
        });
    }

    function mostrarAdvertencia() {
        document.getElementById('modal-advertencia').style.display = 'block';
        document.getElementById('modal-qr').style.display = 'none';
        limpiarCountdown();
    }

    function mostrarQR() {
        document.getElementById('modal-advertencia').style.display = 'none';
        document.getElementById('modal-qr').style.display = 'block';
    }

    function generarCodigoEmparejamiento() {
        // Mostrar loading
        btnGenerarQR.disabled = true;
        btnGenerarQR.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generando...';

        fetch(serverUrl + 'api/generar_codigo_emparejamiento.php?session=' + codigoSesion)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentPairCode = data.pair_code;

                    // Mostrar QR
                    document.getElementById('qr-image').src = data.qr_image;
                    document.getElementById('pair-code').textContent = data.pair_code;

                    // Cambiar a pantalla de QR
                    mostrarQR();

                    // Iniciar countdown
                    iniciarCountdown(data.expires_in);

                    // Restaurar botón
                    btnGenerarQR.disabled = false;
                    btnGenerarQR.innerHTML = '<i class="fas fa-check me-2"></i> Entiendo, Generar QR';
                } else {
                    alert('Error al generar código: ' + data.message);
                    btnGenerarQR.disabled = false;
                    btnGenerarQR.innerHTML = '<i class="fas fa-check me-2"></i> Entiendo, Generar QR';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al generar código');
                btnGenerarQR.disabled = false;
                btnGenerarQR.innerHTML = '<i class="fas fa-check me-2"></i> Entiendo, Generar QR';
            });
    }

    function iniciarCountdown(seconds) {
        limpiarCountdown();

        let remaining = seconds;
        const progressBar = document.getElementById('countdown-progress');
        const countdownText = document.getElementById('countdown-text');

        function updateCountdown() {
            if (remaining <= 0) {
                limpiarCountdown();
                // Mostrar que expiró
                progressBar.classList.remove('bg-warning');
                progressBar.classList.add('bg-danger');
                countdownText.textContent = 'Código expirado';

                setTimeout(() => {
                    mostrarAdvertencia();
                }, 2000);
                return;
            }

            const percentage = (remaining / seconds) * 100;
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
            countdownText.textContent = `Expira en: ${remaining}s`;

            // Cambiar color si quedan menos de 10 segundos
            if (remaining <= 10) {
                progressBar.classList.remove('bg-warning');
                progressBar.classList.add('bg-danger');
            }

            remaining--;
        }

        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);
    }

    function limpiarCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        // Resetear barra de progreso
        const progressBar = document.getElementById('countdown-progress');
        const countdownText = document.getElementById('countdown-text');

        if (progressBar) {
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-warning');
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', 100);
        }

        if (countdownText) {
            countdownText.textContent = 'Expira en: 30s';
        }
    }

    // Limpiar countdown al cerrar modal
    document.getElementById('modalControlMovil').addEventListener('hidden.bs.modal', () => {
        limpiarCountdown();
        mostrarAdvertencia();
    });

    // Permitir copiar código al hacer clic
    document.getElementById('pair-code').addEventListener('click', function() {
        const text = this.textContent;
        navigator.clipboard.writeText(text).then(() => {
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Copiado';
            setTimeout(() => {
                this.textContent = text;
            }, 1500);
        });
    });

    // ============================================================
    // SINCRONIZACIÓN CON CONTROL MÓVIL
    // ============================================================

    <?php if ($has_mobile_control): ?>
    // Hay control móvil conectado - sincronizar cambios
    let lastPreguntaActual = preguntaActual;

    function sincronizarConMovil() {
        fetch(serverUrl + 'api/get_pregunta_actual.php?codigo=' + codigoSesion + '&t=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                if (data.success && data.pregunta_actual !== lastPreguntaActual) {
                    // La pregunta cambió desde el móvil, recargar página
                    console.log('Pregunta cambiada desde móvil:', data.pregunta_actual);
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error sincronizando con móvil:', error);
            });
    }

    // Sincronizar cada 2 segundos
    setInterval(sincronizarConMovil, 2000);
    console.log('Sincronización con control móvil activada');
    <?php endif; ?>

    });
</script>