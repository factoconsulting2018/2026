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

$convertTimeToParts = function (?string $time, string $default = '09:00') {
    $time = $time ?: $default;
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        $time = $default;
    }
    [$hour, $minute] = explode(':', $time);
    $hour = (int)$hour;
    $period = $hour >= 12 ? 'PM' : 'AM';
    if ($hour === 0) {
        $displayHour = 12;
    } elseif ($hour > 12) {
        $displayHour = $hour - 12;
    } else {
        $displayHour = $hour;
    }
    return [
        'hour' => str_pad($displayHour, 2, '0', STR_PAD_LEFT),
        'minute' => $minute,
        'period' => $period,
    ];
};

$startParts = $convertTimeToParts($model->hora_inicio, '09:00');
$endParts = $convertTimeToParts($model->hora_final, '18:00');

$correapartirDate = '';
$correapartirParts = $convertTimeToParts(null, '09:00');
if (!empty($model->fecha_correapartir)) {
    $timestamp = strtotime($model->fecha_correapartir);
    if ($timestamp) {
        $correapartirDate = date('Y-m-d', $timestamp);
        $correapartirParts = $convertTimeToParts(date('H:i', $timestamp), date('H:i', $timestamp));
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
        <label class="form-label fw-bold">Hora de Inicio</label>
        <div class="row g-2">
            <div class="col-4">
                <select class="form-select" id="hora-inicio-hours">
                    <?php for ($i = 1; $i <= 12; $i++): $value = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $value ?>" <?= $startParts['hour'] === $value ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-4">
                <select class="form-select" id="hora-inicio-minutes">
                    <?php for ($i = 0; $i < 60; $i++): $minute = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $minute ?>" <?= $startParts['minute'] === $minute ? 'selected' : '' ?>><?= $minute ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-4">
                <select class="form-select" id="hora-inicio-period">
                    <option value="AM" <?= $startParts['period'] === 'AM' ? 'selected' : '' ?>>AM</option>
                    <option value="PM" <?= $startParts['period'] === 'PM' ? 'selected' : '' ?>>PM</option>
                </select>
            </div>
        </div>
        <?= Html::activeHiddenInput($model, 'hora_inicio', ['id' => 'rental-hora_inicio']) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'fecha_final')->input('date') ?>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Hora Final</label>
        <div class="row g-2">
            <div class="col-4">
                <select class="form-select" id="hora-final-hours">
                    <?php for ($i = 1; $i <= 12; $i++): $value = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $value ?>" <?= $endParts['hour'] === $value ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-4">
                <select class="form-select" id="hora-final-minutes">
                    <?php for ($i = 0; $i < 60; $i++): $minute = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $minute ?>" <?= $endParts['minute'] === $minute ? 'selected' : '' ?>><?= $minute ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-4">
                <select class="form-select" id="hora-final-period">
                    <option value="AM" <?= $endParts['period'] === 'AM' ? 'selected' : '' ?>>AM</option>
                    <option value="PM" <?= $endParts['period'] === 'PM' ? 'selected' : '' ?>>PM</option>
                </select>
            </div>
        </div>
        <?= Html::activeHiddenInput($model, 'hora_final', ['id' => 'rental-hora_final']) ?>
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

<div class="form-group mb-3" id="correapartir-field" style="<?= $model->correapartir_enabled ? '' : 'display: none;' ?>">
    <label class="form-label fw-bold" style="color: #FF6600;">Fecha y Hora de Correapartir</label>
    <div class="row mb-2">
        <div class="col-md-6">
            <label class="form-label" style="color: #FF6600;">Fecha</label>
            <input type="date" class="form-control" id="correapartir-fecha" value="<?= $correapartirDate ?>">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-4">
            <label class="form-label" style="color: #FF6600; font-weight: bold;">Hora</label>
            <select class="form-select" id="correapartir-hours">
                <?php for ($i = 1; $i <= 12; $i++): $value = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                    <option value="<?= $value ?>" <?= $correapartirParts['hour'] === $value ? 'selected' : '' ?>><?= $value ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-4">
            <label class="form-label" style="color: #FF6600; font-weight: bold;">Minutos</label>
            <select class="form-select" id="correapartir-minutes">
                <?php for ($i = 0; $i < 60; $i++): $minute = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                    <option value="<?= $minute ?>" <?= $correapartirParts['minute'] === $minute ? 'selected' : '' ?>><?= $minute ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-4">
            <label class="form-label" style="color: #FF6600; font-weight: bold;">Periodo</label>
            <select class="form-select" id="correapartir-period">
                <option value="AM" <?= $correapartirParts['period'] === 'AM' ? 'selected' : '' ?>>AM</option>
                <option value="PM" <?= $correapartirParts['period'] === 'PM' ? 'selected' : '' ?>>PM</option>
            </select>
        </div>
    </div>
    <?= Html::activeHiddenInput($model, 'fecha_correapartir', ['id' => 'rental-fecha_correapartir']) ?>
</div>

<?= $form->field($model, 'condiciones_especiales')->textarea(['rows' => 4]) ?>

<div class="form-group mt-4">
    <?= Html::submitButton($model->isNewRecord ? 'Guardar Orden Asincrónica' : 'Actualizar Orden Asincrónica', [
        'class' => 'btn btn-primary'
    ]) ?>
    <?= Html::a('Regresar', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
(function() {
    const hiddenHoraInicio = document.getElementById('rental-hora_inicio');
    const hiddenHoraFinal = document.getElementById('rental-hora_final');

    function updateHiddenTime(prefix, hiddenInput) {
        const hours = document.getElementById(prefix + '-hours');
        const minutes = document.getElementById(prefix + '-minutes');
        const period = document.getElementById(prefix + '-period');
        if (!hours || !minutes || !period || !hiddenInput) {
            return;
        }
        let hour = parseInt(hours.value, 10);
        if (period.value === 'PM' && hour < 12) {
            hour += 12;
        }
        if (period.value === 'AM' && hour === 12) {
            hour = 0;
        }
        const hourStr = hour.toString().padStart(2, '0');
        hiddenInput.value = hourStr + ':' + minutes.value;
    }

    ['hora-inicio', 'hora-final'].forEach(function(prefix) {
        const hidden = prefix === 'hora-inicio' ? hiddenHoraInicio : hiddenHoraFinal;
        ['hours', 'minutes', 'period'].forEach(function(suffix) {
            const element = document.getElementById(prefix + '-' + suffix);
            if (element) {
                element.addEventListener('change', function() {
                    updateHiddenTime(prefix, hidden);
                });
            }
        });
        updateHiddenTime(prefix, hidden);
    });

    // Correapartir handling
    const correCheckbox = document.getElementById('rental-correapartir_enabled');
    const correField = document.getElementById('correapartir-field');
    const correFecha = document.getElementById('correapartir-fecha');
    const correHours = document.getElementById('correapartir-hours');
    const correMinutes = document.getElementById('correapartir-minutes');
    const correPeriod = document.getElementById('correapartir-period');
    const correHidden = document.getElementById('rental-fecha_correapartir');

    function updateCorreapartirHidden() {
        if (!correHidden || !correFecha) {
            return;
        }
        if (!correCheckbox || !correCheckbox.checked) {
            correHidden.value = '';
            return;
        }
        if (!correFecha.value) {
            correHidden.value = '';
            return;
        }
        let hour = parseInt(correHours.value, 10);
        if (correPeriod.value === 'PM' && hour < 12) {
            hour += 12;
        }
        if (correPeriod.value === 'AM' && hour === 12) {
            hour = 0;
        }
        const hourStr = hour.toString().padStart(2, '0');
        correHidden.value = correFecha.value + ' ' + hourStr + ':' + correMinutes.value + ':00';
    }

    if (correCheckbox) {
        correCheckbox.addEventListener('change', function() {
            if (correField) {
                correField.style.display = correCheckbox.checked ? '' : 'none';
            }
            updateCorreapartirHidden();
        });
    }

    [correFecha, correHours, correMinutes, correPeriod].forEach(function(element) {
        if (element) {
            element.addEventListener('change', updateCorreapartirHidden);
        }
    });

    updateCorreapartirHidden();
})();
JS);
?>

