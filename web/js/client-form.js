/**
 * JavaScript para el formulario de clientes con integración Hacienda
 */

// Variables globales
let consultaTimeout = null;

function consultarHacienda() {
    const cedula = document.getElementById('cedula-input').value.trim();
    
    console.log('Iniciando consulta de Hacienda para cédula:', cedula);
    
    if (!cedula) {
        showNotification('❌ Por favor ingrese la cédula antes de consultar Hacienda', 'warning');
        return;
    }
    
    // Validar formato de cédula (9 o 10 dígitos)
    if (!/^\d{9,10}$/.test(cedula)) {
        showNotification('❌ La cédula debe tener entre 9 y 10 dígitos', 'warning');
        return;
    }
    
    // Mostrar loading
    document.getElementById('hacienda-loading').style.display = 'block';
    document.getElementById('hacienda-result').style.display = 'none';
    document.getElementById('hacienda-error').style.display = 'none';
    document.getElementById('consultar-btn').disabled = true;
    
    // Realizar consulta AJAX
    fetch('/hacienda/consultar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            cedula: cedula
        })
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        document.getElementById('hacienda-loading').style.display = 'none';
        
        if (data.success && data.data) {
            // Llenar campos automáticamente
            llenarCamposDesdeHacienda(data.data);
            mostrarResultadoHacienda(data.data);
        } else {
            console.log('Error en respuesta:', data.message || 'Sin datos');
            document.getElementById('hacienda-error').style.display = 'block';
            showNotification('⚠️ ' + (data.message || 'No se encontró información en Hacienda'), 'warning');
        }
    })
    .catch(error => {
        console.error('Error en consulta:', error);
        document.getElementById('hacienda-loading').style.display = 'none';
        document.getElementById('hacienda-error').style.display = 'block';
        showNotification('❌ Error al consultar Hacienda: ' + error.message, 'danger');
    })
    .finally(() => {
        document.getElementById('consultar-btn').disabled = false;
    });
}

function llenarCamposDesdeHacienda(data) {
    // Llenar campos del formulario con datos de Hacienda
    console.log('Llenando campos con datos:', data);
    
    // Nombre completo
    if (data.nombre) {
        document.getElementById('nombre-input').value = data.nombre;
        document.getElementById('nombre-input').style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            document.getElementById('nombre-input').style.backgroundColor = '';
        }, 2000);
    }
    
    // Tipo de identificación
    if (data.tipoIdentificacion) {
        document.getElementById('tipo-identificacion-input').value = data.tipoIdentificacion;
        document.getElementById('tipo-identificacion-input').style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            document.getElementById('tipo-identificacion-input').style.backgroundColor = '';
        }, 2000);
    }
    
    // Situación tributaria
    if (data.situacionTributaria) {
        document.getElementById('situacion-tributaria-input').value = data.situacionTributaria;
        document.getElementById('situacion-tributaria-input').style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            document.getElementById('situacion-tributaria-input').style.backgroundColor = '';
        }, 2000);
    }
    
    // Régimen tributario
    if (data.regimenTributario) {
        document.getElementById('regimen-tributario-input').value = data.regimenTributario;
        document.getElementById('regimen-tributario-input').style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            document.getElementById('regimen-tributario-input').style.backgroundColor = '';
        }, 2000);
    }
    
    // Actividad económica
    if (data.actividadEconomica) {
        const actividad = data.actividadEconomica;
        if (actividad.codigo) {
            document.getElementById('actividad-codigo-input').value = actividad.codigo;
            document.getElementById('actividad-codigo-input').style.backgroundColor = '#e8f5e8';
            setTimeout(() => {
                document.getElementById('actividad-codigo-input').style.backgroundColor = '';
            }, 2000);
        }
        if (actividad.descripcion) {
            document.getElementById('actividad-descripcion-input').value = actividad.descripcion;
            document.getElementById('actividad-descripcion-input').style.backgroundColor = '#e8f5e8';
            setTimeout(() => {
                document.getElementById('actividad-descripcion-input').style.backgroundColor = '';
            }, 2000);
        }
    }
    
    // Establecer estado activo por defecto
    const statusSelect = document.querySelector('select[name="Client[status]"]');
    if (statusSelect) {
        statusSelect.value = 'active';
    }
    
    // Establecer como cliente Facto por defecto
    const clienteFactoCheckbox = document.getElementById('cliente-facto');
    if (clienteFactoCheckbox && !clienteFactoCheckbox.checked) {
        clienteFactoCheckbox.checked = true;
    }
    
    // Mostrar mensaje de éxito
    showNotification('✅ Campos completados automáticamente desde Hacienda', 'success');
}

function mostrarResultadoHacienda(data) {
    // Mostrar resumen de la información obtenida
    const resultDiv = document.getElementById('hacienda-result');
    resultDiv.innerHTML = `
        <div class="alert alert-success">
            <h6><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">verified</span>Información Obtenida</h6>
            <p><strong>Nombre:</strong> ${data.nombre || 'N/A'}</p>
            <p><strong>Tipo:</strong> ${data.tipoIdentificacion || 'N/A'}</p>
            <p><strong>Situación:</strong> ${data.situacionTributaria || 'N/A'}</p>
            <p><strong>Régimen:</strong> ${data.regimenTributario || 'N/A'}</p>
        </div>
    `;
    resultDiv.style.display = 'block';
}

function limpiarFormulario() {
    // Limpiar todos los campos del formulario
    document.getElementById('cedula-input').value = '';
    document.getElementById('nombre-input').value = '';
    document.getElementById('tipo-identificacion-input').value = '';
    document.getElementById('situacion-tributaria-input').value = '';
    document.getElementById('regimen-tributario-input').value = '';
    document.getElementById('actividad-codigo-input').value = '';
    document.getElementById('actividad-descripcion-input').value = '';
    
    // Limpiar resultados de Hacienda
    document.getElementById('hacienda-result').style.display = 'none';
    document.getElementById('hacienda-error').style.display = 'none';
    document.getElementById('hacienda-loading').style.display = 'none';
}

function showNotification(message, type = 'info') {
    // Crear notificación flotante
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function validarFormulario() {
    const cedula = document.getElementById('cedula-input').value.trim();
    const nombre = document.getElementById('nombre-input').value.trim();
    
    if (!cedula) {
        showNotification('❌ La cédula es requerida', 'warning');
        return false;
    }
    
    if (!/^\d{9,10}$/.test(cedula)) {
        showNotification('❌ La cédula debe tener entre 9 y 10 dígitos', 'warning');
        return false;
    }
    
    if (!nombre) {
        showNotification('❌ El nombre completo es requerido', 'warning');
        return false;
    }
    
    return true;
}

// Variables globales para modales
let currentDuplicateCedula = '';

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const cedulaInput = document.getElementById('cedula-input');
    const clientForm = document.getElementById('client-form');
    
    // Auto-consulta después de 2 segundos de no escribir
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function() {
            clearTimeout(consultaTimeout);
            consultaTimeout = setTimeout(() => {
                const cedula = this.value.trim();
                if (cedula && /^\d{9,10}$/.test(cedula)) {
                    console.log('Auto-consultando Hacienda para:', cedula);
                    consultarHacienda();
                }
            }, 2000);
        });
    }
    
    // Validación del formulario
    if (clientForm) {
        clientForm.addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
                return false;
            }
            
            // Mostrar loading en el botón de envío
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            }
            
            // Interceptar la respuesta para manejar errores de cédula duplicada
            const originalSubmit = this.submit;
            this.submit = function() {
                // Enviar formulario con AJAX para manejar la respuesta
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Restaurar botón
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Guardar Cliente';
                    
                    // Verificar si la respuesta contiene un error de cédula duplicada
                    if (html.includes('ya está registrada') || html.includes('has already been taken')) {
                        const cedula = document.getElementById('cedula-input').value.trim();
                        showDuplicateCedulaModal(cedula, 'La cédula ya existe en el sistema');
                    } else {
                        // Si no hay error, recargar la página con la respuesta
                        document.open();
                        document.write(html);
                        document.close();
                    }
                })
                .catch(error => {
                    // Restaurar botón en caso de error
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Guardar Cliente';
                    showNotification('❌ Error al guardar: ' + error.message, 'danger');
                });
            };
        });
    }
    
    // Formateo en tiempo real de la cédula
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Solo números
            this.value = value;
            
            // Feedback visual
            if (value.length >= 9 && value.length <= 10) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    // Verificar si hay un flash de cédula duplicada
    checkForDuplicateCedula();
});

// Función para verificar si hay un flash de cédula duplicada
function checkForDuplicateCedula() {
    // Esta función se ejecutará cuando la página se carga
    // Si hay un flash message de cédula duplicada, mostrar el modal
    const duplicateFlash = document.querySelector('[data-flash="cedula_duplicate"]');
    if (duplicateFlash) {
        const data = JSON.parse(duplicateFlash.textContent);
        showDuplicateCedulaModal(data.cedula, data.message);
    }
}

// Función para mostrar el modal de cédula duplicada
function showDuplicateCedulaModal(cedula, message) {
    currentDuplicateCedula = cedula;
    document.getElementById('duplicate-cedula').textContent = cedula;
    
    const modal = new bootstrap.Modal(document.getElementById('cedulaDuplicateModal'));
    modal.show();
}

// Función para mostrar el modal de confirmación de eliminación
function mostrarModalEliminar() {
    document.getElementById('delete-cedula').textContent = currentDuplicateCedula;
    
    // Cerrar el modal actual
    const duplicateModal = bootstrap.Modal.getInstance(document.getElementById('cedulaDuplicateModal'));
    if (duplicateModal) {
        duplicateModal.hide();
    }
    
    // Mostrar el modal de confirmación
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    deleteModal.show();
}

// Función para eliminar cliente por cédula
function eliminarClientePorCedula() {
    const cedula = currentDuplicateCedula;
    
    // Mostrar loading
    const deleteBtn = document.querySelector('#confirmDeleteModal .btn-danger');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';
    deleteBtn.disabled = true;
    
    // Realizar petición AJAX
    fetch('/client/delete-by-cedula', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            cedula: cedula
        })
    })
    .then(response => response.json())
    .then(data => {
        // Restaurar botón
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        
        if (data.success) {
            // Cerrar modales
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            if (deleteModal) {
                deleteModal.hide();
            }
            
            // Mostrar notificación de éxito
            showNotification('✅ ' + data.message, 'success');
            
            // Limpiar el campo de cédula y enfocar
            const cedulaInput = document.getElementById('cedula-input');
            cedulaInput.value = '';
            cedulaInput.focus();
            
        } else {
            showNotification('❌ ' + data.message, 'danger');
        }
    })
    .catch(error => {
        // Restaurar botón
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        
        console.error('Error:', error);
        showNotification('❌ Error al eliminar cliente: ' + error.message, 'danger');
    });
}

// Función para buscar cliente existente
function buscarClienteExistente() {
    const cedula = currentDuplicateCedula;
    
    // Redirigir a la búsqueda de clientes con la cédula como filtro y mostrar todos los estados
    window.location.href = `/client/index?search=${encodeURIComponent(cedula)}&estado=all`;
}

// Función para convertir texto a mayúsculas automáticamente
function convertirAMayusculas(input) {
    input.value = input.value.toUpperCase();
}

// Inicializar conversión automática a mayúsculas
document.addEventListener('DOMContentLoaded', function() {
    const nombreInput = document.getElementById('nombre-input');
    if (nombreInput) {
        // Convertir a mayúsculas mientras el usuario escribe
        nombreInput.addEventListener('input', function() {
            convertirAMayusculas(this);
        });
        
        // Convertir a mayúsculas al perder el foco
        nombreInput.addEventListener('blur', function() {
            convertirAMayusculas(this);
        });
    }

    // Inicializar biblioteca de archivos si el tab existe
    const bibliotecaTab = document.getElementById('biblioteca-tab');
    if (bibliotecaTab) {
        // Cargar archivos cuando se haga clic en el tab
        bibliotecaTab.addEventListener('shown.bs.tab', function() {
            loadFiles();
        });
        
        // También cargar si el tab ya está activo al cargar la página
        const bibliotecaPane = document.getElementById('biblioteca-pane');
        if (bibliotecaPane && bibliotecaPane.classList.contains('active')) {
            loadFiles();
        }
    }

    // Permitir búsqueda con Enter
    const fileSearchInput = document.getElementById('file-search-input');
    if (fileSearchInput) {
        fileSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchFiles();
            }
        });
    }
});

// ==================== FUNCIONES DE BIBLIOTECA DE ARCHIVOS ====================

let currentClientId = null;
let currentSearchTerm = '';

// Función para cargar archivos del cliente
function loadFiles(clientId = null, search = '') {
    // Obtener clientId de múltiples fuentes si no se proporciona
    if (!clientId) {
        // 1. Intentar obtener del atributo data del botón
        const uploadBtn = document.getElementById('upload-file-btn');
        if (uploadBtn && uploadBtn.dataset.clientId) {
            clientId = uploadBtn.dataset.clientId;
        }
        
        // 2. Intentar obtener del URL
        if (!clientId) {
            const pathParts = window.location.pathname.split('/').filter(p => p);
            const updateIndex = pathParts.indexOf('update');
            
            if (updateIndex !== -1 && pathParts[updateIndex + 1]) {
                clientId = pathParts[updateIndex + 1];
            } else {
                // Intentar obtener de la URL actual si estamos en view o update
                const urlMatch = window.location.pathname.match(/\/client\/(update|view)\/(\d+)/);
                if (urlMatch && urlMatch[2]) {
                    clientId = urlMatch[2];
                }
            }
        }
        
        // 3. Usar currentClientId si está disponible
        if (!clientId && currentClientId) {
            clientId = currentClientId;
        }
    }
    
    // Convertir a número para validar
    clientId = parseInt(clientId);
    
    if (!clientId || isNaN(clientId) || clientId <= 0) {
        document.getElementById('files-container').innerHTML = '<div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">error</span><p>No se pudo determinar el ID del cliente</p><small>URL: ' + window.location.pathname + '</small></div>';
        console.error('Client ID no disponible o inválido:', clientId);
        return;
    }
    
    currentClientId = clientId;
    currentSearchTerm = search;
    
    const url = `/client/list-files/${clientId}${search ? '?search=' + encodeURIComponent(search) : ''}`;
    
    console.log('Cargando archivos desde:', url);
    
    document.getElementById('files-container').innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-3">Cargando archivos...</p></div>';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Archivos cargados:', data);
            if (data.success) {
                displayFiles(data.data);
            } else {
                document.getElementById('files-container').innerHTML = `<div class="alert alert-danger"><span class="material-symbols-outlined">error</span> ${data.message || 'Error al cargar archivos'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error cargando archivos:', error);
            document.getElementById('files-container').innerHTML = `<div class="alert alert-danger"><span class="material-symbols-outlined">error</span> Error al cargar archivos: ${error.message}</div>`;
        });
}

// Función para mostrar archivos en la lista
function displayFiles(files) {
    const container = document.getElementById('files-container');
    
    if (!files || files.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">folder_off</span><p>No hay archivos subidos aún</p></div>';
        return;
    }
    
    let html = '<div class="row">';
    
    files.forEach(file => {
        const fileIcon = getFileIcon(file.file_type);
        const createdDate = new Date(file.created_at).toLocaleDateString('es-CR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-2">
                            <span class="material-symbols-outlined me-2" style="font-size: 32px; color: #3fa9f5;">${file.icon || fileIcon}</span>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" title="${file.file_name}">${file.file_name}</h6>
                                <small class="text-muted d-block">${file.original_name}</small>
                                ${file.description ? `<small class="text-muted d-block mt-1">${file.description}</small>` : ''}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">schedule</span>
                                ${createdDate}
                            </small>
                            <small class="text-muted">${file.formatted_size || formatFileSize(file.file_size)}</small>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadFile(${file.id})" title="Descargar">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">download</span>
                                Descargar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFile(${file.id})" title="Eliminar">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Función para obtener icono según tipo de archivo
function getFileIcon(fileType) {
    if (!fileType) return 'description';
    
    if (fileType.includes('pdf')) return 'picture_as_pdf';
    if (fileType.includes('image')) return 'image';
    if (fileType.includes('word') || fileType.includes('document')) return 'description';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'table_chart';
    
    return 'description';
}

// Función para formatear tamaño de archivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Función para subir archivo
function uploadFile(clientId) {
    // Obtener clientId de múltiples fuentes
    if (!clientId) {
        // 1. Intentar obtener del atributo data del botón
        const uploadBtn = document.getElementById('upload-file-btn');
        if (uploadBtn && uploadBtn.dataset.clientId) {
            clientId = uploadBtn.dataset.clientId;
        }
        
        // 2. Intentar obtener del URL
        if (!clientId) {
            const pathParts = window.location.pathname.split('/').filter(p => p);
            const updateIndex = pathParts.indexOf('update');
            
            if (updateIndex !== -1 && pathParts[updateIndex + 1]) {
                clientId = pathParts[updateIndex + 1];
            } else {
                // Intentar obtener de la URL actual si estamos en view o update
                const urlMatch = window.location.pathname.match(/\/client\/(update|view)\/(\d+)/);
                if (urlMatch && urlMatch[2]) {
                    clientId = urlMatch[2];
                }
            }
        }
        
        // 3. Intentar obtener de currentClientId
        if (!clientId && currentClientId) {
            clientId = currentClientId;
        }
    }
    
    // Convertir a número para validar
    clientId = parseInt(clientId);
    
    if (!clientId || isNaN(clientId) || clientId <= 0) {
        showNotification('❌ Error: No se pudo determinar el ID del cliente. Por favor, recarga la página.', 'danger');
        console.error('Client ID no disponible o inválido:', clientId);
        console.log('URL actual:', window.location.pathname);
        return;
    }
    
    console.log('Usando Client ID:', clientId);
    
    const fileInput = document.getElementById('file-input');
    const fileNameInput = document.getElementById('file-name-input');
    const descriptionInput = document.getElementById('file-description-input');
    
    // Validar que los elementos existan
    if (!fileInput) {
        showNotification('❌ Error: No se encontró el campo de archivo', 'danger');
        console.error('Elemento file-input no encontrado');
        return;
    }
    
    if (!fileNameInput) {
        showNotification('❌ Error: No se encontró el campo de nombre', 'danger');
        console.error('Elemento file-name-input no encontrado');
        return;
    }
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showNotification('❌ Por favor seleccione un archivo', 'warning');
        return;
    }
    
    if (!fileNameInput.value.trim()) {
        showNotification('❌ Por favor ingrese un nombre para el archivo', 'warning');
        fileNameInput.focus();
        return;
    }
    
    const file = fileInput.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (file.size > maxSize) {
        showNotification('❌ El archivo es demasiado grande. Tamaño máximo: 10MB', 'danger');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('file_name', fileNameInput.value.trim());
    formData.append('description', descriptionInput.value.trim());
    
    // Mostrar loading
    const uploadBtn = document.querySelector('#file-upload-form button[type="button"]');
    
    if (!uploadBtn) {
        showNotification('❌ Error: No se encontró el botón de subir', 'danger');
        console.error('Botón de upload no encontrado');
        return;
    }
    
    const originalText = uploadBtn.innerHTML;
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...';
    
    // Construir URL correcta
    const baseUrl = window.location.origin;
    const uploadUrl = `${baseUrl}/client/upload-file/${clientId}`;
    
    console.log('Subiendo archivo a:', uploadUrl);
    console.log('Client ID:', clientId);
    
    // Obtener token CSRF si está disponible
    const csrfToken = document.querySelector('meta[name="csrf-token"]') 
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : (typeof yii !== 'undefined' && yii.getCsrfToken) 
            ? yii.getCsrfToken() 
            : null;
    
    fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status, response.statusText);
        
        // Si la respuesta no es exitosa, intentar obtener el error del cuerpo
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error del servidor:', text);
                let errorData;
                try {
                    errorData = JSON.parse(text);
                } catch (e) {
                    errorData = { success: false, message: `Error del servidor (${response.status}): ${text.substring(0, 200)}` };
                }
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            });
        }
        
        // Si es exitosa, parsear JSON
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            
            // Limpiar formulario
            fileInput.value = '';
            fileNameInput.value = '';
            if (descriptionInput) {
                descriptionInput.value = '';
            }
            
            // Recargar lista de archivos
            if (clientId) {
                loadFiles(clientId, currentSearchTerm);
            }
        } else {
            showNotification('❌ ' + (data.message || 'Error al subir el archivo'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        showNotification('❌ Error al subir el archivo: ' + error.message, 'danger');
    });
}

// Función para buscar archivos
function searchFiles() {
    const searchInput = document.getElementById('file-search-input');
    const searchTerm = searchInput.value.trim();
    
    if (currentClientId) {
        loadFiles(currentClientId, searchTerm);
    }
}

// Función para limpiar búsqueda
function clearFileSearch() {
    document.getElementById('file-search-input').value = '';
    if (currentClientId) {
        loadFiles(currentClientId, '');
    }
}

// Función para descargar archivo
function downloadFile(fileId) {
    window.location.href = `/client/download-file/${fileId}`;
}

// Función para eliminar archivo
function deleteFile(fileId) {
    if (!confirm('¿Está seguro de que desea eliminar este archivo? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch(`/client/delete-file/${fileId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            // Recargar lista de archivos
            if (currentClientId) {
                loadFiles(currentClientId, currentSearchTerm);
            }
        } else {
            showNotification('❌ ' + (data.message || 'Error al eliminar el archivo'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Error al eliminar el archivo: ' + error.message, 'danger');
    });
}
