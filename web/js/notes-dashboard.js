// Dashboard de Notas - JavaScript Dinámico

let currentViewMode = 'grid';
let allNotes = [];
let filteredNotes = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    animateStats();
});

function initializeDashboard() {
    // Obtener todas las notas del DOM
    const noteElements = document.querySelectorAll('.note-card');
    allNotes = Array.from(noteElements).map(note => ({
        element: note,
        id: note.dataset.id,
        status: note.dataset.status,
        color: note.dataset.color,
        title: note.dataset.title.toLowerCase(),
        content: note.dataset.content.toLowerCase(),
        created: note.dataset.created
    }));
    
    filteredNotes = [...allNotes];
    
    // Aplicar animaciones de entrada
    animateNoteCards();
    
    // Agregar tooltips para doble clic
    addDoubleClickTooltips();
    
    // Inicializar indicador de filtro activo (mostrar todas por defecto)
    updateActiveFilterIndicator(null);
}

function setupEventListeners() {
    // Filtros
    document.getElementById('searchInput').addEventListener('input', filterNotes);
    document.getElementById('statusFilter').addEventListener('change', filterNotes);
    document.getElementById('colorFilter').addEventListener('change', filterNotes);
    document.getElementById('sortFilter').addEventListener('change', sortNotes);
    
    // Botones de vista
    document.querySelectorAll('[data-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            setViewMode(this.dataset.view);
        });
    });
    
    // Contadores como filtros
    setupCounterFilters();
}

function filterNotes() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const colorFilter = document.getElementById('colorFilter').value;
    
    filteredNotes = allNotes.filter(note => {
        const matchesSearch = !searchTerm || 
            note.title.includes(searchTerm) || 
            note.content.includes(searchTerm);
        
        const matchesStatus = !statusFilter || note.status === statusFilter;
        const matchesColor = !colorFilter || note.color === colorFilter;
        
        return matchesSearch && matchesStatus && matchesColor;
    });
    
    updateNotesDisplay();
    updateFilteredCount();
}

function sortNotes() {
    const sortBy = document.getElementById('sortFilter').value;
    
    filteredNotes.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                return new Date(b.created) - new Date(a.created);
            case 'oldest':
                return new Date(a.created) - new Date(b.created);
            case 'title':
                return a.title.localeCompare(b.title);
            case 'status':
                return a.status.localeCompare(b.status);
            default:
                return 0;
        }
    });
    
    updateNotesDisplay();
}

function updateNotesDisplay() {
    const container = document.getElementById('notesContainer');
    const emptyState = document.getElementById('emptyState');
    
    // Ocultar todas las notas
    allNotes.forEach(note => {
        note.element.style.display = 'none';
    });
    
    if (filteredNotes.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        container.style.display = 'grid';
        emptyState.style.display = 'none';
        
        // Mostrar notas filtradas con animación
        filteredNotes.forEach((note, index) => {
            note.element.style.display = 'block';
            note.element.style.animationDelay = `${index * 0.1}s`;
            note.element.classList.add('fade-in');
        });
    }
}

function updateFilteredCount() {
    const countElement = document.getElementById('filteredCount');
    const totalNotes = allNotes.length;
    const filteredCount = filteredNotes.length;
    
    if (filteredCount === totalNotes) {
        countElement.textContent = `${filteredCount} nota${filteredCount !== 1 ? 's' : ''}`;
        countElement.className = 'badge bg-info';
    } else {
        countElement.textContent = `${filteredCount} de ${totalNotes} nota${totalNotes !== 1 ? 's' : ''}`;
        countElement.className = 'badge bg-warning';
    }
}

function setViewMode(mode) {
    currentViewMode = mode;
    const container = document.getElementById('notesContainer');
    const buttons = document.querySelectorAll('[data-view]');
    
    // Actualizar botones
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === mode) {
            btn.classList.add('active');
        }
    });
    
    // Aplicar clase de vista
    container.className = `notes-container ${mode}-view`;
    
    // Actualizar icono del botón toggle
    const toggleBtn = document.getElementById('viewModeIcon');
    const toggleText = document.getElementById('viewModeText');
    
    if (mode === 'grid') {
        toggleBtn.className = 'fas fa-th';
        toggleText.textContent = 'Vista Compacta';
    } else {
        toggleBtn.className = 'fas fa-list';
        toggleText.textContent = 'Vista Lista';
    }
}

function toggleViewMode() {
    const newMode = currentViewMode === 'grid' ? 'list' : 'grid';
    setViewMode(newMode);
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('colorFilter').value = '';
    document.getElementById('sortFilter').value = 'newest';
    
    filterNotes();
    sortNotes();
}

function exportNotes() {
    // Crear datos para exportar
    const exportData = filteredNotes.map(note => ({
        título: note.element.querySelector('.note-title').textContent,
        estado: note.status,
        color: note.color,
        contenido: note.element.querySelector('.note-content')?.textContent || '',
        fecha: note.element.querySelector('.note-date').textContent
    }));
    
    // Convertir a CSV
    const csv = convertToCSV(exportData);
    
    // Descargar archivo
    downloadCSV(csv, 'notas-exportadas.csv');
}

function convertToCSV(data) {
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => `"${row[header]}"`).join(','))
    ].join('\n');
    
    return csvContent;
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number[data-target]');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.dataset.target);
        animateNumber(stat, 0, target, 1000);
    });
}

function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

function animateNoteCards() {
    const cards = document.querySelectorAll('.note-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Funciones para modales (reutilizadas del list.php)
function viewNote(noteId) {
    console.log('Abriendo modal para nota ID:', noteId);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('viewNoteModal'));
    modal.show();
    
    // Ocultar botón de editar inicialmente
    document.getElementById('editNoteBtn').style.display = 'none';
    
    // Intentar obtener datos de la tarjeta de la nota
    const noteCard = document.querySelector(`.note-card[data-id="${noteId}"]`);
    if (noteCard) {
        const title = noteCard.querySelector('.note-title').textContent;
        const content = noteCard.querySelector('.note-content')?.textContent || '';
        const statusBadge = noteCard.querySelector('.badge');
        const date = noteCard.querySelector('.note-date').textContent;
        
        // Extraer información de los badges
        const status = statusBadge ? statusBadge.textContent.trim() : 'Pendiente';
        const color = noteCard.dataset.color;
        
        // Crear contenido del modal con datos de la tarjeta
        const content_html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título:</label>
                        <p class="form-control-plaintext">${title}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado:</label>
                        <div>
                            <span class="badge ${getStatusClassFromText(status)}">
                                ${getStatusIconFromText(status)} ${status}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Color:</label>
                        <div>
                            <span class="badge" style="background-color: ${getColorValueFromText(getColorNameFromValue(color))}; color: white;">
                                ${getColorNameFromValue(color)}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Creación:</label>
                        <p class="form-control-plaintext">${date}</p>
                    </div>
                </div>
            </div>
            
            ${content ? `
            <div class="mb-3">
                <label class="form-label fw-bold">Contenido:</label>
                <div class="p-3 bg-light rounded" style="min-height: 100px;">
                    ${content.replace(/\n/g, '<br>')}
                </div>
            </div>
            ` : ''}
        `;
        
        document.getElementById('noteModalContent').innerHTML = content_html;
        
        // Mostrar botón de editar y configurar su acción
        const editBtn = document.getElementById('editNoteBtn');
        editBtn.style.display = 'inline-block';
        editBtn.onclick = function() {
            window.location.href = `/notes/update?id=${noteId}`;
        };
    }
}

function editNote(noteId) {
    window.location.href = `/notes/update?id=${noteId}`;
}

function changeStatus(noteId) {
    // Implementar cambio de estado
    console.log('Cambiar estado de nota:', noteId);
}

// Funciones auxiliares para formatear datos
function getStatusClassFromText(statusText) {
    if (statusText.includes('Pendiente')) return 'bg-warning';
    if (statusText.includes('Procesando')) return 'bg-info';
    if (statusText.includes('Completada')) return 'bg-success';
    return 'bg-secondary';
}

function getStatusIconFromText(statusText) {
    if (statusText.includes('Pendiente')) return '⏳';
    if (statusText.includes('Procesando')) return '🔄';
    if (statusText.includes('Completada')) return '✅';
    return '❓';
}

function getColorValueFromText(colorText) {
    const colorMap = {
        'Amarillo': '#ffeb3b',
        'Azul': '#2196f3',
        'Verde': '#4caf50',
        'Rojo': '#f44336',
        'Naranja': '#ff9800',
        'Morado': '#9c27b0',
        'Rosa': '#e91e63',
        'Gris': '#9e9e9e',
        'Azul Claro': '#03a9f4',
        'Verde Claro': '#8bc34a',
    };
    return colorMap[colorText] || '#ffeb3b';
}

function getColorNameFromValue(colorValue) {
    const colorMap = {
        'yellow': 'Amarillo',
        'blue': 'Azul',
        'green': 'Verde',
        'red': 'Rojo',
        'orange': 'Naranja',
        'purple': 'Morado',
        'pink': 'Rosa',
        'gray': 'Gris',
        'lightblue': 'Azul Claro',
        'lightgreen': 'Verde Claro',
    };
    return colorMap[colorValue] || 'Amarillo';
}

// Configurar contadores como filtros
function setupCounterFilters() {
    // Agregar event listeners a los contadores
    const totalCounter = document.querySelector('.stat-card.total-notes');
    const pendingCounter = document.querySelector('.stat-card.pending-notes');
    const processingCounter = document.querySelector('.stat-card.processing-notes');
    const completedCounter = document.querySelector('.stat-card.completed-notes');
    
    if (totalCounter) {
        totalCounter.addEventListener('click', function() {
            clearAllFilters();
            showNotification('info', 'Mostrando todas las notas');
        });
        totalCounter.style.cursor = 'pointer';
        totalCounter.title = 'Hacer clic para mostrar todas las notas';
    }
    
    if (pendingCounter) {
        pendingCounter.addEventListener('click', function() {
            filterByStatus('pending');
            showNotification('info', 'Mostrando solo notas pendientes');
        });
        pendingCounter.style.cursor = 'pointer';
        pendingCounter.title = 'Hacer clic para filtrar por pendientes';
    }
    
    if (processingCounter) {
        processingCounter.addEventListener('click', function() {
            filterByStatus('processing');
            showNotification('info', 'Mostrando solo notas en proceso');
        });
        processingCounter.style.cursor = 'pointer';
        processingCounter.title = 'Hacer clic para filtrar por en proceso';
    }
    
    if (completedCounter) {
        completedCounter.addEventListener('click', function() {
            filterByStatus('completed');
            showNotification('info', 'Mostrando solo notas completadas');
        });
        completedCounter.style.cursor = 'pointer';
        completedCounter.title = 'Hacer clic para filtrar por completadas';
    }
}

// Filtrar por estado específico
function filterByStatus(status) {
    // Limpiar otros filtros
    document.getElementById('searchInput').value = '';
    document.getElementById('colorFilter').value = '';
    
    // Establecer filtro de estado
    document.getElementById('statusFilter').value = status;
    
    // Aplicar filtro
    filterNotes();
    
    // Actualizar contador de filtrados
    updateFilteredCount();
    
    // Actualizar indicadores visuales
    updateActiveFilterIndicator(status);
}

// Actualizar indicador de filtro activo
function updateActiveFilterIndicator(activeStatus) {
    // Remover clase activa de todos los contadores
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('active-filter');
    });
    
    // Agregar clase activa al contador correspondiente
    if (activeStatus) {
        const activeCard = document.querySelector(`.stat-card.${activeStatus}-notes`);
        if (activeCard) {
            activeCard.classList.add('active-filter');
        }
    } else {
        // Si no hay filtro, marcar el contador total como activo
        const totalCard = document.querySelector('.stat-card.total-notes');
        if (totalCard) {
            totalCard.classList.add('active-filter');
        }
    }
}

// Limpiar todos los filtros
function clearAllFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('colorFilter').value = '';
    document.getElementById('sortFilter').value = 'newest';
    
    // Aplicar filtro (mostrar todas)
    filterNotes();
    updateFilteredCount();
    
    // Actualizar indicador visual
    updateActiveFilterIndicator(null);
}

// Función para editar nota
function editNote(noteId) {
    if (!noteId) {
        console.error('ID de nota no válido');
        showNotification('error', 'Error: ID de nota no válido');
        return;
    }
    
    // Mostrar notificación de carga
    showNotification('info', 'Abriendo editor de nota...');
    
    // Redirigir a la página de edición
    const editUrl = `/notes/update/${noteId}`;
    window.location.href = editUrl;
}

// Agregar tooltips para doble clic
function addDoubleClickTooltips() {
    const noteCards = document.querySelectorAll('.note-card');
    noteCards.forEach(card => {
        if (!card.hasAttribute('title')) {
            card.setAttribute('title', 'Doble clic para editar');
        }
    });
}

// Función para mostrar notificaciones
function showNotification(type, message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'info' ? 'info-circle' : type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
        ${message}
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

// Efectos de búsqueda en tiempo real
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterNotes();
    }, 300);
});

// Efectos de hover mejorados
document.addEventListener('mouseover', function(e) {
    if (e.target.closest('.note-card')) {
        const card = e.target.closest('.note-card');
        card.style.transform = 'translateY(-3px) scale(1.02)';
    }
});

document.addEventListener('mouseout', function(e) {
    if (e.target.closest('.note-card')) {
        const card = e.target.closest('.note-card');
        card.style.transform = 'translateY(0) scale(1)';
    }
});

// Doble clic para editar nota
document.addEventListener('dblclick', function(e) {
    console.log('Doble clic detectado en:', e.target);
    
    // Verificar si el clic fue en un botón de acción
    if (e.target.closest('.note-actions') || e.target.closest('button')) {
        console.log('Doble clic en botón de acción, ignorando...');
        return;
    }
    
    const noteCard = e.target.closest('.note-card');
    if (noteCard) {
        console.log('Tarjeta de nota encontrada:', noteCard);
        
        // Prevenir comportamiento por defecto
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        const noteId = noteCard.dataset.id;
        console.log('ID de nota extraído:', noteId);
        
        if (noteId) {
            // Agregar efecto visual inmediato
            noteCard.classList.add('double-click-effect');
            
            // Limpiar efecto visual después de la animación
            setTimeout(() => {
                noteCard.classList.remove('double-click-effect');
            }, 600);
            
            // Redirigir inmediatamente sin delay adicional
            console.log('Redirigiendo inmediatamente a edición...');
            const editUrl = `/notes/update/${noteId}`;
            window.location.href = editUrl;
        } else {
            console.error('No se pudo obtener el ID de la nota');
            showNotification('error', 'Error: No se pudo obtener el ID de la nota');
        }
    } else {
        console.log('No se encontró tarjeta de nota en el doble clic');
    }
});
