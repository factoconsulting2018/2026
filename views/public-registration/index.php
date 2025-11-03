<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Client $model */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - Facto Rent a Car</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    </style>
</head>
<body>
<div class="registration-container">
    <div class="card">
        <div class="card-header text-center">
            <h2 class="mb-0">
                <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 10px;">
                    person_add
                </span>
                Registro de Nuevo Cliente
            </h2>
            <p class="mb-0 mt-2" style="font-size: 14px; opacity: 0.9;">
                Completa el formulario y tu solicitud será revisada
            </p>
        </div>
        <div class="card-body p-4">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>
            
            <?php $form = ActiveForm::begin([
                'id' => 'registration-form',
                'options' => ['class' => 'form-horizontal'],
                'fieldConfig' => [
                    'template' => "<div class='row mb-3'><div class='col-sm-4'>{label}</div><div class='col-sm-8'>{input}{error}</div></div>",
                    'labelOptions' => ['class' => 'form-label'],
                ],
            ]); ?>
            
            <?= Html::hiddenInput('Client[approval_status]', 'pending') ?>
            
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
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
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
                            'placeholder' => 'Ej: RONALD ALBERTO ROJAS CASTRO',
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

            <div class="text-center mt-4">
                <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">send</span>Enviar Solicitud', [
                    'class' => 'btn btn-primary btn-lg'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
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
</body>
</html>

