<?php
/** @var yii\web\View $this */
/** @var app\models\Car[] $cars */
/** @var string $fecha */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Disponibles';
$this->params['breadcrumbs'][] = $this->title;

$fechaLabel = Yii::$app->formatter->asDate($fecha, 'long');
$hoy = date('Y-m-d');
$esHoy = ($fecha === $hoy);

$calendarMonthUrl = Url::to(['car/calendar-rentals']);
$calendarDayUrl = Url::to(['car/calendar-day']);
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

    <!-- ===== Calendario interactivo de alquileres ===== -->
    <div class="card mb-4 rentals-calendar-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 22px;">calendar_month</span>
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
                                <?php $st = $labels[$model->status] ?? ['class' => 'secondary', 't' => $model->status]; ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="material-symbols-outlined align-middle me-1" style="font-size: 20px; color: #3fa9f5;">directions_car</span>
                                            <strong><?= Html::encode($model->nombre) ?></strong>
                                        </div>
                                        <div class="d-md-none mt-1">
                                            <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
                                            <?php if (!empty($model->cantidad_pasajeros)): ?>
                                                <span class="badge bg-light text-dark border ms-1">
                                                    <span class="material-symbols-outlined align-middle" style="font-size: 14px;">group</span>
                                                    <?= Html::encode((string) $model->cantidad_pasajeros) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= Html::encode($model->placa) ?></span></td>
                                    <td class="d-none d-md-table-cell"><?= Html::encode((string) ($model->cantidad_pasajeros ?? '—')) ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
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

<!-- ===== Modal: detalle de alquileres del día ===== -->
<div class="modal fade" id="rcDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header rc-modal-header" style="background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);">
                <h5 class="modal-title" style="color: #ffffff !important;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 22px; margin-right: 6px; color: #ffffff;">event_note</span>
                    <span id="rc-modal-title" style="color: #ffffff;">Alquileres del día</span>
                </h5>
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

<style>
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
.rc-calendar .rc-day.rc-clickable {
    cursor: pointer;
}
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
.rc-calendar .rc-day .rc-icon .material-symbols-outlined {
    font-size: 24px;
}
.rc-calendar .rc-day .rc-count {
    font-weight: 700;
    font-size: 14px;
}
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
/* Coloreado del fondo de cada día según mayoría de estado (clases agregadas por JS) */
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
</style>

<?php
$jsMonth = json_encode(substr($fecha, 0, 7));
$jsToday = json_encode(date('Y-m-d'));
$jsMonthUrl = json_encode($calendarMonthUrl);
$jsDayUrl = json_encode($calendarDayUrl);
$jsDispBase = json_encode(Url::to(['car/disponibles']));
?>
<script>
(function () {
    var RC_MONTH_URL = <?= $jsMonthUrl ?>;
    var RC_DAY_URL = <?= $jsDayUrl ?>;
    var RC_DISP_URL = <?= $jsDispBase ?>;
    var RC_TODAY = <?= $jsToday ?>;

    var calRoot = document.getElementById('rc-calendar');
    var labelEl = document.getElementById('rc-month-label');
    var btnPrev = document.getElementById('rc-prev');
    var btnNext = document.getElementById('rc-next');
    var btnToday = document.getElementById('rc-today');
    var modalEl = document.getElementById('rcDayModal');
    var modalBody = document.getElementById('rc-modal-body');
    var modalTitle = document.getElementById('rc-modal-title');
    if (!calRoot) return;

    var MONTH_NAMES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var DOW = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

    var currentMonth = <?= $jsMonth ?>; // 'YYYY-MM'

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

        var firstDow = new Date(year, month - 1, 1).getDay(); // 0=domingo
        // Mover para que la semana empiece en lunes:
        var leading = (firstDow + 6) % 7; // si domingo (0) -> 6, si lunes (1) -> 0
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

                // Mini barra inferior con la composición de estados
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
        // Padding final hasta completar la última fila
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
        // Quitar el grid mientras carga para que el spinner ocupe todo
        calRoot.style.display = 'block';

        fetch(RC_MONTH_URL + '?month=' + encodeURIComponent(monthStr), {
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

    function showDay(dateStr) {
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
            if (!data || !data.items || data.items.length === 0) {
                modalBody.innerHTML = '<div class="text-center text-muted py-4">'
                    + '<span class="material-symbols-outlined" style="font-size: 48px; opacity: .5;">event_busy</span>'
                    + '<div class="mt-2">No hay alquileres activos en este día.</div>'
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
            summary += '<span class="ms-auto text-muted small">' + data.items.length + ' alquiler(es)</span>';
            summary += '</div>';

            // ----- Vista escritorio: tabla -----
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
                    + '<td><span class="material-symbols-outlined align-middle" style="font-size:16px;color:#3fa9f5;">directions_car</span> '
                        + (it.car_name || '—')
                        + (it.car_placa ? ' <span class="badge bg-secondary ms-1">' + it.car_placa + '</span>' : '')
                    + '</td>'
                    + '<td class="small text-muted">' + dRange + '</td>'
                    + '<td>' + estadoBadge(it.estado_pago) + '</td>'
                    + '<td class="text-end">₡ ' + total + '</td>'
                    + '<td class="text-end text-nowrap">'
                        + '<a href="' + it.view_url + '" class="btn btn-sm btn-outline-primary" title="Ver"><span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">visibility</span></a> '
                        + '<a href="' + it.update_url + '" class="btn btn-sm btn-outline-secondary" title="Editar"><span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">edit</span></a>'
                    + '</td>'
                    + '</tr>';
            });

            var deskTable = '<div class="table-responsive d-none d-md-block">'
                + '<table class="table table-sm table-hover align-middle mb-2">'
                + '<thead class="table-light"><tr>'
                + '<th>Orden</th><th>Cliente</th><th>Vehículo</th><th>Periodo</th><th>Estado</th><th class="text-end">Total</th><th></th>'
                + '</tr></thead>'
                + '<tbody>' + rows + '</tbody>'
                + '</table>'
                + '</div>';

            // ----- Vista móvil: acordeón -----
            var accId = 'rcDayAcc_' + Math.random().toString(36).slice(2, 8);
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
                    + '<dt><span class="material-symbols-outlined align-middle" style="font-size:14px;color:#3fa9f5;">directions_car</span> Vehículo</dt>'
                    + '<dd>' + (it.car_name || '—')
                    + (it.car_placa ? ' <span class="badge bg-secondary ms-1">' + it.car_placa + '</span>' : '') + '</dd>'
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

    if (btnPrev) btnPrev.addEventListener('click', function () { loadMonth(shiftMonth(currentMonth, -1)); });
    if (btnNext) btnNext.addEventListener('click', function () { loadMonth(shiftMonth(currentMonth, +1)); });
    if (btnToday) btnToday.addEventListener('click', function () { loadMonth(RC_TODAY.substring(0, 7)); });

    loadMonth(currentMonth);
})();
</script>
