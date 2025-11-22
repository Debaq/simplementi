<?php
/**
 * EJEMPLO DE USO DEL SISTEMA DE TRADUCCIONES
 *
 * Este archivo demuestra cómo integrar el sistema de traducciones
 * en una página PHP de SimpleMenti.
 */

// PASO 1: Iniciar sesión (IMPORTANTE: debe estar antes de Translation)
session_start();

// PASO 2: Incluir el sistema de traducciones
require_once '../includes/Translation.php';

// Opcional: Cambiar idioma programáticamente
// setLang('en'); // Descomenta para cambiar a inglés

?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('app.name'); ?> - Ejemplo de Traducciones</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">

        <!-- Selector de Idioma en la esquina superior derecha -->
        <div class="position-fixed top-0 end-0 m-3" style="z-index: 1000;">
            <?php require_once '../includes/language_selector.php'; ?>
        </div>

        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4">
                <i class="fas fa-language text-primary"></i>
                <?php echo t('app.name'); ?>
            </h1>
            <p class="lead text-muted">
                <?php echo t('app.tagline'); ?>
            </p>
            <p class="text-muted">
                <small>Idioma actual: <strong><?php echo currentLang(); ?></strong></small>
            </p>
        </div>

        <!-- Ejemplo 1: Traducciones Simples -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Ejemplo 1: Traducciones Simples
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre de la app:</strong> <?php echo t('app.name'); ?></p>
                <p><strong>Lema:</strong> <?php echo t('app.tagline'); ?></p>
                <p><strong>Crear:</strong> <?php echo t('common.create'); ?></p>
                <p><strong>Guardar:</strong> <?php echo t('common.save'); ?></p>
                <p><strong>Cancelar:</strong> <?php echo t('common.cancel'); ?></p>
            </div>
        </div>

        <!-- Ejemplo 2: Traducciones con Parámetros -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-code me-2"></i>
                    Ejemplo 2: Traducciones con Parámetros
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Datos de ejemplo
                $numPreguntas = 15;
                $year = date('Y');
                ?>
                <p>
                    <strong>Número de preguntas:</strong>
                    <?php echo t('presentation.num_questions', ['count' => $numPreguntas]); ?>
                </p>
                <p>
                    <strong>Copyright:</strong>
                    <?php echo t('app.copyright', ['year' => $year]); ?>
                </p>
            </div>
        </div>

        <!-- Ejemplo 3: Formulario con Traducciones -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Ejemplo 3: Formulario con Traducciones
                </h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">
                            <?php echo t('admin.username'); ?>
                        </label>
                        <input type="text" class="form-control"
                               placeholder="<?php echo t('admin.username'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <?php echo t('admin.password'); ?>
                        </label>
                        <input type="password" class="form-control"
                               placeholder="<?php echo t('admin.password'); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <?php echo t('common.confirm'); ?>
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        <?php echo t('common.cancel'); ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Ejemplo 4: Tabla con Traducciones -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Ejemplo 4: Tabla con Traducciones
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo t('presentation.title'); ?></th>
                            <th><?php echo t('presentation.author'); ?></th>
                            <th><?php echo t('presentation.num_questions'); ?></th>
                            <th><?php echo t('common.actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Datos de ejemplo
                        $presentaciones = [
                            ['titulo' => 'Demo 1', 'autor' => 'Juan', 'preguntas' => 5],
                            ['titulo' => 'Demo 2', 'autor' => 'María', 'preguntas' => 8],
                            ['titulo' => 'Demo 3', 'autor' => 'Pedro', 'preguntas' => 12]
                        ];

                        foreach ($presentaciones as $pres):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pres['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($pres['autor']); ?></td>
                            <td><?php echo t('presentation.num_questions', ['count' => $pres['preguntas']]); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary">
                                    <?php echo t('common.edit'); ?>
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <?php echo t('common.delete'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ejemplo 5: Alertas y Mensajes -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ejemplo 5: Alertas y Mensajes
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo t('messages.saved_successfully'); ?>
                </div>

                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <?php echo t('messages.error_loading'); ?>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo t('validation.required_field'); ?>
                </div>
            </div>
        </div>

        <!-- Ejemplo 6: Verificar si existe una traducción -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Ejemplo 6: Verificar si existe una traducción
                </h5>
            </div>
            <div class="card-body">
                <?php
                $translation = Translation::getInstance();

                $claves = [
                    'app.name' => $translation->has('app.name'),
                    'clave.inexistente' => $translation->has('clave.inexistente'),
                    'common.save' => $translation->has('common.save')
                ];
                ?>

                <ul class="list-group">
                    <?php foreach ($claves as $clave => $existe): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <code><?php echo $clave; ?></code>
                        <?php if ($existe): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i> Existe
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-times me-1"></i> No existe
                            </span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Ejemplo 7: Idiomas Disponibles -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-globe me-2"></i>
                    Ejemplo 7: Idiomas Disponibles
                </h5>
            </div>
            <div class="card-body">
                <?php
                $availableLanguages = Translation::getInstance()->getAvailableLanguages();
                ?>
                <p><strong>Idiomas disponibles en el sistema:</strong></p>
                <ul class="list-group">
                    <?php foreach ($availableLanguages as $lang): ?>
                    <li class="list-group-item">
                        <?php if ($lang === currentLang()): ?>
                            <i class="fas fa-check text-success me-2"></i>
                            <strong><?php echo strtoupper($lang); ?> (Actual)</strong>
                        <?php else: ?>
                            <i class="far fa-circle me-2"></i>
                            <?php echo strtoupper($lang); ?>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 mb-3">
            <p class="text-muted">
                <?php echo t('app.copyright', ['year' => date('Y')]); ?>
            </p>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script de ejemplo para usar traducciones en JavaScript -->
    <script>
    // Pasar traducciones a JavaScript
    const i18n = {
        confirm: "<?php echo t('common.confirm'); ?>",
        cancel: "<?php echo t('common.cancel'); ?>",
        deleteConfirm: "<?php echo t('messages.confirm_delete'); ?>",
        currentLang: "<?php echo currentLang(); ?>"
    };

    // Ejemplo de uso
    function confirmarEliminacion() {
        if (confirm(i18n.deleteConfirm)) {
            alert('Elemento eliminado');
        }
    }

    // Mostrar idioma actual en consola
    console.log('Idioma actual:', i18n.currentLang);
    console.log('Traducciones cargadas:', i18n);
    </script>
</body>
</html>
