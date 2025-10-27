<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $rentalsDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Alquileres';
$this->params['breadcrumbs'][] = $this->title;

// CSS para el encabezado de tabla moderno
$this->registerCss('
    .table-header {
        background: linear-gradient(135deg, #3fa9f5 0%, #1b305b 100%);
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 16px 16px 0 0;
    }
    
    .table-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .table-title h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .table-title .material-symbols-outlined {
        font-size: 28px;
    }
    
    .table-stats {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 16px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
    }
    
    .stat-item .material-symbols-outlined {
        font-size: 20px;
    }
    
    /* Estilos para headers de la tabla */
    .table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white !important;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 16px 12px;
        border: none;
        text-align: center;
        vertical-align: middle;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-decoration: none !important;
    }
    
    .table thead th,
    .table thead th *,
    .table thead th a,
    .table thead th span,
    .table thead th div {
        color: white !important;
        text-decoration: none !important;
    }
    
    .table thead th:first-child {
        border-radius: 8px 0 0 0;
    }
    
    .table thead th:last-child {
        border-radius: 0 8px 0 0;
    }
    
    .table thead th:hover {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
        color: white !important;
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }
    
    .table thead th:hover,
    .table thead th:hover *,
    .table thead th:hover a,
    .table thead th:hover span,
    .table thead th:hover div {
        color: white !important;
        text-decoration: none !important;
    }
    
    /* Estilos para el cuerpo de la tabla */
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-color: #e9ecef;
    }
');
?>

<div class="order-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">directions_car</span><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nuevo Alquiler', ['/rental/create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>


    <div class="card">
        <div class="table-header">
            <div class="table-title">
                <span class="material-symbols-outlined">receipt_long</span>
                <h3>Órdenes de Alquiler</h3>
            </div>
            <div class="table-stats">
                <span class="stat-item">
                    <span class="material-symbols-outlined">receipt</span>
                    <span><?= $rentalsDataProvider->getTotalCount() ?> Alquileres</span>
                </span>
            </div>
        </div>
        <div class="card-body">
            <!-- Panel de Control - Estados de Pago -->
            <div class="row mb-4">
                <?php
                $estados = [
                    'pagado' => [
                        'title' => 'Pagados',
                        'icon' => 'check_circle',
                        'gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                        'description' => 'Órdenes pagadas'
                    ],
                    'pendiente' => [
                        'title' => 'Pendientes',
                        'icon' => 'pending',
                        'gradient' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
                        'description' => 'Por procesar'
                    ],
                    'reservado' => [
                        'title' => 'Reservados',
                        'icon' => 'event_available',
                        'gradient' => 'linear-gradient(135deg, #3fa9f5 0%, #3891d6 100%)',
                        'description' => 'Reservaciones'
                    ],
                    'cancelado' => [
                        'title' => 'Cancelados',
                        'icon' => 'cancel',
                        'gradient' => 'linear-gradient(135deg, #dc3545 0%, #e83e8c 100%)',
                        'description' => 'Cancelados'
                    ],
                ];
                
                foreach ($estados as $estado => $config) {
                    $count = isset($paymentCounters[$estado]) ? $paymentCounters[$estado] : 0;
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card text-white" style="background: <?= $config['gradient'] ?>;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">
                                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">
                                            <?= $config['icon'] ?>
                                        </span>
                                        <?= $config['title'] ?>
                                    </h6>
                                    <h2 class="mt-2"><?= $count ?></h2>
                                    <small><?= $config['description'] ?></small>
                                </div>
                                <div class="fs-1">
                                    <span class="material-symbols-outlined" style="font-size: 48px;">
                                        <?= $config['icon'] ?>
                                    </span>
                                </div>
                            </div>
                            <a href="<?= Url::to(['/order/index', 'sale_mode' => $estado]) ?>" class="btn btn-sm btn-light mt-3">
                                Ver <?= strtolower($config['title']) ?> →
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Sección de Órdenes -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px;">shopping_cart</span>
                Órdenes de Venta (<?= $dataProvider->getTotalCount() ?>)
            </h3>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover'],
                'showHeader' => true,
                'emptyText' => '',
                'columns' => [
                    'id',
                    'ticket_id',
                    [
                        'attribute' => 'client_id',
                        'label' => '👤 Cliente',
                        'value' => function($model) {
                            return $model->client ? $model->client->full_name : 'N/A';
                        },
                    ],
                    [
                        'attribute' => 'article_id',
                        'label' => '📦 Artículo',
                        'value' => function($model) {
                            return $model->article ? $model->article->name : 'N/A';
                        },
                    ],
                    'quantity',
                    [
                        'attribute' => 'unit_price',
                        'label' => '💰 Precio Unitario',
                        'value' => function($model) {
                            return '₡' . ($model->unit_price && $model->unit_price > 0 ? number_format($model->unit_price, 2) : '0.00');
                        },
                    ],
                    [
                        'attribute' => 'total_price',
                        'label' => '💰 Total',
                        'value' => function($model) {
                            return '₡' . ($model->total_price && $model->total_price > 0 ? number_format($model->total_price, 2) : '0.00');
                        },
                    ],
                    [
                        'attribute' => 'sale_mode',
                        'label' => 'Modo',
                        'value' => function($model) {
                            return $model->sale_mode === 'retail' ? 'Retail' : 'Wholesale';
                        },
                    ],
                    'created_at:datetime',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Acciones',
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('👁️', ['/order/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-info', 'title' => 'Ver']);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('✏️', ['/order/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary', 'title' => 'Editar']);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('🗑️', ['/order/delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Eliminar',
                                    'data-confirm' => '¿Estás seguro de eliminar esta orden?',
                                    'data-method' => 'post',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
