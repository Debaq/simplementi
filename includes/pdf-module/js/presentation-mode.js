// Estado del modo presentación
let PresentationState = {
    active: false,
    currentIndex: 0
};

// Elementos DOM
const presentationElements = {
    previewSequenceBtn: document.getElementById('preview-sequence-btn'),
    viewPresentationBtn: document.getElementById('view-presentation-mode'),
    questionOverlay: document.getElementById('question-overlay'),
    submitAnswerBtn: document.getElementById('submit-answer'),
    currentViewTitle: document.getElementById('current-view-title'),
    sequenceProgress: document.getElementById('sequence-progress'),
    sequenceCurrent: document.getElementById('sequence-current'),
    sequenceTotal: document.getElementById('sequence-total')
};

// Inicializar modo presentación
function initPresentationMode() {
    // Evento para el botón de previsualización
    presentationElements.previewSequenceBtn.addEventListener('click', startPresentation);
    
    // Evento para el botón de modo presentación/edición
    presentationElements.viewPresentationBtn.addEventListener('click', togglePresentationMode);
    
    // Evento para enviar respuesta a pregunta
    presentationElements.submitAnswerBtn.addEventListener('click', function() {
        presentationElements.questionOverlay.classList.remove('active');
        PresentationState.currentIndex++;
        showCurrentSequenceItem();
    });
    
    // Eventos de teclado para navegación en presentación
    document.addEventListener('keydown', handlePresentationKeyboard);
}

// Iniciar modo presentación
function startPresentation() {
    if (window.SequenceEditor.state.items.length === 0) return;
    
    PresentationState.active = true;
    PresentationState.currentIndex = 0;
    
    // Actualizar UI
    presentationElements.currentViewTitle.innerHTML = '<i class="fas fa-play me-2"></i> Modo presentación';
    presentationElements.viewPresentationBtn.innerHTML = '<i class="fas fa-edit me-1"></i> Volver a edición';
    
    // Exponer variable global para otros módulos
    window.presentationActive = true;
    
    // Mostrar el primer ítem
    showCurrentSequenceItem();
}

// Alternar entre modo presentación y edición
function togglePresentationMode() {
    if (window.SequenceEditor.state.items.length === 0) return;
    
    if (PresentationState.active) {
        // Volver a modo edición
        exitPresentationMode();
    } else {
        // Entrar en modo presentación
        startPresentation();
    }
}

// Salir del modo presentación
function exitPresentationMode() {
    PresentationState.active = false;
    window.presentationActive = false;
    
    // Ocultar overlay y progreso
    presentationElements.questionOverlay.classList.remove('active');
    presentationElements.sequenceProgress.style.display = 'none';
    
    // Actualizar UI
    presentationElements.currentViewTitle.innerHTML = '<i class="fas fa-eye me-2"></i> Vista previa';
    presentationElements.viewPresentationBtn.innerHTML = '<i class="fas fa-play me-1"></i> Modo presentación';
    
    // Volver a mostrar la página actual
    if (window.PDFViewer.state.pdfDoc) {
        window.PDFViewer.renderPage(window.PDFViewer.state.pageNum);
    }
}

// Mostrar elemento actual de la secuencia
function showCurrentSequenceItem() {
    const items = window.SequenceEditor.state.items;
    
    if (PresentationState.currentIndex >= items.length) {
        // Fin de la presentación
        alert('Fin de la presentación');
        exitPresentationMode();
        return;
    }
    
    const currentItem = items[PresentationState.currentIndex];
    
    // Actualizar indicador de progreso
    presentationElements.sequenceProgress.style.display = 'block';
    presentationElements.sequenceCurrent.textContent = PresentationState.currentIndex + 1;
    presentationElements.sequenceTotal.textContent = items.length;
    
    // Actualizar título
    presentationElements.currentViewTitle.innerHTML = 
        `<i class="fas fa-play me-2"></i> Modo presentación (${PresentationState.currentIndex + 1}/${items.length})`;
    
    if (currentItem.type === 'slide') {
        // Mostrar diapositiva
        presentationElements.questionOverlay.classList.remove('active');
        window.PDFViewer.state.pageNum = currentItem.number;
        window.PDFViewer.queueRenderPage(currentItem.number);
    } else {
        // Mostrar pregunta
        updateQuestionContent(currentItem);
        presentationElements.questionOverlay.classList.add('active');
    }
}

// Actualizar contenido de la pregunta
function updateQuestionContent(question) {
    // Actualizar título
    const questionTitle = document.querySelector('#question-overlay .card-header h5');
    if (questionTitle) {
        questionTitle.textContent = question.text;
    }
    
    // Actualizar opciones
    if (question.options && question.options.length > 0) {
        const optionLabels = document.querySelectorAll('#question-overlay .form-check-label');
        const optionInputs = document.querySelectorAll('#question-overlay .form-check-input');
        
        optionLabels.forEach((label, idx) => {
            if (idx < question.options.length) {
                label.textContent = question.options[idx];
                if (optionInputs[idx]) {
                    optionInputs[idx].checked = false;
                }
            }
        });
    }
}

// Manejar eventos de teclado en modo presentación
function handlePresentationKeyboard(e) {
    if (!PresentationState.active) return;
    
    if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'Enter') {
        // Si hay una pregunta activa, enviar respuesta
        if (presentationElements.questionOverlay.classList.contains('active')) {
            presentationElements.submitAnswerBtn.click();
        } else {
            // Avanzar a siguiente elemento
            PresentationState.currentIndex++;
            showCurrentSequenceItem();
        }
    } else if (e.key === 'ArrowLeft') {
        // Retroceder
        if (PresentationState.currentIndex > 0) {
            PresentationState.currentIndex--;
            showCurrentSequenceItem();
        }
    } else if (e.key === 'Escape') {
        // Salir de modo presentación
        exitPresentationMode();
    }
}

// Exportar funciones y variables para uso en otros archivos
window.PresentationMode = {
    init: initPresentationMode,
    start: startPresentation,
    exit: exitPresentationMode,
    showCurrentItem: showCurrentSequenceItem,
    state: PresentationState
};