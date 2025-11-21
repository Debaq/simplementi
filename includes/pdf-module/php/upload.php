<?php
// Este archivo se encargará de la subida y almacenamiento de PDFs
// Implementación básica para el futuro

// Directorio para almacenar PDFs (ruta absoluta desde la raíz del proyecto)
$upload_dir = '../../../data/uploads/pdfs/';

// Asegurarse de que el directorio existe
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Variables de respuesta
$response = [
    'success' => false,
    'message' => '',
    'file_id' => '',
    'file_url' => ''
];

// Verificar si se envió un archivo
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['pdf_file']['tmp_name'];
    $file_name = $_FILES['pdf_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Verificar extensión
    if ($file_ext !== 'pdf') {
        $response['message'] = 'Solo se permiten archivos PDF.';
    } else {
        // Generar nombre único para el archivo
        $file_id = uniqid('pdf_');
        $new_file_name = $file_id . '.pdf';
        $file_destination = $upload_dir . $new_file_name;
        
        // Mover el archivo
        if (move_uploaded_file($file_tmp, $file_destination)) {
            $response['success'] = true;
            $response['message'] = 'Archivo subido correctamente.';
            $response['file_id'] = $file_id;
            $response['file_url'] = 'data/uploads/pdfs/' . $new_file_name;
        } else {
            $response['message'] = 'Error al guardar el archivo.';
        }
    }
} else {
    $response['message'] = 'No se ha enviado ningún archivo o ha ocurrido un error.';
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);