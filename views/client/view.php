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

            <!-- Gestor de la biblioteca -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📚 Gestor de la Biblioteca</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <span class="material-symbols-outlined me-2" style="font-size: 24px; color: #007bff;">folder_open</span>
                                <div>
                                    <h6 class="mb-0">Documentos del Cliente</h6>
                                    <small class="text-muted">Gestiona archivos y documentos</small>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="uploadDocument()">
                                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">upload</span>
                                    Subir Documento
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="viewDocuments()">
                                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">visibility</span>
                                    Ver Documentos
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <span class="material-symbols-outlined me-2" style="font-size: 24px; color: #28a745;">description</span>
                                <div>
                                    <h6 class="mb-0">Contratos y Acuerdos</h6>
                                    <small class="text-muted">Documentos legales y contractuales</small>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="createContract()">
                                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">add</span>
                                    Nuevo Contrato
                                </button>
                                <button class="btn btn-outline-warning btn-sm" onclick="viewContracts()">
                                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">list</span>
                                    Ver Contratos
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de documentos recientes -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">📄 Documentos Recientes</h6>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #dc3545;">picture_as_pdf</span>
                                    <div>
                                        <div class="fw-bold">Contrato_Alquiler_001</div>
                                        <small class="text-muted">Subido hace 2 días • PDF</small>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadDocument('Contrato_Alquiler_001.pdf')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteDocument('Contrato_Alquiler_001.pdf')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #007bff;">description</span>
                                    <div>
                                        <div class="fw-bold">Cedula_Identidad_Cliente</div>
                                        <small class="text-muted">Subido hace 1 semana • PDF</small>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadDocument('Cedula_Identidad_Cliente.pdf')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteDocument('Cedula_Identidad_Cliente.pdf')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                    </button>
                                </div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #28a745;">image</span>
                                    <div>
                                        <div class="fw-bold">Licencia_Conducir_Frontal</div>
                                        <small class="text-muted">Subido hace 2 semanas • JPG</small>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm" onclick="downloadDocument('Licencia_Conducir_Frontal.jpg')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteDocument('Licencia_Conducir_Frontal.jpg')">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                    </button>
                                </div>
                            </div>
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
// Cargar el archivo JavaScript externo para el gestor de biblioteca
$this->registerJsFile('@web/js/library-manager.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
?>