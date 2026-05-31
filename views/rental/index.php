<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\data\ActiveDataProvider $recurringDataProvider */
/** @var int $recurringCount */
/** @var string $status */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Alquileres';
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
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .share-btn:hover::before {
        left: 100%;
    }

    /* Botón PDF */
    .pdf-btn {
        background: linear-gradient(135deg, #9249ff, #6f42c1);
        color: white;
    }
    
    .pdf-btn:hover {
        background: linear-gradient(135deg, #6f42c1, #5a32a3);
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
    
    .pdf-btn:hover::before {
        left: 100%;
    }

    .pdf2-btn {
        background: linear-gradient(135deg, #6F42C1, #5A32A3);
        color: white;
    }
    
    .pdf2-btn:hover {
        background: linear-gradient(135deg, #5A32A3, #4A2A8A);
    }
    
    .pdf2-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .pdf2-btn:hover::before {
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

    /* ========================================
       TABS DE ALQUILERES — color uniforme + texto blanco
       ======================================== */
    #rentalTabs.nav-tabs {
        border-bottom: 2px solid #1b305b;
        gap: 6px;
    }

    #rentalTabs.nav-tabs .nav-link {
        background: linear-gradient(135deg, #3fa9f5 0%, #1b305b 100%);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-bottom: none;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
        padding: 10px 18px;
        transition: filter 0.2s ease, transform 0.2s ease;
        opacity: 0.85;
    }

    #rentalTabs.nav-tabs .nav-link:hover,
    #rentalTabs.nav-tabs .nav-link:focus {
        filter: brightness(1.1);
        opacity: 1;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.4);
    }

    #rentalTabs.nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #1b305b 0%, #3fa9f5 100%);
        color: #ffffff !important;
        opacity: 1;
        border-color: #1b305b;
        box-shadow: 0 4px 12px rgba(27, 48, 91, 0.25);
        transform: translateY(-1px);
    }

    #rentalTabs.nav-tabs .nav-link .material-symbols-outlined {
        color: #ffffff;
        vertical-align: middle;
    }

    #rentalTabs.nav-tabs .nav-link .badge {
        background-color: #ffffff !important;
        color: #1b305b !important;
        font-weight: 700;
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
        
        /* Mejorar espaciado en acordeones */
        .accordion-actions {
            padding: 12px;
        }
        
        /* Ajustar padding de los cards de acordeón */
        .accordion-body {
            padding: 0.75rem !important;
        }

        /* Leyenda de estados: botón compacto en móvil */
        .mobile-legend-trigger {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
        }
    }
');
?>

<div class="rental-index">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">receipt_long</span><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button"
                    id="overdueRentalsBtn"
                    class="btn btn-outline-danger position-relative d-none"
                    onclick="openOverdueRentalsModal()"
                    title="Órdenes vencidas que requieren atención">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">notification_important</span>
                Requieren atención
                <span id="overdueRentalsBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
            </button>
            <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nuevo Alquiler', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
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
            <button class="nav-link" id="recurring-tab" data-bs-toggle="tab" data-bs-target="#recurring-pane" type="button" role="tab" aria-controls="recurring-pane" aria-selected="false">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">repeat</span>
                Solicitudes recurrentes
                <?php if ($recurringCount > 0): ?>
                <span class="badge rounded-pill bg-warning text-dark ms-1"><?= (int) $recurringCount ?></span>
                <?php endif; ?>
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


    <!-- Leyenda móvil: botón + modal -->
    <div class="d-lg-none mb-3 text-end">
        <button type="button" class="btn btn-outline-secondary mobile-legend-trigger" data-bs-toggle="modal" data-bs-target="#statusLegendModal">
            <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">info</span>
            Leyenda
        </button>
    </div>

    <!-- Resumen de Estados (solo desktop/tablet grande) -->
    <div class="card mb-4 d-none d-lg-block">
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
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por ID, cliente, cédula, teléfono, vehículo o placa..." value="<?= Yii::$app->request->get('search', '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>Estado</label>
                    <select name="estado_pago" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= $status === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="pagado" <?= $status === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                        <option value="reservado" <?= $status === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                        <option value="finalizado" <?= $status === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="cancelado" <?= $status === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
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
                        <?php $estadosCerrados = ['cancelado', 'pagado', 'finalizado']; ?>
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
                            if ($fechaFin && $fechaFin < $hoy && !in_array($estado, $estadosCerrados, true)) {
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
                                            <?php if ($model->client): ?>
                                                <span class="client-id">ID: <?= $model->client->id ?></span>
                                                <?php if ($model->client->cedula_fisica): ?>
                                                    <span class="client-cedula"> | <?= Html::encode($model->client->cedula_fisica) ?></span>
                                                <?php endif; ?>
                                                <?php 
                                                $telefono = '';
                                                if ($model->client && !empty($model->client->whatsapp)) {
                                                    $telefono = $model->client->whatsapp;
                                                } elseif ($model->client && !empty($model->client->telefono)) {
                                                    $telefono = $model->client->telefono;
                                                } elseif ($model->client && !empty($model->client->celular)) {
                                                    $telefono = $model->client->celular;
                                                }
                                                if ($telefono): ?>
                                                    <span class="client-phone"> | <?= Html::encode($telefono) ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="client-id">ID: N/A</span>
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
                                            <?php if ($model->isSwapped()): ?>
                                                <?php $repCar = $model->replacementRental->car ?? null; ?>
                                                <span class="badge bg-warning text-dark ms-1" title="Cambio de vehículo">
                                                    <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">published_with_changes</span>
                                                    Cambiado<?= $repCar ? ' a ' . Html::encode($repCar->nombre) : '' ?>
                                                </span>
                                            <?php elseif ($model->isReplacement()): ?>
                                                <span class="badge bg-info text-dark ms-1" title="Reemplazo de <?= Html::encode($model->parentRental->rental_id ?? '') ?>">
                                                    <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">sync_alt</span>
                                                    Reemplazo<?= $model->parentRental ? ' #' . Html::encode($model->parentRental->rental_id) : '' ?>
                                                </span>
                                            <?php endif; ?>
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
                                        <div class="end-date <?= $fechaFin && $fechaFin < $hoy && !in_array($estado, $estadosCerrados, true) ? 'expired' : ($diferencia && $diferencia <= 2 && $estado === 'pagado' ? 'expiring' : '') ?>">
                                            <span class="material-symbols-outlined">stop</span>
                                            <span>
                                                <?php
                                                if ($model->fecha_final) {
                                                    $fechaFinFormatted = date('d/m/Y', strtotime($model->fecha_final));
                                                    $hoyObj = new \DateTime();
                                                    $fechaFinObj = new \DateTime($model->fecha_final);
                                                    
                                                    if ($fechaFinObj->format('Y-m-d') === $hoyObj->format('Y-m-d')) {
                                                        echo '<strong>Hoy (' . $fechaFinFormatted . ')</strong>';
                                                    } elseif ($fechaFinObj < $hoyObj && !in_array($estado, $estadosCerrados, true)) {
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
                                        <span class="amount"><?= number_format($model->total_precio ?? 0, 2) ?></span>
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
                                        <?php if ($model->canSwapVehicle()): ?>
                                        <button type="button" class="action-btn swap-vehicle-btn"
                                                title="Cambiar vehículo"
                                                data-rental-id="<?= $model->id ?>"
                                                onclick="openSwapVehicleModal(<?= $model->id ?>)">
                                            <span class="material-symbols-outlined">directions_car</span>
                                            <span class="material-symbols-outlined" style="font-size:10px;vertical-align:super;">sync</span>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($model->isSwapped()): ?>
                                            <?php if ($model->canUndoSwap()): ?>
                                            <button type="button" class="action-btn undo-swap-btn"
                                                    title="Deshacer cambio de vehículo"
                                                    data-rental-id="<?= $model->id ?>"
                                                    onclick="confirmUndoSwap(<?= $model->id ?>)">
                                                <span class="material-symbols-outlined">undo</span>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="action-btn undo-swap-btn"
                                                    title="No se puede deshacer: el reemplazo tiene precio distinto o ya está facturado."
                                                    disabled
                                                    style="opacity:.45;cursor:not-allowed;">
                                                <span class="material-symbols-outlined">undo</span>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <a href="<?= Url::to(['/pdf/rental-order', 'id' => $model->id]) ?>" class="action-btn pdf-btn" 
                                           title="Descargar PDF de Orden" 
                                           onclick="openPdfChoice(<?= $model->id ?>, <?= ($model->isSwapped() || $model->isReplacement()) ? 'true' : 'false' ?>); return false;">
                                            <span class="material-symbols-outlined">description</span>
                                        </a>
                                        <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" class="action-btn delete-btn" 
                                           title="Eliminar Alquiler"
                                           data-confirm="¿Estás seguro de eliminar este alquiler?" 
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
            $estado = strtolower(trim((string) ($model->estado_pago ?? 'pendiente')));
            if ($estado === '') {
                $estado = 'pendiente';
            }
            $estadoLabels = [
                'pendiente' => 'Pendiente',
                'pagado' => 'Pagado',
                'reservado' => 'Reservado',
                'finalizado' => 'Finalizado',
                'cancelado' => 'Cancelado',
            ];
            $estadosCerrados = ['cancelado', 'pagado', 'finalizado'];
            $estadoLabel = $estadoLabels[$estado] ?? ucfirst($estado);
            $estadoClass = 'estado-' . $estado;

            $hoy = new \DateTime();
            $fechaFin = null;
            $diferencia = null;
            if (!empty($model->fecha_final)) {
                $fechaFin = new \DateTime($model->fecha_final);
                $diferencia = (int) $hoy->diff($fechaFin)->days;
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
                        <div class="accordion-rental-id-status-container">
                            <div class="accordion-rental-id"><?= Html::encode(!empty($model->rental_id) ? $model->rental_id : 'R' . $model->id) ?></div>
                            <div class="accordion-status-badge accordion-status-<?= Html::encode($estado) ?>">
                                <?= Html::encode($estadoLabel) ?>
                            </div>
                        </div>
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
                            <?php if ($model->isSwapped()): ?>
                                <span class="badge bg-warning text-dark mt-1">Cambiado</span>
                            <?php elseif ($model->isReplacement()): ?>
                                <span class="badge bg-info text-dark mt-1">Reemplazo</span>
                            <?php endif; ?>
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
                                    if ($fechaFin instanceof \DateTime && $fechaFin < $hoy && !in_array($estado, $estadosCerrados, true)) {
                                        $fechaFinClass = 'vencida';
                                    } elseif ($fechaFin instanceof \DateTime && $diferencia !== null && $diferencia <= 2 && $estado === 'pagado') {
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
                                            } elseif ($fechaFinObj < $hoyObj && !in_array($estado, $estadosCerrados, true)) {
                                                $diasVencido = $hoyObj->diff($fechaFinObj)->days;
                                                echo '<strong>Vencido hace ' . $diasVencido . ' día' . ($diasVencido != 1 ? 's' : '') . ' (' . $fechaFinFormatted . ')</strong>';
                                            } elseif ($diferencia !== null && $diferencia <= 2 && $estado === 'pagado') {
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
                                        ₡<?= number_format($model->total_precio ?? 0, 2) ?>
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
                                <?php if ($model->canSwapVehicle()): ?>
                                <button type="button" class="action-btn swap-vehicle-btn"
                                        title="Cambiar vehículo"
                                        onclick="openSwapVehicleModal(<?= $model->id ?>)">
                                    <span class="material-symbols-outlined">directions_car</span>
                                    <span class="material-symbols-outlined" style="font-size:10px;vertical-align:super;">sync</span>
                                </button>
                                <?php endif; ?>
                                <?php if ($model->isSwapped()): ?>
                                    <?php if ($model->canUndoSwap()): ?>
                                    <button type="button" class="action-btn undo-swap-btn"
                                            title="Deshacer cambio de vehículo"
                                            onclick="confirmUndoSwap(<?= $model->id ?>)">
                                        <span class="material-symbols-outlined">undo</span>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="action-btn undo-swap-btn"
                                            title="No se puede deshacer: el reemplazo tiene precio distinto o ya está facturado."
                                            disabled
                                            style="opacity:.45;cursor:not-allowed;">
                                        <span class="material-symbols-outlined">undo</span>
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= Url::to(['/pdf/rental-order', 'id' => $model->id]) ?>" class="action-btn pdf-btn" 
                                   title="Descargar PDF de Orden" 
                                   onclick="openPdfChoice(<?= $model->id ?>, <?= ($model->isSwapped() || $model->isReplacement()) ? 'true' : 'false' ?>); return false;">
                                    <span class="material-symbols-outlined">description</span>
                                </a>
                                <a href="<?= $deleteUrl ?>" class="action-btn delete-btn" 
                                   title="Eliminar Alquiler"
                                   data-confirm="¿Estás seguro de eliminar este alquiler?" 
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
        </div>
        <!-- Fin Tab 1: Listado de Alquileres -->

        <!-- Tab 2: Solicitudes recurrentes -->
        <div class="tab-pane fade" id="recurring-pane" role="tabpanel" aria-labelledby="recurring-tab">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">repeat</span>
                        Solicitudes de clientes recurrentes
                    </h5>
                    <span class="badge bg-warning text-dark"><?= (int) $recurringCount ?> pendiente<?= $recurringCount !== 1 ? 's' : '' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if ($recurringCount === 0): ?>
                        <div class="p-4 text-center text-muted">
                            <span class="material-symbols-outlined d-block mb-2" style="font-size: 40px; opacity: 0.5;">inbox</span>
                            No hay solicitudes recurrentes por revisar.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Orden</th>
                                        <th>Cliente</th>
                                        <th>Tipo solicitado</th>
                                        <th>Período</th>
                                        <th>Vehículo</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recurringDataProvider->getModels() as $model): ?>
                                        <?php
                                        $rentalId = !empty($model->rental_id) ? $model->rental_id : ('R' . $model->id);
                                        $client = $model->client;
                                        $clientName = $client
                                            ? Html::encode(trim($client->full_name ?? (($client->nombre ?? '') . ' ' . ($client->apellido ?? ''))))
                                            : 'Cliente no encontrado';
                                        $telefono = '';
                                        if ($client) {
                                            foreach (['whatsapp', 'celular', 'telefono'] as $phoneField) {
                                                $val = trim((string) ($client->{$phoneField} ?? ''));
                                                if ($val !== '') {
                                                    $telefono = $val;
                                                    break;
                                                }
                                            }
                                        }
                                        $phoneDigits = $telefono !== '' ? preg_replace('/\D+/', '', $telefono) : '';
                                        $tipo = trim((string) ($model->tipo_auto_solicitado ?? ''));
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary-emphasis"><?= Html::encode($rentalId) ?></span>
                                                <div class="small text-muted mt-1">Recurrente</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= $clientName ?></div>
                                                <?php if ($telefono !== ''): ?>
                                                    <?php if ($phoneDigits !== '' && strlen($phoneDigits) >= 7): ?>
                                                        <a href="https://wa.me/<?= Html::encode($phoneDigits) ?>" target="_blank" rel="noopener" class="small text-success text-decoration-none">
                                                            <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">chat</span>
                                                            <?= Html::encode($telefono) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="small text-muted"><?= Html::encode($telefono) ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $tipo !== '' ? Html::encode($tipo) : '<span class="text-muted">—</span>' ?></td>
                                            <td>
                                                <div class="small">
                                                    <?= $model->fecha_inicio ? date('d/m/Y', strtotime($model->fecha_inicio)) : '—' ?>
                                                    <?php if ($model->hora_inicio): ?>
                                                        <span class="text-muted"><?= Html::encode($model->hora_inicio) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted">
                                                    → <?= $model->fecha_final ? date('d/m/Y', strtotime($model->fecha_final)) : '—' ?>
                                                    <?php if ($model->hora_final): ?>
                                                        <?= Html::encode($model->hora_final) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">Por asignar</span>
                                            </td>
                                            <td class="text-end">
                                                <?= Html::a(
                                                    '<span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span> Asignar vehículo',
                                                    ['update', 'id' => $model->id],
                                                    ['class' => 'btn btn-sm btn-outline-primary']
                                                ) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($recurringDataProvider->pagination && $recurringDataProvider->pagination->pageCount > 1): ?>
                        <div class="d-flex justify-content-center p-3 border-top">
                            <?= \yii\widgets\LinkPager::widget([
                                'pagination' => $recurringDataProvider->pagination,
                                'options' => ['class' => 'pagination mb-0'],
                                'linkOptions' => ['class' => 'page-link'],
                                'pageCssClass' => 'page-item',
                                'prevPageCssClass' => 'page-item',
                                'nextPageCssClass' => 'page-item',
                                'activePageCssClass' => 'active',
                                'disabledPageCssClass' => 'disabled',
                            ]) ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Fin Tab 2: Solicitudes recurrentes -->

        <!-- Tab 3: Calendario de Disponibilidad -->
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


<!-- Modal: Leyenda de estados (móvil) -->
<div class="modal fade" id="statusLegendModal" tabindex="-1" aria-labelledby="statusLegendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusLegendModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">palette</span>
                    Leyenda de Estados
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <div class="d-flex align-items-center mb-2">
                        <div class="status-indicator bg-warning me-2"></div>
                        <span><strong>Pendiente:</strong> Esperando pago</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="status-indicator bg-success me-2"></div>
                        <span><strong>Pagado:</strong> Alquiler activo</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="status-indicator bg-info me-2"></div>
                        <span><strong>Reservado:</strong> Reserva confirmada</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="status-indicator bg-danger me-2"></div>
                        <span><strong>Cancelado:</strong> Alquiler cancelado</span>
                    </div>
                </div>
                <hr>
                <small class="text-muted d-block mb-1">
                    <span class="text-danger">⚠️</span> <strong>Vencido:</strong> Fecha de entrega pasada
                </small>
                <small class="text-muted d-block">
                    <span class="text-warning">⏰</span> <strong>Por vencer:</strong> Próximo a vencer (2 días o menos)
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal cambio de vehículo -->
<div class="modal fade" id="swapVehicleModal" tabindex="-1" aria-labelledby="swapVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="swapVehicleModalLabel">
                    <span class="material-symbols-outlined" style="font-size:20px;vertical-align:middle;margin-right:8px;">sync_alt</span>
                    Cambio de vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="swapVehicleLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <form id="swapVehicleForm" class="d-none">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                    <input type="hidden" id="swapOriginalRentalId" name="id">
                    <p class="text-muted small" id="swapVehicleSummary"></p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehículo actual</label>
                            <input type="text" class="form-control" id="swapCurrentCar" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="swapNewCarId" class="form-label">Vehículo nuevo *</label>
                            <select class="form-select" id="swapNewCarId" name="new_car_id" required></select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="swapDate" class="form-label">Fecha del cambio *</label>
                            <input type="date" class="form-control" id="swapDate" name="swap_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="swapFechaFinal" class="form-label">Fecha final del alquiler</label>
                            <input type="date" class="form-control" id="swapFechaFinal" name="fecha_final">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="swapReason" class="form-label">Motivo del cambio *</label>
                        <textarea class="form-control" id="swapReason" name="swap_reason" rows="2" required placeholder="Ej.: falla mecánica, cliente solicita otro modelo"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="swapPrecioDia" class="form-label">Precio por día</label>
                            <input type="number" step="0.01" class="form-control" id="swapPrecioDia" name="precio_por_dia">
                        </div>
                        <div class="col-md-4">
                            <label for="swapLugarEntrega" class="form-label">Lugar entrega</label>
                            <input type="text" class="form-control" id="swapLugarEntrega" name="lugar_entrega">
                        </div>
                        <div class="col-md-4">
                            <label for="swapLugarRetiro" class="form-label">Lugar retiro</label>
                            <input type="text" class="form-control" id="swapLugarRetiro" name="lugar_retiro">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="swapComprobante" class="form-label">Comprobante de pago</label>
                            <input type="text" class="form-control" id="swapComprobante" name="comprobante_pago">
                        </div>
                        <div class="col-md-6">
                            <label for="swapEjecutivo" class="form-label">Ejecutivo</label>
                            <input type="text" class="form-control" id="swapEjecutivo" name="ejecutivo">
                        </div>
                    </div>
                </form>
                <div id="swapVehicleError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="swapVehicleSubmitBtn" onclick="submitSwapVehicleForm()">
                    <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;">check</span>
                    Confirmar cambio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal selección de PDF (original / cambio) -->
<div class="modal fade" id="pdfChoiceModal" tabindex="-1" aria-labelledby="pdfChoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfChoiceModalLabel">
                    <span class="material-symbols-outlined" style="font-size:20px;vertical-align:middle;margin-right:8px;">description</span>
                    Descargar PDF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted">Esta orden tiene cambio de vehículo. Elija qué PDF abrir:</p>
                <div class="d-grid gap-2">
                    <a href="#" id="pdfChoiceOriginal" class="btn btn-outline-primary btn-lg" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="vertical-align:middle;">history</span>
                        PDF Original <small id="pdfChoiceOriginalLabel" class="d-block"></small>
                    </a>
                    <a href="#" id="pdfChoiceSwap" class="btn btn-outline-warning btn-lg text-dark" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="vertical-align:middle;">published_with_changes</span>
                        PDF Cambio <small id="pdfChoiceSwapLabel" class="d-block"></small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sub-modal: órdenes en conflicto al elegir un vehículo ocupado en el swap -->
<div class="modal fade" id="carConflictsModal" tabindex="-1" aria-labelledby="carConflictsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="carConflictsModalLabel">
                    <span class="material-symbols-outlined" style="font-size:22px;vertical-align:middle;margin-right:6px;">report</span>
                    Vehículo con órdenes en conflicto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2" id="carConflictsSubtitle"></p>
                <div id="carConflictsLoading" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Cargando...</span></div>
                </div>
                <div id="carConflictsError" class="alert alert-danger d-none"></div>
                <div id="carConflictsList"></div>
                <div id="carConflictsEmpty" class="text-center py-3 d-none text-success fw-bold">
                    <span class="material-symbols-outlined" style="font-size:36px;vertical-align:middle;">check_circle</span>
                    Sin conflictos restantes. Puedes continuar con el cambio.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="carConflictsBackBtn">Elegir otro vehículo</button>
                <button type="button" class="btn btn-success d-none" id="carConflictsContinueBtn">Continuar con este vehículo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal "Órdenes que requieren atención" -->
<div class="modal fade" id="overdueRentalsModal" tabindex="-1" aria-labelledby="overdueRentalsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="overdueRentalsModalLabel">
                    <span class="material-symbols-outlined" style="font-size:22px;vertical-align:middle;margin-right:6px;">notification_important</span>
                    Órdenes que requieren atención
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">
                    Alquileres con fecha final vencida y estado distinto a "Pagado" o "Cancelado".
                    Cierra cada orden marcándola Pagado o Cancelado para que el sistema libere correctamente los vehículos.
                </p>
                <div id="overdueRentalsLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"><span class="visually-hidden">Cargando...</span></div>
                </div>
                <div id="overdueRentalsError" class="alert alert-danger d-none"></div>
                <div id="overdueRentalsEmpty" class="text-center py-4 d-none">
                    <span class="material-symbols-outlined" style="font-size:48px;color:#28a745;">verified</span>
                    <p class="mb-0 text-success fw-bold">No hay órdenes vencidas pendientes. Todo en orden.</p>
                </div>
                <div id="overdueRentalsList"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="overdueDontShowToday">
                    <label class="form-check-label small" for="overdueDontShowToday">No mostrar de nuevo hoy</label>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
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
                        <select class="form-select" id="newStatus" name="newStatus" required onchange="toggleAbonosFields()">
                            <option value="">Seleccione un estado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="reservado">Reservado</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    
                    <!-- Campos de Abonos (solo visible cuando estado es "Reservado") -->
                    <div id="abonosFields" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">payments</span>
                                    Abonos
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label for="abono<?= $i ?>_descripcion" class="form-label">Abono <?= $i ?> Descripción</label>
                                            <input type="text" class="form-control" id="abono<?= $i ?>_descripcion" name="abono<?= $i ?>_descripcion" placeholder="Ej: Anticipo">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="abono<?= $i ?>_monto" class="form-label">Abono <?= $i ?> Monto (₡)</label>
                                            <input type="number" class="form-control" id="abono<?= $i ?>_monto" name="abono<?= $i ?>_monto" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comprobanteFile" class="form-label">
                            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">upload_file</span>
                            Comprobantes de Pago
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
    
    // Limpiar campos de abonos
    for (let i = 1; i <= 5; i++) {
        document.getElementById(`abono${i}_descripcion`).value = '';
        document.getElementById(`abono${i}_monto`).value = '';
    }
    
    // Cargar comprobante actual y abonos si existen
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
    // Hacer petición para obtener información del comprobante actual y abonos
    fetch(`<?= Url::to(['rental/get-comprobante-info']) ?>?id=${rentalId}`)
        .then(response => response.json())
        .then(data => {
            // Cargar comprobante si existe
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
            
            // Cargar abonos anteriores si existen
            if (data.success && data.abonos) {
                for (let i = 0; i < data.abonos.length; i++) {
                    const abono = data.abonos[i];
                    if (abono.descripcion && abono.monto) {
                        document.getElementById(`abono${i + 1}_descripcion`).value = abono.descripcion;
                        document.getElementById(`abono${i + 1}_monto`).value = abono.monto;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar comprobante:', error);
            document.getElementById('currentComprobante').style.display = 'none';
            document.getElementById('comprobanteActions').style.display = 'none';
        });
}

function toggleAbonosFields() {
    const newStatus = document.getElementById('newStatus').value;
    const abonosFields = document.getElementById('abonosFields');
    
    if (newStatus === 'reservado') {
        abonosFields.style.display = 'block';
    } else {
        abonosFields.style.display = 'none';
    }
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

const SWAP_VEHICLE_DATA_URL = <?= json_encode(Url::to(['swap-vehicle-data'])) ?>;
const SWAP_VEHICLE_POST_URL = <?= json_encode(Url::to(['swap-vehicle'])) ?>;
const UNDO_SWAP_URL = <?= json_encode(Url::to(['undo-swap'])) ?>;
const PDF_CHOICES_URL = <?= json_encode(Url::to(['pdf-choices'])) ?>;
const GET_AVAILABLE_CARS_URL = <?= json_encode(Url::to(['get-available-cars'])) ?>;
const OVERDUE_RENTALS_URL = <?= json_encode(Url::to(['overdue-rentals'])) ?>;
const CONFLICTING_RENTALS_URL = <?= json_encode(Url::to(['conflicting-rentals'])) ?>;
const UPDATE_PAYMENT_STATUS_URL = <?= json_encode(Url::to(['update-payment-status'])) ?>;
const RENTAL_UPDATE_URL = <?= json_encode(Url::to(['update'])) ?>;
const OVERDUE_TODAY_KEY = 'overdueRentalsDismissed:' + (new Date().toISOString().slice(0, 10));

let overdueRentalsModalInstance = null;
let carConflictsModalInstance = null;
let carConflictsContext = { carId: null, startDate: null, endDate: null };

function openCarConflictsModal(carId, startDate, endDate) {
    carConflictsContext = { carId, startDate, endDate };
    const modalEl = document.getElementById('carConflictsModal');
    const loading = document.getElementById('carConflictsLoading');
    const errBox = document.getElementById('carConflictsError');
    const list = document.getElementById('carConflictsList');
    const empty = document.getElementById('carConflictsEmpty');
    const continueBtn = document.getElementById('carConflictsContinueBtn');
    const subtitle = document.getElementById('carConflictsSubtitle');
    const car = swapAllCarsCache.byId[carId];

    loading.classList.remove('d-none');
    errBox.classList.add('d-none');
    empty.classList.add('d-none');
    continueBtn.classList.add('d-none');
    list.innerHTML = '';
    const fmtDate = d => d ? new Date(d).toLocaleDateString('es-CR') : '—';
    subtitle.textContent = (car ? (car.nombre + (car.placa ? ' (' + car.placa + ')' : '')) : 'Vehículo') +
        ' tiene órdenes que chocan con ' + fmtDate(startDate) + ' → ' + fmtDate(endDate) +
        '. Cancela o ajusta las fechas para liberarlo.';

    if (!carConflictsModalInstance && typeof bootstrap !== 'undefined') {
        carConflictsModalInstance = new bootstrap.Modal(modalEl);
    }
    carConflictsModalInstance ? carConflictsModalInstance.show() : (window.jQuery && jQuery(modalEl).modal('show'));

    fetchConflictsAndRender();
}

function fetchConflictsAndRender() {
    const { carId, startDate, endDate } = carConflictsContext;
    const loading = document.getElementById('carConflictsLoading');
    const errBox = document.getElementById('carConflictsError');
    const list = document.getElementById('carConflictsList');
    const empty = document.getElementById('carConflictsEmpty');
    const continueBtn = document.getElementById('carConflictsContinueBtn');

    loading.classList.remove('d-none');
    errBox.classList.add('d-none');
    empty.classList.add('d-none');
    continueBtn.classList.add('d-none');
    list.innerHTML = '';

    const params = new URLSearchParams({
        car_id: String(carId),
        start_date: startDate,
        end_date: endDate,
    });
    fetch(CONFLICTING_RENTALS_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (!data.success) {
                errBox.textContent = data.message || 'No se pudieron cargar las órdenes en conflicto.';
                errBox.classList.remove('d-none');
                return;
            }
            renderConflictsList(data.rentals || []);
        })
        .catch(() => {
            loading.classList.add('d-none');
            errBox.textContent = 'Error de conexión.';
            errBox.classList.remove('d-none');
        });
}

function renderConflictsList(rentals) {
    const list = document.getElementById('carConflictsList');
    const empty = document.getElementById('carConflictsEmpty');
    const continueBtn = document.getElementById('carConflictsContinueBtn');
    const escapeHtml = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const fmtDate = d => d ? new Date(d).toLocaleDateString('es-CR') : '—';
    const fmtMoney = n => '₡' + (Number(n) || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const statusClass = {
        pagado: 'bg-success',
        pendiente: 'bg-warning text-dark',
        reservado: 'bg-info text-dark',
        finalizado: 'bg-dark',
        cancelado: 'bg-danger'
    };

    if (!rentals || rentals.length === 0) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        continueBtn.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    continueBtn.classList.add('d-none');

    list.innerHTML = rentals.map(r => `
        <div class="card mb-2 shadow-sm border-warning" data-rental-id="${r.id}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div>
                        <strong class="me-2">${escapeHtml(r.rental_id)}</strong>
                        <span class="badge ${statusClass[r.estado_pago] || 'bg-secondary'}">${escapeHtml(r.estado_pago)}</span>
                    </div>
                    <strong class="text-success">${fmtMoney(r.total_precio)}</strong>
                </div>
                <div class="row small text-muted mb-2">
                    <div class="col-md-6">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">person</span>
                        ${escapeHtml(r.client_name)}${r.client_phone ? ' · ' + escapeHtml(r.client_phone) : ''}
                    </div>
                    <div class="col-md-6">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">event</span>
                        ${fmtDate(r.fecha_inicio)} → ${fmtDate(r.fecha_final)}
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-danger" onclick="cancelConflictingRental(${r.id}, this)">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">cancel</span>
                        Cancelar orden
                    </button>
                    <a href="${r.update_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span>
                        Modificar fechas
                    </a>
                    <a href="${r.view_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span>
                        Ver
                    </a>
                </div>
                <div class="conflict-feedback small mt-2 d-none"></div>
            </div>
        </div>
    `).join('');
}

function cancelConflictingRental(rentalId, btn) {
    if (!confirm('¿Cancelar la orden seleccionada? El vehículo quedará libre para el cambio.')) return;
    const card = btn.closest('.card');
    const feedback = card ? card.querySelector('.conflict-feedback') : null;
    const buttons = card ? card.querySelectorAll('button, a') : [];
    buttons.forEach(b => { if (b.tagName === 'BUTTON') b.disabled = true; });
    if (feedback) {
        feedback.textContent = 'Cancelando...';
        feedback.className = 'conflict-feedback small mt-2 text-muted';
    }

    const formData = new FormData();
    formData.append('rentalId', rentalId);
    formData.append('newStatus', 'cancelado');
    formData.append('observaciones', 'Cancelado para liberar vehículo en cambio de orden');

    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (csrf) headers['X-CSRF-Token'] = csrf.getAttribute('content');

    fetch(UPDATE_PAYMENT_STATUS_URL, { method: 'POST', body: formData, headers })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (card) card.remove();
                fetchConflictsAndRender();
            } else {
                buttons.forEach(b => { if (b.tagName === 'BUTTON') b.disabled = false; });
                if (feedback) {
                    feedback.className = 'conflict-feedback small mt-2 text-danger';
                    feedback.textContent = 'Error: ' + (data.message || 'no se pudo cancelar.');
                }
            }
        })
        .catch(() => {
            buttons.forEach(b => { if (b.tagName === 'BUTTON') b.disabled = false; });
            if (feedback) {
                feedback.className = 'conflict-feedback small mt-2 text-danger';
                feedback.textContent = 'Error de conexión.';
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const backBtn = document.getElementById('carConflictsBackBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            const sel = document.getElementById('swapNewCarId');
            if (sel) sel.value = '';
            if (carConflictsModalInstance) carConflictsModalInstance.hide();
        });
    }
    const continueBtn = document.getElementById('carConflictsContinueBtn');
    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            const sel = document.getElementById('swapNewCarId');
            if (sel) {
                const startDate = document.getElementById('swapDate').value;
                const endDate = document.getElementById('swapFechaFinal').value;
                const excludeCarId = document.getElementById('swapOriginalRentalId').dataset.carId || null;
                loadSwapAvailableCars(startDate, endDate, excludeCarId);
            }
            if (carConflictsModalInstance) carConflictsModalInstance.hide();
        });
    }
});

function fetchOverdueRentals() {
    return fetch(OVERDUE_RENTALS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .catch(() => ({ success: false, count: 0, rentals: [] }));
}

function refreshOverdueBadge() {
    fetchOverdueRentals().then(data => {
        const btn = document.getElementById('overdueRentalsBtn');
        const badge = document.getElementById('overdueRentalsBadge');
        if (!btn || !badge) return;
        const count = (data && data.success) ? (data.count || 0) : 0;
        if (count > 0) {
            badge.textContent = String(count);
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
        return { data, count };
    });
}

function renderOverdueList(rentals) {
    const list = document.getElementById('overdueRentalsList');
    const empty = document.getElementById('overdueRentalsEmpty');
    const escapeHtml = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const fmtDate = d => d ? new Date(d).toLocaleDateString('es-CR') : '—';
    const fmtMoney = n => '₡' + (Number(n) || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const statusClass = {
        pagado: 'bg-success',
        pendiente: 'bg-warning text-dark',
        reservado: 'bg-info text-dark',
        finalizado: 'bg-dark',
        cancelado: 'bg-danger'
    };

    if (!rentals || rentals.length === 0) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    list.innerHTML = rentals.map(r => `
        <div class="card mb-2 shadow-sm border-danger" data-rental-id="${r.id}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div>
                        <strong class="me-2">${escapeHtml(r.rental_id)}</strong>
                        <span class="badge ${statusClass[r.estado_pago] || 'bg-secondary'}">${escapeHtml(r.estado_pago)}</span>
                        <span class="badge bg-danger ms-1">Vencido hace ${r.dias_vencido} día${r.dias_vencido === 1 ? '' : 's'}</span>
                    </div>
                    <strong class="text-success">${fmtMoney(r.total_precio)}</strong>
                </div>
                <div class="row small text-muted mb-2">
                    <div class="col-md-6">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">person</span>
                        ${escapeHtml(r.client_name)}${r.client_phone ? ' · ' + escapeHtml(r.client_phone) : ''}
                    </div>
                    <div class="col-md-6">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">directions_car</span>
                        ${escapeHtml(r.car_name)}${r.car_placa ? ' (' + escapeHtml(r.car_placa) + ')' : ''}
                    </div>
                    <div class="col-md-6">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">event</span>
                        ${fmtDate(r.fecha_inicio)} → ${fmtDate(r.fecha_final)}
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-success" onclick="quickUpdateOverdueStatus(${r.id}, 'pagado', this)">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">check_circle</span>
                        Marcar pagado
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="quickUpdateOverdueStatus(${r.id}, 'cancelado', this)">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">cancel</span>
                        Cancelar
                    </button>
                    <a href="${r.view_url}" class="btn btn-sm btn-outline-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span>
                        Ver
                    </a>
                    <a href="${r.update_url}" class="btn btn-sm btn-outline-secondary">
                        <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span>
                        Editar
                    </a>
                </div>
                <div class="quick-status-feedback small mt-2 d-none"></div>
            </div>
        </div>
    `).join('');
}

function openOverdueRentalsModal() {
    const modalEl = document.getElementById('overdueRentalsModal');
    const loading = document.getElementById('overdueRentalsLoading');
    const errBox = document.getElementById('overdueRentalsError');
    const list = document.getElementById('overdueRentalsList');
    const empty = document.getElementById('overdueRentalsEmpty');

    loading.classList.remove('d-none');
    errBox.classList.add('d-none');
    empty.classList.add('d-none');
    list.innerHTML = '';

    if (!overdueRentalsModalInstance && typeof bootstrap !== 'undefined') {
        overdueRentalsModalInstance = new bootstrap.Modal(modalEl);
    }
    overdueRentalsModalInstance ? overdueRentalsModalInstance.show() : (window.jQuery && jQuery(modalEl).modal('show'));

    fetchOverdueRentals().then(data => {
        loading.classList.add('d-none');
        if (!data || !data.success) {
            errBox.textContent = (data && data.message) || 'No se pudo cargar la lista.';
            errBox.classList.remove('d-none');
            return;
        }
        renderOverdueList(data.rentals || []);
    });
}

function quickUpdateOverdueStatus(rentalId, newStatus, btn) {
    const card = btn.closest('.card');
    const feedback = card ? card.querySelector('.quick-status-feedback') : null;
    const allBtns = card ? card.querySelectorAll('button') : [];
    allBtns.forEach(b => b.disabled = true);
    if (feedback) {
        feedback.textContent = 'Actualizando...';
        feedback.className = 'quick-status-feedback small mt-2 text-muted';
    }

    const formData = new FormData();
    formData.append('rentalId', rentalId);
    formData.append('newStatus', newStatus);
    formData.append('observaciones', 'Cierre rápido desde modal de órdenes vencidas');

    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (csrf) headers['X-CSRF-Token'] = csrf.getAttribute('content');

    fetch(UPDATE_PAYMENT_STATUS_URL, { method: 'POST', body: formData, headers })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (card) {
                    card.classList.add('border-success');
                    if (feedback) {
                        feedback.className = 'quick-status-feedback small mt-2 text-success fw-bold';
                        feedback.textContent = 'Listo. Estado: ' + newStatus + '. Refrescando...';
                    }
                    setTimeout(() => card.remove(), 600);
                }
                setTimeout(() => {
                    refreshOverdueBadge();
                    const remaining = document.querySelectorAll('#overdueRentalsList .card').length;
                    if (remaining === 0) {
                        document.getElementById('overdueRentalsEmpty').classList.remove('d-none');
                    }
                }, 700);
            } else {
                allBtns.forEach(b => b.disabled = false);
                if (feedback) {
                    feedback.className = 'quick-status-feedback small mt-2 text-danger';
                    feedback.textContent = 'Error: ' + (data.message || 'no se pudo actualizar.');
                }
            }
        })
        .catch(() => {
            allBtns.forEach(b => b.disabled = false);
            if (feedback) {
                feedback.className = 'quick-status-feedback small mt-2 text-danger';
                feedback.textContent = 'Error de conexión.';
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const dontShowCheckbox = document.getElementById('overdueDontShowToday');
    if (dontShowCheckbox) {
        dontShowCheckbox.addEventListener('change', function () {
            if (this.checked) {
                try { localStorage.setItem(OVERDUE_TODAY_KEY, '1'); } catch (e) {}
            } else {
                try { localStorage.removeItem(OVERDUE_TODAY_KEY); } catch (e) {}
            }
        });
    }

    fetchOverdueRentals().then(data => {
        const btn = document.getElementById('overdueRentalsBtn');
        const badge = document.getElementById('overdueRentalsBadge');
        if (!btn || !badge || !data || !data.success) return;
        const count = data.count || 0;
        if (count > 0) {
            badge.textContent = String(count);
            btn.classList.remove('d-none');
            let dismissed = false;
            try { dismissed = localStorage.getItem(OVERDUE_TODAY_KEY) === '1'; } catch (e) {}
            if (!dismissed) {
                setTimeout(() => openOverdueRentalsModal(), 600);
            }
        }
    });
});

let swapVehicleModalInstance = null;
let pdfChoiceModalInstance = null;

function openSwapVehicleModal(rentalId) {
    const modalEl = document.getElementById('swapVehicleModal');
    const form = document.getElementById('swapVehicleForm');
    const loading = document.getElementById('swapVehicleLoading');
    const errBox = document.getElementById('swapVehicleError');
    errBox.classList.add('d-none');
    form.classList.add('d-none');
    loading.classList.remove('d-none');

    if (!swapVehicleModalInstance && typeof bootstrap !== 'undefined') {
        swapVehicleModalInstance = new bootstrap.Modal(modalEl);
    }
    swapVehicleModalInstance ? swapVehicleModalInstance.show() : $(modalEl).modal('show');

    fetch(SWAP_VEHICLE_DATA_URL + '?id=' + rentalId, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('d-none');
            if (!data.success) {
                errBox.textContent = data.message || 'No se pudo cargar la orden.';
                errBox.classList.remove('d-none');
                return;
            }
            const r = data.rental;
            document.getElementById('swapOriginalRentalId').value = r.id;
            document.getElementById('swapCurrentCar').value = r.car_name;
            document.getElementById('swapDate').value = r.fecha_inicio;
            document.getElementById('swapDate').min = r.fecha_inicio;
            document.getElementById('swapDate').max = r.fecha_final;
            document.getElementById('swapFechaFinal').value = r.fecha_final;
            document.getElementById('swapPrecioDia').value = r.precio_por_dia;
            document.getElementById('swapLugarEntrega').value = r.lugar_entrega || '';
            document.getElementById('swapLugarRetiro').value = r.lugar_retiro || '';
            document.getElementById('swapComprobante').value = r.comprobante_pago || '';
            document.getElementById('swapEjecutivo').value = r.ejecutivo || '';
            document.getElementById('swapReason').value = '';
            document.getElementById('swapVehicleSummary').textContent =
                'Orden ' + r.rental_id + ' — ' + (r.client_name || '') + '. Se creará una nueva orden; la original no se modifica salvo el registro del cambio.';
            form.classList.remove('d-none');
            loadSwapAvailableCars(r.fecha_inicio, r.fecha_final, r.car_id);
            document.getElementById('swapDate').onchange = function () {
                const fd = document.getElementById('swapFechaFinal').value;
                loadSwapAvailableCars(this.value, fd, r.car_id);
            };
            document.getElementById('swapFechaFinal').onchange = function () {
                loadSwapAvailableCars(document.getElementById('swapDate').value, this.value, r.car_id);
            };
        })
        .catch(() => {
            loading.classList.add('d-none');
            errBox.textContent = 'Error de conexión al cargar datos.';
            errBox.classList.remove('d-none');
        });
}

let swapAllCarsCache = { byId: {}, startDate: null, endDate: null };

function loadSwapAvailableCars(startDate, endDate, excludeCarId) {
    const sel = document.getElementById('swapNewCarId');
    sel.innerHTML = '<option value="">Cargando...</option>';
    swapAllCarsCache = { byId: {}, startDate: startDate, endDate: endDate };
    if (!startDate || !endDate) return;
    const start = new Date(startDate);
    const end = new Date(endDate);
    let duration = Math.max(1, Math.round((end - start) / (86400000)) + 1);
    const params = new URLSearchParams({
        start_date: startDate,
        duration: String(duration),
        include_busy: '1',
    });
    fetch(GET_AVAILABLE_CARS_URL + '?' + params.toString())
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Seleccione vehículo</option>';
            if (!data.success || !data.data) return;
            const available = (data.data.available_cars || []);
            const busy = (data.data.busy_cars || []);
            if (available.length > 0) {
                const og = document.createElement('optgroup');
                og.label = 'Disponibles';
                available.forEach(car => {
                    if (parseInt(car.id, 10) === parseInt(excludeCarId, 10)) return;
                    swapAllCarsCache.byId[car.id] = car;
                    const opt = document.createElement('option');
                    opt.value = car.id;
                    opt.textContent = car.nombre + ' (' + car.placa + ')';
                    opt.dataset.busy = '0';
                    og.appendChild(opt);
                });
                sel.appendChild(og);
            }
            if (busy.length > 0) {
                const og = document.createElement('optgroup');
                og.label = 'Con orden en estas fechas (requiere acción)';
                busy.forEach(car => {
                    if (parseInt(car.id, 10) === parseInt(excludeCarId, 10)) return;
                    swapAllCarsCache.byId[car.id] = car;
                    const opt = document.createElement('option');
                    opt.value = car.id;
                    opt.textContent = car.nombre + ' (' + car.placa + ') — ocupado';
                    opt.dataset.busy = '1';
                    og.appendChild(opt);
                });
                sel.appendChild(og);
            }
            sel.onchange = function () {
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.dataset.busy === '1') {
                    openCarConflictsModal(parseInt(sel.value, 10), startDate, endDate);
                }
            };
        });
}

function submitSwapVehicleForm() {
    const rentalId = document.getElementById('swapOriginalRentalId').value;
    const btn = document.getElementById('swapVehicleSubmitBtn');
    const errBox = document.getElementById('swapVehicleError');
    errBox.classList.add('d-none');
    btn.disabled = true;

    const body = new FormData(document.getElementById('swapVehicleForm'));
    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (csrf) headers['X-CSRF-Token'] = csrf.getAttribute('content');

    fetch(SWAP_VEHICLE_POST_URL + '?id=' + rentalId, { method: 'POST', body: body, headers: headers })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                if (swapVehicleModalInstance) swapVehicleModalInstance.hide();
                openPdfChoice(data.replacement_id || rentalId, true, true);
            } else {
                errBox.textContent = data.message || 'Error al registrar el cambio.';
                errBox.classList.remove('d-none');
            }
        })
        .catch(() => {
            btn.disabled = false;
            errBox.textContent = 'Error de conexión.';
            errBox.classList.remove('d-none');
        });
}

function openPdfChoice(rentalId, hasSwap, reloadAfterClose) {
    const pdfUrl = <?= json_encode(Url::to(['/pdf/rental-order'])) ?> + '?id=' + rentalId;
    if (!hasSwap) {
        downloadPdfDirect(pdfUrl);
        return;
    }
    fetch(PDF_CHOICES_URL + '?id=' + rentalId, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.has_swap) {
                downloadPdfDirect(data.original_pdf_url || pdfUrl);
                return;
            }
            const originalBtn = document.getElementById('pdfChoiceOriginal');
            const swapBtn = document.getElementById('pdfChoiceSwap');
            originalBtn.href = data.original_pdf_url;
            swapBtn.href = data.swap_pdf_url;
            document.getElementById('pdfChoiceOriginalLabel').textContent = data.original_label || '';
            document.getElementById('pdfChoiceSwapLabel').textContent = data.swap_label || '';
            originalBtn.onclick = null;
            swapBtn.onclick = null;
            const modalEl = document.getElementById('pdfChoiceModal');
            if (!pdfChoiceModalInstance && typeof bootstrap !== 'undefined') {
                pdfChoiceModalInstance = new bootstrap.Modal(modalEl);
            }
            if (reloadAfterClose) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    window.location.reload();
                }, { once: true });
            }
            pdfChoiceModalInstance ? pdfChoiceModalInstance.show() : $(modalEl).modal('show');
        })
        .catch(() => downloadPdfDirect(pdfUrl));
}

function downloadPdfDirect(url) {
    var link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    link.rel = 'noopener';
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function confirmUndoSwap(rentalId) {
    if (!confirm('¿Deshacer el cambio de vehículo? La orden volverá al vehículo original y el reemplazo se conservará en el historial como cancelado.')) {
        return;
    }
    const csrf = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
    if (csrf) headers['X-CSRF-Token'] = csrf.getAttribute('content');
    const body = new FormData();

    fetch(UNDO_SWAP_URL + '?id=' + rentalId, { method: 'POST', body: body, headers: headers })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                window.location.reload();
            } else {
                alert((data && data.message) || 'No se pudo deshacer el cambio.');
            }
        })
        .catch(() => alert('Error de conexión al deshacer el cambio.'));
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
