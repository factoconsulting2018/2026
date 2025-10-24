/**
 * Manejo de modales para notas
 */

// Verificar que Bootstrap esté cargado
console.log('Bootstrap disponible:', typeof bootstrap !== 'undefined');
console.log('jQuery disponible:', typeof $ !== 'undefined');

// Manejo del formulario de crear nota
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que Bootstrap esté disponible
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap no está disponible. Los modales no funcionarán.');
        return;
    }
    
    console.log('Bootstrap cargado correctamente');
    const createForm = document.getElementById('createNoteForm');
    const createBtn = document.getElementById('createNoteBtn');
    const createSpinner = document.getElementById('createSpinner');
    const createBtnText = document.getElementById('createBtnText');
    
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Mostrar loading
            createBtn.disabled = true;
            createSpinner.classList.remove('d-none');
            createBtnText.textContent = 'Creando...';
            
            // Obtener datos del formulario
            const formData = new FormData(createForm);
            
            // Enviar petición AJAX
            fetch(createForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showNotification('success', 'Nota creada exitosamente');
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createNoteModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Recargar página después de un breve delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Mostrar mensaje de error
                    showNotification('error', data.message || 'Error al crear la nota');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error de conexión');
            })
            .finally(() => {
                // Restaurar botón
                createBtn.disabled = false;
                createSpinner.classList.add('d-none');
                createBtnText.textContent = 'Crear Nota';
            });
        });
    }
    
    // Función para mostrar notificaciones
    window.showNotification = function(type, message) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Agregar al DOM
        document.body.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    };
    
    // Función para editar nota
    window.editNote = function(noteId) {
        console.log('Editando nota:', noteId);
        window.location.href = `/notes/update?id=${noteId}`;
    };
    
    // Función para ver nota
    window.viewNote = function(noteId) {
        console.log('Viendo nota:', noteId);
        // Implementar lógica de ver nota
    };
    
    // Función para cambiar estado
    window.changeStatus = function(noteId, newStatus) {
        console.log('Cambiando estado de nota:', noteId, 'a', newStatus);
        // Implementar lógica de cambio de estado
    };
    
    // Función para manejar clics de edición
    window.handleEditClick = function(event, noteId) {
        event.stopPropagation();
        editNote(noteId);
    };
    
    // Función para editar nota directamente
    window.editNoteDirect = function(noteId) {
        editNote(noteId);
    };
    
    // Función de prueba de modal
    window.testModal = function() {
        console.log('=== TEST DE MODALES ===');
        const testModal = document.getElementById('createNoteModal');
        if (testModal) {
            console.log('Modal de prueba encontrado');
            try {
                const modal = new bootstrap.Modal(testModal);
                console.log('Modal instanciado correctamente');
                modal.show();
            } catch (error) {
                console.error('Error al instanciar modal:', error);
            }
        } else {
            console.error('Modal de prueba no encontrado');
        }
    };
    
    // Agregar botón de prueba si no existe
    if (!document.getElementById('testModalBtn')) {
        const testBtn = document.createElement('button');
        testBtn.id = 'testModalBtn';
        testBtn.className = 'btn btn-warning btn-sm';
        testBtn.textContent = 'Test Modal';
        testBtn.onclick = testModal;
        testBtn.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999;';
        document.body.appendChild(testBtn);
    }
    
    console.log('Script de modales de notas cargado correctamente');
});
