<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Participante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Librerías para generación de PDF en el cliente -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .question-container {
            max-width: 600px;
            margin: 20px auto;
        }
        .pregunta-imagen {
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
        }
        .opcion-card {
            transition: all 0.2s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .opcion-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .opcion-card.selected {
            border-color: #4e73df;
            background-color: #f8f9ff;
        }
        .countdown-timer {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        @media (max-width: 576px) {
            .question-container {
                margin: 10px auto;
            }
        }
        .text-input-container {
            position: relative;
            margin-bottom: 20px;
        }
        .character-counter {
            position: absolute;
            right: 10px;
            bottom: -25px;
            font-size: 0.8rem;
            color: #6c757d;
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
        .completion-container {
            max-width: 500px;
            margin: 100px auto;
        }
    </style>
</head>
<body>