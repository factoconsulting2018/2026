<?php
/** @var yii\web\View $this */
/** @var array $stats */
/** @var array $recentRentals */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$calendarMonthUrl = Url::to(['car/calendar-rentals']);
$calendarDayUrl = Url::to(['car/calendar-day']);
$calendarDispUrl = Url::to(['car/disponibles']);
$jsToday = json_encode(date('Y-m-d'));
$jsMonth = json_encode(date('Y-m'));
$jsFiltro = json_encode(date('Y-m-d'));
$jsMonthUrl = json_encode($calendarMonthUrl);
$jsDayUrl = json_encode($calendarDayUrl);
$jsDispUrl = json_encode($calendarDispUrl);
$jsPayUpdateUrl = json_encode(Url::to(['/rental/update-payment-status']));
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
            <button type="button" id="btn-resumen-dia" class="btn" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: #fff; border: none;">
                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">summarize</span>
                Resumen del día
            </button>
        </div>
    </div>

    <?php
    $counters = [
        [
            'title' => 'Clientes',
            'icon' => 'group',
            'value' => (string) $stats['total_clients'],
            'sub' => 'Registrados',
            'url' => Url::to(['/client/index']),
            'link' => 'Ver todos',
            'gradient' => 'linear-gradient(135deg, #3fa9f5 0%, #3891d6 100%)',
        ],
        [
            'title' => 'Ventas Hoy',
            'icon' => 'point_of_sale',
            'value' => '₡' . number_format($stats['today_revenue'], 0),
            'sub' => 'Monto vendido',
            'url' => Url::to(['/reports/ventas2-report', 'format' => 'excel']),
            'link' => 'Ver detalle',
            'gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
        ],
        [
            'title' => 'Órdenes Hoy',
            'icon' => 'today',
            'value' => (string) $stats['today_sales'],
            'sub' => 'Generadas',
            'url' => Url::to(['/rental/index']),
            'link' => 'Ver órdenes',
            'gradient' => 'linear-gradient(135deg, #17a673 0%, #117a55 100%)',
        ],
        [
            'title' => 'Alquileres',
            'icon' => 'receipt_long',
            'value' => (string) $stats['active_rentals'],
            'sub' => 'Activos',
            'url' => Url::to(['/rental/index']),
            'link' => 'Ver todos',
            'gradient' => 'linear-gradient(135deg, #22487a 0%, #1b305b 100%)',
        ],
        [
            'title' => 'Pendientes',
            'icon' => 'pending_actions',
            'value' => (string) $stats['pending_orders'],
            'sub' => 'Por procesar',
            'url' => Url::to(['/rental/index', 'estado_pago' => 'pendiente']),
            'link' => 'Ver pendientes',
            'gradient' => 'linear-gradient(135deg, #dc3545 0%, #e83e8c 100%)',
        ],
        [
            'title' => 'Ventas del Mes',
            'icon' => 'calendar_month',
            'value' => '₡' . number_format($stats['month_revenue'], 0),
            'sub' => 'Ingresos del mes',
            'url' => Url::to(['/sale/index']),
            'link' => 'Ver reportes',
            'gradient' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)',
        ],
        [
            'title' => 'Asincrónicas',
            'icon' => 'history',
            'value' => (string) $stats['async_sales'],
            'sub' => 'Registradas',
            'url' => Url::to(['/async-rental/index']),
            'link' => 'Ver órdenes',
            'gradient' => 'linear-gradient(135deg, #ff6600 0%, #d9480f 100%)',
        ],
    ];
    ?>

    <!-- ===== Calendario interactivo de alquileres ===== -->
    <div class="card mt-3 mb-4 rentals-calendar-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 22px; color: #3fa9f5;">calendar_month</span>
                <strong>Calendario de alquileres</strong>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="rc-prev" title="Mes anterior">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">chevron_left</span>
                </button>
                <span id="rc-month-label" class="fw-semibold mx-2">—</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="rc-next" title="Mes siguiente">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">chevron_right</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="rc-today" title="Ir a hoy">
                    Hoy
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="rc-legend d-flex flex-wrap gap-3 small text-muted mb-3">
                <span><span class="rc-dot rc-st-pagado"></span> Pagado</span>
                <span><span class="rc-dot rc-st-pendiente"></span> Pendiente</span>
                <span><span class="rc-dot rc-st-reservado"></span> Reservado</span>
                <span><span class="rc-dot rc-st-finalizado"></span> Finalizado</span>
                <span class="ms-auto">
                    <span class="material-symbols-outlined align-middle" style="font-size: 16px;">directions_car</span>
                    Día con alquileres — click para ver detalle
                </span>
            </div>
            <div id="rc-calendar" class="rc-calendar">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <div class="small mt-2">Cargando calendario…</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Modal: detalle de alquileres del día ===== -->
    <div class="modal fade" id="rcDayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable rc-day-modal-dialog">
            <div class="modal-content">
                <div class="modal-header rc-modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);">
                    <div class="rc-modal-title-nav d-flex align-items-center flex-grow-1 me-2">
                        <button type="button" id="rc-day-prev" class="btn rc-day-nav-btn" title="Día anterior" aria-label="Día anterior">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <h5 class="modal-title mb-0" style="color: #ffffff !important;">
                            <span class="material-symbols-outlined align-middle" style="font-size: 22px; margin-right: 6px; color: #ffffff;">event_note</span>
                            <span id="rc-modal-title" style="color: #ffffff;">Alquileres del día</span>
                        </h5>
                        <button type="button" id="rc-day-next" class="btn rc-day-nav-btn" title="Día siguiente" aria-label="Día siguiente">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="rc-modal-body">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <div class="small mt-2">Cargando…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: reportar pago / cambiar estado (desde badge Pendiente) -->
    <div class="modal fade" id="rcPaymentModal" tabindex="-1" aria-labelledby="rcPaymentModalLabel" aria-hidden="true" style="z-index: 1065;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); color: #fff;">
                    <h5 class="modal-title" id="rcPaymentModalLabel" style="color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:20px;vertical-align:middle;margin-right:6px;">payments</span>
                        Reportar pago / cambiar estado
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="rcPaymentForm" enctype="multipart/form-data">
                        <input type="hidden" id="rcPayRentalId" name="rentalId">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="rcPayRentalCode">ID Alquiler</label>
                                <input type="text" class="form-control" id="rcPayRentalCode" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="rcPayCurrentStatus">Estado actual</label>
                                <input type="text" class="form-control" id="rcPayCurrentStatus" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rcPayNewStatus">Nuevo estado de pago</label>
                            <select class="form-select" id="rcPayNewStatus" name="newStatus" required onchange="rcTogglePayAbonos()">
                                <option value="">Seleccione un estado</option>
                                <option value="pagado" selected>Pagado</option>
                                <option value="reservado">Reservado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div id="rcPayAbonosFields" class="mb-3" style="display:none;">
                            <div class="card">
                                <div class="card-header bg-info text-white py-2">
                                    <strong>Abonos</strong>
                                </div>
                                <div class="card-body">
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <div class="row mb-2">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control form-control-sm" name="abono<?= $i ?>_descripcion" placeholder="Abono <?= $i ?> descripción">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control form-control-sm" name="abono<?= $i ?>_monto" step="0.01" placeholder="Monto ₡">
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rcPayComprobante">Comprobante de pago</label>
                            <input type="file" class="form-control" id="rcPayComprobante" name="comprobanteFile"
                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <div class="form-text">JPG, PNG, PDF, DOC, DOCX (máx. 10MB)</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="rcPayObs">Observaciones (opcional)</label>
                            <textarea class="form-control" id="rcPayObs" name="observaciones" rows="2"
                                      placeholder="Detalle del pago, referencia Sinpe, etc."></textarea>
                        </div>
                    </form>
                    <div id="rcPayError" class="alert alert-danger mt-3 d-none mb-0"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="rcPaySaveBtn" onclick="rcSavePaymentStatus()">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">save</span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Contadores (debajo del calendario) ===== -->
    <div class="row g-2 g-md-3 mt-2 dashboard-counters">
        <?php foreach ($counters as $c): ?>
            <div class="col-6 col-sm-4 col-lg-3 col-xxl-2">
                <div class="card text-white dashboard-counter" style="background: <?= $c['gradient'] ?>;">
                    <div class="card-body">
                        <div class="dashboard-counter-row">
                            <div class="dashboard-counter-main">
                                <div class="dashboard-counter-label">
                                    <span class="material-symbols-outlined">
                                        <?= Html::encode($c['icon']) ?>
                                    </span>
                                    <?= Html::encode($c['title']) ?>
                                </div>
                                <div class="dashboard-counter-value"><?= Html::encode($c['value']) ?></div>
                                <div class="dashboard-counter-sub"><?= Html::encode($c['sub']) ?></div>
                            </div>
                            <div class="dashboard-counter-icon">
                                <span class="material-symbols-outlined">
                                    <?= Html::encode($c['icon']) ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= $c['url'] ?>" class="dashboard-counter-link">
                            <?= Html::encode($c['link']) ?> →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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
                                        'finalizado' => 'bg-dark',
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
                                    'finalizado' => 'bg-dark',
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

/* ===== Contadores compactos ===== */
.dashboard-counters .dashboard-counter {
    border: none;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    height: 100%;
    color: #ffffff !important;
}
.dashboard-counters .dashboard-counter,
.dashboard-counters .dashboard-counter * {
    color: #ffffff !important;
}
.dashboard-counters .dashboard-counter .card-body {
    padding: 0.75rem 0.9rem;
}
.dashboard-counter-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
}
.dashboard-counter-main { min-width: 0; }
.dashboard-counter-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-shadow: 0 1px 1px rgba(0,0,0,0.18);
}
.dashboard-counter-label .material-symbols-outlined { font-size: 16px; color: #ffffff !important; }
.dashboard-counter-value {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
    margin-top: 2px;
    color: #ffffff !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-shadow: 0 1px 2px rgba(0,0,0,0.22);
}
.dashboard-counter-sub {
    font-size: 0.72rem;
    color: #ffffff !important;
    line-height: 1.1;
    text-shadow: 0 1px 1px rgba(0,0,0,0.18);
}
.dashboard-counter-icon .material-symbols-outlined {
    font-size: 34px;
    color: #ffffff !important;
    opacity: 0.85;
    text-shadow: 0 2px 3px rgba(0,0,0,0.18);
}
.dashboard-counter-link {
    display: inline-block;
    margin-top: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    color: #ffffff !important;
    background: rgba(0,0,0,0.18);
    padding: 3px 10px;
    border-radius: 12px;
    text-decoration: none;
    transition: background-color .15s ease, transform .15s ease;
}
.dashboard-counter-link:hover {
    background: rgba(0,0,0,0.32);
    color: #ffffff !important;
    transform: translateY(-1px);
}
@media (max-width: 575.98px) {
    .dashboard-counter-value { font-size: 1.25rem; }
    .dashboard-counter-icon .material-symbols-outlined { font-size: 26px; }
    .dashboard-counter-label { font-size: 0.72rem; }
    .dashboard-counter-sub { font-size: 0.66rem; }
}

/* ===== Calendario interactivo ===== */
.rentals-calendar-card .rc-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    vertical-align: middle;
    margin-right: 4px;
}
.rentals-calendar-card .rc-st-pagado    { background: #198754; }
.rentals-calendar-card .rc-st-pendiente { background: #ffc107; }
.rentals-calendar-card .rc-st-reservado { background: #0d6efd; }
.rentals-calendar-card .rc-st-finalizado{ background: #6c757d; }
.rentals-calendar-card .rc-st-cancelado { background: #dc3545; }

.rc-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}
.rc-calendar .rc-dow {
    text-align: center;
    font-weight: 600;
    color: #6c757d;
    padding: 6px 0;
    font-size: 12px;
    text-transform: uppercase;
}
.rc-calendar .rc-day {
    position: relative;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    min-height: 78px;
    padding: 6px;
    background: #fff;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    cursor: default;
    overflow: hidden;
}
.rc-calendar .rc-day.rc-empty {
    background: #f8f9fa;
    border-color: transparent;
}
.rc-calendar .rc-day.rc-clickable { cursor: pointer; }
.rc-calendar .rc-day.rc-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #3fa9f5;
}
.rc-calendar .rc-day.rc-today {
    border: 2px solid #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,0.12);
}
.rc-calendar .rc-day .rc-num {
    font-weight: 700;
    font-size: 13px;
    color: #212529;
}
.rc-calendar .rc-day .rc-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-top: 4px;
}
.rc-calendar .rc-day .rc-icon .material-symbols-outlined { font-size: 24px; }
.rc-calendar .rc-day .rc-count { font-weight: 700; font-size: 14px; }
.rc-calendar .rc-day .rc-bar {
    position: absolute;
    left: 6px; right: 6px; bottom: 4px;
    display: flex;
    gap: 2px;
    height: 4px;
    border-radius: 2px;
    overflow: hidden;
}
.rc-calendar .rc-day .rc-bar > span { height: 100%; }
.rc-calendar .rc-day.rc-bg-pagado    { background: linear-gradient(180deg, #d1e7dd 0%, #ffffff 100%); }
.rc-calendar .rc-day.rc-bg-pendiente { background: linear-gradient(180deg, #fff3cd 0%, #ffffff 100%); }
.rc-calendar .rc-day.rc-bg-reservado { background: linear-gradient(180deg, #cfe2ff 0%, #ffffff 100%); }
.rc-calendar .rc-day.rc-bg-finalizado{ background: linear-gradient(180deg, #e2e3e5 0%, #ffffff 100%); }

@media (max-width: 575.98px) {
    .rc-calendar .rc-day { min-height: 64px; padding: 4px; }
    .rc-calendar .rc-day .rc-icon .material-symbols-outlined { font-size: 20px; }
    .rc-calendar .rc-day .rc-num { font-size: 12px; }
    .rc-calendar .rc-day .rc-count { font-size: 12px; }
    .rentals-calendar-card .rc-legend > span { font-size: 11px; }
}

.rc-modal-header .modal-title,
.rc-modal-header .modal-title * { color: #ffffff !important; }

.rc-modal-title-nav {
    gap: 4px;
    min-width: 0;
}
.rc-modal-title-nav .modal-title {
    flex: 1 1 auto;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rc-day-nav-btn {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 1px solid rgba(255,255,255,0.45);
    border-radius: 10px;
    background: rgba(255,255,255,0.12);
    color: #ffffff !important;
    line-height: 1;
}
.rc-day-nav-btn .material-symbols-outlined {
    font-size: 32px !important;
    color: #ffffff !important;
    line-height: 1;
}
.rc-day-nav-btn:hover,
.rc-day-nav-btn:focus {
    background: rgba(255,255,255,0.28);
    border-color: #ffffff;
    color: #ffffff !important;
    box-shadow: none;
}
.rc-day-nav-btn:active {
    background: rgba(255,255,255,0.38);
}

/* Modal de alquileres del día: más ancho y sin corte horizontal */
.rc-day-modal-dialog {
    max-width: min(1200px, 96vw);
    width: 96vw;
    margin-left: auto;
    margin-right: auto;
}
#rcDayModal .modal-body {
    overflow-x: hidden;
}
#rcDayModal .rc-day-table-wrap {
    overflow-x: visible;
}
#rcDayModal .rc-day-table {
    width: 100%;
    table-layout: fixed;
    margin-bottom: 0.5rem;
}
#rcDayModal .rc-day-table th,
#rcDayModal .rc-day-table td {
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: anywhere;
    white-space: normal;
}
#rcDayModal .rc-day-table th:nth-child(1),
#rcDayModal .rc-day-table td:nth-child(1) { width: 9%; }
#rcDayModal .rc-day-table th:nth-child(2),
#rcDayModal .rc-day-table td:nth-child(2) { width: 18%; }
#rcDayModal .rc-day-table th:nth-child(3),
#rcDayModal .rc-day-table td:nth-child(3) { width: 18%; }
#rcDayModal .rc-day-table th:nth-child(4),
#rcDayModal .rc-day-table td:nth-child(4) { width: 28%; }
#rcDayModal .rc-day-table th:nth-child(5),
#rcDayModal .rc-day-table td:nth-child(5) { width: 10%; }
#rcDayModal .rc-day-table th:nth-child(6),
#rcDayModal .rc-day-table td:nth-child(6) { width: 9%; }
#rcDayModal .rc-day-table th:nth-child(7),
#rcDayModal .rc-day-table td:nth-child(7) { width: 8%; }
#rcDayModal .rc-correa-badge {
    display: inline-block;
    white-space: normal;
    text-align: left;
    line-height: 1.35;
    max-width: 100%;
    font-weight: 600;
}

#rcDayModal .rc-estado-clickable {
    font-size: inherit;
    line-height: inherit;
    padding: 0.35em 0.65em;
}
#rcDayModal .rc-estado-clickable:hover {
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.55);
    filter: brightness(0.97);
}

#rcPaymentModal {
    z-index: 1065;
}

.rc-day-accordion .accordion-item {
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
}
.rc-day-accordion .accordion-button {
    padding: 10px 12px;
    background: #f8f9fa;
    box-shadow: none;
}
.rc-day-accordion .accordion-button:not(.collapsed) {
    background: #e8f0fe;
    color: #12355b;
}
.rc-day-accordion .accordion-button:focus { box-shadow: none; }
.rc-day-accordion .rc-row-head {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    width: 100%;
    padding-right: 8px;
}
.rc-day-accordion .rc-row-head .rc-row-top {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    width: 100%;
}
.rc-day-accordion .rc-row-head .rc-row-id { font-weight: 700; color: #12355b; }
.rc-day-accordion .rc-row-head .rc-row-client { font-weight: 600; }
.rc-day-accordion .rc-row-head .rc-row-state { margin-left: auto; }
.rc-day-accordion .accordion-body { padding: 12px; font-size: 13px; }
.rc-day-accordion .accordion-body dl {
    display: grid;
    grid-template-columns: max-content 1fr;
    gap: 4px 10px;
    margin-bottom: 10px;
}
.rc-day-accordion .accordion-body dt { color: #6c757d; font-weight: 600; }
.rc-day-accordion .accordion-body dd { margin: 0; }
.rc-day-accordion .rc-row-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

[data-theme="dark"] .rc-calendar .rc-day { background: #343a40; border-color: #495057; }
[data-theme="dark"] .rc-calendar .rc-day .rc-num { color: #e9ecef; }
[data-theme="dark"] .rc-calendar .rc-day.rc-empty { background: #2a2d31; }
[data-theme="dark"] .rc-day-accordion .accordion-item { border-color: #495057; }
[data-theme="dark"] .rc-day-accordion .accordion-button { background: #343a40; color: #e9ecef; }
[data-theme="dark"] .rc-day-accordion .accordion-button:not(.collapsed) { background: #2d3748; color: #cfe2ff; }
</style>

<script>
(function () {
    var RC_MONTH_URL = <?= $jsMonthUrl ?>;
    var RC_DAY_URL = <?= $jsDayUrl ?>;
    var RC_DISP_URL = <?= $jsDispUrl ?>;
    var RC_PAY_URL = <?= $jsPayUpdateUrl ?>;
    var RC_TODAY = <?= $jsToday ?>;
    var RC_FROM = <?= $jsFiltro ?>;

    var calRoot = document.getElementById('rc-calendar');
    var labelEl = document.getElementById('rc-month-label');
    var btnPrev = document.getElementById('rc-prev');
    var btnNext = document.getElementById('rc-next');
    var btnToday = document.getElementById('rc-today');
    var modalEl = document.getElementById('rcDayModal');
    var modalBody = document.getElementById('rc-modal-body');
    var modalTitle = document.getElementById('rc-modal-title');
    var btnDayPrev = document.getElementById('rc-day-prev');
    var btnDayNext = document.getElementById('rc-day-next');
    if (!calRoot) return;

    var MONTH_NAMES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var DOW = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

    var currentMonth = <?= $jsMonth ?>;
    var currentModalDate = null;
    var dayFetchSeq = 0;

    function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }

    function shiftMonth(monthStr, delta) {
        var parts = monthStr.split('-');
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) + delta;
        while (m < 1) { m += 12; y--; }
        while (m > 12) { m -= 12; y++; }
        return y + '-' + pad(m);
    }

    function formatHuman(monthStr) {
        var parts = monthStr.split('-');
        return MONTH_NAMES[parseInt(parts[1],10)-1] + ' ' + parts[0];
    }

    function dominantStatus(byStatus) {
        if (!byStatus) return null;
        var best = null, bestN = 0;
        for (var k in byStatus) {
            if (byStatus[k] > bestN) { best = k; bestN = byStatus[k]; }
        }
        return best;
    }

    function buildCalendar(monthStr, data) {
        labelEl.textContent = formatHuman(monthStr);
        var parts = monthStr.split('-');
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);

        var firstDow = new Date(year, month - 1, 1).getDay();
        var leading = (firstDow + 6) % 7;
        var daysInMonth = new Date(year, month, 0).getDate();

        var html = '';
        for (var d = 0; d < DOW.length; d++) {
            html += '<div class="rc-dow">' + DOW[d] + '</div>';
        }
        for (var i = 0; i < leading; i++) {
            html += '<div class="rc-day rc-empty"></div>';
        }
        for (var day = 1; day <= daysInMonth; day++) {
            var key = year + '-' + pad(month) + '-' + pad(day);
            var info = (data && data.days && data.days[key]) ? data.days[key] : null;
            var isToday = (key === RC_TODAY);
            var classes = 'rc-day';
            if (isToday) classes += ' rc-today';

            var inner = '<span class="rc-num">' + day + '</span>';

            if (info && info.total > 0) {
                classes += ' rc-clickable';
                var dom = dominantStatus(info.by_status);
                if (dom) classes += ' rc-bg-' + dom;

                inner += '<div class="rc-icon">'
                    + '<span class="material-symbols-outlined">directions_car</span>'
                    + '<span class="rc-count">' + info.total + '</span>'
                    + '</div>';

                var bar = '<div class="rc-bar">';
                var total = info.total;
                for (var s in info.by_status) {
                    var w = (info.by_status[s] / total * 100).toFixed(1);
                    bar += '<span class="rc-st-' + s + '" style="flex:0 0 ' + w + '%"></span>';
                }
                bar += '</div>';
                inner += bar;

                html += '<div class="' + classes + '" data-date="' + key + '" title="' + info.total + ' alquiler(es)">' + inner + '</div>';
            } else {
                html += '<div class="' + classes + '">' + inner + '</div>';
            }
        }
        var totalCells = leading + daysInMonth;
        var trailing = (7 - (totalCells % 7)) % 7;
        for (var t = 0; t < trailing; t++) {
            html += '<div class="rc-day rc-empty"></div>';
        }
        calRoot.innerHTML = html;
    }

    function loadMonth(monthStr) {
        currentMonth = monthStr;
        calRoot.innerHTML = '<div class="text-center text-muted py-4" style="grid-column: 1 / -1;">'
            + '<div class="spinner-border spinner-border-sm" role="status"></div>'
            + '<div class="small mt-2">Cargando ' + formatHuman(monthStr) + '…</div>'
            + '</div>';
        calRoot.style.display = 'block';

        fetch(RC_MONTH_URL + '?month=' + encodeURIComponent(monthStr) + '&from=' + encodeURIComponent(RC_FROM), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            calRoot.style.display = '';
            buildCalendar(monthStr, data || { days: {} });
        })
        .catch(function (err) {
            calRoot.style.display = '';
            calRoot.innerHTML = '<div class="text-danger small text-center py-3" style="grid-column: 1 / -1;">'
                + 'Error cargando el calendario: ' + (err && err.message ? err.message : 'desconocido')
                + '</div>';
        });
    }

    function estadoBadge(estado, it) {
        var map = {
            'pagado':     ['success', '✅ Pagado'],
            'pendiente':  ['warning', '🟡 Pendiente'],
            'reservado':  ['primary', '📌 Reservado'],
            'finalizado': ['secondary', '🏁 Finalizado'],
            'cancelado':  ['danger', '❌ Cancelado']
        };
        var m = map[estado] || ['secondary', estado || '—'];
        if (estado === 'pendiente' && it && it.id) {
            var code = JSON.stringify(it.rental_id || ('R' + it.id));
            return '<button type="button" class="badge bg-warning text-dark border-0 rc-estado-clickable"'
                + ' style="cursor:pointer;" title="Clic para reportar pago / cambiar estado"'
                + ' onclick="event.preventDefault(); event.stopPropagation(); rcOpenPaymentModal('
                + parseInt(it.id, 10) + ', ' + code + ', \'pendiente\');">'
                + m[1] + '</button>';
        }
        return '<span class="badge bg-' + m[0] + '">' + m[1] + '</span>';
    }

    function formatTime12h(t) {
        if (!t) return '';
        var m = String(t).match(/^(\d{1,2}):(\d{2})/);
        if (!m) return '';
        var h = parseInt(m[1], 10);
        var min = m[2];
        var p = h >= 12 ? 'pm' : 'am';
        h = h % 12; if (h === 0) h = 12;
        return h + ':' + min + ' ' + p;
    }

    function formatDateDMY(s) {
        if (!s) return '';
        var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return s;
        return m[3] + '/' + m[2] + '/' + m[1];
    }

    function formatCorreapartir(raw, refDateStr) {
        if (!raw) return '';
        var s = String(raw).trim();
        if (!s || s.indexOf('0000-00-00') === 0) return '';
        var m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
        if (!m) return s;

        var y = parseInt(m[1], 10);
        var mo = parseInt(m[2], 10);
        var d = parseInt(m[3], 10);
        var target = new Date(y, mo - 1, d);
        if (isNaN(target.getTime())) return s;

        var ref;
        if (refDateStr && /^\d{4}-\d{2}-\d{2}$/.test(refDateStr)) {
            var rm = refDateStr.match(/^(\d{4})-(\d{2})-(\d{2})/);
            ref = new Date(parseInt(rm[1], 10), parseInt(rm[2], 10) - 1, parseInt(rm[3], 10));
        } else {
            var now = new Date();
            ref = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        }

        var timePart = '';
        if (m[4] !== undefined && m[5] !== undefined) {
            timePart = formatTime12h(m[4] + ':' + m[5]);
        }

        var diffDays = Math.round((target.getTime() - ref.getTime()) / 86400000);
        var dayLabel;
        if (diffDays === 0) {
            dayLabel = 'Hoy';
        } else if (diffDays === 1) {
            dayLabel = 'Mañana';
        } else if (diffDays === -1) {
            dayLabel = 'Ayer';
        } else {
            var dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            dayLabel = dias[target.getDay()] + ', ' + d + ' de ' + meses[mo - 1] + ' ' + y;
        }

        return timePart ? (dayLabel + ' ' + timePart) : dayLabel;
    }

    function correapartirBadge(it, refDateStr) {
        if (!it || !it.correapartir_enabled) return '';
        var f = formatCorreapartir(it.fecha_correapartir, refDateStr);
        if (!f) {
            return '<span class="badge bg-warning text-dark rc-correa-badge" title="Corre apartir habilitado">⏰ Corre apartir</span>';
        }
        return '<span class="badge bg-warning text-dark rc-correa-badge" title="Corre apartir">⏰ Corre apartir: ' + f + '</span>';
    }

    function shiftDay(dateStr, delta) {
        var m = String(dateStr || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return dateStr;
        var dt = new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
        dt.setDate(dt.getDate() + delta);
        return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate());
    }

    function showDay(dateStr) {
        if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return;
        currentModalDate = dateStr;
        var fetchId = ++dayFetchSeq;

        // Si el día pertenece a otro mes, sincronizar el calendario de fondo.
        var monthOfDay = dateStr.substring(0, 7);
        if (monthOfDay !== currentMonth) {
            loadMonth(monthOfDay);
        }

        modalTitle.textContent = 'Alquileres del ' + formatDateDMY(dateStr);
        modalBody.innerHTML = '<div class="text-center text-muted py-4">'
            + '<div class="spinner-border spinner-border-sm" role="status"></div>'
            + '<div class="small mt-2">Cargando…</div>'
            + '</div>';

        if (window.bootstrap && window.bootstrap.Modal) {
            var inst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            inst.show();
        } else {
            modalEl.style.display = 'block';
        }

        fetch(RC_DAY_URL + '?fecha=' + encodeURIComponent(dateStr), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (fetchId !== dayFetchSeq) return; // respuesta obsoleta
            if (!data || !data.items || data.items.length === 0) {
                modalBody.innerHTML = '<div class="text-center text-muted py-4">'
                    + '<span class="material-symbols-outlined" style="font-size: 48px; opacity: .5;">event_busy</span>'
                    + '<div class="mt-2">No hay alquileres activos en este día.</div>'
                    + '<div class="text-end mt-3">'
                    + '<a href="' + RC_DISP_URL + '?fecha=' + encodeURIComponent(dateStr) + '" class="btn btn-sm btn-outline-primary">'
                    + '<span class="material-symbols-outlined align-middle" style="font-size:16px;">filter_alt</span> Ver disponibles ese día</a>'
                    + '</div>'
                    + '</div>';
                return;
            }
            var counts = {};
            data.items.forEach(function (it) { counts[it.estado_pago] = (counts[it.estado_pago] || 0) + 1; });
            var summary = '<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">';
            Object.keys(counts).forEach(function (k) {
                summary += estadoBadge(k) + ' <span class="text-muted small">×' + counts[k] + '</span>';
            });
            summary += '<span class="ms-auto text-muted small">' + data.items.length + ' alquiler(es)</span>';
            summary += '</div>';

            var rows = '';
            data.items.forEach(function (it) {
                var horaIni = formatTime12h(it.hora_inicio);
                var horaFin = formatTime12h(it.hora_final);
                var dRange = formatDateDMY(it.fecha_inicio) + (horaIni ? ' ' + horaIni : '')
                    + ' → ' + formatDateDMY(it.fecha_final) + (horaFin ? ' ' + horaFin : '');
                var total = Number(it.total_precio || 0).toLocaleString('es-CR');
                var correa = correapartirBadge(it, RC_TODAY);

                rows += '<tr>'
                    + '<td><strong>' + it.rental_id + '</strong></td>'
                    + '<td><span class="material-symbols-outlined align-middle" style="font-size:16px;color:#3fa9f5;">directions_car</span> '
                        + (it.car_name || '—')
                        + (it.car_placa ? ' <span class="badge bg-secondary ms-1">' + it.car_placa + '</span>' : '')
                    + '</td>'
                    + '<td>' + (it.client_name || '—') + '</td>'
                    + '<td class="small text-muted">' + dRange
                        + (correa ? '<div class="mt-1">' + correa + '</div>' : '')
                    + '</td>'
                    + '<td>' + estadoBadge(it.estado_pago, it) + '</td>'
                    + '<td class="text-end">₡ ' + total + '</td>'
                    + '<td class="text-end text-nowrap">'
                        + '<a href="' + it.view_url + '" class="btn btn-sm btn-outline-primary" title="Ver"><span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span></a> '
                        + '<a href="' + it.update_url + '" class="btn btn-sm btn-outline-secondary" title="Editar"><span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span></a>'
                    + '</td>'
                    + '</tr>';
            });

            var deskTable = '<div class="rc-day-table-wrap d-none d-md-block">'
                + '<table class="table table-sm table-hover align-middle mb-2 rc-day-table">'
                + '<thead class="table-light"><tr>'
                + '<th>Orden</th><th>Vehículo</th><th>Cliente</th><th>Periodo</th><th>Estado</th><th class="text-end">Total</th><th></th>'
                + '</tr></thead>'
                + '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '</div>';

            var accId = 'rcDayAcc_' + Math.random().toString(36).slice(2, 8);
            var accItems = '';
            data.items.forEach(function (it, idx) {
                var horaIni = formatTime12h(it.hora_inicio);
                var horaFin = formatTime12h(it.hora_final);
                var dRange = formatDateDMY(it.fecha_inicio) + (horaIni ? ' ' + horaIni : '')
                    + ' → ' + formatDateDMY(it.fecha_final) + (horaFin ? ' ' + horaFin : '');
                var total = Number(it.total_precio || 0).toLocaleString('es-CR');
                var correa = correapartirBadge(it, RC_TODAY);
                var headerId = accId + '_h_' + idx;
                var bodyId = accId + '_b_' + idx;

                accItems += '<div class="accordion-item">'
                    + '<h2 class="accordion-header" id="' + headerId + '">'
                    + '<button class="accordion-button collapsed" type="button"'
                    + ' data-bs-toggle="collapse" data-bs-target="#' + bodyId + '"'
                    + ' aria-expanded="false" aria-controls="' + bodyId + '">'
                    + '<div class="rc-row-head">'
                    + '<div class="rc-row-top">'
                    + '<span class="rc-row-id">' + it.rental_id + '</span>'
                    + '<span class="rc-row-state">' + estadoBadge(it.estado_pago, it) + '</span>'
                    + '</div>'
                    + '<div class="rc-row-client small text-muted">'
                    + '<span class="material-symbols-outlined align-middle" style="font-size:14px;">person</span> '
                    + (it.client_name || '—')
                    + '</div>'
                    + (correa ? '<div class="mt-1">' + correa + '</div>' : '')
                    + '</div>'
                    + '</button>'
                    + '</h2>'
                    + '<div id="' + bodyId + '" class="accordion-collapse collapse"'
                    + ' aria-labelledby="' + headerId + '" data-bs-parent="#' + accId + '">'
                    + '<div class="accordion-body">'
                    + '<dl>'
                    + '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;color:#3fa9f5;">directions_car</span> Vehículo</dt>'
                    + '<dd>' + (it.car_name || '—')
                    + (it.car_placa ? ' <span class="badge bg-secondary ms-1">' + it.car_placa + '</span>' : '') + '</dd>'
                    + '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;">date_range</span> Periodo</dt>'
                    + '<dd class="small">' + dRange + '</dd>'
                    + (correa
                        ? '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;">schedule</span> Corre apartir</dt><dd>' + correa + '</dd>'
                        : '')
                    + '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;">payments</span> Total</dt>'
                    + '<dd>₡ ' + total + '</dd>'
                    + '</dl>'
                    + '<div class="rc-row-actions">'
                    + '<a href="' + it.view_url + '" class="btn btn-sm btn-outline-primary">'
                    + '<span class="material-symbols-outlined align-middle" style="font-size:16px;">visibility</span> Ver</a>'
                    + '<a href="' + it.update_url + '" class="btn btn-sm btn-outline-secondary">'
                    + '<span class="material-symbols-outlined align-middle" style="font-size:16px;">edit</span> Editar</a>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '</div>';
            });

            var mobileAcc = '<div class="d-md-none">'
                + '<div class="accordion rc-day-accordion" id="' + accId + '">'
                + accItems
                + '</div>'
                + '</div>';

            modalBody.innerHTML = summary
                + deskTable
                + mobileAcc
                + '<div class="text-end mt-3">'
                + '<a href="' + RC_DISP_URL + '?fecha=' + encodeURIComponent(dateStr) + '" class="btn btn-sm btn-outline-primary">'
                + '<span class="material-symbols-outlined align-middle" style="font-size:16px;">filter_alt</span> Ver disponibles ese día</a>'
                + '</div>';
        })
        .catch(function (err) {
            modalBody.innerHTML = '<div class="text-danger small text-center py-3">'
                + 'Error: ' + (err && err.message ? err.message : 'desconocido')
                + '</div>';
        });
    }

    calRoot.addEventListener('click', function (ev) {
        var cell = ev.target.closest('.rc-day.rc-clickable');
        if (!cell) return;
        var d = cell.getAttribute('data-date');
        if (d) showDay(d);
    });

    if (btnDayPrev) {
        btnDayPrev.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (!currentModalDate) return;
            showDay(shiftDay(currentModalDate, -1));
        });
    }
    if (btnDayNext) {
        btnDayNext.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (!currentModalDate) return;
            showDay(shiftDay(currentModalDate, 1));
        });
    }

    // Flechas del teclado mientras el modal está abierto
    document.addEventListener('keydown', function (ev) {
        if (!currentModalDate || !modalEl) return;
        var open = modalEl.classList.contains('show') || modalEl.style.display === 'block';
        if (!open) return;
        var payModal = document.getElementById('rcPaymentModal');
        if (payModal && (payModal.classList.contains('show') || payModal.style.display === 'block')) return;
        if (ev.key === 'ArrowLeft') {
            ev.preventDefault();
            showDay(shiftDay(currentModalDate, -1));
        } else if (ev.key === 'ArrowRight') {
            ev.preventDefault();
            showDay(shiftDay(currentModalDate, 1));
        }
    });

    if (btnPrev) btnPrev.addEventListener('click', function () { loadMonth(shiftMonth(currentMonth, -1)); });
    if (btnNext) btnNext.addEventListener('click', function () { loadMonth(shiftMonth(currentMonth, +1)); });
    if (btnToday) btnToday.addEventListener('click', function () { loadMonth(RC_TODAY.substring(0, 7)); });

    var RC_CSRF_PARAM = <?= json_encode(Yii::$app->request->csrfParam) ?>;
    var RC_CSRF_TOKEN = <?= json_encode(Yii::$app->request->csrfToken) ?>;

    window.rcTogglePayAbonos = function () {
        var sel = document.getElementById('rcPayNewStatus');
        var box = document.getElementById('rcPayAbonosFields');
        if (!sel || !box) return;
        box.style.display = sel.value === 'reservado' ? 'block' : 'none';
    };

    window.rcOpenPaymentModal = function (rentalId, rentalCode, currentStatus) {
        var form = document.getElementById('rcPaymentForm');
        var err = document.getElementById('rcPayError');
        if (err) {
            err.classList.add('d-none');
            err.textContent = '';
        }
        if (form) form.reset();

        document.getElementById('rcPayRentalId').value = rentalId;
        document.getElementById('rcPayRentalCode').value = rentalCode || ('R' + rentalId);
        var st = (currentStatus || 'pendiente');
        document.getElementById('rcPayCurrentStatus').value = st.charAt(0).toUpperCase() + st.slice(1);
        document.getElementById('rcPayNewStatus').value = 'pagado';
        window.rcTogglePayAbonos();

        var el = document.getElementById('rcPaymentModal');
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(el);
            if (!inst) inst = new bootstrap.Modal(el);
            inst.show();
        } else if (typeof $ !== 'undefined') {
            $('#rcPaymentModal').modal('show');
        }
    };

    window.rcSavePaymentStatus = function () {
        var form = document.getElementById('rcPaymentForm');
        var err = document.getElementById('rcPayError');
        var btn = document.getElementById('rcPaySaveBtn');
        if (!form) return;

        var newStatus = document.getElementById('rcPayNewStatus').value;
        if (!newStatus) {
            if (err) {
                err.textContent = 'Seleccione un nuevo estado de pago.';
                err.classList.remove('d-none');
            }
            return;
        }

        var fd = new FormData(form);
        if (RC_CSRF_PARAM && RC_CSRF_TOKEN) {
            fd.set(RC_CSRF_PARAM, RC_CSRF_TOKEN);
        }

        var fileInput = document.getElementById('rcPayComprobante');
        if (fileInput && fileInput.files && fileInput.files[0] && fileInput.files[0].size > 10 * 1024 * 1024) {
            if (err) {
                err.textContent = 'El archivo es demasiado grande (máx. 10MB).';
                err.classList.remove('d-none');
            }
            return;
        }

        var orig = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Guardando…';
        }
        if (err) {
            err.classList.add('d-none');
            err.textContent = '';
        }

        fetch(RC_PAY_URL, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-Token': RC_CSRF_TOKEN || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    var el = document.getElementById('rcPaymentModal');
                    if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var inst = bootstrap.Modal.getInstance(el);
                        if (inst) inst.hide();
                    } else if (typeof $ !== 'undefined') {
                        $('#rcPaymentModal').modal('hide');
                    }
                    if (currentModalDate) {
                        showDay(currentModalDate);
                    }
                    loadMonth(currentMonth);
                } else {
                    if (err) {
                        err.textContent = (data && data.message) ? data.message : 'No se pudo actualizar el estado.';
                        err.classList.remove('d-none');
                    }
                }
            })
            .catch(function (e) {
                if (err) {
                    err.textContent = 'Error de red: ' + (e && e.message ? e.message : 'desconocido');
                    err.classList.remove('d-none');
                }
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            });
    };

    loadMonth(currentMonth);
})();
</script>

<script>
(function () {
    const btn = document.getElementById('btn-resumen-dia');
    if (!btn) return;

    const dailyUrl = <?= json_encode(Url::to(['config/whatsapp-daily-test'])) ?>;
    const csrfParam = <?= json_encode(Yii::$app->request->csrfParam) ?>;
    const csrfToken = <?= json_encode(Yii::$app->request->csrfToken) ?>;

    function showToast(message, type) {
        const el = document.createElement('div');
        el.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger')
            + ' alert-dismissible fade show';
        el.setAttribute('role', 'alert');
        el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;max-width:420px;';
        el.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(el);
        setTimeout(function () {
            if (el.parentNode) el.remove();
        }, 6000);
    }

    btn.addEventListener('click', async function () {
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Enviando…';
        try {
            const body = new URLSearchParams();
            body.set(csrfParam, csrfToken);
            const res = await fetch(dailyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: body.toString(),
            });
            const data = await res.json();
            if (data.success) {
                showToast('✅ ' + (data.message || 'Resumen del día enviado por WhatsApp.'), 'success');
            } else {
                const errs = (data.report && data.report.errors && data.report.errors.length)
                    ? '<br><small>' + data.report.errors.join('<br>') + '</small>'
                    : '';
                showToast('❌ ' + (data.message || 'No se pudo enviar el resumen.') + errs, 'danger');
            }
        } catch (e) {
            showToast('❌ Error al enviar el resumen: ' + (e.message || 'desconocido'), 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
})();
</script>

<?php
// Cargar el archivo JavaScript externo para el tema
$this->registerJsFile('@web/js/theme-manager.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
