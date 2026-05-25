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

                    <div class="form-check mt-3 mb-3">
                        <?= Html::activeCheckbox($model, 'correapartir_enabled', [
                            'class' => 'form-check-input',
                            'id' => 'rental-correapartir_enabled',
                            'label' => 'Habilitar Correapartir',
                            'labelOptions' => ['class' => 'form-check-label']
                        ]) ?>
                    </div>

                    <div class="form-group mb-3" id="correapartir-field" style="<?= $model->correapartir_enabled ? '' : 'display: none;' ?>">
                        <label class="form-label fw-bold" style="color: #FF6600;">Fecha y Hora de Correapartir</label>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label" style="color: #FF6600;">Fecha</label>
                                <input type="date" class="form-control" id="correapartir-fecha">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label" style="color: #FF6600; font-weight: bold;">Hora</label>
                                <select class="form-select" id="correapartir-hours">
                                    <?php for ($i = 1; $i <= 12; $i++) { echo '<option value="' . $i . '">' . $i . '</option>'; } ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label" style="color: #FF6600; font-weight: bold;">Minutos</label>
                                <select class="form-select" id="correapartir-minutes">
                                    <?php for ($i = 0; $i < 60; $i++) { $min = str_pad($i, 2, '0', STR_PAD_LEFT); echo '<option value="' . $min . '">' . $min . '</option>'; } ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label" style="color: #FF6600; font-weight: bold;">Periodo</label>
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
                    <div class="form-group mb-3" id="medio-dia-valor-field" style="display: <?= $model->medio_dia_enabled ? 'block' : 'none' ?>;">
                        <?= $form->field($model, 'medio_dia_valor')->input('number', [
                            'step' => '0.01',
                            'min' => 0,
                            'id' => 'rental-medio_dia_valor',
                            'placeholder' => '0.00'
                        ]) ?>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Precio Total</label>
                        <input type="text" id="total-preview" class="form-control" readonly 
                               placeholder="Se calculará automáticamente" 
                               style="background-color: #f8f9fa;">
                        <small class="form-text text-muted">Se calcula automáticamente: <span id="precio-calculo-texto">Cantidad de días × Precio por día</span></small>
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
                        'value' => $model->fecha_inicio ? date('Y-m-d', strtotime($model->fecha_inicio)) : '',
                        'id' => 'rental-fecha_inicio'
                    ]) ?>

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

                    <?= $form->field($model, 'fecha_final')->input('date', [
                        'id' => 'rental-fecha_final'
                    ]) ?>

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
    // CÁLCULO DE PRECIO TOTAL
    // ==========================================
    
    const cantidadDias = document.getElementById('rental-cantidad_dias');
    const precioPorDia = document.getElementById('rental-precio_por_dia');
    const fechaInicio = document.getElementById('rental-fecha_inicio');
    const fechaFinal = document.getElementById('rental-fecha_final');
    const totalPreview = document.getElementById('total-preview');
    
    function calcularTotal() {
        const precio = parseFloat(precioPorDia.value) || 0;
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
            totalPreview.value = '₡' + total.toLocaleString('es-CR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            totalPreview.value = '';
        }
    }
    
    /**
     * Actualiza el texto de ayuda del cálculo de precio
     */
    function actualizarTextoAyudaPrecio() {
        const fechaIni = fechaInicio ? fechaInicio.value : '';
        const fechaFin = fechaFinal ? fechaFinal.value : '';
        const esPorHoras = fechaIni && fechaFin && fechaIni === fechaFin;
        const precioCalculoTexto = document.getElementById('precio-calculo-texto');
        
        if (precioCalculoTexto) {
            if (esPorHoras) {
                precioCalculoTexto.textContent = 'Precio fijo por horas (independiente de la cantidad de horas)';
            } else {
                precioCalculoTexto.textContent = 'Cantidad de días × Precio por día';
            }
        }
        
        // Recalcular precio cuando cambie el tipo de alquiler
        calcularTotal();
    }
    
    if (cantidadDias && precioPorDia && totalPreview) {
        cantidadDias.addEventListener('input', calcularTotal);
        precioPorDia.addEventListener('input', calcularTotal);
        if (fechaInicio) {
            fechaInicio.addEventListener('change', actualizarTextoAyudaPrecio);
        }
        if (fechaFinal) {
            fechaFinal.addEventListener('change', actualizarTextoAyudaPrecio);
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
        
        // Calcular inicialmente
        calcularTotal();
        actualizarTextoAyudaPrecio();
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
        if (!campoOculto) {
            return;
        }
        
        const fechaInput = document.getElementById('correapartir-fecha');
        const horasSelect = document.getElementById('correapartir-hours');
        const minutosSelect = document.getElementById('correapartir-minutes');
        const periodoSelect = document.getElementById('correapartir-period');
        
        if (!fechaInput || !horasSelect || !minutosSelect || !periodoSelect) {
            return;
        }
        
        // Si hay valor en el campo oculto, parsearlo
        if (campoOculto.value && campoOculto.value.trim()) {
            // Parsear fecha_correapartir: "YYYY-MM-DD HH:MM:SS" o "YYYY-MM-DD HH:MM"
            const fechaHora = campoOculto.value.trim();
            const parts = fechaHora.split(' ');
            const fechaPart = parts[0] || '';
            let horaPart = parts[1] || '';
            
            // Si la hora tiene segundos, removerlos
            if (horaPart && horaPart.includes(':')) {
                const horaParts = horaPart.split(':');
                horaPart = horaParts[0] + ':' + horaParts[1]; // Solo HH:MM
            }
            
            // Establecer fecha
            if (fechaPart) {
                fechaInput.value = fechaPart;
            }
            
            // Establecer hora en formato 12h
            if (horaPart) {
                const hora12 = convertir24hA12h(horaPart);
                horasSelect.value = hora12.hora;
                minutosSelect.value = String(hora12.minutos).padStart(2, '0');
                periodoSelect.value = hora12.periodo;
            } else {
                // Si no hay hora, establecer valores por defecto
                horasSelect.value = 12;
                minutosSelect.value = '00';
                periodoSelect.value = 'AM';
            }
        } else {
            // Si no hay valor, establecer valores por defecto
            fechaInput.value = '';
            horasSelect.value = 12;
            minutosSelect.value = '00';
            periodoSelect.value = 'AM';
        }
    }
    
    // Mostrar/ocultar campo de correapartir
    const correapartirCheckbox = document.getElementById('rental-correapartir_enabled');
    const correapartirField = document.getElementById('correapartir-field');
    
    if (correapartirCheckbox && correapartirField) {
        // Inicializar si ya está habilitado (con pequeño delay para asegurar que los elementos estén renderizados)
        if (correapartirCheckbox.checked) {
            setTimeout(function() {
                inicializarCorreapartir12h();
            }, 100);
        }
        
        correapartirCheckbox.addEventListener('change', function() {
            correapartirField.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                setTimeout(function() {
                    inicializarCorreapartir12h();
                }, 100);
            } else {
                // Limpiar campo oculto cuando se desactiva
                const campoOculto = document.getElementById('rental-fecha_correapartir');
                if (campoOculto) {
                    campoOculto.value = '';
                }
            }
        });
        
        // Actualizar campo oculto cuando cambian los valores
        ['correapartir-fecha', 'correapartir-hours', 'correapartir-minutes', 'correapartir-period'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', function() {
                    actualizarCorreapartirOculta();
                });
            }
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
