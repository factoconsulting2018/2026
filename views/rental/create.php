<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$this->title = 'Crear Alquiler';
$this->params['breadcrumbs'][] = ['label' => 'Alquileres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                        <small class="float-end">Verificar disponibilidad del vehículo</small>
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
                                'required' => true,
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
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            location_on
                        </span>
                        Ubicaciones
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
                        'reservado' => 'Reservado'
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
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">
                            notes
                        </span>
                        Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'condiciones_especiales')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Condiciones especiales del alquiler...'
                    ]) ?>

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

                    <?php if ($model->correapartir_enabled): ?>
                        <?= $form->field($model, 'fecha_correapartir')->input('datetime-local') ?>
                    <?php endif; ?>
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
    const totalPreview = document.getElementById('total-preview');
    
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
    
    if (cantidadDias && precioPorDia && totalPreview) {
        cantidadDias.addEventListener('input', calcularTotal);
        precioPorDia.addEventListener('input', calcularTotal);
        calcularTotal();
    }
    
    // ==========================================
    // CÁLCULO BIDIRECCIONAL: FECHA FINAL ↔ CANTIDAD DE DÍAS
    // ==========================================
    
    const fechaInicio = document.getElementById('rental-fecha_inicio');
    const fechaFinal = document.getElementById('rental-fecha_final');
    
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
            const fecha = new Date(fechaIni);
            fecha.setDate(fecha.getDate() + dias - 1); // -1 porque si inicio y fin son el mismo día = 1 día
            const fechaFormateada = fecha.toISOString().split('T')[0];
            fechaFinal.value = fechaFormateada;
        } else if (!fechaIni || dias <= 0) {
            fechaFinal.value = '';
        }
        
        calculando = false;
    }
    
    /**
     * Calcula cantidad_dias basándose en fecha_final - fecha_inicio
     */
    function calcularDiasDesdeFechas() {
        if (calculando) return;
        calculando = true;
        
        const fechaIni = fechaInicio.value;
        const fechaFin = fechaFinal.value;
        
        if (fechaIni && fechaFin) {
            const inicio = new Date(fechaIni);
            const fin = new Date(fechaFin);
            
            // Validar que fecha_final >= fecha_inicio
            if (fin < inicio) {
                alert('La fecha final no puede ser anterior a la fecha de inicio');
                fechaFinal.value = '';
                calculando = false;
                return;
            }
            
            const diffTime = fin - inicio;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 porque incluye ambos días
            cantidadDias.value = diffDays > 0 ? diffDays : 1;
            cantidadDias.min = 1;
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
        });
    }
    
    if (cantidadDias) {
        cantidadDias.addEventListener('input', function() {
            calcularFechaFinalDesdeDias();
        });
    }
    
    // Calcular fecha final inicialmente si hay valores
    setTimeout(function() {
        if (fechaInicio && fechaInicio.value && cantidadDias && cantidadDias.value) {
            calcularFechaFinalDesdeDias();
        } else if (fechaInicio && fechaInicio.value && fechaFinal && fechaFinal.value) {
            calcularDiasDesdeFechas();
        }
    }, 100);
    
    // ==========================================
    // MOSTRAR/OCULTAR CAMPO CORREAPARTIR
    // ==========================================
    
    const correapartirCheckbox = document.getElementById('rental-correapartir_enabled');
    const correapartirField = document.querySelector('input[name="Rental[fecha_correapartir]"]');
    
    if (correapartirCheckbox && correapartirField) {
        correapartirCheckbox.addEventListener('change', function() {
            const fieldContainer = correapartirField.closest('.form-group');
            if (fieldContainer) {
                fieldContainer.style.display = this.checked ? 'block' : 'none';
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
                    option.textContent = `${car.nombre} (${car.placa})`;
                    option.dataset.status = car.status;
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
    
    .form-select option[data-status="alquilado"] {
        color: #dc3545;
    }
    
    .form-select option[data-status="disponible"] {
        color: #28a745;
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
