<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $companyInfo array */
/* @var $fileConfigs array */

$this->title = 'Configuración de la Empresa';
$this->params['breadcrumbs'][] = $this->title;

$logoModel = new \app\models\CompanyConfig();
$conditionsModel = new \app\models\CompanyConfig();
?>

<div class="config-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Configuración de Facto Rent a Car
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i>
                            <?= Yii::$app->session->getFlash('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= Yii::$app->session->getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">
                                <i class="fas fa-building"></i> Información de la Empresa
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">
                                <i class="fas fa-file-upload"></i> Archivos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients" type="button" role="tab" aria-controls="clients" aria-selected="false">
                                <i class="fas fa-users"></i> Gestión de Clientes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="false">
                                <i class="fas fa-eye"></i> Vista Previa
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="configTabsContent">
                        <!-- Tab de Información -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <?php $form = ActiveForm::begin([
                                        'action' => ['config/update-company'],
                                        'method' => 'post',
                                        'options' => ['class' => 'needs-validation', 'novalidate' => true]
                                    ]); ?>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <?= Html::label('Nombre de la Empresa', 'company_name', ['class' => 'form-label']) ?>
                                                <?= Html::textInput('company_name', $companyInfo['name'], [
                                                    'class' => 'form-control',
                                                    'id' => 'company_name',
                                                    'required' => true
                                                ]) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <?= Html::label('Teléfono', 'company_phone', ['class' => 'form-label']) ?>
                                                <?= Html::textInput('company_phone', $companyInfo['phone'], [
                                                    'class' => 'form-control',
                                                    'id' => 'company_phone'
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <?= Html::label('Dirección', 'company_address', ['class' => 'form-label']) ?>
                                        <?= Html::textarea('company_address', $companyInfo['address'], [
                                            'class' => 'form-control',
                                            'id' => 'company_address',
                                            'rows' => 3
                                        ]) ?>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <?= Html::label('Email', 'company_email', ['class' => 'form-label']) ?>
                                                <?= Html::textInput('company_email', $companyInfo['email'], [
                                                    'class' => 'form-control',
                                                    'id' => 'company_email',
                                                    'type' => 'email'
                                                ]) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <?= Html::label('SIMPEMOVIL', 'simemovil_number', ['class' => 'form-label']) ?>
                                                <?= Html::textInput('simemovil_number', $companyInfo['simemovil'], [
                                                    'class' => 'form-control',
                                                    'id' => 'simemovil_number',
                                                    'placeholder' => '83670937'
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cuentas Bancarias -->
                                    <div class="mb-4">
                                        <h5><i class="fas fa-university"></i> Cuentas Bancarias</h5>
                                        <div id="bank-accounts-container">
                                            <?php foreach ($companyInfo['bank_accounts'] as $index => $account): ?>
                                                <div class="row bank-account-row mb-3">
                                                    <div class="col-md-3">
                                                        <?= Html::textInput("bank_accounts[{$index}][bank]", $account['bank'], [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Banco (ej: BCR, BN)'
                                                        ]) ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <?= Html::textInput("bank_accounts[{$index}][account]", $account['account'], [
                                                            'class' => 'form-control',
                                                            'placeholder' => 'Número de cuenta'
                                                        ]) ?>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <?= Html::textInput("bank_accounts[{$index}][currency]", $account['currency'], [
                                                            'class' => 'form-control',
                                                            'placeholder' => '₡'
                                                        ]) ?>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-bank-account">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-bank-account">
                                            <i class="fas fa-plus"></i> Agregar Cuenta Bancaria
                                        </button>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Información', [
                                            'class' => 'btn btn-primary'
                                        ]) ?>
                                    </div>

                                    <?php ActiveForm::end(); ?>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6><i class="fas fa-info-circle"></i> Información</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="small text-muted">
                                                Esta información se utilizará en las órdenes de alquiler y documentos oficiales de la empresa.
                                            </p>
                                            <ul class="small">
                                                <li>El nombre aparecerá en el encabezado de las órdenes</li>
                                                <li>La dirección se mostrará en todos los documentos</li>
                                                <li>Las cuentas bancarias aparecerán en las órdenes</li>
                                                <li>El número SIMPEMOVIL se usará para pagos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección del Logo -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">
                                                <i class="fas fa-image"></i> Gestión del Logo de la Empresa
                                            </h4>
                                            <p class="card-subtitle text-muted">Sube y gestiona el logo que aparecerá en las órdenes PDF (90x90px)</p>
                                        </div>
                                        <div class="card-body">
                                            
                                            <!-- Vista Móvil -->
                                            <div class="d-md-none">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-mobile-alt"></i> <strong>Vista Móvil</strong>
                                                </div>
                                                
                                                <!-- Logo Actual Móvil -->
                                                <div class="card mb-3">
                                                    <div class="card-header">
                                                        <h5><i class="fas fa-eye"></i> Logo Actual</h5>
                                                    </div>
                                                    <div class="card-body text-center">
                                                        <?php if ($companyInfo['logo']): ?>
                                                            <div class="mb-3">
                                                                <img src="<?= $companyInfo['logo'] ?>" alt="Logo actual" class="img-fluid" style="max-height: 150px; border: 2px solid #ddd; padding: 10px; border-radius: 10px; background: white;">
                                                                <p class="text-muted mt-2"><small>Logo actual (90x90px)</small></p>
                                                            </div>
                                                            <div class="d-grid gap-2">
                                                                <?= Html::a('<i class="fas fa-external-link-alt"></i> Ver Completo', ['config/preview-logo'], [
                                                                    'class' => 'btn btn-outline-info btn-sm',
                                                                    'target' => '_blank'
                                                                ]) ?>
                                                                <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['config/delete-logo'], [
                                                                    'class' => 'btn btn-outline-danger btn-sm',
                                                                    'data-confirm' => '¿Estás seguro de que deseas eliminar el logo?',
                                                                    'data-method' => 'post'
                                                                ]) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-center text-muted py-4">
                                                                <i class="fas fa-image fa-3x mb-3"></i>
                                                                <p class="h5">No hay logo configurado</p>
                                                                <p>Sube un logo para que aparezca en las órdenes PDF</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Formulario de Subida Móvil -->
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="fas fa-upload"></i> Subir Nuevo Logo</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            <strong>Requisitos del Logo:</strong>
                                                            <ul class="mb-0 mt-2 small">
                                                                <li><strong>Dimensiones:</strong> Cualquier tamaño (se redimensionará a 90x90px)</li>
                                                                <li><strong>Formatos:</strong> PNG, JPG, JPEG, GIF, SVG</li>
                                                                <li><strong>Tamaño máximo:</strong> 2MB</li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <?php $form = ActiveForm::begin([
                                                            'action' => ['config/upload-logo'],
                                                            'options' => ['enctype' => 'multipart/form-data', 'id' => 'mobile-logo-form']
                                                        ]); ?>
                                                        
                                                        <div class="mb-3">
                                                            <label for="mobile-logo-file" class="form-label">Seleccionar Archivo de Logo</label>
                                                            <input type="file" class="form-control" id="mobile-logo-file" name="CompanyConfig[logoFile]" accept="image/*" required>
                                                            <div class="form-text">Formatos: PNG, JPG, JPEG, GIF, SVG (máximo 2MB)</div>
                                                        </div>
                                                        
                                                        <div class="d-grid">
                                                            <button type="submit" class="btn btn-primary btn-lg">
                                                                <i class="fas fa-upload"></i> Subir y Procesar Logo
                                                            </button>
                                                        </div>
                                                        
                                                        <?php ActiveForm::end(); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Vista Desktop -->
                                            <div class="d-none d-md-block">
                                            <div class="row">
                                                <!-- Logo Actual -->
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5><i class="fas fa-eye"></i> Logo Actual</h5>
                                                        </div>
                                                        <div class="card-body text-center">
                                                            <?php if ($companyInfo['logo']): ?>
                                                                <div class="mb-3">
                                                                    <img src="<?= $companyInfo['logo'] ?>" alt="Logo actual" class="img-fluid" style="max-height: 200px; border: 2px solid #ddd; padding: 15px; border-radius: 10px; background: white;">
                                                                    <p class="text-muted mt-2"><small>Logo actual (90x90px)</small></p>
                                                                </div>
                                                                <div class="d-flex gap-2 justify-content-center">
                                                                    <?= Html::a('<i class="fas fa-external-link-alt"></i> Ver Completo', ['config/preview-logo'], [
                                                                        'class' => 'btn btn-outline-info btn-sm',
                                                                        'target' => '_blank'
                                                                    ]) ?>
                                                                    <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['config/delete-logo'], [
                                                                        'class' => 'btn btn-outline-danger btn-sm',
                                                                        'data-confirm' => '¿Estás seguro de que deseas eliminar el logo?',
                                                                        'data-method' => 'post'
                                                                    ]) ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="text-center text-muted py-4">
                                                                    <i class="fas fa-image fa-4x mb-3"></i>
                                                                    <p class="h5">No hay logo configurado</p>
                                                                    <p>Sube un logo para que aparezca en las órdenes PDF</p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Subir Logo -->
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5><i class="fas fa-upload"></i> Subir Nuevo Logo</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="alert alert-info">
                                                                <i class="fas fa-info-circle"></i>
                                                                <strong>Requisitos del Logo:</strong>
                                                                <ul class="mb-0 mt-2">
                                                                    <li><strong>Dimensiones:</strong> Cualquier tamaño (se redimensionará a 90x90px)</li>
                                                                    <li><strong>Formatos:</strong> PNG, JPG, JPEG, GIF, SVG</li>
                                                                    <li><strong>Tamaño máximo:</strong> 2MB</li>
                                                                    <li><strong>Procesamiento:</strong> Redimensionamiento automático manteniendo proporción</li>
                                                                    <li><strong>Calidad:</strong> Optimizado para mejor visualización</li>
                                                                    <li><strong>Mínimo recomendado:</strong> 100x100 píxeles</li>
                                                                </ul>
                                                            </div>

                                                            <?php $form = ActiveForm::begin([
                                                                'action' => ['config/upload-logo'],
                                                                'options' => ['enctype' => 'multipart/form-data']
                                                            ]); ?>

                                                            <div class="mb-3">
                                                                <?= $form->field($logoModel, 'logoFile')->fileInput([
                                                                    'accept' => 'image/*',
                                                                    'class' => 'form-control',
                                                                    'required' => true
                                                                ])->label('Seleccionar Archivo de Logo') ?>
                                                            </div>

                                                            <?= Html::submitButton('<i class="fas fa-upload"></i> Subir y Procesar Logo', [
                                                                'class' => 'btn btn-primary btn-lg w-100'
                                                            ]) ?>

                                                            <?php ActiveForm::end(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Información Adicional -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5><i class="fas fa-lightbulb"></i> Información Adicional</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-file-pdf"></i> En PDFs de Órdenes:</h6>
                                                                    <ul>
                                                                        <li>El logo aparece en la parte superior</li>
                                                                        <li>Tamaño optimizado: 90x90 píxeles</li>
                                                                        <li>Centrado en el encabezado</li>
                                                                        <li>Compatible con todos los formatos de orden</li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-cog"></i> Procesamiento Automático:</h6>
                                                                    <ul>
                                                                        <li>Redimensionamiento inteligente</li>
                                                                        <li>Mantiene proporción original</li>
                                                                        <li>Centrado automático en canvas</li>
                                                                        <li>Optimización de calidad</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab de Logo eliminado - contenido movido a la pestaña de información -->

                        <!-- Tab de Archivos -->
                        <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-file-code"></i> Condiciones del Alquiler (HTML)</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php $form = ActiveForm::begin([
                                                'action' => ['config/update-conditions-html'],
                                                'method' => 'post',
                                            ]); ?>
                                                <div class="mb-3">
                                                    <?= Html::textarea('conditions_html', \app\models\CompanyConfig::getConfig('rental_conditions_html', ''), [
                                                        'class' => 'form-control',
                                                        'rows' => 16,
                                                        'placeholder' => 'Pega aquí el HTML de las condiciones del alquiler. Este contenido será la página 2 del PDF.'
                                                    ]) ?>
                                                    <small class="text-muted">Este contenido se insertará como segunda página en el PDF de rentas. Se acepta HTML básico.</small>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Condiciones (HTML)', ['class' => 'btn btn-primary']) ?>
                                                </div>
                                            <?php ActiveForm::end(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab de Gestión de Clientes -->
                        <div class="tab-pane fade" id="clients" role="tabpanel" aria-labelledby="clients-tab">
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5><i class="fas fa-users"></i> Importar/Exportar Clientes</h5>
                                            <p class="card-subtitle text-muted">Gestiona la importación masiva de clientes mediante archivos Excel</p>
                                        </div>
                                        <div class="card-body">
                                            <!-- Estadísticas del Sistema -->
                                            <div class="row mb-4">
                                                <div class="col-md-3">
                                                    <div class="card bg-primary text-white">
                                                        <div class="card-body text-center">
                                                            <h3><?= \app\models\Client::find()->count() ?: 0 ?></h3>
                                                            <p class="mb-0">Clientes Totales</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-success text-white">
                                                        <div class="card-body text-center">
                                                            <h3><?= \app\models\Client::find()->where(['status' => 'active'])->count() ?: 0 ?></h3>
                                                            <p class="mb-0">Clientes Activos</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-info text-white">
                                                        <div class="card-body text-center">
                                                            <h3><?= \app\models\Client::find()->where(['es_cliente_facto' => 1])->count() ?: 0 ?></h3>
                                                            <p class="mb-0">Clientes Facto</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="card bg-warning text-white">
                                                        <div class="card-body text-center">
                                                            <h3><?= \app\models\Client::find()->where(['es_aliado' => 1])->count() ?: 0 ?></h3>
                                                            <p class="mb-0">Aliados</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Exportar Plantilla -->
                                                <div class="col-md-6">
                                                    <div class="card h-100">
                                                        <div class="card-header bg-success text-white">
                                                            <h5><i class="fas fa-download"></i> Exportar Plantilla Excel</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <p>Descarga la plantilla Excel con la estructura correcta para importar clientes.</p>
                                                            <ul class="list-unstyled">
                                                                <li><i class="fas fa-check text-success"></i> Encabezados predefinidos</li>
                                                                <li><i class="fas fa-check text-success"></i> Datos de ejemplo</li>
                                                                <li><i class="fas fa-check text-success"></i> Validaciones incluidas</li>
                                                            </ul>
                                                            <div class="d-grid">
                                                                <?= Html::a('<i class="fas fa-download"></i> Descargar Plantilla Excel', ['config/export-client-template'], [
                                                                    'class' => 'btn btn-success btn-lg',
                                                                    'target' => '_blank'
                                                                ]) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Importar Clientes -->
                                                <div class="col-md-6">
                                                    <div class="card h-100">
                                                        <div class="card-header bg-primary text-white">
                                                            <h5><i class="fas fa-upload"></i> Importar Clientes</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <p>Sube un archivo Excel con los datos de los clientes a importar.</p>
                                                            
                                                            <?php $form = ActiveForm::begin([
                                                                'action' => ['config/import-clients'],
                                                                'options' => ['enctype' => 'multipart/form-data']
                                                            ]); ?>

                                                            <div class="mb-3">
                                                                <?= $form->field($model, 'clientsFile')->fileInput([
                                                                    'accept' => '.xlsx,.xls',
                                                                    'class' => 'form-control',
                                                                    'required' => true
                                                                ])->label('Seleccionar Archivo Excel') ?>
                                                                <small class="form-text text-muted">
                                                                    Formatos: .xlsx, .xls. Tamaño máximo: 10MB
                                                                </small>
                                                            </div>

                                                            <div class="d-grid">
                                                                <?= Html::submitButton('<i class="fas fa-upload"></i> Importar Clientes', [
                                                                    'class' => 'btn btn-primary btn-lg'
                                                                ]) ?>
                                                            </div>

                                                            <?php ActiveForm::end(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Estructura de la Plantilla -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5><i class="fas fa-table"></i> Estructura de la Plantilla Excel</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped">
                                                                    <thead class="table-dark">
                                                                        <tr>
                                                                            <th style="width: 10%;">Columna</th>
                                                                            <th style="width: 20%;">Campo</th>
                                                                            <th style="width: 25%;">Descripción</th>
                                                                            <th style="width: 15%;">Requerido</th>
                                                                            <th style="width: 30%;">Ejemplo</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td><strong>A</strong></td>
                                                                            <td>Nombre Completo</td>
                                                                            <td>Nombre y apellidos del cliente</td>
                                                                            <td><span class="badge bg-danger">Sí</span></td>
                                                                            <td>Juan Pérez González</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>B</strong></td>
                                                                            <td>Cédula Física</td>
                                                                            <td>Número de identificación</td>
                                                                            <td><span class="badge bg-danger">Sí</span></td>
                                                                            <td>123456789</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>C</strong></td>
                                                                            <td>Email</td>
                                                                            <td>Correo electrónico</td>
                                                                            <td><span class="badge bg-warning">Opcional</span></td>
                                                                            <td>juan@email.com</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>D</strong></td>
                                                                            <td>WhatsApp</td>
                                                                            <td>Número de WhatsApp</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>8888-8888</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>E</strong></td>
                                                                            <td>Dirección</td>
                                                                            <td>Dirección física</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>San José, Costa Rica</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>F</strong></td>
                                                                            <td>Es Cliente Facto</td>
                                                                            <td>1=Sí, 0=No</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>1</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>G</strong></td>
                                                                            <td>Es Aliado</td>
                                                                            <td>1=Sí, 0=No</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>0</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>H</strong></td>
                                                                            <td>Estado</td>
                                                                            <td>active/inactive</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>active</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>I</strong></td>
                                                                            <td>Notas</td>
                                                                            <td>Información adicional</td>
                                                                            <td><span class="badge bg-secondary">Opcional</span></td>
                                                                            <td>Cliente preferencial</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Instrucciones -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5><i class="fas fa-info-circle"></i> Instrucciones de Uso</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-download text-success"></i> Paso 1: Descargar Plantilla</h6>
                                                                    <ol>
                                                                        <li>Haz clic en "Descargar Plantilla Excel"</li>
                                                                        <li>Guarda el archivo en tu computadora</li>
                                                                        <li>Abre el archivo con Excel o similar</li>
                                                                    </ol>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-edit text-primary"></i> Paso 2: Completar Datos</h6>
                                                                    <ol>
                                                                        <li>Completa las filas con datos de clientes</li>
                                                                        <li>No modifiques los encabezados</li>
                                                                        <li>Guarda el archivo en formato .xlsx</li>
                                                                    </ol>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-upload text-info"></i> Paso 3: Importar</h6>
                                                                    <ol>
                                                                        <li>Selecciona el archivo completado</li>
                                                                        <li>Haz clic en "Importar Clientes"</li>
                                                                        <li>Revisa los resultados de la importación</li>
                                                                    </ol>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6><i class="fas fa-exclamation-triangle text-warning"></i> Consideraciones</h6>
                                                                    <ul>
                                                                        <li>Los clientes duplicados se omitirán</li>
                                                                        <li>Se validarán campos requeridos</li>
                                                                        <li>Máximo 10MB por archivo</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab de Vista Previa -->
                        <div class="tab-pane fade" id="preview" role="tabpanel" aria-labelledby="preview-tab">
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5><i class="fas fa-eye"></i> Vista Previa de la Orden</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="order-preview">
                                                <!-- Header -->
                                                <div class="row mb-4">
                                                    <div class="col-md-8">
                                                        <h2 class="text-primary">
                                                            <?= Html::encode($companyInfo['name']) ?>
                                                        </h2>
                                                        <p class="text-muted mb-0">
                                                            <?= nl2br(Html::encode($companyInfo['address'])) ?>
                                                        </p>
                                                        <?php if ($companyInfo['phone']): ?>
                                                            <p class="text-muted mb-0">Tel: <?= Html::encode($companyInfo['phone']) ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($companyInfo['email']): ?>
                                                            <p class="text-muted mb-0">Email: <?= Html::encode($companyInfo['email']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <?php if ($companyInfo['logo']): ?>
                                                            <img src="<?= $companyInfo['logo'] ?>" alt="Logo" class="img-fluid" style="max-height: 100px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: white;">
                                                            <p class="text-muted mt-2"><small>Logo en PDF (90x90px)</small></p>
                                                        <?php else: ?>
                                                            <div class="bg-light p-4 rounded border">
                                                                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted mb-0"><small>Logo no cargado</small></p>
                                                                <p class="text-muted"><small>Sube un logo en la pestaña "Logo"</small></p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Cuentas Bancarias -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Cuentas Bancarias:</h6>
                                                        <?php foreach ($companyInfo['bank_accounts'] as $account): ?>
                                                            <p class="mb-1">
                                                                <strong><?= Html::encode($account['bank']) ?>:</strong> 
                                                                <?= Html::encode($account['account']) ?>
                                                            </p>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Pago Móvil:</h6>
                                                        <p class="mb-1">
                                                            <strong>SIMPEMOVIL:</strong> <?= Html::encode($companyInfo['simemovil']) ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Condiciones -->
                                                <?php if ($companyInfo['conditions']): ?>
                                                    <div class="mt-4">
                                                        <h6>Condiciones de Alquiler:</h6>
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-file-pdf"></i> 
                                                            Las condiciones de alquiler se adjuntan como segunda página de la orden.
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Agregar cuenta bancaria
    $('#add-bank-account').click(function() {
        const container = $('#bank-accounts-container');
        const index = container.find('.bank-account-row').length;
        
        const newRow = `
            <div class="row bank-account-row mb-3">
                <div class="col-md-3">
                    <input type="text" name="bank_accounts[${index}][bank]" class="form-control" placeholder="Banco (ej: BCR, BN)">
                </div>
                <div class="col-md-6">
                    <input type="text" name="bank_accounts[${index}][account]" class="form-control" placeholder="Número de cuenta">
                </div>
                <div class="col-md-2">
                    <input type="text" name="bank_accounts[${index}][currency]" class="form-control" placeholder="₡" value="₡">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-bank-account">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.append(newRow);
    });
    
    // Eliminar cuenta bancaria
    $(document).on('click', '.remove-bank-account', function() {
        $(this).closest('.bank-account-row').remove();
    });
    
    // JavaScript del logo movido a la pestaña de información
    
    // Mejorar funcionalidad del formulario de logo
    const logoForms = document.querySelectorAll('form[action*="upload-logo"]');
    logoForms.forEach((form, index) => {
        console.log('Formulario de logo encontrado:', index, form);
        
        // Agregar validación en tiempo real
        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('Archivo seleccionado:', {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    });
                    
                    // Validar tamaño (2MB máximo)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('El archivo es demasiado grande. El tamaño máximo es 2MB.');
                        e.target.value = '';
                        return;
                    }
                    
                    // Validar tipo
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Tipo de archivo no permitido. Solo se permiten: PNG, JPG, JPEG, GIF, SVG.');
                        e.target.value = '';
                        return;
                    }
                    
                    console.log('✅ Archivo válido para subir');
                }
            });
        }
        
        // Manejar envío del formulario
        form.addEventListener('submit', function(e) {
            console.log('Formulario de logo enviado');
            
            // Verificar que hay un archivo seleccionado
            const fileInput = form.querySelector('input[type="file"]');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Por favor selecciona un archivo de logo.');
                return false;
            }
            
            // Mostrar indicador de carga
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                submitBtn.disabled = true;
                
                // Restaurar botón después de 10 segundos como fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
    
    // Función para probar subida de archivo
    window.testLogoUpload = function() {
        console.log('Probando funcionalidad de subida...');
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.click();
        } else {
            console.error('No se encontró input de archivo');
        }
    };
    
    console.log('Funciones adicionales disponibles: testLogoUpload()');
});
</script>