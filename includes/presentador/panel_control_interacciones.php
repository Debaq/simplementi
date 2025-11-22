<?php
/**
 * Panel de control de interacciones en tiempo real
 * Muestra: manos levantadas, preguntas, comprensión, reacciones
 */
?>

<style>
#interaction-control-panel {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 380px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    max-height: calc(100vh - 100px);
    display: flex;
    flex-direction: column;
}

#interaction-control-panel.minimized {
    width: auto;
}

#interaction-control-panel.minimized .panel-body {
    display: none;
}

.panel-header {
    background: linear-gradient(to right, #4e73df, #224abe);
    color: white;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
}

.panel-header h5 {
    margin: 0;
    font-size: 16px;
}

.panel-header .btn-minimize {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 20px;
}

.panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.interaction-section {
    margin-bottom: 20px;
}

.section-title {
    font-weight: bold;
    color: #4e73df;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title .badge {
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 12px;
}

.hands-list, .questions-list, .reactions-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hand-item, .question-item, .reaction-item {
    padding: 10px;
    background: #f8f9fc;
    border-radius: 8px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.hand-item {
    background: #fff3cd;
    border-left: 3px solid #ffc107;
}

.question-item {
    background: #d1ecf1;
    border-left: 3px solid #17a2b8;
    flex-direction: column;
    align-items: flex-start;
}

.question-item small {
    color: #666;
    font-size: 11px;
}

.question-text {
    color: #333;
    font-size: 14px;
    margin: 5px 0;
}

.reaction-item {
    background: #f8f9fc;
    justify-content: space-between;
}

.reaction-emoji {
    font-size: 24px;
}

.understanding-meter {
    display: flex;
    gap: 10px;
    padding: 10px;
    background: #f8f9fc;
    border-radius: 8px;
}

.understanding-stat {
    flex: 1;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
}

.understanding-stat.confused {
    background: #fee;
    color: #e74c3c;
}

.understanding-stat.understood {
    background: #efe;
    color: #1cc88a;
}

.understanding-stat .number {
    font-size: 24px;
    font-weight: bold;
}

.understanding-stat .label {
    font-size: 12px;
    margin-top: 5px;
}

.empty-state {
    text-align: center;
    color: #999;
    padding: 20px;
    font-size: 14px;
}

.dismiss-btn {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    margin-left: auto;
}

.dismiss-btn:hover {
    color: #e74c3c;
}
</style>

<div id="interaction-control-panel">
    <div class="panel-header" onclick="togglePanel()">
        <h5><i class="fas fa-broadcast-tower me-2"></i>Interacciones en Vivo</h5>
        <button class="btn-minimize" onclick="event.stopPropagation(); togglePanel()">
            <i class="fas fa-chevron-down" id="panel-chevron"></i>
        </button>
    </div>
    <div class="panel-body">
        <!-- Manos levantadas -->
        <div class="interaction-section">
            <div class="section-title">
                <i class="fas fa-hand-paper"></i>
                Manos Levantadas
                <span class="badge" id="hands-count">0</span>
            </div>
            <ul class="hands-list" id="hands-list">
                <li class="empty-state">Nadie ha levantado la mano</li>
            </ul>
        </div>

        <!-- Comprensión -->
        <div class="interaction-section">
            <div class="section-title">
                <i class="fas fa-brain"></i>
                Nivel de Comprensión
            </div>
            <div class="understanding-meter">
                <div class="understanding-stat confused">
                    <div class="number" id="confused-count">0</div>
                    <div class="label">
                        <i class="fas fa-frown"></i> Confundidos
                    </div>
                </div>
                <div class="understanding-stat understood">
                    <div class="number" id="understood-count">0</div>
                    <div class="label">
                        <i class="fas fa-smile"></i> Entendieron
                    </div>
                </div>
            </div>
        </div>

        <!-- Preguntas -->
        <div class="interaction-section">
            <div class="section-title">
                <i class="fas fa-question-circle"></i>
                Preguntas
                <span class="badge" id="questions-count">0</span>
            </div>
            <ul class="questions-list" id="questions-list">
                <li class="empty-state">No hay preguntas</li>
            </ul>
        </div>

        <!-- Reacciones recientes -->
        <div class="interaction-section">
            <div class="section-title">
                <i class="fas fa-smile"></i>
                Reacciones Recientes
            </div>
            <ul class="reactions-list" id="reactions-list">
                <li class="empty-state">No hay reacciones</li>
            </ul>
        </div>
    </div>
</div>

<script>
let panelMinimized = false;

function togglePanel() {
    const panel = document.getElementById('interaction-control-panel');
    const chevron = document.getElementById('panel-chevron');

    panelMinimized = !panelMinimized;

    if (panelMinimized) {
        panel.classList.add('minimized');
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
    } else {
        panel.classList.remove('minimized');
        chevron.classList.remove('fa-chevron-up');
        chevron.classList.add('fa-chevron-down');
    }
}

function updateInteractionPanel() {
    const codigo = '<?php echo $codigo_sesion; ?>';

    fetch('api/obtener_interacciones.php?codigo=' + codigo)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar manos levantadas
                updateHandsRaised(data.hands_raised, data.hands_raised_count);

                // Actualizar preguntas
                updateQuestions(data.questions, data.questions_count);

                // Actualizar comprensión
                updateUnderstanding(data.understanding_stats);

                // Actualizar reacciones
                updateReactions(data.recent_reactions);
            }
        })
        .catch(error => {
            console.error('Error al obtener interacciones:', error);
        });
}

function updateHandsRaised(hands, count) {
    const list = document.getElementById('hands-list');
    const countBadge = document.getElementById('hands-count');

    countBadge.textContent = count;

    if (count === 0) {
        list.innerHTML = '<li class="empty-state">Nadie ha levantado la mano</li>';
    } else {
        list.innerHTML = hands.map(hand => `
            <li class="hand-item">
                <i class="fas fa-hand-paper" style="color: #ffc107;"></i>
                <strong>${hand.nombre_participante}</strong>
                <button class="dismiss-btn" onclick="dismissHand('${hand.id_participante}')">
                    <i class="fas fa-times"></i>
                </button>
            </li>
        `).join('');
    }
}

function updateQuestions(questions, count) {
    const list = document.getElementById('questions-list');
    const countBadge = document.getElementById('questions-count');

    countBadge.textContent = count;

    if (questions.length === 0) {
        list.innerHTML = '<li class="empty-state">No hay preguntas</li>';
    } else {
        list.innerHTML = questions.map((q, index) => `
            <li class="question-item">
                <small>${q.data.anonymous ? 'Anónimo' : q.nombre_participante} - ${formatTimestamp(q.timestamp)} - Slide ${q.data.slide_number}</small>
                <div class="question-text">${escapeHtml(q.data.question)}</div>
            </li>
        `).join('');
    }
}

function updateUnderstanding(stats) {
    document.getElementById('confused-count').textContent = stats.confused;
    document.getElementById('understood-count').textContent = stats.understood;
}

function updateReactions(reactions) {
    const list = document.getElementById('reactions-list');

    if (reactions.length === 0) {
        list.innerHTML = '<li class="empty-state">No hay reacciones</li>';
    } else {
        list.innerHTML = reactions.map(r => `
            <li class="reaction-item">
                <span class="reaction-emoji">${r.data.reaction}</span>
                <small>${r.nombre_participante}</small>
            </li>
        `).join('');
    }
}

function dismissHand(participanteId) {
    // Enviar petición para bajar la mano
    // Por ahora, simplemente actualizar
    updateInteractionPanel();
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Actualizar cada 2 segundos
setInterval(updateInteractionPanel, 2000);

// Primera actualización
updateInteractionPanel();
</script>
