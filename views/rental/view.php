<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */

$this->title = 'Alquiler #' . $model->rental_id;
$this->params['breadcrumbs'][] = ['label' => 'Alquileres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="rental-view">

    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">
            receipt_long
        </span>
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0" style="color: white !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            info
                        </span>
                        Detalles del Alquiler
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'rental_id',
                            [
                                'attribute' => 'client_id',
                                'value' => $model->client ? $model->client->full_name . ' (' . $model->client->cedula_fisica . ')' : 'N/A',
                            ],
                            [
                                'attribute' => 'car_id',
                                'value' => $model->car ? $model->car->nombre . ' (' . $model->car->placa . ')' : 'N/A',
                            ],
                            'fecha_inicio',
                            'hora_inicio',
                            'fecha_final',
                            'hora_final',
                            'cantidad_dias',
                            [
                                'attribute' => 'precio_por_dia',
                                'value' => '₡' . number_format($model->precio_por_dia, 2),
                            ],
                            [
                                'attribute' => 'total_precio',
                                'value' => '₡' . number_format($model->total_precio ?? 0, 2),
                                'format' => 'raw',
                                'value' => function($model) {
                                    $total = number_format($model->total_precio ?? 0, 2);
                                    return '<strong style="color: #28a745; font-size: 18px;">₡' . $total . '</strong>';
                                }
                            ],
                            [
                                'attribute' => 'estado_pago',
                                'value' => function($model) {
                                    $badges = [
                                        'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
                                        'pagado' => '<span class="badge bg-success">Pagado</span>',
                                        'reservado' => '<span class="badge bg-info">Reservado</span>',
                                        'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
                                    ];
                                    return $badges[$model->estado_pago] ?? '<span class="badge bg-secondary">' . $model->estado_pago . '</span>';
                                },
                                'format' => 'raw',
                            ],
                            'lugar_entrega',
                            'lugar_retiro',
                            [
                                'attribute' => 'correapartir_enabled',
                                'value' => $model->correapartir_enabled ? 'Sí' : 'No',
                            ],
                            'fecha_correapartir',
                            'comprobante_pago',
                            'created_at',
                            'updated_at',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0" style="color: white !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            settings
                        </span>
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">edit</span>Editar', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary btn-lg',
                            'style' => 'background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); border: none;'
                        ]) ?>

                        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">delete</span>Cancelar Alquiler', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-lg',
                            'data' => [
                                'confirm' => '¿Estás seguro de que quieres cancelar este alquiler?',
                                'method' => 'post',
                            ],
                        ]) ?>

                        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">arrow_back</span>Volver', ['index'], [
                            'class' => 'btn btn-secondary btn-lg'
                        ]) ?>
                    </div>
                </div>
            </div>

            <?php if ($model->condiciones_especiales || $model->choferes_autorizados): ?>
            <div class="card mt-3">
                <div class="card-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: white;">
                    <h5 class="card-title mb-0" style="color: white !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px; color: white;">
                            notes
                        </span>
                        Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($model->condiciones_especiales): ?>
                    <div class="mb-3">
                        <h6><strong>Condiciones Especiales:</strong></h6>
                        <p class="text-muted"><?= Html::encode($model->condiciones_especiales) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($model->choferes_autorizados): ?>
                    <div class="mb-3">
                        <h6><strong>Choferes Autorizados:</strong></h6>
                        <p class="text-muted"><?= Html::encode($model->choferes_autorizados) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Spinner de descarga PDF -->
    <div id="pdfDownloadOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 40px; border-radius: 12px; text-align: center;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Cargando...</span>
            </div>
            <p style="margin-top: 20px; font-size: 18px; color: #333;">Generando y descargando PDF...</p>
        </div>
    </div>

</div>

<?php
// Descargar PDF automáticamente si viene de crear orden
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $this->registerJs('
    $(document).ready(function() {
        var rentalId = ' . $model->id . ';
        
        // Mostrar spinner
        $("#pdfDownloadOverlay").show();
        
        // Esperar un poco para que se genere el PDF
        setTimeout(function() {
            // Intentar descargar el PDF
            var downloadUrl = "/pdf/download-rental?id=" + rentalId;
            window.location.href = downloadUrl;
            
            // Ocultar spinner después de 2 segundos
            setTimeout(function() {
                $("#pdfDownloadOverlay").hide();
            }, 2000);
        }, 1000);
    });
    ');
}
?>

<style>
.rental-view .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.rental-view .btn-lg {
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 8px;
}

.rental-view .btn:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.rental-view .detail-view th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.rental-view .detail-view td {
    vertical-align: middle;
}
</style>
