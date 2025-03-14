// Estado de la secuencia
let SequenceState = {
    items: [],
    currentIndex: 0
};

// Elementos DOM
const sequenceElements = {
    sequenceList: document.getElementById('sequence-list'),
    addQuestionBtn: document.getElementById('add-question-btn'),
    insertQuestionModal: new bootstrap.Modal(document.getElementById('insertQuestionModal')),
    insertPositionSelect: document.getElementById('insert-position'),
    insertQuestionBtn: document.getElementById('insert-question-btn')
};

// Inicializar secuencia con todas las diapositivas
function initializeSequence(numPages) {
    SequenceState.items = [];
    
    for (let i = 1; i <= numPages; i++) {
        SequenceState.items.push({
            type: 'slide',
            number: i
        });
    }
    
    renderSequenceList();
    updateInsertPositions();
}

// Renderizar lista de secuencia
function renderSequenceList() {
    sequenceElements.sequenceList.innerHTML = '';
    
    if (SequenceState.items.length === 0) {
        sequenceElements.sequenceList.innerHTML = '<div class="alert alert-secondary text-center">No hay elementos en la secuencia</div>';
        return;
    }
    
    SequenceState.items.forEach((item, index) => {
        const itemEl = document.createElement('div');
        itemEl.className = `sequence-item ${item.type === 'slide' ? 'slide-item' : 'question-item'}`;
        
        if (item.type === 'slide') {
            itemEl.innerHTML = `
                <div>
                    <i class="fas fa-file-powerpoint me-2"></i>
                    <span>Diapositiva ${item.number}</span>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary move-up-btn" data-index="${index}" 
                        ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary move-down-btn" data-index="${index}" 
                        ${index === SequenceState.items.length - 1 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
            `;
        } else {
            itemEl.innerHTML = `
                <div>
                    <i class="fas fa-question-circle me-2"></i>
                    <span>Pregunta: ${item.text}</span>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary move-up-btn" data-index="${index}" 
                        ${index === 0 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary move-down-btn" data-index="${index}" 
                        ${index === SequenceState.items.length - 1 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger remove-btn" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
        
        sequenceElements.sequenceList.appendChild(itemEl);
    });
    
    // Agregar eventos a los botones
    attachSequenceButtonEvents();
}

// Agregar eventos a los botones de la secuencia
function attachSequenceButtonEvents() {
    // Botones de mover arriba
    document.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            if (index > 0) {
                const temp = SequenceState.items[index];
                SequenceState.items[index] = SequenceState.items[index - 1];
                SequenceState.items[index - 1] = temp;
                renderSequenceList();
                updateInsertPositions();
            }
        });
    });
    
    // Botones de mover abajo
    document.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            if (index < SequenceState.items.length - 1) {
                const temp = SequenceState.items[index];
                SequenceState.items[index] = SequenceState.items[index + 1];
                SequenceState.items[index + 1] = temp;
                renderSequenceList();
                updateInsertPositions();
            }
        });
    });
    
    // Botones de eliminar
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            SequenceState.items.splice(index, 1);
            renderSequenceList();
            updateInsertPositions();
        });
    });
}

// Actualizar opciones de posición para insertar preguntas
function updateInsertPositions() {
    sequenceElements.insertPositionSelect.innerHTML = '';
    
    // Opción para insertar al inicio
    const initialOption = document.createElement('option');
    initialOption.value = -1;
    initialOption.textContent = 'Al inicio';
    sequenceElements.insertPositionSelect.appendChild(initialOption);
    
    // Opciones para cada elemento de la secuencia
    SequenceState.items.forEach((item, index) => {
        const option = document.createElement('option');
        option.value = index;
        if (item.type === 'slide') {
            option.textContent = `Después de Diapositiva ${item.number}`;
        } else {
            option.textContent = `Después de Pregunta: ${item.text}`;
        }
        sequenceElements.insertPositionSelect.appendChild(option);
    });
}

// Inicializar eventos del editor de secuencia
function initSequenceEditor() {
    // Evento para el botón de agregar pregunta
    sequenceElements.addQuestionBtn.addEventListener('click', function() {
        updateInsertPositions();
        sequenceElements.insertQuestionModal.show();
    });
    
    // Insertar pregunta en la secuencia
    sequenceElements.insertQuestionBtn.addEventListener('click', function() {
        const questionText = document.getElementById('question-text').value;
        if (!questionText.trim()) {
            alert('El texto de la pregunta es obligatorio');
            return;
        }
        
        // Recoger opciones
        const options = [
            document.getElementById('option-a').value,
            document.getElementById('option-b').value,
            document.getElementById('option-c').value,
            document.getElementById('option-d').value
        ];
        
        // Obtener respuesta correcta
        const correctAnswerRadios = document.getElementsByName('correct-answer');
        let correctIndex = 0;
        for (let i = 0; i < correctAnswerRadios.length; i++) {
            if (correctAnswerRadios[i].checked) {
                correctIndex = parseInt(correctAnswerRadios[i].value);
                break;
            }
        }
        
        const position = parseInt(sequenceElements.insertPositionSelect.value);
        const newQuestion = {
            type: 'question',
            text: questionText,
            options: options,
            correct: correctIndex
        };
        
        if (position === -1) {
            // Insertar al inicio
            SequenceState.items.unshift(newQuestion);
        } else {
            // Insertar después de la posición seleccionada
            SequenceState.items.splice(position + 1, 0, newQuestion);
        }
        
        renderSequenceList();
        updateInsertPositions();
        sequenceElements.insertQuestionModal.hide();
    });
}

// Exportar funciones y variables para uso en otros archivos
window.SequenceEditor = {
    init: initSequenceEditor,
    initializeSequence: initializeSequence,
    renderSequenceList: renderSequenceList,
    updateInsertPositions: updateInsertPositions,
    state: SequenceState
};