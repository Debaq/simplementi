<?php
/**
 * Pantalla fullscreen con anotaciones para estudiantes
 * Permite dibujar y escribir sobre las diapositivas
 */

// Obtener configuraciones
$config = isset($test_data['configuracion']) ? $test_data['configuracion'] : [];
$permitir_notas = isset($config['permitir_notas']) && $config['permitir_notas'];
$permitir_marcadores = isset($config['permitir_marcadores']) && $config['permitir_marcadores'];
$permitir_navegacion_libre = isset($config['permitir_navegacion_libre']) && $config['permitir_navegacion_libre'];
$permitir_interacciones = isset($config['permitir_interacciones']) && $config['permitir_interacciones'];
$permitir_exportar = isset($config['exportar_con_anotaciones']) && $config['exportar_con_anotaciones'];
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #000;
        color: #fff;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        overflow: hidden;
    }

    #slide-container {
        width: 100vw;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        position: relative;
    }

    #slide-wrapper {
        position: relative;
        max-width: 100%;
        max-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #slide-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    #annotation-canvas {
        position: absolute;
        top: 0;
        left: 0;
        cursor: crosshair;
        touch-action: none;
    }

    /* Barra de herramientas */
    #toolbar {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        padding: 15px 20px;
        border-radius: 50px;
        display: flex;
        gap: 15px;
        align-items: center;
        backdrop-filter: blur(10px);
        z-index: 1000;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .tool-btn {
        width: 45px;
        height: 45px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 18px;
    }

    .tool-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .tool-btn.active {
        background: #4e73df;
        border-color: #4e73df;
    }

    .color-picker {
        display: flex;
        gap: 8px;
    }

    .color-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .color-btn:hover,
    .color-btn.active {
        border-color: #fff;
        transform: scale(1.1);
    }

    .size-selector {
        display: flex;
        gap: 8px;
        padding: 0 10px;
        border-left: 1px solid rgba(255, 255, 255, 0.2);
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }

    .size-btn {
        width: 35px;
        height: 35px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
    }

    .size-btn:hover,
    .size-btn.active {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
    }

    /* Indicador de carga */
    #loading-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.1);
        padding: 10px 20px;
        border-radius: 20px;
        display: none;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    #loading-indicator.show {
        display: flex;
    }

    .loading-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Indicador de slide */
    #slide-info {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #fff;
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    /* Controles de navegación */
    #navigation-controls {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.9);
        padding: 10px 20px;
        border-radius: 50px;
        display: flex;
        gap: 15px;
        align-items: center;
        backdrop-filter: blur(10px);
        z-index: 999;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    #navigation-controls button {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    #navigation-controls button:hover:not(:disabled) {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
    }

    #navigation-controls button:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    #sync-toggle {
        background: #4e73df;
        border-color: #4e73df;
        padding: 8px 20px;
        width: auto;
        border-radius: 20px;
        font-size: 14px;
    }

    #sync-toggle.synced {
        background: #1cc88a;
        border-color: #1cc88a;
    }

    /* Banner de desincronización */
    #desync-banner {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(231, 76, 60, 0.95);
        color: #fff;
        padding: 15px 30px;
        border-radius: 12px;
        display: none;
        z-index: 2001;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        text-align: center;
    }

    #desync-banner.show {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    #desync-banner strong {
        font-size: 16px;
    }

    #desync-banner small {
        display: block;
        margin-top: 5px;
        opacity: 0.9;
    }

    /* Panel de interacción */
    #interaction-panel {
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.9);
        padding: 15px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        backdrop-filter: blur(10px);
        z-index: 1000;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .interaction-btn {
        width: 50px;
        height: 50px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 20px;
        position: relative;
    }

    .interaction-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    .interaction-btn.active {
        background: #e74c3c;
        border-color: #e74c3c;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
        }
    }

    .interaction-btn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }

    /* Modal de pregunta */
    #question-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px;
        border-radius: 12px;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        min-width: 400px;
        max-width: 500px;
    }

    #question-modal.show {
        display: block;
    }

    #question-input {
        width: 100%;
        padding: 15px;
        border: 2px solid #4e73df;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
        resize: vertical;
        min-height: 100px;
    }

    /* Modal de reacciones */
    #reaction-picker {
        position: fixed;
        bottom: 90px;
        left: 20px;
        background: rgba(0, 0, 0, 0.95);
        padding: 15px;
        border-radius: 12px;
        display: none;
        z-index: 1001;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    #reaction-picker.show {
        display: flex;
        gap: 10px;
        animation: fadeIn 0.2s ease-out;
    }

    .reaction-option {
        font-size: 30px;
        cursor: pointer;
        transition: transform 0.2s;
        padding: 5px;
    }

    .reaction-option:hover {
        transform: scale(1.3);
    }

    /* Panel de notas */
    #notes-panel {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(10px);
        border-top: 2px solid rgba(255, 255, 255, 0.1);
        transition: transform 0.3s ease-out;
        transform: translateY(calc(100% - 50px));
        z-index: 998;
    }

    #notes-panel.expanded {
        transform: translateY(0);
    }

    #notes-header {
        padding: 12px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
    }

    #notes-header:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    #notes-header h6 {
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #notes-body {
        padding: 20px;
        max-height: 300px;
        overflow-y: auto;
    }

    #notes-textarea {
        width: 100%;
        min-height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: #fff;
        padding: 15px;
        font-size: 14px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        resize: vertical;
    }

    #notes-textarea:focus {
        outline: none;
        border-color: #4e73df;
        background: rgba(255, 255, 255, 0.15);
    }

    #notes-textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    #notes-status {
        margin-top: 10px;
        text-align: right;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
    }

    #notes-status.saved {
        color: #1cc88a;
    }

    .notes-hint {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.5);
        margin-bottom: 10px;
    }

    /* Modal de texto */
    #text-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px;
        border-radius: 12px;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        min-width: 300px;
    }

    #text-modal.show {
        display: block;
    }

    #text-input {
        width: 100%;
        padding: 10px;
        border: 2px solid #4e73df;
        border-radius: 8px;
        font-size: 16px;
        margin-bottom: 15px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
    }

    .modal-btn-primary {
        background: #4e73df;
        color: #fff;
    }

    .modal-btn-secondary {
        background: #858796;
        color: #fff;
    }

    /* Mensaje de sincronización */
    #sync-message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 30px 40px;
        border-radius: 12px;
        text-align: center;
        display: none;
        z-index: 2000;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    #sync-message.show {
        display: block;
    }

    #sync-message h3 {
        margin-bottom: 10px;
        color: #4e73df;
    }

    #sync-message p {
        margin: 0;
        color: #666;
    }

    /* Botón de modo oscuro */
    #dark-mode-toggle {
        position: fixed;
        bottom: 70px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: rgba(0, 0, 0, 0.7);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 1000;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    #dark-mode-toggle:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(1.1);
    }

    /* Estilos para modo oscuro */
    body.dark-mode {
        background: #1a1a1a;
    }

    body.dark-mode #slide-container {
        background: #0d0d0d;
    }

    body.dark-mode #toolbar {
        background: rgba(20, 20, 20, 0.95);
        border: 1px solid #333;
    }

    body.dark-mode .tool-btn,
    body.dark-mode .size-btn {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    body.dark-mode .tool-btn:hover,
    body.dark-mode .size-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    body.dark-mode .tool-btn.active,
    body.dark-mode .size-btn.active {
        background: #4e73df;
    }

    body.dark-mode #navigation-controls {
        background: rgba(20, 20, 20, 0.95);
        border: 1px solid #333;
    }

    body.dark-mode #navigation-controls button {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    body.dark-mode #navigation-controls button:hover:not(:disabled) {
        background: rgba(255, 255, 255, 0.2);
    }

    body.dark-mode #navigation-controls #sync-toggle.synced {
        background: rgba(28, 200, 138, 0.2);
        color: #1cc88a;
    }

    body.dark-mode #slide-info {
        background: rgba(20, 20, 20, 0.9);
        border: 1px solid #333;
    }

    body.dark-mode #dark-mode-toggle {
        background: rgba(255, 255, 255, 0.15);
    }

    body.dark-mode #dark-mode-toggle:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    body.dark-mode #notes-panel {
        background: rgba(20, 20, 20, 0.98);
        border-top: 1px solid #333;
    }

    body.dark-mode #notes-header {
        background: rgba(30, 30, 30, 0.95);
        border-bottom: 1px solid #444;
    }

    body.dark-mode #notes-header h6 {
        color: #fff;
    }

    body.dark-mode #notes-textarea {
        background: rgba(40, 40, 40, 0.95);
        color: #fff;
        border-color: #444;
    }

    body.dark-mode #notes-textarea::placeholder {
        color: #888;
    }

    body.dark-mode .notes-hint {
        background: rgba(78, 115, 223, 0.15);
        border-left-color: #4e73df;
        color: #aaa;
    }

    body.dark-mode #notes-status {
        color: #aaa;
    }

    body.dark-mode #text-modal,
    body.dark-mode #question-modal {
        background: rgba(30, 30, 30, 0.98);
        color: #fff;
        border: 1px solid #444;
    }

    body.dark-mode #text-input,
    body.dark-mode #question-input {
        background: rgba(40, 40, 40, 0.95);
        color: #fff;
        border-color: #555;
    }

    body.dark-mode #text-input::placeholder,
    body.dark-mode #question-input::placeholder {
        color: #888;
    }

    body.dark-mode .form-check-label {
        color: #ccc;
    }

    body.dark-mode .modal-btn-secondary {
        background: #555;
    }

    body.dark-mode #loading-indicator {
        background: rgba(20, 20, 20, 0.95);
        color: #fff;
    }

    body.dark-mode #interaction-panel {
        background: rgba(20, 20, 20, 0.95);
        border: 1px solid #333;
    }

    body.dark-mode .interaction-btn {
        background: rgba(255, 255, 255, 0.1);
    }

    body.dark-mode .interaction-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    body.dark-mode #reaction-picker {
        background: rgba(20, 20, 20, 0.98);
        border: 1px solid #333;
    }

    /* Sistema de marcadores */
    #bookmark-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: #ffc107;
        font-size: 20px;
        transition: all 0.3s;
    }

    #bookmark-btn:hover {
        background: rgba(255, 193, 7, 0.2);
        transform: scale(1.1);
    }

    #bookmark-btn.active {
        background: rgba(255, 193, 7, 0.3);
        color: #ffc107;
        border-color: #ffc107;
        animation: pulse 0.5s ease-out;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    #bookmarks-panel-btn {
        position: fixed;
        top: 80px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: rgba(255, 193, 7, 0.9);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 1001;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }

    #bookmarks-panel-btn:hover {
        background: rgba(255, 193, 7, 1);
        transform: scale(1.1);
    }

    #bookmarks-panel-btn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74a3b;
        color: white;
        border-radius: 50%;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: bold;
    }

    #bookmarks-panel {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 20px rgba(0, 0, 0, 0.2);
        z-index: 1002;
        transition: right 0.3s ease-out;
        display: flex;
        flex-direction: column;
    }

    #bookmarks-panel.open {
        right: 0;
    }

    .bookmarks-header {
        background: linear-gradient(to right, #ffc107, #ff9800);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .bookmarks-header h5 {
        margin: 0;
        font-size: 18px;
    }

    .bookmarks-header .btn-close-panel {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
    }

    .bookmarks-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .bookmark-item {
        background: #f8f9fc;
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .bookmark-item:hover {
        background: #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transform: translateX(-5px);
    }

    .bookmark-item.category-important {
        border-left-color: #e74a3b;
    }

    .bookmark-item.category-review {
        border-left-color: #f6c23e;
    }

    .bookmark-item.category-doubt {
        border-left-color: #36b9cc;
    }

    .bookmark-item.category-understood {
        border-left-color: #1cc88a;
    }

    .bookmark-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .bookmark-slide-number {
        font-weight: bold;
        color: #4e73df;
        font-size: 16px;
    }

    .bookmark-category {
        font-size: 12px;
        padding: 3px 10px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
    }

    .bookmark-category.important {
        background: #e74a3b;
    }

    .bookmark-category.review {
        background: #f6c23e;
        color: #333;
    }

    .bookmark-category.doubt {
        background: #36b9cc;
    }

    .bookmark-category.understood {
        background: #1cc88a;
    }

    .bookmark-note {
        color: #666;
        font-size: 14px;
        margin-top: 8px;
        font-style: italic;
    }

    .bookmark-delete {
        background: none;
        border: none;
        color: #e74a3b;
        font-size: 16px;
        cursor: pointer;
        padding: 5px;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

    .bookmark-delete:hover {
        opacity: 1;
    }

    .empty-bookmarks {
        text-align: center;
        color: #999;
        padding: 40px 20px;
    }

    .empty-bookmarks i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    #bookmark-modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        z-index: 10001;
        min-width: 400px;
    }

    #bookmark-modal.show {
        display: block;
    }

    #bookmark-modal h4 {
        margin-bottom: 20px;
        color: #4e73df;
    }

    .category-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .category-option {
        flex: 1;
        min-width: 80px;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
        font-weight: 500;
    }

    .category-option:hover {
        border-color: #4e73df;
    }

    .category-option.selected {
        border-width: 3px;
    }

    .category-option.important.selected {
        background: #fee;
        border-color: #e74a3b;
        color: #e74a3b;
    }

    .category-option.review.selected {
        background: #fffbea;
        border-color: #f6c23e;
        color: #856404;
    }

    .category-option.doubt.selected {
        background: #e7f7f9;
        border-color: #36b9cc;
        color: #0c5460;
    }

    .category-option.understood.selected {
        background: #edf7f3;
        border-color: #1cc88a;
        color: #155724;
    }

    #bookmark-note-input {
        width: 100%;
        min-height: 80px;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
        font-family: inherit;
    }

    #bookmark-note-input:focus {
        outline: none;
        border-color: #4e73df;
    }

    /* Dark mode para marcadores */
    body.dark-mode #bookmarks-panel {
        background: #1a1a1a;
        color: #fff;
    }

    body.dark-mode .bookmark-item {
        background: rgba(255, 255, 255, 0.05);
    }

    body.dark-mode .bookmark-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    body.dark-mode #bookmark-modal {
        background: #2a2a2a;
        color: #fff;
    }

    body.dark-mode #bookmark-note-input {
        background: #1a1a1a;
        border-color: #444;
        color: #fff;
    }

    body.dark-mode .category-option {
        background: #1a1a1a;
        border-color: #444;
        color: #fff;
    }
</style>

<div id="slide-container">
    <div id="slide-wrapper">
        <img id="slide-image"
             src="<?php echo htmlspecialchars($slide_data['path']); ?>"
             alt="Slide <?php echo $slide_number; ?>">
        <canvas id="annotation-canvas"></canvas>
    </div>
</div>

<!-- Barra de herramientas -->
<div id="toolbar">
    <button class="tool-btn active" id="tool-pen" title="Lápiz">
        <i class="fas fa-pen"></i>
    </button>
    <button class="tool-btn" id="tool-marker" title="Marcador">
        <i class="fas fa-highlighter"></i>
    </button>
    <button class="tool-btn" id="tool-text" title="Texto">
        <i class="fas fa-font"></i>
    </button>
    <button class="tool-btn" id="tool-eraser" title="Borrador">
        <i class="fas fa-eraser"></i>
    </button>

    <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2); margin: 0 5px;"></div>

    <button class="tool-btn" id="tool-arrow" title="Flecha">
        <i class="fas fa-arrow-right"></i>
    </button>
    <button class="tool-btn" id="tool-line" title="Línea recta">
        <i class="fas fa-minus"></i>
    </button>
    <button class="tool-btn" id="tool-circle" title="Círculo">
        <i class="far fa-circle"></i>
    </button>
    <button class="tool-btn" id="tool-rectangle" title="Rectángulo">
        <i class="far fa-square"></i>
    </button>

    <div class="size-selector">
        <button class="size-btn" data-size="2" title="Fino">S</button>
        <button class="size-btn active" data-size="4" title="Medio">M</button>
        <button class="size-btn" data-size="8" title="Grueso">L</button>
    </div>

    <div class="color-picker">
        <button class="color-btn active" style="background: #000;" data-color="#000000"></button>
        <button class="color-btn" style="background: #e74c3c;" data-color="#e74c3c"></button>
        <button class="color-btn" style="background: #3498db;" data-color="#3498db"></button>
        <button class="color-btn" style="background: #2ecc71;" data-color="#2ecc71"></button>
        <button class="color-btn" style="background: #f39c12;" data-color="#f39c12"></button>
    </div>

    <button class="tool-btn" id="tool-undo" title="Deshacer">
        <i class="fas fa-undo"></i>
    </button>
    <button class="tool-btn" id="tool-clear" title="Limpiar todo">
        <i class="fas fa-trash"></i>
    </button>
    <button class="tool-btn" id="tool-save" title="Guardar anotaciones">
        <i class="fas fa-save"></i>
    </button>
    <?php if ($permitir_exportar): ?>
    <button class="tool-btn" id="tool-export-pdf" title="Exportar PDF con anotaciones y notas" style="background: #1cc88a; color: white;">
        <i class="fas fa-file-pdf"></i>
    </button>
    <?php endif; ?>
</div>

<!-- Controles de navegación -->
<div id="navigation-controls">
    <?php if ($permitir_navegacion_libre): ?>
    <button id="nav-prev" title="Slide anterior">
        <i class="fas fa-chevron-left"></i>
    </button>
    <?php endif; ?>
    <?php if ($permitir_marcadores): ?>
    <button id="bookmark-btn" title="Marcar este slide">
        <i class="far fa-star"></i>
    </button>
    <?php endif; ?>
    <button id="sync-toggle" class="synced" title="Sincronizado con presentador">
        <i class="fas fa-sync"></i> Sincronizado
    </button>
    <?php if ($permitir_navegacion_libre): ?>
    <button id="nav-next" title="Siguiente slide">
        <i class="fas fa-chevron-right"></i>
    </button>
    <?php endif; ?>
</div>

<?php if ($permitir_marcadores): ?>
<!-- Botón flotante para abrir panel de marcadores -->
<button id="bookmarks-panel-btn" title="Ver marcadores">
    <i class="fas fa-bookmark"></i>
    <span class="badge" id="bookmarks-count" style="display: none;">0</span>
</button>

<!-- Panel lateral de marcadores -->
<div id="bookmarks-panel">
    <div class="bookmarks-header">
        <h5><i class="fas fa-bookmark me-2"></i>Mis Marcadores</h5>
        <button class="btn-close-panel" onclick="toggleBookmarksPanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="bookmarks-body" id="bookmarks-list">
        <div class="empty-bookmarks">
            <i class="fas fa-bookmark"></i>
            <p>No tienes marcadores aún</p>
            <small>Marca slides importantes con la estrella ⭐</small>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($permitir_marcadores): ?>
<!-- Modal para crear/editar marcador -->
<div id="bookmark-modal">
    <h4><i class="fas fa-star me-2"></i>Marcar Slide <?php echo $slide_number; ?></h4>
    <div class="category-selector">
        <div class="category-option important" data-category="important">
            <div>🔴 Importante</div>
        </div>
        <div class="category-option review" data-category="review">
            <div>📝 Revisar</div>
        </div>
        <div class="category-option doubt" data-category="doubt">
            <div>❓ Duda</div>
        </div>
        <div class="category-option understood" data-category="understood">
            <div>✅ Entendido</div>
        </div>
    </div>
    <textarea id="bookmark-note-input" placeholder="Agrega una nota opcional (ej: 'Revisar este concepto para el examen')"></textarea>
    <div class="modal-buttons">
        <button class="modal-btn modal-btn-secondary" id="bookmark-cancel">Cancelar</button>
        <button class="modal-btn modal-btn-primary" id="bookmark-save">Guardar Marcador</button>
    </div>
</div>
<?php endif; ?>

<!-- Banner de desincronización -->
<div id="desync-banner">
    <strong><i class="fas fa-unlink me-2"></i>Desincronizado</strong>
    <small>Estás en el slide <span id="current-viewing"></span>, el profesor está en <span id="presenter-at"></span></small>
</div>

<?php if ($permitir_interacciones): ?>
<!-- Panel de interacción -->
<div id="interaction-panel">
    <button class="interaction-btn" id="raise-hand-btn" title="Levantar mano">
        <i class="fas fa-hand-paper"></i>
    </button>
    <button class="interaction-btn" id="ask-question-btn" title="Hacer pregunta">
        <i class="fas fa-question-circle"></i>
    </button>
    <button class="interaction-btn" id="understanding-btn" title="Medidor de comprensión">
        <i class="fas fa-brain"></i>
    </button>
    <button class="interaction-btn" id="reaction-btn" title="Reacciones rápidas">
        <i class="fas fa-smile"></i>
    </button>
</div>

<!-- Picker de reacciones -->
<div id="reaction-picker">
    <span class="reaction-option" data-reaction="👍">👍</span>
    <span class="reaction-option" data-reaction="❤️">❤️</span>
    <span class="reaction-option" data-reaction="😮">😮</span>
    <span class="reaction-option" data-reaction="🤔">🤔</span>
    <span class="reaction-option" data-reaction="👏">👏</span>
    <span class="reaction-option" data-reaction="🎉">🎉</span>
</div>
<?php endif; ?>

<div id="slide-info">
    <i class="fas fa-file-pdf me-2"></i>
    Slide <span id="current-slide"><?php echo $slide_number; ?></span> / <span id="total-slides"><?php echo count($test_data['pdf_images']); ?></span>
</div>

<button id="dark-mode-toggle" title="Modo oscuro">
    <i class="fas fa-moon"></i>
</button>

<div id="loading-indicator">
    <div class="loading-spinner"></div>
    <span>Sincronizando...</span>
</div>

<div id="sync-message">
    <h3><i class="fas fa-sync-alt"></i> Sincronizando</h3>
    <p>Cargando nuevo contenido...</p>
</div>

<!-- Modal de texto -->
<div id="text-modal">
    <h4 style="margin-bottom: 15px;">Agregar texto</h4>
    <input type="text" id="text-input" placeholder="Escribe tu texto aquí...">
    <div class="modal-buttons">
        <button class="modal-btn modal-btn-secondary" id="text-cancel">Cancelar</button>
        <button class="modal-btn modal-btn-primary" id="text-ok">Agregar</button>
    </div>
</div>

<?php if ($permitir_interacciones): ?>
<!-- Modal de pregunta -->
<div id="question-modal">
    <h4 style="margin-bottom: 15px;"><i class="fas fa-question-circle me-2"></i>Hacer una pregunta</h4>
    <textarea id="question-input" placeholder="Escribe tu pregunta aquí... (será enviada de forma anónima)"></textarea>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="question-anonymous" checked>
        <label class="form-check-label" for="question-anonymous">
            Enviar de forma anónima
        </label>
    </div>
    <div class="modal-buttons">
        <button class="modal-btn modal-btn-secondary" id="question-cancel">Cancelar</button>
        <button class="modal-btn modal-btn-primary" id="question-send">Enviar pregunta</button>
    </div>
</div>
<?php endif; ?>

<!-- Modal de progreso de exportación PDF -->
<div id="pdf-export-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 40px; border-radius: 15px; text-align: center; max-width: 500px;">
        <div class="loading-spinner" style="margin: 0 auto 20px;"></div>
        <h4 style="color: #4e73df; margin-bottom: 15px;">Generando PDF</h4>
        <p id="pdf-progress-text" style="color: #666; margin-bottom: 10px;">Preparando exportación...</p>
        <div style="background: #f0f0f0; border-radius: 10px; height: 30px; overflow: hidden;">
            <div id="pdf-progress-bar" style="background: linear-gradient(to right, #4e73df, #1cc88a); height: 100%; width: 0%; transition: width 0.3s;"></div>
        </div>
        <small id="pdf-progress-detail" style="color: #999; display: block; margin-top: 10px;">Esto puede tomar unos momentos...</small>
    </div>
</div>

<?php if ($permitir_notas): ?>
<!-- Panel de notas -->
<div id="notes-panel">
    <div id="notes-header" onclick="toggleNotes()">
        <h6>
            <i class="fas fa-sticky-note"></i>
            Notas del Slide <?php echo $slide_number; ?>
            <span id="notes-char-count">0 caracteres</span>
        </h6>
        <i class="fas fa-chevron-up" id="notes-chevron"></i>
    </div>
    <div id="notes-body">
        <div class="notes-hint">
            <i class="fas fa-lightbulb"></i> Escribe tus notas aquí. Se guardan automáticamente para cada diapositiva.
        </div>
        <textarea id="notes-textarea" placeholder="Escribe tus notas sobre esta diapositiva..."></textarea>
        <div id="notes-status">Sin guardar</div>
    </div>
</div>
<?php endif; ?>

<script>
const codigo = '<?php echo $codigo_sesion; ?>';
const participanteId = '<?php echo $participante_id; ?>';
const participanteNombre = '<?php echo isset($nombre_participante) ? addslashes($nombre_participante) : "Estudiante"; ?>';
let currentSequenceIndex = <?php echo $sequence_index; ?>;
let presenterSequenceIndex = <?php echo $sequence_index; ?>; // Índice del presentador
let viewingSequenceIndex = <?php echo $sequence_index; ?>; // Índice que está viendo el estudiante
const slideNumber = <?php echo $slide_number; ?>;
const totalSlides = <?php echo count($test_data['pdf_images']); ?>;
const serverUrl = window.location.protocol + '//' + window.location.host + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
const slideSequence = <?php echo json_encode($test_data['pdf_sequence']); ?>;

// Estado de sincronización
let isSynced = true;
let handRaised = false;
let currentUnderstanding = null; // 'confused' o 'understood'

// Variables de estado del canvas
let canvas, ctx;
let isDrawing = false;
let currentTool = 'pen';
let currentColor = '#000000';
let currentSize = 4;
let strokes = [];
let currentStroke = null;
let textPosition = null;
let shapeStartPoint = null; // Para formas geométricas
let tempCanvas = null; // Canvas temporal para preview de formas
let tempCtx = null;

// Inicializar canvas
function initCanvas() {
    canvas = document.getElementById('annotation-canvas');
    ctx = canvas.getContext('2d');
    const img = document.getElementById('slide-image');

    // Esperar a que la imagen cargue
    img.onload = function() {
        resizeCanvas();
    };

    if (img.complete) {
        resizeCanvas();
    }
}

function resizeCanvas() {
    const img = document.getElementById('slide-image');
    canvas.width = img.width;
    canvas.height = img.height;

    // Posicionar el canvas sobre la imagen
    const rect = img.getBoundingClientRect();
    canvas.style.width = img.width + 'px';
    canvas.style.height = img.height + 'px';

    // Redibujar anotaciones existentes
    redrawAnnotations();
}

// Eventos del canvas
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

// Soporte táctil
canvas.addEventListener('touchstart', handleTouchStart);
canvas.addEventListener('touchmove', handleTouchMove);
canvas.addEventListener('touchend', handleTouchEnd);

function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchEnd(e) {
    e.preventDefault();
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
}

function getCanvasCoordinates(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    return {
        x: (e.clientX - rect.left) * scaleX,
        y: (e.clientY - rect.top) * scaleY
    };
}

function startDrawing(e) {
    if (currentTool === 'text') {
        const coords = getCanvasCoordinates(e);
        textPosition = coords;
        showTextModal();
        return;
    }

    isDrawing = true;
    const coords = getCanvasCoordinates(e);

    // Para formas geométricas, guardar punto inicial
    if (['arrow', 'line', 'circle', 'rectangle'].includes(currentTool)) {
        shapeStartPoint = coords;
        currentStroke = {
            tool: currentTool,
            color: currentColor,
            size: currentSize,
            startPoint: coords,
            endPoint: coords
        };
    } else {
        currentStroke = {
            tool: currentTool,
            color: currentColor,
            size: currentSize,
            points: [coords]
        };
    }
}

function draw(e) {
    if (!isDrawing || currentTool === 'text') return;

    const coords = getCanvasCoordinates(e);

    // Para formas geométricas, actualizar endpoint y mostrar preview
    if (['arrow', 'line', 'circle', 'rectangle'].includes(currentTool)) {
        currentStroke.endPoint = coords;

        // Redibujar todo para mostrar preview
        redrawAnnotations();
        drawShape(currentStroke, true); // true = es preview
    } else {
        currentStroke.points.push(coords);
        drawStroke(currentStroke);
    }
}

function stopDrawing() {
    if (!isDrawing) return;

    isDrawing = false;

    // Para formas geométricas
    if (currentStroke && ['arrow', 'line', 'circle', 'rectangle'].includes(currentStroke.tool)) {
        if (shapeStartPoint) {
            strokes.push(currentStroke);
            shapeStartPoint = null;
            currentStroke = null;
            redrawAnnotations();
            autoSaveAnnotations();
        }
    }
    // Para trazos libres
    else if (currentStroke && currentStroke.points && currentStroke.points.length > 0) {
        strokes.push(currentStroke);
        currentStroke = null;
        autoSaveAnnotations();
    }
}

function drawStroke(stroke) {
    if (!stroke || stroke.points.length === 0) return;

    ctx.beginPath();
    ctx.strokeStyle = stroke.color;
    ctx.lineWidth = stroke.size;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    if (stroke.tool === 'eraser') {
        ctx.globalCompositeOperation = 'destination-out';
        ctx.lineWidth = stroke.size * 3;
    } else if (stroke.tool === 'marker') {
        ctx.globalAlpha = 0.5;
    } else {
        ctx.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = 1.0;
    }

    ctx.moveTo(stroke.points[0].x, stroke.points[0].y);

    for (let i = 1; i < stroke.points.length; i++) {
        ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
    }

    ctx.stroke();
    ctx.globalCompositeOperation = 'source-over';
    ctx.globalAlpha = 1.0;
}

function drawText(text, position, color, size) {
    ctx.font = `${size * 5}px Arial`;
    ctx.fillStyle = color;
    ctx.fillText(text, position.x, position.y);
}

function drawShape(shape, isPreview = false) {
    if (!shape || !shape.startPoint || !shape.endPoint) return;

    ctx.strokeStyle = shape.color;
    ctx.lineWidth = shape.size;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    if (isPreview) {
        ctx.setLineDash([5, 5]); // Línea punteada para preview
    } else {
        ctx.setLineDash([]);
    }

    const start = shape.startPoint;
    const end = shape.endPoint;

    switch (shape.tool) {
        case 'line':
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();
            break;

        case 'arrow':
            // Dibujar línea
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();

            // Dibujar punta de flecha
            const angle = Math.atan2(end.y - start.y, end.x - start.x);
            const headLength = 15 + (shape.size * 2);

            ctx.beginPath();
            ctx.moveTo(end.x, end.y);
            ctx.lineTo(
                end.x - headLength * Math.cos(angle - Math.PI / 6),
                end.y - headLength * Math.sin(angle - Math.PI / 6)
            );
            ctx.moveTo(end.x, end.y);
            ctx.lineTo(
                end.x - headLength * Math.cos(angle + Math.PI / 6),
                end.y - headLength * Math.sin(angle + Math.PI / 6)
            );
            ctx.stroke();
            break;

        case 'circle':
            const radiusX = Math.abs(end.x - start.x) / 2;
            const radiusY = Math.abs(end.y - start.y) / 2;
            const centerX = (start.x + end.x) / 2;
            const centerY = (start.y + end.y) / 2;

            ctx.beginPath();
            ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
            ctx.stroke();
            break;

        case 'rectangle':
            const width = end.x - start.x;
            const height = end.y - start.y;

            ctx.beginPath();
            ctx.rect(start.x, start.y, width, height);
            ctx.stroke();
            break;
    }

    ctx.setLineDash([]); // Restaurar línea sólida
}

function redrawAnnotations() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    strokes.forEach(stroke => {
        if (stroke.type === 'text') {
            drawText(stroke.text, stroke.position, stroke.color, stroke.size);
        } else if (['arrow', 'line', 'circle', 'rectangle'].includes(stroke.tool)) {
            drawShape(stroke);
        } else {
            drawStroke(stroke);
        }
    });
}

// Herramientas
document.getElementById('tool-pen').addEventListener('click', () => setTool('pen'));
document.getElementById('tool-marker').addEventListener('click', () => setTool('marker'));
document.getElementById('tool-text').addEventListener('click', () => setTool('text'));
document.getElementById('tool-eraser').addEventListener('click', () => setTool('eraser'));
document.getElementById('tool-arrow').addEventListener('click', () => setTool('arrow'));
document.getElementById('tool-line').addEventListener('click', () => setTool('line'));
document.getElementById('tool-circle').addEventListener('click', () => setTool('circle'));
document.getElementById('tool-rectangle').addEventListener('click', () => setTool('rectangle'));

function setTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tool-' + tool).classList.add('active');

    canvas.style.cursor = tool === 'eraser' ? 'not-allowed' : 'crosshair';
}

// Colores
document.querySelectorAll('.color-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        currentColor = btn.dataset.color;
        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Tamaños
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        currentSize = parseInt(btn.dataset.size);
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Deshacer
document.getElementById('tool-undo').addEventListener('click', () => {
    if (strokes.length > 0) {
        strokes.pop();
        redrawAnnotations();
        autoSaveAnnotations();
    }
});

// Limpiar todo
document.getElementById('tool-clear').addEventListener('click', () => {
    if (confirm('¿Estás seguro de que quieres borrar todas las anotaciones de esta diapositiva?')) {
        strokes = [];
        redrawAnnotations();
        autoSaveAnnotations();
    }
});

// Guardar
document.getElementById('tool-save').addEventListener('click', saveAnnotations);

// Modal de texto
function showTextModal() {
    document.getElementById('text-modal').classList.add('show');
    document.getElementById('text-input').value = '';
    document.getElementById('text-input').focus();
}

function hideTextModal() {
    document.getElementById('text-modal').classList.remove('show');
    textPosition = null;
}

document.getElementById('text-ok').addEventListener('click', () => {
    const text = document.getElementById('text-input').value;
    if (text && textPosition) {
        const textStroke = {
            type: 'text',
            text: text,
            position: textPosition,
            color: currentColor,
            size: currentSize
        };
        strokes.push(textStroke);
        drawText(text, textPosition, currentColor, currentSize);
        autoSaveAnnotations();
    }
    hideTextModal();
});

document.getElementById('text-cancel').addEventListener('click', hideTextModal);

// Auto-guardar cada 5 segundos
let saveTimeout;
function autoSaveAnnotations() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(saveAnnotations, 5000);
}

// ========== SISTEMA DE ALMACENAMIENTO LOCAL (Solo Cliente) ==========

// Claves para localStorage - Las anotaciones/notas son SOLO del cliente
const STORAGE_PREFIX = `simplementi_${codigo}_${participanteId}_`;
const ANNOTATIONS_KEY = STORAGE_PREFIX + 'annotations';
const NOTES_KEY = STORAGE_PREFIX + 'notes';

// Funciones auxiliares para localStorage
function getLocalStorageData(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('Error al leer localStorage:', e);
        return null;
    }
}

function setLocalStorageData(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.error('Error al escribir en localStorage:', e);
    }
}

// Guardar anotaciones SOLO en localStorage (no servidor)
function saveAnnotations() {
    const localData = getLocalStorageData(ANNOTATIONS_KEY) || {};
    localData[slideNumber] = strokes;
    setLocalStorageData(ANNOTATIONS_KEY, localData);
    console.log('Anotaciones guardadas localmente');
}

// Cargar anotaciones SOLO desde localStorage
function loadAnnotations() {
    const localData = getLocalStorageData(ANNOTATIONS_KEY);
    if (localData && localData[slideNumber]) {
        strokes = localData[slideNumber];
        redrawAnnotations();
        console.log('Anotaciones cargadas desde localStorage');
    }
}

// Verificar cambios en la secuencia (modo inteligente con sincronización)
function checkSequenceUpdate() {
    const loadingIndicator = document.getElementById('loading-indicator');
    loadingIndicator.classList.add('show');

    fetch(serverUrl + 'api/get_sequence_index.php?codigo=' + codigo)
        .then(response => response.json())
        .then(data => {
            loadingIndicator.classList.remove('show');

            if (data.success && data.sequence_index !== undefined) {
                const newIndex = parseInt(data.sequence_index);
                presenterSequenceIndex = newIndex;

                // Actualizar banner si está desincronizado
                updateSyncStatus();

                // Solo recargar si está sincronizado Y el índice cambió
                if (isSynced && newIndex !== currentSequenceIndex) {
                    // Guardar antes de cambiar de slide
                    saveAnnotations();

                    const syncMessage = document.getElementById('sync-message');
                    syncMessage.classList.add('show');

                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            }
        })
        .catch(error => {
            console.error('Error al verificar actualización:', error);
            loadingIndicator.classList.remove('show');
        });
}

// Actualizar estado de sincronización visual
function updateSyncStatus() {
    const desyncBanner = document.getElementById('desync-banner');
    const syncToggle = document.getElementById('sync-toggle');

    if (!isSynced && presenterSequenceIndex !== viewingSequenceIndex) {
        // Mostrar banner de desincronización
        desyncBanner.classList.add('show');
        document.getElementById('current-viewing').textContent = getSlideNumberFromIndex(viewingSequenceIndex);
        document.getElementById('presenter-at').textContent = getSlideNumberFromIndex(presenterSequenceIndex);

        syncToggle.classList.remove('synced');
        syncToggle.innerHTML = '<i class="fas fa-unlink"></i> Desincronizado';
    } else {
        desyncBanner.classList.remove('show');
        syncToggle.classList.add('synced');
        syncToggle.innerHTML = '<i class="fas fa-sync"></i> Sincronizado';
    }

    // Actualizar botones de navegación
    updateNavigationButtons();
}

// Obtener número de slide desde índice de secuencia
function getSlideNumberFromIndex(index) {
    if (index < 0 || index >= slideSequence.length) return '?';
    const item = slideSequence[index];
    return item.type === 'slide' ? item.number : `Pregunta ${item.id}`;
}

// Actualizar estado de botones de navegación
function updateNavigationButtons() {
    const prevBtn = document.getElementById('nav-prev');
    const nextBtn = document.getElementById('nav-next');

    // Puede retroceder si no está en el primer slide
    prevBtn.disabled = viewingSequenceIndex <= 0;

    // Puede avanzar solo hasta donde está el presentador
    nextBtn.disabled = viewingSequenceIndex >= presenterSequenceIndex;
}

// Navegación manual
function navigateTo(direction) {
    const targetIndex = viewingSequenceIndex + direction;

    // Validar límites
    if (targetIndex < 0 || targetIndex >= slideSequence.length) return;
    if (targetIndex > presenterSequenceIndex) return; // No puede adelantarse al profesor

    // Desincroni zar automáticamente
    if (isSynced) {
        isSynced = false;
    }

    // Guardar anotaciones actuales
    saveAnnotations();

    // Navegar al nuevo slide
    viewingSequenceIndex = targetIndex;
    const targetItem = slideSequence[targetIndex];

    // Construir URL para navegar
    let url = `participante.php?codigo=${codigo}`;
    window.location.href = url;
}

// Toggle de sincronización
document.getElementById('sync-toggle').addEventListener('click', () => {
    if (isSynced) {
        // Desincronizar
        isSynced = false;
        viewingSequenceIndex = currentSequenceIndex;
    } else {
        // Resincronizar - volver a seguir al presentador
        isSynced = true;

        // Si el presentador está en un slide diferente, navegar allí
        if (presenterSequenceIndex !== currentSequenceIndex) {
            saveAnnotations();
            window.location.reload();
        }
    }

    updateSyncStatus();
});

// Botones de navegación
document.getElementById('nav-prev').addEventListener('click', () => navigateTo(-1));
document.getElementById('nav-next').addEventListener('click', () => navigateTo(1));

// ========== INTERACCIONES ==========

// Levantar mano
document.getElementById('raise-hand-btn').addEventListener('click', () => {
    handRaised = !handRaised;
    const btn = document.getElementById('raise-hand-btn');

    if (handRaised) {
        btn.classList.add('active');
    } else {
        btn.classList.remove('active');
    }

    // Enviar estado al servidor
    sendInteraction('raise_hand', { raised: handRaised });
});

// Hacer pregunta
document.getElementById('ask-question-btn').addEventListener('click', () => {
    document.getElementById('question-modal').classList.add('show');
    document.getElementById('question-input').value = '';
    document.getElementById('question-input').focus();
});

document.getElementById('question-cancel').addEventListener('click', () => {
    document.getElementById('question-modal').classList.remove('show');
});

document.getElementById('question-send').addEventListener('click', () => {
    const question = document.getElementById('question-input').value.trim();
    const anonymous = document.getElementById('question-anonymous').checked;

    if (question) {
        sendInteraction('question', {
            question: question,
            anonymous: anonymous,
            slide_number: slideNumber
        });

        document.getElementById('question-modal').classList.remove('show');

        // Feedback visual
        const btn = document.getElementById('ask-question-btn');
        btn.style.background = '#1cc88a';
        setTimeout(() => {
            btn.style.background = '';
        }, 2000);
    }
});

// Medidor de comprensión
document.getElementById('understanding-btn').addEventListener('click', () => {
    // Ciclar entre: null -> confused -> understood -> null
    if (currentUnderstanding === null) {
        currentUnderstanding = 'confused';
    } else if (currentUnderstanding === 'confused') {
        currentUnderstanding = 'understood';
    } else {
        currentUnderstanding = null;
    }

    const btn = document.getElementById('understanding-btn');

    if (currentUnderstanding === 'confused') {
        btn.style.background = '#e74c3c';
        btn.innerHTML = '<i class="fas fa-frown"></i>';
    } else if (currentUnderstanding === 'understood') {
        btn.style.background = '#1cc88a';
        btn.innerHTML = '<i class="fas fa-smile"></i>';
    } else {
        btn.style.background = '';
        btn.innerHTML = '<i class="fas fa-brain"></i>';
    }

    sendInteraction('understanding', {
        level: currentUnderstanding,
        slide_number: slideNumber
    });
});

// Reacciones rápidas
document.getElementById('reaction-btn').addEventListener('click', () => {
    const picker = document.getElementById('reaction-picker');
    picker.classList.toggle('show');
});

document.querySelectorAll('.reaction-option').forEach(option => {
    option.addEventListener('click', () => {
        const reaction = option.dataset.reaction;

        sendInteraction('reaction', {
            reaction: reaction,
            slide_number: slideNumber
        });

        // Ocultar picker
        document.getElementById('reaction-picker').classList.remove('show');

        // Feedback visual
        const btn = document.getElementById('reaction-btn');
        btn.innerHTML = reaction;
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-smile"></i>';
        }, 3000);
    });
});

// Enviar interacción al servidor
function sendInteraction(type, data) {
    const interactionData = {
        codigo_sesion: codigo,
        id_participante: participanteId,
        nombre_participante: participanteNombre,
        type: type,
        data: data,
        timestamp: new Date().toISOString()
    };

    fetch(serverUrl + 'api/guardar_interaccion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(interactionData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Interacción enviada:', type);
        } else {
            console.error('Error al enviar interacción:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al enviar interacción:', error);
    });
}

// Precargar slides
function preloadAllSlides() {
    const slides = <?php echo json_encode($test_data['pdf_images']); ?>;

    slides.forEach((slide, index) => {
        const img = new Image();
        img.src = slide.path;
    });
}

// Inicializar
window.addEventListener('load', function() {
    initCanvas();
    loadAnnotations();
    preloadAllSlides();
    updateSyncStatus(); // Inicializar estado de sincronización
    console.log('Visor con anotaciones y navegación libre iniciado');
});

// Redimensionar canvas al cambiar tamaño de ventana
window.addEventListener('resize', resizeCanvas);

// Verificar cambios cada 3 segundos (reducido para disminuir carga del servidor)
setInterval(checkSequenceUpdate, 3000);

// Soporte para fullscreen
document.getElementById('slide-container').addEventListener('dblclick', function() {
    const elem = document.documentElement;

    if (!document.fullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
});

// Guardar antes de salir
window.addEventListener('beforeunload', () => {
    saveAnnotations();
    saveNotes();
});

// ========== SISTEMA DE NOTAS ==========

let notesTimeout = null;
let currentNotes = '';
let notesSaved = true;

// Toggle del panel de notas
function toggleNotes() {
    const panel = document.getElementById('notes-panel');
    const chevron = document.getElementById('notes-chevron');

    panel.classList.toggle('expanded');

    if (panel.classList.contains('expanded')) {
        chevron.classList.remove('fa-chevron-up');
        chevron.classList.add('fa-chevron-down');
    } else {
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
    }
}

// Actualizar contador de caracteres
function updateCharCount() {
    const textarea = document.getElementById('notes-textarea');
    const charCount = document.getElementById('notes-char-count');
    const count = textarea.value.length;

    charCount.textContent = count + ' caracteres';
}

// Guardar notas SOLO en localStorage (no servidor)
function saveNotes() {
    const textarea = document.getElementById('notes-textarea');
    const statusDiv = document.getElementById('notes-status');

    const localData = getLocalStorageData(NOTES_KEY) || {};
    localData[slideNumber] = {
        contenido: textarea.value,
        timestamp: new Date().toISOString()
    };
    setLocalStorageData(NOTES_KEY, localData);

    statusDiv.textContent = 'Guardado ✓';
    statusDiv.style.color = '#1cc88a';
    notesSaved = true;

    setTimeout(() => {
        if (notesSaved) {
            statusDiv.textContent = '';
        }
    }, 2000);

    console.log('Notas guardadas localmente');
}

// Manejar cambios en el textarea con debouncing
function handleNotesChange() {
    notesSaved = false;
    const statusDiv = document.getElementById('notes-status');
    statusDiv.textContent = 'Sin guardar';
    statusDiv.style.color = '#858796';

    updateCharCount();

    // Cancelar timeout anterior
    if (notesTimeout) {
        clearTimeout(notesTimeout);
    }

    // Guardar después de 500ms de inactividad (más rápido ahora que es local)
    notesTimeout = setTimeout(() => {
        saveNotes();
    }, 500);
}

// Cargar notas SOLO desde localStorage
function loadNotes() {
    const localData = getLocalStorageData(NOTES_KEY);
    if (localData && localData[slideNumber]) {
        const textarea = document.getElementById('notes-textarea');
        textarea.value = localData[slideNumber].contenido;
        currentNotes = localData[slideNumber].contenido;
        updateCharCount();
        console.log('Notas cargadas desde localStorage');
    }
}

// Event listener para el textarea
document.getElementById('notes-textarea').addEventListener('input', handleNotesChange);

// Cargar notas al iniciar
setTimeout(() => {
    loadNotes();
}, 500);

// ========== SISTEMA DE MARCADORES ==========

const BOOKMARKS_KEY = STORAGE_PREFIX + 'bookmarks';
let selectedCategory = 'important'; // Categoría por defecto

// Obtener marcadores
function getBookmarks() {
    return getLocalStorageData(BOOKMARKS_KEY) || {};
}

// Guardar marcadores
function saveBookmarks(bookmarks) {
    setLocalStorageData(BOOKMARKS_KEY, bookmarks);
    updateBookmarksUI();
}

// Verificar si el slide actual está marcado
function isSlideBookmarked() {
    const bookmarks = getBookmarks();
    return bookmarks.hasOwnProperty(slideNumber);
}

// Actualizar UI del botón de marcador
function updateBookmarkButton() {
    const btn = document.getElementById('bookmark-btn');
    const icon = btn.querySelector('i');

    if (isSlideBookmarked()) {
        btn.classList.add('active');
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.title = 'Quitar marcador';
    } else {
        btn.classList.remove('active');
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.title = 'Marcar este slide';
    }
}

// Toggle de marcador (abrir modal si no está marcado, quitar si está marcado)
function toggleBookmark() {
    if (isSlideBookmarked()) {
        // Si ya está marcado, quitarlo
        removeBookmark(slideNumber);
    } else {
        // Si no está marcado, abrir modal para agregar
        openBookmarkModal();
    }
}

// Abrir modal de marcador
function openBookmarkModal() {
    const modal = document.getElementById('bookmark-modal');
    const noteInput = document.getElementById('bookmark-note-input');

    // Si ya existe el marcador, cargar sus datos
    const bookmarks = getBookmarks();
    if (bookmarks[slideNumber]) {
        selectedCategory = bookmarks[slideNumber].category;
        noteInput.value = bookmarks[slideNumber].note || '';
    } else {
        selectedCategory = 'important';
        noteInput.value = '';
    }

    // Actualizar selección de categoría
    document.querySelectorAll('.category-option').forEach(opt => {
        opt.classList.remove('selected');
        if (opt.dataset.category === selectedCategory) {
            opt.classList.add('selected');
        }
    });

    modal.classList.add('show');
}

// Cerrar modal de marcador
function closeBookmarkModal() {
    document.getElementById('bookmark-modal').classList.remove('show');
}

// Guardar marcador
function saveBookmark() {
    const bookmarks = getBookmarks();
    const noteInput = document.getElementById('bookmark-note-input');

    bookmarks[slideNumber] = {
        slide: slideNumber,
        category: selectedCategory,
        note: noteInput.value.trim(),
        timestamp: new Date().toISOString()
    };

    saveBookmarks(bookmarks);
    updateBookmarkButton();
    closeBookmarkModal();

    console.log('Marcador guardado para slide', slideNumber);
}

// Eliminar marcador
function removeBookmark(slideNum) {
    const bookmarks = getBookmarks();
    delete bookmarks[slideNum];
    saveBookmarks(bookmarks);
    updateBookmarkButton();

    console.log('Marcador eliminado para slide', slideNum);
}

// Toggle panel de marcadores
function toggleBookmarksPanel() {
    const panel = document.getElementById('bookmarks-panel');
    panel.classList.toggle('open');
}

// Renderizar lista de marcadores
function renderBookmarksList() {
    const bookmarks = getBookmarks();
    const list = document.getElementById('bookmarks-list');
    const count = Object.keys(bookmarks).length;

    // Actualizar contador
    const badge = document.getElementById('bookmarks-count');
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }

    // Si no hay marcadores, mostrar mensaje vacío
    if (count === 0) {
        list.innerHTML = `
            <div class="empty-bookmarks">
                <i class="fas fa-bookmark"></i>
                <p>No tienes marcadores aún</p>
                <small>Marca slides importantes con la estrella ⭐</small>
            </div>
        `;
        return;
    }

    // Convertir a array y ordenar por número de slide
    const bookmarksArray = Object.values(bookmarks).sort((a, b) => a.slide - b.slide);

    // Renderizar cada marcador
    list.innerHTML = bookmarksArray.map(bookmark => {
        const categoryLabels = {
            important: 'Importante',
            review: 'Revisar',
            doubt: 'Duda',
            understood: 'Entendido'
        };

        return `
            <div class="bookmark-item category-${bookmark.category}" onclick="navigateToBookmark(${bookmark.slide})">
                <div class="bookmark-header">
                    <div class="bookmark-slide-number">
                        <i class="fas fa-file-powerpoint me-2"></i>Slide ${bookmark.slide}
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="bookmark-category ${bookmark.category}">
                            ${categoryLabels[bookmark.category]}
                        </span>
                        <button class="bookmark-delete" onclick="event.stopPropagation(); removeBookmark(${bookmark.slide})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${bookmark.note ? `<div class="bookmark-note">"${bookmark.note}"</div>` : ''}
                <small style="color: #999; font-size: 12px; margin-top: 5px; display: block;">
                    <i class="far fa-clock me-1"></i>${formatBookmarkDate(bookmark.timestamp)}
                </small>
            </div>
        `;
    }).join('');
}

// Navegar a un slide marcado
function navigateToBookmark(targetSlide) {
    if (targetSlide === slideNumber) {
        // Ya estamos en este slide
        toggleBookmarksPanel();
        return;
    }

    // Guardar anotaciones y notas actuales
    saveAnnotations();
    saveNotes();

    // Si el slide objetivo está más adelante que el presentador, no permitir
    const targetIndex = slideSequence.findIndex(item => item.type === 'slide' && item.number === targetSlide);

    if (targetIndex > presenterSequenceIndex) {
        alert('No puedes navegar a un slide que el presentador aún no ha mostrado.');
        return;
    }

    // Desincronizar si está sincronizado
    if (isSynced) {
        isSynced = false;
    }

    // Navegar al slide
    viewingSequenceIndex = targetIndex;
    window.location.href = `participante.php?codigo=${codigo}`;
}

// Formatear fecha del marcador
function formatBookmarkDate(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} min`;
    if (hours < 24) return `Hace ${hours} h`;
    return `Hace ${days} día${days > 1 ? 's' : ''}`;
}

// Actualizar toda la UI de marcadores
function updateBookmarksUI() {
    updateBookmarkButton();
    renderBookmarksList();
}

// Event listeners para marcadores
document.getElementById('bookmark-btn').addEventListener('click', toggleBookmark);
document.getElementById('bookmarks-panel-btn').addEventListener('click', toggleBookmarksPanel);

document.getElementById('bookmark-save').addEventListener('click', saveBookmark);
document.getElementById('bookmark-cancel').addEventListener('click', closeBookmarkModal);

// Selector de categoría
document.querySelectorAll('.category-option').forEach(option => {
    option.addEventListener('click', () => {
        document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        selectedCategory = option.dataset.category;
    });
});

// Inicializar UI de marcadores
updateBookmarksUI();

// ========== MODO OSCURO ==========

let darkModeEnabled = false;

// Cargar preferencia de modo oscuro
function loadDarkModePreference() {
    const savedPreference = localStorage.getItem('darkMode');
    if (savedPreference === 'true') {
        enableDarkMode();
    }
}

// Activar modo oscuro
function enableDarkMode() {
    document.body.classList.add('dark-mode');
    darkModeEnabled = true;
    localStorage.setItem('darkMode', 'true');

    const icon = document.querySelector('#dark-mode-toggle i');
    icon.classList.remove('fa-moon');
    icon.classList.add('fa-sun');
}

// Desactivar modo oscuro
function disableDarkMode() {
    document.body.classList.remove('dark-mode');
    darkModeEnabled = false;
    localStorage.setItem('darkMode', 'false');

    const icon = document.querySelector('#dark-mode-toggle i');
    icon.classList.remove('fa-sun');
    icon.classList.add('fa-moon');
}

// Toggle modo oscuro
function toggleDarkMode() {
    if (darkModeEnabled) {
        disableDarkMode();
    } else {
        enableDarkMode();
    }
}

// Event listener para el botón de modo oscuro
document.getElementById('dark-mode-toggle').addEventListener('click', toggleDarkMode);

// Cargar preferencia al iniciar
loadDarkModePreference();

// ========== EXPORTACIÓN DE PDF EN EL CLIENTE ==========

async function exportPDF() {
    const { jsPDF } = window.jspdf;

    // Mostrar modal de progreso
    const modal = document.getElementById('pdf-export-modal');
    modal.style.display = 'flex';

    const progressText = document.getElementById('pdf-progress-text');
    const progressBar = document.getElementById('pdf-progress-bar');
    const progressDetail = document.getElementById('pdf-progress-detail');

    try {
        // Paso 1: Obtener resultados de evaluación del servidor
        progressText.textContent = 'Obteniendo resultados de evaluación...';
        progressBar.style.width = '10%';

        const resultsResponse = await fetch(serverUrl + `api/obtener_resultados_participante.php?codigo=${codigo}&participante=${participanteId}`);
        const resultsData = await resultsResponse.json();

        if (!resultsData.success) {
            throw new Error('No se pudieron obtener los resultados');
        }

        // Paso 2: Obtener todas las slides y anotaciones/notas locales
        progressText.textContent = 'Cargando anotaciones y notas...';
        progressBar.style.width = '20%';

        const allSlides = <?php echo json_encode($test_data['pdf_images']); ?>;
        const annotations = getLocalStorageData(ANNOTATIONS_KEY) || {};
        const notes = getLocalStorageData(NOTES_KEY) || {};

        // Crear PDF
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        // Paso 3: Procesar cada slide
        for (let i = 0; i < allSlides.length; i++) {
            const slideNum = i + 1;
            const slide = allSlides[i];

            progressText.textContent = `Procesando slide ${slideNum} de ${allSlides.length}...`;
            const progress = 20 + ((i / allSlides.length) * 50);
            progressBar.style.width = progress + '%';
            progressDetail.textContent = `Renderizando anotaciones del slide ${slideNum}...`;

            if (i > 0) pdf.addPage();

            // Crear canvas temporal para renderizar slide con anotaciones
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            // Cargar imagen del slide
            const img = await loadImage(slide.path);
            tempCanvas.width = img.width;
            tempCanvas.height = img.height;

            // Dibujar imagen
            tempCtx.drawImage(img, 0, 0);

            // Dibujar anotaciones si existen
            if (annotations[slideNum]) {
                const slideAnnotations = annotations[slideNum];
                for (const stroke of slideAnnotations) {
                    if (stroke.type === 'text') {
                        // Dibujar texto
                        tempCtx.fillStyle = stroke.color;
                        tempCtx.font = `${stroke.size * 3}px Arial`;
                        tempCtx.fillText(stroke.text, stroke.position.x, stroke.position.y);
                    } else if (stroke.tool === 'arrow' || stroke.tool === 'line' || stroke.tool === 'circle' || stroke.tool === 'rectangle') {
                        // Dibujar forma geométrica
                        drawShapeOnCanvas(tempCtx, stroke);
                    } else {
                        // Dibujar trazo
                        if (stroke.points && stroke.points.length > 1) {
                            tempCtx.strokeStyle = stroke.color;
                            tempCtx.lineWidth = stroke.size;
                            tempCtx.globalAlpha = stroke.tool === 'marker' ? 0.5 : 1;

                            tempCtx.beginPath();
                            tempCtx.moveTo(stroke.points[0].x, stroke.points[0].y);
                            for (let j = 1; j < stroke.points.length; j++) {
                                tempCtx.lineTo(stroke.points[j].x, stroke.points[j].y);
                            }
                            tempCtx.stroke();
                            tempCtx.globalAlpha = 1;
                        }
                    }
                }
            }

            // Convertir canvas a imagen y agregar al PDF
            const imgData = tempCanvas.toDataURL('image/png');
            pdf.addImage(imgData, 'PNG', 10, 10, pageWidth - 20, pageHeight - 40);

            // Agregar notas si existen
            if (notes[slideNum] && notes[slideNum].contenido) {
                pdf.setFontSize(9);
                pdf.setTextColor(78, 115, 223);
                pdf.text('Notas:', 10, pageHeight - 20);

                pdf.setFontSize(8);
                pdf.setTextColor(50, 50, 50);
                const notesText = notes[slideNum].contenido;
                const splitText = pdf.splitTextToSize(notesText, pageWidth - 20);
                pdf.text(splitText, 10, pageHeight - 15);
            }

            // Número de slide
            pdf.setFontSize(10);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`Slide ${slideNum} / ${allSlides.length}`, pageWidth - 30, pageHeight - 5);
        }

        // Paso 3.5: Agregar página de marcadores (si existen)
        const bookmarks = getBookmarks();
        const bookmarksCount = Object.keys(bookmarks).length;

        if (bookmarksCount > 0) {
            progressText.textContent = 'Agregando marcadores...';
            progressBar.style.width = '75%';

            pdf.addPage();

            // Título
            pdf.setFontSize(18);
            pdf.setTextColor(255, 193, 7);
            pdf.text('Mis Marcadores', pageWidth / 2, 20, { align: 'center' });

            pdf.setFontSize(12);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`${bookmarksCount} slide${bookmarksCount > 1 ? 's' : ''} marcado${bookmarksCount > 1 ? 's' : ''}`, pageWidth / 2, 30, { align: 'center' });

            let y = 45;
            const categoryLabels = {
                important: '🔴 Importante',
                review: '📝 Revisar',
                doubt: '❓ Duda',
                understood: '✅ Entendido'
            };

            const categoryColors = {
                important: [231, 74, 59],
                review: [246, 194, 62],
                doubt: [54, 185, 204],
                understood: [28, 200, 138]
            };

            // Ordenar marcadores por slide
            const bookmarksArray = Object.values(bookmarks).sort((a, b) => a.slide - b.slide);

            for (const bookmark of bookmarksArray) {
                if (y > pageHeight - 30) {
                    pdf.addPage();
                    y = 20;
                }

                // Recuadro del marcador
                pdf.setDrawColor(...categoryColors[bookmark.category]);
                pdf.setLineWidth(0.5);
                pdf.rect(15, y - 5, pageWidth - 30, 25);

                // Número de slide
                pdf.setFontSize(14);
                pdf.setTextColor(...categoryColors[bookmark.category]);
                pdf.text(`Slide ${bookmark.slide}`, 20, y + 2);

                // Categoría
                pdf.setFontSize(10);
                pdf.text(categoryLabels[bookmark.category], 20, y + 9);

                // Nota (si existe)
                if (bookmark.note) {
                    pdf.setFontSize(9);
                    pdf.setTextColor(50, 50, 50);
                    const noteText = pdf.splitTextToSize(bookmark.note, pageWidth - 50);
                    pdf.text(noteText, 20, y + 16);
                }

                y += 30;
            }
        }

        // Paso 4: Agregar página de resultados
        progressText.textContent = 'Agregando resultados de evaluación...';
        progressBar.style.width = '80%';

        pdf.addPage();

        // Título
        pdf.setFontSize(18);
        pdf.setTextColor(78, 115, 223);
        pdf.text('Resultados de Evaluación', pageWidth / 2, 20, { align: 'center' });

        pdf.setFontSize(14);
        pdf.setTextColor(50, 50, 50);
        pdf.text(resultsData.presentacion.titulo, pageWidth / 2, 30, { align: 'center' });

        pdf.setFontSize(12);
        pdf.text(`Participante: ${resultsData.participante.nombre}`, pageWidth / 2, 40, { align: 'center' });

        // Estadísticas
        let y = 55;
        pdf.setFontSize(11);
        pdf.setTextColor(78, 115, 223);
        pdf.text('Estadísticas:', 15, y);

        y += 10;
        pdf.setFontSize(10);
        pdf.setTextColor(50, 50, 50);
        pdf.text(`Puntaje total: ${resultsData.estadisticas.puntaje} puntos`, 20, y);

        y += 7;
        pdf.text(`Porcentaje de acierto: ${resultsData.estadisticas.porcentaje_acierto}%`, 20, y);

        y += 7;
        pdf.setTextColor(28, 200, 138);
        pdf.text(`Respuestas correctas: ${resultsData.estadisticas.total_correctas}`, 20, y);

        y += 7;
        pdf.setTextColor(231, 74, 59);
        pdf.text(`Respuestas incorrectas: ${resultsData.estadisticas.total_incorrectas}`, 20, y);

        y += 7;
        pdf.setTextColor(50, 50, 50);
        pdf.text(`Tiempo promedio: ${resultsData.estadisticas.tiempo_promedio} segundos`, 20, y);

        // Detalle de preguntas (solo las primeras para no saturar)
        y += 15;
        pdf.setFontSize(11);
        pdf.setTextColor(78, 115, 223);
        pdf.text('Detalle de Respuestas:', 15, y);

        y += 10;
        pdf.setFontSize(9);

        const maxQuestions = Math.min(resultsData.preguntas.length, 15); // Máximo 15 preguntas en resumen
        for (let i = 0; i < maxQuestions; i++) {
            const q = resultsData.preguntas[i];

            if (y > pageHeight - 20) {
                pdf.addPage();
                y = 20;
            }

            pdf.setTextColor(78, 115, 223);
            pdf.text(`${i + 1}. ${q.pregunta.pregunta.substring(0, 80)}${q.pregunta.pregunta.length > 80 ? '...' : ''}`, 20, y);

            y += 6;
            if (q.respondida) {
                if (q.es_correcta) {
                    pdf.setTextColor(28, 200, 138);
                    pdf.text('✓ Correcta', 25, y);
                } else {
                    pdf.setTextColor(231, 74, 59);
                    pdf.text(`✗ Incorrecta - Respuesta correcta: ${q.pregunta.respuesta_correcta}`, 25, y);
                }
            } else {
                pdf.setTextColor(150, 150, 150);
                pdf.text('No respondida', 25, y);
            }

            y += 8;
        }

        if (resultsData.preguntas.length > maxQuestions) {
            pdf.setFontSize(8);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`... y ${resultsData.preguntas.length - maxQuestions} preguntas más`, 20, y);
        }

        // Pie de página
        progressText.textContent = 'Finalizando PDF...';
        progressBar.style.width = '95%';

        const totalPages = pdf.internal.pages.length - 1;
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(8);
            pdf.setTextColor(150, 150, 150);
            pdf.text(`Generado con SimpleMenti - ${new Date().toLocaleDateString()}`, pageWidth / 2, pageHeight - 5, { align: 'center' });
        }

        // Paso 5: Guardar PDF
        progressText.textContent = '¡Listo! Descargando PDF...';
        progressBar.style.width = '100%';

        const filename = `presentacion_${resultsData.participante.nombre}_${new Date().toISOString().split('T')[0]}.pdf`;
        pdf.save(filename);

        // Cerrar modal después de un momento
        setTimeout(() => {
            modal.style.display = 'none';
            progressBar.style.width = '0%';
        }, 1500);

    } catch (error) {
        console.error('Error al generar PDF:', error);
        progressText.textContent = 'Error al generar el PDF';
        progressDetail.textContent = error.message;
        progressBar.style.width = '100%';
        progressBar.style.background = '#e74a3b';

        setTimeout(() => {
            modal.style.display = 'none';
            progressBar.style.width = '0%';
            progressBar.style.background = 'linear-gradient(to right, #4e73df, #1cc88a)';
        }, 3000);
    }
}

// Función auxiliar para cargar imágenes
function loadImage(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = url;
    });
}

// Función auxiliar para dibujar formas en canvas
function drawShapeOnCanvas(ctx, shape) {
    if (!shape.startPoint || !shape.endPoint) return;

    ctx.strokeStyle = shape.color;
    ctx.lineWidth = shape.size;

    const start = shape.startPoint;
    const end = shape.endPoint;

    switch (shape.tool) {
        case 'arrow':
            // Línea
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();

            // Punta de flecha
            const angle = Math.atan2(end.y - start.y, end.x - start.x);
            const headLength = 15 + (shape.size * 2);

            ctx.beginPath();
            ctx.moveTo(end.x, end.y);
            ctx.lineTo(
                end.x - headLength * Math.cos(angle - Math.PI / 6),
                end.y - headLength * Math.sin(angle - Math.PI / 6)
            );
            ctx.moveTo(end.x, end.y);
            ctx.lineTo(
                end.x - headLength * Math.cos(angle + Math.PI / 6),
                end.y - headLength * Math.sin(angle + Math.PI / 6)
            );
            ctx.stroke();
            break;

        case 'line':
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();
            break;

        case 'circle':
            const centerX = (start.x + end.x) / 2;
            const centerY = (start.y + end.y) / 2;
            const radiusX = Math.abs(end.x - start.x) / 2;
            const radiusY = Math.abs(end.y - start.y) / 2;

            ctx.beginPath();
            ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
            ctx.stroke();
            break;

        case 'rectangle':
            const width = end.x - start.x;
            const height = end.y - start.y;

            ctx.beginPath();
            ctx.rect(start.x, start.y, width, height);
            ctx.stroke();
            break;
    }
}

// Event listener para el botón de exportar PDF
document.getElementById('tool-export-pdf').addEventListener('click', exportPDF);
</script>
