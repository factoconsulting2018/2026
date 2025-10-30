<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$this->title = 'Actualizar Alquiler: ' . $model->rental_id;
$this->params['breadcrumbs'][] = ['label' => 'Alquileres', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->rental_id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="rental-update">

    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">
            edit
        </span>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'rental-form'],
        'fieldConfig' => [
            'template' => '<div class="form-group mb-3">{label}{input}{error}</div>',
            'labelOptions' => ['class' => 'form-label fw-bold'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            person
                        </span>
                        Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'client_id')->dropDownList(
                        ArrayHelper::map($clients, 'id', function($client) {
                            return $client->full_name . ' (' . $client->cedula_fisica . ')';
                        }),
                        [
                            'prompt' => 'Seleccionar cliente...',
                            'class' => 'form-select',
                            'required' => true
                        ]
                    ) ?>

                    <?= $form->field($model, 'choferes_autorizados')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Ingrese los choferes autorizados (uno por línea)'
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            directions_car
                        </span>
                        Información del Vehículo
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'car_id')->dropDownList(
                        ArrayHelper::map($cars, 'id', function($car) {
                            return $car->nombre . ' (' . $car->placa . ')';
                        }),
                        [
                            'prompt' => 'Seleccionar vehículo...',
                            'class' => 'form-select',
                            'required' => true
                        ]
                    ) ?>

                    <?= $form->field($model, 'cantidad_dias')->input('number', [
                        'min' => 1,
                        'required' => true
                    ]) ?>

                    <?= $form->field($model, 'precio_por_dia')->input('number', [
                        'step' => '0.01',
                        'min' => 0
                    ]) ?>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Precio Total</label>
                        <input type="text" id="total-preview" class="form-control" readonly 
                               placeholder="Se calculará automáticamente" 
                               style="background-color: #f8f9fa;">
                        <small class="form-text text-muted">Se calcula automáticamente: Cantidad de días × Precio por día</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            calendar_today
                        </span>
                        Fechas del Alquiler
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'fecha_inicio')->input('date', [
                        'required' => true,
                        'value' => $model->fecha_inicio ? date('Y-m-d', strtotime($model->fecha_inicio)) : ''
                    ]) ?>

                    <?= $form->field($model, 'hora_inicio')->input('time') ?>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Fecha Final</label>
                        <input type="text" id="fecha-final-preview" class="form-control" readonly 
                               placeholder="Se calculará automáticamente" 
                               style="background-color: #f8f9fa;">
                        <small class="form-text text-muted">Se calcula automáticamente: Fecha de inicio + Cantidad de días</small>
                        <!-- Campo oculto para enviar fecha_final calculada -->
                        <input type="hidden" id="rental-fecha_final" name="Rental[fecha_final]" value="<?= Html::encode($model->fecha_final ?: '') ?>">
                    </div>

                    <?= $form->field($model, 'hora_final')->input('time') ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            location_on
                        </span>
                        Ubicaciones y Estado
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'lugar_entrega')->textInput([
                        'placeholder' => 'Lugar de entrega del vehículo'
                    ]) ?>

                    <?= $form->field($model, 'lugar_retiro')->textInput([
                        'placeholder' => 'Lugar de retiro del vehículo'
                    ]) ?>

                    <?= $form->field($model, 'estado_pago')->dropDownList([
                        'pendiente' => 'Pendiente',
                        'pagado' => 'Pagado',
                        'reservado' => 'Reservado',
                        'cancelado' => 'Cancelado'
                    ], [
                        'class' => 'form-select'
                    ]) ?>

                    <?= $form->field($model, 'comprobante_pago')->dropDownList([
                        '' => 'Seleccionar método de pago',
                        'Sinpe Móvil' => 'Sinpe Móvil',
                        'Transferencia - BCR' => 'Transferencia - BCR',
                        'Transferencia - BAC' => 'Transferencia - BAC',
                        'Transferencia - BN' => 'Transferencia - BN',
                        'Pago en efectivo' => 'Pago en efectivo',
                        'Tarjeta de crédito' => 'Tarjeta de crédito'
                    ], [
                        'class' => 'form-select'
                    ]) ?>

                    <?= $form->field($model, 'ejecutivo')->dropDownList([
                        '' => 'Seleccionar ejecutivo',
                        'Gerardo' => 'Gerardo',
                        'Christian' => 'Christian',
                        'Alejandro' => 'Alejandro',
                        'Jose Ed' => 'Jose Ed',
                        'Ronald RA' => 'Ronald RA',
                        'Otro' => 'Otro'
                    ], [
                        'class' => 'form-select',
                        'id' => 'ejecutivo-select'
                    ]) ?>

                    <?= $form->field($model, 'ejecutivo_otro')->textInput([
                        'placeholder' => 'Especificar ejecutivo',
                        'style' => 'display: none;',
                        'id' => 'ejecutivo-otro-field'
                    ])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            notes
                        </span>
                        Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'condiciones_especiales')->textarea([
                        'rows' => 10,
                        'placeholder' => 'Condiciones del alquiler (HTML) solo para esta orden. Si lo dejas vacío, se usará el HTML global de Configuración.'
                    ])->label('Condiciones de la Renta (HTML) – Página 2 del PDF') ?>

                    <?= $form->field($model, 'comprobante_pago')->textInput([
                        'placeholder' => 'Número de comprobante de pago'
                    ]) ?>

                    <div class="form-check mt-3">
                        <?= Html::activeCheckbox($model, 'correapartir_enabled', [
                            'class' => 'form-check-input',
                            'label' => 'Habilitar Correapartir',
                            'labelOptions' => ['class' => 'form-check-label']
                        ]) ?>
                    </div>

                    <div class="form-group" id="correapartir-field" style="<?= $model->correapartir_enabled ? '' : 'display: none;' ?>">
                        <?= $form->field($model, 'fecha_correapartir')->input('datetime-local') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex gap-3">
            <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Actualizar Alquiler', [
                'class' => 'btn btn-success btn-lg',
                'style' => 'background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); border: none;'
            ]) ?>

            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">visibility</span>Ver', ['view', 'id' => $model->id], [
                'class' => 'btn btn-info btn-lg'
            ]) ?>

            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">arrow_back</span>Volver', ['index'], [
                'class' => 'btn btn-secondary btn-lg'
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calcular automáticamente el precio total y fecha final
    const cantidadDias = document.getElementById('rental-cantidad_dias');
    const precioPorDia = document.getElementById('rental-precio_por_dia');
    const fechaInicio = document.getElementById('rental-fecha_inicio');
    const totalPreview = document.getElementById('total-preview');
    const fechaFinalPreview = document.getElementById('fecha-final-preview');
    
    function calcularTotal() {
        const dias = parseFloat(cantidadDias.value) || 0;
        const precio = parseFloat(precioPorDia.value) || 0;
        const total = dias * precio;
        if (total > 0) {
            totalPreview.value = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            totalPreview.value = '';
        }
    }
    
    function calcularFechaFinal() {
        const fechaIni = fechaInicio.value;
        const dias = parseInt(cantidadDias.value) || 0;
        
        if (fechaIni && dias > 0) {
            // Validar que la fecha no sea inválida
            const fecha = new Date(fechaIni);
            if (isNaN(fecha.getTime())) {
                fechaFinalPreview.value = 'Fecha inválida';
                const fechaFinalHidden = document.getElementById('rental-fecha_final');
                if (fechaFinalHidden) {
                    fechaFinalHidden.value = '';
                }
                return;
            }
            
            fecha.setDate(fecha.getDate() + dias);
            const fechaFormateada = fecha.toISOString().split('T')[0];
            fechaFinalPreview.value = fechaFormateada;
            
            // Actualizar también el campo oculto
            const fechaFinalHidden = document.getElementById('rental-fecha_final');
            if (fechaFinalHidden) {
                fechaFinalHidden.value = fechaFormateada;
            }
        } else {
            fechaFinalPreview.value = '';
            const fechaFinalHidden = document.getElementById('rental-fecha_final');
            if (fechaFinalHidden) {
                fechaFinalHidden.value = '';
            }
        }
    }
    
    if (cantidadDias && precioPorDia && totalPreview) {
        cantidadDias.addEventListener('input', calcularTotal);
        precioPorDia.addEventListener('input', calcularTotal);
        // Calcular inicialmente
        calcularTotal();
    }
    
    if (cantidadDias && fechaInicio && fechaFinalPreview) {
        cantidadDias.addEventListener('input', calcularFechaFinal);
        fechaInicio.addEventListener('input', calcularFechaFinal);
        // Calcular inicialmente
        calcularFechaFinal();
        
        // Si ya hay una fecha final en el modelo, mostrarla
        const fechaFinalHidden = document.getElementById('rental-fecha_final');
        if (fechaFinalHidden && fechaFinalHidden.value) {
            fechaFinalPreview.value = fechaFinalHidden.value;
        }
    }
    
    // Mostrar/ocultar campo de correapartir
    const correapartirCheckbox = document.getElementById('rental-correapartir_enabled');
    const correapartirField = document.getElementById('correapartir-field');
    
    if (correapartirCheckbox && correapartirField) {
        correapartirCheckbox.addEventListener('change', function() {
            correapartirField.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Manejar campo "Otro" en ejecutivo
    const ejecutivoSelect = document.getElementById('ejecutivo-select');
    const ejecutivoOtroField = document.getElementById('ejecutivo-otro-field');
    
    if (ejecutivoSelect && ejecutivoOtroField) {
        // Verificar si ya hay un valor "Otro" seleccionado
        if (ejecutivoSelect.value === 'Otro') {
            ejecutivoOtroField.style.display = 'block';
            ejecutivoOtroField.required = true;
        }
        
        ejecutivoSelect.addEventListener('change', function() {
            if (this.value === 'Otro') {
                ejecutivoOtroField.style.display = 'block';
                ejecutivoOtroField.required = true;
            } else {
                ejecutivoOtroField.style.display = 'none';
                ejecutivoOtroField.required = false;
                ejecutivoOtroField.value = '';
            }
        });
    }
});
</script>

<style>
.rental-form .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.rental-form .form-label {
    color: #333;
    margin-bottom: 8px;
}

.rental-form .form-control:focus,
.rental-form .form-select:focus {
    border-color: #3fa9f5;
    box-shadow: 0 0 0 0.2rem rgba(63, 169, 245, 0.25);
}

.rental-form .btn-lg {
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 8px;
}

.rental-form .btn:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>
