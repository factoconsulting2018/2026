<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rentas Asincrónicas';
$this->params['breadcrumbs'][] = $this->title;

$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
$page = $pagination ? (int) $pagination->page : 0;
$pageSize = $pagination ? (int) $pagination->pageSize : count($dataProvider->getModels());
$start = $totalCount > 0 ? ($page * $pageSize) + 1 : 0;
$end = min(($page + 1) * $pageSize, $totalCount);

$pagerConfig = [
    'pagination' => $pagination,
    'options' => ['class' => 'pagination pagination-modern mb-0'],
    'linkContainerOptions' => ['class' => 'page-item'],
    'linkOptions' => ['class' => 'page-link'],
    'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
    'prevPageCssClass' => 'page-item',
    'nextPageCssClass' => 'page-item',
    'activePageCssClass' => 'active',
    'disabledPageCssClass' => 'disabled',
];

$gridColumns = [
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
                'finalizado' => 'badge bg-dark',
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
];

$this->registerCss('
    .async-rental-index .async-header-actions {
        flex-shrink: 0;
    }

    .async-rental-index .async-header-actions .btn {
        white-space: nowrap;
    }

    .async-index-mobile .accordion-button {
        white-space: normal;
        line-height: 1.35;
        font-size: 0.92rem;
        padding: 0.65rem 0.85rem;
    }

    .async-index-mobile .accordion-button:not(.collapsed) {
        font-weight: 600;
        background-color: #f8fafc;
        color: #1b305b;
    }

    .async-index-mobile .async-acc-meta {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.15rem;
    }

    .async-mobile-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.15rem;
    }

    .async-mobile-value {
        font-size: 0.95rem;
        color: #1e293b;
        word-break: break-word;
    }

    .async-mobile-tabs .nav-link {
        font-size: 0.82rem;
        padding: 0.45rem 0.35rem;
        color: #475569;
    }

    .async-mobile-tabs .nav-link.active {
        color: #1b305b;
        font-weight: 600;
    }

    .async-pagination-bar {
        padding: 14px 18px;
        background: #fff;
        border: 1px solid #e6ecf3;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .async-pagination-summary {
        font-size: 0.95rem;
        color: #5b6b82;
        font-weight: 500;
    }

    .pagination-modern {
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-modern .page-item .page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 1px solid #d9e2ec;
        background: #ffffff;
        color: #1b305b;
        font-weight: 600;
    }

    .pagination-modern .page-item.active .page-link {
        background: linear-gradient(135deg, #1b305b 0%, #22487a 100%);
        border-color: #1b305b;
        color: #ffffff;
    }

    @media (max-width: 767.98px) {
        .async-rental-index h1 {
            font-size: 1.35rem;
        }

        .async-rental-index .page-header-row {
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.75rem;
        }

        .async-rental-index .async-header-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .async-mobile-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .async-mobile-tabs .nav-item {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .async-mobile-tabs .nav-link {
            font-size: 0.78rem;
        }
    }
');
?>

<div class="async-rental-index">
    <div class="d-flex justify-content-between align-items-center mb-3 page-header-row">
        <h1 class="mb-0">
            <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #ff6600;">
                history
            </span>
            <?= Html::encode($this->title) ?>
        </h1>
        <div class="async-header-actions">
            <?= Html::a('<span class="material-symbols-outlined">add_circle</span> Nueva Orden Asincrónica', ['create'], [
                'class' => 'btn btn-primary d-inline-flex align-items-center gap-1',
                'style' => 'background-color: #0f1d41; border-color: #0f1d41;',
            ]) ?>
        </div>
    </div>

    <p class="alert alert-info">
        Las rentas asincrónicas permiten registrar órdenes históricas sin afectar la disponibilidad ni el estado actual de los vehículos.
    </p>

    <?php Pjax::begin(['id' => 'async-rental-pjax']); ?>

    <div class="d-none d-md-block">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
            'columns' => $gridColumns,
        ]) ?>
    </div>

    <div class="d-md-none async-index-mobile">
        <?php
        $models = $dataProvider->getModels();
        if (count($models) === 0): ?>
            <p class="text-muted text-center py-4 mb-0">No hay rentas asincrónicas registradas.</p>
        <?php else: ?>
            <div class="accordion accordion-flush border rounded overflow-hidden" id="asyncMobileAccordion">
                <?php foreach ($models as $i => $model):
                    $orderId = $model->rental_id ?: ('R' . str_pad((string) $model->id, 6, '0', STR_PAD_LEFT));
                    $accId = 'async-acc-' . $model->id;
                    $headingId = 'async-acc-heading-' . $model->id;
                    $carLabel = $model->car ? $model->car->nombre : 'Sin vehículo';
                    $clientLabel = $model->client ? $model->client->full_name : 'Sin cliente';
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="<?= Html::encode($headingId) ?>">
                            <button class="accordion-button <?= $i !== 0 ? 'collapsed' : '' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= Html::encode($accId) ?>"
                                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                    aria-controls="<?= Html::encode($accId) ?>">
                                <div class="w-100">
                                    <div class="fw-semibold"><?= Html::encode($orderId) ?></div>
                                    <div class="async-acc-meta"><?= Html::encode($clientLabel) ?> · <?= Html::encode($carLabel) ?></div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= Html::encode($accId) ?>"
                             class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             aria-labelledby="<?= Html::encode($headingId) ?>"
                             data-bs-parent="#asyncMobileAccordion">
                            <div class="accordion-body bg-white px-2 py-3">
                                <?= $this->render('_index_mobile_pane', ['model' => $model]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="async-pagination-bar d-flex flex-column align-items-center gap-3 mt-4">
            <div class="async-pagination-summary text-center">
                Mostrando <?= $start ?> - <?= $end ?> de <?= $totalCount ?> órdenes
            </div>
            <?= LinkPager::widget($pagerConfig) ?>
        </div>
    </div>

    <?php if ($pagination && $pagination->pageCount > 1): ?>
    <div class="d-none d-md-flex async-pagination-bar flex-md-row justify-content-between align-items-center gap-3 mt-3">
        <div class="async-pagination-summary">
            Mostrando <?= $start ?> - <?= $end ?> de <?= $totalCount ?> órdenes
        </div>
        <?= LinkPager::widget($pagerConfig) ?>
    </div>
    <?php endif; ?>

    <?php Pjax::end(); ?>
</div>
