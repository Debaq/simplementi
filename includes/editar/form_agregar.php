<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Agregar nueva pregunta</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card cursor-pointer question-type-card" data-tipo="opcion_multiple">
                    <div class="card-body text-center">
                        <i class="fas fa-list-ul fa-3x mb-3 text-primary"></i>
                        <h5>Opción múltiple</h5>
                        <p class="text-muted small">Pregunta con varias opciones y una respuesta correcta</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cursor-pointer question-type-card" data-tipo="verdadero_falso">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <h5>Verdadero / Falso</h5>
                        <p class="text-muted small">Pregunta con dos opciones: verdadero o falso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cursor-pointer question-type-card" data-tipo="nube_palabras">
                    <div class="card-body text-center">
                        <i class="fas fa-cloud fa-3x mb-3 text-info"></i>
                        <h5>Nube de palabras</h5>
                        <p class="text-muted small">Los participantes responden con una palabra</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cursor-pointer question-type-card" data-tipo="palabra_libre">
                    <div class="card-body text-center">
                        <i class="fas fa-comment-alt fa-3x mb-3 text-warning"></i>
                        <h5>Respuesta libre</h5>
                        <p class="text-muted small">Los participantes responden con texto libre</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensaje de instrucción inicial -->
        <div id="tipo-instruccion">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Seleccione un tipo de pregunta para comenzar.
            </div>
        </div>
        
        <!-- Incluir formularios para cada tipo de pregunta -->
        <?php include('includes/editar/modal_opcion_multiple.php'); ?>
        <?php include('includes/editar/modal_verdadero_falso.php'); ?>
        <?php include('includes/editar/modal_nube_palabras.php'); ?>
        <?php include('includes/editar/modal_palabra_libre.php'); ?>
    </div>
</div>