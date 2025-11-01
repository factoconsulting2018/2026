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

// Función para mostrar alerta de éxito (modal Bootstrap)
function showSuccessAlert(title, message) {
    // Remover modales anteriores si existen
    const existingModal = document.getElementById('file-upload-success-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'file-upload-success-modal';
    modal.setAttribute('data-bs-backdrop', 'static');
    modal.setAttribute('data-bs-keyboard', 'false');
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <span class="material-symbols-outlined me-2" style="font-size: 24px; vertical-align: middle;">check_circle</span>
                        ${title}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">done</span>
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Remover del DOM después de cerrar
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
    
    // También mostrar notificación flotante
    showNotification(title + ': ' + message, 'success');
}

// Función para mostrar alerta de error (modal Bootstrap)
function showErrorAlert(title, message, details = null) {
    // Remover modales anteriores si existen
    const existingModal = document.getElementById('file-upload-error-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'file-upload-error-modal';
    modal.setAttribute('data-bs-backdrop', 'static');
    modal.setAttribute('data-bs-keyboard', 'false');
    
    let detailsHtml = '';
    if (details && typeof details === 'string') {
        detailsHtml = `
            <hr>
            <details class="mt-3">
                <summary class="text-muted" style="cursor: pointer;">Ver detalles técnicos</summary>
                <pre class="mt-2 p-2 bg-light border rounded" style="font-size: 11px; max-height: 200px; overflow-y: auto;">${details.substring(0, 1000)}</pre>
            </details>
        `;
    }
    
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <span class="material-symbols-outlined me-2" style="font-size: 24px; vertical-align: middle;">error</span>
                        ${title}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <strong>Error:</strong> ${message}
                    </div>
                    ${detailsHtml}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">close</span>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Remover del DOM después de cerrar
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
    
    // También mostrar notificación flotante
    showNotification(title + ': ' + message, 'danger');
}

function validarFormulario() {
    // Validar solo si los elementos existen (para evitar errores)
    const cedulaInput = document.getElementById('cedula-input');
    const nombreInput = document.getElementById('nombre-input');
    
    if (!cedulaInput || !nombreInput) {
        console.warn('⚠️ Elementos de validación no encontrados, validación omitida');
        return true; // Permitir envío si no se encuentran los elementos
    }
    
    const cedula = cedulaInput.value.trim();
    const nombre = nombreInput.value.trim();
    
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
    
    // Validación del formulario y envío con AJAX
    if (clientForm) {
        console.log('✅ Formulario de cliente encontrado');
        
        // Detectar si es actualización ANTES de agregar el listener
        const currentPath = window.location.pathname;
        const isUpdate = currentPath.includes('/client/update/');
        
        console.log('📍 URL actual:', currentPath);
        console.log('🔄 Es actualización?', isUpdate);
        
        if (isUpdate) {
            console.log('✅ MODO ACTUALIZACIÓN: El formulario se enviará normalmente (sin interceptar)');
            console.log('✅ El botón "Guardar Cliente" funcionará sin validación JavaScript');
            console.log('✅ La validación se realizará en el servidor');
            
            // Verificar que el botón existe
            const submitBtn = clientForm.querySelector('button[type="submit"]');
            console.log('🔍 Botón submit encontrado:', submitBtn ? '✅ SÍ' : '❌ NO');
            
            if (submitBtn) {
                console.log('🔍 Botón submit ID:', submitBtn.id);
                console.log('🔍 Botón submit texto:', submitBtn.textContent.trim());
                console.log('🔍 Botón submit disabled:', submitBtn.disabled);
            }
            
            // NO agregar ningún listener - dejar que el formulario se envíe normalmente
            // El servidor validará los datos y mostrará errores si es necesario
            console.log('✅ Listo para actualizar - sin interceptar submit');
            console.log('✅ El formulario debería enviarse normalmente al hacer clic en "Guardar Cliente"');
        } else {
            console.log('📝 MODO CREACIÓN: Agregando event listener para AJAX (necesario para cédula duplicada)');
            
            clientForm.addEventListener('submit', function(e) {
                console.log('=== SUBMIT DEL FORMULARIO INTERCEPTADO (CREACIÓN) ===');
                
                const form = this;
                const formAction = form.action || form.getAttribute('action') || '/client/create';
                
                if (!validarFormulario()) {
                    console.log('Validación del formulario falló - PREVENIR ENVÍO');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                
                console.log('Validación exitosa - usando AJAX para creación');
                e.preventDefault(); // Solo prevenir para creaciones
            
                // Mostrar loading en el botón de envío
                const submitBtn = form.querySelector('button[type="submit"]');
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
                }
                
                // Enviar formulario con AJAX para manejar la respuesta
                // El FormData incluirá automáticamente todos los campos del formulario, incluido el CSRF token de Yii2
                const formData = new FormData(form);
                
                console.log('Enviando formulario a:', formAction);
            
            fetch(formAction, {
                method: 'POST',
                body: formData,
                redirect: 'follow', // Permitir que el navegador siga redirecciones
                credentials: 'same-origin' // Incluir cookies/sesión
            })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.type, response.url);
                
                // Si hay redirección (response.redirected o URL diferente)
                if (response.redirected || response.url !== formAction) {
                    const finalUrl = response.url || response.headers.get('Location');
                    console.log('Redirección detectada a:', finalUrl);
                    
                    if (finalUrl) {
                        // Construir URL completa si es relativa
                        let redirectUrl = finalUrl;
                        if (!redirectUrl.startsWith('http')) {
                            redirectUrl = redirectUrl.startsWith('/') ? 
                                (window.location.origin + redirectUrl) : 
                                (window.location.origin + '/' + redirectUrl);
                        }
                        console.log('Redirigiendo a:', redirectUrl);
                        window.location.href = redirectUrl;
                        return null;
                    }
                }
                
                // Si es una redirección HTTP (status 301, 302, 303, 307, 308)
                if (response.status >= 300 && response.status < 400) {
                    const location = response.headers.get('Location');
                    console.log('Redirección HTTP detectada:', location);
                    
                    if (location) {
                        let redirectUrl = location;
                        if (!redirectUrl.startsWith('http')) {
                            redirectUrl = redirectUrl.startsWith('/') ? 
                                (window.location.origin + redirectUrl) : 
                                (window.location.origin + '/' + redirectUrl);
                        }
                        console.log('Redirigiendo a:', redirectUrl);
                        window.location.href = redirectUrl;
                        return null;
                    }
                }
                
                // Si el status es OK (200), procesar el HTML
                if (response.ok || response.status === 200) {
                    return response.text();
                }
                
                // Si hay un error HTTP, intentar leer el texto de la respuesta
                return response.text().then(text => {
                    throw new Error('Error HTTP ' + response.status + ': ' + text.substring(0, 200));
                });
            })
                .then(html => {
                    if (!html) return; // Ya se manejó la redirección
                    
                    // Restaurar botón
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Guardar Cliente';
                    }
                        
                    // Verificar si la respuesta contiene un error de cédula duplicada
                    if (html.includes('ya está registrada') || html.includes('has already been taken') || html.includes('cedulaDuplicateModal')) {
                        // En caso de cédula duplicada, redirigir directamente al listado (el servidor ya configuró el mensaje)
                        window.location.href = '/client/index';
                    } else if (html.includes('Gestión de Clientes') || html.includes('client-index')) {
                        // Si la respuesta es la página de listado, significa que se creó exitosamente
                        window.location.href = '/client/index';
                    } else {
                        // Para cualquier otro caso, recargar la página con la respuesta
                        console.log('Recargando página con respuesta HTML');
                        document.open();
                        document.write(html);
                        document.close();
                    }
                })
                .catch(error => {
                    console.error('Error al enviar formulario:', error);
                    
                    // Restaurar botón en caso de error
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Guardar Cliente';
                    }
                    
                    // Mostrar error al usuario
                    showNotification('❌ Error al guardar: ' + (error.message || 'Error desconocido. Por favor, intenta nuevamente.'), 'danger');
                });
            
            return false; // Prevenir submit adicional
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
    
    // Ya no se verifica modal de cédula duplicada - se maneja con redirección automática
});

// Funciones de modal de cédula duplicada eliminadas - ya no se usan
// El sistema ahora redirige automáticamente al listado con mensaje de error

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
    
    // Deshabilitar campos del formulario
    fileInput.disabled = true;
    fileNameInput.disabled = true;
    if (descriptionInput) {
        descriptionInput.disabled = true;
    }
    
    // Mostrar overlay de loading
    const uploadOverlay = document.getElementById('file-upload-overlay');
    if (uploadOverlay) {
        uploadOverlay.style.display = 'flex';
        uploadOverlay.style.position = 'fixed';
        uploadOverlay.style.top = '0';
        uploadOverlay.style.left = '0';
        uploadOverlay.style.width = '100%';
        uploadOverlay.style.height = '100%';
        uploadOverlay.style.background = 'rgba(0,0,0,0.7)';
        uploadOverlay.style.zIndex = '9999';
        uploadOverlay.style.justifyContent = 'center';
        uploadOverlay.style.alignItems = 'center';
    }
    
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
        
        // Ocultar overlay
        if (uploadOverlay) {
            uploadOverlay.style.display = 'none';
        }
        
        // Restaurar botón y campos
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        fileInput.disabled = false;
        fileNameInput.disabled = false;
        if (descriptionInput) {
            descriptionInput.disabled = false;
        }
        
        if (data.success) {
            // Mostrar alerta de éxito con Bootstrap
            showSuccessAlert('✅ ' + data.message, 'El archivo se ha subido exitosamente.');
            
            // Limpiar formulario
            fileInput.value = '';
            fileNameInput.value = '';
            if (descriptionInput) {
                descriptionInput.value = '';
            }
            
            // Recargar lista de archivos
            if (clientId) {
                setTimeout(() => {
                    loadFiles(clientId, currentSearchTerm);
                }, 500);
            }
        } else {
            // Mostrar alerta de error con detalles
            const errorMessage = data.message || 'Error al subir el archivo';
            const errorDetails = data.error_details || null;
            showErrorAlert('❌ Error al subir archivo', errorMessage, errorDetails);
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        
        // Ocultar overlay
        if (uploadOverlay) {
            uploadOverlay.style.display = 'none';
        }
        
        // Restaurar botón y campos
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        fileInput.disabled = false;
        fileNameInput.disabled = false;
        if (descriptionInput) {
            descriptionInput.disabled = false;
        }
        
        // Mostrar alerta de error
        showErrorAlert('❌ Error al subir el archivo', error.message, null);
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
