<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Client $model */
/** @var yii\widgets\ActiveForm $form */

// Registrar el JavaScript y CSS externos
$this->registerJsFile('/js/client-form.js', ['depends' => [yii\web\JqueryAsset::class]]);
$this->registerCssFile('/css/client-form.css');
?>

<div class="client-form">

    <?php $form = ActiveForm::begin([
        'id' => 'client-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "<div class='row mb-3'><div class='col-sm-3'>{label}</div><div class='col-sm-9'>{input}{error}</div></div>",
            'labelOptions' => ['class' => 'form-label'],
            'inputOptions' => ['class' => 'form-control'],
        ],
    ]); ?>

    <!-- Sección Personal -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">person</span>
                Información Personal
            </h5>
        </div>
        <div class="card-body">
            <!-- Cédula Física (PRIMER CAMPO) -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">badge</span>
                        Cédula Física *
                    </label>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'cedula_fisica', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Ej: 112610049',
                            'id' => 'cedula-input',
                            'required' => true
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-3">
                    <button type="button" class="btn btn-outline-primary" onclick="consultarHacienda()" id="consultar-btn">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">account_balance</span>
                        Consultar Hacienda
                    </button>
                </div>
            </div>

            <!-- Nombre Completo -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">person</span>
                        Nombre Completo *
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'full_name', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Ej: RONALD ALBERTO ROJAS CASTRO',
                            'id' => 'nombre-input',
                            'required' => true,
                            'style' => 'text-transform: uppercase;'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Email -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">email</span>
                        Email
                    </label>
                </div>
                <div class="col-sm-9">
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
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">phone</span>
                        WhatsApp
                    </label>
                </div>
                <div class="col-sm-9">
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
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">location_on</span>
                        Dirección
                    </label>
                </div>
                <div class="col-sm-9">
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
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">drive_eta</span>
                        Licencias de Choferes
                    </label>
                </div>
                <div class="col-sm-9">
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
        </div>
    </div>

    <!-- Sección Tributaria -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">account_balance</span>
                Información Tributaria (Hacienda)
            </h5>
        </div>
        <div class="card-body">
            <!-- Tipo de Identificación -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">fingerprint</span>
                        Tipo de Identificación
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'tipo_identificacion', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'readonly' => true,
                            'id' => 'tipo-identificacion-input'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Situación Tributaria -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">verified</span>
                        Situación Tributaria
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'situacion_tributaria', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'readonly' => true,
                            'id' => 'situacion-tributaria-input'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Régimen Tributario -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">business</span>
                        Régimen Tributario
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'regimen_tributario', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'readonly' => true,
                            'id' => 'regimen-tributario-input'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Actividad Económica - Código -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">code</span>
                        Código Actividad Económica
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'actividad_economica_codigo', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'readonly' => true,
                            'id' => 'actividad-codigo-input'
                        ]
                    ])->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <!-- Actividad Económica - Descripción -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">description</span>
                        Descripción Actividad Económica
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'actividad_economica_descripcion', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'readonly' => true,
                            'id' => 'actividad-descripcion-input',
                            'rows' => 2
                        ]
                    ])->textarea() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Configuración -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">settings</span>
                Configuración del Cliente
            </h5>
        </div>
        <div class="card-body">
            <!-- Estado -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">toggle_on</span>
                        Estado
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'status', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control'
                        ]
                    ])->dropDownList([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo'
                    ], ['prompt' => 'Seleccionar estado']) ?>
                </div>
            </div>

            <!-- Es Aliado -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">handshake</span>
                        Es Aliado
                    </label>
                </div>
                <div class="col-sm-9">
                    <div class="form-check">
                        <?= $form->field($model, 'es_aliado', [
                            'template' => '{input}{label}{error}',
                            'inputOptions' => [
                                'class' => 'form-check-input'
                            ]
                        ])->checkbox(['label' => 'Marcar si es aliado de la empresa']) ?>
                    </div>
                </div>
            </div>

            <!-- Es Cliente Facto -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">star</span>
                        Es Cliente Facto
                    </label>
                </div>
                <div class="col-sm-9">
                    <div class="form-check">
                        <?= $form->field($model, 'es_cliente_facto', [
                            'template' => '{input}{label}{error}',
                            'inputOptions' => [
                                'class' => 'form-check-input',
                                'id' => 'cliente-facto'
                            ]
                        ])->checkbox(['label' => 'Marcar si es cliente de Facto Rent a Car']) ?>
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <div class="row mb-3">
                <div class="col-sm-3">
                    <label class="form-label">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px; color: #3fa9f5;">note</span>
                        Notas
                    </label>
                </div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'notes', [
                        'template' => '{input}{error}',
                        'inputOptions' => [
                            'class' => 'form-control',
                            'placeholder' => 'Notas adicionales sobre el cliente',
                            'rows' => 3
                        ]
                    ])->textarea() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de Hacienda -->
    <div id="hacienda-loading" class="alert alert-info" style="display: none;">
        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 8px;">sync</span>
        Consultando información en Hacienda...
    </div>

    <div id="hacienda-result" class="alert alert-success" style="display: none;">
        <h6><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">verified</span>Información Obtenida</h6>
        <div id="hacienda-info"></div>
    </div>

    <div id="hacienda-error" class="alert alert-warning" style="display: none;">
        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 8px;">warning</span>
        No se encontró información para esta cédula en Hacienda
    </div>

    <!-- Botones de Acción -->
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="d-flex gap-2">
                <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>Guardar Cliente', [
                    'class' => 'btn btn-primary',
                    'style' => 'background: linear-gradient(135deg, #3fa9f5 0%, #22487a 100%); border: none;'
                ]) ?>
                
                <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">clear</span>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<!-- Modal para cédula duplicada -->
<div class="modal fade" id="cedulaDuplicateModal" tabindex="-1" aria-labelledby="cedulaDuplicateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white;">
                <h5 class="modal-title" id="cedulaDuplicateModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px;">warning</span>
                    Cédula Duplicada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">info</span>
                    La cédula <strong id="duplicate-cedula"></strong> ya está registrada en el sistema.
                </div>
                <p><strong>¿Qué deseas hacer?</strong></p>
                <div class="row">
                    <div class="col-12">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-3 text-info">search</span>
                                    <div>
                                        <strong>Buscar el cliente existente</strong>
                                        <br><small class="text-muted">Para ver o editar sus datos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-3 text-warning">delete</span>
                                    <div>
                                        <strong>Eliminar el cliente existente</strong>
                                        <br><small class="text-muted">Para poder crear uno nuevo</small>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-3 text-secondary">edit</span>
                                    <div>
                                        <strong>Usar otra cédula</strong>
                                        <br><small class="text-muted">Para continuar con el formulario actual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">close</span>
                    Cancelar
                </button>
                <button type="button" class="btn btn-info" onclick="buscarClienteExistente()">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>
                    Buscar Cliente
                </button>
                <button type="button" class="btn btn-warning" onclick="mostrarModalEliminar()">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">delete</span>
                    Eliminar Cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); color: white;">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px;">delete_forever</span>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">warning</span>
                    <strong>¡ATENCIÓN!</strong> Esta acción no se puede deshacer.
                </div>
                <p>¿Estás seguro de que deseas eliminar <strong>TODOS</strong> los clientes con la cédula <strong id="delete-cedula"></strong>?</p>
                <p class="text-muted">Se eliminarán todos los registros relacionados con esta cédula.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">close</span>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarClientePorCedula()">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">delete_forever</span>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>