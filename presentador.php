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

    // Incluir helpers para detectar control móvil
    require_once 'api/helpers_proyeccion.php';

    // Detectar si es una sesión iniciada desde control móvil (sin login en PC)
    $is_mobile_session = !isset($_SESSION['auth_test']);

    // Detectar si hay un control móvil conectado actualmente
    $has_mobile_control = tieneControlMovilConectado($codigo_sesion);

    // Incluir la cabecera HTML
    include('includes/presentador/head.php');

    // Solo mostrar navbar si NO es sesión móvil
    if (!$is_mobile_session) {
        include('includes/presentador/navbar.php');
    }

    // Detectar si es presentación solo de PDF (sin preguntas)
    $es_solo_pdf = $total_preguntas === 0 && !empty($test_data['pdf_enabled']);

    // Si es sesión móvil y está en intro (pregunta_actual = 0), avanzar automáticamente
    if ($is_mobile_session && $pregunta_actual_index === 0 && !$es_solo_pdf) {
        // Solo avanzar si hay preguntas
        // Para presentaciones solo de PDF, dejar pregunta_actual en 0
        $sessionFile = "data/respuestas/{$test_id}/sesion_{$codigo_sesion}.json";
        if (file_exists($sessionFile)) {
            $sessionData = json_decode(file_get_contents($sessionFile), true);
            $sessionData['pregunta_actual'] = 1;

            // Inicializar pdf_sequence_index si hay secuencia de PDF
            if (!empty($test_data['pdf_sequence'])) {
                $sessionData['pdf_sequence_index'] = 0;
            }

            file_put_contents($sessionFile, json_encode($sessionData, JSON_PRETTY_PRINT));

            // Redirigir para recargar con el nuevo estado
            header("Location: presentador.php?codigo=$codigo_sesion");
            exit;
        }
    }

    // Para sesiones móviles, nunca mostrar intro
    if ($is_mobile_session) {
        $show_intro = false;
    }

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
    } elseif ($pregunta_actual_index <= $total_preguntas || $es_solo_pdf) {
        // Mostrar contenido si hay preguntas o si es solo PDF
        // Verificar si hay PDF con secuencia configurada
        $tiene_pdf_con_secuencia = !empty($test_data['pdf_enabled']) &&
                                    isset($test_data['pdf_sequence']) &&
                                    !empty($test_data['pdf_sequence']);

        // Determinar ancho de columnas según si hay control móvil
        $col_presentacion = $has_mobile_control ? 'col-lg-12' : 'col-lg-8';
        $col_panel = $has_mobile_control ? 'col-lg-12 d-none' : 'col-lg-4';

        // Si hay PDF con secuencia, mostrar solo la pantalla de PDF (modo presentación)
        // Si no, mostrar el flujo tradicional de preguntas
        if ($tiene_pdf_con_secuencia) {
            // Modo presentación con PDF - solo mostrar el botón de fullscreen
            ?>
            <div class="row">
                <div class="<?php echo $col_presentacion; ?>">
                    <?php include('includes/presentador/pantalla_pdf.php'); ?>
                </div>
                <div class="<?php echo $col_panel; ?>">
                    <?php include('includes/presentador/panel_participantes.php'); ?>
                </div>
            </div>
            <?php
        } else {
            // Modo tradicional - mostrar preguntas individualmente
            ?>
            <div class="row">
                <div class="<?php echo $col_presentacion; ?>">
                    <?php
                    // Si hay PDF habilitado (pero sin secuencia), mostrar también la pantalla del PDF
                    if (!empty($test_data['pdf_enabled'])) {
                        include('includes/presentador/pantalla_pdf.php');
                    }
                    include('includes/presentador/pantalla_pregunta.php');
                    ?>
                </div>
                <div class="<?php echo $col_panel; ?>">
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
        // Incluir panel de control de interacciones
        include('includes/presentador/panel_control_interacciones.php');
    }

    // Incluir modal de control móvil
    include('includes/presentador/modal_control_movil.php');

    // Incluir puntero virtual overlay
    include('includes/presentador/puntero_virtual.php');

    // Incluir scripts
    include('includes/presentador/scripts.php');
    
    // Cerrar el HTML
    ?>
    </body>
    </html>
    <?php
}
?>