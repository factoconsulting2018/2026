<?php
/** @var yii\web\View $this */
/** @var app\models\Client $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Cliente: ' . $model->fullNameUppercase;
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="client-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>👤 <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('✏️ Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('← Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>🆔 Cédula:</strong> <?= Html::encode($model->cedula_fisica) ?></p>
                            <p><strong>📧 Email:</strong> <?= Html::encode($model->email) ?></p>
                            <p><strong>📱 WhatsApp:</strong> <?= Html::encode($model->whatsapp) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>📅 Registro:</strong> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                            <p><strong>🔄 Actualizado:</strong> <?= Yii::$app->formatter->asDate($model->updated_at) ?></p>
                            <p><strong>📊 Estado:</strong> 
                                <span class="badge <?= $model->status === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $model->status === 'active' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <?php if ($model->address): ?>
                    <p><strong>📍 Dirección:</strong> <?= Html::encode($model->address) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Biblioteca de Archivos -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📚 Biblioteca de Archivos</h5>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#upload-form-collapse" aria-expanded="false" aria-controls="upload-form-collapse">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">upload_file</span>
                        Subir Archivo
                    </button>
                </div>
                <div class="card-body">
                    <!-- Formulario de Subida (Colapsable) -->
                    <div class="collapse mb-4" id="upload-form-collapse">
                        <div class="card" style="background: #f8f9fa;">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">upload_file</span>
                                    Subir Nuevo Archivo
                                </h6>
                                <form id="file-upload-form" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Archivo *</label>
                                            <input type="file" class="form-control" id="file-input" name="file" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.xls,.docx,.doc" required>
                                            <small class="form-text text-muted">Formatos permitidos: PDF, PNG, JPG, XLSX, DOCX (máximo 10MB)</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre del Archivo *</label>
                                            <input type="text" class="form-control" id="file-name-input" placeholder="Ej: Contrato 2025" required>
                                            <small class="form-text text-muted">Nombre personalizado para identificar el archivo</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Descripción (Opcional)</label>
                                            <textarea class="form-control" id="file-description-input" rows="2" placeholder="Descripción adicional del archivo"></textarea>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary" id="upload-file-btn" data-client-id="<?= $model->id ?>" onclick="uploadFile()">
                                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">upload</span>
                                        Subir Archivo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador de Archivos -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="material-symbols-outlined">search</span>
                            </span>
                            <input type="text" class="form-control" id="file-search-input" placeholder="Buscar archivos por nombre o descripción...">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchFiles()">
                                Buscar
                            </button>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearFileSearch()" title="Limpiar búsqueda">
                                <span class="material-symbols-outlined">clear</span>
                            </button>
                        </div>
                    </div>

                    <!-- Overlay de Loading para Subida de Archivos -->
                    <div id="file-upload-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
                        <div class="text-center bg-white p-5 rounded shadow-lg" style="max-width: 400px; margin: auto;">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Subiendo...</span>
                            </div>
                            <h5 class="mb-2">Subiendo archivo...</h5>
                            <p class="text-muted mb-3">Por favor, espere mientras se procesa el archivo.</p>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Archivos -->
                    <div id="files-container">
                        <div class="text-center text-muted py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-3">Cargando archivos...</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->tipo_identificacion || $model->situacion_tributaria): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🏛️ Información de Hacienda</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($model->tipo_identificacion): ?>
                            <p><strong>Tipo de Identificación:</strong> <?= Html::encode($model->tipo_identificacion) ?></p>
                            <?php endif; ?>
                            <?php if ($model->situacion_tributaria): ?>
                            <p><strong>Situación Tributaria:</strong> <?= Html::encode($model->situacion_tributaria) ?></p>
                            <?php endif; ?>
                            <?php if ($model->regimen_tributario): ?>
                            <p><strong>Régimen Tributario:</strong> <?= Html::encode($model->regimen_tributario) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($model->actividad_economica_codigo): ?>
                            <p><strong>Código de Actividad:</strong> <?= Html::encode($model->actividad_economica_codigo) ?></p>
                            <?php endif; ?>
                            <?php if ($model->actividad_economica_descripcion): ?>
                            <p><strong>Actividad Económica:</strong> <?= Html::encode($model->actividad_economica_descripcion) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($model->notes): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📝 Notas</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(Html::encode($model->notes)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">⚙️ Configuración</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>🏢 Cliente Facto:</strong> 
                        <span class="badge <?= $model->es_cliente_facto ? 'bg-primary' : 'bg-secondary' ?>">
                            <?= $model->es_cliente_facto ? 'Sí' : 'No' ?>
                        </span>
                    </p>
                    <p>
                        <strong>🤝 Es Aliado:</strong> 
                        <span class="badge <?= $model->es_aliado ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $model->es_aliado ? 'Sí' : 'No' ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('✏️ Editar Cliente', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('📋 Nuevo Alquiler', ['/rental/create', 'client_id' => $model->id], ['class' => 'btn btn-success']) ?>
                        <?= Html::a('📊 Ver Alquileres', ['/rental/index', 'client_id' => $model->id], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('🗑️ Eliminar', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar este cliente?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el archivo JavaScript externo para la biblioteca de archivos
$this->registerJsFile('@web/js/client-form.js', ['depends' => [yii\web\JqueryAsset::class]]);

// Inicializar carga de archivos al cargar la página
$this->registerJs("
    // Inicializar currentClientId cuando se carga la página
    if (typeof currentClientId === 'undefined') {
        window.currentClientId = {$model->id};
    }
    
    // Cargar archivos del cliente al inicializar
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof loadFiles === 'function') {
            loadFiles({$model->id}, '');
        }
    });
", \yii\web\View::POS_READY);
?>