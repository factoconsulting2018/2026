<?php
/** @var yii\web\View $this */
/** @var string $start */
/** @var string $end */
/** @var array $rows  Lista de filas ['car' => Car, 'visits' => int, 'rentals' => int, 'conversion' => float] */
/** @var array $top   Top 5 mismas filas */
/** @var array $daily ['YYYY-MM-DD' => int] visitas por día */
/** @var int $totalVisits */
/** @var int $totalRentals */
/** @var int $activePromos */
/** @var int $totalPromos */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Análisis de Campaña Facebook';
$this->params['breadcrumbs'][] = ['label' => 'Vehículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    ['position' => $this::POS_HEAD]
);

$labels = [];
$visitsData = [];
$rentalsData = [];
foreach ($rows as $r) {
    /** @var \app\models\Car $car */
    $car = $r['car'];
    $label = trim((string) $car->nombre);
    if ($label === '') {
        $label = 'Vehículo #' . $car->id;
    }
    $plate = trim((string) $car->placa);
    if ($plate !== '') {
        $label .= ' (' . $plate . ')';
    }
    $labels[] = $label;
    $visitsData[] = (int) $r['visits'];
    $rentalsData[] = (int) $r['rentals'];
}

$dailyLabels = array_keys($daily);
$dailyDataArr = array_values($daily);

$conversionTotal = $totalVisits > 0
    ? round(($totalRentals / max($totalVisits, 1)) * 100, 1)
    : 0.0;
?>

<style>
    .analytics-container .stat-card {
        border-radius: 12px;
        padding: 18px 20px;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .analytics-container .stat-card .stat-label { font-size: 13px; opacity: .85; margin-bottom: 4px; }
    .analytics-container .stat-card .stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .analytics-container .stat-card .stat-sub { font-size: 12px; opacity: .8; margin-top: 4px; }
    .analytics-container .bg-blue   { background: linear-gradient(135deg, #22487a 0%, #0d001e 100%); }
    .analytics-container .bg-green  { background: linear-gradient(135deg, #198754 0%, #0d3a23 100%); }
    .analytics-container .bg-purple { background: linear-gradient(135deg, #6f42c1 0%, #2a1a52 100%); }
    .analytics-container .bg-orange { background: linear-gradient(135deg, #d97706 0%, #5a2f00 100%); }
    .analytics-container .chart-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .analytics-container .chart-card h5 { margin-bottom: 16px; }
    .analytics-container .promo-url-cell {
        font-family: ui-monospace, 'Cascadia Code', Menlo, Consolas, monospace;
        font-size: 12px;
        word-break: break-all;
    }
    .analytics-container .copy-btn {
        font-size: 12px;
        padding: 2px 8px;
    }
</style>

<div class="analytics-container">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="mb-0">
            <span class="material-symbols-outlined" style="font-size:32px; vertical-align: middle;">campaign</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a('← Volver a Vehículos', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <form method="get" class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="start" class="form-control" value="<?= Html::encode($start) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="end" class="form-control" value="<?= Html::encode($end) ?>">
                </div>
                <div class="col-md-6 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <span class="material-symbols-outlined align-middle" style="font-size:18px;">filter_alt</span>
                        Aplicar filtro
                    </button>
                    <?= Html::a(
                        '<span class="material-symbols-outlined align-middle" style="font-size:18px;">calendar_month</span> Mes actual',
                        ['analytics'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                    <?php
                    $prevStart = (new \DateTimeImmutable('first day of last month'))->format('Y-m-d');
                    $prevEnd = (new \DateTimeImmutable('last day of last month'))->format('Y-m-d');
                    ?>
                    <?= Html::a(
                        '<span class="material-symbols-outlined align-middle" style="font-size:18px;">history</span> Mes pasado',
                        ['analytics', 'start' => $prevStart, 'end' => $prevEnd],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card bg-blue">
                <div class="stat-label">Visitas totales</div>
                <div class="stat-value"><?= number_format($totalVisits) ?></div>
                <div class="stat-sub">Período <?= Html::encode($start) ?> → <?= Html::encode($end) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card bg-green">
                <div class="stat-label">Alquileres en período</div>
                <div class="stat-value"><?= number_format($totalRentals) ?></div>
                <div class="stat-sub">Excluye cancelados</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card bg-purple">
                <div class="stat-label">Tasa de conversión</div>
                <div class="stat-value"><?= $conversionTotal ?>%</div>
                <div class="stat-sub">Alquileres / visitas</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card bg-orange">
                <div class="stat-label">Promos activas</div>
                <div class="stat-value"><?= $activePromos ?> <span style="font-size:18px; opacity:.6;">/ <?= $totalPromos ?></span></div>
                <div class="stat-sub">Vehículos con slug</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="chart-card">
                <h5>📊 Visitas por vehículo</h5>
                <canvas id="chartVisits" height="280"></canvas>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="chart-card">
                <h5>🏆 Top 5 más visitados</h5>
                <canvas id="chartTop" height="280"></canvas>
                <?php if (empty($top) || $top[0]['visits'] === 0): ?>
                    <p class="text-muted small mt-2 mb-0">Aún no hay visitas registradas en este período.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="chart-card">
                <h5>🚙 Alquileres por vehículo</h5>
                <canvas id="chartRentals" height="280"></canvas>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="chart-card">
                <h5>📈 Tendencia de visitas</h5>
                <canvas id="chartDaily" height="280"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-card mt-3">
        <h5>🔗 Enlaces de campaña por vehículo</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Vehículo</th>
                        <th>Estado promo</th>
                        <th>Enlace público</th>
                        <th class="text-end">Visitas</th>
                        <th class="text-end">Alquileres</th>
                        <th class="text-end">Conv. %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Aún no hay vehículos con promoción Facebook configurada.
                                <br>
                                <?= Html::a('Configurar el primero →', ['index'], ['class' => 'btn btn-sm btn-primary mt-2']) ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r): ?>
                            <?php
                                /** @var \app\models\Car $car */
                                $car = $r['car'];
                                $promoUrl = $car->getFacebookPromoUrl();
                                if ($promoUrl === null) {
                                    // Si está apagado pero hay slug, mostrar URL igual (sirve histórico).
                                    $slug = trim((string) $car->facebook_promo_slug);
                                    $promoUrl = $slug !== '' ? Url::to(['/promo/' . $slug], true) : null;
                                }
                                $isEnabled = (int) $car->facebook_promo_enabled === 1;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= Html::encode($car->nombre) ?></strong>
                                    <?php if ($car->placa): ?>
                                        <div class="text-muted small"><?= Html::encode($car->placa) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isEnabled): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($promoUrl): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <a class="promo-url-cell" href="<?= Html::encode($promoUrl) ?>" target="_blank" rel="noopener">
                                                <?= Html::encode($promoUrl) ?>
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary btn-sm copy-btn"
                                                    data-url="<?= Html::encode($promoUrl) ?>">Copiar</button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><strong><?= number_format($r['visits']) ?></strong></td>
                                <td class="text-end"><?= number_format($r['rentals']) ?></td>
                                <td class="text-end"><?= $r['conversion'] ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$labelsJson = json_encode($labels, JSON_UNESCAPED_UNICODE);
$visitsJson = json_encode($visitsData);
$rentalsJson = json_encode($rentalsData);
$topLabelsJson = json_encode(array_map(static function ($r) {
    /** @var \app\models\Car $car */
    $car = $r['car'];
    $label = trim((string) $car->nombre);
    if ($label === '') { $label = 'Vehículo #' . $car->id; }
    return $label;
}, $top), JSON_UNESCAPED_UNICODE);
$topVisitsJson = json_encode(array_map(static function ($r) { return (int) $r['visits']; }, $top));
$dailyLabelsJson = json_encode($dailyLabels);
$dailyDataJson = json_encode($dailyDataArr);

$js = <<<JS
(function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no se cargó.');
        return;
    }

    const labels = $labelsJson;
    const visits = $visitsJson;
    const rentals = $rentalsJson;
    const topLabels = $topLabelsJson;
    const topVisits = $topVisitsJson;
    const dailyLabels = $dailyLabelsJson;
    const dailyData = $dailyDataJson;

    const palette = [
        '#22487a', '#198754', '#6f42c1', '#d97706',
        '#0d6efd', '#dc3545', '#20c997', '#fd7e14',
        '#6610f2', '#0dcaf0', '#ffc107', '#e83e8c'
    ];
    function colorFor(i) { return palette[i % palette.length]; }
    function alphaFor(i) { return colorFor(i) + 'cc'; }

    function makeBar(ctxId, labels, data, title, colorHexFn) {
        const el = document.getElementById(ctxId);
        if (!el) return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    backgroundColor: labels.map((_, i) => (colorHexFn || alphaFor)(i)),
                    borderColor: labels.map((_, i) => colorFor(i)),
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    makeBar('chartVisits', labels, visits, 'Visitas');
    makeBar('chartRentals', labels, rentals, 'Alquileres');

    const topEl = document.getElementById('chartTop');
    if (topEl && topLabels.length > 0) {
        new Chart(topEl, {
            type: 'doughnut',
            data: {
                labels: topLabels,
                datasets: [{
                    data: topVisits,
                    backgroundColor: topLabels.map((_, i) => colorFor(i)),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    const dailyEl = document.getElementById('chartDaily');
    if (dailyEl) {
        new Chart(dailyEl, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Visitas',
                    data: dailyData,
                    borderColor: '#22487a',
                    backgroundColor: 'rgba(34,72,122,0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = btn.getAttribute('data-url') || '';
            if (!url) return;
            const restore = function () {
                btn.textContent = 'Copiado';
                setTimeout(function () { btn.textContent = 'Copiar'; }, 1800);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(restore).catch(restore);
            } else {
                restore();
            }
        });
    });
})();
JS;
$this->registerJs($js);
?>
