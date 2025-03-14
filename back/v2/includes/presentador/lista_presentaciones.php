<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Presentador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .category-badge {
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 text-center">
                <h1 class="display-4 fw-bold text-primary">SimpleMenti</h1>
                <p class="lead">Seleccione una presentación para comenzar</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($presentaciones as $presentacion): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100 card-hover">
                    <div class="card-header <?php echo $presentacion['protegido'] ? 'bg-warning' : 'bg-success'; ?> text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($presentacion['titulo']); ?></h5>
                            <?php if ($presentacion['protegido']): ?>
                            <i class="fas fa-lock" title="Protegido con contraseña"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($presentacion['descripcion']); ?></p>
                        
                        <div class="mb-3">
                            <?php foreach ($presentacion['categorias'] as $categoria): ?>
                            <span class="badge bg-secondary category-badge"><?php echo htmlspecialchars($categoria); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small>
                                <i class="fas fa-question-circle"></i> <?php echo $presentacion['num_preguntas']; ?> preguntas
                            </small>
                            <small>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($presentacion['autor']); ?>
                            </small>
                        </div>
                        
                        <a href="index.php?test=<?php echo urlencode($presentacion['id']); ?>" class="btn btn-primary w-100">
                            <i class="fas fa-play me-2"></i> Iniciar presentación
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Volver a la página principal
                </a>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">SimpleMenti &copy; <?php echo date('Y'); ?> - tmeduca.org</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>