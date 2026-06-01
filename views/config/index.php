<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $companyInfo array */
/* @var $fileConfigs array */
/* @var $incidentNotifEnabled bool */
/* @var $incidentNotifFrequencyDays int */
/* @var $rentalOrderPdfFormat string */

$this->title = 'Configuración de la Empresa';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js', ['position' => \yii\web\View::POS_END]);

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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab" aria-controls="api" aria-selected="false">
                                <i class="fas fa-code"></i> API
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notificaciones-tab" data-bs-toggle="tab" data-bs-target="#notificaciones" type="button" role="tab" aria-controls="notificaciones" aria-selected="false">
                                <i class="fas fa-bell"></i> Notificaciones
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dekra-tab" data-bs-toggle="tab" data-bs-target="#dekra" type="button" role="tab" aria-controls="dekra" aria-selected="false">
                                <i class="fas fa-car-side"></i> Mantenimiento Dekra
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab" aria-controls="whatsapp" aria-selected="false">
                                <i class="fab fa-whatsapp text-success"></i> WhatsApp
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="marketing-tab" data-bs-toggle="tab" data-bs-target="#marketing" type="button" role="tab" aria-controls="marketing" aria-selected="false">
                                <i class="fas fa-bullhorn text-primary"></i> Marketing
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

                                    <div class="mb-4">
                                        <?= Html::label('Requisitos para Solicitud de Membresía', 'company_requirements_editor', ['class' => 'form-label']) ?>
                                        <div id="company_requirements_editor" class="bg-white"></div>
                                        <?= Html::textarea('company_requirements', $companyInfo['requirements'] ?? '', [
                                            'id' => 'company_requirements',
                                            'class' => 'd-none',
                                        ]) ?>
                                        <div class="form-text">
                                            Este contenido se mostrará en <code>/solicitud-membresia</code> antes del formulario.
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

                                    <div class="mb-4 mt-3 border rounded p-3 bg-light" id="orden-renta-pdf">
                                        <h5 class="mb-2"><i class="fas fa-file-pdf"></i> Formato PDF — Orden de renta</h5>
                                        <p class="small text-muted mb-3">Define cómo se maqueta la primera página del PDF de la orden de alquiler. La segunda página sigue siendo las condiciones configuradas en el sistema.</p>
                                        <?php $pdfFmtForm = ActiveForm::begin([
                                            'action' => ['config/update-rental-order-pdf-format'],
                                            'method' => 'post',
                                            'options' => ['id' => 'rental-order-pdf-format-form'],
                                        ]); ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="rental_order_pdf_format" id="rental_pdf_general" value="general" <?= ($rentalOrderPdfFormat ?? 'general') === 'general' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="rental_pdf_general"><strong>General</strong> — formato actual en dos columnas con colores de acento.</label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="rental_order_pdf_format" id="rental_pdf_moderna" value="moderna" <?= ($rentalOrderPdfFormat ?? '') === 'moderna' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="rental_pdf_moderna"><strong>Moderna</strong> — diseño tipo carta con secciones (cliente, entrega, devolución, vehículo, resumen y totales).</label>
                                        </div>
                                        <div id="rental-pdf-moderna-options" class="border rounded p-3 mb-3 bg-white" style="<?= ($rentalOrderPdfFormat ?? '') === 'moderna' ? '' : 'display:none;' ?>">
                                            <h6 class="mb-2"><i class="fas fa-car"></i> Imagen del vehículo en PDF moderna</h6>
                                            <p class="small text-muted mb-3">Tamaño máximo de la foto en la banda gris bajo el encabezado (valores en píxeles).</p>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="rental_order_pdf_vehicle_img_max_w">Ancho máximo (px)</label>
                                                    <input type="number" class="form-control form-control-sm" name="rental_order_pdf_vehicle_img_max_w" id="rental_order_pdf_vehicle_img_max_w" min="40" max="400" step="5" value="<?= (int) ($rentalOrderPdfVehicleImgMaxW ?? 170) ?>">
                                                    <div class="form-text">Entre 40 y 400. Predeterminado: 170.</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label" for="rental_order_pdf_vehicle_img_max_h">Alto máximo (px)</label>
                                                    <input type="number" class="form-control form-control-sm" name="rental_order_pdf_vehicle_img_max_h" id="rental_order_pdf_vehicle_img_max_h" min="30" max="280" step="5" value="<?= (int) ($rentalOrderPdfVehicleImgMaxH ?? 90) ?>">
                                                    <div class="form-text">Entre 30 y 280. Predeterminado: 90.</div>
                                                </div>
                                            </div>
                                            <hr class="my-3">
                                            <h6 class="mb-2"><i class="fas fa-text-height"></i> Tamaño de textos en PDF moderna</h6>
                                            <p class="small text-muted mb-2">Encabezado azul y bloque de empresa (Facto Rent a Car, dirección, WhatsApp).</p>
                                            <?php
                                            $pdfTextForm = $rentalOrderPdfTextFormValues ?? [];
                                            $pdfTextBase = $rentalOrderPdfTextBaseSizes ?? [];
                                            $pdfTextModeVal = $rentalOrderPdfTextMode ?? 'proporcional';
                                            ?>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="radio" name="rental_order_pdf_text_mode" id="rental_pdf_text_proporcional" value="proporcional" <?= $pdfTextModeVal === 'proporcional' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="rental_pdf_text_proporcional"><strong>Proporcional</strong> — un porcentaje escala todos los textos.</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="rental_order_pdf_text_mode" id="rental_pdf_text_numeros" value="numeros" <?= $pdfTextModeVal === 'numeros' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="rental_pdf_text_numeros"><strong>Números (pt)</strong> — define cada tamaño en puntos.</label>
                                            </div>
                                            <div id="rental-pdf-text-proporcional" class="mb-3" style="<?= $pdfTextModeVal === 'proporcional' ? '' : 'display:none;' ?>">
                                                <label class="form-label" for="rental_order_pdf_text_scale">Escala de textos (%)</label>
                                                <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_scale" id="rental_order_pdf_text_scale" min="50" max="300" step="5" value="<?= (int) ($rentalOrderPdfTextScale ?? 100) ?>">
                                                <div class="form-text">100 = tamaño base. 200 = el doble. Rango: 50–300.</div>
                                            </div>
                                            <div id="rental-pdf-text-numeros" class="mb-2" style="<?= $pdfTextModeVal === 'numeros' ? '' : 'display:none;' ?>">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="rental_order_pdf_text_header_titulo">ORDEN DE ALQUILER (pt)</label>
                                                        <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_header_titulo" id="rental_order_pdf_text_header_titulo" min="8" max="120" value="<?= (int) ($pdfTextForm['header_titulo'] ?? $pdfTextBase['header_titulo'] ?? 39) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="rental_order_pdf_text_header_modelo">Modelo vehículo (pt)</label>
                                                        <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_header_modelo" id="rental_order_pdf_text_header_modelo" min="8" max="120" value="<?= (int) ($pdfTextForm['header_modelo'] ?? $pdfTextBase['header_modelo'] ?? 48) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="rental_order_pdf_text_header_meta">No. orden y fecha (pt)</label>
                                                        <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_header_meta" id="rental_order_pdf_text_header_meta" min="8" max="120" value="<?= (int) ($pdfTextForm['header_meta'] ?? $pdfTextBase['header_meta'] ?? 27) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="rental_order_pdf_text_empresa_nombre">Nombre empresa (pt)</label>
                                                        <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_empresa_nombre" id="rental_order_pdf_text_empresa_nombre" min="8" max="120" value="<?= (int) ($pdfTextForm['empresa_nombre'] ?? $pdfTextBase['empresa_nombre'] ?? 36) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="rental_order_pdf_text_empresa_linea">Líneas empresa / contacto (pt)</label>
                                                        <input type="number" class="form-control form-control-sm" name="rental_order_pdf_text_empresa_linea" id="rental_order_pdf_text_empresa_linea" min="8" max="120" value="<?= (int) ($pdfTextForm['empresa_linea'] ?? $pdfTextBase['empresa_linea'] ?? 24) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar formato PDF', ['class' => 'btn btn-primary btn-sm']) ?>
                                        <?php ActiveForm::end(); ?>
                                    </div>
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
                                                <li>El formato <strong>General / Moderna</strong> aplica al PDF de la orden de renta (bloque inferior)</li>
                                                <li>En <strong>Moderna</strong> puedes ajustar la foto del vehículo y el tamaño de textos del encabezado y empresa</li>
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

<style>
/* Colores para tabs de Configuración */
#info-tab { background-color: #f1f3f5; color: #212529; }
#files-tab { background-color: #e7f5ff; color: #0b7285; }
#clients-tab { background-color: #fff3bf; color: #7f5f01; }
#preview-tab { background-color: #ffe3e3; color: #c92a2a; }
#api-tab { background-color: #f3e8ff; color: #5f3dc4; }
#notificaciones-tab { background-color: #fff4e6; color: #d9480f; }
#dekra-tab { background-color: #e6fcf5; color: #087f5b; }
#whatsapp-tab { background-color: #d3f9d8; color: #2b8a3e; }

/* Permite que los tabs hagan wrap a varias filas si no caben en una sola */
#configTabs.nav-tabs {
    flex-wrap: wrap;
    row-gap: 4px;
}

.nav-tabs .nav-link {
    margin-right: 6px;
    border-radius: 6px 6px 0 0;
    border: 1px solid transparent;
}
.nav-tabs .nav-link.active {
    font-weight: 600;
    border-color: #dee2e6 #dee2e6 #fff;
    filter: brightness(1.03);
    box-shadow: inset 0 -2px 0 rgba(0,0,0,0.05);
}
.nav-tabs .nav-link:hover {
    filter: brightness(0.96);
}
</style>
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

                        <!-- Tab de API -->
                        <div class="tab-pane fade" id="api" role="tabpanel" aria-labelledby="api-tab">
                            <div class="row mt-4">
                                <div class="col-12">
                                    <!-- Mostrar nueva API Key si se acaba de crear -->
                                    <?php if (Yii::$app->session->hasFlash('new_api_key')): ?>
                                        <div class="alert alert-success alert-dismissible fade show">
                                            <h5><i class="fas fa-key"></i> Nueva API Key Creada</h5>
                                            <p><strong>IMPORTANTE:</strong> Esta es la única vez que verás esta key. Cópiala y guárdala en un lugar seguro.</p>
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" id="new-api-key" value="<?= Html::encode(Yii::$app->session->getFlash('new_api_key')) ?>" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyApiKey()">
                                                    <i class="fas fa-copy"></i> Copiar
                                                </button>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Formulario para crear nueva API Key -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5><i class="fas fa-plus-circle"></i> Crear Nueva API Key</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $apiKeyModel = new \app\models\ApiKey();
                                            $form = ActiveForm::begin([
                                                'action' => ['config/create-api-key'],
                                                'method' => 'post',
                                                'options' => ['class' => 'needs-validation', 'novalidate' => true]
                                            ]); ?>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <?= $form->field($apiKeyModel, 'name')->textInput([
                                                            'class' => 'form-control',
                                                            'required' => true,
                                                            'placeholder' => 'Ej: API de Producción, API de Desarrollo',
                                                            'name' => 'name'
                                                        ])->label('Nombre') ?>
                                                        <small class="form-text text-muted">Nombre descriptivo para identificar esta API Key</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <?= $form->field($apiKeyModel, 'description')->textarea([
                                                            'class' => 'form-control',
                                                            'rows' => 2,
                                                            'placeholder' => 'Descripción opcional de para qué se usará esta key',
                                                            'name' => 'description'
                                                        ])->label('Descripción') ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-key"></i> Crear API Key
                                            </button>
                                            
                                            <?php ActiveForm::end(); ?>
                                        </div>
                                    </div>

                                    <!-- Mensaje si la tabla no existe -->
                                    <?php
                                    $tableExists = true;
                                    try {
                                        $tableSchema = Yii::$app->db->getTableSchema('api_keys', true);
                                        $tableExists = ($tableSchema !== null);
                                    } catch (\Exception $e) {
                                        $tableExists = false;
                                    }
                                    ?>
                                    <?php if (!$tableExists): ?>
                                        <div class="alert alert-warning">
                                            <h5><i class="fas fa-exclamation-triangle"></i> Tabla API Keys No Existe</h5>
                                            <p>La tabla <code>api_keys</code> no existe en la base de datos. Para habilitar la funcionalidad de API Keys, ejecuta la migración:</p>
                                            <pre class="bg-light p-3 rounded mt-2"><code>sudo docker-compose exec app php yii migrate</code></pre>
                                            <p class="mb-0"><strong>O desde el servidor:</strong></p>
                                            <pre class="bg-light p-3 rounded mt-2"><code>cd /var/www/html/app/factorentacar
sudo docker-compose exec app php yii migrate</code></pre>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Lista de API Keys existentes -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h5><i class="fas fa-list"></i> API Keys Existentes</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!$tableExists): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Ejecuta la migración primero para habilitar la gestión de API Keys.
                                                </div>
                                            <?php elseif (empty($apiKeys)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No hay API Keys creadas. Crea una nueva usando el formulario de arriba.
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Nombre</th>
                                                                <th>Key (primeros 20 caracteres)</th>
                                                                <th>Estado</th>
                                                                <th>Último Uso</th>
                                                                <th>Creada</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($apiKeys as $key): ?>
                                                                <tr>
                                                                    <td>
                                                                        <strong><?= Html::encode($key->name) ?></strong>
                                                                        <?php if ($key->description): ?>
                                                                            <br><small class="text-muted"><?= Html::encode($key->description) ?></small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <code><?= Html::encode(substr($key->key, 0, 20)) ?>...</code>
                                                                    </td>
                                                                    <td>
                                                                        <?= $key->getStatusBadge() ?>
                                                                    </td>
                                                                    <td>
                                                                        <?= $key->getFormattedLastUsed() ?>
                                                                    </td>
                                                                    <td>
                                                                        <?= Yii::$app->formatter->asDate($key->created_at) ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($key->is_active): ?>
                                                                            <?= Html::a('<i class="fas fa-ban"></i> Desactivar', 
                                                                                ['config/toggle-api-key', 'id' => $key->id],
                                                                                [
                                                                                    'class' => 'btn btn-sm btn-warning',
                                                                                    'data' => ['method' => 'post', 'confirm' => '¿Estás seguro de desactivar esta API Key?']
                                                                                ]) ?>
                                                                        <?php else: ?>
                                                                            <?= Html::a('<i class="fas fa-check"></i> Activar', 
                                                                                ['config/toggle-api-key', 'id' => $key->id],
                                                                                [
                                                                                    'class' => 'btn btn-sm btn-success',
                                                                                    'data' => ['method' => 'post']
                                                                                ]) ?>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?= Html::a('<i class="fas fa-trash"></i> Eliminar', 
                                                                            ['config/delete-api-key', 'id' => $key->id],
                                                                            [
                                                                                'class' => 'btn btn-sm btn-danger',
                                                                                'data' => ['method' => 'post', 'confirm' => '¿Estás seguro de eliminar esta API Key? Esta acción no se puede deshacer.']
                                                                            ]) ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Información sobre uso de API -->
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h5><i class="fas fa-info-circle"></i> Información sobre la API</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>La API REST está disponible en: <code>https://app.factorentacar.com/api/v1/</code></p>
                                            <p class="text-muted"><small>URL actual: <?= Yii::$app->request->hostInfo ?>/api/v1/</small></p>
                                            <p>Para usar la API, incluye tu API Key en el header de la petición:</p>
                                            <pre class="bg-light p-3 rounded"><code>X-API-Key: tu_api_key_aqui</code></pre>
                                            <p>O como parámetro de query:</p>
                                            <pre class="bg-light p-3 rounded"><code>?api_key=tu_api_key_aqui</code></pre>
                                            <p class="mb-0">
                                                <a href="<?= Url::to(['config/api-docs']) ?>" class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-book"></i> Ver Documentación Completa de la API
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="dekra" role="tabpanel" aria-labelledby="dekra-tab">
                            <?php
                            $dekraConfig = $dekraConfig ?? \app\models\CompanyConfig::getDekraConfig();
                            $dekraDefaultMap = $dekraDefaultMap ?? \app\models\CompanyConfig::getDekraDefaultPlateMonthMap();
                            $monthNames = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                            ];
                            ?>
                            <div class="row mt-4">
                                <div class="col-lg-10">
                                    <h5 class="mb-3"><i class="fas fa-car-side"></i> Recordatorios automáticos de Dekra (Revisión Vehicular)</h5>
                                    <p class="text-muted">
                                        Cuando entres a <strong>Mantenimiento</strong>, el sistema genera automáticamente una orden por vehículo y por año (la Revisión Técnica Vehicular se realiza una vez al año). El mes se asigna según el <strong>último dígito numérico de la placa</strong> y el <strong>mapeo</strong> que definas abajo.
                                    </p>

                                    <?php $dekraForm = ActiveForm::begin([
                                        'action' => ['config/update-dekra-config'],
                                        'method' => 'post',
                                        'options' => ['class' => 'needs-validation', 'novalidate' => true, 'id' => 'dekra-config-form'],
                                    ]); ?>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" name="dekra_enabled" value="1" id="dekra_enabled" <?= $dekraConfig['enabled'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="dekra_enabled">Generar recordatorios automáticamente</label>
                                            </div>
                                            <small class="text-muted">Si lo desactivas, no se crearán órdenes al abrir el módulo de mantenimiento.</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="dekra_years_ahead">Años futuros</label>
                                            <input type="number" class="form-control" min="0" max="20" name="dekra_years_ahead" id="dekra_years_ahead" value="<?= (int) $dekraConfig['years_ahead'] ?>">
                                            <small class="text-muted">Cantidad de años a partir del actual (0 = solo año en curso).</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="dekra_day_of_month">Día del mes</label>
                                            <input type="number" class="form-control" min="1" max="28" name="dekra_day_of_month" id="dekra_day_of_month" value="<?= (int) $dekraConfig['day_of_month'] ?>">
                                            <small class="text-muted">Día programado de la orden (1-28).</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="dekra_taller_name">Nombre del taller / etiqueta</label>
                                            <input type="text" class="form-control" name="dekra_taller_name" id="dekra_taller_name" maxlength="255" value="<?= Html::encode($dekraConfig['taller_name']) ?>">
                                            <small class="text-muted">Texto que se guarda en el campo "Taller" para detectar duplicados.</small>
                                        </div>
                                    </div>

                                    <h6 class="mt-4 mb-2"><i class="fas fa-map"></i> Mapeo: último dígito de la placa → mes</h6>
                                    <p class="text-muted small mb-3">
                                        Ejemplo: si una placa termina en <strong>8</strong> y el dígito 8 está asignado a <em>Agosto</em>, se generará una orden para el <strong>1 de agosto</strong> de cada año configurado.
                                    </p>

                                    <div class="row g-2">
                                        <?php for ($digit = 0; $digit <= 9; $digit++): ?>
                                            <?php $current = $dekraConfig['plate_month_map'][$digit] ?? $dekraDefaultMap[$digit]; ?>
                                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                                <label class="form-label mb-1">
                                                    Dígito <strong><?= $digit ?></strong>
                                                </label>
                                                <select name="dekra_map[<?= $digit ?>]" class="form-select form-select-sm">
                                                    <?php foreach ($monthNames as $monthNum => $monthLabel): ?>
                                                        <option value="<?= $monthNum ?>" <?= $current === $monthNum ? 'selected' : '' ?>>
                                                            <?= $monthNum ?> · <?= Html::encode($monthLabel) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted">Por defecto: <?= Html::encode($monthNames[$dekraDefaultMap[$digit]]) ?></small>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Al guardar, se aplicará el mapeo y se intentará generar de inmediato los recordatorios faltantes para el año actual y los años configurados. Los recordatorios ya creados no se borran ni modifican.
                                    </div>

                                    <div class="alert alert-secondary mt-3 mb-0">
                                        <h6 class="mb-2"><i class="fas fa-clock"></i> Ejecución automática sin abrir mantenimiento</h6>
                                        <p class="mb-2">
                                            Para programarlo en Windows Task Scheduler, usa el archivo:
                                        </p>
                                        <code>c:\Users\ronal\OneDrive\Escritorio\RAA\run-dekra-reminders.bat</code>
                                        <p class="mt-2 mb-2">
                                            También se puede ejecutar manualmente desde la consola:
                                        </p>
                                        <code>php yii dekra</code>
                                        <p class="mt-2 mb-0 small text-muted">
                                            El comando usa esta misma configuración, genera el año actual y los años futuros indicados, y no duplica órdenes ya existentes.
                                        </p>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar configuración Dekra</button>
                                        <?= Html::a(
                                            '<i class="fas fa-wrench"></i> Ir a Mantenimiento',
                                            ['/maintenance-order/index'],
                                            ['class' => 'btn btn-outline-secondary', 'encode' => false]
                                        ) ?>
                                    </div>

                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="whatsapp" role="tabpanel" aria-labelledby="whatsapp-tab">
                            <div class="row mt-4">
                                <div class="col-lg-7">
                                    <h5 class="mb-2"><i class="fab fa-whatsapp text-success"></i> Integración WhatsApp</h5>
                                    <p class="text-muted">
                                        Conecte su cuenta de WhatsApp escaneando el código QR. Al crear una nueva orden de
                                        alquiler se enviará un aviso con el PDF adjunto a los teléfonos administrativos.
                                    </p>
                                    <form action="<?= Url::to(['config/update-whatsapp']) ?>" method="post">
                                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="whatsapp_enabled" value="1" id="whatsapp_enabled"
                                                   <?= !empty($whatsappConfig['enabled']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="whatsapp_enabled">
                                                Activar integración con WhatsApp
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="whatsapp_notify_on_create" value="1" id="whatsapp_notify_on_create"
                                                   <?= !empty($whatsappConfig['notify_on_create']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="whatsapp_notify_on_create">
                                                Enviar aviso automático al crear/actualizar una orden de alquiler
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="whatsapp_notify_client" value="1" id="whatsapp_notify_client"
                                                   <?= !empty($whatsappConfig['notify_client']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="whatsapp_notify_client">
                                                <i class="fab fa-whatsapp text-success"></i>
                                                Enviar también la notificación al <strong>cliente</strong> (al WhatsApp del cliente registrado en la orden)
                                            </label>
                                            <div class="form-text text-muted">
                                                Si está activo, el cliente recibirá los mismos avisos que los administradores cuando se crea o actualiza una orden.
                                                Las cancelaciones se envían siempre a los administradores, independientemente de este ajuste.
                                            </div>
                                        </div>

                                        <div class="card border-success mb-3">
                                            <div class="card-header bg-success-subtle d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined" style="font-size: 20px; color: #198754;">schedule_send</span>
                                                <strong>Resumen diario programado</strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           name="whatsapp_daily_enabled" value="1" id="whatsapp_daily_enabled"
                                                           <?= !empty($whatsappConfig['daily_enabled']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="whatsapp_daily_enabled">
                                                        Enviar <strong>resumen diario</strong> por WhatsApp (entregas + devoluciones + disponibles)
                                                    </label>
                                                </div>

                                                <div class="row g-3 align-items-end">
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="whatsapp_daily_time">Hora de envío</label>
                                                        <input type="time" class="form-control" name="whatsapp_daily_time"
                                                               id="whatsapp_daily_time"
                                                               value="<?= Html::encode($whatsappConfig['daily_time'] ?? '08:00') ?>">
                                                        <div class="form-text">Formato 24h. Ej: 08:00</div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label d-block">Último envío exitoso</label>
                                                        <span class="badge bg-secondary">
                                                            <?= !empty($whatsappConfig['daily_last_sent'])
                                                                ? Html::encode($whatsappConfig['daily_last_sent'])
                                                                : 'nunca' ?>
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-outline-success ms-2" id="btn-whatsapp-daily-test">
                                                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">send</span>
                                                            Enviar resumen de prueba ahora
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info mt-3 mb-0 small">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <span class="material-symbols-outlined" style="font-size: 18px;">info</span>
                                                        <div>
                                                            <strong>Cómo activarlo en el servidor</strong> — para que el envío sea automático debes configurar un cron en el servidor:
                                                            <pre class="mb-1 mt-1" style="white-space: pre-wrap; font-size: 11px;">* * * * * cd /ruta/al/proyecto &amp;&amp; php yii whatsapp/daily-deliveries &gt;&gt; runtime/logs/whatsapp-daily.log 2&gt;&amp;1</pre>
                                                            El cron se ejecuta cada minuto, pero el sistema solo envía una vez al día a la hora configurada (control anti-duplicado).
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label" for="whatsapp_api_url">URL base de la API</label>
                                                <input type="url" class="form-control" name="whatsapp_api_url"
                                                       id="whatsapp_api_url"
                                                       value="<?= Html::encode($whatsappConfig['api_url']) ?>"
                                                       placeholder="https://descargapro.com" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label" for="whatsapp_session_id">Session ID</label>
                                                <input type="text" class="form-control" name="whatsapp_session_id"
                                                       id="whatsapp_session_id"
                                                       value="<?= Html::encode($whatsappConfig['session_id']) ?>"
                                                       placeholder="facto_rent" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="whatsapp_country_code">Código de país</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+</span>
                                                    <input type="text" class="form-control" name="whatsapp_country_code"
                                                           id="whatsapp_country_code"
                                                           value="<?= Html::encode($whatsappConfig['country_code']) ?>"
                                                           placeholder="506" required>
                                                </div>
                                                <div class="form-text">Se antepone si el número no lo incluye.</div>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label" for="whatsapp_public_base_url">
                                                    URL pública del sitio (para descargar el PDF) — opcional
                                                </label>
                                                <input type="url" class="form-control" name="whatsapp_public_base_url"
                                                       id="whatsapp_public_base_url"
                                                       value="<?= Html::encode($whatsappConfig['public_base_url']) ?>"
                                                       placeholder="https://misitio.com">
                                                <div class="form-text">
                                                    Debe ser accesible desde Internet (https). Si se deja vacío se intentará detectar automáticamente.
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <h6 class="mb-3"><i class="fas fa-user-shield"></i> Teléfonos administrativos</h6>
                                        <p class="text-muted small">
                                            Hasta 5 números de WhatsApp que recibirán el aviso y el PDF de la nueva orden.
                                            Si el número no incluye código de país, se usará el configurado arriba.
                                        </p>
                                        <div class="row g-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <div class="col-md-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i> <?= $i ?></span>
                                                        <input type="text" class="form-control"
                                                               name="whatsapp_admin_phone_<?= $i ?>"
                                                               value="<?= Html::encode($whatsappConfig['admin_phones'][$i] ?? '') ?>"
                                                               placeholder="Ej: 506 8888 8888">
                                                    </div>
                                                </div>
                                            <?php endfor; ?>
                                        </div>

                                        <div class="mt-4 d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Guardar configuración
                                            </button>
                                            <button type="button" class="btn btn-outline-success" id="btn-whatsapp-test">
                                                <i class="fas fa-paper-plane"></i> Enviar mensaje de prueba
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-lg-5">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-qrcode"></i> Conexión de WhatsApp</span>
                                            <span class="badge bg-light text-dark" id="whatsapp-status-badge">
                                                <i class="fas fa-circle-notch fa-spin"></i> Cargando…
                                            </span>
                                        </div>
                                        <div class="card-body text-center">
                                            <div id="whatsapp-qr-container" class="d-flex align-items-center justify-content-center"
                                                 style="min-height: 260px;">
                                                <div class="text-muted">
                                                    <i class="fas fa-mobile-alt fa-3x mb-2"></i>
                                                    <p class="mb-0">Inicie la sesión para generar el código QR.</p>
                                                </div>
                                            </div>
                                            <div id="whatsapp-info-msg" class="alert alert-info mt-3 mb-0 small d-none"></div>

                                            <div class="d-grid gap-2 mt-3">
                                                <button type="button" class="btn btn-primary" id="btn-whatsapp-create">
                                                    <i class="fas fa-plus-circle"></i> 1. Crear sesión
                                                </button>
                                                <button type="button" class="btn btn-success" id="btn-whatsapp-start" disabled>
                                                    <i class="fas fa-qrcode"></i> 2. Iniciar sesión / Generar QR
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" id="btn-whatsapp-refresh">
                                                    <i class="fas fa-sync"></i> Actualizar estado
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" id="btn-whatsapp-disconnect">
                                                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                                                </button>
                                            </div>

                                            <p class="text-muted small mt-3 mb-0">
                                                Abra WhatsApp en su teléfono → <strong>Ajustes</strong> →
                                                <strong>Dispositivos vinculados</strong> → <strong>Vincular un dispositivo</strong>
                                                y escanee el QR.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="notificaciones" role="tabpanel" aria-labelledby="notificaciones-tab">
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                    <h5 class="mb-3"><i class="fas fa-bell"></i> Notificaciones de cobro (insidentes)</h5>
                                    <p class="text-muted">Tras iniciar sesión, si hay insidentes abiertos con saldo pendiente, se muestra un aviso con el listado. El usuario debe cerrarlo hasta tres veces; después no volverá a mostrarse en esa sesión. Tras la tercera vez, el aviso permanece oculto durante la cantidad de días indicada en <strong>frecuencia</strong> (mediante una pausa en este navegador). Para desactivar por completo las notificaciones debe ingresar la contraseña de seguridad.</p>
                                    <?php $notifForm = ActiveForm::begin([
                                        'action' => ['config/update-incident-notifications'],
                                        'method' => 'post',
                                        'options' => ['class' => 'needs-validation', 'novalidate' => true, 'id' => 'incident-notifications-form'],
                                    ]); ?>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" name="incident_notifications_enabled" value="1" id="incident_notifications_enabled" <?= !empty($incidentNotifEnabled) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="incident_notifications_enabled">Activar notificaciones al iniciar sesión</label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="incident_notifications_frequency_days">Frecuencia (días de pausa tras cerrar 3 veces)</label>
                                        <input type="number" class="form-control" name="incident_notifications_frequency_days" id="incident_notifications_frequency_days" min="1" max="365" value="<?= (int) ($incidentNotifFrequencyDays ?? 3) ?>" style="max-width: 120px;">
                                        <small class="form-text text-muted">Ejemplo: 3 significa que, tras cerrar el aviso tres veces, no se volverá a mostrar hasta pasados 3 días (en este equipo). Use 1 para la pausa mínima.</small>
                                    </div>
                                    <div class="mb-3" id="incident-notif-disable-password-wrap" style="display: none;">
                                        <label class="form-label" for="incident_notif_disable_password">Contraseña para desactivar</label>
                                        <input type="password" class="form-control" name="disable_password" id="incident_notif_disable_password" autocomplete="off" style="max-width: 280px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>

                        <?php $mkConfig = \app\models\CompanyConfig::getMarketingConfig(); ?>
                        <div class="tab-pane fade" id="marketing" role="tabpanel" aria-labelledby="marketing-tab">
                            <div class="row mt-4">
                                <div class="col-lg-9">
                                    <h5 class="mb-3"><i class="fas fa-bullhorn text-primary"></i> Configuración de campañas de marketing</h5>
                                    <p class="text-muted">
                                        Aquí controla cómo se envían las campañas masivas de WhatsApp para evitar bloqueos o que la cuenta sea
                                        marcada como spam. Las campañas se gestionan desde
                                        <a href="<?= \yii\helpers\Url::to(['/marketing/index']) ?>">Menú &gt; Marketing</a>.
                                    </p>

                                    <?php $mkForm = ActiveForm::begin([
                                        'action' => ['config/update-marketing'],
                                        'method' => 'post',
                                        'options' => ['class' => 'needs-validation', 'novalidate' => true],
                                    ]); ?>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="marketing_interval_seconds">Intervalo por defecto (segundos)</label>
                                            <input type="number" class="form-control" id="marketing_interval_seconds" name="marketing_interval_seconds" min="1" max="120" value="<?= (int) $mkConfig['interval_seconds'] ?>">
                                            <small class="form-text text-muted">Tiempo entre cada mensaje individual. Recomendado: 5–10s.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="marketing_batch_size">Tamaño del lote</label>
                                            <input type="number" class="form-control" id="marketing_batch_size" name="marketing_batch_size" min="1" max="500" value="<?= (int) $mkConfig['batch_size'] ?>">
                                            <small class="form-text text-muted">Cuántos contactos se procesan por petición.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="marketing_batch_pause">Pausa entre lotes (segundos)</label>
                                            <input type="number" class="form-control" id="marketing_batch_pause" name="marketing_batch_pause" min="0" max="3600" value="<?= (int) $mkConfig['batch_pause'] ?>">
                                            <small class="form-text text-muted">Pausa adicional al completar cada lote.</small>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label" for="marketing_signature">Firma al pie del mensaje</label>
                                        <textarea class="form-control" name="marketing_signature" id="marketing_signature" rows="4" placeholder="Ej: — FACTO RENT A CAR · 8367-0937 · www.factorentacar.com"><?= \yii\helpers\Html::encode($mkConfig['signature']) ?></textarea>
                                        <small class="form-text text-muted">Se agrega automáticamente al final de cada mensaje enviado en una campaña.</small>
                                    </div>

                                    <?php if (!empty($mkConfig['last_campaign_at'])): ?>
                                        <p class="text-muted mt-3 mb-0">Última campaña enviada: <strong><?= \yii\helpers\Html::encode($mkConfig['last_campaign_at']) ?></strong></p>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar configuración de Marketing</button>
                                        <a href="<?= \yii\helpers\Url::to(['/marketing/index']) ?>" class="btn btn-outline-success ms-2"><i class="fas fa-bullhorn"></i> Ir a Marketing</a>
                                    </div>

                                    <?php ActiveForm::end(); ?>
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
document.addEventListener('DOMContentLoaded', function() {
    const notifEn = document.getElementById('incident_notifications_enabled');
    const notifWrap = document.getElementById('incident-notif-disable-password-wrap');
    const notifWasEnabled = <?= !empty($incidentNotifEnabled) ? 'true' : 'false' ?>;
    if (notifEn && notifWrap) {
        const syncNotifDisablePwd = function () {
            notifWrap.style.display = (notifWasEnabled && !notifEn.checked) ? 'block' : 'none';
        };
        notifEn.addEventListener('change', syncNotifDisablePwd);
        syncNotifDisablePwd();
    }

    if (window.location.hash === '#notificaciones' && window.bootstrap && window.bootstrap.Tab) {
        var notifTabBtn = document.getElementById('notificaciones-tab');
        if (notifTabBtn) {
            (new bootstrap.Tab(notifTabBtn)).show();
        }
    }
    if (window.location.hash === '#dekra' && window.bootstrap && window.bootstrap.Tab) {
        var dekraTabBtn = document.getElementById('dekra-tab');
        if (dekraTabBtn) {
            (new bootstrap.Tab(dekraTabBtn)).show();
        }
    }
    if (window.location.hash === '#whatsapp' && window.bootstrap && window.bootstrap.Tab) {
        var waTabBtn = document.getElementById('whatsapp-tab');
        if (waTabBtn) {
            (new bootstrap.Tab(waTabBtn)).show();
        }
    }
    if (window.location.hash === '#marketing' && window.bootstrap && window.bootstrap.Tab) {
        var mkTabBtn = document.getElementById('marketing-tab');
        if (mkTabBtn) {
            (new bootstrap.Tab(mkTabBtn)).show();
        }
    }
    if (window.location.hash === '#orden-renta-pdf' && window.bootstrap && window.bootstrap.Tab) {
        var infoTabBtn = document.getElementById('info-tab');
        if (infoTabBtn) {
            (new bootstrap.Tab(infoTabBtn)).show();
            var el = document.getElementById('orden-renta-pdf');
            if (el) {
                setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
            }
        }
    }

    var requirementsTextarea = document.getElementById('company_requirements');
    var requirementsEditorEl = document.getElementById('company_requirements_editor');
    var companyForm = document.querySelector('form[action*="update-company"]');
    if (requirementsTextarea && requirementsEditorEl && typeof Quill !== 'undefined') {
        var requirementsQuill = new Quill(requirementsEditorEl, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });
        requirementsQuill.root.innerHTML = requirementsTextarea.value || '';

        if (companyForm) {
            companyForm.addEventListener('submit', function () {
                requirementsTextarea.value = requirementsQuill.root.innerHTML;
            });
        }
    }

    var rentalPdfGeneral = document.getElementById('rental_pdf_general');
    var rentalPdfModerna = document.getElementById('rental_pdf_moderna');
    var rentalPdfModernaOpts = document.getElementById('rental-pdf-moderna-options');
    function syncRentalPdfModernaOptions() {
        if (!rentalPdfModernaOpts) return;
        rentalPdfModernaOpts.style.display = (rentalPdfModerna && rentalPdfModerna.checked) ? 'block' : 'none';
    }
    if (rentalPdfGeneral) rentalPdfGeneral.addEventListener('change', syncRentalPdfModernaOptions);
    if (rentalPdfModerna) rentalPdfModerna.addEventListener('change', syncRentalPdfModernaOptions);
    syncRentalPdfModernaOptions();

    var rentalPdfTextProporcional = document.getElementById('rental_pdf_text_proporcional');
    var rentalPdfTextNumeros = document.getElementById('rental_pdf_text_numeros');
    var rentalPdfTextProporcionalBlock = document.getElementById('rental-pdf-text-proporcional');
    var rentalPdfTextNumerosBlock = document.getElementById('rental-pdf-text-numeros');
    function syncRentalPdfTextMode() {
        var useNumeros = rentalPdfTextNumeros && rentalPdfTextNumeros.checked;
        if (rentalPdfTextProporcionalBlock) {
            rentalPdfTextProporcionalBlock.style.display = useNumeros ? 'none' : 'block';
        }
        if (rentalPdfTextNumerosBlock) {
            rentalPdfTextNumerosBlock.style.display = useNumeros ? 'block' : 'none';
        }
    }
    if (rentalPdfTextProporcional) rentalPdfTextProporcional.addEventListener('change', syncRentalPdfTextMode);
    if (rentalPdfTextNumeros) rentalPdfTextNumeros.addEventListener('change', syncRentalPdfTextMode);
    syncRentalPdfTextMode();

    // Agregar cuenta bancaria
    const addBankAccountBtn = document.getElementById('add-bank-account');
    if (addBankAccountBtn) {
        addBankAccountBtn.addEventListener('click', function() {
            const container = document.getElementById('bank-accounts-container');
            if (container) {
                const rows = container.querySelectorAll('.bank-account-row');
                const index = rows.length;
                
                const newRow = document.createElement('div');
                newRow.className = 'row bank-account-row mb-3';
                newRow.innerHTML = `
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
                `;
                container.appendChild(newRow);
            }
        });
    }
    
    // Eliminar cuenta bancaria
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('remove-bank-account') || e.target.closest('.remove-bank-account'))) {
            const btn = e.target.classList.contains('remove-bank-account') ? e.target : e.target.closest('.remove-bank-account');
            const row = btn.closest('.bank-account-row');
            if (row) {
                row.remove();
            }
        }
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

    // ==================== WhatsApp ====================
    (function () {
        const WA_URLS = {
            status: <?= json_encode(Url::to(['config/whatsapp-status'])) ?>,
            qr: <?= json_encode(Url::to(['config/whatsapp-qr'])) ?>,
            start: <?= json_encode(Url::to(['config/whatsapp-start'])) ?>,
            del: <?= json_encode(Url::to(['config/whatsapp-delete'])) ?>,
            test: <?= json_encode(Url::to(['config/whatsapp-test'])) ?>,
            dailyTest: <?= json_encode(Url::to(['config/whatsapp-daily-test'])) ?>,
        };
        // Valores GUARDADOS (no del formulario) para que coincidan con lo que usa el backend.
        const WA_API_URL = <?= json_encode($whatsappConfig['api_url']) ?>;
        const WA_SESSION_ID = <?= json_encode($whatsappConfig['session_id']) ?>;
        const CSRF = <?= json_encode(Yii::$app->request->csrfToken) ?>;

        const tabBtn = document.getElementById('whatsapp-tab');
        const badge = document.getElementById('whatsapp-status-badge');
        const qrBox = document.getElementById('whatsapp-qr-container');
        const infoMsg = document.getElementById('whatsapp-info-msg');
        const btnCreate = document.getElementById('btn-whatsapp-create');
        const btnStart = document.getElementById('btn-whatsapp-start');
        const btnRefresh = document.getElementById('btn-whatsapp-refresh');
        const btnDisconnect = document.getElementById('btn-whatsapp-disconnect');
        const btnTest = document.getElementById('btn-whatsapp-test');

        let sessionConfirmed = false;

        function setStartEnabled(enabled, labelOverride) {
            if (!btnStart) return;
            btnStart.disabled = !enabled;
            if (labelOverride) {
                btnStart.innerHTML = labelOverride;
            }
        }

        function setCreateEnabled(enabled, labelOverride) {
            if (!btnCreate) return;
            btnCreate.disabled = !enabled;
            if (labelOverride) {
                btnCreate.innerHTML = labelOverride;
            }
        }

        function markSessionConfirmed(confirmed) {
            sessionConfirmed = !!confirmed;
            setStartEnabled(sessionConfirmed,
                sessionConfirmed
                    ? '<i class="fas fa-qrcode"></i> 2. Iniciar sesión / Generar QR'
                    : '<i class="fas fa-qrcode"></i> 2. Iniciar sesión / Generar QR'
            );
            setCreateEnabled(true,
                sessionConfirmed
                    ? '<i class="fas fa-check-circle"></i> Sesión creada — recrear si es necesario'
                    : '<i class="fas fa-plus-circle"></i> 1. Crear sesión'
            );
        }

        if (!tabBtn) return;

        let pollHandle = null;
        let polling = false;
        let pollCount = 0;
        let pollStartedAt = 0;

        function setBadge(state) {
            if (!badge) return;
            const states = {
                connected: { cls: 'bg-success text-white', html: '<i class="fas fa-check-circle"></i> Conectado' },
                pending: { cls: 'bg-warning text-dark', html: '<i class="fas fa-qrcode"></i> Esperando QR' },
                disconnected: { cls: 'bg-secondary text-white', html: '<i class="fas fa-times-circle"></i> Desconectado' },
                error: { cls: 'bg-danger text-white', html: '<i class="fas fa-exclamation-triangle"></i> Error' },
                loading: { cls: 'bg-light text-dark', html: '<i class="fas fa-circle-notch fa-spin"></i> Cargando…' },
            };
            const s = states[state] || states.loading;
            badge.className = 'badge ' + s.cls;
            badge.innerHTML = s.html;
        }

        function showInfo(msg, type) {
            if (!infoMsg) return;
            if (!msg) {
                infoMsg.classList.add('d-none');
                infoMsg.textContent = '';
                return;
            }
            infoMsg.classList.remove('d-none', 'alert-info', 'alert-danger', 'alert-success', 'alert-warning');
            infoMsg.classList.add('alert-' + (type || 'info'));
            infoMsg.textContent = msg;
        }

        function renderQrPlaceholder(html) {
            if (qrBox) qrBox.innerHTML = html;
        }

        function renderQrImage(dataUrl) {
            if (!qrBox) return;
            const img = new Image();
            img.alt = 'QR WhatsApp';
            img.style.maxWidth = '260px';
            img.style.width = '100%';
            img.style.height = 'auto';
            img.onload = function () {
                qrBox.innerHTML = '';
                qrBox.appendChild(img);
            };
            img.onerror = function () {
                console.warn('[whatsapp] QR image failed to load:', dataUrl);
                renderQrPlaceholder(
                    '<div class="text-muted">' +
                    '<div class="spinner-border spinner-border-sm mb-2" role="status"></div>' +
                    '<p class="mb-0">Generando QR…</p></div>'
                );
            };
            img.src = dataUrl;
        }

        function buildQrImageUrl() {
            const eff = currentFormSession();
            if (!eff.api_url || !eff.session_id) return '';
            return eff.api_url.replace(/\/+$/, '') + '/session/' + encodeURIComponent(eff.session_id) + '/qr-image?t=' + Date.now();
        }

        function currentFormSession() {
            const url = ((document.getElementById('whatsapp_api_url') || {}).value || '').trim().replace(/\/+$/, '');
            const sid = ((document.getElementById('whatsapp_session_id') || {}).value || '').trim();
            return {
                api_url: url || WA_API_URL || '',
                session_id: sid || WA_SESSION_ID || '',
            };
        }

        function buildUrlWithSession(url) {
            const eff = currentFormSession();
            const params = new URLSearchParams();
            if (eff.session_id) params.set('session_id', eff.session_id);
            if (eff.api_url) params.set('api_url', eff.api_url);
            const qs = params.toString();
            if (!qs) return url;
            return url + (url.indexOf('?') === -1 ? '?' : '&') + qs;
        }

        async function apiGet(url) {
            const fullUrl = buildUrlWithSession(url);
            const res = await fetch(fullUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            return res.json();
        }

        async function apiPost(url, extraBody) {
            const body = Object.assign({}, currentFormSession(), extraBody || {});
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF,
                },
                body: JSON.stringify(body),
            });
            return res.json();
        }

        async function refreshStatus() {
            try {
                const r = await apiGet(WA_URLS.status);
                console.log('[whatsapp] status response:', r);
                const data = r && r.data ? r.data : {};
                const apiStatus = (data.status || '').toString();
                // Nuevo contrato: { status: "connected"|"connecting"|"not_found", qr: bool }
                const sessionExists = data.sessionExists === true || (apiStatus !== '' && apiStatus !== 'not_found');

                if (data.isConnected || apiStatus === 'connected') {
                    setBadge('connected');
                    showInfo('Sesión conectada. El sistema puede enviar mensajes.', 'success');
                    renderQrPlaceholder(
                        '<div class="text-success">' +
                        '<i class="fas fa-check-circle fa-3x mb-2"></i>' +
                        '<p class="mb-0">Conexión activa</p></div>'
                    );
                    stopPolling();
                    return { connected: true, sessionExists: true };
                }

                if (apiStatus === 'connecting') {
                    setBadge('pending');
                    return { connected: false, sessionExists: true };
                }

                if (apiStatus === 'not_found' || !sessionExists) {
                    setBadge('disconnected');
                    return { connected: false, sessionExists: false };
                }

                if (r && !r.success) {
                    const errMsg = (data && data.message) || r.error || 'desconocido';
                    setBadge('disconnected');
                    return { connected: false, sessionExists: false, error: errMsg };
                }

                setBadge('disconnected');
                return { connected: false, sessionExists: sessionExists };
            } catch (e) {
                setBadge('error');
                showInfo('No se pudo contactar la API: ' + e.message, 'danger');
                return { connected: false, sessionExists: false, error: e.message };
            }
        }

        function findQrInResponse(obj) {
            if (!obj || typeof obj !== 'object') return null;
            if (typeof obj.qr === 'string' && obj.qr.length > 30) return obj.qr;
            for (const k of Object.keys(obj)) {
                const v = obj[k];
                if (typeof v === 'string' && v.indexOf('data:image') === 0) return v;
                if (v && typeof v === 'object') {
                    const found = findQrInResponse(v);
                    if (found) return found;
                }
            }
            return null;
        }

        async function fetchQr() {
            try {
                const r = await apiGet(WA_URLS.qr);
                console.log('[whatsapp] qr response:', r);
                const data = r && r.data ? r.data : {};
                const apiStatus = (data.status || '').toString();

                if (apiStatus === 'connected' || data.isConnected) {
                    setBadge('connected');
                    showInfo('Sesión conectada.', 'success');
                    renderQrPlaceholder(
                        '<div class="text-success">' +
                        '<i class="fas fa-check-circle fa-3x mb-2"></i>' +
                        '<p class="mb-0">Conexión activa</p></div>'
                    );
                    stopPolling();
                    return { hadQr: false, connected: true, sessionExists: true };
                }

                const msg = (data && data.message) ? String(data.message) : '';
                const sessionMissing = apiStatus === 'not_found'
                    || data.sessionExists === false
                    || /no encontrada|not found|no existe/i.test(msg);

                // Estrategia 1: QR base64 desde el JSON.
                const qrSrc = findQrInResponse(data) || (r && findQrInResponse(r));
                if (qrSrc) {
                    const dataUrl = qrSrc.indexOf('data:image') === 0 ? qrSrc : ('data:image/png;base64,' + qrSrc.replace(/^data:[^,]*,/, ''));
                    renderQrImage(dataUrl);
                    setBadge('pending');
                    showInfo('Escanee el QR desde su WhatsApp (Dispositivos vinculados).', 'info');
                    return { hadQr: true, connected: false, sessionExists: true };
                }

                // Estrategia 2: usar el endpoint PNG directo /session/{id}/qr-image,
                // pero solo si la sesión existe.
                if (!sessionMissing) {
                    const imgUrl = buildQrImageUrl();
                    if (imgUrl) {
                        // Pre-fetch para verificar que devuelve una imagen valida
                        // (asi sabemos si el QR ya esta listo o no).
                        try {
                            const head = await fetch(imgUrl, { method: 'GET', mode: 'cors', cache: 'no-store' });
                            console.log('[whatsapp] qr-image HTTP', head.status, head.headers.get('content-type'));
                            const ct = (head.headers.get('content-type') || '').toLowerCase();
                            if (head.ok && ct.indexOf('image') !== -1) {
                                renderQrImage(imgUrl);
                                setBadge('pending');
                                showInfo('Escanee el QR desde su WhatsApp (Dispositivos vinculados).', 'info');
                                return { hadQr: true, connected: false, sessionExists: true };
                            }
                        } catch (fetchErr) {
                            // CORS o red: intentar igual con <img> directamente.
                            console.warn('[whatsapp] no se pudo pre-validar qr-image:', fetchErr.message);
                            renderQrImage(imgUrl);
                            setBadge('pending');
                            showInfo('Escanee el QR desde su WhatsApp (Dispositivos vinculados).', 'info');
                            return { hadQr: true, connected: false, sessionExists: true };
                        }
                    }
                }

                renderQrPlaceholder(
                    '<div class="text-muted">' +
                    '<div class="spinner-border spinner-border-sm mb-2" role="status"></div>' +
                    '<p class="mb-0">' + (sessionMissing ? 'Sesión no encontrada — reiniciando…' : 'Esperando código QR…') + '</p></div>'
                );
                if (msg) {
                    showInfo('API: ' + msg, sessionMissing ? 'warning' : 'info');
                }
                return { hadQr: false, connected: false, sessionExists: !sessionMissing };
            } catch (e) {
                showInfo('Error obteniendo QR: ' + e.message, 'danger');
                return { hadQr: false, connected: false, sessionExists: true, error: e.message };
            }
        }

        async function startSession() {
            setBadge('loading');
            showInfo('Iniciando sesión en el servidor…', 'info');
            const r = await apiPost(WA_URLS.start);
            console.log('[whatsapp] start response:', r);
            const data = r && r.data ? r.data : {};
            const ok = r.success || (data && (data.status === 'exists' || data.status === 'starting' || data.status === 'ok'));
            if (!ok) {
                const errMsg = (data && data.message) || r.error || 'desconocido';
                showInfo('No se pudo iniciar la sesión: ' + errMsg, 'danger');
                setBadge('error');
                return false;
            }
            return true;
        }

        function startPolling() {
            if (polling) return;
            polling = true;
            pollCount = 0;
            pollStartedAt = Date.now();

            const tick = async () => {
                if (!polling) return;
                pollCount++;

                const elapsed = (Date.now() - pollStartedAt) / 1000;
                // Polling agresivo (1.5s) los primeros 30s; luego cada 4s.
                const nextDelay = elapsed < 30 ? 1500 : 4000;

                const status = await refreshStatus();
                if (status.connected) return;

                if (!status.sessionExists) {
                    // La sesion fue eliminada o nunca se creo: reintenta start una vez.
                    console.warn('[whatsapp] sesión inexistente, reintentando start...');
                    await startSession();
                }

                const qr = await fetchQr();
                if (qr.connected) return;

                if (!qr.sessionExists && status.sessionExists) {
                    // QR endpoint reporta sesión no encontrada pero status decía que existía: reiniciar.
                    await startSession();
                }

                // Timeout duro a 3 minutos sin conexión.
                if (elapsed > 180 && !qr.hadQr) {
                    showInfo('No se pudo generar el QR en 3 minutos. Pulse "Cerrar sesión" y luego "Iniciar sesión / Generar QR" de nuevo.', 'warning');
                    stopPolling();
                    setBadge('error');
                    return;
                }

                if (polling) {
                    pollHandle = setTimeout(tick, nextDelay);
                }
            };

            // Primer tick inmediato.
            tick();
        }

        function stopPolling() {
            polling = false;
            if (pollHandle) {
                clearTimeout(pollHandle);
                clearInterval(pollHandle);
                pollHandle = null;
            }
        }

        function syncSavedFromResponse(r) {
            if (!r || typeof r !== 'object') return;
            if (typeof r.session_id === 'string' && r.session_id) {
                window.WA_SESSION_ID = r.session_id;
            }
        }

        async function confirmSessionExists(maxAttempts) {
            const attempts = maxAttempts || 10;
            for (let i = 1; i <= attempts; i++) {
                const r = await apiGet(WA_URLS.status);
                console.log('[whatsapp] confirm attempt ' + i + ':', r);
                const data = (r && r.data) ? r.data : {};
                const apiStatus = (data.status || '').toString().toLowerCase();

                if (data.isConnected || apiStatus === 'connected') {
                    return { exists: true, connected: true };
                }
                if (data.sessionExists === true
                    || apiStatus === 'connecting'
                    || apiStatus === 'starting'
                    || apiStatus === 'ok'
                    || data.qr) {
                    return { exists: true, connected: false };
                }
                await new Promise((resolve) => setTimeout(resolve, 800));
            }
            return { exists: false, connected: false };
        }

        async function createAndConfirmSession() {
            const eff = currentFormSession();
            if (!eff.api_url) {
                showInfo('Configura primero la URL base de la API.', 'warning');
                return false;
            }
            if (!eff.session_id) {
                showInfo('Configura primero un Session ID.', 'warning');
                return false;
            }

            markSessionConfirmed(false);
            setCreateEnabled(false, '<i class="fas fa-spinner fa-spin"></i> Creando sesión…');
            setStartEnabled(false);
            stopPolling();
            setBadge('loading');
            showInfo('Creando sesión "' + eff.session_id + '" en el servidor…', 'info');
            renderQrPlaceholder(
                '<div class="text-muted">' +
                '<div class="spinner-border text-primary mb-2" role="status"></div>' +
                '<p class="mb-0">Creando sesión "' + eff.session_id + '"…</p></div>'
            );

            try {
                const r = await apiPost(WA_URLS.start);
                console.log('[whatsapp] create response:', r);
                syncSavedFromResponse(r);

                const data = (r && r.data) ? r.data : {};
                const apiStatus = (data.status || '').toString().toLowerCase();
                const createOk = r.success
                    || ['ok', 'exists', 'starting', 'connecting', 'connected', 'created'].indexOf(apiStatus) !== -1;

                if (!createOk) {
                    const errMsg = (data && data.message) || r.error || 'desconocido';
                    showInfo('No se pudo crear la sesión: ' + errMsg, 'danger');
                    setBadge('error');
                    markSessionConfirmed(false);
                    setCreateEnabled(true);
                    return false;
                }

                showInfo('Sesión enviada al servidor. Confirmando creación…', 'info');
                renderQrPlaceholder(
                    '<div class="text-muted">' +
                    '<div class="spinner-border text-primary mb-2" role="status"></div>' +
                    '<p class="mb-0">Confirmando sesión con la API…</p></div>'
                );

                const confirm = await confirmSessionExists(12);
                window.WA_API_URL = eff.api_url;
                window.WA_SESSION_ID = eff.session_id;

                if (!confirm.exists) {
                    showInfo('La API no confirmó la creación de la sesión. Inténtelo de nuevo.', 'danger');
                    setBadge('error');
                    markSessionConfirmed(false);
                    setCreateEnabled(true);
                    return false;
                }

                if (confirm.connected) {
                    setBadge('connected');
                    showInfo('La sesión ya está conectada. No es necesario escanear el QR.', 'success');
                    renderQrPlaceholder(
                        '<div class="text-success">' +
                        '<i class="fas fa-check-circle fa-3x mb-2"></i>' +
                        '<p class="mb-0">Conexión activa</p></div>'
                    );
                    markSessionConfirmed(true);
                    setStartEnabled(false, '<i class="fas fa-check"></i> Ya conectado');
                    return true;
                }

                setBadge('pending');
                showInfo('Sesión creada y confirmada. Pulse "2. Iniciar sesión / Generar QR" para mostrar el código.', 'success');
                renderQrPlaceholder(
                    '<div class="text-muted">' +
                    '<i class="fas fa-mobile-alt fa-3x mb-2"></i>' +
                    '<p class="mb-0">Pulse "2. Iniciar sesión / Generar QR" para mostrar el código.</p></div>'
                );
                markSessionConfirmed(true);
                return true;
            } catch (e) {
                console.error('[whatsapp] create error', e);
                showInfo('Error de red al crear la sesión: ' + e.message, 'danger');
                setBadge('error');
                markSessionConfirmed(false);
                setCreateEnabled(true);
                return false;
            }
        }

        if (btnCreate) {
            btnCreate.addEventListener('click', async () => {
                await createAndConfirmSession();
            });
        }

        if (btnStart) {
            btnStart.addEventListener('click', async () => {
                if (!sessionConfirmed) {
                    showInfo('Primero pulse "1. Crear sesión" y espere la confirmación.', 'warning');
                    return;
                }
                const eff = currentFormSession();
                setStartEnabled(false, '<i class="fas fa-spinner fa-spin"></i> Solicitando QR…');
                renderQrPlaceholder(
                    '<div class="text-muted">' +
                    '<div class="spinner-border text-success mb-2" role="status"></div>' +
                    '<p class="mb-0">Solicitando QR de la sesión "' + (eff.session_id || '?') + '"…</p></div>'
                );
                try {
                    stopPolling();
                    const qr = await fetchQr();
                    startPolling();
                    if (qr.connected) {
                        setStartEnabled(false, '<i class="fas fa-check"></i> Ya conectado');
                    } else {
                        setStartEnabled(true, '<i class="fas fa-redo"></i> Refrescar QR');
                    }
                } catch (e) {
                    console.error('[whatsapp] start (QR) error', e);
                    showInfo('Error al solicitar el QR: ' + e.message, 'danger');
                    setStartEnabled(true);
                }
            });
        }

        if (btnRefresh) {
            btnRefresh.addEventListener('click', async () => {
                setBadge('loading');
                const status = await refreshStatus();
                if (status.connected) {
                    markSessionConfirmed(true);
                    setStartEnabled(false, '<i class="fas fa-check"></i> Ya conectado');
                } else if (status.sessionExists) {
                    markSessionConfirmed(true);
                    setStartEnabled(true);
                } else {
                    markSessionConfirmed(false);
                }
            });
        }

        if (btnDisconnect) {
            btnDisconnect.addEventListener('click', async () => {
                const eff = currentFormSession();
                if (!confirm('¿Cerrar la sesión "' + (eff.session_id || '?') + '" de WhatsApp? Esto también la elimina en la API y tendrás que volver a escanear el QR.')) return;
                btnDisconnect.disabled = true;
                try {
                    const r = await apiPost(WA_URLS.del);
                    console.log('[whatsapp] delete response', r);
                    syncSavedFromResponse(r);
                    if (r && r.success) {
                        showInfo('Sesión "' + (r.session_id || eff.session_id) + '" cerrada en la API.', 'warning');
                        setBadge('disconnected');
                        renderQrPlaceholder(
                            '<div class="text-muted">' +
                            '<i class="fas fa-mobile-alt fa-3x mb-2"></i>' +
                            '<p class="mb-0">Pulse "1. Crear sesión" para volver a iniciar.</p></div>'
                        );
                        stopPolling();
                        markSessionConfirmed(false);
                    } else {
                        showInfo('No se pudo confirmar el cierre en la API: ' + ((r && r.error) || 'sin respuesta'), 'danger');
                        await refreshStatus();
                    }
                } catch (err) {
                    console.error('[whatsapp] delete error', err);
                    showInfo('Error de red al cerrar sesión.', 'danger');
                } finally {
                    btnDisconnect.disabled = false;
                }
            });
        }

        if (btnTest) {
            btnTest.addEventListener('click', async () => {
                const orig = btnTest.innerHTML;
                btnTest.disabled = true;
                btnTest.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando…';
                try {
                    const r = await apiPost(WA_URLS.test);
                    if (r.success) {
                        showInfo('✅ ' + (r.message || 'Mensaje enviado.'), 'success');
                    } else {
                        const detail = (r.errors && r.errors.length) ? '\n' + r.errors.join('\n') : '';
                        showInfo('❌ ' + (r.message || 'No se pudo enviar.') + detail, 'danger');
                    }
                } catch (e) {
                    showInfo('Error: ' + e.message, 'danger');
                } finally {
                    btnTest.disabled = false;
                    btnTest.innerHTML = orig;
                }
            });
        }

        const btnDailyTest = document.getElementById('btn-whatsapp-daily-test');
        if (btnDailyTest) {
            btnDailyTest.addEventListener('click', async () => {
                const orig = btnDailyTest.innerHTML;
                btnDailyTest.disabled = true;
                btnDailyTest.innerHTML = '<span class="material-symbols-outlined align-middle" style="font-size:16px;">progress_activity</span> Enviando resumen…';
                try {
                    const r = await apiPost(WA_URLS.dailyTest);
                    if (r.success) {
                        showInfo('✅ ' + (r.message || 'Resumen diario enviado.'), 'success');
                    } else {
                        const errs = (r.report && r.report.errors && r.report.errors.length)
                            ? '\n' + r.report.errors.join('\n') : '';
                        showInfo('❌ ' + (r.message || 'No se pudo enviar el resumen.') + errs, 'danger');
                    }
                } catch (e) {
                    showInfo('Error: ' + e.message, 'danger');
                } finally {
                    btnDailyTest.disabled = false;
                    btnDailyTest.innerHTML = orig;
                }
            });
        }

        async function syncButtonsFromServer() {
            const status = await refreshStatus();
            if (status.connected) {
                markSessionConfirmed(true);
                setStartEnabled(false, '<i class="fas fa-check"></i> Ya conectado');
            } else if (status.sessionExists) {
                markSessionConfirmed(true);
                setBadge('pending');
                showInfo('Sesión ya existente en la API. Pulse "2. Iniciar sesión / Generar QR" para mostrar el código.', 'info');
            } else {
                markSessionConfirmed(false);
            }
            return status;
        }

        tabBtn.addEventListener('shown.bs.tab', async () => {
            await syncButtonsFromServer();
        });

        if (tabBtn.classList.contains('active') || window.location.hash === '#whatsapp') {
            syncButtonsFromServer();
        }

        window.addEventListener('beforeunload', stopPolling);
    })();
    
    // Función para copiar API Key
    window.copyApiKey = function(e) {
        const input = document.getElementById('new-api-key');
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999); // Para dispositivos móviles
            document.execCommand('copy');
            
            // Mostrar mensaje de confirmación
            const btn = e ? e.target.closest('button') : document.querySelector('#new-api-key').nextElementSibling;
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }
        }
    };
});
</script>