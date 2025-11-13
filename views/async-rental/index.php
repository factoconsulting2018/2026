<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rentas Asincrónicas';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="async-rental-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #ff6600;">
                history
            </span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a('<span class="material-symbols-outlined">add_circle</span> Nueva Orden Asincrónica', ['create'], [
            'class' => 'btn btn-primary',
            'style' => 'background-color: #0f1d41; border-color: #0f1d41;',
        ]) ?>
    </div>

    <p class="alert alert-info">
        Las rentas asincrónicas permiten registrar órdenes históricas sin afectar la disponibilidad ni el estado actual de los vehículos.
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'rental_id',
                'label' => 'ID Orden',
                'value' => function ($model) {
                    return $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
                },
            ],
            [
                'attribute' => 'client_id',
                'label' => 'Cliente',
                'value' => function ($model) {
                    return $model->client ? $model->client->full_name : 'N/A';
                },
            ],
            [
                'attribute' => 'car_id',
                'label' => 'Vehículo',
                'value' => function ($model) {
                    return $model->car ? $model->car->nombre : 'N/A';
                },
            ],
            [
                'attribute' => 'fecha_inicio',
                'label' => 'Fecha Inicio',
                'value' => function ($model) {
                    return $model->fecha_inicio ? date('d/m/Y', strtotime($model->fecha_inicio)) : 'N/A';
                },
            ],
            [
                'attribute' => 'fecha_final',
                'label' => 'Fecha Fin',
                'value' => function ($model) {
                    return $model->fecha_final ? date('d/m/Y', strtotime($model->fecha_final)) : 'N/A';
                },
            ],
            [
                'attribute' => 'cantidad_dias',
                'label' => 'Días',
            ],
            [
                'attribute' => 'estado_pago',
                'label' => 'Estado de Pago',
                'format' => 'raw',
                'value' => function ($model) {
                    $colors = [
                        'pendiente' => 'badge bg-warning text-dark',
                        'pagado' => 'badge bg-success',
                        'reservado' => 'badge bg-info text-dark',
                        'cancelado' => 'badge bg-danger',
                    ];
                    $class = $colors[$model->estado_pago] ?? 'badge bg-secondary';
                    $label = $model->estado_pago ? strtoupper($model->estado_pago) : 'N/A';
                    return Html::tag('span', $label, ['class' => $class]);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'async-rental',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="material-symbols-outlined">visibility</span>', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Ver orden',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<span class="material-symbols-outlined">edit</span>', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Editar orden',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="material-symbols-outlined">delete</span>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Eliminar orden',
                            'data' => [
                                'confirm' => '¿Está seguro que desea eliminar esta orden asincrónica?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
                'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap;'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

