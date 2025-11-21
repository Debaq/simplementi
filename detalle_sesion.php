<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Obtener código de sesión
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo)) {
    header("Location: admin_panel.php?seccion=realizadas");
    exit;
}

// Cargar datos de la sesión
$sesion_data = null;
$presentacion_data = null;
$path_sesion = '';

// Buscar el archivo de la sesión
if (is_dir('data/respuestas')) {
    $presentaciones_dirs = scandir('data/respuestas');
    foreach ($presentaciones_dirs as $presentacion_dir) {
        if ($presentacion_dir === '.' || $presentacion_dir === '..') continue;

        $path_presentacion = 'data/respuestas/' . $presentacion_dir;
        if (is_dir($path_presentacion)) {
            $archivo_sesion = $path_presentacion . '/sesion_' . $codigo . '.json';
            if (file_exists($archivo_sesion)) {
                $path_sesion = $archivo_sesion;
                $sesion_json = file_get_contents($archivo_sesion);
                $sesion_data = json_decode($sesion_json, true);
                break;
            }
        }
    }
}

if (!$sesion_data) {
    header("Location: admin_panel.php?seccion=realizadas");
    exit;
}

// Cargar datos de la presentación
$id_presentacion = $sesion_data['id_presentacion'];
$archivo_presentacion = 'data/presentaciones/' . $id_presentacion . '.json';

if (file_exists($archivo_presentacion)) {
    $presentacion_json = file_get_contents($archivo_presentacion);
    $presentacion_data = json_decode($presentacion_json, true);
}

// Procesar mensaje si existe
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipo_mensaje = isset($_GET['tipo']) ? $_GET['tipo'] : 'info';
}

// Calcular estadísticas adicionales
$total_participantes = isset($sesion_data['participantes']) ? count($sesion_data['participantes']) : 0;
$participantes_con_datos = [];

foreach ($sesion_data['participantes'] as $participante) {
    $id_participante = $participante['id'];
    $nombre = isset($participante['nombre']) ? $participante['nombre'] : 'Anónimo';
    $respuestas = isset($participante['respuestas']) ? $participante['respuestas'] : [];

    $total_respuestas = count($respuestas);
    $respuestas_correctas = 0;
    $tiempo_total = 0;

    foreach ($respuestas as $respuesta) {
        if (isset($respuesta['correcta']) && $respuesta['correcta']) {
            $respuestas_correctas++;
        }
        if (isset($respuesta['tiempo_respuesta'])) {
            $tiempo_total += $respuesta['tiempo_respuesta'];
        }
    }

    $porcentaje = $total_respuestas > 0 ? ($respuestas_correctas / $total_respuestas) * 100 : 0;
    $tiempo_promedio = $total_respuestas > 0 ? $tiempo_total / $total_respuestas : 0;
    $puntos = $respuestas_correctas * 10; // 10 puntos por respuesta correcta

    $participantes_con_datos[] = [
        'id' => $id_participante,
        'nombre' => $nombre,
        'total_respuestas' => $total_respuestas,
        'respuestas_correctas' => $respuestas_correctas,
        'porcentaje' => $porcentaje,
        'tiempo_promedio' => $tiempo_promedio,
        'puntos' => $puntos
    ];
}

// Ordenar por porcentaje (descendente)
usort($participantes_con_datos, function($a, $b) {
    return $b['porcentaje'] - $a['porcentaje'];
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Sesión - SimpleMenti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .stat-card {
            border-left: 4px solid;
        }
        .stat-card.primary {
            border-color: #4e73df;
        }
        .stat-card.success {
            border-color: #1cc88a;
        }
        .stat-card.warning {
            border-color: #f6c23e;
        }
        .stat-card.info {
            border-color: #36b9cc;
        }
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover {
            background-color: #f8f9fa;
        }
        .editing-cell {
            background-color: #fff3cd !important;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_panel.php">
                <i class="fas fa-chalkboard me-2"></i>
                SimpleMenti
            </a>
            <div class="ms-auto">
                <a href="admin_panel.php?seccion=realizadas" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show mb-4">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Información de la sesión -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información de la Sesión
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Código de sesión:</strong> <code><?php echo htmlspecialchars($codigo); ?></code></p>
                                <p><strong>Presentación:</strong> <?php echo htmlspecialchars($presentacion_data ? $presentacion_data['titulo'] : 'Sin título'); ?></p>
                                <p><strong>Fecha inicio:</strong> <?php echo isset($sesion_data['fecha_inicio']) ? date('d/m/Y H:i:s', strtotime($sesion_data['fecha_inicio'])) : '-'; ?></p>
                                <p><strong>Fecha fin:</strong> <?php echo isset($sesion_data['fecha_fin']) ? date('d/m/Y H:i:s', strtotime($sesion_data['fecha_fin'])) : '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>ID Presentación:</strong> <code><?php echo htmlspecialchars($id_presentacion); ?></code></p>
                                <p><strong>Estado:</strong>
                                    <span class="badge bg-<?php echo $sesion_data['estado'] === 'finalizada' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($sesion_data['estado']); ?>
                                    </span>
                                </p>
                                <p><strong>Autor:</strong> <?php echo htmlspecialchars($presentacion_data ? $presentacion_data['autor'] : '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas generales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow stat-card primary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Participantes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $total_participantes; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow stat-card success">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Preguntas Completadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo isset($sesion_data['estadisticas']['preguntas_completadas']) ? $sesion_data['estadisticas']['preguntas_completadas'] : 0; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow stat-card warning">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            % Respuestas Correctas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo isset($sesion_data['estadisticas']['porcentaje_respuestas_correctas']) ? number_format($sesion_data['estadisticas']['porcentaje_respuestas_correctas'], 1) : 0; ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow stat-card info">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tiempo Promedio
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo isset($sesion_data['estadisticas']['tiempo_promedio_respuesta']) ? number_format($sesion_data['estadisticas']['tiempo_promedio_respuesta'], 1) : 0; ?>s
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-download me-2"></i>
                            Descargar Informes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="resumen.php?codigo=<?php echo urlencode($codigo); ?>"
                               class="btn btn-primary" target="_blank">
                                <i class="fas fa-chart-bar me-1"></i> Ver Informe General
                            </a>
                            <button class="btn btn-success" onclick="descargarExcel()">
                                <i class="fas fa-file-excel me-1"></i> Descargar Excel General
                            </button>
                            <button class="btn btn-info" onclick="descargarTodosPDFs()">
                                <i class="fas fa-file-pdf me-1"></i> Descargar Todos los PDFs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de participantes -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users me-2"></i>
                            Resultados de Participantes
                        </h6>
                        <button class="btn btn-sm btn-warning" id="btnEditarResultados">
                            <i class="fas fa-edit me-1"></i> Editar Resultados
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablaParticipantes">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Respuestas</th>
                                        <th>Correctas</th>
                                        <th>Incorrectas</th>
                                        <th>% Acierto</th>
                                        <th>Puntos</th>
                                        <th>Tiempo Promedio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($participantes_con_datos)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No hay participantes.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php $posicion = 1; ?>
                                    <?php foreach ($participantes_con_datos as $participante): ?>
                                    <tr data-participante-id="<?php echo htmlspecialchars($participante['id']); ?>">
                                        <td><?php echo $posicion++; ?></td>
                                        <td><?php echo htmlspecialchars($participante['nombre']); ?></td>
                                        <td><?php echo $participante['total_respuestas']; ?></td>
                                        <td class="editable-cell" data-field="correctas">
                                            <?php echo $participante['respuestas_correctas']; ?>
                                        </td>
                                        <td>
                                            <?php echo $participante['total_respuestas'] - $participante['respuestas_correctas']; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $color_badge = $participante['porcentaje'] >= 70 ? 'success' : ($participante['porcentaje'] >= 50 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $color_badge; ?>">
                                                <?php echo number_format($participante['porcentaje'], 1); ?>%
                                            </span>
                                        </td>
                                        <td class="editable-cell" data-field="puntos">
                                            <?php echo $participante['puntos']; ?>
                                        </td>
                                        <td><?php echo number_format($participante['tiempo_promedio'], 1); ?>s</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="participante_resumen.php?codigo=<?php echo urlencode($codigo); ?>&participante=<?php echo urlencode($participante['id']); ?>"
                                                   class="btn btn-primary" title="Ver detalle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-success"
                                                        onclick="descargarPDF('<?php echo htmlspecialchars($participante['id']); ?>', '<?php echo htmlspecialchars($participante['nombre']); ?>')"
                                                        title="Descargar PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        const codigoSesion = '<?php echo addslashes($codigo); ?>';
        let modoEdicion = false;

        // Modo edición
        document.getElementById('btnEditarResultados').addEventListener('click', function() {
            modoEdicion = !modoEdicion;
            const celdas = document.querySelectorAll('.editable-cell');

            if (modoEdicion) {
                this.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                this.classList.remove('btn-warning');
                this.classList.add('btn-success');
                celdas.forEach(celda => {
                    celda.classList.add('editing-cell');
                    celda.title = 'Clic para editar';
                });
            } else {
                this.innerHTML = '<i class="fas fa-edit me-1"></i> Editar Resultados';
                this.classList.remove('btn-success');
                this.classList.add('btn-warning');
                celdas.forEach(celda => {
                    celda.classList.remove('editing-cell');
                    celda.title = '';
                });
                guardarCambios();
            }
        });

        // Editar celda
        document.querySelectorAll('.editable-cell').forEach(celda => {
            celda.addEventListener('click', function() {
                if (!modoEdicion) return;

                const valorActual = this.textContent.trim();
                const input = document.createElement('input');
                input.type = 'number';
                input.value = valorActual;
                input.className = 'form-control form-control-sm';
                input.style.width = '80px';

                this.textContent = '';
                this.appendChild(input);
                input.focus();
                input.select();

                input.addEventListener('blur', function() {
                    const nuevoValor = this.value || valorActual;
                    celda.textContent = nuevoValor;
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            });
        });

        // Guardar cambios
        function guardarCambios() {
            const cambios = [];
            const filas = document.querySelectorAll('#tablaParticipantes tbody tr[data-participante-id]');

            filas.forEach(fila => {
                const idParticipante = fila.dataset.participanteId;
                const correctas = parseInt(fila.querySelector('[data-field="correctas"]').textContent.trim());
                const puntos = parseInt(fila.querySelector('[data-field="puntos"]').textContent.trim());

                cambios.push({
                    id: idParticipante,
                    correctas: correctas,
                    puntos: puntos
                });
            });

            // Enviar al servidor
            fetch('api/guardar_cambios_resultados.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    codigo: codigoSesion,
                    cambios: cambios
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    alert('Cambios guardados correctamente');
                    location.reload();
                } else {
                    alert('Error al guardar cambios: ' + data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar cambios');
            });
        }

        // Descargar Excel
        function descargarExcel() {
            window.open('api/exportar_resultados.php?codigo=' + codigoSesion, '_blank');
        }

        // Descargar PDF individual
        function descargarPDF(idParticipante, nombreParticipante) {
            window.open('generar_pdf.php?codigo=' + codigoSesion + '&participante=' + idParticipante, '_blank');
        }

        // Descargar todos los PDFs
        function descargarTodosPDFs() {
            const filas = document.querySelectorAll('#tablaParticipantes tbody tr[data-participante-id]');
            if (filas.length === 0) {
                alert('No hay participantes para descargar');
                return;
            }

            if (confirm('¿Desea descargar los PDFs de todos los participantes (' + filas.length + ')? Esto abrirá múltiples pestañas.')) {
                filas.forEach((fila, index) => {
                    setTimeout(() => {
                        const idParticipante = fila.dataset.participanteId;
                        window.open('generar_pdf.php?codigo=' + codigoSesion + '&participante=' + idParticipante, '_blank');
                    }, index * 500); // Retraso de 500ms entre cada descarga
                });
            }
        }
    </script>
</body>
</html>
