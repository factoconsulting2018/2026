<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\CompanyConfig;

/** @var yii\web\View $this */
/** @var app\models\Client $model */

$companyInfo = CompanyConfig::getCompanyInfo();
$logoPath = $companyInfo['logo'] ?? null;
$requirements = (string) ($companyInfo['requirements'] ?? '');
$showRequirementsFirst = trim(strip_tags($requirements)) !== '';

$defaultRentalStartDate = date('Y-m-d');
$defaultRentalEndDate = date('Y-m-d', strtotime('+3 days'));
$defaultRentalTime = '08:00';

$hasFormErrors = $model->hasErrors();
$formErrors = [];
if ($hasFormErrors) {
    foreach ($model->getErrors() as $attr => $errs) {
        foreach ((array) $errs as $err) {
            $formErrors[] = $err;
        }
    }
}

$postedRentalFechaInicio  = (string) Yii::$app->request->post('rental_fecha_inicio', $defaultRentalStartDate);
$postedRentalHoraInicio   = (string) Yii::$app->request->post('rental_hora_inicio', $defaultRentalTime);
$postedRentalFechaFinal   = (string) Yii::$app->request->post('rental_fecha_final', $defaultRentalEndDate);
$postedRentalHoraFinal    = (string) Yii::$app->request->post('rental_hora_final', $defaultRentalTime);
$postedRentalTipoAuto     = (string) Yii::$app->request->post('rental_tipo_auto', '');
$postedRentalTipoAutoOtro = (string) Yii::$app->request->post('rental_tipo_auto_otro', '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - Facto Rent a Car</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= Html::encode(Url::to('@web/css/material-symbols.css')) ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
    <style>
        body {
            background: #2e6faa;
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .registration-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
            color: white;
            padding: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-primary {
            background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
            border: none;
            padding: 12px 40px;
            font-size: 16px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .alert {
            border-radius: 10px;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
        .inline-flex {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        /* Stepper / migajas */
        .reg-stepper {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .reg-stepper .step {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f4f9;
            color: #6c757d;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: background .15s ease, color .15s ease, border-color .15s ease, transform .15s ease;
            user-select: none;
        }
        .reg-stepper .step:hover {
            background: #e2e8f3;
            color: #22487a;
        }
        .reg-stepper .step.active {
            background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
            color: #fff;
            border-color: #22487a;
        }
        .reg-stepper .step.completed {
            background: #e8f5e8;
            color: #1e7e34;
            border-color: #c3e6cb;
        }
        .reg-stepper .step.completed .step-num::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome;
            font-weight: 900;
        }
        .reg-stepper .step.completed .step-num span { display: none; }
        .reg-stepper .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            color: inherit;
            font-size: 13px;
        }
        .reg-stepper .step.active .step-num {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }
        .reg-stepper .step-divider {
            flex: 0 0 30px;
            height: 2px;
            background: #d6dce6;
            border-radius: 1px;
        }
        .reg-stepper .step-divider.done { background: #1e7e34; }
        @media (max-width: 480px) {
            .reg-stepper .step { font-size: 12px; padding: 5px 10px; }
            .reg-stepper .step-num { width: 22px; height: 22px; font-size: 12px; }
            .reg-stepper .step-divider { flex-basis: 18px; }
        }
        /* Indicadores visuales por campo (check / precaución / x) */
        .field-status-wrap {
            position: relative;
            display: block;
        }
        .field-status-wrap > .form-control,
        .field-status-wrap > .form-select {
            padding-right: 38px;
        }
        .field-status {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            line-height: 1;
            pointer-events: none;
            display: none;
        }
        .field-status.is-ok    { color: #198754; display: inline-flex; }
        .field-status.is-warn  { color: #d39e00; display: inline-flex; }
        .field-status.is-error { color: #dc3545; display: inline-flex; }
        .field-status-wrap > .form-control.is-status-error,
        .field-status-wrap > .form-select.is-status-error {
            border-color: #f1aeb5;
        }
        .field-status-wrap > .form-control.is-status-ok,
        .field-status-wrap > .form-select.is-status-ok {
            border-color: #a3cfbb;
        }
        .field-status-wrap > .form-control.is-status-warn,
        .field-status-wrap > .form-select.is-status-warn {
            border-color: #ffe69c;
        }
    </style>
</head>
<body>
<div class="registration-container">
    <div class="card">
        <div class="card-header text-center">
            <?php if ($logoPath): ?>
                <div class="mb-3">
                    <img src="<?= Html::encode($logoPath) ?>" alt="Logo" style="max-height: 100px;">
                </div>
            <?php endif; ?>
            <h2 class="mb-0">
                Registro de Nuevo Cliente
            </h2>
            <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.9;">
                Completa el formulario y tu solicitud será revisada
            </p>
            <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.95;">
                <span class="material-symbols-outlined align-middle" style="font-size: 16px;">call</span>
                <strong>Teléfono:</strong>
                <a href="tel:+50640700485" style="color: #fff; text-decoration: none;">4070-0485</a>
                <span class="mx-2" aria-hidden="true">|</span>
                <span class="material-symbols-outlined align-middle" style="font-size: 16px;">chat</span>
                <strong>WhatsApp:</strong>
                <a href="https://wa.me/50683670937" target="_blank" rel="noopener" style="color: #fff; text-decoration: none;">8367-0937</a>
            </p>
        </div>
        <div class="card-body p-4">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <strong><i class="fas fa-exclamation-triangle"></i></strong>
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($formErrors)): ?>
                <div class="alert alert-warning">
                    <strong>Revisa los siguientes datos:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($formErrors as $err): ?>
                            <li><?= Html::encode($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <ul class="reg-stepper" id="reg-stepper" aria-label="Pasos para completar la solicitud">
                <?php if ($showRequirementsFirst): ?>
                    <li class="step active" data-step="1" role="button" tabindex="0" aria-current="step">
                        <span class="step-num"><span>1</span></span>
                        <span class="step-label">Requisitos</span>
                    </li>
                    <li class="step-divider" aria-hidden="true"></li>
                <?php endif; ?>
                <li class="step <?= $showRequirementsFirst ? '' : 'active' ?>"
                    data-step="2"
                    role="button"
                    tabindex="0"
                    <?= $showRequirementsFirst ? '' : 'aria-current="step"' ?>>
                    <span class="step-num"><span>2</span></span>
                    <span class="step-label">Detalles del alquiler</span>
                </li>
                <li class="step-divider" aria-hidden="true"></li>
                <li class="step" data-step="3" role="button" tabindex="0">
                    <span class="step-num"><span>3</span></span>
                    <span class="step-label">Solicitud</span>
                </li>
            </ul>

            <?php if ($showRequirementsFirst): ?>
                <div id="requisitos-section" class="mb-4">
                    <h4 class="mb-3"><i class="fas fa-list-check"></i> Requisitos</h4>
                    <div class="border rounded p-3 bg-light">
                        <?= $requirements ?>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary btn-lg" id="btn-completar-solicitud">
                            Continuar
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div id="rental-details-section" class="mb-4"<?= $showRequirementsFirst ? ' style="display:none;"' : '' ?>>
                <h4 class="mb-3"><i class="fas fa-calendar-check"></i> Detalles del alquiler</h4>
                <p class="text-muted small mb-3">Indique las fechas que necesita alquilar y el tipo de vehículo que busca.</p>

                <div class="row g-3">
                    <div class="col-12"><h6 class="mb-1 text-uppercase text-muted small">Inicio del alquiler</h6></div>
                    <div class="col-sm-7">
                        <label class="form-label" for="rental_fecha_inicio">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">event</span>
                            Fecha de inicio *
                        </label>
                        <input type="date" class="form-control" id="rental_fecha_inicio" name="rental_fecha_inicio"
                               value="<?= Html::encode($postedRentalFechaInicio) ?>" min="<?= Html::encode($defaultRentalStartDate) ?>" required>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label" for="rental_hora_inicio">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">schedule</span>
                            Hora de inicio *
                        </label>
                        <input type="time" class="form-control" id="rental_hora_inicio" name="rental_hora_inicio"
                               value="<?= Html::encode($postedRentalHoraInicio) ?>" required>
                    </div>

                    <div class="col-12 mt-4"><h6 class="mb-1 text-uppercase text-muted small">Fin del alquiler</h6></div>
                    <div class="col-sm-7">
                        <label class="form-label" for="rental_fecha_final">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">event_available</span>
                            Fecha final *
                        </label>
                        <input type="date" class="form-control" id="rental_fecha_final" name="rental_fecha_final"
                               value="<?= Html::encode($postedRentalFechaFinal) ?>" min="<?= Html::encode($defaultRentalStartDate) ?>" required>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label" for="rental_hora_final">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">schedule</span>
                            Hora final *
                        </label>
                        <input type="time" class="form-control" id="rental_hora_final" name="rental_hora_final"
                               value="<?= Html::encode($postedRentalHoraFinal) ?>" required>
                    </div>

                    <div class="col-12 mt-4"><h6 class="mb-1 text-uppercase text-muted small">Tipo de vehículo</h6></div>
                    <div class="col-md-6">
                        <label class="form-label" for="rental_tipo_auto">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">directions_car</span>
                            Tipo de auto *
                        </label>
                        <?php $tipoOpts = ['Sedán' => '🚗 Sedán', 'SUV' => '🚙 SUV', 'Pickup 4x4' => '🛻 Pickup 4x4', 'Camión' => '🚚 Camión', 'Buseta' => '🚐 Buseta', 'otro' => 'Otro…']; ?>
                        <select class="form-select" id="rental_tipo_auto" name="rental_tipo_auto" required>
                            <option value="">Seleccione una opción</option>
                            <?php foreach ($tipoOpts as $value => $label): ?>
                                <option value="<?= Html::encode($value) ?>"<?= $postedRentalTipoAuto === $value ? ' selected' : '' ?>><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6" id="rental-tipo-otro-wrap" style="display:none;">
                        <label class="form-label" for="rental_tipo_auto_otro">
                            <span class="material-symbols-outlined align-middle" style="font-size:18px;">edit</span>
                            Especifique
                        </label>
                        <input type="text" class="form-control" id="rental_tipo_auto_otro" name="rental_tipo_auto_otro"
                               value="<?= Html::encode($postedRentalTipoAutoOtro) ?>"
                               placeholder="Ej: Furgón, motocicleta, etc.">
                    </div>
                </div>

                <div id="rental-details-error" class="alert alert-warning mt-3" style="display:none;"></div>

                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="btn-rental-back"<?= $showRequirementsFirst ? '' : ' style="visibility:hidden;"' ?>>
                        <span class="material-symbols-outlined align-middle" style="font-size:18px;">arrow_back</span>
                        Atrás
                    </button>
                    <button type="button" class="btn btn-primary btn-lg" id="btn-rental-next">
                        Siguiente
                        <span class="material-symbols-outlined align-middle" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            <div id="registration-form-wrapper" style="display:none;">
            <?php $form = ActiveForm::begin([
                'id' => 'registration-form',
                'action' => Url::current(),
                'options' => ['class' => 'form-horizontal'],
                'fieldConfig' => [
                    'template' => "<div class='row mb-3'><div class='col-sm-4'>{label}</div><div class='col-sm-8'>{input}{error}</div></div>",
                    'labelOptions' => ['class' => 'form-label'],
                ],
            ]); ?>
            
            <?= Html::hiddenInput('Client[approval_status]', 'pending') ?>
            <?= Html::hiddenInput('rental_fecha_inicio', '', ['id' => 'h_rental_fecha_inicio']) ?>
            <?= Html::hiddenInput('rental_hora_inicio', '', ['id' => 'h_rental_hora_inicio']) ?>
            <?= Html::hiddenInput('rental_fecha_final', '', ['id' => 'h_rental_fecha_final']) ?>
            <?= Html::hiddenInput('rental_hora_final', '', ['id' => 'h_rental_hora_final']) ?>
            <?= Html::hiddenInput('rental_tipo_auto', '', ['id' => 'h_rental_tipo_auto']) ?>
            <?= Html::hiddenInput('rental_tipo_auto_otro', '', ['id' => 'h_rental_tipo_auto_otro']) ?>
            
            <!-- Cédula Física -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">badge</span>
                        Cédula Física *
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'cedula_fisica', [
                        'template' => '{input}<span id="hacienda-loading-public" style="display:none; color: #0066CC; margin-top: 5px;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Consultando Hacienda...</span><span id="hacienda-error-public" style="display:none; color: #dc3545; margin-top: 5px;">⚠️ No se encontró información</span>{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'id' => 'public-cedula-input',
                            'placeholder' => 'Ej: 112610049',
                            'required' => true
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Nombre Completo -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">person</span>
                        Nombre Completo *
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'full_name', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'id' => 'public-nombre-input',
                            'placeholder' => 'Ej: RICARDO RODRIGUEZ CASTRO',
                            'required' => true,
                            'style' => 'text-transform: uppercase;'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Email -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">email</span>
                        Email
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'email', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'ejemplo@correo.com',
                            'type' => 'email'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- WhatsApp -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">phone</span>
                        WhatsApp
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'whatsapp', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Ej: 88888888'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Dirección -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">location_on</span>
                        Dirección
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'address', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Dirección completa',
                            'rows' => 3
                        ]
                    ])->textarea() ?>
                </div>
            </div>

            <!-- Licencias de Choferes -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">drive_eta</span>
                        Licencias de Choferes
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'licencias_choferes', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Información de licencias de choferes autorizados',
                            'rows' => 3
                        ]
                    ])->textarea() ?>
                </div>
            </div>

            <!-- Vencimiento Licencia -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">calendar_today</span>
                        Vencimiento Licencia
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'fecha_vencimiento_licencia', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'type' => 'date'
                        ]
                    ])->input('date') ?>
                </div>
            </div>

            <!-- Vencimiento Cédula -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">event_busy</span>
                        Vencimiento Cédula
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'fecha_vencimiento_cedula', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'type' => 'date'
                        ]
                    ])->input('date') ?>
                </div>
            </div>

            <!-- Situación Financiera Actual -->
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">account_balance</span>
                        Situación Financiera Actual
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'situacion_financiera', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'id' => 'situacion-financiera'
                        ]
                    ])->dropDownList([
                        '' => 'Seleccione una opción',
                        'independiente' => 'Independiente',
                        'asalariado' => 'Asalariado',
                        'tiene_empresa' => 'Tiene empresa'
                    ], ['class' => 'form-select']) ?>
                </div>
            </div>

            <!-- Detalle Situación Financiera (aparece dinámicamente) -->
            <div class="row mb-3" id="detalle-situacion-container" style="display: none;">
                <div class="col-sm-4">
                    <label class="form-label" id="detalle-situacion-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">description</span>
                    </label>
                </div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'situacion_financiera_detalle', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'rows' => 3
                        ]
                    ])->textarea() ?>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" id="btn-form-back">
                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">arrow_back</span>
                    Atrás
                </button>
                <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">send</span>Enviar Solicitud', [
                    'class' => 'btn btn-primary btn-lg'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <p style="color: white; font-size: 14px;">
            © <?= date('Y') ?> Facto Rent a Car. Todos los derechos reservados.
        </p>
        <p style="color: white; font-size: 12px; opacity: 0.8;">
            Desarrollado por Ing. Ronald Rojas Castro
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const requisitosSection = document.getElementById('requisitos-section');
        const rentalSection = document.getElementById('rental-details-section');
        const formWrapper = document.getElementById('registration-form-wrapper');
        const btnCompletarSolicitud = document.getElementById('btn-completar-solicitud');
        const stepper = document.getElementById('reg-stepper');
        const hasRequirements = !!requisitosSection;

        const rentalInputs = {
            fechaInicio: document.getElementById('rental_fecha_inicio'),
            horaInicio: document.getElementById('rental_hora_inicio'),
            fechaFinal: document.getElementById('rental_fecha_final'),
            horaFinal: document.getElementById('rental_hora_final'),
            tipoAuto: document.getElementById('rental_tipo_auto'),
            tipoAutoOtro: document.getElementById('rental_tipo_auto_otro'),
        };
        const rentalHidden = {
            fechaInicio: document.getElementById('h_rental_fecha_inicio'),
            horaInicio: document.getElementById('h_rental_hora_inicio'),
            fechaFinal: document.getElementById('h_rental_fecha_final'),
            horaFinal: document.getElementById('h_rental_hora_final'),
            tipoAuto: document.getElementById('h_rental_tipo_auto'),
            tipoAutoOtro: document.getElementById('h_rental_tipo_auto_otro'),
        };
        const rentalError = document.getElementById('rental-details-error');
        const tipoOtroWrap = document.getElementById('rental-tipo-otro-wrap');
        const btnRentalNext = document.getElementById('btn-rental-next');
        const btnRentalBack = document.getElementById('btn-rental-back');
        const btnFormBack = document.getElementById('btn-form-back');

        function setStep(step, opts) {
            opts = opts || {};
            if (hasRequirements) {
                requisitosSection.style.display = (step === 1) ? '' : 'none';
            } else if (step === 1) {
                step = 2;
            }
            if (rentalSection) rentalSection.style.display = (step === 2) ? '' : 'none';
            if (formWrapper) formWrapper.style.display = (step === 3) ? '' : 'none';

            if (stepper) {
                stepper.querySelectorAll('.step').forEach(function (el) {
                    const n = parseInt(el.getAttribute('data-step'), 10);
                    el.classList.remove('active', 'completed');
                    el.removeAttribute('aria-current');
                    if (n === step) {
                        el.classList.add('active');
                        el.setAttribute('aria-current', 'step');
                    } else if (n < step) {
                        el.classList.add('completed');
                    }
                });
                stepper.querySelectorAll('.step-divider').forEach(function (div, idx) {
                    div.classList.toggle('done', step >= (idx + 2));
                });
            }
            if (opts.scroll !== false) {
                const target = step === 1 ? requisitosSection : (step === 2 ? rentalSection : formWrapper);
                if (target && target.scrollIntoView) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            if (typeof window.__publicRegRefreshFieldStatuses === 'function') {
                setTimeout(window.__publicRegRefreshFieldStatuses, 0);
            }
        }

        if (rentalInputs.fechaInicio && rentalInputs.fechaFinal) {
            rentalInputs.fechaInicio.addEventListener('change', function () {
                const v = rentalInputs.fechaInicio.value;
                if (v) {
                    rentalInputs.fechaFinal.min = v;
                    if (rentalInputs.fechaFinal.value && rentalInputs.fechaFinal.value < v) {
                        rentalInputs.fechaFinal.value = v;
                    }
                }
            });
        }

        if (rentalInputs.tipoAuto && tipoOtroWrap) {
            const syncOtro = function () {
                const isOtro = rentalInputs.tipoAuto.value === 'otro';
                tipoOtroWrap.style.display = isOtro ? '' : 'none';
                if (rentalInputs.tipoAutoOtro) {
                    rentalInputs.tipoAutoOtro.required = isOtro;
                    if (!isOtro) rentalInputs.tipoAutoOtro.value = '';
                }
            };
            rentalInputs.tipoAuto.addEventListener('change', syncOtro);
            syncOtro();
        }

        function showRentalError(msg) {
            if (!rentalError) return;
            rentalError.textContent = msg;
            rentalError.style.display = msg ? '' : 'none';
        }

        function validateRentalStep() {
            showRentalError('');
            const fIni = rentalInputs.fechaInicio && rentalInputs.fechaInicio.value;
            const hIni = rentalInputs.horaInicio && rentalInputs.horaInicio.value;
            const fFin = rentalInputs.fechaFinal && rentalInputs.fechaFinal.value;
            const hFin = rentalInputs.horaFinal && rentalInputs.horaFinal.value;
            const tipo = rentalInputs.tipoAuto && rentalInputs.tipoAuto.value;
            const otro = rentalInputs.tipoAutoOtro && rentalInputs.tipoAutoOtro.value.trim();

            if (!fIni || !hIni || !fFin || !hFin) {
                showRentalError('Complete las fechas y horas de inicio y fin.');
                return false;
            }
            const dIni = new Date(fIni + 'T' + hIni);
            const dFin = new Date(fFin + 'T' + hFin);
            if (isNaN(dIni.getTime()) || isNaN(dFin.getTime())) {
                showRentalError('Las fechas/horas ingresadas no son válidas.');
                return false;
            }
            if (dFin <= dIni) {
                showRentalError('La fecha/hora final debe ser posterior a la de inicio.');
                return false;
            }
            if (!tipo) {
                showRentalError('Seleccione el tipo de vehículo.');
                return false;
            }
            if (tipo === 'otro' && !otro) {
                showRentalError('Indique en el campo "Especifique" qué tipo de vehículo busca.');
                return false;
            }
            return true;
        }

        function copyRentalToHidden() {
            if (rentalHidden.fechaInicio) rentalHidden.fechaInicio.value = rentalInputs.fechaInicio.value;
            if (rentalHidden.horaInicio) rentalHidden.horaInicio.value = rentalInputs.horaInicio.value;
            if (rentalHidden.fechaFinal) rentalHidden.fechaFinal.value = rentalInputs.fechaFinal.value;
            if (rentalHidden.horaFinal) rentalHidden.horaFinal.value = rentalInputs.horaFinal.value;
            if (rentalHidden.tipoAuto) rentalHidden.tipoAuto.value = rentalInputs.tipoAuto.value;
            if (rentalHidden.tipoAutoOtro) rentalHidden.tipoAutoOtro.value = (rentalInputs.tipoAutoOtro ? rentalInputs.tipoAutoOtro.value : '');
        }

        if (btnCompletarSolicitud) {
            btnCompletarSolicitud.addEventListener('click', function () { setStep(2); });
        }
        if (btnRentalBack) {
            btnRentalBack.addEventListener('click', function () { setStep(1); });
        }
        if (btnRentalNext) {
            btnRentalNext.addEventListener('click', function () {
                if (!validateRentalStep()) return;
                copyRentalToHidden();
                setStep(3);
            });
        }
        if (btnFormBack) {
            btnFormBack.addEventListener('click', function () { setStep(2); });
        }

        if (stepper) {
            stepper.querySelectorAll('.step').forEach(function (el) {
                const goto = function () {
                    const n = parseInt(el.getAttribute('data-step'), 10);
                    if (isNaN(n)) return;
                    if (n === 3) {
                        if (!validateRentalStep()) {
                            setStep(2);
                            return;
                        }
                        copyRentalToHidden();
                    }
                    setStep(n);
                };
                el.addEventListener('click', goto);
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        goto();
                    }
                });
            });
        }

        // === Indicadores visuales por campo (check verde / precaución / x roja) ===
        const STATUS_ICONS = {
            ok: 'fa-circle-check',
            warn: 'fa-triangle-exclamation',
            error: 'fa-circle-xmark'
        };

        function decorateField(field) {
            if (!field || field.dataset.statusDecorated === '1') return;
            const t = (field.type || '').toLowerCase();
            if (t === 'hidden' || t === 'submit' || t === 'button' || t === 'reset') return;
            const wrap = document.createElement('div');
            wrap.className = 'field-status-wrap';
            field.parentNode.insertBefore(wrap, field);
            wrap.appendChild(field);
            const status = document.createElement('span');
            status.className = 'field-status';
            status.setAttribute('aria-hidden', 'true');
            wrap.appendChild(status);
            field.dataset.statusDecorated = '1';
        }

        function evaluateField(field) {
            const t = (field.type || '').toLowerCase();
            const tag = (field.tagName || '').toLowerCase();
            if (t === 'hidden' || t === 'submit' || t === 'button' || t === 'reset') return 'skip';
            if (field.id === 'rental_tipo_auto_otro') {
                const tipo = document.getElementById('rental_tipo_auto');
                if (!tipo || tipo.value !== 'otro') return 'skip';
            }
            const value = (field.value || '').trim();
            const isReq = field.required || field.hasAttribute('required');
            if (!value) return isReq ? 'warn' : 'neutral';

            if (t === 'email') {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? 'ok' : 'error';
            }
            if (t === 'tel' || /(whatsapp|celular|telefono|tel)/i.test(field.name || field.id || '')) {
                return value.replace(/\D+/g, '').length >= 8 ? 'ok' : 'error';
            }
            if (field.id === 'public-cedula-input') {
                return /^\d{9,10}$/.test(value) ? 'ok' : 'error';
            }
            if (t === 'date') {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return 'error';
                if (field.id === 'rental_fecha_final') {
                    const ini = document.getElementById('rental_fecha_inicio');
                    if (ini && ini.value && value < ini.value) return 'error';
                }
                return 'ok';
            }
            if (t === 'time') {
                return /^\d{1,2}:\d{2}/.test(value) ? 'ok' : 'error';
            }
            if (tag === 'select') {
                return value === '' ? 'warn' : 'ok';
            }
            return 'ok';
        }

        function setFieldStatus(field, state) {
            if (state === 'skip' || !field || field.dataset.statusDecorated !== '1') return;
            const wrap = field.closest('.field-status-wrap');
            if (!wrap) return;
            const status = wrap.querySelector('.field-status');
            if (!status) return;
            status.classList.remove('is-ok', 'is-warn', 'is-error');
            field.classList.remove('is-status-ok', 'is-status-warn', 'is-status-error');
            if (state === 'neutral') {
                status.innerHTML = '';
                return;
            }
            status.classList.add('is-' + state);
            field.classList.add('is-status-' + state);
            status.innerHTML = '<i class="fas ' + STATUS_ICONS[state] + '"></i>';
        }

        function refreshFieldStatus(field) {
            setFieldStatus(field, evaluateField(field));
        }

        function getAllValidatedFields() {
            const containers = [
                document.getElementById('rental-details-section'),
                document.getElementById('registration-form-wrapper'),
            ].filter(Boolean);
            const result = [];
            containers.forEach(function (c) {
                c.querySelectorAll('input, textarea, select').forEach(function (f) {
                    const t = (f.type || '').toLowerCase();
                    if (t === 'hidden' || t === 'submit' || t === 'button' || t === 'reset') return;
                    result.push(f);
                });
            });
            return result;
        }

        function setupFieldValidation() {
            const fields = getAllValidatedFields();
            fields.forEach(function (f) {
                decorateField(f);
                ['input', 'change', 'blur'].forEach(function (evt) {
                    f.addEventListener(evt, function () {
                        refreshFieldStatus(f);
                        if (f.id === 'rental_fecha_inicio') {
                            const fin = document.getElementById('rental_fecha_final');
                            if (fin) refreshFieldStatus(fin);
                        }
                        if (f.id === 'rental_tipo_auto') {
                            const otro = document.getElementById('rental_tipo_auto_otro');
                            if (otro) refreshFieldStatus(otro);
                        }
                    });
                });
                refreshFieldStatus(f);
            });
        }

        function refreshAllFieldStatuses() {
            getAllValidatedFields().forEach(refreshFieldStatus);
        }

        setupFieldValidation();
        window.__publicRegRefreshFieldStatuses = refreshAllFieldStatuses;

        // Si el servidor regresó la vista con errores de validación, repoblar los
        // hidden inputs (para que un segundo envío conserve los datos del paso 2)
        // y saltar al paso 3 para que el usuario vea los mensajes junto a los campos.
        <?php if ($hasFormErrors): ?>
            copyRentalToHidden();
            setStep(3, { scroll: true });
        <?php endif; ?>

        // ========== SITUACIÓN FINANCIERA ==========
        const situacionField = document.getElementById('situacion-financiera');
        const detalleContainer = document.getElementById('detalle-situacion-container');
        const detalleLabel = document.getElementById('detalle-situacion-label');
        
        if (situacionField && detalleContainer && detalleLabel) {
            const labelTexts = {
                'independiente': '¿Qué profesión o actividad ejerce actualmente? Indique cantidad de años.',
                'asalariado': '¿En qué empresa o institución trabaja actualmente? Indique cantidad de años.',
                'tiene_empresa': 'Ingrese el nombre de su empresa y cédula jurídica. Indique cantidad de años.'
            };
            
            situacionField.addEventListener('change', function() {
                const value = this.value;
                
                if (value && labelTexts[value]) {
                    detalleLabel.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">description</span>' + labelTexts[value];
                    detalleContainer.style.display = 'block';
                } else {
                    detalleContainer.style.display = 'none';
                    document.getElementById('client-situacion_financiera_detalle').value = '';
                }
            });
            
            // Mostrar campo si ya hay un valor seleccionado (edición)
            if (situacionField.value) {
                situacionField.dispatchEvent(new Event('change'));
            }
        }

        // ========== CONSULTA HACIENDA AUTOMÁTICA ==========
        const cedulaInput = document.getElementById('public-cedula-input');
        const nombreInput = document.getElementById('public-nombre-input');
        const loadingEl = document.getElementById('hacienda-loading-public');
        const errorEl = document.getElementById('hacienda-error-public');
        
        let consultaTimeout = null;
        
        if (cedulaInput && nombreInput) {
            cedulaInput.addEventListener('input', function() {
                const cedula = this.value.trim();
                
                // Ocultar mensajes anteriores
                if (errorEl) errorEl.style.display = 'none';
                
                // Cancelar consulta anterior
                if (consultaTimeout) {
                    clearTimeout(consultaTimeout);
                }
                
                // Validar formato de cédula (9 o 10 dígitos)
                if (!/^\d{9,10}$/.test(cedula)) {
                    return;
                }
                
                // Mostrar loading
                if (loadingEl) loadingEl.style.display = 'inline-flex';
                if (errorEl) errorEl.style.display = 'none';
                
                // Esperar 500ms después de que el usuario termine de escribir
                consultaTimeout = setTimeout(function() {
                    consultarHaciendaPublic(cedula);
                }, 500);
            });
        }
        
        function consultarHaciendaPublic(cedula) {
            console.log('Consultando Hacienda para cédula:', cedula);
            
            fetch('/hacienda/consultar-public', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    cedula: cedula
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta Hacienda:', data);
                if (loadingEl) loadingEl.style.display = 'none';
                
                if (data.success && data.data && data.data.nombre) {
                    // Llenar campo de nombre
                    if (nombreInput) {
                        nombreInput.value = data.data.nombre.toUpperCase();
                        nombreInput.style.backgroundColor = '#e8f5e8';
                        setTimeout(() => {
                            nombreInput.style.backgroundColor = '';
                        }, 2000);
                        nombreInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                } else {
                    // No se encontró información
                    if (errorEl) errorEl.style.display = 'inline';
                }
            })
            .catch(error => {
                console.error('Error al consultar Hacienda:', error);
                if (loadingEl) loadingEl.style.display = 'none';
                if (errorEl) errorEl.style.display = 'inline';
            });
        }
    });
</script>
</body>
</html>

