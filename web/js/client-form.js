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
    
    // Mostrar loading (verificar que los elementos existan)
    const loadingEl = document.getElementById('hacienda-loading');
    const resultEl = document.getElementById('hacienda-result');
    const errorEl = document.getElementById('hacienda-error');
    const consultarBtn = document.getElementById('consultar-btn');
    
    if (loadingEl) loadingEl.style.display = 'block';
    if (resultEl) resultEl.style.display = 'none';
    if (errorEl) errorEl.style.display = 'none';
    if (consultarBtn) consultarBtn.disabled = true;
    
    // Obtener CSRF token de Yii2 (está en un campo oculto del formulario o en los headers)
    let csrfToken = null;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    } else {
        // Buscar el token en el formulario (Yii2 lo agrega como campo oculto)
        const csrfInput = document.querySelector('input[name="_csrf"]') || 
                          document.querySelector('input[name="csrf-token"]') ||
                          document.querySelector('input[name="YII_CSRF_TOKEN"]');
        if (csrfInput) {
            csrfToken = csrfInput.value;
        } else if (typeof yii !== 'undefined' && yii.getCsrfToken) {
            // Usar el token de Yii2 si está disponible
            csrfToken = yii.getCsrfToken();
        }
    }
    
    console.log('🔍 CSRF Token encontrado:', csrfToken ? '✅ Sí' : '❌ No');
    
    // Preparar headers
    const headers = {
        'Content-Type': 'application/json',
    };
    
    // Agregar CSRF token si se encontró
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
        // También incluir en el body para Yii2
        headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    
    console.log('📤 Enviando petición a /hacienda/consultar con cédula:', cedula);
    
    // Realizar consulta AJAX
    fetch('/hacienda/consultar', {
        method: 'POST',
        headers: headers,
        credentials: 'same-origin', // Incluir cookies/sesión
        body: JSON.stringify({
            cedula: cedula
        })
    })
    .then(response => {
        console.log('📥 Respuesta recibida:', response.status, response.statusText);
        console.log('📥 URL de respuesta:', response.url);
        
        // Clonar la respuesta para poder leerla múltiples veces si es necesario
        const responseClone = response.clone();
        
        // Si hay un error HTTP, leer el texto primero
        if (!response.ok) {
            return response.text().then(text => {
                console.error('❌ Error HTTP:', response.status);
                console.error('❌ Respuesta del servidor:', text.substring(0, 500));
                
                // Intentar parsear como JSON
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || 'Error HTTP ' + response.status);
                } catch (e) {
                    // No es JSON, usar el texto
                    throw new Error('Error HTTP ' + response.status + ': ' + text.substring(0, 200));
                }
            });
        }
        
        // Verificar si la respuesta es JSON
        const contentType = response.headers.get('content-type') || '';
        console.log('📄 Content-Type:', contentType);
        
        if (!contentType.includes('application/json')) {
            console.warn('⚠️ La respuesta no es JSON. Content-Type:', contentType);
            return response.text().then(text => {
                console.log('📄 Respuesta recibida (texto):', text.substring(0, 500));
                
                // Intentar parsear como JSON de todos modos (a veces el header está mal)
                try {
                    const jsonData = JSON.parse(text);
                    console.log('✅ Se pudo parsear como JSON');
                    return jsonData;
                } catch (e) {
                    throw new Error('La respuesta del servidor no es JSON. Tipo: ' + contentType);
                }
            });
        }
        
        return response.json().catch(error => {
            console.error('❌ Error al parsear JSON:', error);
            // Intentar leer como texto y parsear manualmente
            return responseClone.text().then(text => {
                console.log('📄 Respuesta (texto):', text.substring(0, 500));
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('No se pudo parsear la respuesta como JSON: ' + text.substring(0, 200));
                }
            });
        });
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (loadingEl) loadingEl.style.display = 'none';
        
        if (data.success && data.data) {
            // Llenar campos automáticamente
            llenarCamposDesdeHacienda(data.data);
            mostrarResultadoHacienda(data.data);
        } else {
            console.log('Error en respuesta:', data.message || 'Sin datos');
            if (errorEl) errorEl.style.display = 'block';
            showNotification('⚠️ ' + (data.message || 'No se encontró información en Hacienda'), 'warning');
        }
    })
    .catch(error => {
        console.error('❌ Error en consulta:', error);
        if (loadingEl) loadingEl.style.display = 'none';
        if (errorEl) errorEl.style.display = 'block';
        
        // Manejar diferentes tipos de errores
        let errorMessage = 'Error desconocido';
        
        if (error.message) {
            errorMessage = error.message;
        } else if (typeof error === 'string') {
            errorMessage = error;
        }
        
        console.error('📛 Detalles del error:', {
            error: error,
            message: errorMessage,
            stack: error.stack
        });
        
        showNotification('❌ Error al consultar Hacienda: ' + errorMessage, 'danger');
    })
    .finally(() => {
        if (consultarBtn) consultarBtn.disabled = false;
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
    const tipoIdentificacionInput = document.getElementById('tipo-identificacion-input');
    if (data.tipoIdentificacion && tipoIdentificacionInput) {
        tipoIdentificacionInput.value = data.tipoIdentificacion;
        tipoIdentificacionInput.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            if (tipoIdentificacionInput) tipoIdentificacionInput.style.backgroundColor = '';
        }, 2000);
    }
    
    // Situación tributaria
    const situacionTributariaInput = document.getElementById('situacion-tributaria-input');
    if (data.situacionTributaria && situacionTributariaInput) {
        situacionTributariaInput.value = data.situacionTributaria;
        situacionTributariaInput.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            if (situacionTributariaInput) situacionTributariaInput.style.backgroundColor = '';
        }, 2000);
    }
    
    // Régimen tributario
    const regimenTributarioInput = document.getElementById('regimen-tributario-input');
    if (data.regimenTributario && regimenTributarioInput) {
        regimenTributarioInput.value = data.regimenTributario;
        regimenTributarioInput.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            if (regimenTributarioInput) regimenTributarioInput.style.backgroundColor = '';
        }, 2000);
    }
    
    // Actividad económica
    if (data.actividadEconomica) {
        const actividad = data.actividadEconomica;
        const codigoInput = document.getElementById('actividad-codigo-input');
        const descripcionInput = document.getElementById('actividad-descripcion-input');
        
        if (actividad.codigo && codigoInput) {
            codigoInput.value = actividad.codigo;
            codigoInput.style.backgroundColor = '#e8f5e8';
            setTimeout(() => {
                if (codigoInput) codigoInput.style.backgroundColor = '';
            }, 2000);
        }
        if (actividad.descripcion && descripcionInput) {
            descripcionInput.value = actividad.descripcion;
            descripcionInput.style.backgroundColor = '#e8f5e8';
            setTimeout(() => {
                if (descripcionInput) descripcionInput.style.backgroundColor = '';
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

// Función para validar formulario por pestañas
function validarFormularioPorPestanas() {
    const errors = [];
    const tabsWithErrors = new Set();
    
    // Tab 1: Información Personal
    const personalPane = document.getElementById('personal-pane');
    if (personalPane) {
        const personalErrors = validarTabPersonal();
        if (personalErrors.length > 0) {
            errors.push(...personalErrors);
            tabsWithErrors.add('personal');
        }
    }
    
    // Tab 2: Información Tributaria (si es requerida)
    const tributariaPane = document.getElementById('tributaria-pane');
    if (tributariaPane) {
        const tributariaErrors = validarTabTributaria();
        if (tributariaErrors.length > 0) {
            errors.push(...tributariaErrors);
            tabsWithErrors.add('tributaria');
        }
    }
    
    // Tab 3: Configuración (si es requerida)
    const configPane = document.getElementById('config-pane');
    if (configPane) {
        const configErrors = validarTabConfig();
        if (configErrors.length > 0) {
            errors.push(...configErrors);
            tabsWithErrors.add('config');
        }
    }
    
    // Mostrar errores si existen
    if (errors.length > 0) {
        mostrarErroresValidacion(errors, tabsWithErrors);
        return false;
    } else {
        ocultarErroresValidacion();
        return true;
    }
}

// Validar Tab Personal
function validarTabPersonal() {
    const errors = [];
    const cedulaInput = document.getElementById('cedula-input');
    const nombreInput = document.getElementById('nombre-input');
    
    if (!cedulaInput) {
        console.warn('⚠️ Campo cédula no encontrado');
    } else {
        const cedula = cedulaInput.value.trim();
        if (!cedula) {
            errors.push({ tab: 'personal', field: 'cedula', message: 'La cédula física es requerida' });
        } else if (!/^\d{9,10}$/.test(cedula)) {
            errors.push({ tab: 'personal', field: 'cedula', message: 'La cédula debe tener entre 9 y 10 dígitos' });
        }
    }
    
    if (!nombreInput) {
        console.warn('⚠️ Campo nombre completo no encontrado');
    } else {
        const nombre = nombreInput.value.trim();
    if (!nombre) {
            errors.push({ tab: 'personal', field: 'nombre', message: 'El nombre completo es requerido' });
        }
    }
    
    // Validar email si está presente
    const emailInput = document.getElementById('email-input');
    if (emailInput && emailInput.value.trim()) {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push({ tab: 'personal', field: 'email', message: 'El email no es válido' });
        }
    }
    
    return errors;
}

// Validar Tab Tributaria
function validarTabTributaria() {
    const errors = [];
    // Agregar validaciones específicas de información tributaria si es necesario
    // Por ahora no hay campos requeridos en esta pestaña según el modelo
    return errors;
}

// Validar Tab Configuración
function validarTabConfig() {
    const errors = [];
    // Agregar validaciones específicas de configuración si es necesario
    return errors;
}

// Mostrar errores de validación arriba del formulario
function mostrarErroresValidacion(errors, tabsWithErrors) {
    const errorContainer = document.getElementById('form-validation-errors');
    const errorList = document.getElementById('validation-errors-list');
    
    if (!errorContainer || !errorList) {
        console.warn('⚠️ Contenedor de errores no encontrado');
        return;
    }
    
    // Limpiar lista anterior
    errorList.innerHTML = '';
    
    // Agregar errores agrupados por pestaña
    const tabNames = {
        'personal': 'Información Personal',
        'tributaria': 'Información Tributaria',
        'config': 'Configuración'
    };
    
    const errorsByTab = {};
    errors.forEach(error => {
        if (!errorsByTab[error.tab]) {
            errorsByTab[error.tab] = [];
        }
        errorsByTab[error.tab].push(error);
    });
    
    // Crear lista de errores
    Object.keys(errorsByTab).forEach(tab => {
        const tabName = tabNames[tab] || tab;
        const tabErrors = errorsByTab[tab];
        
        const tabLi = document.createElement('li');
        tabLi.innerHTML = `<strong>${tabName}:</strong>`;
        const tabUl = document.createElement('ul');
        tabErrors.forEach(error => {
            const errorLi = document.createElement('li');
            errorLi.textContent = error.message;
            tabUl.appendChild(errorLi);
        });
        tabLi.appendChild(tabUl);
        errorList.appendChild(tabLi);
    });
    
    // Mostrar contenedor de errores
    errorContainer.style.display = 'block';
    
    // Resaltar pestañas con errores
    tabsWithErrors.forEach(tab => {
        const tabElement = document.getElementById(`${tab}-tab`);
        if (tabElement) {
            tabElement.style.borderBottom = '3px solid #dc3545';
            tabElement.style.backgroundColor = '#f8d7da';
        }
    });
    
    // Scroll hasta el mensaje de error
    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Ocultar errores de validación
function ocultarErroresValidacion() {
    const errorContainer = document.getElementById('form-validation-errors');
    if (errorContainer) {
        errorContainer.style.display = 'none';
    }
    
    // Quitar resaltado de pestañas
    ['personal', 'tributaria', 'config'].forEach(tab => {
        const tabElement = document.getElementById(`${tab}-tab`);
        if (tabElement) {
            tabElement.style.borderBottom = '';
            tabElement.style.backgroundColor = '';
        }
    });
}

// Función legacy para compatibilidad
function validarFormulario() {
    return validarFormularioPorPestanas();
}

// Variables globales para modales
let currentDuplicateCedula = '';

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('🚀 Inicializando client-form.js');
        
    const cedulaInput = document.getElementById('cedula-input');
    const clientForm = document.getElementById('client-form');
        
        // Detectar si es actualización ANTES de cualquier otra cosa
        const currentPath = window.location.pathname;
        const isUpdate = currentPath.includes('/client/update/');
        
        console.log('📍 URL actual:', currentPath);
        console.log('🔄 Es actualización?', isUpdate);
        
        // Para actualizaciones: NO interferir con submit pero SÍ inicializar biblioteca de archivos
        if (isUpdate) {
            console.log('✅ MODO ACTUALIZACIÓN: client-form.js NO interferirá con submit');
            console.log('✅ Pero SÍ inicializará biblioteca de archivos y otros listeners');
            // NO hacer return - continuar para inicializar biblioteca de archivos
        }
        
        // Para CREACIÓN y ACTUALIZACIÓN: agregar listeners (excepto submit para actualizaciones)
        if (isUpdate) {
            console.log('📝 MODO ACTUALIZACIÓN: Agregando listeners de biblioteca (NO submit)');
        } else {
            console.log('📝 MODO CREACIÓN: Agregando todos los listeners');
        }
        
        // Agregar listener al botón de consultar Hacienda
        const consultarBtn = document.getElementById('consultar-btn');
        if (consultarBtn) {
            consultarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                consultarHacienda();
            });
        }
        
        // Agregar listener al botón de limpiar formulario
        const limpiarBtn = document.getElementById('limpiar-formulario-btn');
        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                limpiarFormulario();
            });
        }
        
        // Agregar listeners para otros botones
        const searchFilesBtn = document.getElementById('search-files-btn');
        if (searchFilesBtn) {
            searchFilesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof searchFiles === 'function') searchFiles();
            });
        }
        
        const clearFileSearchBtn = document.getElementById('clear-file-search-btn');
        if (clearFileSearchBtn) {
            clearFileSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof clearFileSearch === 'function') clearFileSearch();
            });
        }
        
        // Listener para botón de subir archivo (puede estar en tab oculto)
        // Intentar encontrar el botón con múltiples métodos
        let uploadFileBtn = document.getElementById('upload-file-btn');
        
        if (!uploadFileBtn) {
            // Intentar buscar por múltiples selectores
            const selectors = [
                '#file-upload-form #upload-file-btn',
                '#file-upload-form button[type="button"]',
                'button[id="upload-file-btn"]',
                'button[data-client-id]',
                '.btn-primary[id="upload-file-btn"]'
            ];
            
            for (const selector of selectors) {
                try {
                    uploadFileBtn = document.querySelector(selector);
                    if (uploadFileBtn) {
                        console.log('✅ Botón encontrado con selector en DOMContentLoaded:', selector);
                        break;
                    }
                } catch (e) {
                    // Ignorar selectores inválidos
                }
            }
        }
        
        if (uploadFileBtn) {
            console.log('✅ Botón upload-file-btn encontrado en DOMContentLoaded, agregando listener');
            uploadFileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🖱️ Click en botón Subir Archivo (desde DOMContentLoaded)');
                if (typeof uploadFile === 'function') {
                    console.log('✅ Función uploadFile disponible, llamando...');
                    uploadFile();
                } else {
                    console.error('❌ Función uploadFile no está disponible');
                    showNotification('❌ Error: Función de subida no disponible. Por favor, recarga la página.', 'danger');
                }
            });
        } else {
            console.warn('⚠️ Botón upload-file-btn no encontrado en DOMContentLoaded (puede estar en tab oculto)');
            // Intentar agregarlo cuando se muestre el tab (ya se maneja en el listener del tab)
        }
        
        const buscarClienteBtn = document.getElementById('buscar-cliente-existente-btn');
        if (buscarClienteBtn) {
            buscarClienteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof buscarClienteExistente === 'function') buscarClienteExistente();
            });
        }
        
        const mostrarModalEliminarBtn = document.getElementById('mostrar-modal-eliminar-btn');
        if (mostrarModalEliminarBtn) {
            mostrarModalEliminarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof mostrarModalEliminar === 'function') mostrarModalEliminar();
            });
        }
        
        const eliminarClienteBtn = document.getElementById('eliminar-cliente-por-cedula-btn');
        if (eliminarClienteBtn) {
            eliminarClienteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof eliminarClientePorCedula === 'function') eliminarClientePorCedula();
            });
        }
    
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
    
        // Validación del formulario y envío con AJAX (SOLO PARA CREACIÓN)
        if (clientForm && !isUpdate) {
            console.log('✅ Formulario de cliente encontrado (CREACIÓN)');
            console.log('📝 MODO CREACIÓN: Agregando event listener para AJAX (necesario para cédula duplicada)');
            
        clientForm.addEventListener('submit', function(e) {
                console.log('=== SUBMIT DEL FORMULARIO INTERCEPTADO (CREACIÓN) ===');
                
                const form = this;
                const formAction = form.action || form.getAttribute('action') || '/client/create';
                
                if (!validarFormularioPorPestanas()) {
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
    
        // Formateo en tiempo real de la cédula (solo para creación)
        if (cedulaInput && !isUpdate) {
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
    
        if (isUpdate) {
            console.log('✅ Listeners de biblioteca de archivos agregados para actualización');
        } else {
            console.log('✅ Todos los listeners de CREACIÓN agregados');
        }
    } catch (error) {
        console.error('❌ ERROR CRÍTICO en client-form.js:', error);
        console.error('Stack trace:', error.stack);
        // Mostrar notificación al usuario si es posible
        if (typeof showNotification === 'function') {
            showNotification('❌ Error al inicializar el formulario. Por favor, recarga la página.', 'danger');
        } else {
            alert('❌ Error al inicializar el formulario. Por favor, recarga la página.');
        }
    }
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
            console.log('📚 Tab Biblioteca de Archivos activado');
            loadFiles();
            
            // Asegurar que el listener del botón esté agregado cuando el tab se muestra
            // Usar un timeout más largo para asegurar que el DOM esté completamente renderizado
            setTimeout(function() {
                // Intentar múltiples métodos para encontrar el botón
                let uploadFileBtn = document.getElementById('upload-file-btn');
                
                if (!uploadFileBtn) {
                    // Buscar por múltiples selectores
                    const selectors = [
                        '#file-upload-form #upload-file-btn',
                        '#file-upload-form button[type="button"]',
                        'button[id="upload-file-btn"]',
                        'button[data-client-id]'
                    ];
                    
                    for (const selector of selectors) {
                        uploadFileBtn = document.querySelector(selector);
                        if (uploadFileBtn) {
                            console.log('✅ Botón encontrado con selector alternativo:', selector);
                            break;
                        }
                    }
                }
                
                if (uploadFileBtn) {
                    // Remover listeners anteriores si existen
                    const newBtn = uploadFileBtn.cloneNode(true);
                    uploadFileBtn.parentNode.replaceChild(newBtn, uploadFileBtn);
                    uploadFileBtn = newBtn;
                    
                    if (!uploadFileBtn.hasAttribute('data-listener-added')) {
                        console.log('✅ Agregando listener a botón upload-file-btn cuando tab se muestra');
                        uploadFileBtn.setAttribute('data-listener-added', 'true');
                        uploadFileBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('🖱️ Click en botón Subir Archivo (desde tab listener)');
                            if (typeof uploadFile === 'function') {
                                uploadFile();
                            } else {
                                console.error('❌ Función uploadFile no disponible');
                                showNotification('❌ Error: Función de subida no disponible', 'danger');
                            }
                        });
                        console.log('✅ Listener agregado exitosamente al botón');
                    } else {
                        console.log('ℹ️ Listener ya agregado al botón upload-file-btn');
                    }
                } else {
                    console.error('❌ Botón upload-file-btn aún no encontrado después de mostrar tab');
                    console.error('Buscando en biblioteca-pane...');
                    const bibliotecaPane = document.getElementById('biblioteca-pane');
                    if (bibliotecaPane) {
                        console.error('Contenido de biblioteca-pane:', bibliotecaPane.innerHTML.substring(0, 500));
                    }
                }
            }, 200); // Aumentar timeout a 200ms para dar más tiempo al renderizado
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
        // 4. window.currentClientId (p. ej. vista cliente que lo define en inline script)
        if (!clientId && typeof window !== 'undefined' && window.currentClientId) {
            clientId = parseInt(window.currentClientId, 10);
        }
    }
    
    // Convertir a número para validar
    clientId = parseInt(clientId, 10);
    
    const container = document.getElementById('files-container');
    if (!container) {
        console.warn('files-container no existe en el DOM');
        return;
    }
    
    if (!clientId || isNaN(clientId) || clientId <= 0) {
        container.innerHTML = '<div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">error</span><p>No se pudo determinar el ID del cliente</p><small>URL: ' + window.location.pathname + '</small></div>';
        console.error('Client ID no disponible o inválido:', clientId);
        return;
    }
    
    currentClientId = clientId;
    window.currentClientId = clientId;
    currentSearchTerm = search;
    
    const term = (search || '').trim();
    const initialCount = container.getAttribute('data-initial-file-count');
    const forceFetch = container.getAttribute('data-force-files-fetch') === '1';
    if (term === '' && initialCount === '0' && !forceFetch) {
        displayFiles([]);
        return;
    }
    if (forceFetch) {
        container.removeAttribute('data-force-files-fetch');
    }
    
    const url = `/client/list-files/${clientId}${term ? '?search=' + encodeURIComponent(term) : ''}`;
    
    console.log('Cargando archivos desde:', url);
    
    container.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-3">Cargando archivos...</p></div>';
    
    fetch(url, { credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Archivos cargados:', data);
            if (data.success) {
                if (typeof data.count !== 'undefined') {
                    container.setAttribute('data-initial-file-count', String(parseInt(data.count, 10) || 0));
                }
                displayFiles(Array.isArray(data.data) ? data.data : []);
            } else {
                container.innerHTML = `<div class="alert alert-danger"><span class="material-symbols-outlined">error</span> ${data.message || 'Error al cargar archivos'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error cargando archivos:', error);
            container.innerHTML = `<div class="alert alert-danger"><span class="material-symbols-outlined">error</span> Error al cargar archivos: ${error.message}</div>`;
        });
}

// Función para mostrar archivos en la lista
function displayFiles(files) {
    const container = document.getElementById('files-container');
    if (!container) {
        return;
    }
    const list = Array.isArray(files) ? files : [];
    
    if (list.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-5"><span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">folder_off</span><p>No hay archivos subidos aún</p></div>';
        return;
    }
    
    let html = '<div class="row">';
    
    list.forEach(file => {
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
    
    // Los campos de biblioteca de archivos NO son obligatorios
    // Solo validar si el usuario intenta subir un archivo
    const hasFile = fileInput.files && fileInput.files.length > 0;
    
    // Si no hay archivo ni nombre, informar al usuario pero no bloquear
    if (!hasFile && !fileNameInput.value.trim()) {
        showNotification('ℹ️ Para subir un archivo, seleccione un archivo y/o ingrese un nombre', 'info');
        return;
    }
    
    // Si hay archivo, validar tamaño
    if (hasFile) {
        const file = fileInput.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            showNotification('❌ El archivo es demasiado grande. Tamaño máximo: 10MB', 'danger');
            return;
        }
    }
    
    const formData = new FormData();
    
    // Solo agregar archivo si existe
    if (hasFile) {
        formData.append('file', fileInput.files[0]);
    }
    
    // Agregar nombre y descripción solo si tienen valor
    const fileName = fileNameInput.value.trim();
    if (fileName) {
        formData.append('file_name', fileName);
    }
    
    const description = descriptionInput ? descriptionInput.value.trim() : '';
    if (description) {
        formData.append('description', description);
    }
    
    // Agregar token CSRF al FormData (aunque esté deshabilitado en el servidor, mejor incluirlo)
    const csrfInput = document.querySelector('input[name="_csrf"]') || 
                      document.querySelector('input[name="csrf-token"]') ||
                      document.querySelector('input[name="YII_CSRF_TOKEN"]');
    if (csrfInput) {
        formData.append('_csrf', csrfInput.value);
        console.log('✅ Token CSRF agregado al FormData');
    } else {
        // Intentar obtener de meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            formData.append('_csrf', csrfMeta.getAttribute('content'));
            console.log('✅ Token CSRF agregado desde meta tag');
        } else if (typeof yii !== 'undefined' && yii.getCsrfToken) {
            const token = yii.getCsrfToken();
            if (token) {
                formData.append('_csrf', token);
                console.log('✅ Token CSRF agregado desde yii.getCsrfToken()');
            }
        }
    }
    
    // Mostrar loading - buscar botón por ID directamente
    // IMPORTANTE: El botón puede estar en un tab oculto, así que buscar incluso si está oculto
    let uploadBtn = document.getElementById('upload-file-btn');
    
    if (!uploadBtn) {
        // Fallback: intentar buscar por múltiples selectores (el botón puede estar en un tab oculto)
        console.warn('⚠️ Botón no encontrado por ID, intentando selectores alternativos...');
        
        // Intentar varios selectores diferentes
        const selectors = [
            '#upload-file-btn',
            'button#upload-file-btn',
            '[id="upload-file-btn"]',
            '#file-upload-form button[type="button"]',
            '#file-upload-form #upload-file-btn',
            'button[data-client-id]',
            '.btn-primary[id="upload-file-btn"]'
        ];
        
        let fallbackBtn = null;
        for (const selector of selectors) {
            try {
                fallbackBtn = document.querySelector(selector);
                if (fallbackBtn) {
                    console.log('✅ Botón encontrado con selector:', selector);
                    break;
                }
            } catch (e) {
                console.warn('Selector inválido:', selector);
            }
        }
        
        if (fallbackBtn) {
            uploadBtn = fallbackBtn;
        } else {
            // Último intento: buscar todos los botones y encontrar el que tenga el texto "Subir Archivo"
            console.warn('⚠️ Buscando botón por texto "Subir Archivo"...');
            const allButtons = Array.from(document.querySelectorAll('button'));
            const uploadButtonByText = allButtons.find(btn => 
                btn.textContent.includes('Subir Archivo') || 
                btn.textContent.includes('Subir') ||
                btn.id === 'upload-file-btn'
            );
            
            if (uploadButtonByText) {
                console.log('✅ Botón encontrado por texto');
                uploadBtn = uploadButtonByText;
            } else {
                showNotification('❌ Error: No se encontró el botón de subir. Por favor, recarga la página.', 'danger');
                console.error('❌ Botón de upload no encontrado con ningún método');
                console.error('Total de botones en la página:', allButtons.length);
                console.error('IDs de botones:', allButtons.map(b => b.id).filter(id => id));
                console.error('Botones type="button":', allButtons.map(b => ({
                    id: b.id, 
                    text: b.textContent.trim().substring(0, 30),
                    classes: b.className
                })));
                return;
            }
        }
    }
    
    console.log('✅ Botón de upload encontrado:', uploadBtn.id || 'sin ID', 'texto:', uploadBtn.textContent.trim().substring(0, 30));
    
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
                markClientFilesNeedServerFetch();
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
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    loadFiles(currentClientId || null, searchTerm);
}

// Función para limpiar búsqueda
function clearFileSearch() {
    const searchInput = document.getElementById('file-search-input');
    if (searchInput) {
        searchInput.value = '';
    }
    loadFiles(currentClientId || null, '');
}

function markClientFilesNeedServerFetch() {
    const c = document.getElementById('files-container');
    if (c) {
        c.setAttribute('data-force-files-fetch', '1');
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
    
    // Obtener token CSRF
    const csrfInput = document.querySelector('input[name="_csrf"]') || 
                      document.querySelector('input[name="csrf-token"]') ||
                      document.querySelector('input[name="YII_CSRF_TOKEN"]');
    const csrfToken = csrfInput ? csrfInput.value : null;
    
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    };
    
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
    }
    
    fetch(`/client/delete-file/${fileId}`, {
        method: 'POST',
        headers: headers,
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Respuesta delete-file:', response.status, response.statusText);
        
        // Si la respuesta no es exitosa, intentar obtener el error del cuerpo
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error del servidor:', text);
                let errorData;
                try {
                    // Intentar parsear como JSON
                    errorData = JSON.parse(text);
                } catch (e) {
                    // Si no es JSON, crear un objeto de error
                    errorData = { 
                        success: false, 
                        message: `Error del servidor (${response.status}): ${text.substring(0, 200)}` 
                    };
                }
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            });
        }
        
        // Si es exitosa, parsear JSON
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            // Recargar lista de archivos
            markClientFilesNeedServerFetch();
            if (currentClientId) {
                loadFiles(currentClientId, currentSearchTerm);
            } else {
                const uploadBtn = document.getElementById('upload-file-btn');
                const cid = uploadBtn && uploadBtn.dataset.clientId ? parseInt(uploadBtn.dataset.clientId, 10) : null;
                if (cid) {
                    loadFiles(cid, currentSearchTerm);
                }
            }
        } else {
            showNotification('❌ ' + (data.message || 'Error al eliminar el archivo'), 'danger');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        showNotification('❌ Error al eliminar el archivo: ' + (error.message || error), 'danger');
    });
}

// ========== SITUACIÓN FINANCIERA ==========
// Manejar la visualización del campo de detalle de situación financiera
document.addEventListener('DOMContentLoaded', function() {
    const situacionField = document.getElementById('situacion-financiera');
    const detalleContainer = document.getElementById('detalle-situacion-container');
    const detalleLabel = document.getElementById('detalle-situacion-label');
    
    if (!situacionField || !detalleContainer || !detalleLabel) {
        return; // Los campos no existen en esta vista
    }
    
    const labelTexts = {
        'independiente': '¿Qué profesión o actividad ejerce actualmente? Indique cantidad de años.',
        'asalariado': '¿En qué empresa o institución trabaja actualmente? Indique cantidad de años.',
        'tiene_empresa': 'Ingrese el nombre de su empresa y cédula jurídica. Indique cantidad de años.'
    };
    
    situacionField.addEventListener('change', function() {
        const value = this.value;
        
        if (value && labelTexts[value]) {
            detalleLabel.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">description</span>' + labelTexts[value];
            detalleContainer.style.display = 'block';
        } else {
            detalleContainer.style.display = 'none';
            document.getElementById('client-situacion_financiera_detalle').value = '';
        }
    });
    
    // Mostrar campo si ya hay un valor seleccionado (edición)
    if (situacionField.value) {
        situacionField.dispatchEvent(new Event('change'));
    }
});