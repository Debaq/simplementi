<?php
/**
 * Sistema de Traducciones para SimpleMenti
 *
 * Esta clase maneja la carga y gestión de traducciones
 * desde archivos JSON almacenados en la carpeta /lang
 */
class Translation {
    private static $instance = null;
    private $currentLanguage = 'es'; // Idioma por defecto
    private $translations = [];
    private $fallbackLanguage = 'es';
    private $langPath;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        $this->langPath = dirname(__DIR__) . '/lang/';
        $this->loadLanguageFromSession();
        $this->loadTranslations();
    }

    /**
     * Obtener instancia única de la clase
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Cargar idioma desde la sesión
     */
    private function loadLanguageFromSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['language'])) {
            $this->currentLanguage = $_SESSION['language'];
        }
    }

    /**
     * Cargar traducciones desde archivo JSON
     */
    private function loadTranslations() {
        $filePath = $this->langPath . $this->currentLanguage . '.json';

        if (!file_exists($filePath)) {
            // Si no existe el archivo del idioma actual, cargar el fallback
            $filePath = $this->langPath . $this->fallbackLanguage . '.json';
        }

        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $this->translations = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error al cargar traducciones: " . json_last_error_msg());
                $this->translations = [];
            }
        } else {
            error_log("No se encontró archivo de traducciones: $filePath");
            $this->translations = [];
        }
    }

    /**
     * Obtener una traducción por su clave
     *
     * @param string $key Clave de la traducción (puede usar notación de punto: "menu.home")
     * @param array $params Parámetros para reemplazar en la traducción (opcional)
     * @return string La traducción o la clave si no se encuentra
     */
    public function get($key, $params = []) {
        $keys = explode('.', $key);
        $value = $this->translations;

        // Navegar por la estructura anidada
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                // Si no se encuentra la traducción, devolver la clave
                return $key;
            }
        }

        // Si el valor no es una cadena, devolver la clave
        if (!is_string($value)) {
            return $key;
        }

        // Reemplazar parámetros si existen
        if (!empty($params)) {
            foreach ($params as $paramKey => $paramValue) {
                $value = str_replace("{{$paramKey}}", $paramValue, $value);
            }
        }

        return $value;
    }

    /**
     * Cambiar el idioma actual
     *
     * @param string $language Código del idioma (ej: 'es', 'en')
     * @return bool True si se cambió correctamente, false si no existe el archivo
     */
    public function setLanguage($language) {
        $filePath = $this->langPath . $language . '.json';

        if (file_exists($filePath)) {
            $this->currentLanguage = $language;
            $_SESSION['language'] = $language;
            $this->loadTranslations();
            return true;
        }

        return false;
    }

    /**
     * Obtener el idioma actual
     *
     * @return string Código del idioma actual
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }

    /**
     * Obtener lista de idiomas disponibles
     *
     * @return array Array con códigos de idiomas disponibles
     */
    public function getAvailableLanguages() {
        $languages = [];
        $files = glob($this->langPath . '*.json');

        foreach ($files as $file) {
            $languages[] = basename($file, '.json');
        }

        return $languages;
    }

    /**
     * Verificar si existe una traducción para una clave
     *
     * @param string $key Clave de la traducción
     * @return bool True si existe, false si no
     */
    public function has($key) {
        $keys = explode('.', $key);
        $value = $this->translations;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return false;
            }
        }

        return is_string($value);
    }
}

/**
 * Función helper global para obtener traducciones
 *
 * @param string $key Clave de la traducción
 * @param array $params Parámetros opcionales
 * @return string La traducción
 */
function t($key, $params = []) {
    return Translation::getInstance()->get($key, $params);
}

/**
 * Función helper para obtener el idioma actual
 *
 * @return string Código del idioma actual
 */
function currentLang() {
    return Translation::getInstance()->getCurrentLanguage();
}

/**
 * Función helper para cambiar el idioma
 *
 * @param string $language Código del idioma
 * @return bool True si se cambió correctamente
 */
function setLang($language) {
    return Translation::getInstance()->setLanguage($language);
}
