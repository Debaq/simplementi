<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Crear la estructura si no existe
$data_dir = 'data';
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0755, true);
}

// Crear archivos JSON predeterminados si no existen
$preguntas_file = "$data_dir/preguntas.json";
if (!file_exists($preguntas_file)) {
    $preguntas_data = array(
        'presentaciones' => array(
            array(
                'id' => 'demo_test',
                'titulo' => 'Test de Demostración',
                'descripcion' => 'Un test simple para probar el sistema',
                'preguntas' => array(
                    array(
                        'id' => 1,
                        'tipo' => 'opcion_multiple',
                        'pregunta' => '¿Cuál es tu color favorito?',
                        'opciones' => array('Rojo', 'Verde', 'Azul', 'Amarillo'),
                        'respuesta_correcta' => ''
                    ),
                    array(
                        'id' => 2,
                        'tipo' => 'nube_palabras',
                        'pregunta' => 'Escribe una palabra que describa cómo te sientes hoy'
                    )
                )
            )
        )
    );
    file_put_contents($preguntas_file, json_encode($preguntas_data, JSON_PRETTY_PRINT));
}

$respuestas_file = "$data_dir/respuestas.json";
if (!file_exists($respuestas_file)) {
    $respuestas_data = array(
        'sesiones' => array()
    );
    file_put_contents($respuestas_file, json_encode($respuestas_data, JSON_PRETTY_PRINT));
}

// Página principal que detecta si es presentador o participante
$test_name = isset($_GET['nombredeltest']) ? $_GET['nombredeltest'] : '';
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$modo = isset($_GET['modo']) ? $_GET['modo'] : '';

// Si no hay parámetros, mostrar página de inicio
if (empty($test_name) && empty($codigo_sesion)) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleMenti - tmeduca.org</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">SimpleMenti</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Elija una opción</h5>
                        
                        <div class="d-grid gap-3">
                            <a href="presentador.php" class="btn btn-primary">Crear presentación</a>
                            
                            <div class="text-center my-3">O</div>
                            
                            <form action="" method="get">
                                <div class="input-group mb-3">
                                    <input type="text" name="codigo" class="form-control" placeholder="Ingrese código de sesión">
                                    <button class="btn btn-success" type="submit">Unirse</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
} else if (!empty($codigo_sesion)) {
    // Redireccionar a la página del participante
    include('participante.php');
} else if (!empty($test_name)) {
    // Buscar si existe la presentación
    $preguntas_json = file_get_contents('data/preguntas.json');
    $preguntas_data = json_decode($preguntas_json, true);
    
    $presentacion_encontrada = false;
    foreach ($preguntas_data['presentaciones'] as $presentacion) {
        if ($presentacion['id'] == $test_name) {
            $presentacion_encontrada = true;
            break;
        }
    }
    
    if ($presentacion_encontrada) {
        // Generar código único para la sesión si no existe
        $codigo_nuevo = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 3)), 0, 6);
        
        // Crear nueva sesión en respuestas.json
        $respuestas_json = file_get_contents('data/respuestas.json');
        $respuestas_data = json_decode($respuestas_json, true);
        
        $nueva_sesion = [
            "codigo_sesion" => $codigo_nuevo,
            "id_presentacion" => $test_name,
            "fecha_creacion" => date('Y-m-d\TH:i:s'),
            "estado" => "activa",
            "pregunta_actual" => 1,
            "participantes" => []
        ];
        
        $respuestas_data['sesiones'][] = $nueva_sesion;
        file_put_contents('data/respuestas.json', json_encode($respuestas_data, JSON_PRETTY_PRINT));
        
        // Redireccionar a la página del presentador con el código de sesión
        header("Location: presentador.php?codigo=$codigo_nuevo");
        exit;
    } else {
        echo "Presentación no encontrada.";
    }
}
?>
