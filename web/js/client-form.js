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
});
