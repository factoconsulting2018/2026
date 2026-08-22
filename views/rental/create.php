<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */
/** @var bool $movilizaPriorityEnabled */

$this->title = 'Crear Alquiler';
$this->params['breadcrumbs'][] = ['label' => 'Alquileres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$movilizaPriorityEnabled = !empty($movilizaPriorityEnabled);

$carDropdownItems = [];
$carDropdownOptions = [];
foreach ($cars as $car) {
    $carDropdownItems[$car->id] = $car->nombre . ' (' . $car->placa . ')';
    $carDropdownOptions[$car->id] = [
        'data-empresa' => (string) ($car->empresa ?? ''),
        'data-status' => (string) ($car->status ?? ''),
        'data-skip-priority' => $car->skipsPriority() ? '1' : '0',
    ];
}
?>
<div class="rental-create">

    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">
            receipt_long
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

    <!-- FECHAS DEL ALQUILER - PRIORITARIO -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            calendar_today
                        </span>
                        📅 Fechas del Alquiler
                        <small class="float-end" style="color: white;">Verificar disponibilidad del vehículo</small>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Fila 1: Fechas de inicio, final y cantidad de días -->
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'fecha_inicio')->input('date', [
                                'required' => true,
                                'value' => $model->fecha_inicio ?: date('Y-m-d'),
                                'id' => 'rental-fecha_inicio'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'fecha_final')->input('date', [
                                // No requerido; se calcula automáticamente y es opcional si es por horas
                                'id' => 'rental-fecha_final'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'cantidad_dias')->input('number', [
                                'min' => 1,
                                'value' => $model->cantidad_dias ?: 3,
                                'required' => true,
                                'id' => 'rental-cantidad_dias'
                            ]) ?>
                        </div>
                    </div>

                    <!-- Correapartir: opcional, después de Cantidad de días -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mt-3" style="background-color: #d4edda; border: 2px solid #28a745; border-radius: 8px; padding: 12px;">
                                <?= Html::activeCheckbox($model, 'correapartir_enabled', [
                                    'class' => 'form-check-input',
                                    'label' => 'Habilitar corre apartir (opcional)',
                                    'labelOptions' => ['class' => 'form-check-label', 'style' => 'font-weight: 600; color: #155724;']
                                ]) ?>
                            </div>

                            <div class="form-group mb-3" id="correapartir-datetime-field" style="display: none;">
                                <label class="form-label fw-bold">Fecha y Hora de Correapartir</label>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="correapartir-fecha">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label">Hora</label>
                                        <select class="form-select" id="correapartir-hours">
                                            <?php for ($i = 1; $i <= 12; $i++) { echo '<option value="' . $i . '">' . $i . '</option>'; } ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Minutos</label>
                                        <select class="form-select" id="correapartir-minutes">
                                            <?php for ($i = 0; $i < 60; $i++) { $min = str_pad($i, 2, '0', STR_PAD_LEFT); echo '<option value="' . $min . '">' . $min . '</option>'; } ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Periodo</label>
                                        <select class="form-select" id="correapartir-period">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" id="rental-fecha_correapartir" name="Rental[fecha_correapartir]" value="<?= $model->fecha_correapartir ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fila 2: Horas en formato 12h -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Hora de Inicio</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <select class="form-select" id="hora-inicio-hours">
                                            <?php
                                            for ($i = 1; $i <= 12; $i++) {
                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" id="hora-inicio-minutes">
                                            <?php
                                            for ($i = 0; $i < 60; $i++) {
                                                $min = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                echo '<option value="' . $min . '">' . $min . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" id="hora-inicio-period">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" id="rental-hora_inicio" name="Rental[hora_inicio]" value="<?= $model->hora_inicio ?: '09:00' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Hora Final</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <select class="form-select" id="hora-final-hours">
                                            <?php
                                            for ($i = 1; $i <= 12; $i++) {
                                                echo '<option value="' . $i . '">' . $i . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" id="hora-final-minutes">
                                            <?php
                                            for ($i = 0; $i < 60; $i++) {
                                                $min = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                echo '<option value="' . $min . '">' . $min . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" id="hora-final-period">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" id="rental-hora_final" name="Rental[hora_final]" value="<?= $model->hora_final ?: '18:00' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fila 3: Estado de disponibilidad -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Estado de Disponibilidad</label>
                                <div id="availability-status" class="form-control" style="background-color: #f8f9fa; min-height: 38px; display: flex; align-items: center;">
                                    <span class="text-muted">Seleccione vehículo y fechas para verificar disponibilidad</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DEL VEHÍCULO -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            directions_car
                        </span>
                        <span style="color: white;">Información del Vehículo</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'car_id')->dropDownList(
                        $carDropdownItems,
                        [
                            'prompt' => 'Seleccionar vehículo...',
                            'class' => 'form-select',
                            'required' => true,
                            'options' => $carDropdownOptions,
                            'oninvalid' => "this.setCustomValidity('Debes seleccionar un vehículo.')",
                            'oninput' => "this.setCustomValidity('')",
                        ]
                    ) ?>
                    <p class="small text-muted mb-0">
                        <span class="text-success fw-bold">Verde</span> = Facto Rent a Car disponible &nbsp;·&nbsp;
                        <span class="text-secondary">Gris</span> = Moviliza
                    </p>

                    <?php
                    $precioPorDiaValor = $model->precio_por_dia !== null && $model->precio_por_dia !== ''
                        ? (float) $model->precio_por_dia
                        : null;
                    $precioPorDiaDisplay = $precioPorDiaValor > 0
                        ? number_format($precioPorDiaValor, 0, '.', ',')
                        : '';
                    ?>
                    <div class="form-group mb-3 field-rental-precio_por_dia required">
                        <label class="form-label fw-bold" for="precio-por-dia-display">Precio por Día</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">₡</span>
                            <input type="text"
                                   id="precio-por-dia-display"
                                   class="form-control"
                                   inputmode="decimal"
                                   autocomplete="off"
                                   placeholder="35,000"
                                   required
                                   value="<?= Html::encode($precioPorDiaDisplay) ?>">
                        </div>
                        <?= Html::activeHiddenInput($model, 'precio_por_dia', ['id' => 'rental-precio_por_dia']) ?>
                        <?php if ($model->hasErrors('precio_por_dia')): ?>
                            <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('precio_por_dia')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Checkbox 1/2 día -->
                    <div class="form-check mt-3 mb-3" style="background-color: #e3f2fd; border: 2px solid #2196f3; border-radius: 8px; padding: 12px;">
                        <?= Html::activeCheckbox($model, 'medio_dia_enabled', [
                            'class' => 'form-check-input',
                            'id' => 'rental-medio_dia_enabled',
                            'label' => '1/2 día (opcional)',
                            'labelOptions' => ['class' => 'form-check-label', 'style' => 'font-weight: 600; color: #1565c0;']
                        ]) ?>
                    </div>

                    <!-- Campo de valor medio día (se muestra solo cuando está activado) -->
                    <div class="form-group mb-3" id="medio-dia-valor-field" style="display: none;">
                        <?= $form->field($model, 'medio_dia_valor')->input('number', [
                            'step' => '0.01',
                            'min' => 0,
                            'id' => 'rental-medio_dia_valor',
                            'placeholder' => '0.00'
                        ]) ?>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Precio Total</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">₡</span>
                            <input type="text" id="total-preview" class="form-control" readonly
                                   placeholder="Se calculará automáticamente"
                                   style="background-color: #f8f9fa;">
                        </div>
                        <small class="form-text text-muted">Se calcula automáticamente: <span id="precio-calculo-texto">Cantidad de días × Precio por día</span></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            person
                        </span>
                        <span style="color: white;">Información del Cliente</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3 field-rental-client_id required">
                        <label class="form-label fw-bold" for="rental-client_id">Cliente</label>
                        <div class="input-group">
                            <?= Html::activeDropDownList($model, 'client_id',
                                ArrayHelper::map($clients, 'id', function ($client) {
                                    return $client->full_name . ' (' . $client->cedula_fisica . ')';
                                }),
                                [
                                    'prompt' => 'Seleccionar cliente...',
                                    'class' => 'form-select',
                                    'required' => true,
                                    'oninvalid' => "this.setCustomValidity('Debes seleccionar un cliente.')",
                                    'oninput' => "this.setCustomValidity('')",
                                ]
                            ) ?>
                            <button type="button" class="btn btn-outline-primary" id="btn-open-client-search"
                                    data-bs-toggle="modal" data-bs-target="#clientSearchModal"
                                    title="Buscar cliente">
                                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle;">search</span>
                            </button>
                        </div>
                        <?php if ($model->hasErrors('client_id')): ?>
                            <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('client_id')) ?></div>
                        <?php endif; ?>
                    </div>

                    <?= $form->field($model, 'choferes_autorizados')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Ingrese los choferes autorizados (uno por línea)'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            location_on
                        </span>
                        <span style="color: white;">Ubicaciones</span>
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
                        'finalizado' => 'Finalizado',
                    ], [
                        'class' => 'form-select',
                        'value' => 'pendiente'
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

                    <?= $form->field($model, 'numero_factura')->textInput([
                        'placeholder' => 'Ej: 001-001-00001234'
                    ]) ?>

                    <?= $form->field($model, 'fecha_factura')->input('date') ?>

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

        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            notes
                        </span>
                        <span style="color: white;">Información Adicional</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        La página 2 (Condiciones de la Renta) se toma del HTML global al crear la orden.
                        Podrás editarla de forma personalizada después de crear la orden en la pantalla de edición.
                    </div>

                    <?= $form->field($model, 'comprobante_pago')->textInput([
                        'placeholder' => 'Número de comprobante de pago'
                    ]) ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Cuadro Informe Total -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            assignment
                        </span>
                        <span style="color: white;">Informe Total</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="informe-total" style="padding: 15px; border-radius: 8px; min-height: 50px;">
                        <p class="mb-0" id="informe-mensaje" style="margin: 0;">
                            <span style="color: #28a745;">✓ Verificando formulario...</span>
                        </p>
                        <ul id="informe-lista" style="margin-top: 10px; padding-left: 20px; display: none;">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex gap-3">
            <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Crear Alquiler', [
                'class' => 'btn btn-success btn-lg',
                'style' => 'background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); border: none;'
            ]) ?>

            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">arrow_back</span>Volver', ['index'], [
                'class' => 'btn btn-secondary btn-lg'
            ]) ?>
        </div>
    </div>

    <?= Html::hiddenInput('moviliza_justificacion', (string) Yii::$app->request->post('moviliza_justificacion', ''), [
        'id' => 'moviliza-justificacion',
    ]) ?>

    <?php ActiveForm::end(); ?>

    <!-- Modal: justificación al alquilar Moviliza con Facto disponible -->
    <div class="modal fade" id="movilizaPriorityModal" tabindex="-1" aria-labelledby="movilizaPriorityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #e67700 0%, #d9480f 100%); color: #fff;">
                    <h5 class="modal-title" id="movilizaPriorityModalLabel" style="color:#fff;">
                        <span class="material-symbols-outlined align-middle" style="font-size:22px;margin-right:6px;">warning</span>
                        Justifique el alquiler de Moviliza
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Hay vehículos de <strong>Facto Rent a Car</strong> disponibles para estas fechas.
                        Debe justificar por qué alquila <strong>Moviliza</strong> antes que Facto.
                    </p>
                    <p class="small text-muted mb-2" id="moviliza-facto-count-msg"></p>
                    <label class="form-label fw-semibold" for="moviliza-justificacion-input">Motivo (mínimo 40 caracteres)</label>
                    <textarea id="moviliza-justificacion-input" class="form-control" rows="4"
                              maxlength="1000"
                              placeholder="Explique el motivo del alquiler con Moviliza..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted"><span id="moviliza-justificacion-count">0</span> / 40 mínimo</small>
                        <small id="moviliza-justificacion-hint" class="text-danger" style="display:none;">Faltan caracteres.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="moviliza-justificacion-confirm" disabled>
                        Continuar y crear orden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Búsqueda de cliente -->
    <div class="modal fade" id="clientSearchModal" tabindex="-1" aria-labelledby="clientSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="modal-title" id="clientSearchModalLabel">
                        <span class="material-symbols-outlined" style="font-size: 22px; vertical-align: middle; margin-right: 6px;">person_search</span>
                        Buscar cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><span class="material-symbols-outlined" style="font-size: 20px;">search</span></span>
                            <input type="text" class="form-control form-control-lg" id="client-search-input"
                                   placeholder="Buscar por nombre, apellido o cédula… (Enter para seleccionar la primera)"
                                   autocomplete="off">
                        </div>
                        <small class="form-text text-muted">Filtra mientras escribes. Pulsa <strong>Enter</strong> para seleccionar la primera coincidencia.</small>
                    </div>

                    <div id="client-search-status" class="mb-2 small text-muted">
                        Empieza a escribir para filtrar (o pulsa Enter sin texto para tomar el primer cliente).
                    </div>

                    <div class="table-responsive" style="max-height: 50vh;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="width: 36%;">Nombre completo</th>
                                    <th style="width: 24%;">Cédula</th>
                                    <th style="width: 24%;">WhatsApp / Email</th>
                                    <th style="width: 16%; text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="client-search-results">
                                <tr><td colspan="4" class="text-center text-muted py-4">Cargando clientes…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const SEARCH_URL = <?= json_encode(\yii\helpers\Url::to(['/client/search'])) ?>;
        const modalEl = document.getElementById('clientSearchModal');
        const inputEl = document.getElementById('client-search-input');
        const tbodyEl = document.getElementById('client-search-results');
        const statusEl = document.getElementById('client-search-status');
        const clientSelect = document.getElementById('rental-client_id');

        if (!modalEl || !inputEl || !tbodyEl || !clientSelect) {
            return;
        }

        let searchTimer = null;
        let lastResults = [];
        let lastQuery = null;

        function escapeHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function highlight(text, q) {
            const safe = escapeHtml(text);
            if (!q) return safe;
            try {
                const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
                return safe.replace(re, '<mark style="padding:0 2px;">$1</mark>');
            } catch (e) {
                return safe;
            }
        }

        function renderRows(items, q) {
            if (!items || items.length === 0) {
                tbodyEl.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron clientes.</td></tr>';
                statusEl.textContent = '0 resultados';
                return;
            }
            const rows = items.map(function (c, idx) {
                const contact = c.whatsapp ? c.whatsapp : (c.email || '—');
                return '<tr class="client-row" data-client-id="' + c.id + '" data-index="' + idx + '" style="cursor:pointer;">' +
                    '<td>' + highlight(c.full_name || ((c.nombre || '') + ' ' + (c.apellido || '')).trim(), q) + '</td>' +
                    '<td>' + highlight(c.cedula_fisica, q) + '</td>' +
                    '<td>' + escapeHtml(contact) + '</td>' +
                    '<td class="text-end">' +
                        '<button type="button" class="btn btn-sm btn-success btn-pick-client" data-client-id="' + c.id + '">' +
                            '<span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">check</span> Seleccionar' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            }).join('');
            tbodyEl.innerHTML = rows;
            statusEl.textContent = items.length + ' resultado(s). Pulsa Enter para seleccionar el primero.';
            // Marca visualmente la primera fila como activa
            const first = tbodyEl.querySelector('.client-row');
            if (first) first.classList.add('table-active');
        }

        function fetchResults(q) {
            const query = (q || '').trim();
            if (query === lastQuery) return Promise.resolve(lastResults);
            statusEl.textContent = 'Buscando…';
            lastQuery = query;
            const url = SEARCH_URL + (SEARCH_URL.indexOf('?') === -1 ? '?' : '&') + 'q=' + encodeURIComponent(query) + '&limit=60';
            return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    const items = (data && data.items) ? data.items : [];
                    lastResults = items;
                    renderRows(items, query);
                    return items;
                })
                .catch(function (err) {
                    console.error('client search error:', err);
                    tbodyEl.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Error al cargar clientes.</td></tr>';
                    statusEl.textContent = 'Error al buscar.';
                    return [];
                });
        }

        function pickClient(clientId) {
            if (!clientId) return;
            const idStr = String(clientId);
            let option = clientSelect.querySelector('option[value="' + idStr + '"]');
            if (!option) {
                // El cliente no estaba en el dropdown inicial: lo añadimos.
                const found = lastResults.find(function (c) { return String(c.id) === idStr; });
                const label = found
                    ? (found.full_name + ' (' + found.cedula_fisica + ')')
                    : ('Cliente #' + idStr);
                option = document.createElement('option');
                option.value = idStr;
                option.textContent = label;
                clientSelect.appendChild(option);
            }
            clientSelect.value = idStr;
            clientSelect.dispatchEvent(new Event('change', { bubbles: true }));

            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
        }

        // Carga inicial al abrir
        modalEl.addEventListener('shown.bs.modal', function () {
            inputEl.value = '';
            lastQuery = null;
            fetchResults('').then(function () {
                inputEl.focus();
            });
        });

        // Debounce mientras escribe
        inputEl.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                fetchResults(inputEl.value);
            }, 200);
        });

        // Enter: seleccionar la primera coincidencia
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                fetchResults(inputEl.value).then(function (items) {
                    if (items && items.length > 0) {
                        pickClient(items[0].id);
                    }
                });
            } else if (e.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });

        // Click en fila o botón "Seleccionar"
        tbodyEl.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-pick-client');
            if (btn) {
                pickClient(btn.dataset.clientId);
                return;
            }
            const row = e.target.closest('.client-row');
            if (row) {
                pickClient(row.dataset.clientId);
            }
        });
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // FORMATO MONEDA (₡) — precio por día y total
    // ==========================================
    function parseColonesValue(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }
        const cleaned = String(value).replace(/[₡\s]/g, '').replace(/,/g, '');
        const num = parseFloat(cleaned);
        return isNaN(num) ? 0 : num;
    }

    function formatColonesNumber(num) {
        const n = parseFloat(num);
        if (isNaN(n) || n <= 0) {
            return '';
        }
        return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatColonesWithSymbol(num) {
        const formatted = formatColonesNumber(num);
        return formatted ? '₡' + formatted : '';
    }

    function syncPrecioPorDiaFromDisplay() {
        const display = document.getElementById('precio-por-dia-display');
        const hidden = document.getElementById('rental-precio_por_dia');
        if (!display || !hidden) {
            return 0;
        }
        const num = parseColonesValue(display.value);
        hidden.value = num > 0 ? String(num) : '';
        return num;
    }

    function formatPrecioPorDiaInputWhileTyping(raw) {
        if (raw === '' || raw === '.') {
            return raw;
        }
        const endsWithDot = raw.endsWith('.');
        const parts = raw.split('.');
        const intPart = parts[0].replace(/^0+(?=\d)/, '');
        const intFormatted = intPart !== '' ? parseInt(intPart, 10).toLocaleString('en-US') : '';
        if (endsWithDot) {
            return intFormatted + '.';
        }
        if (parts.length > 1) {
            const decimals = parts[1].replace(/[^\d]/g, '').slice(0, 2);
            return decimals !== '' ? intFormatted + '.' + decimals : intFormatted;
        }
        return intFormatted;
    }

    function initPrecioPorDiaCurrencyInput() {
        const display = document.getElementById('precio-por-dia-display');
        const hidden = document.getElementById('rental-precio_por_dia');
        if (!display || !hidden) {
            return;
        }

        if (hidden.value) {
            display.value = formatColonesNumber(parseFloat(hidden.value));
        }

        display.addEventListener('input', function() {
            let raw = display.value.replace(/[^\d.,]/g, '').replace(/,/g, '');
            const dotIndex = raw.indexOf('.');
            if (dotIndex !== -1) {
                raw = raw.slice(0, dotIndex + 1) + raw.slice(dotIndex + 1).replace(/\./g, '');
            }
            const num = parseColonesValue(raw);
            hidden.value = num > 0 ? String(num) : '';
            display.value = formatPrecioPorDiaInputWhileTyping(raw);
            if (typeof calcularTotal === 'function') {
                calcularTotal();
            }
        });

        display.addEventListener('blur', function() {
            const num = syncPrecioPorDiaFromDisplay();
            display.value = formatColonesNumber(num);
        });

        display.addEventListener('focus', function() {
            const num = parseColonesValue(display.value);
            if (num > 0) {
                display.value = String(num);
            }
        });
    }

    initPrecioPorDiaCurrencyInput();

    // ==========================================
    // FUNCIONES DE CONVERSIÓN 12H ↔ 24H
    // ==========================================
    
    /**
     * Convierte hora de formato 24h a formato 12h
     * @param {string} hora24 - Hora en formato "HH:mm" (ej: "14:30")
     * @returns {object} - {hora: 2, minutos: 30, periodo: "PM"}
     */
    function convertir24hA12h(hora24) {
        if (!hora24 || !hora24.includes(':')) {
            return {hora: 12, minutos: 0, periodo: 'AM'};
        }
        
        const [horas, minutos] = hora24.split(':').map(Number);
        let hora12 = horas;
        let periodo = 'AM';
        
        if (horas === 0) {
            hora12 = 12;
            periodo = 'AM';
        } else if (horas === 12) {
            hora12 = 12;
            periodo = 'PM';
        } else if (horas > 12) {
            hora12 = horas - 12;
            periodo = 'PM';
        }
        
        return {
            hora: hora12,
            minutos: minutos || 0,
            periodo: periodo
        };
    }
    
    /**
     * Convierte hora de formato 12h a formato 24h
     * @param {number} hora - Hora 1-12
     * @param {number} minutos - Minutos 0-59
     * @param {string} periodo - "AM" o "PM"
     * @returns {string} - Hora en formato "HH:mm" (ej: "14:30")
     */
    function convertir12hA24h(hora, minutos, periodo) {
        let horas24 = hora;
        
        if (periodo === 'AM') {
            if (hora === 12) {
                horas24 = 0;
            }
        } else { // PM
            if (hora !== 12) {
                horas24 = hora + 12;
            }
        }
        
        const horasStr = String(horas24).padStart(2, '0');
        const minutosStr = String(minutos).padStart(2, '0');
        
        return `${horasStr}:${minutosStr}`;
    }
    
    /**
     * Actualiza el campo oculto de hora cuando cambian los selectores
     * @param {string} prefix - "hora-inicio" o "hora-final"
     */
    function actualizarHoraOculta(prefix) {
        const horasSelect = document.getElementById(`${prefix}-hours`);
        const minutosSelect = document.getElementById(`${prefix}-minutes`);
        const periodoSelect = document.getElementById(`${prefix}-period`);
        const campoOculto = document.getElementById(`rental-${prefix === 'hora-inicio' ? 'hora_inicio' : 'hora_final'}`);
        
        if (horasSelect && minutosSelect && periodoSelect && campoOculto) {
            const hora24 = convertir12hA24h(
                parseInt(horasSelect.value),
                parseInt(minutosSelect.value),
                periodoSelect.value
            );
            campoOculto.value = hora24;
        }
    }
    
    /**
     * Inicializa los selectores de hora 12h con el valor del campo oculto
     * @param {string} prefix - "hora-inicio" o "hora-final"
     * @param {string} hora24 - Hora en formato 24h
     */
    function inicializarHora12h(prefix, hora24) {
        const hora12 = convertir24hA12h(hora24);
        const horasSelect = document.getElementById(`${prefix}-hours`);
        const minutosSelect = document.getElementById(`${prefix}-minutes`);
        const periodoSelect = document.getElementById(`${prefix}-period`);
        
        if (horasSelect && minutosSelect && periodoSelect) {
            horasSelect.value = hora12.hora;
            periodoSelect.value = hora12.periodo;
            
            minutosSelect.value = String(hora12.minutos).padStart(2, '0');
        }
    }
    
    // Inicializar campos de hora 12h
    const horaInicioOculta = document.getElementById('rental-hora_inicio');
    const horaFinalOculta = document.getElementById('rental-hora_final');
    
    if (horaInicioOculta) {
        inicializarHora12h('hora-inicio', horaInicioOculta.value || '09:00');
    }
    
    if (horaFinalOculta) {
        inicializarHora12h('hora-final', horaFinalOculta.value || '18:00');
    }
    
    // Event listeners para actualizar campos ocultos cuando cambian selectores
    ['hora-inicio', 'hora-final'].forEach(prefix => {
        ['hours', 'minutes', 'period'].forEach(tipo => {
            const selector = document.getElementById(`${prefix}-${tipo}`);
            if (selector) {
                selector.addEventListener('change', function() {
                    actualizarHoraOculta(prefix);
                });
            }
        });
    });
    
    // ==========================================
    // DEFINIR VARIABLES DE FECHA (ANTES DE USARLAS)
    // ==========================================
    
    const fechaInicio = document.getElementById('rental-fecha_inicio');
    const fechaFinal = document.getElementById('rental-fecha_final');
    
    // ==========================================
    // CÁLCULO DE PRECIO TOTAL
    // ==========================================
    
    const cantidadDias = document.getElementById('rental-cantidad_dias');
    const precioPorDia = document.getElementById('rental-precio_por_dia');
    const totalPreview = document.getElementById('total-preview');
    
    function calcularTotal() {
        const precio = parseColonesValue(precioPorDia ? precioPorDia.value : 0);
        let total = 0;
        
        // Verificar si es alquiler por horas (mismo día)
        const fechaIni = fechaInicio ? fechaInicio.value : '';
        const fechaFin = fechaFinal ? fechaFinal.value : '';
        // Verificar si medio día está habilitado
        const medioDiaEnabled = document.getElementById('rental-medio_dia_enabled');
        const medioDiaValor = document.getElementById('rental-medio_dia_valor');
        
        if (medioDiaEnabled && medioDiaEnabled.checked && medioDiaValor) {
            const valorMedioDia = parseFloat(medioDiaValor.value) || 0;
            // Si medio día está activo, el total es solo el valor del medio día (tarifa fija)
            total = valorMedioDia;
        } else {
            // Si no está activo medio día, calcular normalmente
            const esPorHoras = fechaIni && fechaFin && fechaIni === fechaFin;
            
            if (esPorHoras) {
                // Si es por horas, el precio total es igual al precio por día (fijo)
                total = precio;
            } else {
                // Si es por días, calcular normalmente: cantidad_dias × precio_por_dia
                const dias = parseFloat(cantidadDias.value) || 0;
                total = dias * precio;
            }
        }
        
        if (total > 0) {
            totalPreview.value = formatColonesNumber(total);
        } else {
            totalPreview.value = '';
        }
    }
    
    if (cantidadDias && precioPorDia && totalPreview) {
        cantidadDias.addEventListener('input', calcularTotal);
        if (fechaInicio) {
            fechaInicio.addEventListener('change', calcularTotal);
        }
        if (fechaFinal) {
            fechaFinal.addEventListener('change', calcularTotal);
        }
        
        // Agregar listeners para medio día
        const medioDiaEnabled = document.getElementById('rental-medio_dia_enabled');
        const medioDiaValor = document.getElementById('rental-medio_dia_valor');
        const medioDiaValorField = document.getElementById('medio-dia-valor-field');
        if (medioDiaEnabled) {
            // Inicializar estado al cargar
            if (medioDiaValorField) {
                medioDiaValorField.style.display = medioDiaEnabled.checked ? 'block' : 'none';
            }
            
            medioDiaEnabled.addEventListener('change', function() {
                if (medioDiaValorField) {
                    medioDiaValorField.style.display = this.checked ? 'block' : 'none';
                }
                calcularTotal();
            });
        }
        if (medioDiaValor) {
            medioDiaValor.addEventListener('input', calcularTotal);
        }
        
        calcularTotal();
    }
    
    // ==========================================
    // CÁLCULO BIDIRECCIONAL: FECHA FINAL ↔ CANTIDAD DE DÍAS
    // ==========================================
    
    // Bandera para prevenir loops infinitos
    let calculando = false;
    
    /**
     * Calcula fecha_final basándose en fecha_inicio + cantidad_dias
     */
    function calcularFechaFinalDesdeDias() {
        if (calculando) return;
        calculando = true;
        
        const fechaIni = fechaInicio.value;
        const dias = parseInt(cantidadDias.value) || 0;
        
        if (fechaIni && dias > 0) {
            // Parsear fecha manualmente para evitar problemas de zona horaria
            const [year, month, day] = fechaIni.split('-').map(Number);
            const fecha = new Date(year, month - 1, day + dias); // month es 0-indexed, day + dias
            const fechaFormateada = fecha.getFullYear() + '-' + 
                String(fecha.getMonth() + 1).padStart(2, '0') + '-' + 
                String(fecha.getDate()).padStart(2, '0');
            fechaFinal.value = fechaFormateada;
        } else if (!fechaIni || dias <= 0) {
            fechaFinal.value = '';
        }
        
        calculando = false;
    }
    
    /**
     * Calcula cantidad_dias o horas basándose en fecha_final - fecha_inicio
     * Si es el mismo día, calcula horas entre hora_inicio y hora_final
     */
    function calcularDiasDesdeFechas() {
        if (calculando) return;
        calculando = true;
        
        const fechaIni = fechaInicio.value;
        const fechaFin = fechaFinal.value;
        
        if (fechaIni && fechaFin) {
            const inicio = new Date(fechaIni);
            const fin = new Date(fechaFin);
            
            // Validar que fecha_final >= fecha_inicio (permitir igual para alquileres por horas)
            if (fin < inicio) {
                alert('La fecha final no puede ser anterior a la fecha de inicio');
                fechaFinal.value = '';
                calculando = false;
                return;
            }
            
            // Si fecha_inicio = fecha_final (mismo día): calcular horas
            if (fechaIni === fechaFin) {
                // Obtener horas desde campos ocultos
                const horaInicioOculta = document.getElementById('rental-hora_inicio');
                const horaFinalOculta = document.getElementById('rental-hora_final');
                
                if (horaInicioOculta && horaFinalOculta && horaInicioOculta.value && horaFinalOculta.value) {
                    const horaIni = horaInicioOculta.value.split(':');
                    const horaFin = horaFinalOculta.value.split(':');
                    
                    const horaIniDate = new Date(`${fechaIni}T${horaInicioOculta.value}:00`);
                    const horaFinDate = new Date(`${fechaFin}T${horaFinalOculta.value}:00`);
                    
                    // Validar que hora_final > hora_inicio
                    if (horaFinDate <= horaIniDate) {
                        alert('La hora final debe ser posterior a la hora de inicio cuando es el mismo día');
                        calculando = false;
                        return;
                    }
                    
                    // Si es alquiler por horas en el mismo día, cantidad_dias = 1 (no las horas totales)
                    // El cálculo del precio se maneja en calcularTotal() según si medio_dia está activo
                    cantidadDias.value = 1;
                    cantidadDias.min = 1;
                } else {
                    // Si no hay horas, establecer 1 hora por defecto
                    cantidadDias.value = 1;
                    cantidadDias.min = 1;
                }
            } else {
                // Alquiler por días - calcular días correctamente
                // Parsear fechas manualmente para evitar problemas de zona horaria
                const [yearIni, monthIni, dayIni] = fechaIni.split('-').map(Number);
                const [yearFin, monthFin, dayFin] = fechaFin.split('-').map(Number);
                const fechaInicioParsed = new Date(yearIni, monthIni - 1, dayIni);
                const fechaFinalParsed = new Date(yearFin, monthFin - 1, dayFin);
                
                const diffTime = fechaFinalParsed - fechaInicioParsed;
                const diasCalculados = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                
                // Si es el mismo día, usar 1 día. Si hay diferencia, usar la diferencia exacta
                cantidadDias.value = diasCalculados >= 0 ? (diasCalculados === 0 ? 1 : diasCalculados) : 1;
                cantidadDias.min = 1;
            }
        }
        
        calculando = false;
    }
    
    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split('T')[0];
    if (fechaInicio) {
        fechaInicio.min = today;
        if (!fechaInicio.value) {
            fechaInicio.value = today;
        }
        fechaInicio.addEventListener('change', function() {
            calcularFechaFinalDesdeDias();
        });
    }
    
    if (fechaFinal) {
        fechaFinal.min = today;
        fechaFinal.addEventListener('change', function() {
            calcularDiasDesdeFechas();
            actualizarLabelCantidad();
        });
    }
    
    if (cantidadDias) {
        cantidadDias.addEventListener('input', function() {
            // Solo calcular fecha_final si NO es alquiler por horas (mismo día)
            const fechaIni = fechaInicio.value;
            const fechaFin = fechaFinal.value;
            if (fechaIni && fechaFin && fechaIni !== fechaFin) {
                calcularFechaFinalDesdeDias();
            }
            actualizarLabelCantidad();
        });
    }
    
    /**
     * Actualiza el label de cantidad_dias para mostrar "horas" o "días" según corresponda
     * También actualiza el texto de ayuda del cálculo de precio
     */
    function actualizarLabelCantidad() {
        const fechaIni = fechaInicio.value;
        const fechaFin = fechaFinal.value;
        const labelCantidad = document.querySelector('label[for="rental-cantidad_dias"]');
        const precioCalculoTexto = document.getElementById('precio-calculo-texto');
        const esPorHoras = fechaIni && fechaFin && fechaIni === fechaFin;
        
        if (labelCantidad) {
            if (esPorHoras) {
                labelCantidad.textContent = 'Cantidad de Horas *';
            } else {
                labelCantidad.textContent = 'Cantidad de Días *';
            }
        }
        
        if (precioCalculoTexto) {
            if (esPorHoras) {
                precioCalculoTexto.textContent = 'Precio fijo por horas (independiente de la cantidad de horas)';
            } else {
                precioCalculoTexto.textContent = 'Cantidad de días × Precio por día';
            }
        }
        
        // Recalcular precio cuando cambie el tipo de alquiler
        if (precioPorDia && precioPorDia.value) {
            calcularTotal();
        }
    }
    
    // Actualizar label cuando cambien las fechas
    if (fechaInicio) {
        fechaInicio.addEventListener('change', function() {
            actualizarLabelCantidad();
        });
    }
    
    // Actualizar label cuando cambien las horas (para recalcular en mismo día)
    const horaInicioHours = document.getElementById('hora-inicio-hours');
    const horaInicioMinutes = document.getElementById('hora-inicio-minutes');
    const horaInicioPeriod = document.getElementById('hora-inicio-period');
    const horaFinalHours = document.getElementById('hora-final-hours');
    const horaFinalMinutes = document.getElementById('hora-final-minutes');
    const horaFinalPeriod = document.getElementById('hora-final-period');
    
    ['hora-inicio-hours', 'hora-inicio-minutes', 'hora-inicio-period', 'hora-final-hours', 'hora-final-minutes', 'hora-final-period'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                // Esperar un momento para que se actualice el campo oculto
                setTimeout(function() {
                    const fechaIni = fechaInicio.value;
                    const fechaFin = fechaFinal.value;
                    if (fechaIni && fechaFin && fechaIni === fechaFin) {
                        calcularDiasDesdeFechas();
                    }
                }, 100);
            });
        }
    });
    
    // Actualizar label inicialmente
    actualizarLabelCantidad();
    
    // Calcular fecha final inicialmente si hay valores
    setTimeout(function() {
        if (fechaInicio && fechaInicio.value && cantidadDias && cantidadDias.value) {
            calcularFechaFinalDesdeDias();
        } else if (fechaInicio && fechaInicio.value && fechaFinal && fechaFinal.value) {
            calcularDiasDesdeFechas();
        }
    }, 100);

    // Asegurar valores antes de enviar el formulario (fecha_final opcional)
    const crearForm = document.querySelector('form.rental-form');
    if (crearForm) {
        crearForm.addEventListener('submit', function() {
            if (fechaInicio && fechaInicio.value && fechaFinal && !fechaFinal.value) {
                calcularFechaFinalDesdeDias();
                if (!fechaFinal.value) {
                    fechaFinal.value = fechaInicio.value; // fallback para no bloquear
                }
            }
        });
    }
    
    // ==========================================
    // MANEJO DE CORREAPARTIR FORMATO 12H
    // ==========================================
    
    /**
     * Actualiza el campo oculto fecha_correapartir combinando fecha y hora
     */
    function actualizarCorreapartirOculta() {
        const fechaInput = document.getElementById('correapartir-fecha');
        const horasSelect = document.getElementById('correapartir-hours');
        const minutosSelect = document.getElementById('correapartir-minutes');
        const periodoSelect = document.getElementById('correapartir-period');
        const campoOculto = document.getElementById('rental-fecha_correapartir');
        
        if (!fechaInput || !horasSelect || !minutosSelect || !periodoSelect || !campoOculto) {
            return;
        }
        
        const fecha = fechaInput.value;
        if (!fecha) {
            campoOculto.value = '';
            return;
        }
        
        // Convertir hora 12h a 24h
        const hora24 = convertir12hA24h(
            parseInt(horasSelect.value),
            parseInt(minutosSelect.value),
            periodoSelect.value
        );
        
        // Combinar fecha y hora: "YYYY-MM-DD HH:MM:SS"
        campoOculto.value = fecha + ' ' + hora24 + ':00';
    }
    
    /**
     * Inicializa campos de correapartir con valor existente
     */
    function inicializarCorreapartir12h() {
        const campoOculto = document.getElementById('rental-fecha_correapartir');
        if (!campoOculto || !campoOculto.value) {
            return;
        }
        
        // Parsear fecha_correapartir: "YYYY-MM-DD HH:MM:SS"
        const fechaHora = campoOculto.value;
        const [fecha, hora] = fechaHora.split(' ');
        const [horaPart, minutosPart] = hora ? hora.split(':') : ['00', '00'];
        
        // Inicializar campo de fecha
        const fechaInput = document.getElementById('correapartir-fecha');
        if (fechaInput && fecha) {
            fechaInput.value = fecha;
        }
        
        // Inicializar selectores de hora
        const hora12 = convertir24hA12h(horaPart + ':' + minutosPart);
        const horasSelect = document.getElementById('correapartir-hours');
        const minutosSelect = document.getElementById('correapartir-minutes');
        const periodoSelect = document.getElementById('correapartir-period');
        
        if (horasSelect && minutosSelect && periodoSelect) {
            horasSelect.value = hora12.hora;
            minutosSelect.value = String(hora12.minutos).padStart(2, '0');
            periodoSelect.value = hora12.periodo;
        }
    }
    
    // Mostrar/ocultar campo correapartir
    const correapartirCheckbox = document.getElementById('rental-correapartir_enabled');
    const correapartirField = document.getElementById('correapartir-datetime-field');
    
    if (correapartirCheckbox && correapartirField) {
        // Inicializar si ya está habilitado al cargar
        if (correapartirCheckbox.checked) {
            correapartirField.style.display = 'block';
            inicializarCorreapartir12h();
        }
        
        correapartirCheckbox.addEventListener('change', function() {
            correapartirField.style.display = this.checked ? 'block' : 'none';
            
            if (this.checked) {
                const fechaInput = document.getElementById('correapartir-fecha');
                const fechaInicio = document.getElementById('rental-fecha_inicio');
                
                if (fechaInput && !fechaInput.value) {
                    // Si no hay valor, establecer fecha como un día antes de fecha_inicio
                    if (fechaInicio && fechaInicio.value) {
                        const fechaInicioDate = new Date(fechaInicio.value);
                        fechaInicioDate.setDate(fechaInicioDate.getDate() - 1);
                        fechaInput.value = fechaInicioDate.toISOString().split('T')[0];
                        fechaInput.min = new Date().toISOString().split('T')[0];
                        
                        // Actualizar campo oculto con la nueva fecha (sin hora aún)
                        actualizarCorreapartirOculta();
                    } else {
                        // Si no hay fecha_inicio, establecer fecha mínima como hoy
                        fechaInput.min = new Date().toISOString().split('T')[0];
                    }
                }
                inicializarCorreapartir12h();
                // Hacer requerida la fecha de correapartir cuando está habilitado
                if (fechaInput) {
                    fechaInput.setAttribute('required', 'required');
                    fechaInput.oninvalid = function(){ this.setCustomValidity('Selecciona una fecha de correapartir o desactiva la opción.'); };
                    fechaInput.oninput = function(){ this.setCustomValidity(''); };
                }
            } else {
                // Limpiar campo oculto si se deshabilita
                const campoOculto = document.getElementById('rental-fecha_correapartir');
                if (campoOculto) {
                    campoOculto.value = '';
                }
                const fechaInput = document.getElementById('correapartir-fecha');
                if (fechaInput) {
                    fechaInput.removeAttribute('required');
                }
            }
        });
        
        // También actualizar fecha correapartir cuando cambia fecha_inicio (si correapartir está habilitado)
        if (fechaInicio) {
            fechaInicio.addEventListener('change', function() {
                if (correapartirCheckbox && correapartirCheckbox.checked) {
                    const fechaInput = document.getElementById('correapartir-fecha');
                    if (fechaInput && this.value) {
                        const fechaInicioDate = new Date(this.value);
                        fechaInicioDate.setDate(fechaInicioDate.getDate() - 1);
                        fechaInput.value = fechaInicioDate.toISOString().split('T')[0];
                        actualizarCorreapartirOculta();
                    }
                }
            });
        }
    }
    
    // Event listeners para actualizar campo oculto cuando cambian selectores
    ['correapartir-fecha', 'correapartir-hours', 'correapartir-minutes', 'correapartir-period'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                actualizarCorreapartirOculta();
            });
        }
    });
    
    // ==========================================
    // VALIDACIÓN DEL FORMULARIO - INFORME TOTAL
    // ==========================================
    
    function validarFormularioCompleto() {
        const errores = [];
        const advertencias = [];
        
        // 1. Validar Cliente (requerido)
        const cliente = document.getElementById('rental-client_id');
        if (!cliente || !cliente.value || cliente.value === '') {
            errores.push('Cliente: Debes seleccionar un cliente.');
        }
        
        // 2. Validar Vehículo (requerido)
        const vehiculo = document.getElementById('rental-car_id');
        if (!vehiculo || !vehiculo.value || vehiculo.value === '') {
            errores.push('Vehículo: Debes seleccionar un vehículo.');
        }
        
        // 3. Validar Fecha de inicio (requerida)
        const fechaInicio = document.getElementById('rental-fecha_inicio');
        if (!fechaInicio || !fechaInicio.value) {
            errores.push('Fecha de inicio: Debes seleccionar una fecha de inicio.');
        }
        
        // 4. Validar Hora de inicio (requerida)
        const horaInicio = document.getElementById('rental-hora_inicio');
        if (!horaInicio || !horaInicio.value) {
            errores.push('Hora de inicio: Debes seleccionar una hora de inicio.');
        }
        
        // 5. Validar Fecha final (requerida)
        const fechaFinal = document.getElementById('rental-fecha_final');
        if (!fechaFinal || !fechaFinal.value) {
            errores.push('Fecha final: Debes seleccionar una fecha final.');
        }
        
        // 6. Validar Hora final (requerida)
        const horaFinal = document.getElementById('rental-hora_final');
        if (!horaFinal || !horaFinal.value) {
            errores.push('Hora final: Debes seleccionar una hora final.');
        }
        
        // 7. Validar Cantidad de días (requerido)
        const cantidadDias = document.getElementById('rental-cantidad_dias');
        if (!cantidadDias || !cantidadDias.value || parseInt(cantidadDias.value) <= 0) {
            errores.push('Cantidad de días: Debes ingresar una cantidad de días válida (mayor a 0).');
        }
        
        // 8. Validar Precio por día (requerido)
        syncPrecioPorDiaFromDisplay();
        const precioPorDia = document.getElementById('rental-precio_por_dia');
        if (!precioPorDia || !precioPorDia.value || parseColonesValue(precioPorDia.value) <= 0) {
            errores.push('Precio por día: Debes ingresar un precio por día válido (mayor a 0).');
        }
        
        // 9. Validar Lugar de entrega
        const lugarEntrega = document.getElementById('rental-lugar_entrega');
        if (!lugarEntrega || !lugarEntrega.value || lugarEntrega.value.trim() === '') {
            advertencias.push('Lugar de entrega: Es recomendable especificar el lugar de entrega.');
        }
        
        // 10. Validar Lugar de retiro
        const lugarRetiro = document.getElementById('rental-lugar_retiro');
        if (!lugarRetiro || !lugarRetiro.value || lugarRetiro.value.trim() === '') {
            advertencias.push('Lugar de retiro: Es recomendable especificar el lugar de retiro.');
        }
        
        // 11. Validar fechas: fecha final debe ser mayor o igual a fecha inicio
        if (fechaInicio && fechaInicio.value && fechaFinal && fechaFinal.value) {
            const fechaIni = new Date(fechaInicio.value);
            const fechaFin = new Date(fechaFinal.value);
            if (fechaFin < fechaIni) {
                errores.push('Fechas: La fecha final no puede ser anterior a la fecha de inicio.');
            }
        }
        
        // 12. Validar corre apartir (OPCIONAL - no genera error)
        const correApartirEnabled = document.getElementById('rental-correapartir_enabled');
        if (correApartirEnabled && correApartirEnabled.checked) {
            const fechaCorreApartir = document.getElementById('rental-fecha_correapartir');
            if (!fechaCorreApartir || !fechaCorreApartir.value) {
                advertencias.push('Corre apartir: Has habilitado corre apartir pero no has seleccionado una fecha.');
            }
        }
        
        // 13. Validar 1/2 día (OPCIONAL - no genera error)
        const medioDiaEnabled = document.getElementById('rental-medio_dia_enabled');
        if (medioDiaEnabled && medioDiaEnabled.checked) {
            const medioDiaValor = document.getElementById('rental-medio_dia_valor');
            if (!medioDiaValor || !medioDiaValor.value || parseFloat(medioDiaValor.value) <= 0) {
                advertencias.push('1/2 día: Has habilitado 1/2 día pero no has ingresado un valor válido.');
            }
        }
        
        // Actualizar el informe total
        actualizarInformeTotal(errores, advertencias);
        
        return errores.length === 0;
    }
    
    function actualizarInformeTotal(errores, advertencias) {
        const informeTotal = document.getElementById('informe-total');
        const informeMensaje = document.getElementById('informe-mensaje');
        const informeLista = document.getElementById('informe-lista');
        
        if (!informeTotal || !informeMensaje || !informeLista) {
            return;
        }
        
        // Limpiar lista anterior
        informeLista.innerHTML = '';
        
        if (errores.length === 0 && advertencias.length === 0) {
            // Todo está bien - mostrar en verde
            informeTotal.style.backgroundColor = '#d4edda';
            informeTotal.style.border = '2px solid #28a745';
            informeMensaje.innerHTML = '<span style="color: #155724; font-weight: 600;">✓ Formulario completo y válido. Puedes proceder a crear el alquiler.</span>';
            informeLista.style.display = 'none';
        } else if (errores.length > 0) {
            // Hay errores - mostrar en rojo
            informeTotal.style.backgroundColor = '#f8d7da';
            informeTotal.style.border = '2px solid #dc3545';
            informeMensaje.innerHTML = '<span style="color: #721c24; font-weight: 600;">✗ Se encontraron ' + errores.length + ' error(es) que deben corregirse:</span>';
            informeLista.style.display = 'block';
            
            errores.forEach(function(error) {
                const li = document.createElement('li');
                li.style.color = '#721c24';
                li.style.marginBottom = '5px';
                li.textContent = error;
                informeLista.appendChild(li);
            });
            
            // Agregar advertencias si las hay
            if (advertencias.length > 0) {
                const advertenciasTitle = document.createElement('li');
                advertenciasTitle.style.color = '#856404';
                advertenciasTitle.style.fontWeight = '600';
                advertenciasTitle.style.marginTop = '10px';
                advertenciasTitle.textContent = 'Advertencias:';
                informeLista.appendChild(advertenciasTitle);
                
                advertencias.forEach(function(advertencia) {
                    const li = document.createElement('li');
                    li.style.color = '#856404';
                    li.style.marginBottom = '5px';
                    li.textContent = advertencia;
                    informeLista.appendChild(li);
                });
            }
        } else {
            // Solo advertencias - mostrar en amarillo/naranja
            informeTotal.style.backgroundColor = '#fff3cd';
            informeTotal.style.border = '2px solid #ffc107';
            informeMensaje.innerHTML = '<span style="color: #856404; font-weight: 600;">⚠ Formulario válido pero con ' + advertencias.length + ' advertencia(s):</span>';
            informeLista.style.display = 'block';
            
            advertencias.forEach(function(advertencia) {
                const li = document.createElement('li');
                li.style.color = '#856404';
                li.style.marginBottom = '5px';
                li.textContent = advertencia;
                informeLista.appendChild(li);
            });
        }
    }
    
    // Agregar listeners a todos los campos del formulario para validación en tiempo real
    const camposFormulario = [
        'rental-client_id',
        'rental-car_id',
        'rental-fecha_inicio',
        'rental-hora_inicio',
        'rental-fecha_final',
        'rental-hora_final',
        'rental-cantidad_dias',
        'precio-por-dia-display',
        'rental-precio_por_dia',
        'rental-lugar_entrega',
        'rental-lugar_retiro',
        'rental-correapartir_enabled',
        'rental-fecha_correapartir',
        'rental-medio_dia_enabled',
        'rental-medio_dia_valor',
        // Selectores de hora
        'hora-inicio-hours',
        'hora-inicio-minutes',
        'hora-inicio-period',
        'hora-final-hours',
        'hora-final-minutes',
        'hora-final-period'
    ];
    
    camposFormulario.forEach(function(campoId) {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.addEventListener('change', function() {
                // Para los selectores de hora, esperar un momento para que se actualice el campo oculto
                if (campoId.includes('hora-')) {
                    setTimeout(validarFormularioCompleto, 100);
                } else {
                    validarFormularioCompleto();
                }
            });
            campo.addEventListener('input', validarFormularioCompleto);
            campo.addEventListener('blur', validarFormularioCompleto);
        }
    });
    
    // Validar al cargar la página
    setTimeout(function() {
        validarFormularioCompleto();
    }, 500);
    
    // Validar antes de enviar el formulario
    const form = document.querySelector('form.rental-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            syncPrecioPorDiaFromDisplay();
            if (!validarFormularioCompleto()) {
                e.preventDefault();
                // Scroll al informe total
                const informeTotal = document.getElementById('informe-total');
                if (informeTotal) {
                    informeTotal.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
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

.availability-status {
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    display: none;
}

.availability-status.available {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.availability-status.unavailable {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.availability-status.checking {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carSelect = document.getElementById('rental-car_id');
    const startDateInput = document.getElementById('rental-fecha_inicio');
    const availabilityStatus = document.getElementById('availability-status');
    
    let checkTimeout;
    let filterTimeout;
    
    // Función para filtrar vehículos disponibles por fecha
    function filterAvailableCars() {
        const startDate = startDateInput.value;
        const duration = document.getElementById('rental-cantidad_dias').value || 1;
        
        if (!startDate) {
            return; // No filtrar si no hay fecha
        }
        
        // Mostrar indicador de carga
        carSelect.innerHTML = '<option value="">Cargando vehículos disponibles...</option>';
        
        // Hacer petición para obtener vehículos disponibles
        fetch(`/rental/get-available-cars?start_date=${startDate}&duration=${duration}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Limpiar el select
                carSelect.innerHTML = '<option value="">Seleccionar vehículo disponible...</option>';
                
                // Agregar solo vehículos disponibles
                data.data.available_cars.forEach(car => {
                    const option = document.createElement('option');
                    option.value = car.id;
                    const empresa = car.empresa || '';
                    const isMov = String(empresa).toLowerCase() === 'moviliza';
                    option.textContent = `${car.nombre} (${car.placa})${isMov ? ' · Moviliza' : ''}`;
                    option.dataset.status = car.status || '';
                    option.dataset.empresa = empresa;
                    option.dataset.skipPriority = car.skip_priority ? '1' : '0';
                    carSelect.appendChild(option);
                });
                
                // Mostrar mensaje si no hay vehículos disponibles
                if (data.data.available_cars.length === 0) {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "❌ No hay vehículos disponibles para esta fecha";
                    option.disabled = true;
                    carSelect.appendChild(option);
                }
                
                // Mostrar contador de vehículos disponibles
                const carLabel = document.querySelector('label[for="rental-car_id"]');
                if (carLabel) {
                    const count = data.data.available_cars.length;
                    carLabel.innerHTML = `Vehículo <span class="badge bg-success">${count} disponible${count !== 1 ? 's' : ''}</span>`;
                }
                
            } else {
                console.error('Error al filtrar vehículos:', data.message);
                carSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            carSelect.innerHTML = '<option value="">Error al cargar vehículos</option>';
        });
    }
    
    function checkAvailability() {
        const carId = carSelect.value;
        const startDate = startDateInput.value;
        const endDate = document.getElementById('rental-fecha_final').value;
        
        if (!carId || !startDate || !endDate) {
            availabilityStatus.style.display = 'none';
            return;
        }
        
        // Mostrar estado de verificación
        availabilityStatus.className = 'availability-status checking';
        availabilityStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando disponibilidad...';
        availabilityStatus.style.display = 'block';
        
        // Enviar solicitud AJAX
        fetch('/rental/check-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: new URLSearchParams({
                'car_id': carId,
                'start_date': startDate + ' 00:00:00',
                'end_date': endDate + ' 23:59:59'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.available) {
                    availabilityStatus.className = 'availability-status available';
                    availabilityStatus.innerHTML = '<i class="fas fa-check-circle"></i> Vehículo disponible en las fechas seleccionadas.';
                } else {
                    availabilityStatus.className = 'availability-status unavailable';
                    let message = '<i class="fas fa-times-circle"></i> ' + data.message;
                    
                    if (data.next_available) {
                        const nextStart = new Date(data.next_available.start_date).toLocaleDateString('es-ES');
                        const nextEnd = new Date(data.next_available.end_date).toLocaleDateString('es-ES');
                        message += '<br><small>Próxima disponibilidad: ' + nextStart + ' - ' + nextEnd + '</small>';
                    }
                    
                    availabilityStatus.innerHTML = message;
                }
            } else {
                availabilityStatus.className = 'availability-status unavailable';
                availabilityStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al verificar disponibilidad: ' + data.message;
            }
        })
        .catch(error => {
            availabilityStatus.className = 'availability-status unavailable';
            availabilityStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al verificar disponibilidad.';
            console.error('Error:', error);
        });
    }
    
    // Event listeners
    carSelect.addEventListener('change', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    startDateInput.addEventListener('change', function() {
        // Filtrar vehículos disponibles para la nueva fecha
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterAvailableCars, 300);
        
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    // También filtrar cuando cambie la cantidad de días o fecha final
    document.getElementById('rental-cantidad_dias').addEventListener('change', function() {
        if (startDateInput.value) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(filterAvailableCars, 300);
        }
        
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    document.getElementById('rental-fecha_final').addEventListener('change', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split('T')[0];
    startDateInput.min = today;
    document.getElementById('rental-fecha_final').min = today;
    
    // Auto-filtrar y verificar al cargar la página si hay valores
    setTimeout(() => {
        if (startDateInput.value) {
            filterAvailableCars();
        }
        setTimeout(checkAvailability, 1000);
    }, 500);

    // Manejar campo "Otro" en ejecutivo
    const ejecutivoSelect = document.getElementById('ejecutivo-select');
    const ejecutivoOtroField = document.getElementById('ejecutivo-otro-field');
    
    if (ejecutivoSelect && ejecutivoOtroField) {
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
    .rental-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .card {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }
    .form-control:focus {
        border-color: #22487a;
        box-shadow: 0 0 0 0.2rem rgba(34, 72, 122, 0.25);
    }
    .btn-success {
        background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(34, 72, 122, 0.4);
    }
    .btn-secondary {
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    
    /* Estilos para el estado de disponibilidad */
    .availability-status {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .availability-status.available {
        background-color: #d4edda !important;
        border: 2px solid #28a745;
        color: #155724;
    }
    .availability-status.unavailable {
        background-color: #f8d7da !important;
        border: 2px solid #dc3545;
        color: #721c24;
    }
    .availability-status.checking {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107;
        color: #856404;
    }
    .availability-status .availability-icon {
        font-size: 18px;
        margin-right: 8px;
    }
    .availability-status .availability-text {
        flex: 1;
    }
    .availability-status .availability-suggestion {
        font-size: 12px;
        margin-top: 4px;
        font-style: italic;
    }
    
    /* Destacar la sección de fechas */
    .card-header.gradient-green {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }
    .card-header.gradient-green small {
        opacity: 0.9;
        font-size: 0.85em;
    }
    
    /* Estilos para el contador de vehículos */
    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
        border-radius: 0.375rem;
    }
    
    .form-label .badge {
        margin-left: 8px;
        vertical-align: middle;
    }
    
    /* Estilos para el dropdown de vehículos */
    .form-select option:disabled {
        color: #6c757d;
        font-style: italic;
    }
    
    .form-select option[data-status="alquilado"]:not([data-empresa="Moviliza" i]) {
        color: #dc3545;
    }
    
    .form-select option[data-status="disponible"]:not([data-empresa="Moviliza" i]) {
        color: #28a745;
        font-weight: 700;
    }

    /* Vehículos Moviliza: gris (no rojo por estado alquilado/mantenimiento) */
    #rental-car_id option[data-empresa="Moviliza" i],
    .form-select option[data-empresa="Moviliza" i] {
        color: #6c757d !important;
        font-weight: 500;
    }
    
    /* Estilos para el texto de ayuda de fecha final */
    #fecha-final-help {
        font-size: 0.875em;
        margin-top: 4px;
        padding: 6px 8px;
        background-color: #e3f2fd;
        border-radius: 4px;
        border-left: 3px solid #2196f3;
    }
    
    #fecha-final-help strong {
        color: #1976d2;
    }
    
    /* Estilos para campos de hora 12h */
    .hora-12h-wrapper .form-select {
        font-size: 0.9rem;
    }
    
    .form-group label.fw-bold {
        margin-bottom: 0.5rem;
    }
</style>

<script>
(function () {
    var RULE_ENABLED = <?= $movilizaPriorityEnabled ? 'true' : 'false' ?>;
    if (!RULE_ENABLED) return;

    var form = document.querySelector('form.rental-form');
    var carSelect = document.getElementById('rental-car_id');
    var hiddenJust = document.getElementById('moviliza-justificacion');
    var inputJust = document.getElementById('moviliza-justificacion-input');
    var countEl = document.getElementById('moviliza-justificacion-count');
    var hintEl = document.getElementById('moviliza-justificacion-hint');
    var confirmBtn = document.getElementById('moviliza-justificacion-confirm');
    var factoMsg = document.getElementById('moviliza-facto-count-msg');
    var modalEl = document.getElementById('movilizaPriorityModal');
    if (!form || !carSelect || !hiddenJust || !inputJust || !modalEl) return;

    var allowSubmit = false;
    var MIN_CHARS = 40;

    function isMovilizaEmpresa(empresa) {
        return String(empresa || '').toLowerCase() === 'moviliza';
    }

    function countFactoAvailableInSelect() {
        var n = 0;
        Array.prototype.forEach.call(carSelect.options, function (opt) {
            if (!opt.value) return;
            if (isMovilizaEmpresa(opt.dataset.empresa)) return;
            if (String(opt.dataset.skipPriority || '0') === '1') return;
            n++;
        });
        return n;
    }

    function selectedIsMoviliza() {
        var opt = carSelect.options[carSelect.selectedIndex];
        return !!(opt && opt.value && isMovilizaEmpresa(opt.dataset.empresa));
    }

    function syncCounter() {
        var len = (inputJust.value || '').trim().length;
        if (countEl) countEl.textContent = String(len);
        var ok = len >= MIN_CHARS;
        if (confirmBtn) confirmBtn.disabled = !ok;
        if (hintEl) hintEl.style.display = ok || len === 0 ? 'none' : 'inline';
        return ok;
    }

    inputJust.addEventListener('input', syncCounter);

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!syncCounter()) return;
            hiddenJust.value = (inputJust.value || '').trim();
            allowSubmit = true;
            if (window.bootstrap && window.bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                inst.hide();
            }
            // Re-disparar submit después de cerrar el modal
            setTimeout(function () {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }, 200);
        });
    }

    form.addEventListener('submit', function (e) {
        if (allowSubmit) {
            allowSubmit = false;
            return;
        }
        if (!selectedIsMoviliza()) return;

        var existing = (hiddenJust.value || '').trim();
        if (existing.length >= MIN_CHARS) return;

        var factoCount = countFactoAvailableInSelect();
        if (factoCount <= 0) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        if (factoMsg) {
            factoMsg.textContent = 'Facto Rent a Car disponibles en el listado: ' + factoCount + '.';
        }
        inputJust.value = existing;
        syncCounter();

        if (window.bootstrap && window.bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            modalEl.style.display = 'block';
        }
        return false;
    }, true); // capture: antes que otros listeners de validación
})();
</script>
