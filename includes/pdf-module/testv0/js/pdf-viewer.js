// Configuración de PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

// Variables globales
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
const canvas = document.getElementById('pdf-canvas');
const ctx = canvas.getContext('2d');
const scale = 1.5;

// Elementos DOM
const pdfForm = document.getElementById('pdf-upload-form');
const pdfFileInput = document.getElementById('pdf-file');
const pdfContainer = document.getElementById('pdf-container');
const pdfPlaceholder = document.getElementById('pdf-placeholder');
const currentPageEl = document.getElementById('current-page');
const totalPagesEl = document.getElementById('total-pages');
const prevButton = document.getElementById('prev-page');
const nextButton = document.getElementById('next-page');

// Función para renderizar una página del PDF
function renderPage(num) {
    pageRendering = true;
    
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale });
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        
        const renderTask = page.render(renderContext);
        
        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });
    });
    
    currentPageEl.textContent = num;
    
    // Actualizar estado de los botones
    prevButton.disabled = num <= 1;
    nextButton.disabled = num >= pdfDoc.numPages;
}

// Cambiar de página
function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

function onPrevPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

function onNextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

// Procesar el PDF subido
pdfForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const file = pdfFileInput.files[0];
    if (!file || file.type !== 'application/pdf') {
        alert('Por favor, seleccione un archivo PDF válido.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const typedarray = new Uint8Array(e.target.result);
        
        pdfjsLib.getDocument(typedarray).promise.then(function(pdf) {
            pdfDoc = pdf;
            totalPagesEl.textContent = pdf.numPages;
            
            // Mostrar PDF
            pdfPlaceholder.style.display = 'none';
            canvas.style.display = 'block';
            
            // Habilitar controles
            prevButton.disabled = true;
            nextButton.disabled = false;
            
            // Renderizar primera página
            pageNum = 1;
            renderPage(pageNum);
            
            console.log("PDF cargado correctamente, páginas:", pdf.numPages);
        }).catch(function(error) {
            console.error("Error al cargar el PDF:", error);
            alert("Error al cargar el PDF: " + error.message);
        });
    };
    reader.readAsArrayBuffer(file);
});

// Eventos para navegación
prevButton.addEventListener('click', onPrevPage);
nextButton.addEventListener('click', onNextPage);

// Eventos de teclado para navegación
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight') {
        onNextPage();
    } else if (e.key === 'ArrowLeft') {
        onPrevPage();
    }
});

// Mensaje de diagnóstico
console.log("PDF Viewer inicializado. Versión de PDF.js:", pdfjsLib.version);