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
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .waiting-container {
            max-width: 500px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="waiting-container">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center mb-0"><?php echo htmlspecialchars($test_data['titulo']); ?></h3>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="loader mx-auto mb-4"></div>
                    <h4 class="mb-3">Esperando a que comience la presentación</h4>
                    <p class="lead mb-1">Has ingresado correctamente a la sesión</p>
                    <p class="text-muted">El presentador iniciará pronto...</p>
                    
                    <div class="alert alert-info mt-4">
                        <strong>Código de sesión:</strong> <?php echo htmlspecialchars($codigo_sesion); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Verificar periódicamente si la presentación ha comenzado
        function verificarInicio() {
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
                console.log('Estado de la presentación:', data);
                if (data.success && data.pregunta_actual > 0) {
                    console.log('¡Presentación iniciada! Recargando...');
                    // La presentación ha comenzado, recargar la página sin caché
                    window.location.href = 'participante.php?codigo=<?php echo $codigo_sesion; ?>&nocache=' + timestamp;
                }
            })
            .catch(error => {
                console.error('Error verificando inicio:', error);
            });
        }
        
        // Verificar cada 2 segundos
        setInterval(verificarInicio, 2000);
    </script>
</body>
</html>