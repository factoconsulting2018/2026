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
            <p class="lead">Bienvenido al sistema de gestión de alquiler de vehículos</p>
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

        <!-- Vehículos Disponibles -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #3179b8 0%, #2a6199 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">directions_car</span>Vehículos</h6>
                            <h2 class="mt-2"><?= $stats['total_cars'] ?></h2>
                            <small>Disponibles</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">directions_car</span></div>
                    </div>
                    <a href="<?= Url::to(['/car/index']) ?>" class="btn btn-sm btn-light mt-3">Ver todos →</a>
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

        <!-- Total Usuarios -->
        <div class="col-md-3 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #14183d 0%, #0d001e 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">person</span>Usuarios</h6>
                            <h2 class="mt-2"><?= $stats['total_users'] ?></h2>
                            <small>Activos</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">person</span></div>
                    </div>
                    <a href="<?= Url::to(['/user/index']) ?>" class="btn btn-sm btn-light mt-3">Ver todos →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Ventas de Hoy -->
        <div class="col-md-4 mb-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">today</span>Ventas de Hoy</h6>
                            <h2 class="mt-2"><?= $stats['today_sales'] ?></h2>
                            <small>Órdenes del día</small>
                        </div>
                        <div class="fs-1"><span class="material-symbols-outlined" style="font-size: 48px;">today</span></div>
                    </div>
                    <a href="<?= Url::to(['/sale/index']) ?>" class="btn btn-sm btn-light mt-3">Ver ventas →</a>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Últimos Alquileres</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                <tr>
                                    <td><?= Html::encode($rental->rental_id ?? 'R' . $rental->id) ?></td>
                                    <td>
                                        <?php
                                        if ($rental->client_id) {
                                            $client = \app\models\Client::findOne($rental->client_id);
                                            echo $client ? Html::encode($client->fullNameUppercase ?? 'Cliente ' . $rental->client_id) : 'Cliente ' . $rental->client_id;
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($rental->car_id) {
                                            $car = \app\models\Car::findOne($rental->car_id);
                                            echo $car ? Html::encode($car->nombre ?? 'Vehículo ' . $rental->car_id) : 'Vehículo ' . $rental->car_id;
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?= Html::encode($rental->fecha_inicio ? date('d/m/Y', strtotime($rental->fecha_inicio)) : 'N/A') ?></td>
                                    <td><?= Html::encode($rental->fecha_final ? date('d/m/Y', strtotime($rental->fecha_final)) : 'N/A') ?></td>
                                    <td>
                                        <?php
                                        $estado = $rental->estado_pago ?? 'pendiente';
                                        $badges = [
                                            'pagado' => 'bg-success',
                                            'pendiente' => 'bg-warning',
                                            'reservado' => 'bg-info',
                                            'cancelado' => 'bg-danger',
                                        ];
                                        $badge = $badges[$estado] ?? 'bg-secondary';
                                        echo '<span class="badge ' . $badge . '">' . Html::encode(ucfirst($estado)) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?= Url::to(['/rental/view', 'id' => $rental->id]) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
</style>

<?php
// Cargar el archivo JavaScript externo para el tema
$this->registerJsFile('@web/js/theme-manager.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
