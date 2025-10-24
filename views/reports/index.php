<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Reportes';
$this->params['breadcrumbs'][] = $this->title;

// Agregar clase específica al body para tema oscuro
$this->registerCss('
    body { 
        background-color: var(--bg-primary) !important; 
    }
    [data-theme="dark"] body { 
        background-color: #1a1a1a !important; 
    }
    [data-theme="light"] body { 
        background-color: #ffffff !important; 
    }
');

// Definir los reportes disponibles
$reports = [
    [
        'id' => 1,
        'name' => 'Ventas',
        'description' => 'Reporte de ventas con colores y diseño mejorado, incluyendo gráficos y análisis visual',
        'icon' => 'bar_chart',
        'color' => 'danger',
        'actions' => [
            'pdf' => ['reports/ventas2-report', 'format' => 'pdf'],
            'excel' => ['reports/ventas2-report', 'format' => 'excel']
        ]
    ],
    [
        'id' => 2,
        'name' => 'Reporte de Órdenes',
        'description' => 'Reporte detallado de todas las órdenes de venta con información completa',
        'icon' => 'shopping_cart',
        'color' => 'success',
        'actions' => [
            'pdf' => ['reports/orders-report', 'format' => 'pdf'],
            'excel' => ['reports/orders-report', 'format' => 'excel'],
            'word' => ['reports/orders-report', 'format' => 'word']
        ]
    ],
    [
        'id' => 3,
        'name' => 'Reporte de Clientes',
        'description' => 'Listado completo de todos los clientes con toda su información de contacto',
        'icon' => 'group',
        'color' => 'info',
        'actions' => [
            'pdf' => ['reports/clients-report', 'format' => 'pdf'],
            'excel' => ['reports/clients-report', 'format' => 'excel'],
            'word' => ['reports/clients-report', 'format' => 'word']
        ]
    ],
    [
        'id' => 4,
        'name' => 'Reporte de Ventas por Cliente',
        'description' => 'Análisis de ventas agrupadas por cliente con totales y estadísticas',
        'icon' => 'analytics',
        'color' => 'warning',
        'actions' => [
            'pdf' => ['reports/sales-by-client-report', 'format' => 'pdf'],
            'excel' => ['reports/sales-by-client-report', 'format' => 'excel'],
            'word' => ['reports/sales-by-client-report', 'format' => 'word']
        ]
    ],
    [
        'id' => 5,
        'name' => 'Calendario Mensual',
        'description' => 'Calendario mensual en Excel mostrando los vehículos alquilados por día',
        'icon' => 'calendar_month',
        'color' => 'secondary',
        'special' => 'calendar'
    ]
];
?>

<div class="reports-index reports-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">assessment</span>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 60%;">Nombre del Reporte</th>
                            <th style="width: 30%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= $report['color'] ?> fs-6"><?= $report['id'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2 text-<?= $report['color'] ?>" style="font-size: 20px;">
                                        <?= $report['icon'] ?>
                                    </span>
                                    <strong><?= $report['name'] ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php if (isset($report['special']) && $report['special'] === 'calendar'): ?>
                                    <!-- Modal para calendario -->
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#calendarModal<?= $report['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">calendar_month</span>
                                        Generar
                                    </button>
                                    
                                    <!-- Modal -->
                                    <div class="modal fade" id="calendarModal<?= $report['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <span class="material-symbols-outlined me-2">calendar_month</span>
                                                        <?= $report['name'] ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="get" action="<?= Url::to(['reports/calendar-report']) ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="year<?= $report['id'] ?>" class="form-label">Año:</label>
                                                            <select name="year" id="year<?= $report['id'] ?>" class="form-select" required>
                                                                <?php for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++): ?>
                                                                    <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="month<?= $report['id'] ?>" class="form-label">Mes:</label>
                                                            <select name="month" id="month<?= $report['id'] ?>" class="form-select" required>
                                                                <?php 
                                                                $months = [
                                                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                                                ];
                                                                foreach ($months as $num => $name): ?>
                                                                    <option value="<?= $num ?>" <?= $num == date('n') ? 'selected' : '' ?>><?= $name ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <small>
                                                                <strong>Nota:</strong> Este reporte generará un archivo Excel con el calendario mensual mostrando los vehículos alquilados por día.
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <span class="material-symbols-outlined me-1" style="font-size: 16px;">download</span>
                                                            Generar Calendario
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Botones de acción para reportes normales -->
                                    <div class="btn-group" role="group">
                                        <?php if (isset($report['actions']['pdf'])): ?>
                                            <a href="<?= Url::to($report['actions']['pdf']) ?>" class="btn btn-outline-danger btn-sm" target="_blank" title="Generar PDF">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">picture_as_pdf</span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (isset($report['actions']['excel'])): ?>
                                            <a href="<?= Url::to($report['actions']['excel']) ?>" class="btn btn-outline-success btn-sm" title="Generar Excel">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">table_chart</span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (isset($report['actions']['word'])): ?>
                                            <a href="<?= Url::to($report['actions']['word']) ?>" class="btn btn-outline-primary btn-sm" title="Generar Word">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
