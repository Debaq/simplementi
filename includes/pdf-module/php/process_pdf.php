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

    // Guardar cada imagen
    $saved_images = [];
    foreach ($images as $index => $imageData) {
        // Extraer datos de la imagen (data:image/webp;base64,...)
        if (preg_match('/^data:image\/(webp|jpeg|png);base64,(.+)$/', $imageData, $matches)) {
            $extension = $matches[1] === 'webp' ? 'webp' : 'jpg';
            $base64_data = $matches[2];
            $binary_data = base64_decode($base64_data);

            if ($binary_data === false) {
                throw new Exception('Error al decodificar imagen ' . ($index + 1));
            }

            // Nombre del archivo
            $page_num = $index + 1;
            $filename = sprintf('page_%03d.%s', $page_num, $extension);
            $filepath = $presentation_dir . '/' . $filename;

            // Guardar archivo
            if (file_put_contents($filepath, $binary_data) === false) {
                throw new Exception('Error al guardar imagen ' . $page_num);
            }

            $saved_images[] = [
                'page' => $page_num,
                'filename' => $filename,
                'path' => 'data/uploads/pdfs/' . $presentacion_id . '/' . $filename,
                'size' => filesize($filepath)
            ];
        } else {
            throw new Exception('Formato de imagen inválido en página ' . ($index + 1));
        }
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
