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

        <!-- Importar preguntas desde formato GIFT -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-file-upload me-2"></i> Importar preguntas desde formato GIFT</h6>
                    </div>
                    <div class="card-body">
                        <form action="importar_gift.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id_presentacion; ?>">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label for="archivo_gift" class="form-label">Seleccionar archivo GIFT (.txt o .gift)</label>
                                    <input type="file" class="form-control" id="archivo_gift" name="archivo_gift" accept=".txt,.gift" required>
                                    <small class="form-text text-muted">
                                        El formato GIFT permite importar preguntas de opción múltiple, verdadero/falso y respuesta libre.
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalAyudaGift">Ver formato</a>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-2"></i> Importar preguntas
                                    </button>
                                </div>
                            </div>
                        </form>
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

<!-- Modal de ayuda para formato GIFT -->
<div class="modal fade" id="modalAyudaGift" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formato GIFT - Guía rápida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>El formato GIFT permite importar preguntas de forma rápida. Cada pregunta debe estar separada por una línea en blanco.</p>

                <h6 class="mt-3">Verdadero/Falso</h6>
                <pre class="bg-light p-2 rounded"><code>¿La tierra es plana? {FALSE # ¡Correcto! La tierra es redonda.}

¿El agua hierve a 100°C? {TRUE # Exacto, a nivel del mar.}</code></pre>
                <p class="text-muted small">El símbolo # permite agregar retroalimentación (feedback).</p>

                <h6 class="mt-3">Opción múltiple</h6>
                <pre class="bg-light p-2 rounded"><code>¿Cuál es la capital de Francia? {
=París # ¡Correcto! París es la capital.
~Londres # Londres es la capital del Reino Unido.
~Madrid # Madrid es la capital de España.
~Roma # Roma es la capital de Italia.
}</code></pre>
                <p class="text-muted small">El símbolo = marca la respuesta correcta, ~ marca las opciones incorrectas, y # agrega feedback a cada opción.</p>

                <h6 class="mt-3">Respuesta libre</h6>
                <pre class="bg-light p-2 rounded"><code>¿Qué opinas sobre el cambio climático? {# Gracias por compartir tu perspectiva.}

Describe cómo te sientes hoy {}</code></pre>
                <p class="text-muted small">Las preguntas abiertas también pueden tener feedback para dar contexto o reflexión.</p>

                <h6 class="mt-3">Consejos</h6>
                <ul class="small">
                    <li>Cada pregunta debe terminar con llaves { }</li>
                    <li>Separe las preguntas con una línea en blanco</li>
                    <li>Las líneas que comienzan con // son comentarios y se ignoran</li>
                    <li>Use # para agregar retroalimentación (feedback) que se mostrará al responder</li>
                    <li>El feedback de la respuesta correcta se mostrará como explicación</li>
                    <li>El feedback de cada opción se mostrará junto a esa opción</li>
                    <li>Puede usar caracteres especiales escapándolos: \=, \~, \#, \{, \}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>