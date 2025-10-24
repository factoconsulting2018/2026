/**
 * DEBUG: Verificar funcionamiento del menú de acciones
 */
console.log('🔍 Actions Menu Debug Script Loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Content Loaded');
    
    // Verificar si existen elementos del menú
    const menus = document.querySelectorAll('.actions-menu');
    console.log('🎯 Found actions menus:', menus.length);
    
    const toggles = document.querySelectorAll('.actions-menu-toggle');
    console.log('🔘 Found toggle buttons:', toggles.length);
    
    const dropdowns = document.querySelectorAll('.actions-dropdown');
    console.log('📋 Found dropdowns:', dropdowns.length);
    
    // Verificar estilos CSS
    const testMenu = document.querySelector('.actions-menu');
    if (testMenu) {
        const computedStyle = window.getComputedStyle(testMenu);
        console.log('🎨 Menu computed styles:', {
            position: computedStyle.position,
            zIndex: computedStyle.zIndex,
            display: computedStyle.display
        });
        
        const dropdown = testMenu.querySelector('.actions-dropdown');
        if (dropdown) {
            const dropdownStyle = window.getComputedStyle(dropdown);
            console.log('📋 Dropdown computed styles:', {
                position: dropdownStyle.position,
                zIndex: dropdownStyle.zIndex,
                display: dropdownStyle.display,
                visibility: dropdownStyle.visibility,
                opacity: dropdownStyle.opacity
            });
        }
    }
    
    // Verificar si ActionsMenu está disponible
    if (window.ActionsMenu) {
        console.log('✅ ActionsMenu class available');
    } else {
        console.log('❌ ActionsMenu class NOT available');
    }
    
    if (window.actionsMenu) {
        console.log('✅ actionsMenu instance available');
    } else {
        console.log('❌ actionsMenu instance NOT available');
    }
    
    // Agregar event listeners manualmente si es necesario
    toggles.forEach((toggle, index) => {
        console.log(`🔧 Setting up toggle ${index + 1}`);
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Toggle clicked:', index + 1);
            
            const menu = this.closest('.actions-menu');
            if (menu) {
                console.log('📋 Menu found, toggling...');
                
                // Cerrar otros menús abiertos primero
                document.querySelectorAll('.actions-menu.active').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('active');
                        const otherDropdown = otherMenu.querySelector('.actions-dropdown');
                        if (otherDropdown) {
                            otherDropdown.style.position = '';
                            otherDropdown.style.zIndex = '';
                            otherDropdown.style.display = '';
                            otherDropdown.style.visibility = '';
                            otherDropdown.style.opacity = '';
                            otherDropdown.style.left = '';
                            otherDropdown.style.top = '';
                            otherDropdown.style.transform = '';
                        }
                    }
                });
                
                // Toggle del menú actual
                menu.classList.toggle('active');
                
                const dropdown = menu.querySelector('.actions-dropdown');
                if (dropdown) {
                    const isActive = menu.classList.contains('active');
                    console.log('🔄 Menu state:', isActive ? 'OPEN' : 'CLOSED');
                    
                    if (isActive) {
                        // Forzar estilos para mostrar el menú
                        dropdown.style.position = 'fixed';
                        dropdown.style.zIndex = '999999';
                        dropdown.style.display = 'block';
                        dropdown.style.visibility = 'visible';
                        dropdown.style.opacity = '1';
                        
                        // Posicionar el menú Material Design 3
                        const rect = this.getBoundingClientRect();
                        let left = rect.right - 200;
                        let top = rect.bottom + 12;
                        
                        // Ajustar si se sale de pantalla
                        if (left < 16) {
                            left = rect.left;
                        }
                        if (left + 200 > window.innerWidth - 16) {
                            left = window.innerWidth - 216;
                        }
                        if (top + 250 > window.innerHeight - 16) {
                            top = rect.top - 250 - 12;
                        }
                        
                        dropdown.style.left = left + 'px';
                        dropdown.style.top = top + 'px';
                        dropdown.style.transform = 'scale(0.8) translateY(-10px)';
                        
                        // Trigger reflow para animación
                        dropdown.offsetHeight;
                        
                        // Aplicar animación de entrada
                        dropdown.style.transform = 'scale(1) translateY(0)';
                        
                        console.log('📍 Menu positioned at:', {
                            left: dropdown.style.left,
                            top: dropdown.style.top,
                            zIndex: dropdown.style.zIndex,
                            transform: dropdown.style.transform
                        });
                    } else {
                        // Limpiar estilos
                        dropdown.style.position = '';
                        dropdown.style.zIndex = '';
                        dropdown.style.display = '';
                        dropdown.style.visibility = '';
                        dropdown.style.opacity = '';
                        dropdown.style.left = '';
                        dropdown.style.top = '';
                        dropdown.style.transform = '';
                    }
                }
            }
        });
    });
    
    // Cerrar menús al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.actions-menu')) {
            const activeMenus = document.querySelectorAll('.actions-menu.active');
            activeMenus.forEach(menu => {
                menu.classList.remove('active');
                const dropdown = menu.querySelector('.actions-dropdown');
                if (dropdown) {
                    dropdown.style.position = '';
                    dropdown.style.zIndex = '';
                    dropdown.style.display = '';
                    dropdown.style.visibility = '';
                    dropdown.style.opacity = '';
                    dropdown.style.left = '';
                    dropdown.style.top = '';
                }
            });
        }
    });
    
    console.log('✅ Debug setup complete');
    
    // Método de emergencia para forzar apertura
    window.forceMenuOpen = function(menuElement) {
        if (menuElement) {
            const dropdown = menuElement.querySelector('.actions-dropdown');
            if (dropdown) {
                menuElement.classList.add('active');
                dropdown.style.position = 'fixed';
                dropdown.style.zIndex = '999999';
                dropdown.style.display = 'block';
                dropdown.style.visibility = 'visible';
                dropdown.style.opacity = '1';
                dropdown.style.left = '50%';
                dropdown.style.top = '50%';
                dropdown.style.transform = 'translate(-50%, -50%)';
                console.log('🚨 Menu forced open');
            }
        }
    };
    
});
