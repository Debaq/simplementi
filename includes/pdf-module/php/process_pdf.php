<?php
/**
 * Procesar PDF y guardar imágenes
 * Convierte las páginas del PDF a imágenes optimizadas y las guarda en el servidor
 */

header('Content-Type: application/json');

// Respuesta por defecto
$response = [
    'success' => false,
    'message' => '',
    'pdf_data' => null
];

try {
    // Validar datos recibidos
    if (!isset($_POST['presentacion_id']) || !isset($_POST['num_pages']) || !isset($_POST['images'])) {
        throw new Exception('Datos incompletos');
    }

    $presentacion_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['presentacion_id']);
    $pdf_name = isset($_POST['pdf_name']) ? $_POST['pdf_name'] : 'documento.pdf';
    $num_pages = intval($_POST['num_pages']);
    $images_json = $_POST['images'];
    $images = json_decode($images_json, true);

    if (!$images || count($images) === 0) {
        throw new Exception('No se recibieron imágenes');
    }

    if (count($images) !== $num_pages) {
        throw new Exception('El número de imágenes no coincide con el número de páginas');
    }

    // Directorio para guardar las imágenes
    $base_dir = '../../../data/uploads/pdfs';
    $presentation_dir = $base_dir . '/' . $presentacion_id;

    // Crear directorio si no existe
    if (!file_exists($base_dir)) {
        mkdir($base_dir, 0755, true);
    }

    if (!file_exists($presentation_dir)) {
        mkdir($presentation_dir, 0755, true);
    } else {
        // Limpiar imágenes anteriores si existen
        $files = glob($presentation_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Guardar cada imagen (full y thumbnail)
    $saved_images = [];
    foreach ($images as $index => $imageObj) {
        $page_num = $index + 1;

        // Verificar que tengamos ambas versiones
        if (!isset($imageObj['full']) || !isset($imageObj['thumb'])) {
            throw new Exception('Datos de imagen incompletos en página ' . $page_num);
        }

        // ====== GUARDAR IMAGEN COMPLETA ======
        if (preg_match('/^data:image\/(webp|jpeg|png);base64,(.+)$/', $imageObj['full'], $matches)) {
            $extension = $matches[1] === 'webp' ? 'webp' : 'jpg';
            $base64_data = $matches[2];
            $binary_data = base64_decode($base64_data);

            if ($binary_data === false) {
                throw new Exception('Error al decodificar imagen completa ' . $page_num);
            }

            $filename = sprintf('page_%03d.%s', $page_num, $extension);
            $filepath = $presentation_dir . '/' . $filename;

            if (file_put_contents($filepath, $binary_data) === false) {
                throw new Exception('Error al guardar imagen completa ' . $page_num);
            }

            $full_path = 'data/uploads/pdfs/' . $presentacion_id . '/' . $filename;
            $full_size = filesize($filepath);
        } else {
            throw new Exception('Formato de imagen completa inválido en página ' . $page_num);
        }

        // ====== GUARDAR MINIATURA ======
        if (preg_match('/^data:image\/(webp|jpeg|png);base64,(.+)$/', $imageObj['thumb'], $matches)) {
            $extension = $matches[1] === 'webp' ? 'webp' : 'jpg';
            $base64_data = $matches[2];
            $binary_data = base64_decode($base64_data);

            if ($binary_data === false) {
                throw new Exception('Error al decodificar miniatura ' . $page_num);
            }

            $thumb_filename = sprintf('page_%03d_thumb.%s', $page_num, $extension);
            $thumb_filepath = $presentation_dir . '/' . $thumb_filename;

            if (file_put_contents($thumb_filepath, $binary_data) === false) {
                throw new Exception('Error al guardar miniatura ' . $page_num);
            }

            $thumb_path = 'data/uploads/pdfs/' . $presentacion_id . '/' . $thumb_filename;
            $thumb_size = filesize($thumb_filepath);
        } else {
            throw new Exception('Formato de miniatura inválido en página ' . $page_num);
        }

        // Agregar ambas versiones a la lista
        $saved_images[] = [
            'page' => $page_num,
            'filename' => $filename,
            'path' => $full_path,
            'size' => $full_size,
            'thumb_filename' => $thumb_filename,
            'thumb_path' => $thumb_path,
            'thumb_size' => $thumb_size
        ];
    }

    // Preparar datos para devolver
    $pdf_data = [
        'enabled' => true,
        'name' => $pdf_name,
        'pages' => $num_pages,
        'images' => $saved_images,
        'directory' => $presentacion_id,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Calcular tamaño total
    $total_size = array_sum(array_column($saved_images, 'size'));
    $pdf_data['total_size'] = $total_size;
    $pdf_data['total_size_mb'] = round($total_size / (1024 * 1024), 2);

    $response['success'] = true;
    $response['message'] = sprintf('PDF procesado: %d páginas, %s MB', $num_pages, $pdf_data['total_size_mb']);
    $response['pdf_data'] = $pdf_data;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
