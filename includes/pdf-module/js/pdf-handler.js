// Configuración global para PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

// Estado del PDF
let PDFState = {
    pdfDoc: null,
    pageNum: 1,
    pageRendering: false,
    pageNumPending: null,
    scale: 1.5,
    canvas: null,
    ctx: null
};

// Elementos DOM
const pdfElements = {
    pdfContainer: document.getElementById('pdf-container'),
    pdfPlaceholder: document.getElementById('pdf-placeholder'),
    canvas: document.getElementById('pdf-canvas'),
    currentPage: document.getElementById('current-page'),
    totalPages: document.getElementById('total-pages'),
    prevButton: document.getElementById('prev-page'),
    nextButton: document.getElementById('next-page'),
    thumbnailsContainer: document.getElementById('thumbnails-container'),
    pdfThumbnails: document.getElementById('pdf-thumbnails'),
    showThumbnails: document.getElementById('show-thumbnails')
};

// Configuración inicial
function initPDFViewer() {
    // Inicializar canvas y contexto
    PDFState.canvas = pdfElements.canvas;
    PDFState.ctx = pdfElements.canvas.getContext('2d');

    // Evento para mostrar/ocultar miniaturas
    pdfElements.showThumbnails.addEventListener('change', toggleThumbnails);

    // Botones de navegación
    pdfElements.prevButton.addEventListener('click', onPrevPage);
    pdfElements.nextButton.addEventListener('click', onNextPage);

    // Manejar navegación con teclado (cuando no está en modo presentación)
    document.addEventListener('keydown', function(e) {
        if (!window.presentationActive) {
            if (e.key === 'ArrowRight') {
                onNextPage();
            } else if (e.key === 'ArrowLeft') {
                onPrevPage();
            }
        }
    });
}

// Renderizar una página del PDF
function renderPage(num) {
    PDFState.pageRendering = true;
    
    PDFState.pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: PDFState.scale });
        PDFState.canvas.height = viewport.height;
        PDFState.canvas.width = viewport.width;
        
        const renderContext = {
            canvasContext: PDFState.ctx,
            viewport: viewport
        };
        
        const renderTask = page.render(renderContext);
        
        renderTask.promise.then(function() {
            PDFState.pageRendering = false;
            if (PDFState.pageNumPending !== null) {
                renderPage(PDFState.pageNumPending);
                PDFState.pageNumPending = null;
            }
        });
    });
    
    pdfElements.currentPage.textContent = num;
    
    // Actualizar estado de los botones
    pdfElements.prevButton.disabled = num <= 1;
    pdfElements.nextButton.disabled = num >= PDFState.pdfDoc.numPages;
    
    // Actualizar miniaturas activas
    updateThumbnailsActive(num);
}

// Cambiar de página
function queueRenderPage(num) {
    if (PDFState.pageRendering) {
        PDFState.pageNumPending = num;
    } else {
        renderPage(num);
    }
}

// Ir a la página anterior
function onPrevPage() {
    if (PDFState.pageNum <= 1) return;
    PDFState.pageNum--;
    queueRenderPage(PDFState.pageNum);
}

// Ir a la página siguiente
function onNextPage() {
    if (PDFState.pageNum >= PDFState.pdfDoc.numPages) return;
    PDFState.pageNum++;
    queueRenderPage(PDFState.pageNum);
}

// Mostrar/ocultar miniaturas
function toggleThumbnails() {
    pdfElements.thumbnailsContainer.style.display = this.checked ? 'block' : 'none';
}

// Actualizar miniaturas activas
function updateThumbnailsActive(pageNum) {
    const thumbnails = document.querySelectorAll('.pdf-thumbnail');
    thumbnails.forEach((thumb, idx) => {
        if (idx + 1 === pageNum) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

// Generar miniaturas de PDF
function generateThumbnails(pdfDoc) {
    pdfElements.pdfThumbnails.innerHTML = '';
    
    for (let i = 1; i <= pdfDoc.numPages; i++) {
        pdfDoc.getPage(i).then(function(page) {
            const viewport = page.getViewport({ scale: 0.2 });
            const thumbnail = document.createElement('canvas');
            thumbnail.width = viewport.width;
            thumbnail.height = viewport.height;
            thumbnail.className = 'pdf-thumbnail';
            thumbnail.title = `Página ${i}`;
            thumbnail.dataset.page = i;
            
            if (i === 1) thumbnail.classList.add('active');
            
            const renderContext = {
                canvasContext: thumbnail.getContext('2d'),
                viewport: viewport
            };
            
            page.render(renderContext);
            pdfElements.pdfThumbnails.appendChild(thumbnail);
            
            // Evento para cambiar a esta página
            thumbnail.addEventListener('click', function() {
                PDFState.pageNum = parseInt(this.dataset.page);
                queueRenderPage(PDFState.pageNum);
            });
        });
    }
    
    if (pdfElements.showThumbnails.checked) {
        pdfElements.thumbnailsContainer.style.display = 'block';
    }
}

// Cargar PDF
function loadPDF(arrayBuffer) {
    pdfjsLib.getDocument(arrayBuffer).promise.then(function(pdf) {
        PDFState.pdfDoc = pdf;
        pdfElements.totalPages.textContent = pdf.numPages;
        
        // Mostrar PDF
        pdfElements.pdfPlaceholder.style.display = 'none';
        pdfElements.canvas.style.display = 'block';
        
        // Habilitar controles
        pdfElements.prevButton.disabled = true;
        pdfElements.nextButton.disabled = false;
        
        // Renderizar primera página
        PDFState.pageNum = 1;
        renderPage(PDFState.pageNum);
        
        // Generar miniaturas
        generateThumbnails(pdf);
        
        // Inicializar secuencia (definida en sequence-editor.js)
        window.initializeSequence(pdf.numPages);
        
        // Habilitar botón de previsualización
        document.getElementById('preview-sequence-btn').disabled = false;
    });
}

// Exportar funciones y variables para uso en otros archivos
window.PDFViewer = {
    init: initPDFViewer,
    loadPDF: loadPDF,
    renderPage: renderPage,
    queueRenderPage: queueRenderPage,
    state: PDFState
};