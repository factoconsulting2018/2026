<?php
/** @var yii\web\View $this */
/** @var app\models\Order $model */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Venta #' . $model->ticket_id;
$this->params['breadcrumbs'][] = ['label' => 'Ventas/Órdenes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="order-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📦 <?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('✏️ Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('🗑️ Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que quieres eliminar esta venta?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Detalles de la Venta</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'ticket_id',
                            'article_id',
                            [
                                'attribute' => 'client_id',
                                'value' => $model->client ? $model->client->full_name : 'N/A',
                            ],
                            [
                                'attribute' => 'sale_mode',
                                'value' => function($model) {
                                    $modes = [
                                        'retail' => '🏪 Retail',
                                        'wholesale' => '📦 Wholesale',
                                        'auction' => '🔨 Auction',
                                    ];
                                    return $modes[$model->sale_mode] ?? $model->sale_mode;
                                },
                            ],
                            'store_id',
                            'quantity',
                            [
                                'attribute' => 'unit_price',
                                'value' => '₡' . number_format($model->unit_price, 2),
                            ],
                            [
                                'attribute' => 'total_price',
                                'value' => '₡' . number_format($model->total_price, 2),
                                'format' => 'raw',
                                'contentOptions' => ['class' => 'text-success font-weight-bold'],
                            ],
                            'notes:ntext',
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">👤 Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <?php if ($model->client): ?>
                        <p><strong>Nombre:</strong> <?= Html::encode($model->client->full_name) ?></p>
                        <p><strong>Cédula:</strong> <?= Html::encode($model->client->cedula_fisica) ?></p>
                        <p><strong>WhatsApp:</strong> <?= Html::encode($model->client->whatsapp) ?></p>
                        <p><strong>Email:</strong> <?= Html::encode($model->client->email) ?></p>
                        <?= Html::a('Ver Cliente', ['/client/view', 'id' => $model->client->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php else: ?>
                        <p class="text-muted">No se encontró información del cliente.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">💰 Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Precio Unitario</small>
                            <p class="h6">₡<?= number_format($model->unit_price, 2) ?></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Cantidad</small>
                            <p class="h6"><?= $model->quantity ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">Total</small>
                            <p class="h4 text-success">₡<?= number_format($model->total_price, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
