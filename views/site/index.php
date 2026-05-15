<?php
/** @var yii\web\View $this */
/** @var array $stats */
/** @var array $recentRentals */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><span class="material-symbols-outlined" style="font-size: 36px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">dashboard</span>Panel de Control</h1>
            <div style="margin-top: -8px; margin-bottom: 8px;">
                <span style="font-size: 14px; color: #6c757d; font-weight: 500;">
                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 5px;">info</span>
                    Versión <?= Yii::$app->params['appVersion'] ?? '1.6' ?>
                </span>
            </div>
            <p class="lead">Bienvenido al sistema de gestión de alquiler de vehículos</p>
        </div>
        <div class="btn-group" role="group" aria-label="Acciones rápidas">
            <a href="<?= Url::to(['/notes/index']) ?>" class="btn" style="background: linear-gradient(135deg, #6f42c1 0%, #5936a2 100%); color: #fff; border: none;">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">sticky_note_2</span>
                Notas
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Total Clientes -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #3fa9f5 0%, #3891d6 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">group</span>Clientes</h6>
                            <h2 class="mt-2"><?= $stats['total_clients'] ?></h2>
                            <small>Registrados</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">group</span></div>
                    </div>
                    <a href="<?= Url::to(['/client/index']) ?>" class="btn btn-sm btn-light mt-3">Ver todos →</a>
                </div>
            </div>
        </div>

        <!-- Ventas de Hoy (monto) -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">point_of_sale</span>Ventas de Hoy</h6>
                            <h2 class="mt-2">₡<?= number_format($stats['today_revenue'], 2) ?></h2>
                            <small>Monto total vendido</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">point_of_sale</span></div>
                    </div>
                    <a href="<?= Url::to(['/reports/ventas2-report', 'format' => 'excel']) ?>" class="btn btn-sm btn-light mt-3">Ver detalle →</a>
                </div>
            </div>
        </div>

        <!-- Rentas Asincrónicas -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #ff6600 0%, #d9480f 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">history</span>Rentas Asincrónicas</h6>
                            <h2 class="mt-2"><?= $stats['async_sales'] ?></h2>
                            <small>Registradas</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">history</span></div>
                    </div>
                    <a href="<?= Url::to(['/async-rental/index']) ?>" class="btn btn-sm btn-light mt-3">Ver órdenes →</a>
                </div>
            </div>
        </div>

        <!-- Alquileres Activos -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #22487a 0%, #1b305b 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">receipt_long</span>Alquileres</h6>
                            <h2 class="mt-2"><?= $stats['active_rentals'] ?></h2>
                            <small>Activos</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">receipt_long</span></div>
                    </div>
                    <a href="<?= Url::to(['/rental/index']) ?>" class="btn btn-sm btn-light mt-3">Ver todos →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Órdenes de Hoy (cantidad) -->
        <div class="col-md-4 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">today</span>Órdenes de Hoy</h6>
                            <h2 class="mt-2"><?= $stats['today_sales'] ?></h2>
                            <small>Órdenes generadas</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">today</span></div>
                    </div>
                    <a href="<?= Url::to(['/rental/index']) ?>" class="btn btn-sm btn-light mt-3">Ver órdenes →</a>
                </div>
            </div>
        </div>

        <!-- Ventas del Mes -->
        <div class="col-md-4 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">calendar_month</span>Ventas del Mes</h6>
                            <h2 class="mt-2">₡<?= number_format($stats['month_revenue'], 2) ?></h2>
                            <small>Ingresos del mes</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">calendar_month</span></div>
                    </div>
                    <a href="<?= Url::to(['/sale/index']) ?>" class="btn btn-sm btn-light mt-3">Ver reportes →</a>
                </div>
            </div>
        </div>

        <!-- Órdenes Pendientes -->
        <div class="col-md-4 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">pending_actions</span>Órdenes Pendientes</h6>
                            <h2 class="mt-2"><?= $stats['pending_orders'] ?></h2>
                            <small>Por procesar</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">pending_actions</span></div>
                    </div>
                    <a href="<?= Url::to(['/rental/index', 'estado_pago' => 'pendiente']) ?>" class="btn btn-sm btn-light mt-3">Ver pendientes →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">flash_on</span>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?= Url::to(['/client/create']) ?>" class="btn btn-outline-primary w-100">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">person_add</span>
                                Nuevo Cliente
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= Url::to(['/rental/create']) ?>" class="btn btn-outline-success w-100">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">receipt_long</span>
                                Nuevo Alquiler
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= Url::to(['/car/index']) ?>" class="btn btn-outline-info w-100">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">directions_car</span>
                                Ver Vehículos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($recentRentals)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-recent-rentals-card">
                <div class="card-header">
                    <h5 class="mb-0 dashboard-recent-rentals-title">
                        <span class="material-symbols-outlined align-middle me-1" style="font-size: 20px;">receipt_long</span>
                        Últimos Alquileres
                    </h5>
                </div>
                <div class="card-body p-0 p-md-3">
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRentals as $rental): ?>
                                    <?php
                                    $clientName = $rental->client
                                        ? ($rental->client->fullNameUppercase ?? $rental->client->full_name ?? 'Cliente')
                                        : 'N/A';
                                    $carName = $rental->car ? ($rental->car->nombre ?? 'N/A') : 'N/A';
                                    $fechaInicio = $rental->fecha_inicio ? date('d/m/Y', strtotime($rental->fecha_inicio)) : 'N/A';
                                    $fechaFin = $rental->fecha_final ? date('d/m/Y', strtotime($rental->fecha_final)) : 'N/A';
                                    $estado = $rental->estado_pago ?? 'pendiente';
                                    $badges = [
                                        'pagado' => 'bg-success',
                                        'pendiente' => 'bg-warning text-dark',
                                        'reservado' => 'bg-info text-dark',
                                        'cancelado' => 'bg-danger',
                                    ];
                                    $badge = $badges[$estado] ?? 'bg-secondary';
                                    ?>
                                <tr>
                                    <td><?= Html::encode($rental->rental_id ?? 'R' . $rental->id) ?></td>
                                    <td><?= Html::encode($clientName) ?></td>
                                    <td><?= Html::encode($carName) ?></td>
                                    <td><?= Html::encode($fechaInicio) ?></td>
                                    <td><?= Html::encode($fechaFin) ?></td>
                                    <td>
                                        <span class="badge <?= $badge ?>"><?= Html::encode(ucfirst($estado)) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= Url::to(['/rental/view', 'id' => $rental->id]) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-md-none dashboard-recent-rentals-mobile">
                        <div class="accordion accordion-flush" id="dashboardRecentRentalsAccordion">
                            <?php foreach ($recentRentals as $i => $rental):
                                $orderId = $rental->rental_id ?: ('R' . str_pad((string) $rental->id, 6, '0', STR_PAD_LEFT));
                                $clientNameMobile = $rental->client
                                    ? ($rental->client->fullNameUppercase ?? $rental->client->full_name ?? 'Sin cliente')
                                    : 'Sin cliente';
                                $carNameMobile = $rental->car ? ($rental->car->nombre ?? 'Sin vehículo') : 'Sin vehículo';
                                $placaMobile = ($rental->car && $rental->car->placa) ? ' (' . $rental->car->placa . ')' : '';
                                $fechaInicioMobile = $rental->fecha_inicio ? date('d/m/Y', strtotime($rental->fecha_inicio)) : '—';
                                $fechaFinMobile = $rental->fecha_final ? date('d/m/Y', strtotime($rental->fecha_final)) : '—';
                                $estadoMobile = $rental->estado_pago ?? 'pendiente';
                                $badgesMobile = [
                                    'pagado' => 'bg-success',
                                    'pendiente' => 'bg-warning text-dark',
                                    'reservado' => 'bg-info text-dark',
                                    'cancelado' => 'bg-danger',
                                ];
                                $badgeMobile = $badgesMobile[$estadoMobile] ?? 'bg-secondary';
                                $accId = 'dash-rental-acc-' . $rental->id;
                                $headingId = 'dash-rental-heading-' . $rental->id;
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
                                                <div class="fw-semibold dashboard-rental-client-name"><?= Html::encode($clientNameMobile) ?></div>
                                                <div class="dashboard-rental-acc-meta">
                                                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">directions_car</span>
                                                    <?= Html::encode($carNameMobile . $placaMobile) ?>
                                                </div>
                                                <div class="dashboard-rental-acc-meta">
                                                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">calendar_month</span>
                                                    <?= Html::encode($fechaInicioMobile) ?> → <?= Html::encode($fechaFinMobile) ?>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="<?= Html::encode($accId) ?>"
                                         class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                         aria-labelledby="<?= Html::encode($headingId) ?>"
                                         data-bs-parent="#dashboardRecentRentalsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-2 small mb-3">
                                                <div class="col-6">
                                                    <div class="text-muted">Orden</div>
                                                    <div class="fw-semibold"><?= Html::encode($orderId) ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted">Estado</div>
                                                    <span class="badge <?= $badgeMobile ?>"><?= Html::encode(ucfirst($estadoMobile)) ?></span>
                                                </div>
                                            </div>
                                            <?= Html::a(
                                                '<span class="material-symbols-outlined align-middle" style="font-size:18px;">visibility</span> Ver alquiler',
                                                ['/rental/view', 'id' => $rental->id],
                                                ['class' => 'btn btn-outline-primary w-100', 'encode' => false]
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Variables CSS para temas */
:root {
    /* Tema Light (por defecto) */
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --text-primary: #212529;
    --text-secondary: #6c757d;
    --border-color: #dee2e6;
    --card-bg: #ffffff;
    --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Tema Dark */
[data-theme="dark"] {
    --bg-primary: #1a1a1a;
    --bg-secondary: #2d2d2d;
    --text-primary: #ffffff;
    --text-secondary: #adb5bd;
    --border-color: #495057;
    --card-bg: #343a40;
    --shadow: 0 0.125rem 0.25rem rgba(255, 255, 255, 0.075);
}

/* Aplicar variables a elementos */
body {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: background-color 0.3s ease, color 0.3s ease;
}

.site-index {
    background-color: var(--bg-primary);
    transition: background-color 0.3s ease;
}

.card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    box-shadow: var(--shadow);
    transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.text-muted {
    color: var(--text-secondary) !important;
}

.lead {
    color: var(--text-secondary);
}

.table {
    --bs-table-bg: var(--card-bg);
    --bs-table-color: var(--text-primary);
    --bs-table-border-color: var(--border-color);
}

.table th {
    background-color: var(--bg-secondary);
    border-color: var(--border-color);
}

.table td {
    border-color: var(--border-color);
}

/* Botón de tema */
#themeToggle {
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
}

#themeToggle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#themeIcon {
    font-size: 24px;
    transition: transform 0.3s ease;
}

[data-theme="dark"] #themeIcon {
    transform: rotate(180deg);
}

/* Ajustes específicos para tarjetas con gradientes en tema dark */
[data-theme="dark"] .card[style*="gradient"] {
    opacity: 0.9;
}

/* Mejorar contraste en tema dark */
[data-theme="dark"] .btn-light {
    background-color: #495057;
    border-color: #6c757d;
    color: #ffffff;
}

[data-theme="dark"] .btn-light:hover {
    background-color: #6c757d;
    border-color: #adb5bd;
}

/* Últimos alquileres — título y móvil */
.dashboard-recent-rentals-title {
    color: #22487a;
    font-weight: 700;
}

.dashboard-recent-rentals-title .material-symbols-outlined {
    color: #3fa9f5;
}

.dashboard-recent-rentals-mobile .accordion-button {
    white-space: normal;
    line-height: 1.35;
    font-size: 0.92rem;
    padding: 0.75rem 0.85rem;
}

.dashboard-recent-rentals-mobile .accordion-button:not(.collapsed) {
    background-color: #eef4ff;
    color: #1b305b;
    font-weight: 600;
}

.dashboard-rental-client-name {
    color: #22487a !important;
    font-size: 1rem;
}

.dashboard-rental-acc-meta {
    font-size: 0.82rem;
    color: #64748b;
    margin-top: 0.2rem;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

@media (max-width: 767.98px) {
    .dashboard-recent-rentals-title {
        color: #3fa9f5;
        font-size: 1.1rem;
    }

    .dashboard-recent-rentals-card .card-header {
        background: linear-gradient(135deg, #eef4ff 0%, #f8fafc 100%);
        border-bottom: 2px solid #3fa9f5;
    }

    .dashboard-recent-rentals-mobile .accordion-item {
        border-left: 3px solid #3fa9f5;
    }

    .dashboard-recent-rentals-mobile .btn-outline-primary {
        min-height: 44px;
    }
}

[data-theme="dark"] .dashboard-recent-rentals-title {
    color: #3fa9f5;
}

[data-theme="dark"] .dashboard-rental-client-name {
    color: #6eb8ff !important;
}

[data-theme="dark"] .dashboard-recent-rentals-mobile .accordion-button:not(.collapsed) {
    background-color: #2d3748;
    color: #e2e8f0;
}
</style>

<?php
// Cargar el archivo JavaScript externo para el tema
$this->registerJsFile('@web/js/theme-manager.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
