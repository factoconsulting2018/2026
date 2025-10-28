<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $status */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Órdenes';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS para colores de estado y acordeón
$this->registerCssFile('@web/css/rental-status.css');
$this->registerCssFile('@web/css/rental-accordion.css');

// Registrar JavaScript para acordeón
$this->registerJsFile('@web/js/rental-accordion.js', ['depends' => [yii\web\JqueryAsset::class]]);

// CSS para la tabla moderna
$this->registerCss('
    /* ========================================
       TABLA MODERNA DE ALQUILERES
       ======================================== */
    
    .modern-rental-table {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        width: 100%;
    }
    
    .table-header {
        background: linear-gradient(135deg, #3fa9f5 0%, #1b305b 100%);
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        gap: 16px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .table-content {
        padding: 0;
        width: 100%;
    }
    
    .modern-table {
        width: 100%;
    }
    
    .table-header-row {
        display: grid;
        grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1fr 1fr 0.8fr;
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        width: 100%;
    }
    
    .header-cell {
        padding: 16px 12px;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
        border-right: 1px solid #e9ecef;
    }
    
    .header-cell:last-child {
        border-right: none;
    }
    
    .header-cell .material-symbols-outlined {
        font-size: 18px;
        color: #6c757d;
    }
    
    .table-body {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .rental-row {
        display: grid;
        grid-template-columns: 1fr 2fr 1.5fr 1.5fr 1fr 1fr 0.8fr;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
        background: white;
        width: 100%;
    }
    
    .rental-row:hover {
        background: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .rental-row.expired {
        background: #fff5f5;
        border-left: 4px solid #dc3545;
    }
    
    .rental-row.expiring {
        background: #fffbf0;
        border-left: 4px solid #ffc107;
    }
    
    .data-cell {
        padding: 16px 12px;
        display: flex;
        align-items: center;
        border-right: 1px solid #e9ecef;
        min-height: 80px;
    }
    
    .data-cell:last-child {
        border-right: none;
    }
    
    /* ID Alquiler */
    .rental-id-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #3fa9f5, #1b305b);
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        min-width: 80px;
        justify-content: center;
    }
    
    .rental-id-badge .material-symbols-outlined {
        font-size: 16px;
    }
    
    /* Información del Cliente */
    .client-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .client-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
        line-height: 1.3;
    }
    
    .client-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: 12px;
        color: #6c757d;
    }
    
    .client-phone {
        color: #28a745;
    }
    
    /* Información del Vehículo */
    .vehicle-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .vehicle-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }
    
    .vehicle-details {
        font-size: 12px;
        color: #6c757d;
    }
    
    .vehicle-plate {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }
    
    /* Rango de Fechas */
    .date-range {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .start-date, .end-date {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        padding: 4px 8px;
        border-radius: 8px;
    }
    
    .start-date {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .end-date {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .end-date.expired {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .end-date.expiring {
        background: #fff8e1;
        color: #f57c00;
    }
    
     .start-date .material-symbols-outlined,
     .end-date .material-symbols-outlined {
         font-size: 16px;
     }
     
     /* Estilos para fechas con referencia temporal */
     .start-date strong,
     .end-date strong {
         color: #1976d2;
         font-weight: 700;
     }
     
     .end-date.expired strong {
         color: #d32f2f;
     }
     
     .end-date.expiring strong {
         color: #f57c00;
     }
    
    /* Estado de Pago */
    .payment-status-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        min-width: 100px;
        justify-content: center;
    }
    
    .payment-status-badge.pagado {
        background: #d4edda;
        color: #155724;
    }
    
    .payment-status-badge.pendiente {
        background: #fff3cd;
        color: #856404;
    }
    
    .payment-status-badge.reservado {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .payment-status-badge.cancelado {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-icon .material-symbols-outlined {
        font-size: 16px;
    }
    
    /* Monto Total */
    .total-amount {
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 700;
        color: #28a745;
        font-size: 14px;
    }
    
    .currency {
        font-size: 12px;
        opacity: 0.8;
    }
    
    .amount {
        font-family: monospace;
    }
    
    /* Acciones CRUD */
    .actions-cell {
        justify-content: center;
    }
    
    .crud-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .action-btn .material-symbols-outlined {
        font-size: 16px;
        transition: transform 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .action-btn:hover .material-symbols-outlined {
        transform: scale(1.1);
    }
    
    .action-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Botón Ver */
    .view-btn {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }
    
    .view-btn:hover {
        background: linear-gradient(135deg, #138496, #117a8b);
    }
    
    .view-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .view-btn:hover::before {
        left: 100%;
    }
    
    /* Botón Editar */
    .edit-btn {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }
    
    .edit-btn:hover {
        background: linear-gradient(135deg, #1e7e34, #155724);
    }
    
    .edit-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .edit-btn:hover::before {
        left: 100%;
    }
    
    /* Botón Cambiar Estado de Pago */
    .payment-btn {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: white;
    }
    
    .payment-btn:hover {
        background: linear-gradient(135deg, #e0a800, #d39e00);
    }
    
    .payment-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .payment-btn:hover::before {
        left: 100%;
    }

    /* Botón Compartir */
    .share-btn {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }
    
    .share-btn:hover {
        background: linear-gradient(135deg, #138496, #117a8b);
    }
    
    .share-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
    }
    
    /* Botón PDF */
    .pdf-btn {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }
    
    .pdf-btn:hover {
        background: linear-gradient(135deg, #c82333, #bd2130);
    }
    
    .pdf-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .share-btn:hover::before {
        left: 100%;
    }
    
    .pdf-btn:hover::before {
        left: 100%;
    }

    /* Botón Eliminar */
    .delete-btn {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }
    
    .delete-btn:hover {
        background: linear-gradient(135deg, #c82333, #bd2130);
    }
    
    .delete-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .delete-btn:hover::before {
        left: 100%;
    }
    
    /* Tooltips */
    .action-btn[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: -35px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
    }
    
    .action-btn[title]:hover::before {
        content: "";
        position: absolute;
        bottom: -25px;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-bottom-color: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        pointer-events: none;
    }
    
    /* Scroll personalizado */
    .table-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .table-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .table-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .table-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Acciones CRUD para móvil */
    .crud-actions-mobile {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: center;
        padding: 16px 0;
        flex-wrap: wrap;
    }
    
    .crud-actions-mobile .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        font-size: 18px;
    }
    
    .crud-actions-mobile .action-btn .material-symbols-outlined {
        font-size: 18px;
    }
    
    @media (max-width: 576px) {
        /* Acciones móviles más pequeñas en pantallas pequeñas */
        .crud-actions-mobile {
            gap: 8px;
            padding: 12px 0;
        }
        
        .crud-actions-mobile .action-btn {
            width: 36px;
            height: 36px;
        }
        
        .crud-actions-mobile .action-btn .material-symbols-outlined {
            font-size: 16px;
        }
    }
    
    @media (max-width: 400px) {
        /* Acciones aún más pequeñas en pantallas muy pequeñas */
        .crud-actions-mobile {
            gap: 6px;
            padding: 10px 0;
        }
        
        .crud-actions-mobile .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }
        
        .crud-actions-mobile .action-btn .material-symbols-outlined {
            font-size: 14px;
        }
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .table-header-row,
        .rental-row {
            grid-template-columns: 0.8fr 1.5fr 1fr 1fr 0.8fr 0.8fr 0.6fr;
        }
    }
    
    @media (max-width: 768px) {
        .crud-actions {
            gap: 6px;
        }
        
        .action-btn {
            width: 28px;
            height: 28px;
        }
        
        .action-btn .material-symbols-outlined {
            font-size: 14px;
        }
    }
    
    @media (max-width: 992px) {
        .modern-rental-table {
            display: none;
        }
        
        /* Mejorar responsividad en móviles */
        .rental-index h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .btn-success {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        .nav-tabs {
            font-size: 0.9rem;
        }
        
        .nav-tabs .material-symbols-outlined {
            font-size: 16px !important;
        }
        
        /* Contador responsive */
        .stat-item {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
        
        .stat-item .material-symbols-outlined {
            font-size: 16px;
        }
    }
    
    @media (max-width: 576px) {
        .rental-index {
            padding: 0.5rem;
        }
        
        .rental-index h1 {
            font-size: 1.25rem;
        }
        
        .rental-index h1 .material-symbols-outlined {
            font-size: 24px !important;
        }
        
        .btn-success {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        
        .nav-tabs {
            font-size: 0.8rem;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
        }
        
        .nav-tabs .nav-item {
            flex: 0 0 auto;
            white-space: nowrap;
        }
        
        .nav-tabs .nav-link {
            padding: 0.5rem 0.75rem;
        }
        
        .accordion-card {
            margin-bottom: 1rem;
        }
        
        /* Mejorar espaciado en acordeones */
        .accordion-actions {
            padding: 12px;
        }
        
        /* Ajustar padding de los cards de acordeón */
        .accordion-body {
            padding: 0.75rem !important;
        }
        
        .stat-item {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
        
        .table-header h3 {
            font-size: 1.2rem !important;
        }
        
        .table-stats {
            flex-direction: column;
            gap: 8px;
        }
    }
');
?>

<div class="rental-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">shopping_cart</span><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nueva Orden', ['/rental/create'], ['class' => 'btn btn-success']) ?>
    </div>

    <!-- Sistema de Tabs -->
    <ul class="nav nav-tabs mb-4" id="rentalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane" type="button" role="tab" aria-controls="list-pane" aria-selected="true">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">list</span>
                Listado de Alquileres
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-pane" type="button" role="tab" aria-controls="calendar-pane" aria-selected="false">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">calendar_month</span>
                Calendario de Disponibilidad
            </button>
        </li>
    </ul>

    <div class="tab-content" id="rentalTabContent">
        <!-- Tab 1: Listado de Alquileres -->
        <div class="tab-pane fade show active" id="list-pane" role="tabpanel" aria-labelledby="list-tab">


    <!-- Resumen de Estados -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">palette</span>
                Leyenda de Estados
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-warning me-2"></div>
                        <span><strong>Pendiente:</strong> Esperando pago</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-success me-2"></div>
                        <span><strong>Pagado:</strong> Alquiler activo</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-info me-2"></div>
                        <span><strong>Reservado:</strong> Reserva confirmada</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator bg-danger me-2"></div>
                        <span><strong>Cancelado:</strong> Alquiler cancelado</span>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <small class="text-muted">
                        <span class="text-danger">⚠️</span> <strong>Vencido:</strong> Fecha de entrega pasada
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        <span class="text-warning">⏰</span> <strong>Por vencer:</strong> Próximo a vencer (2 días o menos)
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>Estado</label>
                    <select name="estado_pago" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= $status === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="pagado" <?= $status === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                        <option value="reservado" <?= $status === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">filter_alt</span>Filtrar</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">clear</span>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor de la tabla moderna (desktop) -->
    <div class="rental-table-container">
        <div class="modern-rental-table">
        <div class="table-header">
            <div class="table-title">
                <span class="material-symbols-outlined">receipt_long</span>
                    <h3>Listado de Alquileres</h3>
            </div>
            <div class="table-stats">
                <span class="stat-item">
                    <span class="material-symbols-outlined">receipt</span>
                        <span><?= $dataProvider->getTotalCount() ?> Alquileres</span>
                </span>
            </div>
        </div>
            
            <div class="table-content">
                <?php Pjax::begin(); ?>
                
                <div class="modern-table">
                    <div class="table-header-row">
                        <div class="header-cell rental-id">
                            <span class="material-symbols-outlined">tag</span>
                            <span>ID Alquiler</span>
                        </div>
                        <div class="header-cell client-info">
                            <span class="material-symbols-outlined">person</span>
                            <span>Cliente</span>
                        </div>
                        <div class="header-cell vehicle-info">
                            <span class="material-symbols-outlined">directions_car</span>
                            <span>Vehículo</span>
                        </div>
                        <div class="header-cell date-range">
                            <span class="material-symbols-outlined">date_range</span>
                            <span>Período</span>
                        </div>
                        <div class="header-cell payment-status">
                            <span class="material-symbols-outlined">payment</span>
                            <span>Estado</span>
                        </div>
                        <div class="header-cell total-amount">
                            <span class="material-symbols-outlined">attach_money</span>
                            <span>Total</span>
                        </div>
                        <div class="header-cell actions">
                            <span class="material-symbols-outlined">more_vert</span>
                            <span>Acciones</span>
                        </div>
                    </div>
                    
                    <div class="table-body">
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                <?php
                            $estado = $model->estado_pago ?? 'pendiente';
                            $rentalId = !empty($model->rental_id) ? $model->rental_id : ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
                            
                            // Cargar relaciones si no están cargadas
                            if (!$model->client && $model->client_id) {
                                $model->client = \app\models\Client::findOne($model->client_id);
                            }
                            if (!$model->car && $model->car_id) {
                                $model->car = \app\models\Car::findOne($model->car_id);
                            }
                            
                            // Verificar fechas
                            $hoy = new \DateTime();
                            $fechaFin = $model->fecha_final ? new \DateTime($model->fecha_final) : null;
                            $diferencia = $fechaFin ? $hoy->diff($fechaFin)->days : null;
                            
                            $rowClass = 'rental-row';
                            if ($fechaFin && $fechaFin < $hoy && $estado !== 'cancelado') {
                                $rowClass .= ' expired';
                            } elseif ($diferencia && $diferencia <= 2 && $estado === 'pagado') {
                                $rowClass .= ' expiring';
                            }
                            ?>
                            
                            <div class="<?= $rowClass ?>" data-estado="<?= $estado ?>">
                                <div class="data-cell rental-id-cell">
                                    <div class="rental-id-badge">
                                        <span class="material-symbols-outlined">receipt</span>
                                        <span class="rental-id-text"><?= Html::encode($rentalId) ?></span>
                                    </div>
                                </div>
                                
                                <div class="data-cell client-cell">
                                    <div class="client-info">
                                        <div class="client-name">
                                            <?= $model->client ? Html::encode($model->client->full_name ?? 'Cliente sin nombre') : 'Cliente no encontrado' ?>
                                        </div>
                                        <div class="client-details">
                                            <span class="client-id">ID: <?= $model->client ? $model->client->id : 'N/A' ?></span>
                                            <?php if ($model->client && $model->client->telefono): ?>
                                                <span class="client-phone">📞 <?= Html::encode($model->client->telefono) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="data-cell vehicle-cell">
                                    <div class="vehicle-info">
                                        <div class="vehicle-name">
                                            <?= $model->car ? Html::encode($model->car->nombre ?? 'Vehículo sin nombre') : 'Vehículo no encontrado' ?>
                                        </div>
                                        <div class="vehicle-details">
                                            <span class="vehicle-plate">🚗 <?= $model->car ? Html::encode($model->car->placa ?? 'Sin placa') : 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="data-cell date-cell">
                                    <div class="date-range">
                                        <div class="start-date">
                                            <span class="material-symbols-outlined">play_arrow</span>
                                            <span>
                                                <?php
                                                if ($model->fecha_inicio) {
                                                    $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
                                                    $hoy = date('d/m/Y');
                                                    if ($fechaInicio === $hoy) {
                                                        echo '<strong>Hoy (' . $fechaInicio . ')</strong>';
                                                    } else {
                                                        echo $fechaInicio;
                                                    }
                                                } else {
                                                    echo 'Sin fecha';
                                                }
                                                ?>
                                        </span>
                                </div>
                                        <div class="end-date <?= $fechaFin && $fechaFin < $hoy && $estado !== 'cancelado' ? 'expired' : ($diferencia && $diferencia <= 2 && $estado === 'pagado' ? 'expiring' : '') ?>">
                                            <span class="material-symbols-outlined">stop</span>
                                            <span>
                                                <?php
                                                if ($model->fecha_final) {
                                                    $fechaFinFormatted = date('d/m/Y', strtotime($model->fecha_final));
                                                    $hoyObj = new \DateTime();
                                                    $fechaFinObj = new \DateTime($model->fecha_final);
                                                    
                                                    if ($fechaFinObj->format('Y-m-d') === $hoyObj->format('Y-m-d')) {
                                                        echo '<strong>Hoy (' . $fechaFinFormatted . ')</strong>';
                                                    } elseif ($fechaFinObj < $hoyObj && $estado !== 'cancelado') {
                                                        $diasVencido = $hoyObj->diff($fechaFinObj)->days;
                                                        echo '<strong>Vencido hace ' . $diasVencido . ' día' . ($diasVencido != 1 ? 's' : '') . ' (' . $fechaFinFormatted . ')</strong>';
                                                    } elseif ($diferencia && $diferencia <= 2 && $estado === 'pagado') {
                                                        echo '<strong>Por vencer en ' . $diferencia . ' día' . ($diferencia != 1 ? 's' : '') . ' (' . $fechaFinFormatted . ')</strong>';
                                                    } else {
                                                        echo $fechaFinFormatted;
                                                    }
                                                } else {
                                                    echo 'Sin fecha';
                                                }
                                                ?>
                                    </span>
                                </div>
                            </div>
                                </div>
                                
                                <div class="data-cell status-cell">
                                    <div class="payment-status-badge <?= $estado ?>">
                                        <span class="status-icon">
                                            <?php
                                            switch ($estado) {
                                                case 'pagado':
                                                    echo '<span class="material-symbols-outlined">check_circle</span>';
                                                    break;
                                                case 'pendiente':
                                                    echo '<span class="material-symbols-outlined">schedule</span>';
                                                    break;
                                                case 'reservado':
                                                    echo '<span class="material-symbols-outlined">bookmark</span>';
                                                    break;
                                                case 'cancelado':
                                                    echo '<span class="material-symbols-outlined">cancel</span>';
                                                    break;
                                                default:
                                                    echo '<span class="material-symbols-outlined">help</span>';
                                            }
                                            ?>
                                        </span>
                                        <span class="status-text"><?= ucfirst($estado) ?></span>
                                    </div>
                                </div>
                                
                                <div class="data-cell amount-cell">
                                    <div class="total-amount">
                                        <span class="currency">₡</span>
                                        <span class="amount"><?= $model->total_precio && $model->total_precio > 0 ? number_format($model->total_precio, 2) : '0.00' ?></span>
                                    </div>
                                </div>
                                
                                <div class="data-cell actions-cell">
                                    <div class="crud-actions">
                                        <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="action-btn view-btn" title="Ver Detalles">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                        <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="action-btn edit-btn" title="Editar">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <button type="button" class="action-btn payment-btn" 
                                                title="Cambiar Estado de Pago"
                                                data-rental-id="<?= $model->id ?>"
                                                data-current-status="<?= $estado ?>"
                                                data-rental-id-text="<?= Html::encode($rentalId) ?>"
                                                onclick="openPaymentModal(this)">
                                            <span class="material-symbols-outlined">payment</span>
                                        </button>
                                        <button type="button" class="action-btn share-btn" 
                                                title="Compartir Orden"
                                                data-rental-id="<?= $model->id ?>"
                                                data-rental-id-text="<?= Html::encode($rentalId) ?>"
                                                onclick="shareRental(<?= $model->id ?>)">
                                            <span class="material-symbols-outlined">share</span>
                                        </button>
                                        <a href="<?= Url::to(['/pdf/download-rental', 'id' => $model->id]) ?>" 
                                           class="action-btn pdf-btn pdf-btn-hide" 
                                           data-rental-id="<?= $model->id ?>"
                                           title="Descargar PDF"
                                           style="display:none;"
                                           download>
                                            <span class="material-symbols-outlined">description</span>
                                        </a>
                                        <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" class="action-btn delete-btn" 
                                           title="Cancelar Alquiler"
                                           data-confirm="¿Estás seguro de cancelar este alquiler?" 
                                           data-method="post">
                                            <span class="material-symbols-outlined">delete</span>
                            </a>
                        </div>
                    </div>
                </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>

    <!-- Paginación para tabla desktop -->
    <div class="d-flex justify-content-center mt-4">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'options' => ['class' => 'pagination'],
            'linkOptions' => ['class' => 'page-link'],
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
        ]) ?>
        </div>

    <!-- Acordeón responsivo (móvil) -->
    <div class="rental-accordion">
            <?php Pjax::begin(); ?>
            
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <?php
                            $estado = $model->estado_pago ?? 'pendiente';
            $estadoClass = 'estado-' . $estado;
            
            // Verificar si el alquiler está vencido o por vencer
            if ($model->fecha_final) {
                $hoy = new \DateTime();
                $fechaFin = new \DateTime($model->fecha_final);
                $diferencia = $hoy->diff($fechaFin)->days;
                
                if ($fechaFin < $hoy && $estado !== 'cancelado') {
                    $estadoClass .= ' vencido';
                } elseif ($diferencia <= 2 && $estado === 'pagado') {
                    $estadoClass .= ' por-vencer';
                }
            }
            
            $viewUrl = Url::to(['view', 'id' => $model->id]);
            $updateUrl = Url::to(['update', 'id' => $model->id]);
            $deleteUrl = Url::to(['delete', 'id' => $model->id]);
            ?>
            
            <div class="rental-accordion-item <?= $estadoClass ?>" data-rental-id="<?= $model->id ?>">
                <button class="accordion-header">
                    <div class="accordion-header-info">
                        <div class="accordion-rental-id"><?= Html::encode(!empty($model->rental_id) ? $model->rental_id : 'R' . $model->id) ?></div>
                        <div class="accordion-client-info">
                            <div class="accordion-client-name">
                                <?php
                                if (!$model->client && $model->client_id) {
                                    $model->client = \app\models\Client::findOne($model->client_id);
                                }
                                echo $model->client ? Html::encode($model->client->full_name ?? $model->client->nombre) : 'N/A';
                                ?>
                            </div>
                            <div class="accordion-car-info">
                                <span class="material-symbols-outlined" style="font-size: 16px;">directions_car</span>
                                <?php
                                if (!$model->car && $model->car_id) {
                                    $model->car = \app\models\Car::findOne($model->car_id);
                                }
                                echo $model->car ? Html::encode($model->car->nombre . ' (' . $model->car->placa . ')') : 'N/A';
                                ?>
                            </div>
                        </div>
                        <div class="accordion-status-badge accordion-status-<?= $estado ?>">
                            <?= ucfirst($estado) ?>
                        </div>
                    </div>
                    <div class="accordion-toggle-icon">
                        <span class="material-symbols-outlined">expand_more</span>
                    </div>
                </button>
                
                <div class="accordion-content">
                    <div class="accordion-body">
                        <div class="accordion-info-grid">
                                <div class="accordion-info-item">
                                    <div class="accordion-info-label">
                                        <span class="material-symbols-outlined">calendar_today</span>
                                        Fecha de Inicio
                                    </div>
                                    <div class="accordion-info-value">
                                        <span class="accordion-fecha">
                                            <?php
                                            if ($model->fecha_inicio) {
                                                $fechaInicio = date('d/m/Y', strtotime($model->fecha_inicio));
                                                $hoy = date('d/m/Y');
                                                if ($fechaInicio === $hoy) {
                                                    echo '<strong>Hoy (' . $fechaInicio . ')</strong>';
                                                } else {
                                                    echo $fechaInicio;
                                                }
                                            } else {
                                                echo 'Sin fecha';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            
                            <div class="accordion-info-item">
                                <div class="accordion-info-label">
                                    <span class="material-symbols-outlined">event</span>
                                    Fecha de Fin
                                </div>
                                <div class="accordion-info-value">
                                    <?php
                                    $fechaFinClass = '';
                                    if ($fechaFin < $hoy && $estado !== 'cancelado') {
                                        $fechaFinClass = 'vencida';
                                    } elseif ($diferencia <= 2 && $estado === 'pagado') {
                                        $fechaFinClass = 'por-vencer';
                                    }
                                    ?>
                                    <span class="accordion-fecha <?= $fechaFinClass ?>">
                                        <?php
                                        if ($model->fecha_final) {
                                            $fechaFinFormatted = date('d/m/Y', strtotime($model->fecha_final));
                                            $hoyObj = new \DateTime();
                                            $fechaFinObj = new \DateTime($model->fecha_final);
                                            
                                            if ($fechaFinObj->format('Y-m-d') === $hoyObj->format('Y-m-d')) {
                                                echo '<strong>Hoy (' . $fechaFinFormatted . ')</strong>';
                                            } elseif ($fechaFinObj < $hoyObj && $estado !== 'cancelado') {
                                                $diasVencido = $hoyObj->diff($fechaFinObj)->days;
                                                echo '<strong>Vencido hace ' . $diasVencido . ' día' . ($diasVencido != 1 ? 's' : '') . ' (' . $fechaFinFormatted . ')</strong>';
                                            } elseif ($diferencia && $diferencia <= 2 && $estado === 'pagado') {
                                                echo '<strong>Por vencer en ' . $diferencia . ' día' . ($diferencia != 1 ? 's' : '') . ' (' . $fechaFinFormatted . ')</strong>';
                                            } else {
                                                echo $fechaFinFormatted;
                                            }
                                        } else {
                                            echo 'Sin fecha';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="accordion-info-item">
                                <div class="accordion-info-label">
                                    <span class="material-symbols-outlined">attach_money</span>
                                    Total del Alquiler
                                </div>
                                <div class="accordion-info-value">
                                    <span class="accordion-precio">
                                        <span class="material-symbols-outlined">monetization_on</span>
                                        ₡<?= $model->total_precio && $model->total_precio > 0 ? number_format($model->total_precio, 2) : '0.00' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="accordion-info-item">
                                <div class="accordion-info-label">
                                    <span class="material-symbols-outlined">person</span>
                                    Información del Cliente
                                </div>
                                <div class="accordion-info-value">
                                    <?php
                                    if (!$model->client && $model->client_id) {
                                        $model->client = \app\models\Client::findOne($model->client_id);
                                    }
                                    echo $model->client ? Html::encode($model->client->full_name ?? $model->client->nombre) : 'N/A';
                                    ?>
                                    <?php if ($model->client && $model->client->telefono): ?>
                                        <br><small class="text-muted">📞 <?= Html::encode($model->client->telefono) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-actions">
                            <div class="crud-actions-mobile">
                                <a href="<?= $viewUrl ?>" class="action-btn view-btn" title="Ver Detalles">
                                    <span class="material-symbols-outlined">visibility</span>
                                </a>
                                <a href="<?= $updateUrl ?>" class="action-btn edit-btn" title="Editar">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <button type="button" class="action-btn payment-btn" 
                                        title="Cambiar Estado de Pago"
                                        data-rental-id="<?= $model->id ?>"
                                        data-current-status="<?= $estado ?>"
                                        data-rental-id-text="<?= Html::encode(!empty($model->rental_id) ? $model->rental_id : 'R' . $model->id) ?>"
                                        onclick="openPaymentModal(this)">
                                    <span class="material-symbols-outlined">payment</span>
                                </button>
                                <button type="button" class="action-btn share-btn" 
                                        title="Compartir Orden"
                                        data-rental-id="<?= $model->id ?>"
                                        onclick="shareRental(<?= $model->id ?>)">
                                    <span class="material-symbols-outlined">share</span>
                                </button>
                                <a href="<?= Url::to(['/pdf/download-rental', 'id' => $model->id]) ?>" 
                                   class="action-btn pdf-btn pdf-btn-hide" 
                                   data-rental-id="<?= $model->id ?>"
                                   title="Descargar PDF"
                                   style="display:none;"
                                   download>
                                    <span class="material-symbols-outlined">description</span>
                                </a>
                                <a href="<?= $deleteUrl ?>" class="action-btn delete-btn" 
                                   title="Cancelar Alquiler"
                                   data-confirm="¿Estás seguro de cancelar este alquiler?" 
                                   data-method="post">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Paginación para acordeón móvil -->
        <div class="d-flex justify-content-center mt-4">
            <?= \yii\widgets\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'prevPageCssClass' => 'page-item',
                'nextPageCssClass' => 'page-item',
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>
        </div>

            <?php Pjax::end(); ?>
        </div>
        <!-- Fin Tab 1: Listado de Alquileres -->

        <!-- Tab 2: Calendario de Disponibilidad -->
        <div class="tab-pane fade" id="calendar-pane" role="tabpanel" aria-labelledby="calendar-tab">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">calendar_month</span>
                            Calendario de Disponibilidad de Vehículos
                        </h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="currentMonth">
                                <i class="fas fa-calendar-day"></i> Hoy
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="availability-calendar">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Cargando calendario...</span>
                                    </div>
                                    <p class="mt-2">Cargando disponibilidad...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="availability-legend">
                                <h6><i class="fas fa-info-circle"></i> Leyenda</h6>
                                <div class="legend-item">
                                    <span class="legend-color available"></span>
                                    <span>Disponible</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color occupied"></span>
                                    <span>Ocupado</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color today"></span>
                                    <span>Hoy</span>
                                </div>
                            </div>
                            
                            <div class="car-selector mt-3">
                                <label for="car-filter" class="form-label">Filtrar por Vehículo:</label>
                                <select id="car-filter" class="form-select">
                                    <option value="">Todos los vehículos</option>
                                </select>
                            </div>
                            
                            <div class="rental-details mt-3" id="rental-details" style="display: none;">
                                <h6>Detalles del Alquiler</h6>
                                <div id="rental-info"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin Tab 2: Calendario de Disponibilidad -->
    </div>
    <!-- Fin Sistema de Tabs -->
</div>


<!-- Modal para cambiar estado de pago -->
<div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentStatusModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">payment</span>
                    Cambiar Estado de Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentStatusForm" enctype="multipart/form-data">
                    <input type="hidden" id="rentalId" name="rentalId">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="rentalIdDisplay" class="form-label">ID Alquiler</label>
                            <input type="text" class="form-control" id="rentalIdDisplay" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="currentStatus" class="form-label">Estado Actual</label>
                            <input type="text" class="form-control" id="currentStatus" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>
                            Nuevo Estado de Pago
                        </label>
                        <select class="form-select" id="newStatus" name="newStatus" required>
                            <option value="">Seleccione un estado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="reservado">Reservado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comprobanteFile" class="form-label">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">upload_file</span>
                            Comprobante de Pago
                        </label>
                        <input type="file" class="form-control" id="comprobanteFile" name="comprobanteFile" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">
                            Formatos permitidos: JPG, PNG, PDF, DOC, DOCX (máximo 10MB)
                        </div>
                    </div>
                    
                    <div id="currentComprobante" class="mb-3" style="display: none;">
                        <label class="form-label">Comprobante Actual</label>
                        <div id="comprobantePreview" class="border p-2 rounded"></div>
                        <div id="comprobanteActions" class="mt-2" style="display: none;">
                            <a href="#" id="downloadComprobante" class="btn btn-sm btn-outline-primary" target="_blank">
                                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">download</span>
                                Descargar
                            </a>
                            <a href="#" id="viewComprobante" class="btn btn-sm btn-outline-info" target="_blank">
                                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">visibility</span>
                                Ver
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">note</span>
                            Observaciones (Opcional)
                        </label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Agregue cualquier observación sobre el cambio de estado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">close</span>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="savePaymentStatus()">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">save</span>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('Modal element exists:', document.getElementById('paymentStatusModal') !== null);
});

// Función de debug para probar el modal desde la consola
window.testModal = function() {
    console.log('Testing modal...');
    const modalElement = document.getElementById('paymentStatusModal');
    if (modalElement) {
        modalElement.classList.add('debug-modal');
        openPaymentModal({
            getAttribute: function(attr) {
                const testData = {
                    'data-rental-id': '1',
                    'data-current-status': 'pendiente',
                    'data-rental-id-text': 'R123456'
                };
                return testData[attr];
            }
        });
    } else {
        console.error('Modal element not found');
    }
};

function openPaymentModal(button) {
    console.log('openPaymentModal called', button);
    
    const rentalId = button.getAttribute('data-rental-id');
    const currentStatus = button.getAttribute('data-current-status');
    const rentalIdText = button.getAttribute('data-rental-id-text');
    
    console.log('Modal data:', { rentalId, currentStatus, rentalIdText });
    
    // Llenar los campos del modal
    document.getElementById('rentalId').value = rentalId;
    document.getElementById('rentalIdDisplay').value = rentalIdText;
    document.getElementById('currentStatus').value = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
    document.getElementById('newStatus').value = '';
    document.getElementById('comprobanteFile').value = '';
    document.getElementById('observaciones').value = '';
    
    // Cargar comprobante actual si existe
    loadCurrentComprobante(rentalId);
    
    // Mostrar el modal
    const modalElement = document.getElementById('paymentStatusModal');
    console.log('Modal element:', modalElement);
    
    if (modalElement) {
        console.log('Creating Bootstrap modal...');
        console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
        
        // Intentar múltiples métodos para mostrar el modal
        let modalShown = false;
        
        // Método 1: Bootstrap 5 nativo
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal shown using Bootstrap 5 native');
                modalShown = true;
            } catch (e) {
                console.error('Error with Bootstrap 5 native:', e);
            }
        }
        
        // Método 2: jQuery (Bootstrap 4/5 con jQuery)
        if (!modalShown && typeof $ !== 'undefined') {
            try {
                $('#paymentStatusModal').modal('show');
                console.log('Modal shown using jQuery');
                modalShown = true;
            } catch (e) {
                console.error('Error with jQuery modal:', e);
            }
        }
        
        // Método 3: Manual (mostrar usando CSS)
        if (!modalShown) {
            console.log('Using manual modal display');
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Crear backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
            
            // Manejar cierre del modal
            const closeModal = () => {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                const existingBackdrop = document.getElementById('modal-backdrop');
                if (existingBackdrop) {
                    existingBackdrop.remove();
                }
            };
            
            // Event listeners para cerrar
            const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });
            
            // Cerrar al hacer clic en el backdrop
            backdrop.addEventListener('click', closeModal);
            
            modalShown = true;
        }
        
        if (!modalShown) {
            console.error('Failed to show modal with any method');
        }
    } else {
        console.error('Modal element not found');
    }
}

function shareRental(rentalId) {
    // Crear URL para compartir la orden
    const baseUrl = window.location.origin;
    const shareUrl = `${baseUrl}/rental/view?id=${rentalId}`;
    
    // Texto para compartir
    const shareText = `Orden de Alquiler #${rentalId} - Facto Rent a Car`;
    
    // Verificar si el navegador soporta Web Share API
    if (navigator.share) {
        navigator.share({
            title: 'Orden de Alquiler',
            text: shareText,
            url: shareUrl
        }).catch(err => {
            console.log('Error al compartir:', err);
            // Fallback: copiar al portapapeles
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
            showNotification('URL copiada al portapapeles', 'success');
        }).catch(err => {
            console.error('Error al copiar:', err);
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('URL copiada al portapapeles', 'success');
    } catch (err) {
        console.error('Error al copiar:', err);
        showNotification('Error al copiar la URL', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function loadCurrentComprobante(rentalId) {
    // Hacer petición para obtener información del comprobante actual
    fetch(`<?= Url::to(['rental/get-comprobante-info']) ?>?id=${rentalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comprobante) {
                const container = document.getElementById('comprobantePreview');
                container.innerHTML = '';
                
                if (data.comprobante.isImage) {
                    // Mostrar imagen
                    const img = document.createElement('img');
                    img.src = data.comprobante.url;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '200px';
                    img.className = 'img-thumbnail';
                    container.appendChild(img);
                } else {
                    // Mostrar icono de documento
                    const icon = document.createElement('span');
                    icon.className = 'material-symbols-outlined';
                    icon.style.fontSize = '48px';
                    icon.textContent = 'description';
                    icon.style.color = '#6c757d';
                    container.appendChild(icon);
                    
                    const fileName = document.createElement('div');
                    fileName.textContent = data.comprobante.fileName;
                    fileName.style.fontSize = '12px';
                    fileName.style.marginTop = '8px';
                    fileName.style.color = '#6c757d';
                    container.appendChild(fileName);
                }
                
                const sizeInfo = document.createElement('div');
                sizeInfo.textContent = `Tamaño: ${data.comprobante.sizeFormatted}`;
                sizeInfo.style.fontSize = '10px';
                sizeInfo.style.color = '#6c757d';
                sizeInfo.style.marginTop = '4px';
                container.appendChild(sizeInfo);
                
                // Configurar enlaces de descarga y visualización
                document.getElementById('downloadComprobante').href = data.comprobante.url;
                document.getElementById('viewComprobante').href = data.comprobante.url;
                
                document.getElementById('currentComprobante').style.display = 'block';
                document.getElementById('comprobanteActions').style.display = 'block';
            } else {
                document.getElementById('currentComprobante').style.display = 'none';
                document.getElementById('comprobanteActions').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error al cargar comprobante:', error);
            document.getElementById('currentComprobante').style.display = 'none';
            document.getElementById('comprobanteActions').style.display = 'none';
        });
}

function savePaymentStatus() {
    const form = document.getElementById('paymentStatusForm');
    const formData = new FormData(form);
    
    // Validar que se haya seleccionado un nuevo estado
    const newStatus = document.getElementById('newStatus').value;
    if (!newStatus) {
        alert('Por favor seleccione un nuevo estado de pago.');
        return;
    }
    
    // Mostrar loading
    const saveButton = document.querySelector('#paymentStatusModal .btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Guardando...';
    saveButton.disabled = true;
    
    // Enviar datos
    fetch('<?= Url::to(['rental/update-payment-status']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            alert('Estado de pago actualizado correctamente.');
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentStatusModal'));
            modal.hide();
            
            // Recargar la página para mostrar los cambios
            location.reload();
        } else {
            alert('Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado de pago. Por favor, intente nuevamente.');
    })
    .finally(() => {
        // Restaurar botón
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// Validar tamaño de archivo
document.getElementById('comprobanteFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
            e.target.value = '';
            return;
        }
        
        // Mostrar preview para imágenes
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.style.maxWidth = '200px';
                preview.style.maxHeight = '200px';
                preview.className = 'img-thumbnail';
                
                const container = document.getElementById('comprobantePreview');
                container.innerHTML = '';
                container.appendChild(preview);
                document.getElementById('currentComprobante').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
});

// ========================================
// CALENDARIO DE DISPONIBILIDAD
// ========================================

let currentMonth = new Date();
let selectedCar = '';

// Inicializar calendario
document.addEventListener('DOMContentLoaded', function() {
    // Cargar opciones de vehículos
    loadCarOptions();
    
    // Event listeners para navegación del calendario
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        loadCalendar();
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        loadCalendar();
    });
    
    document.getElementById('currentMonth').addEventListener('click', function() {
        currentMonth = new Date();
        loadCalendar();
    });
    
    // Event listener para filtro de vehículos
    document.getElementById('car-filter').addEventListener('change', function() {
        selectedCar = this.value;
        loadCalendar();
    });
    
    // Event listener para cuando se activa el tab del calendario
    document.getElementById('calendar-tab').addEventListener('shown.bs.tab', function() {
        // Cargar el calendario solo cuando se active el tab
        loadCalendar();
    });
});

function loadCalendar() {
    const monthStr = currentMonth.getFullYear() + '-' + String(currentMonth.getMonth() + 1).padStart(2, '0');
    
    // Mostrar loading
    document.getElementById('availability-calendar').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Cargando calendario...</span>
            </div>
            <p class="mt-2">Cargando disponibilidad...</p>
        </div>
    `;
    
    fetch(`/rental/availability?month=${monthStr}${selectedCar ? '&car_id=' + selectedCar : ''}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCalendar(data.data, monthStr);
            } else {
                console.error('Error loading calendar:', data.message);
                document.getElementById('availability-calendar').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('availability-calendar').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error al cargar el calendario
                </div>
            `;
        });
}

function loadCarOptions() {
    fetch('/car/index')
        .then(response => response.json())
        .then(data => {
            // Esta función necesitaría ser implementada en el controlador de Car
            // Por ahora, usaremos datos estáticos o una consulta directa
            const carSelect = document.getElementById('car-filter');
            
            // Cargar vehículos disponibles (esto se puede mejorar)
            fetch('/rental/get-car-options')
                .then(response => response.json())
                .then(carData => {
                    if (carData.success) {
                        carData.data.forEach(car => {
                            const option = document.createElement('option');
                            option.value = car.id;
                            option.textContent = `${car.nombre} (${car.placa})`;
                            carSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading cars:', error);
                });
        });
}

function renderCalendar(availabilityData, monthStr) {
    const calendarContainer = document.getElementById('availability-calendar');
    
    // Crear encabezado del mes
    const monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    
    const currentDate = new Date(currentMonth);
    const monthName = monthNames[currentDate.getMonth()];
    const year = currentDate.getFullYear();
    
    let html = `
        <div class="calendar-header mb-3">
            <h4 class="text-center">${monthName} ${year}</h4>
        </div>
        <div class="calendar-grid">
    `;
    
    if (selectedCar) {
        // Mostrar calendario para un vehículo específico
        const carData = availabilityData[selectedCar];
        if (carData) {
            html += renderSingleCarCalendar(carData, monthStr);
        }
    } else {
        // Mostrar calendario para todos los vehículos
        html += renderMultiCarCalendar(availabilityData, monthStr);
    }
    
    html += '</div>';
    calendarContainer.innerHTML = html;
}

function renderSingleCarCalendar(carData, monthStr) {
    const today = new Date().toISOString().split('T')[0];
    const startOfMonth = new Date(monthStr + '-01');
    const endOfMonth = new Date(startOfMonth.getFullYear(), startOfMonth.getMonth() + 1, 0);
    
    let html = `
        <div class="single-car-calendar">
            <div class="car-info mb-3">
                <h5>${carData.car.nombre}</h5>
                <p class="text-muted">Placa: ${carData.car.placa}</p>
            </div>
            <div class="calendar-days">
    `;
    
    // Encabezados de días de la semana
    const dayHeaders = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    html += '<div class="day-headers">';
    dayHeaders.forEach(day => {
        html += `<div class="day-header">${day}</div>`;
    });
    html += '</div>';
    
    // Días del mes
    const days = [];
    const current = new Date(startOfMonth);
    
    // Ajustar para empezar en domingo
    const startDay = startOfMonth.getDay();
    for (let i = 0; i < startDay; i++) {
        days.push(null);
    }
    
    while (current <= endOfMonth) {
        days.push(new Date(current));
        current.setDate(current.getDate() + 1);
    }
    
    days.forEach((day, index) => {
        if (index % 7 === 0) {
            html += '<div class="calendar-week">';
        }
        
        if (day) {
            const dateStr = day.toISOString().split('T')[0];
            const isOccupied = carData.occupied_dates.includes(dateStr);
            const isToday = dateStr === today;
            
            let dayClass = 'calendar-day';
            if (isOccupied) dayClass += ' occupied';
            if (isToday) dayClass += ' today';
            
            html += `
                <div class="${dayClass}" data-date="${dateStr}">
                    <div class="day-number">${day.getDate()}</div>
                    ${isOccupied ? '<div class="day-status occupied">O</div>' : ''}
                </div>
            `;
        } else {
            html += '<div class="calendar-day empty"></div>';
        }
        
        if ((index + 1) % 7 === 0) {
            html += '</div>';
        }
    });
    
    html += '</div></div>';
    return html;
}

function renderMultiCarCalendar(availabilityData, monthStr) {
    let html = '<div class="multi-car-calendar">';
    
    Object.values(availabilityData).forEach(carData => {
        html += `
            <div class="car-calendar-item mb-3">
                <div class="car-header">
                    <h6>${carData.car.nombre} (${carData.car.placa})</h6>
                    <div class="car-stats">
                        <span class="badge bg-success">${carData.available_dates.length} disponibles</span>
                        <span class="badge bg-danger">${carData.occupied_dates.length} ocupados</span>
                    </div>
                </div>
                <div class="car-mini-calendar">
                    ${renderCarMiniCalendar(carData, monthStr)}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    return html;
}

function renderCarMiniCalendar(carData, monthStr) {
    const today = new Date().toISOString().split('T')[0];
    const startOfMonth = new Date(monthStr + '-01');
    const endOfMonth = new Date(startOfMonth.getFullYear(), startOfMonth.getMonth() + 1, 0);
    
    let html = '<div class="mini-calendar-grid">';
    
    const current = new Date(startOfMonth);
    while (current <= endOfMonth) {
        const dateStr = current.toISOString().split('T')[0];
        const isOccupied = carData.occupied_dates.includes(dateStr);
        const isToday = dateStr === today;
        
        let dayClass = 'mini-day';
        if (isOccupied) dayClass += ' occupied';
        if (isToday) dayClass += ' today';
        
        html += `
            <div class="${dayClass}" data-date="${dateStr}" title="${dateStr}">
                ${current.getDate()}
            </div>
        `;
        
        current.setDate(current.getDate() + 1);
    }
    
    html += '</div>';
    return html;
}
</script>

<style>
/* ========================================
   ESTILOS DEL CALENDARIO DE DISPONIBILIDAD
   ======================================== */

.availability-legend {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    margin-right: 8px;
    border: 1px solid #ccc;
}

.legend-color.available {
    background-color: #28a745;
}

.legend-color.occupied {
    background-color: #dc3545;
}

.legend-color.today {
    background-color: #007bff;
}

.calendar-grid {
    margin-top: 15px;
}

.single-car-calendar .calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.day-headers {
    display: contents;
}

.day-header {
    background: #343a40;
    color: white;
    padding: 8px;
    text-align: center;
    font-weight: bold;
    font-size: 12px;
}

.calendar-week {
    display: contents;
}

.calendar-day {
    background: white;
    border: 1px solid #dee2e6;
    padding: 8px;
    min-height: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
}

.calendar-day:hover {
    background: #e9ecef;
    transform: scale(1.05);
}

.calendar-day.occupied {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.calendar-day.occupied:hover {
    background: #f1aeb5;
}

.calendar-day.today {
    background: #cce7ff;
    color: #004085;
    border-color: #007bff;
    font-weight: bold;
}

.calendar-day.today:hover {
    background: #b3d7ff;
}

.calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
}

.calendar-day.empty:hover {
    background: transparent;
    transform: none;
}

.day-number {
    font-size: 14px;
    font-weight: 500;
}

.day-status {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    color: white;
}

.day-status.occupied {
    background: #dc3545;
}

.multi-car-calendar .car-calendar-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: white;
}

.car-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
    gap: 10px;
}

.car-header h6 {
    margin: 0;
    flex: 1;
    min-width: 200px;
}

.car-stats {
    display: flex;
    gap: 5px;
}

.car-stats .badge {
    font-size: 11px;
}

.mini-calendar-grid {
    display: grid;
    grid-template-columns: repeat(31, 1fr);
    gap: 1px;
    background: #f8f9fa;
    padding: 8px;
    border-radius: 6px;
    overflow-x: auto;
}

.mini-day {
    background: white;
    border: 1px solid #dee2e6;
    padding: 4px;
    text-align: center;
    font-size: 11px;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mini-day:hover {
    background: #e9ecef;
    transform: scale(1.1);
}

.mini-day.occupied {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.mini-day.occupied:hover {
    background: #f1aeb5;
}

.mini-day.today {
    background: #cce7ff;
    color: #004085;
    border-color: #007bff;
    font-weight: bold;
}

.mini-day.today:hover {
    background: #b3d7ff;
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 30px;
        padding: 4px;
    }
    
    .day-number {
        font-size: 12px;
    }
    
    .mini-day {
        min-width: 16px;
        height: 16px;
        font-size: 9px;
        padding: 2px;
    }
    
    .car-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .car-stats {
        margin-top: 5px;
    }
}

/* Estilos para el modal de estado de pago */
#paymentStatusModal {
    z-index: 1055 !important;
}

#paymentStatusModal .modal-header {
    background: linear-gradient(135deg, #3fa9f5, #1b305b);
    color: white;
}

#paymentStatusModal .modal-title {
    font-weight: 600;
}

#paymentStatusModal .form-label {
    font-weight: 600;
    color: #495057;
}

#paymentStatusModal .form-control:focus,
#paymentStatusModal .form-select:focus {
    border-color: #3fa9f5;
    box-shadow: 0 0 0 0.2rem rgba(63, 169, 245, 0.25);
}

#paymentStatusModal .btn-primary {
    background: linear-gradient(135deg, #3fa9f5, #1b305b);
    border: none;
}

#paymentStatusModal .btn-primary:hover {
    background: linear-gradient(135deg, #1b305b, #3fa9f5);
}

#comprobantePreview {
    background: #f8f9fa;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Animaciones */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.form-control, .form-select {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Estilos para modal manual */
#paymentStatusModal.show {
    display: block !important;
}

#paymentStatusModal.show .modal-dialog {
    transform: none;
}

.modal-backdrop {
    z-index: 1050;
}

/* Debug styles */
.debug-modal {
    border: 2px solid red !important;
}

.debug-modal * {
    border: 1px solid blue !important;
}
</style>

<?php
// JavaScript para verificar si el PDF existe y mostrar el botón
$this->registerJs('
$(document).ready(function() {
    // Verificar cada botón PDF
    $(".pdf-btn-hide").each(function() {
        var btn = $(this);
        var rentalId = btn.data("rental-id");
        
        // Verificar si el PDF existe
        $.ajax({
            url: "/pdf/check-rental-pdf?id=" + rentalId,
            type: "GET",
            success: function(response) {
                try {
                    var data = typeof response === "string" ? JSON.parse(response) : response;
                    if (data.exists) {
                        // Mostrar el botón si el PDF existe
                        btn.show();
                    }
                } catch (e) {
                    console.error("Error checking PDF:", e);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error checking PDF:", error);
            }
        });
    });
});
');
?>

<!-- Modal de Acciones para Móvil -->
<div class="modal fade" id="actionsModal" tabindex="-1" aria-labelledby="actionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #3fa9f5 0%, #1b305b 100%); color: white;">
                <h5 class="modal-title" id="actionsModalLabel">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">settings</span>
                    Menú de Acciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="actionsModalBody">
                <!-- Contenido dinámico se cargará aquí -->
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript para manejar el modal de acciones en móvil
$this->registerJs('
$(document).ready(function() {
    // En pantallas pequeñas, interceptar clic en el card y abrir modal
    if (window.innerWidth <= 768) {
        $(".rental-accordion-item").on("click", function(e) {
            // Solo abrir modal si se hace clic en el header del acordeón
            if ($(e.target).closest(".accordion-header").length && !$(e.target).closest("a, button").length) {
                var item = $(this);
                var rentalId = item.data("rental-id");
                
                // Evitar que el acordeón normal se ejecute
                e.stopPropagation();
                e.preventDefault();
                
                // Obtener todas las acciones del item
                var actions = item.find(".accordion-actions").html();
                
                // Crear contenido del modal con mejor estructura
                var modalBody = $("#actionsModalBody");
                modalBody.html(`
                    <div class="actions-modal-content">
                        ${actions}
                    </div>
                `);
                
                // Ajustar estilos de los botones en el modal
                modalBody.find(".action-btn").each(function() {
                    var $btn = $(this);
                    var title = $btn.attr("title") || $btn.find(".material-symbols-outlined").attr("aria-label") || "Acción";
                    
                    // Agregar label si no tiene texto visible
                    if (!$btn.text().trim()) {
                        var icon = $btn.html();
                        $btn.html(icon + \'<span class="action-label">\' + title + \'</span>\');
                    }
                    
                    // Hacer que el botón sea más grande y visible
                    $btn.css({
                        "min-height": "80px",
                        "width": "100%",
                        "justify-content": "center",
                        "flex-direction": "column"
                    });
                });
                
                // Mostrar modal
                var modal = new bootstrap.Modal(document.getElementById("actionsModal"));
                modal.show();
            }
        });
    }
});
');
?>

<style>
/* Estilos para el modal de acciones móvil */
@media (max-width: 768px) {
    #actionsModal .modal-dialog {
        margin: 0;
    }
    
    #actionsModal .modal-content {
        min-height: 100vh;
        border-radius: 0;
    }
    
    #actionsModal .modal-header {
        border-bottom: 2px solid rgba(255,255,255,0.2);
        padding: 20px;
    }
    
    #actionsModal .modal-body {
        padding: 20px;
    }
    
    /* Hacer que los botones de acción ocupen todo el ancho en el modal */
    #actionsModalBody .crud-actions-mobile {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 12px;
    }
    
    #actionsModalBody .action-btn {
        width: 100%;
        height: 60px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 500;
    }
    
    #actionsModalBody .action-btn .material-symbols-outlined {
        font-size: 24px;
    }
    
    /* Texto visible debajo de los iconos */
    #actionsModalBody .action-btn .action-label {
        display: block;
        font-size: 11px;
        margin-top: 4px;
    }
}
</style>

