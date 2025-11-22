<?php
/**
 * Exportador de formato GIFT para exportar preguntas
 * Convierte preguntas del sistema al formato GIFT estándar
 */

class GiftExporter {

    /**
     * Exporta un array de preguntas al formato GIFT
     * @param array $preguntas - Array de preguntas en formato del sistema
     * @return string - Contenido en formato GIFT
     */
    public static function export($preguntas) {
        $output = "// Preguntas exportadas desde SimpleMenti\n";
        $output .= "// Fecha: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($preguntas as $index => $pregunta) {
            $output .= self::exportPregunta($pregunta, $index + 1);
            $output .= "\n\n";
        }

        return $output;
    }

    /**
     * Exporta una pregunta individual al formato GIFT
     */
    private static function exportPregunta($pregunta, $numero) {
        $output = "";
        $tipo = $pregunta['tipo'] ?? '';
        $texto = $pregunta['texto'] ?? $pregunta['pregunta'] ?? '';

        // Escapar caracteres especiales de GIFT
        $texto_escapado = self::escaparTexto($texto);

        switch ($tipo) {
            case 'verdadero_falso':
                $output .= self::exportTrueFalse($texto_escapado, $pregunta);
                break;

            case 'opcion_multiple':
                $output .= self::exportMultipleChoice($texto_escapado, $pregunta);
                break;

            case 'palabra_libre':
            case 'nube_palabras':
                $output .= self::exportShortAnswer($texto_escapado, $pregunta);
                break;

            default:
                // Tipo no soportado, exportar como pregunta abierta
                $output .= self::exportShortAnswer($texto_escapado, $pregunta);
        }

        return $output;
    }

    /**
     * Exporta pregunta de Verdadero/Falso
     */
    private static function exportTrueFalse($texto, $pregunta) {
        $respuesta_correcta = $pregunta['respuesta_correcta'] ?? true;

        // Convertir respuesta a formato GIFT
        $es_verdadero = false;
        if (is_bool($respuesta_correcta)) {
            $es_verdadero = $respuesta_correcta;
        } elseif (is_string($respuesta_correcta)) {
            $es_verdadero = (strtolower($respuesta_correcta) === 'verdadero' ||
                           strtolower($respuesta_correcta) === 'true');
        }

        $respuesta_gift = $es_verdadero ? 'TRUE' : 'FALSE';

        // Agregar feedback si existe
        $feedback = '';
        if (isset($pregunta['explicacion']) && !empty($pregunta['explicacion'])) {
            $feedback = '#' . self::escaparTexto($pregunta['explicacion']);
        }

        return "{$texto} {{$respuesta_gift}{$feedback}}";
    }

    /**
     * Exporta pregunta de opción múltiple
     */
    private static function exportMultipleChoice($texto, $pregunta) {
        $opciones = $pregunta['opciones'] ?? [];
        $respuesta_correcta = $pregunta['respuesta_correcta'] ?? '';

        $opciones_gift = [];

        foreach ($opciones as $opcion) {
            $opcion_escapada = self::escaparTexto($opcion);
            $es_correcta = ($opcion === $respuesta_correcta);
            $marcador = $es_correcta ? '=' : '~';

            // Agregar feedback específico si existe
            $feedback = '';
            if (isset($pregunta['feedbacks'][$opcion]) && !empty($pregunta['feedbacks'][$opcion])) {
                $feedback = '#' . self::escaparTexto($pregunta['feedbacks'][$opcion]);
            }

            $opciones_gift[] = "{$marcador}{$opcion_escapada}{$feedback}";
        }

        $opciones_texto = implode("\n\t", $opciones_gift);
        return "{$texto} {\n\t{$opciones_texto}\n}";
    }

    /**
     * Exporta pregunta de respuesta corta (palabra libre o nube de palabras)
     */
    private static function exportShortAnswer($texto, $pregunta) {
        $explicacion = '';

        if (isset($pregunta['explicacion']) && !empty($pregunta['explicacion'])) {
            $explicacion = '#' . self::escaparTexto($pregunta['explicacion']);
        }

        return "{$texto} {{$explicacion}}";
    }

    /**
     * Escapa caracteres especiales del formato GIFT
     */
    private static function escaparTexto($texto) {
        // Escapar caracteres especiales de GIFT
        $texto = str_replace(['=', '~', '#', '{', '}'], ['\=', '\~', '\#', '\{', '\}'], $texto);

        return $texto;
    }

    /**
     * Exporta preguntas directamente a un archivo descargable
     */
    public static function exportToFile($preguntas, $nombre_archivo = 'preguntas.gift') {
        $contenido = self::export($preguntas);

        // Establecer headers para descarga
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Content-Length: ' . strlen($contenido));

        echo $contenido;
        exit;
    }
}
