<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Modo Proyección</title>
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

        .ingreso-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .code-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 1rem;
            border: 3px solid #667eea;
            border-radius: 10px;
        }

        .code-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }

        .logo {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .btn-connect {
            font-size: 1.25rem;
            padding: 1rem 3rem;
            border-radius: 50px;
        }

        .info-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="ingreso-card">
        <div class="logo">
            <i class="fas fa-tv"></i>
        </div>
        <h2 class="fw-bold mb-2">Modo Proyección</h2>
        <p class="text-muted mb-4">Ingresa el código desde tu control móvil</p>

        <form method="GET" action="proyeccion.php">
            <div class="mb-4">
                <input type="text"
                       class="form-control code-input"
                       name="code"
                       placeholder="XXXX-XXXX"
                       maxlength="9"
                       pattern="[A-Z0-9]{4}-[A-Z0-9]{4}"
                       required
                       autofocus
                       autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary btn-connect">
                <i class="fas fa-link me-2"></i>
                Conectar
            </button>
        </form>

        <div class="info-text">
            <i class="fas fa-info-circle me-1"></i>
            Obtén el código desde la app móvil de SimpleMenti
        </div>
    </div>

    <script>
        // Auto-formatear código (agregar guion automáticamente)
        const input = document.querySelector('.code-input');
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4, 8);
            }

            e.target.value = value;
        });
    </script>
</body>
</html>
