<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Editar Presentación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- PDF.js para procesamiento de PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .question-card {
            transition: all 0.3s ease;
        }
        .question-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        .nav-pills .nav-link.active {
            background-color: #4e73df;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .question-type-card {
            transition: all 0.2s ease;
        }
        .question-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">