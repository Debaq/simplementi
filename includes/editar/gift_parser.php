<?php
/**
 * Parser de formato GIFT para importar preguntas
 * Soporta: Multiple Choice, True-False, Short Answer
 */

class GiftParser {

    /**
     * Parse un archivo GIFT y retorna un array de preguntas
     * @param string $contenido - Contenido del archivo GIFT
     * @return array - Array de preguntas en formato del sistema
     */
    public static function parse($contenido) {
        $preguntas = [];

        // Dividir por líneas vacías para separar preguntas
        $bloques = preg_split('/\n\s*\n/', trim($contenido));

        foreach ($bloques as $bloque) {
            if (empty(trim($bloque)) || strpos($bloque, '//') === 0) {
                continue; // Saltar comentarios y bloques vacíos
            }

            $pregunta = self::parsePregunta($bloque);
            if ($pregunta !== null) {
                $preguntas[] = $pregunta;
            }
        }

        return $preguntas;
    }

    /**
     * Parse una pregunta individual del formato GIFT
     */
    private static function parsePregunta($bloque) {
        $lineas = explode("\n", trim($bloque));
        $texto_completo = implode("\n", $lineas);

        // Buscar el patrón de pregunta GIFT: texto_pregunta { opciones }
        if (!preg_match('/^(.*?)\s*\{(.*?)\}\s*$/s', $texto_completo, $matches)) {
            return null;
        }

        $texto_pregunta = trim($matches[1]);
        $contenido_respuestas = trim($matches[2]);

        // Detectar tipo de pregunta y parsear

        // TRUE/FALSE
        if (preg_match('/^(TRUE|FALSE|T|F)$/i', $contenido_respuestas)) {
            return self::parseTrueFalse($texto_pregunta, $contenido_respuestas);
        }

        // MULTIPLE CHOICE (con ~)
        if (strpos($contenido_respuestas, '~') !== false || strpos($contenido_respuestas, '=') === 0) {
            return self::parseMultipleChoice($texto_pregunta, $contenido_respuestas);
        }

        // SHORT ANSWER / ESSAY (sin opciones o con =)
        return self::parseShortAnswer($texto_pregunta, $contenido_respuestas);
    }

    /**
     * Parse pregunta de Verdadero/Falso
     */
    private static function parseTrueFalse($texto, $respuesta) {
        $respuesta_limpia = strtoupper(trim($respuesta));
        $es_verdadero = ($respuesta_limpia === 'TRUE' || $respuesta_limpia === 'T');

        return [
            'tipo' => 'verdadero_falso',
            'pregunta' => self::limpiarTexto($texto),
            'respuesta_correcta' => $es_verdadero,
            'explicacion' => ''
        ];
    }

    /**
     * Parse pregunta de opción múltiple
     */
    private static function parseMultipleChoice($texto, $opciones_texto) {
        $opciones = [];
        $respuesta_correcta = null;

        // Dividir por ~ o =
        $partes = preg_split('/([~=])/', $opciones_texto, -1, PREG_SPLIT_DELIM_CAPTURE);

        $i = 0;
        while ($i < count($partes)) {
            if ($partes[$i] === '=' || $partes[$i] === '~') {
                $es_correcta = ($partes[$i] === '=');
                $i++;

                if ($i < count($partes)) {
                    $texto_opcion = trim($partes[$i]);

                    // Remover feedback si existe (después de #)
                    if (strpos($texto_opcion, '#') !== false) {
                        $texto_opcion = trim(explode('#', $texto_opcion)[0]);
                    }

                    if (!empty($texto_opcion)) {
                        $opciones[] = self::limpiarTexto($texto_opcion);

                        if ($es_correcta) {
                            $respuesta_correcta = self::limpiarTexto($texto_opcion);
                        }
                    }
                }
            }
            $i++;
        }

        // Si no hay respuesta correcta, usar la primera
        if ($respuesta_correcta === null && !empty($opciones)) {
            $respuesta_correcta = $opciones[0];
        }

        return [
            'tipo' => 'opcion_multiple',
            'pregunta' => self::limpiarTexto($texto),
            'opciones' => $opciones,
            'respuesta_correcta' => $respuesta_correcta,
            'explicacion' => ''
        ];
    }

    /**
     * Parse pregunta de respuesta corta (convertida a palabra_libre)
     */
    private static function parseShortAnswer($texto, $respuesta) {
        return [
            'tipo' => 'palabra_libre',
            'pregunta' => self::limpiarTexto($texto),
            'explicacion' => ''
        ];
    }

    /**
     * Limpia el texto de caracteres especiales de GIFT
     */
    private static function limpiarTexto($texto) {
        // Remover escapes de GIFT
        $texto = str_replace(['\=', '\~', '\#', '\{', '\}'], ['=', '~', '#', '{', '}'], $texto);

        // Remover tags HTML potencialmente peligrosos
        $texto = strip_tags($texto, '<b><i><u><br><p>');

        return trim($texto);
    }

    /**
     * Valida que un array de preguntas tenga la estructura correcta
     */
    public static function validar($preguntas) {
        $errores = [];

        foreach ($preguntas as $i => $pregunta) {
            $num = $i + 1;

            if (empty($pregunta['pregunta'])) {
                $errores[] = "Pregunta #$num: El texto de la pregunta está vacío";
            }

            if (!isset($pregunta['tipo']) || !in_array($pregunta['tipo'], ['opcion_multiple', 'verdadero_falso', 'palabra_libre', 'nube_palabras'])) {
                $errores[] = "Pregunta #$num: Tipo de pregunta inválido";
            }

            if ($pregunta['tipo'] === 'opcion_multiple') {
                if (empty($pregunta['opciones']) || count($pregunta['opciones']) < 2) {
                    $errores[] = "Pregunta #$num: Debe tener al menos 2 opciones";
                }

                if (empty($pregunta['respuesta_correcta'])) {
                    $errores[] = "Pregunta #$num: Debe tener una respuesta correcta";
                }
            }
        }

        return $errores;
    }
}
