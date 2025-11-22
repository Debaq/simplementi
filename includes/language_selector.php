<?php
/**
 * Componente selector de idioma
 *
 * Este archivo se puede incluir en cualquier página para mostrar
 * un selector de idioma con estilo moderno.
 *
 * Uso:
 *   require_once 'includes/Translation.php';
 *   require_once 'includes/language_selector.php';
 */

// Asegurar que Translation esté cargado
if (!class_exists('Translation')) {
    require_once __DIR__ . '/Translation.php';
}

$translation = Translation::getInstance();
$currentLang = $translation->getCurrentLanguage();
$availableLanguages = $translation->getAvailableLanguages();

// Nombres completos de los idiomas
$languageNames = [
    'es' => 'Español',
    'en' => 'English',
    'fr' => 'Français',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'pt' => 'Português',
    'ca' => 'Català',
    'eu' => 'Euskara'
];

// Banderas de los idiomas (emojis)
$languageFlags = [
    'es' => '🇪🇸',
    'en' => '🇬🇧',
    'fr' => '🇫🇷',
    'de' => '🇩🇪',
    'it' => '🇮🇹',
    'pt' => '🇵🇹',
    'ca' => '🏴',
    'eu' => '🏴'
];
?>

<!-- Selector de Idioma -->
<div class="language-selector">
    <select id="languageSelect" class="form-select form-select-sm language-select" onchange="changeLanguage(this.value)">
        <?php foreach ($availableLanguages as $lang): ?>
            <option value="<?php echo $lang; ?>" <?php echo ($lang === $currentLang) ? 'selected' : ''; ?>>
                <?php
                    $flag = isset($languageFlags[$lang]) ? $languageFlags[$lang] . ' ' : '';
                    $name = isset($languageNames[$lang]) ? $languageNames[$lang] : strtoupper($lang);
                    echo $flag . $name;
                ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<style>
.language-selector {
    display: inline-block;
}

.language-select {
    min-width: 150px;
    cursor: pointer;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 0.9rem;
    background-color: white;
    transition: all 0.3s ease;
}

.language-select:hover {
    border-color: #6c5ce7;
    box-shadow: 0 2px 8px rgba(108, 92, 231, 0.15);
}

.language-select:focus {
    border-color: #6c5ce7;
    box-shadow: 0 0 0 0.2rem rgba(108, 92, 231, 0.25);
    outline: none;
}

/* Estilo para selector compacto (variante pequeña) */
.language-selector-compact .language-select {
    min-width: 120px;
    font-size: 0.85rem;
    padding: 4px 8px;
}
</style>

<script>
/**
 * Función para cambiar el idioma
 * @param {string} language Código del idioma
 */
function changeLanguage(language) {
    // Mostrar indicador de carga (opcional)
    const select = document.getElementById('languageSelect');
    const originalBg = select.style.backgroundColor;
    select.style.backgroundColor = '#f0f0f0';
    select.disabled = true;

    // Realizar la petición al API
    fetch('api/cambiar_idioma.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ language: language })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar la página para aplicar el nuevo idioma
            window.location.reload();
        } else {
            // Mostrar error
            alert('Error al cambiar idioma: ' + data.message);
            select.style.backgroundColor = originalBg;
            select.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al cambiar idioma');
        select.style.backgroundColor = originalBg;
        select.disabled = false;
    });
}
</script>
