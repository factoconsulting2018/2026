/**
 * Acordeón Responsivo para Gestión de Alquileres
 * Material Design 3
 */

class RentalAccordion {
    constructor() {
        this.init();
    }

    init() {
        console.log('🎯 RentalAccordion: Inicializando...');
        
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupAccordion());
        } else {
            this.setupAccordion();
        }
    }

    setupAccordion() {
        console.log('🔧 RentalAccordion: Configurando acordeón...');
        
        const accordionItems = document.querySelectorAll('.rental-accordion-item');
        console.log(`📊 RentalAccordion: Encontrados ${accordionItems.length} items del acordeón`);

        accordionItems.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            const content = item.querySelector('.accordion-content');
            
            if (header && content) {
                console.log(`🔧 RentalAccordion: Configurando item ${index + 1}`);
                
                // Agregar event listener al header
                header.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log(`🖱️ RentalAccordion: Header ${index + 1} clicked`);
                    this.toggleItem(item, index);
                });

                // Configurar estado inicial
                this.setInitialState(item);
            }
        });

        // Configurar responsive behavior
        this.setupResponsiveBehavior();
        
        console.log('✅ RentalAccordion: Configuración completada');
    }

    toggleItem(item, index) {
        const isActive = item.classList.contains('active');
        const content = item.querySelector('.accordion-content');
        
        console.log(`🔄 RentalAccordion: Toggle item ${index + 1}, estado actual: ${isActive ? 'abierto' : 'cerrado'}`);
        
        if (isActive) {
            this.closeItem(item, index);
        } else {
            // Cerrar otros items abiertos primero
            this.closeOtherItems(item);
            // Esperar un poco antes de abrir el nuevo
            setTimeout(() => {
                this.openItem(item, index);
            }, 50);
        }
    }
    
    closeOtherItems(currentItem) {
        const allItems = document.querySelectorAll('.rental-accordion-item');
        allItems.forEach((item) => {
            if (item !== currentItem && item.classList.contains('active')) {
                const index = Array.from(item.parentNode.children).indexOf(item);
                this.closeItem(item, index);
            }
        });
    }

    openItem(item, index) {
        console.log(`📥 RentalAccordion: Abriendo item ${index + 1}`);
        
        item.classList.add('active');
        const content = item.querySelector('.accordion-content');
        
        if (content) {
            // Remover cualquier animación anterior
            content.classList.remove('closing');
            
            // Obtener la altura real del contenido
            const contentHeight = content.scrollHeight;
            
            // Agregar clase de animación
            content.classList.add('opening');
            
            // Forzar reflow
            content.offsetHeight;
            
            // Mostrar contenido con altura máxima animada
            content.style.maxHeight = contentHeight + 'px';
            content.style.display = 'block';
            
            // Remover clase de animación después de completar
            setTimeout(() => {
                content.classList.remove('opening');
                // NO poner maxHeight a 'none' - mantener el valor
            }, 300);
        }
        
        // Animar icono
        this.animateIcon(item, true);
    }

    closeItem(item, index) {
        console.log(`📤 RentalAccordion: Cerrando item ${index + 1}`);
        
        const content = item.querySelector('.accordion-content');
        
        if (content) {
            // Establecer altura máxima antes de la animación
            content.style.maxHeight = content.scrollHeight + 'px';
            
            // Forzar reflow
            content.offsetHeight;
            
            // Agregar clase de animación
            content.classList.add('closing');
            item.classList.remove('active');
            
            // Reducir altura a 0
            content.style.maxHeight = '0';
            
            // Remover clase de animación después de completar
            setTimeout(() => {
                content.classList.remove('closing');
                content.style.maxHeight = '';
            }, 300);
        } else {
            item.classList.remove('active');
        }
        
        // Animar icono
        this.animateIcon(item, false);
    }

    closeAllItems() {
        const activeItems = document.querySelectorAll('.rental-accordion-item.active');
        activeItems.forEach((item, index) => {
            // No cerrar si es el mismo item que se está abriendo
            const isBeingOpened = item.dataset && item.dataset.rentalId;
            if (!isBeingOpened) {
                this.closeItem(item, index);
            }
        });
    }

    animateIcon(item, isOpening) {
        const icon = item.querySelector('.accordion-toggle-icon');
        if (icon) {
            if (isOpening) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    }

    setInitialState(item) {
        // Todos los items empiezan cerrados
        item.classList.remove('active');
        
        const content = item.querySelector('.accordion-content');
        if (content) {
            // No establecer maxHeight a 0 inicialmente
            // Permitir que el CSS maneje el estado inicial
            content.style.display = 'none';
        }
    }

    setupResponsiveBehavior() {
        console.log('📱 RentalAccordion: Configurando comportamiento responsive...');
        
        // Función para verificar si estamos en modo móvil
        const checkMobileMode = () => {
            const accordion = document.querySelector('.rental-accordion');
            const tableContainer = document.querySelector('.rental-table-container');
            
            if (window.innerWidth <= 768) {
                // Modo móvil - mostrar acordeón, ocultar tabla
                if (accordion) accordion.style.display = 'block';
                if (tableContainer) tableContainer.style.display = 'none';
                console.log('📱 RentalAccordion: Modo móvil activado');
            } else {
                // Modo desktop - ocultar acordeón, mostrar tabla
                if (accordion) accordion.style.display = 'none';
                if (tableContainer) tableContainer.style.display = 'block';
                console.log('💻 RentalAccordion: Modo desktop activado');
            }
        };

        // Verificar al cargar
        checkMobileMode();

        // Verificar al redimensionar
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(checkMobileMode, 250);
        });
    }

    // Método público para abrir un item específico
    openItemById(rentalId) {
        const item = document.querySelector(`[data-rental-id="${rentalId}"]`);
        if (item) {
            const index = Array.from(item.parentNode.children).indexOf(item);
            this.openItem(item, index);
        }
    }

    // Método público para cerrar todos los items
    closeAll() {
        this.closeAllItems();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.rentalAccordion = new RentalAccordion();
    console.log('🚀 RentalAccordion: Instancia global creada');
});

// Exportar para uso en otros scripts si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RentalAccordion;
}
