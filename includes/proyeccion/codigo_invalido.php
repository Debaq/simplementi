<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código Inválido - Proyección</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .error-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #666;
        }

        .error-details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h1>Código Inválido</h1>

        <p>
            El código de emparejamiento ingresado no es válido o ha expirado.
        </p>

        <div class="error-details">
            <strong>Posibles causas:</strong><br>
            • El código ha expirado (los códigos son válidos por 30 segundos)<br>
            • El código fue ingresado incorrectamente<br>
            • La sesión de presentación ha finalizado<br>
            • El código ya fue utilizado
        </div>

        <a href="proyeccion.php" class="btn btn-primary mt-4">
            <i class="fas fa-redo me-2"></i>
            Intentar Nuevamente
        </a>
    </div>
</body>
</html>
