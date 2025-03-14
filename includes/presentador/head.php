<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - Presentador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Librería QRCode para códigos QR -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- SheetJS para exportación a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        .bg-gradient-success {
            background: linear-gradient(to right, #1cc88a, #169b6b);
        }
        .qr-container {
            width: 250px;
            height: 250px;
            margin: 0 auto;
        }
        .pregunta-imagen {
            max-height: 300px;
            object-fit: contain;
        }
        .countdown-timer {
            font-size: 2rem;
            font-weight: bold;
        }
        .slide-indicator {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .slide-indicator-progress {
            height: 100%;
            background-color: #4e73df;
            transition: width 0.3s ease;
        }
        .btn-floating {
            position: fixed;
            right: 20px;
            bottom: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .badge-large {
            font-size: 1.2rem;
            padding: 10px 15px;
        }
        #qr-code-fixed {
            position: fixed;
            right: 20px;
            bottom: 90px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #qr-code-fixed .qr-container {
            width: 120px;
            height: 120px;
        }
        .opciones-leyenda {
    margin-bottom: 20px;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    padding: 15px;
}

.opcion-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.opcion-numero {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background-color: #4e73df;
    color: white;
    font-weight: bold;
    margin-right: 10px;
}

.opciones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    grid-gap: 10px;
}

.chart-leyenda {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.item-correcto .opcion-numero {
    background-color: #1cc88a;
}

.item-correcto .opcion-texto {
    color: #1cc88a;
    font-weight: bold;
}
    </style>
</head>
<body class="bg-light">