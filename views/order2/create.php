<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Order $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$this->title = 'Crear Orden';
$this->params['breadcrumbs'][] = ['label' => 'Órdenes 2', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order2-create">

    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">
            receipt_long
        </span>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'order2-form'],
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
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'fecha_inicio')->input('date', [
                                'required' => true,
                                'value' => $model->fecha_inicio ?: date('Y-m-d')
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'hora_inicio')->input('time', [
                                'value' => $model->hora_inicio ?: '09:00'
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'cantidad_dias')->input('number', [
                                'min' => 1,
                                'value' => $model->cantidad_dias ?: 3,
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Fecha Final</label>
                                <input type="text" id="fecha-final-preview" class="form-control" readonly 
                                       placeholder="Se calculará automáticamente" 
                                       style="background-color: #f8f9fa;">
                                <small class="form-text text-muted" id="fecha-final-help">Se calcula automáticamente: Fecha de inicio + Cantidad de días</small>
                                <!-- Campo oculto para enviar fecha_final calculada -->
                                <input type="hidden" id="order-fecha_final" name="Order[fecha_final]" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'hora_final')->input('time', [
                                'value' => $model->hora_final ?: '18:00'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
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
    // Calcular automáticamente el precio total y fecha final
    const cantidadDias = document.getElementById('order-cantidad_dias');
    const precioPorDia = document.getElementById('order-precio_por_dia');
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
        const fechaFinalHelp = document.getElementById('fecha-final-help');
        
        if (fechaIni && dias > 0) {
            const fecha = new Date(fechaIni);
            fecha.setDate(fecha.getDate() + dias);
            const fechaFormateada = fecha.toISOString().split('T')[0];
            
            // Formatear fecha para mostrar de manera legible
            const fechaLegible = new Date(fechaFormateada).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            fechaFinalPreview.value = fechaFormateada;
            
            // Actualizar texto de ayuda con fecha específica
            if (fechaFinalHelp) {
                fechaFinalHelp.innerHTML = `📅 <strong>Fecha calculada:</strong> ${fechaLegible} (${fechaFormateada})`;
            }
            
            // Actualizar también el campo oculto
            const fechaFinalHidden = document.getElementById('order-fecha_final');
            if (fechaFinalHidden) {
                fechaFinalHidden.value = fechaFormateada;
            }
        } else {
            fechaFinalPreview.value = '';
            const fechaFinalHidden = document.getElementById('order-fecha_final');
            if (fechaFinalHidden) {
                fechaFinalHidden.value = '';
            }
            
            // Restaurar texto de ayuda original
            if (fechaFinalHelp) {
                fechaFinalHelp.innerHTML = 'Se calcula automáticamente: Fecha de inicio + Cantidad de días';
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
    }
    
    // Mostrar/ocultar campo de correapartir
    const correapartirCheckbox = document.getElementById('order-correapartir_enabled');
    const correapartirField = document.querySelector('input[name="Order[fecha_correapartir]"]');
    
    if (correapartirCheckbox && correapartirField) {
        correapartirCheckbox.addEventListener('change', function() {
            const fieldContainer = correapartirField.closest('.form-group');
            if (fieldContainer) {
                fieldContainer.style.display = this.checked ? 'block' : 'none';
            }
        });
    }
    
    // Establecer fecha mínima como hoy y configurar campos de fecha
    const today = new Date().toISOString().split('T')[0];
    const fechaInicio = document.getElementById('order-fecha_inicio');
    const fechaFinal = document.getElementById('order-fecha_final');
    
    // Configurar campo de fecha de inicio
    if (fechaInicio) {
        fechaInicio.min = today;
        // Forzar que la fecha de inicio sea hoy
        fechaInicio.value = today;
        
        // Debug
        console.log('Fecha de hoy:', today);
        console.log('Valor del campo fecha_inicio:', fechaInicio.value);
        alert('Fecha establecida: ' + fechaInicio.value);
        
        // Event listener para recalcular fecha final
        fechaInicio.addEventListener('change', function() {
            calcularFechaFinal();
        });
    }
    
    // Configurar campo de fecha final
    if (fechaFinal) {
        fechaFinal.min = today;
    }
    
    // Calcular fecha final inicialmente
    setTimeout(function() {
        if (fechaInicio && fechaInicio.value) {
            calcularFechaFinal();
        }
    }, 100);
});
</script>

<style>
.order2-form .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.order2-form .form-label {
    color: #333;
    margin-bottom: 8px;
}

.order2-form .form-control:focus,
.order2-form .form-select:focus {
    border-color: #3fa9f5;
    box-shadow: 0 0 0 0.2rem rgba(63, 169, 245, 0.25);
}

.order2-form .btn-lg {
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 8px;
}

.order2-form .btn:hover {
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
    const carSelect = document.getElementById('order-car_id');
    const startDateInput = document.getElementById('order-fecha_inicio');
    const endDateInput = document.getElementById('order-fecha_final');
    const availabilityStatus = document.createElement('div');
    availabilityStatus.className = 'availability-status';
    availabilityStatus.id = 'availability-status';
    
    // Insertar el div de estado después del campo de fecha final
    endDateInput.parentNode.appendChild(availabilityStatus);
    
    let checkTimeout;
    let filterTimeout;
    
    // Función para filtrar vehículos disponibles por fecha
    function filterAvailableCars() {
        const startDate = startDateInput.value;
        const duration = document.getElementById('order-cantidad_dias').value || 1;
        
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
                const carLabel = document.querySelector('label[for="order-car_id"]');
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
        const endDate = endDateInput.value;
        
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
        // Auto-calcular fecha final si no está establecida
        if (!endDateInput.value && this.value) {
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() + 1);
            endDateInput.value = startDate.toISOString().split('T')[0];
        }
        
        // Filtrar vehículos disponibles para la nueva fecha
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterAvailableCars, 300);
        
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    // También filtrar cuando cambie la cantidad de días
    document.getElementById('order-cantidad_dias').addEventListener('change', function() {
        if (startDateInput.value) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(filterAvailableCars, 300);
        }
        
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    endDateInput.addEventListener('change', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(checkAvailability, 500);
    });
    
    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split('T')[0];
    startDateInput.min = today;
    endDateInput.min = today;
    
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
    .order2-form {
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
</style>
