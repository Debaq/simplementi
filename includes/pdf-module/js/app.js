// Variables globales
window.presentationActive = false;

// Inicializar la aplicación
function initApp() {
    // Inicializar componentes
    window.PDFViewer.init();
    window.SequenceEditor.init();
    window.PresentationMode.init();
    
    // Configurar el formulario de subida de PDF
    const pdfForm = document.getElementById('pdf-upload-form');
    const pdfFileInput = document.getElementById('pdf-file');
    
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
            window.PDFViewer.loadPDF(typedarray);
        };
        reader.readAsArrayBuffer(file);
    });
    
    // Solo para desarrollo/demostración - mensaje de instrucción
    console.log('App iniciada. Para integrar con PHP, reemplazar la función de carga de PDF.');
}

// Iniciar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initApp);