/**
 * Navigation Drawer JavaScript
 * Maneja la funcionalidad del drawer de navegación
 */

function toggleDrawer() {
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('drawerOverlay');
    const mainContent = document.getElementById('mainContent');

    if (!drawer) return;

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

    if (!drawer) return;

    drawer.classList.add('open');

    if (window.innerWidth < 768) {
        if (overlay) overlay.classList.add('show');
    } else if (mainContent) {
        mainContent.classList.add('drawer-open');
    }
}

function closeDrawer() {
    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('drawerOverlay');
    const mainContent = document.getElementById('mainContent');

    if (drawer) drawer.classList.remove('open');
    if (overlay) overlay.classList.remove('show');
    if (mainContent) mainContent.classList.remove('drawer-open');
}

function showComingSoon() {
    alert('🚧 Esta funcionalidad estará disponible próximamente');
}

/**
 * Expande / contrae categorías del menú lateral.
 */
function toggleNavCategory(btn) {
    if (!btn) return;
    var cat = btn.closest('.nav-category');
    if (!cat) return;
    var willOpen = !cat.classList.contains('open');
    cat.classList.toggle('open', willOpen);
    btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
}

function initNavCategories() {
    var drawer = document.getElementById('drawer');
    if (!drawer || drawer.dataset.navCategoriesBound === '1') {
        return;
    }
    drawer.dataset.navCategoriesBound = '1';

    // Delegación: funciona aunque el HTML cambie y evita problemas de timing
    drawer.addEventListener('click', function (e) {
        var btn = e.target.closest('.nav-category-toggle');
        if (!btn || !drawer.contains(btn)) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        toggleNavCategory(btn);
    });
}

function initNavigationDrawer() {
    if (window.innerWidth >= 768) {
        openDrawer();
    }

    initNavCategories();

    var navLinks = document.querySelectorAll('.drawer .nav-link');
    var logoutButton = document.querySelector('.logout-button');

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 768) {
                var href = link.getAttribute('href');
                if (href && (href.startsWith('#') || href.startsWith('/'))) {
                    setTimeout(function () {
                        closeDrawer();
                    }, 300);
                }
            }
        });
    });

    if (logoutButton) {
        logoutButton.addEventListener('click', function () {
            if (window.innerWidth < 768) {
                closeDrawer();
            }
        });
    }

    var backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavigationDrawer);
} else {
    initNavigationDrawer();
}

console.log('🎯 Navigation Drawer JavaScript cargado correctamente');
