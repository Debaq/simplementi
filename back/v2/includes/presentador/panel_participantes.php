<!-- Panel de participantes -->
<div class="card shadow-lg mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Participantes</h5>
        <span id="contador-participantes-card" class="badge bg-light text-dark">0</span>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="row g-0">
                <div class="col-6">
                    <div class="d-flex align-items-center border-end h-100 p-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <div>
                            <div class="text-muted small">Respuestas</div>
                            <div class="fw-bold" id="total-respuestas">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center h-100 p-3">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <div>
                            <div class="text-muted small">Tiempo promedio</div>
                            <div class="fw-bold" id="tiempo-promedio">0s</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Lista de participantes</h6>
                <span class="badge bg-secondary" id="total-pendientes">0</span>
            </div>
            <ul class="list-group" id="lista-participantes">
                <li class="list-group-item text-center text-muted">Esperando participantes...</li>
            </ul>
        </div>
    </div>
</div>