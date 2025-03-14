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
    });
</script>