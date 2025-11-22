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

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-paint-brush me-2"></i>
                        <strong>Anotaciones en diapositivas:</strong> Permite que los estudiantes dibujen y escriban sobre las diapositivas durante la presentación.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permitir_anotaciones" name="permitir_anotaciones" value="1"
                                       <?php echo isset($config['permitir_anotaciones']) && $config['permitir_anotaciones'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="permitir_anotaciones">
                                    <i class="fas fa-pencil-alt me-1"></i> Permitir anotaciones a estudiantes
                                </label>
                            </div>
                            <div class="form-text small">Los estudiantes podrán dibujar y escribir sobre las diapositivas (solo si hay presentación PDF)</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="exportar_con_anotaciones" name="exportar_con_anotaciones" value="1"
                                       <?php echo isset($config['exportar_con_anotaciones']) && $config['exportar_con_anotaciones'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="exportar_con_anotaciones">
                                    <i class="fas fa-file-pdf me-1"></i> Incluir anotaciones en PDF exportado
                                </label>
                            </div>
                            <div class="form-text small">El PDF exportado incluirá las diapositivas con las anotaciones de cada estudiante</div>
                        </div>
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