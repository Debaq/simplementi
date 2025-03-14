<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Participante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .completion-container {
            max-width: 500px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="completion-container">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="text-center mb-0">¡Gracias por participar!</h3>
                </div>
                <div class="card-body p-5 text-center">
                    <i class="fas fa-check-circle text-success mb-4" style="font-size: 5rem;"></i>
                    <h4 class="mb-3">Has completado todas las preguntas</h4>
                    <p class="lead">Tu participación ha sido registrada</p>
                    
                    <?php if ($session_data['estado'] === 'finalizada'): ?>
                    <div class="mt-4">
                        <a href="participante_resumen.php?codigo=<?php echo $codigo_sesion; ?>&participante=<?php echo $participante_id; ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-chart-bar me-2"></i> Ver mi resumen
                        </a>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mt-3">Cuando el presentador finalice la sesión, podrás ver tu resumen de resultados.</p>
                    <div class="d-flex justify-content-center mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Verificar periódicamente si la sesión ha finalizado
        function verificarFinalizacion() {
            // Añadir un timestamp para evitar caché
            const timestamp = new Date().getTime();
            fetch('api/get_pregunta_actual.php?codigo=<?php echo $codigo_sesion; ?>&t=' + timestamp)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.estado === 'finalizada') {
                    console.log('¡Sesión finalizada! Redirigiendo al resumen...');
                    window.location.href = 'participante_resumen.php?codigo=<?php echo $codigo_sesion; ?>&participante=<?php echo $participante_id; ?>';
                }
            })
            .catch(error => {
                console.error('Error verificando finalización:', error);
            });
        }
        
        <?php if ($session_data['estado'] !== 'finalizada'): ?>
        // Solo ejecutar si la sesión no está finalizada
        // Verificar cada 3 segundos
        setInterval(verificarFinalizacion, 3000);
        <?php endif; ?>
    </script>
</body>
</html>