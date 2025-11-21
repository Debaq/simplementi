<?php
// Mostrar todos los errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión PHP
session_start();

// Verificar si hay un código de sesión
$codigo_sesion = isset($_GET['codigo']) ? $_GET['codigo'] : '';

// Si no hay código, mostrar lista de presentaciones disponibles
if (empty($codigo_sesion)) {
    // Verificar que el archivo exista
    if (!file_exists('data/index.json')) {
        echo "Error: El archivo index.json no existe. Por favor, vuelva a la página principal.";
        exit;
    }
    
    $index_json = file_get_contents('data/index.json');
    if ($index_json === false) {
        echo "Error: No se pudo leer el archivo index.json.";
        exit;
    }
    
    $index_data = json_decode($index_json, true);
    if ($index_data === null) {
        echo "Error: El archivo index.json no tiene un formato JSON válido.";
        exit;
    }
    
    $presentaciones = $index_data['presentaciones'];
    
    // Incluir la vista de lista de presentaciones
    include('includes/presentador/lista_presentaciones.php');
} else {
    // Incluir la verificación de sesión
    include('includes/presentador/verificacion.php');
    
    // Incluir la cabecera HTML
    include('includes/presentador/head.php');
    
    // Mostrar la navbar
    include('includes/presentador/navbar.php');
    
    // Incluir la barra de progreso
    ?>
    <div class="container py-4">
        <div class="slide-indicator">
            <div class="slide-indicator-progress" style="width: <?php echo $show_intro ? 0 : ($pregunta_actual_index / ($total_preguntas + 1)) * 100; ?>%"></div>
        </div>
    <?php
    
    // Determinar qué pantalla mostrar según el estado
    if ($show_intro) {
        include('includes/presentador/pantalla_inicio.php');
    } elseif ($mostrar_respuesta && isset($pregunta_actual['respuesta_correcta'])) {
        include('includes/presentador/pantalla_respuesta.php');
    } elseif ($pregunta_actual_index <= $total_preguntas) {
        // Verificar si hay PDF con secuencia configurada
        $tiene_pdf_con_secuencia = !empty($test_data['pdf_enabled']) &&
                                    isset($test_data['pdf_sequence']) &&
                                    !empty($test_data['pdf_sequence']);

        // Si hay PDF con secuencia, mostrar solo la pantalla de PDF (modo presentación)
        // Si no, mostrar el flujo tradicional de preguntas
        if ($tiene_pdf_con_secuencia) {
            // Modo presentación con PDF - solo mostrar el botón de fullscreen
            ?>
            <div class="row">
                <div class="col-lg-8">
                    <?php include('includes/presentador/pantalla_pdf.php'); ?>
                </div>
                <div class="col-lg-4">
                    <?php include('includes/presentador/panel_participantes.php'); ?>
                </div>
            </div>
            <?php
        } else {
            // Modo tradicional - mostrar preguntas individualmente
            ?>
            <div class="row">
                <div class="col-lg-8">
                    <?php
                    // Si hay PDF habilitado (pero sin secuencia), mostrar también la pantalla del PDF
                    if (!empty($test_data['pdf_enabled'])) {
                        include('includes/presentador/pantalla_pdf.php');
                    }
                    include('includes/presentador/pantalla_pregunta.php');
                    ?>
                </div>
                <div class="col-lg-4">
                    <?php include('includes/presentador/panel_participantes.php'); ?>
                </div>
            </div>
            <?php
        }
    } else {
        include('includes/presentador/pantalla_finalizacion.php');
    }
    
    // Cerrar el contenedor
    ?>
    </div>
    
    <?php
    // Incluir elementos flotantes
    if (!$show_intro) {
        include('includes/presentador/elementos_flotantes.php');
    }
    
    // Incluir scripts
    include('includes/presentador/scripts.php');
    
    // Cerrar el HTML
    ?>
    </body>
    </html>
    <?php
}
?>