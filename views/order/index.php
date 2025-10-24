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
                        'description' => 'Alquileres pagados'
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
                            <a href="<?= Url::to(['/rental/index', 'estado_pago' => $estado]) ?>" class="btn btn-sm btn-light mt-3">
                                Ver <?= strtolower($config['title']) ?> →
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Sección de Alquileres -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px;">directions_car</span>
                Órdenes de Alquiler (<?= $rentalsDataProvider->getTotalCount() ?>)
            </h3>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            
            <?= GridView::widget([
                'dataProvider' => $rentalsDataProvider,
                'tableOptions' => ['class' => 'table table-hover'],
                'showHeader' => true,
                'emptyText' => '',
                'columns' => [
                    [
                        'attribute' => 'rental_id',
                        'label' => 'ID Alquiler',
                        'format' => 'text',
                        'value' => function($model) {
                            return $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
                        },
                    ],
                    [
                        'attribute' => 'client_id',
                        'label' => '👤 Cliente',
                        'value' => function($model) {
                            return $model->client ? $model->client->full_name : 'N/A';
                        },
                    ],
                    [
                        'attribute' => 'car_id',
                        'label' => '🚗 Vehículo',
                        'value' => function($model) {
                            if ($model->car) {
                                return $model->car->nombre . ' (' . $model->car->placa . ')';
                            }
                            return 'Vehículo no encontrado';
                        },
                    ],
                    [
                        'attribute' => 'fecha_inicio',
                        'label' => '📅 Inicio',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'fecha_final',
                        'label' => '📅 Fin',
                        'format' => 'date',
                    ],
                    [
                        'attribute' => 'total_precio',
                        'label' => '💰 Total',
                        'value' => function($model) {
                            return '₡' . number_format($model->total_precio, 2);
                        },
                    ],
                    [
                        'attribute' => 'estado_pago',
                        'label' => 'Estado',
                        'format' => 'raw',
                        'value' => function($model) {
                            $estado = $model->estado_pago ?? 'pendiente';
                            $badges = [
                                'pagado' => ['success', '✅ Pagado'],
                                'pendiente' => ['warning', '⏳ Pendiente'],
                                'reservado' => ['info', '📋 Reservado'],
                                'cancelado' => ['danger', '❌ Cancelado'],
                            ];
                            $badge = $badges[$estado] ?? ['secondary', $estado];
                            return '<span class="badge bg-' . $badge[0] . '">' . $badge[1] . '</span>';
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Acciones',
                        'template' => '{view} {pdf} {share} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('👁️', ['/rental/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-info', 'title' => 'Ver']);
                            },
                            'pdf' => function ($url, $model) {
                                return Html::a('📄', ['/pdf/rental-order', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-warning',
                                    'title' => 'Descargar PDF',
                                    'target' => '_blank'
                                ]);
                            },
                            'share' => function ($url, $model) {
                                return Html::button('📤', [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Compartir PDF',
                                    'onclick' => 'shareRental(' . $model->id . ')'
                                ]);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('✏️', ['/rental/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary', 'title' => 'Editar']);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('🗑️', ['/rental/delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Cancelar',
                                    'data-confirm' => '¿Estás seguro de cancelar este alquiler?',
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

<script>
function shareRental(rentalId) {
    // URL del PDF de la orden de alquiler
    const pdfUrl = '/pdf/rental-order?id=' + rentalId;
    const shareUrl = window.location.origin + pdfUrl;
    
    // Título del documento a compartir
    const title = 'Orden de Alquiler #' + rentalId;
    const text = 'Compartir orden de alquiler: ' + title;
    
    // Verificar si el navegador soporta Web Share API
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: shareUrl
        }).then(() => {
            showNotification('✅ Orden compartida exitosamente', 'success');
        }).catch((error) => {
            console.log('Error al compartir:', error);
            // Fallback a copiar al portapapeles
            copyToClipboard(shareUrl);
        });
    } else {
        // Fallback: copiar URL al portapapeles
        copyToClipboard(shareUrl);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('✅ URL copiada al portapapeles', 'success');
        }).catch((error) => {
            console.error('Error al copiar:', error);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showNotification('✅ URL copiada al portapapeles', 'success');
        } else {
            showNotification('❌ Error al copiar URL', 'error');
        }
    } catch (err) {
        console.error('Error en fallback copy:', err);
        showNotification('❌ Error al copiar URL', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>