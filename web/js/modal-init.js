// Inicialización de Modales - Bootstrap 5
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando modales...');
    
    // Verificar que Bootstrap esté disponible
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap no está disponible. Los modales no funcionarán.');
        console.log('Assets cargados:', document.querySelectorAll('script[src*="bootstrap"]'));
        return;
    }
    
    console.log('Bootstrap cargado correctamente');
    
    // Inicializar todos los modales
    const modals = document.querySelectorAll('.modal');
    console.log('Modales encontrados:', modals.length);
    
    modals.forEach((modal, index) => {
        console.log(`Modal ${index + 1}:`, modal.id);
        
        // Configurar eventos del modal
        modal.addEventListener('show.bs.modal', function(event) {
            console.log('Modal abriendo:', this.id);
        });
        
        modal.addEventListener('shown.bs.modal', function(event) {
            console.log('Modal abierto:', this.id);
        });
        
        modal.addEventListener('hide.bs.modal', function(event) {
            console.log('Modal cerrando:', this.id);
        });
        
        modal.addEventListener('hidden.bs.modal', function(event) {
            console.log('Modal cerrado:', this.id);
        });
    });
    
    // Función global para abrir modales
    window.openModal = function(modalId) {
        console.log('Abriendo modal:', modalId);
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal no encontrado:', modalId);
        }
    };
    
    // Función global para cerrar modales
    window.closeModal = function(modalId) {
        console.log('Cerrando modal:', modalId);
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    };
    
    // Verificar botones que abren modales
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    console.log('Botones de modal encontrados:', modalTriggers.length);
    
    modalTriggers.forEach((trigger, index) => {
        const target = trigger.getAttribute('data-bs-target');
        console.log(`Botón ${index + 1}:`, trigger.textContent.trim(), '→', target);
        
        trigger.addEventListener('click', function(e) {
            console.log('Botón de modal clickeado:', this.textContent.trim());
            console.log('Target:', target);
        });
    });
    
    // Test de funcionalidad de modales
    setTimeout(() => {
        console.log('=== TEST DE MODALES ===');
        // Buscar cualquier modal disponible para la prueba
        const testModal = document.querySelector('.modal');
        if (testModal) {
            console.log('Modal de prueba encontrado:', testModal.id);
            try {
                const modal = new bootstrap.Modal(testModal);
                console.log('Modal instanciado correctamente');
            } catch (error) {
                console.error('Error al instanciar modal:', error);
            }
        } else {
            console.log('No se encontraron modales en esta página');
        }
    }, 1000);
});
