<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\data\ActiveDataProvider;

$this->title = 'Notas Adhesivas - Listado';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="notes-list">
    <!-- Header con estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">📝 Notas Adhesivas</h1>
                    <p class="text-muted mb-0">Gestiona tus notas y tareas de forma organizada</p>
                </div>
                <div class="d-flex gap-2">
                    <?= Html::a('➕ Nueva Nota', ['create'], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('📊 Vista Kanban', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= Html::encode($search) ?>" 
                           placeholder="Título o contenido...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Procesando</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completadas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Color</label>
                    <select name="color" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (\app\models\Note::COLORS as $colorKey => $colorName): ?>
                            <option value="<?= $colorKey ?>" <?= $color === $colorKey ? 'selected' : '' ?>>
                                <?= $colorName ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="<?= Url::to(['list']) ?>" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de notas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">📋 Listado de Notas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Título</th>
                            <th style="width: 30%;">Contenido</th>
                            <th style="width: 10%;">Color</th>
                            <th style="width: 10%;">Estado</th>
                            <th style="width: 15%;">Fecha</th>
                            <th style="width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dataProvider->getCount() > 0): ?>
                            <?php foreach ($dataProvider->getModels() as $index => $note): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= $dataProvider->pagination->offset + $index + 1 ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= Html::encode($note->title) ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= Html::encode($note->content) ?>">
                                        <?= Html::encode($note->content) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?= $note->getColorValue() ?>; color: white;">
                                        <?= $note->getColorName() ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $note->getStatusClass() ?>">
                                        <?= $note->getStatusIcon() ?> <?= $note->getStatusName() ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= Yii::$app->formatter->asDate($note->created_at) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="viewNote(<?= $note->id ?>)" title="Ver" data-note-id="<?= $note->id ?>">
                                            👁️
                                        </button>
                                        <?= Html::a('✏️', ['update', 'id' => $note->id], [
                                            'class' => 'btn btn-outline-primary',
                                            'title' => 'Editar'
                                        ]) ?>
                                        <?= Html::a('🔄', ['change-status', 'id' => $note->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => 'Cambiar Estado',
                                            'data-method' => 'post'
                                        ]) ?>
                                        <?= Html::a('🗑️', ['delete', 'id' => $note->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => 'Eliminar',
                                            'data-confirm' => '¿Estás seguro de eliminar esta nota?',
                                            'data-method' => 'post'
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-sticky-note fa-3x mb-3"></i>
                                        <p>No hay notas disponibles</p>
                                        <small>Comienza creando tu primera nota</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination justify-content-center mb-0']
            ]) ?>
        </div>
    </div>
</div>

<!-- Modal para ver nota -->
<div class="modal fade" id="viewNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando información de la nota...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="editNoteBtn" style="display: none;">
                    <span class="material-symbols-outlined me-1" style="font-size: 16px;">edit</span>
                    Editar Nota
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewNote(noteId) {
    console.log('Abriendo modal para nota ID:', noteId);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('viewNoteModal'));
    modal.show();
    
    // Ocultar botón de editar inicialmente
    document.getElementById('editNoteBtn').style.display = 'none';
    
    // Intentar obtener datos de la fila de la tabla primero
    const row = document.querySelector(`button[data-note-id="${noteId}"]`).closest('tr');
    if (row) {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
            const title = cells[1].textContent.trim();
            const content = cells[2].textContent.trim();
            const colorBadge = cells[3].querySelector('.badge');
            const statusBadge = cells[4].querySelector('.badge');
            const date = cells[5].textContent.trim();
            
            // Extraer información de los badges
            const color = colorBadge ? colorBadge.textContent.trim() : 'Amarillo';
            const status = statusBadge ? statusBadge.textContent.trim() : 'Pendiente';
            
            // Crear contenido del modal con datos de la tabla
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
                                <span class="badge" style="background-color: ${getColorValueFromText(color)}; color: white;">
                                    ${color}
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
                window.location.href = `<?= Url::to(['/notes/update']) ?>?id=${noteId}`;
            };
            
            return;
        }
    }
    
    // Si no se pueden obtener datos de la tabla, usar AJAX como respaldo
    document.getElementById('noteModalContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando información de la nota...</p>
        </div>
    `;
    
    // URL de la API
    const apiUrl = `<?= Url::to(['/notes/get-note']) ?>?id=${noteId}`;
    console.log('URL de la API:', apiUrl);
    
    // Cargar datos de la nota
    fetch(apiUrl)
        .then(response => {
            console.log('Respuesta del servidor:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if (data.success) {
                const note = data.note;
                
                // Crear contenido del modal
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Título:</label>
                                <p class="form-control-plaintext">${note.title}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <div>
                                    <span class="badge ${getStatusClass(note.status)}">
                                        ${getStatusIcon(note.status)} ${getStatusName(note.status)}
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
                                    <span class="badge" style="background-color: ${getColorValue(note.color)}; color: white;">
                                        ${getColorName(note.color)}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha de Creación:</label>
                                <p class="form-control-plaintext">${formatDate(note.created_at)}</p>
                            </div>
                        </div>
                    </div>
                    
                    ${note.content ? `
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contenido:</label>
                        <div class="p-3 bg-light rounded" style="min-height: 100px;">
                            ${note.content.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    ` : ''}
                `;
                
                document.getElementById('noteModalContent').innerHTML = content;
                
                // Mostrar botón de editar y configurar su acción
                const editBtn = document.getElementById('editNoteBtn');
                editBtn.style.display = 'inline-block';
                editBtn.onclick = function() {
                    window.location.href = `<?= Url::to(['/notes/update']) ?>?id=${noteId}`;
                };
                
            } else {
                document.getElementById('noteModalContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al cargar la información de la nota: ${data.message || 'Error desconocido'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('noteModalContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error de conexión:</strong> ${error.message}<br>
                    <small>Por favor, verifica la consola del navegador para más detalles.</small>
                </div>
            `;
        });
}

// Funciones auxiliares para formatear datos
function getStatusClass(status) {
    const classMap = {
        'pending': 'bg-warning',
        'processing': 'bg-info',
        'completed': 'bg-success',
    };
    return classMap[status] || 'bg-secondary';
}

function getStatusIcon(status) {
    const iconMap = {
        'pending': '⏳',
        'processing': '🔄',
        'completed': '✅',
    };
    return iconMap[status] || '❓';
}

function getStatusName(status) {
    const nameMap = {
        'pending': 'Pendiente',
        'processing': 'Procesando',
        'completed': 'Completada',
    };
    return nameMap[status] || 'Desconocido';
}

function getColorValue(color) {
    const colorMap = {
        'yellow': '#ffeb3b',
        'blue': '#2196f3',
        'green': '#4caf50',
        'red': '#f44336',
        'orange': '#ff9800',
        'purple': '#9c27b0',
        'pink': '#e91e63',
        'gray': '#9e9e9e',
        'lightblue': '#03a9f4',
        'lightgreen': '#8bc34a',
    };
    return colorMap[color] || '#ffeb3b';
}

function getColorName(color) {
    const nameMap = {
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
    return nameMap[color] || 'Amarillo';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Funciones auxiliares para trabajar con datos de la tabla
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
</script>
