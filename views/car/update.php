<?php
/** @var yii\web\View $this */
/** @var app\models\Car $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Vehículo: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Vehículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

// Registrar archivos de validación
$this->registerCssFile('@web/css/form-validation.css');
$this->registerJsFile('@web/js/form-validation.js', ['depends' => [yii\web\JqueryAsset::class]]);
?>

<div class="car-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('👁️ Ver', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('← Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'id' => 'car-form',
                'options' => ['enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'template' => '<div class="row"><div class="col-md-2">{label}</div><div class="col-md-10">{input}{error}</div></div>',
                    'labelOptions' => ['class' => 'form-label'],
                ],
            ]); ?>

            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">🚗 Información del Vehículo</h5>
                    
                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true, 'placeholder' => 'Nombre del vehículo', 'required' => true]) ?>
                    
                    <?= $form->field($model, 'placa')->textInput(['maxlength' => true, 'placeholder' => 'ABC-123', 'required' => true]) ?>
                    
                    <?= $form->field($model, 'vin')->textInput(['maxlength' => true, 'placeholder' => 'VIN del vehículo']) ?>
                    
                    <?= $form->field($model, 'marca_id')->dropDownList($brands, [
                        'prompt' => 'Seleccionar marca...',
                        'required' => true
                    ]) ?>
                    
                    <?= $form->field($model, 'cantidad_pasajeros')->textInput(['type' => 'number', 'placeholder' => '5', 'required' => true]) ?>
                    
                    <?= $form->field($model, 'empresa')->dropDownList([
                        '' => 'Seleccionar empresa...',
                        'Facto Rent a Car' => 'Facto Rent a Car',
                        'Moviliza' => 'Moviliza'
                    ], ['required' => true]) ?>
                    
                    <?= $form->field($model, 'status')->dropDownList([
                        '' => 'Seleccionar estado...',
                        'disponible' => 'Disponible',
                        'alquilado' => 'Alquilado',
                        'mantenimiento' => 'Mantenimiento',
                        'fuera_servicio' => 'Fuera de Servicio'
                    ], ['required' => true]) ?>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">🛡️ Información de Seguro</h5>
                    
                    <?= $form->field($model, 'empresa_seguro')->textInput(['maxlength' => true, 'placeholder' => 'Nombre de la empresa de seguro']) ?>
                    
                    <?= $form->field($model, 'telefono_seguro')->textInput(['maxlength' => true, 'placeholder' => 'Teléfono de contacto']) ?>
                    
                    <?= $form->field($model, 'imagen')->textInput(['maxlength' => true, 'placeholder' => 'URL de la imagen']) ?>
                    
                    <h5 class="mb-3 mt-4">📋 Características</h5>
                    
                    <?= $form->field($model, 'caracteristicas')->textarea(['rows' => 5, 'placeholder' => 'Características del vehículo...']) ?>
                </div>
            </div>

            <div class="form-group mt-4">
                <div class="d-flex justify-content-between">
                    <?= Html::a('Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::submitButton('💾 Guardar Cambios', ['class' => 'btn btn-success']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
