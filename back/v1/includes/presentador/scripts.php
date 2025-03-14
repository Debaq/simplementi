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
                width: 250,
                height: 250,
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
            fetch('api/get_resultados.php?codigo=' + codigoSesion + '&pregunta=' + preguntaActual)
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
            
            const labels = Object.keys(resultados);
            const data = Object.values(resultados);
            
            // Determinar colores según si la respuesta es correcta
            const respuestaCorrecta = <?php echo isset($pregunta_actual['respuesta_correcta']) ? "'" . addslashes($pregunta_actual['respuesta_correcta']) . "'" : 'null'; ?>;
            
            const backgroundColors = labels.map(label => {
                if (label === respuestaCorrecta) {
                    return 'rgba(75, 192, 192, 0.8)'; // Verde para respuestas correctas
                }
                return 'rgba(54, 162, 235, 0.8)'; // Azul para las demás
            });
            
            const borderColors = labels.map(label => {
                if (label === respuestaCorrecta) {
                    return 'rgba(75, 192, 192, 1)';
                }
                return 'rgba(54, 162, 235, 1)';
            });
            
            if (chart) {
                chart.destroy();
            }
            
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Respuestas',
                        data: data,
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
        if (preguntaActual > 0) {
            actualizarResultados();
            setInterval(actualizarResultados, 3000);
        } else {
            // En la pantalla de intro, solo actualizar el contador de participantes
            setInterval(() => {
                fetch('api/get_conteo_participantes.php?codigo=' + codigoSesion)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const contadorParticipantes = document.getElementById('contador-participantes');
                        if (contadorParticipantes) {
                            contadorParticipantes.textContent = data.total_participantes;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error obteniendo conteo de participantes:', error);
                });
            }, 3000);
        }
    });
</script>