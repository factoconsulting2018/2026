/**
 * Gestor de Biblioteca para clientes
 */

// Funciones del Gestor de Biblioteca
function uploadDocument() {
    // Crear modal para subir archivos
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'uploadModal';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #007bff;">upload</span>
                        Subir Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm">
                        <div class="mb-3">
                            <label for="fileName" class="form-label">Nombre del Archivo</label>
                            <input type="text" class="form-control" id="fileName" placeholder="Ej: Contrato_Alquiler_001" required>
                            <div class="form-text">Ingresa un nombre descriptivo para el archivo</div>
                        </div>
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Seleccionar Archivo</label>
                            <input type="file" class="form-control" id="fileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <div class="form-text">Formatos permitidos: PDF, DOC, DOCX, JPG, PNG</div>
                        </div>
                        <div class="mb-3">
                            <label for="fileDescription" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control" id="fileDescription" rows="3" placeholder="Descripción del documento..."></textarea>
                        </div>
                        <div class="progress mb-3" style="display: none;" id="uploadProgress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitUpload()">
                        <span class="spinner-border spinner-border-sm d-none" id="uploadSpinner"></span>
                        <span id="uploadBtnText">Subir Archivo</span>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Mostrar modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    // Limpiar modal cuando se cierre
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function submitUpload() {
    const fileName = document.getElementById('fileName').value;
    const fileInput = document.getElementById('fileInput');
    const description = document.getElementById('fileDescription').value;
    const progress = document.getElementById('uploadProgress');
    const spinner = document.getElementById('uploadSpinner');
    const btnText = document.getElementById('uploadBtnText');
    
    if (!fileName || !fileInput.files[0]) {
        showNotification('error', 'Por favor completa todos los campos requeridos');
        return;
    }
    
    // Mostrar loading
    progress.style.display = 'block';
    spinner.classList.remove('d-none');
    btnText.textContent = 'Subiendo...';
    
    // Simular subida (aquí iría la lógica real de subida)
    setTimeout(() => {
        progress.querySelector('.progress-bar').style.width = '100%';
        
        setTimeout(() => {
            showNotification('success', 'Archivo subido exitosamente');
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            modal.hide();
            
            // Actualizar lista de documentos
            addDocumentToList(fileName, fileInput.files[0].name, description);
        }, 500);
    }, 2000);
}

function startUpload() {
    const fileName = document.getElementById('fileName').value;
    const fileInput = document.getElementById('fileInput');
    const fileDescription = document.getElementById('fileDescription').value;
    const file = fileInput.files[0];
    
    if (!fileName.trim()) {
        showNotification('Por favor ingresa un nombre para el archivo', 'warning');
        return;
    }
    
    if (!file) {
        showNotification('Por favor selecciona un archivo', 'warning');
        return;
    }
    
    // Mostrar loading
    document.getElementById('uploadLoading').style.display = 'block';
    document.querySelector('#uploadModal .modal-footer').style.display = 'none';
    
    // Simular progreso de subida
    let progress = 0;
    const progressBar = document.getElementById('uploadProgress');
    const statusText = document.getElementById('uploadStatus');
    
    const uploadInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        statusText.textContent = Math.round(progress) + '%';
        
        if (progress >= 100) {
            clearInterval(uploadInterval);
            
            // Simular finalización
            setTimeout(() => {
                showNotification(`Archivo "${fileName}" subido exitosamente`, 'success');
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                modal.hide();
                
                // Recargar página para mostrar el nuevo archivo
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }, 500);
        }
    }, 200);
}

function addDocumentToList(fileName, originalName, description) {
    const documentsList = document.getElementById('documentsList');
    if (documentsList) {
        const documentItem = document.createElement('div');
        documentItem.className = 'list-group-item d-flex justify-content-between align-items-center';
        documentItem.innerHTML = `
            <div>
                <h6 class="mb-1">${fileName}</h6>
                <small class="text-muted">${originalName} • ${new Date().toLocaleDateString()}</small>
                ${description ? `<p class="mb-0 mt-1">${description}</p>` : ''}
            </div>
            <div>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="downloadDocument('${fileName}')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteDocument('${fileName}')">
                    <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                </button>
            </div>
        `;
        
        documentsList.appendChild(documentItem);
    }
}

function viewDocuments() {
    showNotification('info', 'Funcionalidad de ver documentos en desarrollo');
}

function createContract() {
    showNotification('info', 'Funcionalidad de crear contrato en desarrollo');
}

function viewContracts() {
    showNotification('info', 'Funcionalidad de ver contratos en desarrollo');
}

function downloadDocument(fileName) {
    showNotification('success', `Descargando ${fileName}...`);
}

function deleteDocument(fileName) {
    if (confirm(`¿Estás seguro de que quieres eliminar ${fileName}?`)) {
        showNotification('success', `${fileName} eliminado exitosamente`);
        // Aquí iría la lógica real de eliminación
    }
}

function showNotification(message, type = 'info') {
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('Library manager inicializado');
});
