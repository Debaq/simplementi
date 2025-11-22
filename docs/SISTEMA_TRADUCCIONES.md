# Sistema de Traducciones SimpleMenti

Este documento explica cómo funciona el sistema de traducciones de SimpleMenti y cómo usarlo correctamente en tus archivos PHP.

## 📋 Tabla de Contenidos

1. [Estructura del Sistema](#estructura-del-sistema)
2. [Cómo Usar las Traducciones](#cómo-usar-las-traducciones)
3. [Crear Nuevos Archivos de Traducción](#crear-nuevos-archivos-de-traducción)
4. [Agregar Nuevas Claves de Traducción](#agregar-nuevas-claves-de-traducción)
5. [Selector de Idioma](#selector-de-idioma)
6. [Funciones Disponibles](#funciones-disponibles)
7. [Ejemplos Prácticos](#ejemplos-prácticos)
8. [Buenas Prácticas](#buenas-prácticas)

---

## 🏗️ Estructura del Sistema

El sistema de traducciones está compuesto por:

```
simplementi/
├── includes/
│   ├── Translation.php          # Clase principal del sistema
│   └── language_selector.php    # Componente selector de idioma
├── lang/
│   └── es.json                  # Archivo de traducción español
└── api/
    └── cambiar_idioma.php       # API para cambiar idioma
```

### Archivos Principales:

- **Translation.php**: Clase que maneja todas las traducciones
- **language_selector.php**: Componente visual para cambiar idioma
- **lang/*.json**: Archivos JSON con las traducciones
- **cambiar_idioma.php**: API que procesa el cambio de idioma

---

## 🚀 Cómo Usar las Traducciones

### Paso 1: Incluir el sistema en tu archivo PHP

Al inicio de tu archivo PHP, incluye el sistema de traducciones:

```php
<?php
session_start(); // Importante: debe estar antes de Translation
require_once 'includes/Translation.php';
?>
```

### Paso 2: Usar traducciones en tu código

Hay dos formas de obtener traducciones:

#### Método 1: Función helper `t()` (Recomendado)

```php
<!-- En HTML -->
<h1><?php echo t('app.name'); ?></h1>
<p><?php echo t('home.title'); ?></p>
```

#### Método 2: Usando la instancia de Translation

```php
<?php
$translation = Translation::getInstance();
echo $translation->get('app.name');
?>
```

### Paso 3: Usar traducciones con parámetros

Puedes pasar parámetros dinámicos a las traducciones:

```php
<!-- En el archivo de traducción JSON -->
{
    "presentation": {
        "num_questions": "{count} preguntas"
    }
}

<!-- En tu archivo PHP -->
<?php echo t('presentation.num_questions', ['count' => 10]); ?>
<!-- Resultado: "10 preguntas" -->
```

---

## 📝 Crear Nuevos Archivos de Traducción

### Para agregar un nuevo idioma (ej: inglés):

1. **Crear el archivo JSON** en la carpeta `lang/`:

```bash
lang/en.json
```

2. **Copiar la estructura** del archivo `es.json`:

```json
{
    "app": {
        "name": "SimpleMenti",
        "tagline": "Interactive system for real-time presentations and surveys",
        "copyright": "SimpleMenti © {year} - tmeduca.org"
    },
    "common": {
        "or": "or",
        "create": "Create",
        "edit": "Edit",
        "delete": "Delete",
        "save": "Save"
    }
}
```

3. **El nuevo idioma estará disponible automáticamente** en el selector de idioma.

### Reglas para nombres de archivos:

- Usar código ISO 639-1 de 2 letras: `es`, `en`, `fr`, `de`, etc.
- Siempre en minúsculas
- Extensión `.json`
- Ejemplos válidos: `es.json`, `en.json`, `fr.json`

---

## ➕ Agregar Nuevas Claves de Traducción

### Estructura recomendada:

Usa una estructura jerárquica con puntos para organizar las traducciones:

```json
{
    "sección": {
        "subsección": {
            "clave": "Traducción"
        }
    }
}
```

### Ejemplo práctico:

```json
{
    "admin": {
        "login": {
            "title": "Iniciar Sesión",
            "username_placeholder": "Ingresa tu usuario",
            "password_placeholder": "Ingresa tu contraseña",
            "submit_button": "Entrar",
            "forgot_password": "¿Olvidaste tu contraseña?"
        }
    }
}
```

### Uso en PHP:

```php
<h2><?php echo t('admin.login.title'); ?></h2>

<input type="text"
       placeholder="<?php echo t('admin.login.username_placeholder'); ?>">

<input type="password"
       placeholder="<?php echo t('admin.login.password_placeholder'); ?>">

<button><?php echo t('admin.login.submit_button'); ?></button>
```

### Agregar traducciones con parámetros dinámicos:

```json
{
    "messages": {
        "welcome_user": "Bienvenido, {name}",
        "items_found": "Se encontraron {count} elementos",
        "date_format": "Hoy es {day} de {month} de {year}"
    }
}
```

Uso:

```php
<?php echo t('messages.welcome_user', ['name' => 'Juan']); ?>
<!-- Resultado: "Bienvenido, Juan" -->

<?php echo t('messages.items_found', ['count' => 25]); ?>
<!-- Resultado: "Se encontraron 25 elementos" -->
```

---

## 🎨 Selector de Idioma

### Incluir el selector en una página:

```php
<?php
session_start();
require_once 'includes/Translation.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo t('app.name'); ?></title>
    <!-- Bootstrap CSS (requerido) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Tu contenido -->

    <!-- Selector de idioma -->
    <div class="container">
        <?php require_once 'includes/language_selector.php'; ?>
    </div>
</body>
</html>
```

### Variantes del selector:

#### Versión compacta:

```php
<div class="language-selector-compact">
    <?php require_once 'includes/language_selector.php'; ?>
</div>
```

#### En la barra de navegación:

```php
<nav class="navbar">
    <div class="container-fluid">
        <span class="navbar-brand"><?php echo t('app.name'); ?></span>
        <div class="d-flex">
            <?php require_once 'includes/language_selector.php'; ?>
        </div>
    </div>
</nav>
```

---

## 🔧 Funciones Disponibles

### 1. `t($key, $params = [])`

Obtiene una traducción por su clave.

```php
// Traducción simple
echo t('common.save');

// Con parámetros
echo t('messages.welcome_user', ['name' => 'María']);
```

### 2. `currentLang()`

Obtiene el idioma actual.

```php
$idioma = currentLang();
echo "Idioma actual: $idioma"; // "Idioma actual: es"
```

### 3. `setLang($language)`

Cambia el idioma programáticamente.

```php
if (setLang('en')) {
    echo "Idioma cambiado a inglés";
} else {
    echo "Error: idioma no disponible";
}
```

### 4. Métodos de la clase Translation:

```php
$translation = Translation::getInstance();

// Verificar si existe una traducción
if ($translation->has('admin.login.title')) {
    echo t('admin.login.title');
}

// Obtener idiomas disponibles
$languages = $translation->getAvailableLanguages();
print_r($languages); // ['es', 'en']
```

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Página de Login Completa

```php
<?php
session_start();
require_once 'includes/Translation.php';
?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo t('admin.login'); ?> - <?php echo t('app.name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Selector de idioma en la esquina -->
        <div class="position-absolute top-0 end-0 m-3">
            <?php require_once 'includes/language_selector.php'; ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo t('admin.login'); ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="procesar_login.php">
                            <div class="mb-3">
                                <label class="form-label">
                                    <?php echo t('admin.username'); ?>
                                </label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <?php echo t('admin.password'); ?>
                                </label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?php echo t('common.confirm'); ?>
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <?php echo t('common.cancel'); ?>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

### Ejemplo 2: Tabla con Traducciones

```php
<table class="table">
    <thead>
        <tr>
            <th><?php echo t('presentation.title'); ?></th>
            <th><?php echo t('presentation.author'); ?></th>
            <th><?php echo t('presentation.num_questions'); ?></th>
            <th><?php echo t('common.actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($presentaciones as $pres): ?>
        <tr>
            <td><?php echo htmlspecialchars($pres['titulo']); ?></td>
            <td><?php echo htmlspecialchars($pres['autor']); ?></td>
            <td><?php echo t('presentation.num_questions', ['count' => $pres['num_preguntas']]); ?></td>
            <td>
                <a href="editar.php?id=<?php echo $pres['id']; ?>">
                    <?php echo t('common.edit'); ?>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### Ejemplo 3: Mensajes de Validación

```php
<?php
$errores = [];

if (empty($_POST['email'])) {
    $errores[] = t('validation.required_field');
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = t('validation.invalid_email');
}

if (strlen($_POST['password']) < 8) {
    $errores[] = t('validation.min_length', ['min' => 8]);
}

if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<div class='alert alert-danger'>$error</div>";
    }
}
?>
```

### Ejemplo 4: JavaScript con Traducciones

Para usar traducciones en JavaScript, puedes pasar las traducciones como variables:

```php
<script>
const translations = {
    confirm: "<?php echo t('common.confirm'); ?>",
    cancel: "<?php echo t('common.cancel'); ?>",
    deleteConfirm: "<?php echo t('messages.confirm_delete'); ?>"
};

function eliminar(id) {
    if (confirm(translations.deleteConfirm)) {
        // Proceder con eliminación
    }
}
</script>
```

---

## ✅ Buenas Prácticas

### 1. Organización de Claves

```json
{
    "módulo": {
        "componente": {
            "elemento": "Traducción"
        }
    }
}
```

Ejemplo:
```json
{
    "admin": {
        "users": {
            "create_title": "Crear Usuario",
            "edit_title": "Editar Usuario",
            "delete_confirm": "¿Eliminar este usuario?"
        }
    }
}
```

### 2. Nombres de Claves Descriptivos

❌ **Mal:**
```json
{
    "btn1": "Guardar",
    "txt1": "Nombre"
}
```

✅ **Bien:**
```json
{
    "common": {
        "save_button": "Guardar",
        "name_label": "Nombre"
    }
}
```

### 3. Reutilizar Traducciones Comunes

Crea una sección `common` para textos que se usan en múltiples lugares:

```json
{
    "common": {
        "save": "Guardar",
        "cancel": "Cancelar",
        "delete": "Eliminar",
        "edit": "Editar",
        "create": "Crear"
    }
}
```

### 4. Mantener Sincronizados los Archivos

Cuando agregues una clave en `es.json`, agrégala también en todos los otros idiomas:

```json
// es.json
{
    "new_feature": {
        "title": "Nueva Función"
    }
}

// en.json
{
    "new_feature": {
        "title": "New Feature"
    }
}
```

### 5. Usar Escapado HTML

Siempre usa `htmlspecialchars()` cuando muestres contenido dinámico:

```php
<h1><?php echo htmlspecialchars(t('page.title')); ?></h1>
```

### 6. Comentarios en JSON

JSON no permite comentarios, pero puedes usar una clave especial:

```json
{
    "_comment": "Traducciones para el módulo de administración",
    "admin": {
        "title": "Administración"
    }
}
```

### 7. Validar JSON

Usa herramientas online para validar que tu JSON esté bien formado:
- https://jsonlint.com/
- https://jsonformatter.org/

---

## 🔍 Solución de Problemas

### Problema: Las traducciones no se cargan

**Solución:**
1. Verifica que `session_start()` esté al inicio del archivo
2. Verifica que el archivo JSON exista en `/lang/`
3. Verifica que el JSON esté bien formado

### Problema: Aparece la clave en lugar de la traducción

**Solución:**
- La clave no existe en el archivo JSON
- Verifica la ruta completa: `app.name` vs `app.title`
- Verifica mayúsculas/minúsculas

### Problema: El selector de idioma no funciona

**Solución:**
1. Verifica que Bootstrap esté incluido
2. Verifica que la ruta a `api/cambiar_idioma.php` sea correcta
3. Abre la consola del navegador para ver errores JavaScript

---

## 📞 Soporte

Para más información sobre SimpleMenti, visita: https://tmeduca.org

---

**Última actualización:** Noviembre 2025
**Versión del sistema:** 1.0
