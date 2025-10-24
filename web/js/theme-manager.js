/**
 * Gestor de temas para la aplicación
 */

document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    // Obtener tema guardado o usar light por defecto
    let currentTheme = localStorage.getItem('theme') || 'light';
    
    // Aplicar tema inicial
    applyTheme(currentTheme);
    
    // Event listener para el botón
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(currentTheme);
            localStorage.setItem('theme', currentTheme);
            
            // Mostrar notificación
            showThemeNotification(currentTheme);
        });
    }
    
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Cambiar icono
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.textContent = 'light_mode';
                if (themeToggle) themeToggle.title = 'Cambiar a tema claro';
            } else {
                themeIcon.textContent = 'dark_mode';
                if (themeToggle) themeToggle.title = 'Cambiar a tema oscuro';
            }
        }
        
        // Actualizar contadores si existen
        updateCountersTheme(theme);
    }
    
    function showThemeNotification(theme) {
        // Crear notificación
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
        
        const themeName = theme === 'dark' ? 'oscuro' : 'claro';
        const themeIcon = theme === 'dark' ? '🌙' : '☀️';
        
        notification.innerHTML = `
            <i class="fas fa-${theme === 'dark' ? 'moon' : 'sun'} me-2"></i>
            Tema ${themeName} activado ${themeIcon}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
    
    function updateCountersTheme(theme) {
        const counters = document.querySelectorAll('.card.text-white');
        counters.forEach(card => {
            if (theme === 'dark') {
                card.classList.add('text-white');
            } else {
                card.classList.remove('text-white');
            }
        });
    }
    
    // Detectar preferencia del sistema
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        // Si no hay tema guardado, usar preferencia del sistema
        if (!localStorage.getItem('theme')) {
            const systemTheme = mediaQuery.matches ? 'dark' : 'light';
            applyTheme(systemTheme);
            localStorage.setItem('theme', systemTheme);
        }
        
        // Escuchar cambios en la preferencia del sistema
        mediaQuery.addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                const systemTheme = e.matches ? 'dark' : 'light';
                applyTheme(systemTheme);
            }
        });
    }
    
    console.log('Theme manager inicializado');
});
