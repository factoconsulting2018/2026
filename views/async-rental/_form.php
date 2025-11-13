<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$clientItems = ArrayHelper::map($clients, 'id', function ($client) {
    return $client->full_name . ' (' . $client->cedula_fisica . ')';
});

$carItems = ArrayHelper::map($cars, 'id', function ($car) {
    $placa = $car->placa ? ' - ' . $car->placa : '';
    return strtoupper($car->nombre) . $placa;
});

if (empty($model->hora_inicio)) {
    $model->hora_inicio = '09:00';
}
if (empty($model->hora_final)) {
    $model->hora_final = '18:00';
}

$correapartirValue = $model->fecha_correapartir;
if (!empty($correapartirValue) && strpos($correapartirValue, 'T') === false) {
    $timestamp = strtotime($correapartirValue);
    if ($timestamp) {
        $correapartirValue = date('Y-m-d\TH:i', $timestamp);
    }
}
?>

<?php $form = ActiveForm::begin([
    'options' => ['class' => 'card card-body shadow-sm'],
]); ?>

<?= Html::activeHiddenInput($model, 'is_async', ['value' => 1]) ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'client_id')->dropDownList($clientItems, [
            'prompt' => 'Seleccionar cliente...',
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'car_id')->dropDownList($carItems, [
            'prompt' => 'Seleccionar vehículo...',
        ]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'fecha_inicio')->input('date') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'hora_inicio')->input('time') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'fecha_final')->input('date') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'hora_final')->input('time') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'cantidad_dias')->input('number', ['min' => 1]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'precio_por_dia')->input('number', ['step' => '0.01', 'min' => 0]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'medio_dia_enabled')->checkbox() ?>
        <?= $form->field($model, 'medio_dia_valor')->input('number', ['step' => '0.01', 'min' => 0]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'lugar_entrega')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'lugar_retiro')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'estado_pago')->dropDownList([
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'reservado' => 'Reservado',
            'cancelado' => 'Cancelado',
        ], ['prompt' => 'Seleccionar estado...']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'comprobante_pago')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'ejecutivo')->dropDownList([
            '' => 'No aplicar',
            'Gerardo' => 'Gerardo',
            'Christian' => 'Christian',
            'Alejandro' => 'Alejandro',
            'Jose Ed' => 'Jose Ed',
            'Ronald RA' => 'Ronald RA',
            'Otro' => 'Otro',
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'ejecutivo_otro')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<?= $form->field($model, 'choferes_autorizados')->textarea(['rows' => 3]) ?>

<?= $form->field($model, 'correapartir_enabled')->checkbox() ?>

<?= $form->field($model, 'fecha_correapartir')->input('datetime-local', ['value' => $correapartirValue]) ?>

<?= $form->field($model, 'condiciones_especiales')->textarea(['rows' => 4]) ?>

<div class="form-group mt-4">
    <?= Html::submitButton($model->isNewRecord ? 'Guardar Orden Asincrónica' : 'Actualizar Orden Asincrónica', [
        'class' => 'btn btn-primary'
    ]) ?>
    <?= Html::a('Regresar', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
</div>

<?php ActiveForm::end(); ?>

