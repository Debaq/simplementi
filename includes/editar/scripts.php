<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        // Función para mostrar/ocultar campo de contraseña
        const protegidoCheck = document.getElementById('protegido');
        const passwordContainer = document.getElementById('password-container');
        
        if (protegidoCheck && passwordContainer) {
            protegidoCheck.addEventListener('change', function() {
                passwordContainer.style.display = this.checked ? 'block' : 'none';
                
                if (this.checked) {
                    passwordInput.setAttribute('required', 'required');
                } else {
                    passwordInput.removeAttribute('required');
                }
            });
        }
        
        // Gestión de formularios de preguntas
        const questionTypeBtns = document.querySelectorAll('.question-type-card');
        const questionForms = document.querySelectorAll('.question-form');
        const tipoInstruccion = document.getElementById('tipo-instruccion');
        const cancelBtns = document.querySelectorAll('.cancel-question');
        
        // Mostrar formulario según el tipo seleccionado
        questionTypeBtns.forEach(btn => {
            btn.style.cursor = 'pointer';
            btn.addEventListener('click', function() {
                const tipo = this.getAttribute('data-tipo');
                
                // Ocultar todos los formularios
                questionForms.forEach(form => {
                    form.style.display = 'none';
                });
                
                // Ocultar instrucción
                tipoInstruccion.style.display = 'none';
                
                // Mostrar el formulario correspondiente
                document.getElementById(`form-${tipo}`).style.display = 'block';
            });
        });
        
        // Botones para cancelar
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Ocultar todos los formularios
                questionForms.forEach(form => {
                    form.style.display = 'none';
                });
                
                // Mostrar instrucción
                tipoInstruccion.style.display = 'block';
            });
        });
        
        // Implementar cambio de pestañas con hash URL
        const tabLinks = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-pane');
        
        // Verificar si hay un hash en la URL
        if (window.location.hash) {
            const activeTab = document.querySelector(`a[href="${window.location.hash}"]`);
            if (activeTab) {
                tabLinks.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('show', 'active'));
                
                activeTab.classList.add('active');
                document.querySelector(window.location.hash).classList.add('show', 'active');
            }
        }
        
        // Actualizar hash al cambiar de pestaña
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                window.location.hash = this.getAttribute('href');
            });
        });
        
        // Funcionalidad para agregar opciones en preguntas de opción múltiple
        const btnAgregarOpcion = document.getElementById('agregar-opcion');
        const opcionesContainer = document.getElementById('opciones-container');
        
        if (btnAgregarOpcion && opcionesContainer) {
            btnAgregarOpcion.addEventListener('click', function() {
                const cantidadOpciones = opcionesContainer.querySelectorAll('.input-group').length;
                const nuevaLetra = String.fromCharCode(65 + cantidadOpciones); // A, B, C, ...
                
                // Crear nueva opción
                const nuevoGrupo = document.createElement('div');
                nuevoGrupo.className = 'input-group mb-2';
                nuevoGrupo.innerHTML = `
                    <span class="input-group-text">${nuevaLetra}</span>
                    <input type="text" class="form-control" name="opciones[]" required>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="radio" name="respuesta_correcta_index" value="${cantidadOpciones}">
                    </div>
                    <button type="button" class="btn btn-outline-danger eliminar-opcion">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                opcionesContainer.appendChild(nuevoGrupo);
                
                // Añadir evento para eliminar la opción
                const btnEliminar = nuevoGrupo.querySelector('.eliminar-opcion');
                btnEliminar.addEventListener('click', function() {
                    nuevoGrupo.remove();
                    // Reordenar las letras y valores de índice
                    actualizarIndicesOpciones();
                });
            });
        }
        
        // Función para actualizar las letras e índices cuando se elimina una opción
        function actualizarIndicesOpciones() {
            const grupos = opcionesContainer.querySelectorAll('.input-group');
            grupos.forEach((grupo, index) => {
                // Actualizar letra
                const letra = String.fromCharCode(65 + index);
                grupo.querySelector('.input-group-text').textContent = letra;

                // Actualizar valor del radio button
                grupo.querySelector('input[type="radio"]').value = index;
            });
        }

        // ==============================================
        // FUNCIONALIDAD PDF (BETA)
        // ==============================================

        // Configurar PDF.js
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        }

        // Mostrar/ocultar sección de PDF
        const usarPdfCheck = document.getElementById('usar_pdf');
        const pdfUploadSection = document.getElementById('pdf-upload-section');

        if (usarPdfCheck && pdfUploadSection) {
            usarPdfCheck.addEventListener('change', function() {
                pdfUploadSection.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Procesar PDF cuando se selecciona un archivo
        const pdfFileInput = document.getElementById('pdf_file');
        if (pdfFileInput) {
            pdfFileInput.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file || file.type !== 'application/pdf') {
                    alert('Por favor, seleccione un archivo PDF válido.');
                    return;
                }

                // Validar tamaño (máximo 10MB)
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('El archivo es muy grande. Tamaño máximo: 10MB');
                    pdfFileInput.value = '';
                    return;
                }

                // Mostrar estado de procesamiento
                const processingStatus = document.getElementById('pdf-processing-status');
                const progressBar = document.getElementById('pdf-progress-bar');
                const statusText = document.getElementById('pdf-status-text');

                if (processingStatus) {
                    processingStatus.style.display = 'block';
                    progressBar.style.width = '0%';
                    statusText.textContent = 'Cargando PDF...';
                }

                try {
                    // Leer el archivo
                    const arrayBuffer = await file.arrayBuffer();
                    const typedArray = new Uint8Array(arrayBuffer);

                    // Cargar PDF con PDF.js
                    statusText.textContent = 'Procesando PDF...';
                    const pdf = await pdfjsLib.getDocument(typedArray).promise;
                    const numPages = pdf.numPages;

                    statusText.textContent = `Convirtiendo ${numPages} páginas a imágenes...`;

                    // Convertir cada página a imagen
                    const images = [];
                    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);

                        // Renderizar página a canvas con escala optimizada para móviles
                        const scale = 1.5; // Escala adecuada para buena calidad
                        const viewport = page.getViewport({ scale });

                        // Limitar ancho máximo a 800px para móviles
                        const maxWidth = 800;
                        const adjustedScale = viewport.width > maxWidth ? (maxWidth / viewport.width) * scale : scale;
                        const adjustedViewport = page.getViewport({ scale: adjustedScale });

                        const canvas = document.createElement('canvas');
                        canvas.width = adjustedViewport.width;
                        canvas.height = adjustedViewport.height;
                        const ctx = canvas.getContext('2d');

                        await page.render({
                            canvasContext: ctx,
                            viewport: adjustedViewport
                        }).promise;

                        // Convertir canvas a WebP (mejor compresión)
                        // Si el navegador no soporta WebP, usar JPEG
                        const imageFormat = canvas.toDataURL('image/webp', 0.8).indexOf('data:image/webp') === 0 ? 'image/webp' : 'image/jpeg';
                        const quality = imageFormat === 'image/webp' ? 0.8 : 0.75;
                        const imageDataUrl = canvas.toDataURL(imageFormat, quality);

                        images.push(imageDataUrl);

                        // Actualizar progreso
                        const progress = Math.round((pageNum / numPages) * 100);
                        progressBar.style.width = progress + '%';
                        statusText.textContent = `Procesando página ${pageNum} de ${numPages}...`;
                    }

                    // Preparar datos para enviar al servidor
                    statusText.textContent = 'Guardando imágenes en el servidor...';

                    const formData = new FormData();
                    formData.append('presentacion_id', '<?php echo $id_presentacion; ?>');
                    formData.append('pdf_name', file.name);
                    formData.append('num_pages', numPages);
                    formData.append('images', JSON.stringify(images));

                    // Enviar al servidor
                    const response = await fetch('includes/pdf-module/php/process_pdf.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        statusText.textContent = '¡PDF procesado correctamente!';
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        progressBar.classList.add('bg-success');

                        // Mostrar mensaje de éxito
                        alert(`PDF procesado exitosamente.\n${numPages} páginas convertidas a imágenes.\nGuarda los cambios para aplicar.`);

                        // Agregar campo hidden con los datos del PDF
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'pdf_data';
                        hiddenInput.value = JSON.stringify(result.pdf_data);
                        pdfFileInput.form.appendChild(hiddenInput);

                    } else {
                        throw new Error(result.message || 'Error al procesar el PDF');
                    }

                } catch (error) {
                    console.error('Error al procesar PDF:', error);
                    alert('Error al procesar el PDF: ' + error.message);
                    pdfFileInput.value = '';

                    if (processingStatus) {
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        progressBar.classList.add('bg-danger');
                        statusText.textContent = 'Error al procesar el PDF';
                    }
                }
            });
        }

        // Botón para eliminar PDF
        const removePdfBtn = document.getElementById('remove-pdf-btn');
        if (removePdfBtn) {
            removePdfBtn.addEventListener('click', function() {
                if (confirm('¿Está seguro de que desea eliminar el PDF? Se eliminarán también todas las imágenes generadas.')) {
                    // Agregar campo hidden para indicar eliminación
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_pdf';
                    hiddenInput.value = '1';
                    this.closest('form').appendChild(hiddenInput);

                    // Enviar formulario
                    this.closest('form').submit();
                }
            });
        }
    });
</script>