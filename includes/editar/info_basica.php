<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Editar información básica</h6>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="id" class="form-label">ID de la presentación</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        <input type="text" class="form-control" id="id" name="id" 
                               value="<?php echo htmlspecialchars($id_presentacion); ?>" readonly>
                    </div>
                    <div class="form-text">El ID no se puede cambiar.</div>
                </div>
                <div class="col-md-6">
                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($presentacion_data['titulo']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($presentacion_data['descripcion']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="categorias" class="form-label">Categorías</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                    <input type="text" class="form-control" id="categorias" name="categorias" 
                           value="<?php echo htmlspecialchars($categorias_texto); ?>">
                </div>
                <div class="form-text">Separadas por comas, ej: educación, matemáticas, primaria</div>
            </div>
            
            <!-- Opción de PDF (BETA) -->
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0 text-warning">
                        <i class="fas fa-flask me-2"></i> Funcionalidad Experimental - PDF
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Versión Beta:</strong> Esta funcionalidad está en pruebas. Permite usar un PDF como base de la presentación intercalando preguntas entre las diapositivas.
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="usar_pdf" name="usar_pdf" value="1"
                               <?php echo !empty($presentacion_data['pdf_enabled']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="usar_pdf">
                            <strong>Usar PDF como base de presentación</strong>
                        </label>
                    </div>

                    <div id="pdf-upload-section" style="<?php echo !empty($presentacion_data['pdf_enabled']) ? '' : 'display: none;'; ?>">
                        <?php if (!empty($presentacion_data['pdf_file'])): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-file-pdf me-2"></i> PDF actual:
                                <strong><?php echo htmlspecialchars(basename($presentacion_data['pdf_file'])); ?></strong>
                                (<?php echo isset($presentacion_data['pdf_pages']) ? $presentacion_data['pdf_pages'] : 0; ?> páginas)
                                <button type="button" class="btn btn-sm btn-outline-danger float-end" id="remove-pdf-btn">
                                    <i class="fas fa-trash"></i> Eliminar PDF
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">Subir archivo PDF</label>
                                <input class="form-control" type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
                                <div class="form-text">
                                    El PDF se convertirá automáticamente a imágenes optimizadas para móviles.
                                    Tamaño máximo recomendado: 10MB
                                </div>
                            </div>

                            <div id="pdf-processing-status" style="display: none;">
                                <div class="progress mb-2">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" id="pdf-progress-bar" style="width: 0%"></div>
                                </div>
                                <small id="pdf-status-text" class="text-muted">Procesando PDF...</small>
                            </div>
                        <?php endif; ?>

                        <div class="form-text">
                            <strong>Cómo funciona:</strong>
                            <ul class="small mb-0">
                                <li>Sube un PDF y el sistema creará diapositivas automáticamente</li>
                                <li>Puedes intercalar preguntas entre las páginas del PDF</li>
                                <li>Las páginas se convierten a imágenes optimizadas para móviles</li>
                                <li>Los participantes verán las imágenes del PDF + las preguntas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="protegido" name="protegido" value="1"
                           <?php echo $presentacion_data['protegido'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="protegido">Proteger con contraseña</label>
                </div>
            </div>
            
            <div class="mb-3" id="password-container" style="<?php echo $presentacion_data['protegido'] ? '' : 'display: none;'; ?>">
                <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" 
                           value="<?php echo $presentacion_data['protegido'] ? $presentacion_data['password'] : ''; ?>">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0">Configuración avanzada</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="mostrar_respuestas" class="form-label">Mostrar respuestas correctas</label>
                        <select class="form-select" id="mostrar_respuestas" name="mostrar_respuestas">
                            <option value="nunca" <?php echo $config['mostrar_respuestas'] === 'nunca' ? 'selected' : ''; ?>>Nunca mostrar respuestas</option>
                            <option value="despues_pregunta" <?php echo $config['mostrar_respuestas'] === 'despues_pregunta' ? 'selected' : ''; ?>>Después de cada pregunta</option>
                            <option value="final" <?php echo $config['mostrar_respuestas'] === 'final' ? 'selected' : ''; ?>>Al final de la presentación</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tiempo_por_pregunta" class="form-label">Tiempo por pregunta (segundos)</label>
                        <input type="number" class="form-control" id="tiempo_por_pregunta" name="tiempo_por_pregunta" 
                               value="<?php echo $config['tiempo_por_pregunta']; ?>" min="0" step="1">
                        <div class="form-text">0 = sin límite de tiempo</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_retroceder" name="permitir_retroceder" value="1"
                                       <?php echo $config['permitir_retroceder'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_retroceder">Permitir retroceder</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="mostrar_estadisticas" name="mostrar_estadisticas" value="1"
                                       <?php echo $config['mostrar_estadisticas'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mostrar_estadisticas">Mostrar estadísticas</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_exportar" name="permitir_exportar" value="1"
                                       <?php echo $config['permitir_exportar'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_exportar">Permitir exportar</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-primary mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>
                        <strong>Funcionalidades para Estudiantes:</strong> Configure qué herramientas estarán disponibles durante la presentación.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_anotaciones" name="permitir_anotaciones" value="1"
                                       <?php echo isset($config['permitir_anotaciones']) && $config['permitir_anotaciones'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_anotaciones">
                                    <i class="fas fa-pencil-alt me-1"></i> <strong>Anotaciones</strong> - Dibujar sobre slides
                                </label>
                            </div>
                            <div class="form-text small mb-3">Lápiz, marcador, formas geométricas, texto (solo con PDF)</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="exportar_con_anotaciones" name="exportar_con_anotaciones" value="1"
                                       <?php echo isset($config['exportar_con_anotaciones']) && $config['exportar_con_anotaciones'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="exportar_con_anotaciones">
                                    <i class="fas fa-file-pdf me-1"></i> <strong>Exportar PDF</strong> con anotaciones
                                </label>
                            </div>
                            <div class="form-text small mb-3">Los estudiantes pueden generar PDF en su dispositivo</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_notas" name="permitir_notas" value="1"
                                       <?php echo isset($config['permitir_notas']) && $config['permitir_notas'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_notas">
                                    <i class="fas fa-sticky-note me-1"></i> <strong>Notas textuales</strong> por slide
                                </label>
                            </div>
                            <div class="form-text small mb-3">Panel de notas debajo de cada diapositiva</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_marcadores" name="permitir_marcadores" value="1"
                                       <?php echo isset($config['permitir_marcadores']) && $config['permitir_marcadores'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_marcadores">
                                    <i class="fas fa-bookmark me-1"></i> <strong>Marcadores</strong> de slides importantes
                                </label>
                            </div>
                            <div class="form-text small mb-3">Marcar y categorizar slides clave (importante, revisar, duda)</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_navegacion_libre" name="permitir_navegacion_libre" value="1"
                                       <?php echo isset($config['permitir_navegacion_libre']) && $config['permitir_navegacion_libre'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_navegacion_libre">
                                    <i class="fas fa-route me-1"></i> <strong>Navegación libre</strong> por slides
                                </label>
                            </div>
                            <div class="form-text small mb-3">Avanzar/retroceder sin depender del presentador (sin spoilers)</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_interacciones" name="permitir_interacciones" value="1"
                                       <?php echo isset($config['permitir_interacciones']) && $config['permitir_interacciones'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_interacciones">
                                    <i class="fas fa-comments me-1"></i> <strong>Interacciones</strong> en tiempo real
                                </label>
                            </div>
                            <div class="form-text small mb-3">Levantar mano, preguntas, comprensión, reacciones</div>
                        </div>
                    </div>

                    <div class="alert alert-warning small mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Nota:</strong> Las anotaciones, notas y marcadores se almacenan en el dispositivo del estudiante (no en el servidor). El modo oscuro siempre está disponible como preferencia personal.
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <input type="hidden" name="guardar_info" value="1">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Sección de exportación y compartir -->
<div class="card shadow mt-4">
    <div class="card-header py-3 bg-success text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-share-alt me-2"></i>Exportar y compartir
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6><i class="fas fa-download me-2"></i>Exportar preguntas</h6>
                <p class="text-muted small">Descargue las preguntas en formato GIFT para importarlas en otras plataformas educativas (Moodle, Canvas, etc.)</p>
                <button type="button" class="btn btn-outline-success" id="btn-exportar-gift">
                    <i class="fas fa-file-download me-2"></i>Descargar GIFT
                </button>
            </div>

            <div class="col-md-6 mb-3">
                <h6><i class="fas fa-link me-2"></i>Link de acceso</h6>
                <p class="text-muted small">Genere links y códigos embed para compartir esta presentación</p>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-compartir">
                    <i class="fas fa-share-nodes me-2"></i>Generar links
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para compartir -->
<div class="modal fade" id="modal-compartir" tabindex="-1" aria-labelledby="modal-compartir-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal-compartir-label">
                    <i class="fas fa-share-nodes me-2"></i>Compartir presentación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Para obtener los links de una sesión específica, primero debe iniciar una sesión desde la página principal.
                    Estos enlaces son para compartir la presentación directamente.
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-link me-2"></i>Link directo a la presentación</h6>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" id="link-presentacion" readonly
                               value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/../index.php?test=' . urlencode($id_presentacion); ?>">
                        <button class="btn btn-outline-primary" type="button" id="copy-link-presentacion">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                    <small class="text-muted">Este link llevará a los usuarios a la página de inicio donde pueden unirse a una sesión activa.</small>
                </div>

                <hr>

                <div class="mb-3">
                    <h6><i class="fas fa-qrcode me-2"></i>Código QR</h6>
                    <p class="text-muted small">Genere un código QR para que los participantes escaneen y accedan fácilmente</p>
                    <div class="text-center" id="qr-code-container">
                        <img id="qr-code-img" src="" alt="Código QR" class="img-fluid" style="max-width: 300px; display: none;">
                        <br>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-generar-qr">
                            <i class="fas fa-qrcode me-1"></i>Generar código QR
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botón para exportar preguntas en formato GIFT
    const btnExportarGift = document.getElementById('btn-exportar-gift');
    if (btnExportarGift) {
        btnExportarGift.addEventListener('click', function() {
            const presentacionId = '<?php echo $id_presentacion; ?>';
            window.location.href = `api/exportar_gift.php?id=${presentacionId}`;
        });
    }

    // Copiar link de presentación
    const btnCopyLink = document.getElementById('copy-link-presentacion');
    const linkInput = document.getElementById('link-presentacion');

    if (btnCopyLink && linkInput) {
        btnCopyLink.addEventListener('click', function() {
            linkInput.select();
            document.execCommand('copy');

            const icon = this.querySelector('i');
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Copiado';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');

            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
        });
    }

    // Generar código QR
    const btnGenerarQR = document.getElementById('btn-generar-qr');
    const qrCodeImg = document.getElementById('qr-code-img');

    if (btnGenerarQR && qrCodeImg) {
        btnGenerarQR.addEventListener('click', function() {
            const link = linkInput.value;
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(link)}`;
            qrCodeImg.src = qrUrl;
            qrCodeImg.style.display = 'block';
            btnGenerarQR.style.display = 'none';
        });
    }
});
</script>