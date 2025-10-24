/**
 * Sistema de Notas Adhesivas (Stickers) - JavaScript FIXED
 * Versión completamente corregida sin errores
 */

let isDragging = false;
let currentSticker = null;
let dragOffset = { x: 0, y: 0 };
let savePositionTimeout = null;

/**
 * Inicializar funcionalidades de stickers
 */
function initStickers() {
    console.log('🎯 Inicializando sistema de stickers...');
    
    // Configurar eventos globales
    setupGlobalEvents();
    
    // Hacer stickers arrastrables (solo si no están en columnas)
    makeStickersDraggable();
    
    // Cargar notas existentes
    loadNotes();
    
    console.log('✅ Sistema de stickers inicializado correctamente');
}

/**
 * Verificar si estamos en layout de columnas
 */
function isColumnLayout() {
    return $('#notesContainer').hasClass('notes-columns-container') || 
           $('.notes-column').length > 0;
}

/**
 * Hacer stickers arrastrables (solo si no están en columnas)
 */
function makeStickersDraggable() {
    if (isColumnLayout()) {
        console.log('📋 Layout de columnas detectado - arrastre deshabilitado');
        return; // No hacer arrastre en layout de columnas
    }
    
    $('.note-sticker').each(function() {
        const sticker = $(this);
        
        // Eventos de mouse
        sticker.on('mousedown', startDrag);
        sticker.on('touchstart', startDragTouch);
        
        // Prevenir selección de texto
        sticker.on('selectstart', function(e) {
            e.preventDefault();
        });
    });
    
    // Eventos globales de arrastre
    $(document).on('mousemove', drag);
    $(document).on('mouseup', endDrag);
    $(document).on('touchmove', dragTouch);
    $(document).on('touchend', endDrag);
    
    // Prevenir arrastre de imágenes
    $(document).on('dragstart', function(e) {
        e.preventDefault();
    });
}

/**
 * Iniciar arrastre con mouse
 */
function startDrag(e) {
    if (isColumnLayout()) {
        console.log('📋 Layout de columnas - arrastre bloqueado');
        return;
    }
    
    e.preventDefault();
    
    isDragging = true;
    currentSticker = $(this);
    
    const rect = currentSticker[0].getBoundingClientRect();
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;
    
    currentSticker.addClass('dragging');
    $('body').addClass('no-select');
    
    console.log('🖱️ Iniciando arrastre:', currentSticker.data('id'));
}

/**
 * Iniciar arrastre táctil
 */
function startDragTouch(e) {
    if (isColumnLayout()) {
        console.log('📋 Layout de columnas - arrastre táctil bloqueado');
        return;
    }
    
    e.preventDefault();
    
    const touch = e.originalEvent.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    
    startDrag.call(this, mouseEvent);
}

/**
 * Arrastrar con mouse
 */
function drag(e) {
    if (isColumnLayout() || !isDragging || !currentSticker) {
        return;
    }
    
    e.preventDefault();
    
    const containerRect = $('#notesContainer')[0].getBoundingClientRect();
    const newX = e.clientX - containerRect.left - dragOffset.x;
    const newY = e.clientY - containerRect.top - dragOffset.y;
    
    // Limitar dentro del contenedor
    const maxX = $('#notesContainer').width() - currentSticker.width();
    const maxY = $('#notesContainer').height() - currentSticker.height();
    
    const constrainedX = Math.max(0, Math.min(newX, maxX));
    const constrainedY = Math.max(0, Math.min(newY, maxY));
    
    currentSticker.css({
        left: constrainedX + 'px',
        top: constrainedY + 'px'
    });
}

/**
 * Arrastrar táctil
 */
function dragTouch(e) {
    if (isColumnLayout() || !isDragging || !currentSticker) {
        return;
    }
    
    e.preventDefault();
    
    const touch = e.originalEvent.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    
    drag.call(currentSticker[0], mouseEvent);
}

/**
 * Finalizar arrastre
 */
function endDrag(e) {
    if (!isDragging || !currentSticker) return;
    
    e.preventDefault();
    
    const noteId = currentSticker.data('id');
    const positionX = parseInt(currentSticker.css('left'));
    const positionY = parseInt(currentSticker.css('top'));
    
    // Solo guardar posición si NO estamos en layout de columnas
    if (!isColumnLayout()) {
        savePosition(noteId, positionX, positionY);
        console.log('📍 Posición guardada:', { noteId, positionX, positionY });
    } else {
        console.log('📋 Layout de columnas - arrastre finalizado sin guardar posición');
    }
    
    currentSticker.removeClass('dragging');
    $('body').removeClass('no-select');
    
    isDragging = false;
    currentSticker = null;
}

/**
 * Guardar posición en el servidor con debounce
 */
function savePosition(noteId, x, y) {
    if (isColumnLayout()) {
        console.log('📋 Layout de columnas - guardado de posición deshabilitado');
        return;
    }
    
    // Validar parámetros
    if (!noteId || x === undefined || y === undefined) {
        console.error('❌ Parámetros inválidos para guardar posición:', { noteId, x, y });
        return;
    }
    
    // Debounce: cancelar timeout anterior
    if (savePositionTimeout) {
        clearTimeout(savePositionTimeout);
    }
    
    // Guardar posición después de 500ms de inactividad
    savePositionTimeout = setTimeout(function() {
        $.ajax({
            url: '/notes/update-position',
            method: 'POST',
            data: {
                id: noteId,
                position_x: Math.round(x),
                position_y: Math.round(y)
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    console.log('✅ Posición guardada exitosamente:', { noteId, x, y });
                } else {
                    console.error('❌ Error al guardar posición:', response ? response.message : 'Respuesta inválida');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX al guardar posición:', {
                    noteId: noteId,
                    x: x,
                    y: y,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }, 500);
}

/**
 * Editar nota
 */
function editNote(noteId) {
    console.log('✏️ Editando nota:', noteId);
    
    if (!noteId) {
        console.error('❌ ID de nota no válido');
        showNotification('Error: ID de nota no válido', 'error');
        return;
    }
    
    // Verificar que el modal existe
    const editModal = $('#editNoteModal');
    if (editModal.length === 0) {
        console.error('❌ Modal de edición no encontrado');
        showNotification('Error: Modal de edición no encontrado', 'error');
        return;
    }
    
    console.log('📋 Obteniendo datos de la nota...');
    
    // Obtener datos de la nota específica
    $.ajax({
        url: '/notes/get-note',
        method: 'GET',
        data: { id: noteId },
        dataType: 'json',
        success: function(response) {
            console.log('📋 Respuesta de get-note:', response);
            
            if (response && response.success) {
                const note = response.note;
                console.log('📋 Datos de la nota:', note);
                
                // Llenar formulario de edición
                $('#edit-note-id').val(note.id);
                $('#edit-note-title').val(note.title);
                $('#edit-note-content').val(note.content);
                $('#edit-note-color').val(note.color);
                $('#edit-note-status').val(note.status);
                
                console.log('📋 Formulario llenado:', {
                    id: $('#edit-note-id').val(),
                    title: $('#edit-note-title').val(),
                    content: $('#edit-note-content').val(),
                    color: $('#edit-note-color').val(),
                    status: $('#edit-note-status').val()
                });
                
                // Mostrar modal
                editModal.modal('show');
                
                console.log('✅ Modal de edición abierto para nota:', noteId);
            } else {
                const errorMsg = response ? response.message : 'Error desconocido';
                console.error('❌ Error al cargar datos de la nota:', errorMsg);
                showNotification('Error al cargar datos de la nota: ' + errorMsg, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error AJAX al cargar datos de la nota:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
            });
            
            let errorMessage = 'Error al cargar datos de la nota';
            if (xhr.status === 404) {
                errorMessage = 'Nota no encontrada.';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor.';
            }
            
            showNotification(errorMessage, 'error');
        }
    });
}

/**
 * Cambiar estado de nota
 */
function changeStatus(noteId) {
    console.log('🔄 Cambiando estado de nota:', noteId);
    
    if (!noteId) {
        console.error('❌ ID de nota no válido');
        return;
    }
    
    const currentSticker = $(`.note-sticker[data-id="${noteId}"]`);
    const currentStatus = currentSticker.find('.status-text').text().toLowerCase();
    
    let newStatus;
    let newStatusName;
    let newStatusIcon;
    
    // Ciclar entre estados
    switch(currentStatus) {
        case 'pendiente':
            newStatus = 'processing';
            newStatusName = 'Procesando';
            newStatusIcon = '🔄';
            break;
        case 'procesando':
            newStatus = 'completed';
            newStatusName = 'Completada';
            newStatusIcon = '✅';
            break;
        case 'completada':
            newStatus = 'pending';
            newStatusName = 'Pendiente';
            newStatusIcon = '⏳';
            break;
        default:
            newStatus = 'processing';
            newStatusName = 'Procesando';
            newStatusIcon = '🔄';
    }
    
    // Actualizar en el servidor
    $.ajax({
        url: '/notes/change-status',
        method: 'POST',
        data: {
            id: noteId,
            status: newStatus
        },
        success: function(response) {
            if (response.success) {
                // Actualizar UI
                updateStickerStatus(noteId, newStatus, newStatusName, newStatusIcon);
                showNotification(`Estado cambiado a ${newStatusName}`, 'success');
                console.log('✅ Estado cambiado exitosamente:', { noteId, newStatus });
                
                // Recargar página después de un breve delay
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                console.error('❌ Error al cambiar estado:', response.message);
                showNotification('Error al cambiar estado: ' + response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al cambiar estado:', error);
            showNotification('Error de conexión al cambiar estado', 'error');
        }
    });
}

/**
 * Actualizar estado del sticker en la UI
 */
function updateStickerStatus(noteId, status, statusName, statusIcon) {
    const sticker = $(`.note-sticker[data-id="${noteId}"]`);
    
    // Remover clases de estado anteriores
    sticker.removeClass('status-pending status-processing status-completed');
    
    // Agregar nueva clase de estado
    sticker.addClass('status-' + status);
    
    // Actualizar icono y texto
    sticker.find('.status-icon').text(statusIcon);
    sticker.find('.status-text').text(statusName);
    
    // Actualizar estadísticas
    updateStats();
}

/**
 * Cargar notas existentes
 */
function loadNotes() {
    console.log('📋 Cargando notas existentes...');
    
    $.ajax({
        url: '/notes/get-notes',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                console.log('Notas cargadas:', response.notes.length);
                updateStats(response.stats);
            } else {
                console.error('❌ Error al cargar notas:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al cargar notas:', error);
        }
    });
}

/**
 * Actualizar estadísticas
 */
function updateStats(stats) {
    if (stats) {
        $('#total-notes').text(stats.total);
        $('#pending-notes').text(stats.pending);
        $('#processing-notes').text(stats.processing);
        $('#completed-notes').text(stats.completed);
    }
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    if (!message) {
        console.warn('⚠️ Intento de mostrar notificación vacía');
        return;
    }
    
    const typeClass = {
        'error': 'danger',
        'success': 'success',
        'info': 'info',
        'warning': 'warning'
    }[type] || 'info';
    
    // Crear elemento de notificación
    const notification = $(`
        <div class="alert alert-${typeClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    `);
    
    // Remover notificaciones anteriores del mismo tipo
    $('.alert.position-fixed').remove();
    
    // Agregar al body
    $('body').append(notification);
    
    // Auto-eliminar después de 5 segundos
    setTimeout(function() {
        if (notification && notification.length) {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }
    }, 5000);
}

/**
 * Configurar eventos globales
 */
function setupGlobalEvents() {
    console.log('🔧 Configurando eventos globales...');
    
    // Verificar que el formulario existe
    if ($('#createNoteForm').length > 0) {
        console.log('✅ Formulario de crear nota encontrado');
    } else {
        console.error('❌ Formulario de crear nota NO encontrado');
    }
    
    // Prevenir arrastre de imágenes
    $(document).on('dragstart', function(e) {
        e.preventDefault();
    });
    
    // Formulario de crear nota
    $('#createNoteForm').on('submit', function(e) {
        console.log('📝 Formulario de crear nota enviado');
        e.preventDefault();
        
        const formData = $(this).serialize();
        console.log('📝 Datos del formulario:', formData);
        
        $.ajax({
            url: '/notes/create',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Respuesta exitosa:', response);
                if (response.success) {
                    $('#createNoteModal').modal('hide');
                    showNotification('Nota creada exitosamente', 'success');
                    setTimeout(function() {
                        location.reload(); // Recargar para mostrar cambios
                    }, 1000);
                } else {
                    console.error('❌ Error en respuesta:', response.message);
                    showNotification('Error al crear nota: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error al crear nota:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                showNotification('Error de conexión al crear nota', 'error');
            }
        });
    });
    
    // Formulario de editar nota
    $('#editNoteForm').on('submit', function(e) {
        console.log('📝 Formulario de edición enviado');
        e.preventDefault();
        
        const formData = $(this).serialize();
        console.log('📝 Datos del formulario:', formData);
        
        // Validar que tenemos un ID
        const noteId = $('#edit-note-id').val();
        if (!noteId) {
            console.error('❌ No se encontró ID de nota en el formulario');
            showNotification('Error: No se encontró ID de nota', 'error');
            return;
        }
        
        console.log('📝 Actualizando nota con ID:', noteId);
        
        $.ajax({
            url: '/notes/update',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Respuesta de actualización:', response);
                if (response && response.success) {
                    $('#editNoteModal').modal('hide');
                    showNotification('Nota actualizada exitosamente', 'success');
                    setTimeout(function() {
                        location.reload(); // Recargar para mostrar cambios
                    }, 1000);
                } else {
                    const errorMsg = response ? response.message : 'Error desconocido';
                    console.error('❌ Error en respuesta:', response);
                    showNotification('Error al actualizar nota: ' + errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX al actualizar nota:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                let errorMessage = 'Error de conexión al actualizar nota';
                if (xhr.status === 404) {
                    errorMessage = 'Endpoint no encontrado. Verifica la URL.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Sin conexión al servidor.';
                }
                
                showNotification(errorMessage, 'error');
            }
        });
    });
    
    // Manejar botones de editar
    $(document).on('click', '.edit-note-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const noteId = $(this).data('id');
        console.log('🔵 Botón editar clickeado para nota:', noteId);
        editNote(noteId);
    });
    
    // Manejar botones de cambiar estado
    $(document).on('click', '.change-status-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const noteId = $(this).data('id');
        console.log('🟢 Botón cambiar estado clickeado para nota:', noteId);
        changeStatus(noteId);
    });
    
    // Manejar doble clic en notas para editar
    $(document).on('dblclick', '.note-sticker', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const noteId = $(this).data('id');
        console.log('🔄 Doble clic en nota:', noteId);
        editNote(noteId);
    });
    
    // Debug: verificar botón de crear nota
    $(document).on('click', 'a[data-bs-target="#createNoteModal"]', function(e) {
        console.log('🔘 Botón de crear nota clickeado');
        console.log('🔘 Modal target:', $(this).data('bs-target'));
    });
}

// Auto-inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log('📄 Documento listo - inicializando stickers...');
    initStickers();
});

console.log('🎯 JavaScript de stickers cargado correctamente');
