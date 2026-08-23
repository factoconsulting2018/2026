/**
 * Navigation Drawer JavaScript
 * Maneja la funcionalidad del drawer de navegación
 */

// Navigation Drawer JavaScript
function toggleDrawer() {
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('drawerOverlay');
    const mainContent = document.getElementById('mainContent');
    
    if (drawer.classList.contains('open')) {
        closeDrawer();
    } else {
        openDrawer();
    }
}

function openDrawer() {
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('drawerOverlay');
    const mainContent = document.getElementById('mainContent');
    
    drawer.classList.add('open');
    
    // Solo mostrar overlay en móvil
    if (window.innerWidth < 768) {
        overlay.classList.add('show');
    } else {
        // En desktop, mover el contenido pero no mostrar overlay
        mainContent.classList.add('drawer-open');
    }
}

function closeDrawer() {
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('drawerOverlay');
    const mainContent = document.getElementById('mainContent');
    
    drawer.classList.remove('open');
    overlay.classList.remove('show');
    mainContent.classList.remove('drawer-open');
}

// Función para mostrar "Próximamente"
function showComingSoon() {
    alert('🚧 Esta funcionalidad estará disponible próximamente');
}

// Auto-inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Auto-abrir drawer en desktop
    if (window.innerWidth >= 768) {
        openDrawer();
    }

    // Categorías colapsables del menú
    document.querySelectorAll('.nav-category-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cat = btn.closest('.nav-category');
            if (!cat) return;
            var willOpen = !cat.classList.contains('open');
            cat.classList.toggle('open', willOpen);
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
    
    // Cerrar drawer al hacer clic en un enlace en móvil
    const navLinks = document.querySelectorAll('.drawer .nav-link');
    const logoutButton = document.querySelector('.logout-button');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Solo cerrar drawer en móvil, no en desktop
            if (window.innerWidth < 768) {
                // Solo cerrar si NO es un link externo o si no es el mismo dominio
                var href = link.getAttribute('href');
                // Verificar si es un link interno
                if (href && (href.startsWith('#') || href.startsWith('/'))) {
                    // Pequeño delay para permitir la navegación
                    setTimeout(() => {
                        closeDrawer();
                    }, 300); // Aumentado a 300ms
                }
            }
            // En desktop, no hacer nada - dejar el drawer abierto
        });
    });
    
    // Cerrar drawer al hacer logout
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            // Cerrar drawer en ambos casos (móvil y desktop) para logout
            setTimeout(() => {
                closeDrawer();
            }, 100);
        });
    }
    
    // Manejar redimensionamiento de ventana
    let resizeTimeout;
    window.addEventListener('resize', function() {
        // Debounce para evitar múltiples llamadas
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const drawer = document.getElementById('drawer');
            const overlay = document.getElementById('drawerOverlay');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth >= 768) {
                // Cambio a desktop: abrir drawer, quitar overlay
                drawer.classList.add('open');
                overlay.classList.remove('show');
                mainContent.classList.add('drawer-open');
            } else {
                // Cambio a móvil: mantener el estado actual del drawer
                // No cerrarlo automáticamente - respetar si el usuario lo abrió
                if (!drawer.classList.contains('open')) {
                    mainContent.classList.remove('drawer-open');
                }
            }
        }, 150);
    });
    
    // Botón de Regreso mejorado
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Intentar regresar en el historial
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Si no hay historial, ir al dashboard
                window.location.href = '/';
            }
        });
    }
});

console.log('🎯 Navigation Drawer JavaScript cargado correctamente');
