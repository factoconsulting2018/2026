/**
 * MENÚ DE ACCIONES DESPLEGABLE
 * Sistema de menús para acciones en tablas
 */

class ActionsMenu {
    constructor() {
        this.activeMenu = null;
        this.init();
    }

    init() {
        console.log('🔧 ActionsMenu init() called');
        
        // Inicializar menús existentes
        this.initMenus();
        
        // Escuchar eventos globales
        this.bindGlobalEvents();
        
        // Observar cambios en el DOM (para AJAX/Pjax)
        this.observeDOM();
        
        console.log('✅ ActionsMenu init() complete');
    }

    initMenus() {
        const menus = document.querySelectorAll('.actions-menu-toggle');
        console.log('🎯 Found', menus.length, 'action menu toggles');
        
        menus.forEach((toggle, index) => {
            if (!toggle.dataset.initialized) {
                console.log('🔧 Binding events to toggle', index + 1);
                this.bindMenuEvents(toggle);
                toggle.dataset.initialized = 'true';
            }
        });
    }

    bindMenuEvents(toggle) {
        const menu = toggle.closest('.actions-menu');
        
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            console.log('🔘 ActionsMenu: Toggle clicked');
            this.toggleMenu(menu);
        });

        // Cerrar al hacer clic en un item (excepto si es un link con confirmación)
        const items = menu.querySelectorAll('.actions-dropdown-item');
        items.forEach(item => {
            item.addEventListener('click', (e) => {
                // Si tiene data-confirm, manejarlo
                if (item.dataset.confirm) {
                    e.preventDefault();
                    this.handleConfirmAction(item);
                } else if (item.dataset.method === 'post') {
                    e.preventDefault();
                    this.handlePostAction(item);
                } else {
                    // Para links normales, cerrar el menú
                    setTimeout(() => this.closeMenu(menu), 100);
                }
            });
        });
    }

    toggleMenu(menu) {
        console.log('🔄 ActionsMenu: toggleMenu called');
        if (menu.classList.contains('active')) {
            console.log('📤 ActionsMenu: Closing menu');
            this.closeMenu(menu);
        } else {
            // Cerrar otros menús abiertos
            if (this.activeMenu && this.activeMenu !== menu) {
                this.closeMenu(this.activeMenu);
            }
            console.log('📥 ActionsMenu: Opening menu');
            this.openMenu(menu);
        }
    }

    openMenu(menu) {
        console.log('📥 ActionsMenu: openMenu called');
        menu.classList.add('active');
        this.activeMenu = menu;
        
        // Forzar estilos inmediatamente
        const dropdown = menu.querySelector('.actions-dropdown');
        if (dropdown) {
            dropdown.style.position = 'fixed';
            dropdown.style.zIndex = '999999';
            dropdown.style.display = 'block';
            dropdown.style.visibility = 'visible';
            dropdown.style.opacity = '1';
            console.log('✅ ActionsMenu: Forced styles applied');
        }
        
        // Calcular y ajustar posición del menú flotante
        this.positionFloatingMenu(menu);
    }

    closeMenu(menu) {
        menu.classList.remove('active');
        if (this.activeMenu === menu) {
            this.activeMenu = null;
        }
        
        // Limpiar estilos de posición fija
        const dropdown = menu.querySelector('.actions-dropdown');
        if (dropdown) {
            dropdown.style.position = '';
            dropdown.style.left = '';
            dropdown.style.top = '';
            dropdown.style.right = '';
            dropdown.style.bottom = '';
            dropdown.style.transform = '';
            dropdown.style.zIndex = '';
        }
    }

    closeAllMenus() {
        document.querySelectorAll('.actions-menu.active').forEach(menu => {
            this.closeMenu(menu);
        });
    }

    positionFloatingMenu(menu) {
        const dropdown = menu.querySelector('.actions-dropdown');
        const toggle = menu.querySelector('.actions-menu-toggle');
        if (!dropdown || !toggle) return;

        // Obtener posición del botón
        const toggleRect = toggle.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Calcular posición del menú Material Design 3
        let left = toggleRect.right - 200; // 200px es el min-width del menú
        let top = toggleRect.bottom + 12; // 12px de separación
        
        // Ajustar si se sale por la izquierda
        if (left < 16) {
            left = toggleRect.left;
        }
        
        // Ajustar si se sale por la derecha
        if (left + 200 > viewportWidth - 16) {
            left = viewportWidth - 216;
        }
        
        // Ajustar si se sale por abajo
        if (top + 250 > viewportHeight - 16) {
            top = toggleRect.top - 250 - 12; // Mostrar arriba del botón
        }
        
        // Aplicar posición fija con animación
        dropdown.style.position = 'fixed';
        dropdown.style.left = left + 'px';
        dropdown.style.top = top + 'px';
        dropdown.style.right = 'auto';
        dropdown.style.bottom = 'auto';
        dropdown.style.transform = 'scale(0.8) translateY(-10px)';
        dropdown.style.zIndex = '999999';
        
        // Trigger reflow para animación
        dropdown.offsetHeight;
        
        // Aplicar animación de entrada
        dropdown.style.transform = 'scale(1) translateY(0)';
    }

    bindGlobalEvents() {
        // Cerrar menús al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.actions-menu')) {
                this.closeAllMenus();
            }
        });

        // Cerrar menús al presionar ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllMenus();
            }
        });

        // Cerrar menús al hacer scroll
        window.addEventListener('scroll', () => {
            this.closeAllMenus();
        }, { passive: true });
    }

    handleConfirmAction(item) {
        const message = item.dataset.confirm;
        const url = item.href;
        const method = item.dataset.method || 'get';

        if (confirm(message)) {
            if (method === 'post') {
                this.submitForm(url, method);
            } else {
                window.location.href = url;
            }
        }
    }

    handlePostAction(item) {
        const url = item.href;
        const confirm = item.dataset.confirm;

        if (confirm) {
            if (!window.confirm(confirm)) {
                return;
            }
        }

        this.submitForm(url, 'post');
    }

    submitForm(url, method) {
        // Crear un formulario temporal para enviar la petición POST
        const form = document.createElement('form');
        form.method = method;
        form.action = url;

        // Agregar CSRF token si existe
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf';
            input.value = csrfToken.getAttribute('content');
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }

    observeDOM() {
        // Observar cambios en el DOM para inicializar nuevos menús (AJAX/Pjax)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    this.initMenus();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Método público para crear un menú desde código
    static createMenu(options) {
        const {
            items = [],
            toggleText = 'Acciones',
            toggleIcon = 'more_vert'
        } = options;

        const menu = document.createElement('div');
        menu.className = 'actions-menu';

        const toggle = document.createElement('button');
        toggle.className = 'actions-menu-toggle';
        toggle.type = 'button';
        toggle.innerHTML = `
            <span class="material-symbols-outlined">${toggleIcon}</span>
            <span>${toggleText}</span>
            <span class="material-symbols-outlined toggle-arrow">expand_more</span>
        `;

        const dropdown = document.createElement('div');
        dropdown.className = 'actions-dropdown';

        items.forEach(item => {
            const link = document.createElement('a');
            link.href = item.url || '#';
            link.className = `actions-dropdown-item action-${item.type || 'default'}`;
            
            if (item.confirm) link.dataset.confirm = item.confirm;
            if (item.method) link.dataset.method = item.method;
            
            link.innerHTML = `
                <span class="material-symbols-outlined">${item.icon || 'radio_button_unchecked'}</span>
                <span class="action-text">${item.label}</span>
            `;

            dropdown.appendChild(link);

            if (item.divider) {
                const divider = document.createElement('div');
                divider.className = 'actions-dropdown-divider';
                dropdown.appendChild(divider);
            }
        });

        menu.appendChild(toggle);
        menu.appendChild(dropdown);

        return menu;
    }
}

// Auto-inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing ActionsMenu...');
    try {
        window.actionsMenu = new ActionsMenu();
        console.log('✅ ActionsMenu initialized successfully');
    } catch (error) {
        console.error('❌ Error initializing ActionsMenu:', error);
    }
});

// Re-inicializar después de eventos Pjax (si Yii2 usa Pjax)
document.addEventListener('pjax:success', function() {
    console.log('🔄 Pjax success, reinitializing menus...');
    if (window.actionsMenu) {
        window.actionsMenu.initMenus();
    }
});

// Fallback: inicializar después de un pequeño delay
setTimeout(function() {
    if (!window.actionsMenu) {
        console.log('⏰ Fallback initialization...');
        try {
            window.actionsMenu = new ActionsMenu();
            console.log('✅ ActionsMenu fallback initialization successful');
        } catch (error) {
            console.error('❌ Error in fallback initialization:', error);
        }
    }
}, 1000);

// Exportar para uso manual
window.ActionsMenu = ActionsMenu;
