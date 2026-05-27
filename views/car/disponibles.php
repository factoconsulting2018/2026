<?php
/** @var yii\web\View $this */
/** @var app\models\Car[] $cars */
/** @var string $fecha */
/** @var array<int,int> $rentalsByCar */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Disponibles';
$this->params['breadcrumbs'][] = $this->title;

$fechaLabel = Yii::$app->formatter->asDate($fecha, 'long');
$hoy = date('Y-m-d');
$esHoy = ($fecha === $hoy);

$rentalsByCar = $rentalsByCar ?? [];

$carRentalsUrl = Url::to(['car/car-rentals']);
?>

<div class="car-disponibles">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">
                <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">event_available</span>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted mb-0">
                Vehículos sin alquiler activo el <?= Html::encode($fechaLabel) ?>
                <?php if (!$esHoy): ?>
                    <span class="badge bg-secondary ms-1">no es hoy</span>
                <?php endif; ?>
            </p>
        </div>
        <form method="get" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label small mb-0">Consultar fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= Html::encode($fecha) ?>" max="2099-12-31">
            </div>
            <button type="submit" class="btn btn-primary">Ver</button>
            <?php if (!$esHoy): ?>
                <?= Html::a('Hoy', ['disponibles'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php endif; ?>
        </form>
    </div>

    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <span class="material-symbols-outlined me-2" style="font-size: 22px;">info</span>
        <div class="small">
            El <strong>calendario interactivo</strong> de alquileres se movió al
            <?= Html::a('Panel de Control', ['/site/index'], ['class' => 'fw-semibold']) ?>.
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (count($cars) === 0): ?>
                <div class="p-5 text-center text-muted">
                    <span class="material-symbols-outlined d-block mb-2" style="font-size: 48px; opacity: 0.5;">no_crash</span>
                    No hay vehículos disponibles para esta fecha (o todos están en mantenimiento / fuera de servicio).
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehículo</th>
                                <th>Placa</th>
                                <th class="d-none d-md-table-cell">Pasajeros</th>
                                <th class="d-none d-md-table-cell">Estado en sistema</th>
                                <th class="text-center d-none d-md-table-cell" title="¿Tiene órdenes asociadas?">Reservas</th>
                                <th class="d-none d-lg-table-cell">Empresa</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $labels = [
                                'disponible' => ['class' => 'success', 't' => 'Disponible'],
                                'alquilado' => ['class' => 'warning', 't' => 'Alquilado'],
                            ];
                            ?>
                            <?php foreach ($cars as $model): ?>
                                <?php
                                $st = $labels[$model->status] ?? ['class' => 'secondary', 't' => $model->status];
                                $carRentalCount = (int) ($rentalsByCar[(int) $model->id] ?? 0);
                                $hasRentals = $carRentalCount > 0;
                                ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="material-symbols-outlined align-middle me-1" style="font-size: 20px; color: #3fa9f5;">directions_car</span>
                                            <strong><?= Html::encode($model->nombre) ?></strong>
                                        </div>
                                        <div class="d-md-none mt-1 d-flex flex-wrap gap-1 align-items-center">
                                            <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
                                            <?php if (!empty($model->cantidad_pasajeros)): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <span class="material-symbols-outlined align-middle" style="font-size: 14px;">group</span>
                                                    <?= Html::encode((string) $model->cantidad_pasajeros) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($hasRentals): ?>
                                                <button type="button"
                                                        class="badge bg-success border-0 rc-reservas-btn"
                                                        data-car-id="<?= (int) $model->id ?>"
                                                        data-car-name="<?= Html::encode($model->nombre) ?>"
                                                        data-car-placa="<?= Html::encode($model->placa) ?>"
                                                        title="Ver órdenes de este vehículo">
                                                    <span class="material-symbols-outlined align-middle" style="font-size: 14px;">check_circle</span>
                                                    Reservas (<?= $carRentalCount ?>)
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border" title="Sin órdenes asociadas">
                                                    <span class="material-symbols-outlined align-middle" style="font-size: 14px;">check_circle</span>
                                                    Sin reservas
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= Html::encode($model->placa) ?></span></td>
                                    <td class="d-none d-md-table-cell"><?= Html::encode((string) ($model->cantidad_pasajeros ?? '—')) ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
                                    </td>
                                    <td class="d-none d-md-table-cell text-center">
                                        <?php if ($hasRentals): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-link p-0 rc-reservas-btn"
                                                    data-car-id="<?= (int) $model->id ?>"
                                                    data-car-name="<?= Html::encode($model->nombre) ?>"
                                                    data-car-placa="<?= Html::encode($model->placa) ?>"
                                                    title="Ver <?= $carRentalCount ?> orden(es) de este vehículo">
                                                <span class="material-symbols-outlined align-middle" style="font-size: 22px; color: #198754;">check_circle</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><?= $carRentalCount ?></span>
                                            </button>
                                        <?php else: ?>
                                            <span title="Sin órdenes asociadas">
                                                <span class="material-symbols-outlined align-middle" style="font-size: 22px; color: #adb5bd;">check_circle</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?= Html::encode($model->empresa ?? '—') ?></td>
                                    <td class="text-end text-nowrap">
                                        <?= Html::a(
                                            '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">visibility</span>',
                                            ['view', 'id' => $model->id],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver vehículo', 'encode' => false]
                                        ) ?>
                                        <?= Html::a(
                                            '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">add_circle</span>',
                                            ['/rental/create', 'car_id' => $model->id],
                                            ['class' => 'btn btn-sm btn-outline-success', 'title' => 'Nuevo alquiler', 'encode' => false]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted small">
                    Total: <?= count($cars) ?> vehículo(s) libres de renta en el día indicado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== Modal: órdenes asociadas a un vehículo ===== -->
<div class="modal fade" id="rcCarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header rc-modal-header" style="background: linear-gradient(135deg, #1e7e34 0%, #0d3a1c 100%);">
                <h5 class="modal-title" style="color: #ffffff !important;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 22px; margin-right: 6px; color: #ffffff;">directions_car</span>
                    <span id="rc-car-title" style="color: #ffffff;">Órdenes del vehículo</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="rc-car-body">
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

<style>
/* Encabezado del modal en blanco (anti-overrides de tema) */
.rc-modal-header .modal-title,
.rc-modal-header .modal-title * {
    color: #ffffff !important;
}

/* ===== Acordeón del modal en móvil ===== */
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
.rc-day-accordion .rc-row-head .rc-row-id {
    font-weight: 700;
    color: #12355b;
}
.rc-day-accordion .rc-row-head .rc-row-client {
    font-weight: 600;
}
.rc-day-accordion .rc-row-head .rc-row-state {
    margin-left: auto;
}
.rc-day-accordion .accordion-body {
    padding: 12px;
    font-size: 13px;
}
.rc-day-accordion .accordion-body dl {
    display: grid;
    grid-template-columns: max-content 1fr;
    gap: 4px 10px;
    margin-bottom: 10px;
}
.rc-day-accordion .accordion-body dt {
    color: #6c757d;
    font-weight: 600;
}
.rc-day-accordion .accordion-body dd { margin: 0; }
.rc-day-accordion .rc-row-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

/* ===== Boton de "Reservas" (check) en la tabla ===== */
button.rc-reservas-btn {
    background: transparent;
    border: 0;
    line-height: 1;
    cursor: pointer;
}
button.rc-reservas-btn:focus { outline: none; box-shadow: none; }
button.rc-reservas-btn:hover .material-symbols-outlined { transform: scale(1.08); }
button.rc-reservas-btn .material-symbols-outlined { transition: transform .15s ease; }
/* Botón pequeño tipo badge usado en la vista móvil */
button.rc-reservas-btn.badge { cursor: pointer; }
button.rc-reservas-btn.badge:hover { filter: brightness(1.05); }
</style>

<?php
$jsFiltro = json_encode($fecha);
?>
<script>
/* IIFE del calendario eliminado: el calendario se movió al Dashboard (/site/index). */

// ===== Modal de órdenes por vehículo (columna Reservas) =====
(function () {
    var CAR_RENTALS_URL = <?= json_encode($carRentalsUrl) ?>;
    var CAR_FROM = <?= $jsFiltro ?>;
    var modalEl = document.getElementById('rcCarModal');
    var bodyEl = document.getElementById('rc-car-body');
    var titleEl = document.getElementById('rc-car-title');
    if (!modalEl) return;

    function estadoBadge(estado) {
        var map = {
            'pagado':     ['success', '✅ Pagado'],
            'pendiente':  ['warning', '🟡 Pendiente'],
            'reservado':  ['primary', '📌 Reservado'],
            'finalizado': ['secondary', '🏁 Finalizado'],
            'cancelado':  ['danger', '❌ Cancelado']
        };
        var m = map[estado] || ['secondary', estado];
        return '<span class="badge bg-' + m[0] + '">' + m[1] + '</span>';
    }

    function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }

    function formatTime12h(t) {
        if (!t) return '';
        var m = String(t).match(/^(\d{1,2}):(\d{2})/);
        if (!m) return '';
        var h = parseInt(m[1], 10);
        var min = m[2];
        var p = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) h = 12;
        return h + ':' + min + ' ' + p;
    }

    function formatDateDMY(s) {
        if (!s) return '';
        var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return s;
        return m[3] + '/' + m[2] + '/' + m[1];
    }

    function openModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            modalEl.style.display = 'block';
        }
    }

    function showCar(carId, carName, carPlaca) {
        titleEl.textContent = (carName || 'Vehículo') + (carPlaca ? ' — ' + carPlaca : '');
        bodyEl.innerHTML = '<div class="text-center text-muted py-4">'
            + '<div class="spinner-border spinner-border-sm" role="status"></div>'
            + '<div class="small mt-2">Cargando órdenes…</div>'
            + '</div>';
        openModal();

        fetch(CAR_RENTALS_URL + '?car_id=' + encodeURIComponent(carId) + '&from=' + encodeURIComponent(CAR_FROM), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !data.success || !data.items || data.items.length === 0) {
                bodyEl.innerHTML = '<div class="text-center text-muted py-4">'
                    + '<span class="material-symbols-outlined" style="font-size: 48px; opacity: .5;">event_busy</span>'
                    + '<div class="mt-2">Este vehículo no tiene órdenes asociadas.</div>'
                    + '</div>';
                return;
            }

            // Resumen por estado
            var counts = {};
            data.items.forEach(function (it) { counts[it.estado_pago] = (counts[it.estado_pago] || 0) + 1; });
            var summary = '<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">';
            Object.keys(counts).forEach(function (k) {
                summary += estadoBadge(k) + ' <span class="text-muted small">×' + counts[k] + '</span>';
            });
            summary += '<span class="ms-auto text-muted small">' + data.items.length + ' orden(es)</span>';
            summary += '</div>';

            // Vista escritorio (tabla)
            var rows = '';
            data.items.forEach(function (it) {
                var horaIni = formatTime12h(it.hora_inicio);
                var horaFin = formatTime12h(it.hora_final);
                var dRange = formatDateDMY(it.fecha_inicio) + (horaIni ? ' ' + horaIni : '')
                    + ' → ' + formatDateDMY(it.fecha_final) + (horaFin ? ' ' + horaFin : '');
                var total = Number(it.total_precio || 0).toLocaleString('es-CR');

                rows += '<tr>'
                    + '<td><strong>' + it.rental_id + '</strong></td>'
                    + '<td>' + (it.client_name || '—') + '</td>'
                    + '<td class="small text-muted">' + dRange + '</td>'
                    + '<td>' + estadoBadge(it.estado_pago) + '</td>'
                    + '<td class="text-end">₡ ' + total + '</td>'
                    + '<td class="text-end text-nowrap">'
                        + '<a href="' + it.view_url + '" class="btn btn-sm btn-outline-primary" title="Ver">'
                        + '<span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span></a> '
                        + '<a href="' + it.update_url + '" class="btn btn-sm btn-outline-secondary" title="Editar">'
                        + '<span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span></a>'
                    + '</td>'
                    + '</tr>';
            });

            var deskTable = '<div class="table-responsive d-none d-md-block">'
                + '<table class="table table-sm table-hover align-middle mb-2">'
                + '<thead class="table-light"><tr>'
                + '<th>Orden</th><th>Cliente</th><th>Periodo</th><th>Estado</th><th class="text-end">Total</th><th></th>'
                + '</tr></thead>'
                + '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '</div>';

            // Vista móvil (acordeón)
            var accId = 'rcCarAcc_' + Math.random().toString(36).slice(2, 8);
            var accItems = '';
            data.items.forEach(function (it, idx) {
                var horaIni = formatTime12h(it.hora_inicio);
                var horaFin = formatTime12h(it.hora_final);
                var dRange = formatDateDMY(it.fecha_inicio) + (horaIni ? ' ' + horaIni : '')
                    + ' → ' + formatDateDMY(it.fecha_final) + (horaFin ? ' ' + horaFin : '');
                var total = Number(it.total_precio || 0).toLocaleString('es-CR');
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
                    + '<span class="rc-row-state">' + estadoBadge(it.estado_pago) + '</span>'
                    + '</div>'
                    + '<div class="rc-row-client small text-muted">'
                    + '<span class="material-symbols-outlined align-middle" style="font-size:14px;">person</span> '
                    + (it.client_name || '—')
                    + '</div>'
                    + '</div>'
                    + '</button>'
                    + '</h2>'
                    + '<div id="' + bodyId + '" class="accordion-collapse collapse"'
                    + ' aria-labelledby="' + headerId + '" data-bs-parent="#' + accId + '">'
                    + '<div class="accordion-body">'
                    + '<dl>'
                    + '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;">date_range</span> Periodo</dt>'
                    + '<dd class="small">' + dRange + '</dd>'
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

            bodyEl.innerHTML = summary + deskTable + mobileAcc;
        })
        .catch(function (err) {
            bodyEl.innerHTML = '<div class="text-danger small text-center py-3">'
                + 'Error cargando órdenes: ' + (err && err.message ? err.message : 'desconocido')
                + '</div>';
        });
    }

    // Delegación: cualquier botón con .rc-reservas-btn abre el modal
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.rc-reservas-btn');
        if (!btn) return;
        ev.preventDefault();
        var carId = btn.getAttribute('data-car-id');
        var carName = btn.getAttribute('data-car-name') || '';
        var carPlaca = btn.getAttribute('data-car-placa') || '';
        if (carId) showCar(carId, carName, carPlaca);
    });
})();
</script>
