<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $notesByStatus array */
/* @var $stats array */

$this->title = 'Dashboard de Notas - Panel Dinámico';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS personalizado
$this->registerCssFile('@web/css/notes-dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="notes-dashboard"
     data-update-url="<?= Html::encode(Url::to(['/notes/update'])) ?>"
     data-delete-url="<?= Html::encode(Url::to(['/notes/delete'])) ?>"
     data-change-status-url="<?= Html::encode(Url::to(['/notes/change-status'])) ?>"
     data-csrf-param="<?= Html::encode(Yii::$app->request->csrfParam) ?>"
     data-csrf-token="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
    <!-- Header con controles dinámicos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="notes-dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
                <div class="notes-dashboard-title">
                    <h1 class="h3 mb-1">📊 Dashboard de Notas</h1>
                    <p class="text-muted mb-0 small">Gestiona tus notas con una interfaz dinámica e interactiva</p>
                </div>
                <div class="notes-dashboard-toolbar d-grid d-sm-flex flex-wrap gap-2">
                    <?= Html::a('➕ Nueva Nota', ['create'], [
                        'class' => 'btn btn-primary btn-sm',
                        'data-bs-toggle' => 'modal',
                        'data-bs-target' => '#createNoteModal'
                    ]) ?>
                    <?= Html::a('📋 Vista Lista', ['list'], ['class' => 'btn btn-outline-info btn-sm']) ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleViewMode()">
                        <i class="fas fa-th" id="viewModeIcon"></i> <span id="viewModeText">Vista Compacta</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros dinámicos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end notes-filters-row">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">🔍 Buscar:</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar por título o contenido...">
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label fw-bold">📊 Estado:</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">Todos los estados</option>
                                <option value="pending">⏳ Pendientes</option>
                                <option value="processing">🔄 Procesando</option>
                                <option value="completed">✅ Completadas</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label fw-bold">🎨 Color:</label>
                            <select class="form-select" id="colorFilter">
                                <option value="">Todos los colores</option>
                                <option value="yellow">🟡 Amarillo</option>
                                <option value="blue">🔵 Azul</option>
                                <option value="green">🟢 Verde</option>
                                <option value="red">🔴 Rojo</option>
                                <option value="orange">🟠 Naranja</option>
                                <option value="purple">🟣 Morado</option>
                                <option value="pink">🩷 Rosa</option>
                                <option value="gray">⚫ Gris</option>
                                <option value="lightblue">🔵 Azul Claro</option>
                                <option value="lightgreen">🟢 Verde Claro</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label fw-bold">📅 Ordenar:</label>
                            <select class="form-select" id="sortFilter">
                                <option value="newest">Más recientes</option>
                                <option value="oldest">Más antiguas</option>
                                <option value="title">Por título</option>
                                <option value="status">Por estado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">⚡ Acciones:</label>
                            <div class="d-grid d-sm-flex gap-2 notes-filter-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearFilters()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportNotes()">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas animadas -->
    <div class="row g-3 mb-4 notes-stats-row">
        <div class="col-6 col-md-3">
            <div class="stat-card total-notes" data-animate="true">
                <div class="stat-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number" data-target="<?= $stats['total'] ?>">0</h3>
                    <p class="stat-label">Total de Notas</p>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card pending-notes" data-animate="true">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number" data-target="<?= $stats['pending'] ?>">0</h3>
                    <p class="stat-label">Pendientes</p>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $stats['total'] > 0 ? ($stats['pending'] / $stats['total']) * 100 : 0 ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card processing-notes" data-animate="true">
                <div class="stat-icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number" data-target="<?= $stats['processing'] ?>">0</h3>
                    <p class="stat-label">Procesando</p>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $stats['total'] > 0 ? ($stats['processing'] / $stats['total']) * 100 : 0 ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card completed-notes" data-animate="true">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number" data-target="<?= $stats['completed'] ?>">0</h3>
                    <p class="stat-label">Completadas</p>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?= $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0 ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de notas dinámico -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header notes-panel-header d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 gap-sm-3">
                    <h5 class="mb-0">📝 Notas Adhesivas</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3">
                        <span class="badge bg-info" id="filteredCount"><?= $stats['total'] ?> notas</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" data-view="grid" onclick="setViewMode('grid')">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="btn btn-outline-primary" data-view="list" onclick="setViewMode('list')">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor de notas -->
                    <div id="notesContainer" class="notes-container">
                        <?php 
                        $allNotes = array_merge($notesByStatus['pending'], $notesByStatus['processing'], $notesByStatus['completed']);
                        foreach ($allNotes as $note): 
                        ?>
                        <div class="note-card <?= $note->getColorClass() ?>" 
                             data-id="<?= $note->id ?>"
                             data-status="<?= $note->status ?>"
                             data-color="<?= $note->color ?>"
                             data-title="<?= Html::encode($note->title) ?>"
                             data-content="<?= Html::encode($note->content) ?>"
                             data-created="<?= $note->created_at ?>">
                            
                            <!-- Header de la nota -->
                            <div class="note-header">
                                <div class="note-title">
                                    <?= Html::encode($note->title) ?>
                                </div>
                                <div class="note-actions d-none d-md-flex">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); viewNote(<?= (int) $note->id ?>)" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $note->id], [
                                        'class' => 'btn btn-sm btn-outline-warning',
                                        'title' => 'Editar Nota',
                                    ]) ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="event.stopPropagation(); changeStatus(<?= (int) $note->id ?>)" title="Cambiar Estado">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size:16px;">delete</span>',
                                        ['delete', 'id' => $note->id],
                                        [
                                            'class' => 'btn btn-sm btn-danger note-delete-btn',
                                            'title' => 'Eliminar',
                                            'encode' => false,
                                            'data' => [
                                                'confirm' => '¿Estás seguro de que deseas eliminar esta nota?',
                                                'method' => 'post',
                                            ],
                                        ]
                                    ) ?>
                                </div>
                            </div>

                            <!-- Contenido de la nota -->
                            <?php if ($note->content): ?>
                                <div class="note-content">
                                    <?= nl2br(Html::encode($note->content)) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Footer de la nota -->
                            <div class="note-footer">
                                <div class="note-meta">
                                    <span class="badge <?= $note->getStatusClass() ?>">
                                        <?= $note->getStatusIcon() ?> <?= $note->getStatusName() ?>
                                    </span>
                                    <span class="note-date">
                                        <?= Yii::$app->formatter->asRelativeTime($note->created_at) ?>
                                    </span>
                                </div>
                                
                                <div class="note-footer-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-md-none" onclick="event.stopPropagation(); viewNote(<?= (int) $note->id ?>)" title="Ver">
                                        <span class="material-symbols-outlined align-middle" style="font-size:16px;">visibility</span>
                                        Ver
                                    </button>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined align-middle" style="font-size:16px;">edit</span> Editar',
                                        ['update', 'id' => $note->id],
                                        ['class' => 'btn btn-warning btn-sm', 'encode' => false, 'title' => 'Editar esta nota']
                                    ) ?>
                                    <button type="button" class="btn btn-outline-success btn-sm d-md-none" onclick="event.stopPropagation(); changeStatus(<?= (int) $note->id ?>)" title="Estado">
                                        <span class="material-symbols-outlined align-middle" style="font-size:16px;">sync_alt</span>
                                        Estado
                                    </button>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined align-middle" style="font-size:16px;">delete</span> Eliminar',
                                        ['delete', 'id' => $note->id],
                                        [
                                            'class' => 'btn btn-danger btn-sm note-delete-btn',
                                            'encode' => false,
                                            'title' => 'Eliminar esta nota',
                                            'data' => [
                                                'confirm' => '¿Estás seguro de que deseas eliminar esta nota?',
                                                'method' => 'post',
                                            ],
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Estado vacío -->
                    <div id="emptyState" class="empty-state" style="display: none;">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron notas</h5>
                            <p class="text-muted">Intenta ajustar los filtros o crear una nueva nota</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                                <i class="fas fa-plus"></i> Crear Nueva Nota
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear nueva nota -->
<div class="modal fade" id="createNoteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ Nueva Nota Adhesiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm(['/notes/create'], 'post', ['id' => 'createNoteForm']) ?>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <?= Html::label('Título', 'note-title', ['class' => 'form-label']) ?>
                            <?= Html::textInput('Note[title]', '', [
                                'id' => 'note-title',
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => 'Título de la nota...'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <?= Html::label('Contenido', 'note-content', ['class' => 'form-label']) ?>
                            <?= Html::textarea('Note[content]', '', [
                                'id' => 'note-content',
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Contenido de la nota...'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <?= Html::label('Color', 'note-color', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList('Note[color]', 'yellow', \app\models\Note::COLORS, [
                                'id' => 'note-color',
                                'class' => 'form-select'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <?= Html::label('Estado', 'note-status', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList('Note[status]', 'pending', \app\models\Note::STATUSES, [
                                'id' => 'note-status',
                                'class' => 'form-select'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="createNoteBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="createSpinner"></span>
                    <span id="createBtnText">Crear Nota</span>
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- Modal para editar nota -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Editar Nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm(['/notes/update'], 'post', ['id' => 'editNoteForm']) ?>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <?= Html::hiddenInput('Note[id]', '', ['id' => 'edit-note-id']) ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <?= Html::label('Título', 'edit-note-title', ['class' => 'form-label']) ?>
                            <?= Html::textInput('Note[title]', '', [
                                'id' => 'edit-note-title',
                                'class' => 'form-control',
                                'required' => true,
                                'placeholder' => 'Título de la nota...'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <?= Html::label('Contenido', 'edit-note-content', ['class' => 'form-label']) ?>
                            <?= Html::textarea('Note[content]', '', [
                                'id' => 'edit-note-content',
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Contenido de la nota...'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <?= Html::label('Color', 'edit-note-color', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList('Note[color]', '', \app\models\Note::COLORS, [
                                'id' => 'edit-note-color',
                                'class' => 'form-select'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <?= Html::label('Estado', 'edit-note-status', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList('Note[status]', '', \app\models\Note::STATUSES, [
                                'id' => 'edit-note-status',
                                'class' => 'form-select'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar Nota</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- Modal para ver nota -->
<div class="modal fade" id="viewNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #007bff;">visibility</span>
                    Ver Nota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="noteModalContent">
                <!-- Contenido se carga dinámicamente -->
            </div>
            <div class="modal-footer notes-modal-footer flex-column flex-sm-row gap-2">
                <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger w-100 w-sm-auto" id="deleteNoteBtn" style="display: none;" onclick="deleteNote(window._viewNoteId)">
                    <span class="material-symbols-outlined me-1" style="font-size: 16px;">delete</span>
                    Eliminar
                </button>
                <button type="button" class="btn btn-primary w-100 w-sm-auto" id="editNoteBtn" style="display: none;">
                    <span class="material-symbols-outlined me-1" style="font-size: 16px;">edit</span>
                    Editar Nota
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el archivo JavaScript externo para modales
$this->registerJsFile('@web/js/notes-modals.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
?>

<?php
// Registrar JavaScript personalizado
$this->registerJsFile('@web/js/notes-dashboard.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// JavaScript adicional para funciones específicas de esta página
$this->registerJs("
// Función para manejar clic del botón de editar
function handleEditClick(event, noteId) {
    console.log('=== BOTÓN EDITAR CLICKEADO ===');
    console.log('ID recibido:', noteId);
    
    // Prevenir propagación para evitar conflictos con doble clic
    event.stopPropagation();
    event.stopImmediatePropagation();
    
    // Llamar a la función de edición
    editNote(noteId);
}

// Función directa para editar nota (sin delays)
function editNoteDirect(noteId) {
    console.log('=== EDITAR NOTA DIRECTA ===');
    console.log('ID recibido:', noteId);
    
    if (!noteId) {
        console.error('ID de nota no válido');
        alert('Error: ID de nota no válido');
        return;
    }
    
    // Mostrar notificación
    console.log('Redirigiendo directamente a edición...');
    
    // Redirigir inmediatamente
    window.location.href = buildNoteEditUrl(noteId);
}

function buildNoteEditUrl(noteId) {
    const base = document.querySelector('.notes-dashboard')?.dataset.updateUrl || '" . Url::to(['/notes/update']) . "';
    const sep = base.indexOf('?') >= 0 ? '&' : '?';
    return base + sep + 'id=' + encodeURIComponent(noteId);
}

// Función para editar nota (usada por botones y doble clic)
function editNote(noteId) {
    if (!noteId) {
        showNotification('error', 'Error: ID de nota no válido');
        return;
    }
    window.location.href = buildNoteEditUrl(noteId);
}

// Función para ver nota
function viewNote(noteId) {
    const card = document.querySelector('.note-card[data-id=\"' + noteId + '\"]');
    if (!card) {
        return;
    }
    window._viewNoteId = noteId;
    const title = card.dataset.title || '';
    const content = card.dataset.content || '';
    const status = card.querySelector('.badge') ? card.querySelector('.badge').textContent.trim() : '';
    document.getElementById('noteModalContent').innerHTML =
        '<h5>' + title + '</h5>' +
        '<p class=\"text-muted small mb-2\">' + status + '</p>' +
        '<div class=\"border rounded p-3 bg-light\" style=\"white-space:pre-wrap;\">' + content + '</div>';
    const editBtn = document.getElementById('editNoteBtn');
    const deleteBtn = document.getElementById('deleteNoteBtn');
    if (editBtn) {
        editBtn.style.display = 'inline-block';
        editBtn.onclick = function() { editNote(noteId); };
    }
    if (deleteBtn) {
        deleteBtn.style.display = 'inline-block';
    }
    const modal = new bootstrap.Modal(document.getElementById('viewNoteModal'));
    modal.show();
}

// Función para cambiar estado
function changeStatus(noteId) {
    const card = document.querySelector('.note-card[data-id=\"' + noteId + '\"]');
    if (!card) {
        return;
    }
    const order = ['pending', 'processing', 'completed'];
    const current = card.dataset.status || 'pending';
    const next = order[(order.indexOf(current) + 1) % order.length];
    const labels = { pending: 'Pendiente', processing: 'Procesando', completed: 'Completada' };
    if (!confirm('¿Cambiar estado a \"' + (labels[next] || next) + '\"?')) {
        return;
    }
    const dash = document.querySelector('.notes-dashboard');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = dash?.dataset.changeStatusUrl || '" . Url::to(['/notes/change-status']) . "';
    form.style.display = 'none';
    const addField = function(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };
    addField('id', noteId);
    addField('status', next);
    if (dash?.dataset.csrfParam && dash?.dataset.csrfToken) {
        addField(dash.dataset.csrfParam, dash.dataset.csrfToken);
    }
    document.body.appendChild(form);
    form.submit();
}

// Eliminar nota (POST con CSRF; respaldo si data-method no está activo)
function deleteNote(noteId) {
    if (!noteId || !confirm('¿Estás seguro de que deseas eliminar esta nota?')) {
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    const dash = document.querySelector('.notes-dashboard');
    const deleteBase = dash?.dataset.deleteUrl || '" . Url::to(['/notes/delete']) . "';
    const sep = deleteBase.indexOf('?') >= 0 ? '&' : '?';
    form.action = deleteBase + sep + 'id=' + encodeURIComponent(noteId);
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = dash?.dataset.csrfParam || '" . Yii::$app->request->csrfParam . "';
    csrf.value = dash?.dataset.csrfToken || '" . Yii::$app->request->csrfToken . "';
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}
", View::POS_READY);
?>
