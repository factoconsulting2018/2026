<?php
/**
 * Contenido del modal de detalle de alquiler (con tabs).
 *
 * @var app\models\Rental $model
 * @var app\models\Rental[] $clientHistory
 */

use yii\helpers\Html;
use yii\helpers\Url;

$clientHistory = $clientHistory ?? [];

$diasSemanaEs = [
    'Sunday' => 'Domingo',
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
];
$formatFechaConDia = static function ($raw) use ($diasSemanaEs) {
    if (empty($raw)) {
        return null;
    }
    $ts = strtotime((string) $raw);
    if ($ts === false) {
        return null;
    }
    $dia = $diasSemanaEs[date('l', $ts)] ?? '';
    return date('d/m/Y', $ts) . ($dia !== '' ? ' ' . $dia : '');
};
$formatFechaCorta = static function ($raw) {
    if (empty($raw)) {
        return '—';
    }
    $ts = strtotime((string) $raw);
    return $ts === false ? '—' : date('d/m/Y', $ts);
};

$rentalCode = !empty($model->rental_id) ? $model->rental_id : ('R' . $model->id);
$clienteTxt = $model->client
    ? ($model->client->full_name . (!empty($model->client->cedula_fisica) ? ' (' . $model->client->cedula_fisica . ')' : ''))
    : 'N/A';
$vehiculoTxt = $model->car
    ? ($model->car->nombre . (!empty($model->car->placa) ? ' (' . $model->car->placa . ')' : ''))
    : 'N/A';

$esMismoDia = ($model->fecha_inicio === $model->fecha_final || strtotime((string) $model->fecha_inicio) === strtotime((string) $model->fecha_final));
$medioDiaActivo = (!empty($model->medio_dia_enabled) || $model->medio_dia_enabled == 1) && !empty($model->medio_dia_valor) && $model->medio_dia_valor > 0;
if ($esMismoDia && $medioDiaActivo) {
    $diasTxt = '1/2 día (¢' . number_format($model->medio_dia_valor, 0) . ')';
} elseif ($medioDiaActivo) {
    $diasTxt = $model->cantidad_dias . ' días + 1/2 día (¢' . number_format($model->medio_dia_valor, 0) . ')';
} elseif ($esMismoDia) {
    $diasTxt = '1 día (por horas)';
} else {
    $diasTxt = $model->cantidad_dias . ' días';
}

$estado = $model->estado_pago ?? 'pendiente';
$uid = 'rmv' . (int) $model->id;
$estadoBadges = [
    'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
    'pagado' => '<span class="badge bg-success">Pagado</span>',
    'reservado' => '<span class="badge bg-info text-dark">Reservado</span>',
    'finalizado' => '<span class="badge bg-dark">Finalizado</span>',
    'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
];
$estadoHtml = $estadoBadges[$estado] ?? ('<span class="badge bg-secondary">' . Html::encode($estado) . '</span>');
$estadoHtml = '<button type="button" class="border-0 p-0 bg-transparent" title="Ir a tab Pago"'
    . ' onclick="var t=document.getElementById(\'' . $uid . '-pago-tab\'); if(t){ if(window.bootstrap&&bootstrap.Tab){var i=bootstrap.Tab.getInstance(t)||new bootstrap.Tab(t); i.show();} else {t.click();} }">'
    . $estadoHtml . '</button>';

$horaInicio = !empty($model->hora_inicio) ? \app\helpers\TimeHelper::convertTo12Hour($model->hora_inicio) : 'N/A';
$horaFinal = !empty($model->hora_final) ? \app\helpers\TimeHelper::convertTo12Hour($model->hora_final) : 'N/A';

$fechaCorre = '—';
if (!empty($model->fecha_correapartir)) {
    $tsCorre = strtotime($model->fecha_correapartir);
    if ($tsCorre !== false) {
        $fechaCorre = $formatFechaConDia($model->fecha_correapartir);
        $horaCorre = date('H:i', $tsCorre);
        if ($horaCorre !== '00:00') {
            $fechaCorre .= ' ' . \app\helpers\TimeHelper::convertTo12Hour($horaCorre);
        }
    }
}

$creadoEl = '—';
if (!empty($model->created_at)) {
    $tsCre = strtotime($model->created_at);
    if ($tsCre !== false) {
        $creadoEl = ($formatFechaConDia($model->created_at) ?? date('d/m/Y', $tsCre))
            . ' ' . \app\helpers\TimeHelper::convertTo12Hour(date('H:i', $tsCre));
    }
}

$rowsDetalles = [
    ['ID del Alquiler', Html::encode($rentalCode)],
    ['Cliente', Html::encode($clienteTxt)],
    ['Vehículo', Html::encode($vehiculoTxt)],
    ['Fecha de Inicio', Html::encode($formatFechaConDia($model->fecha_inicio) ?? 'N/A')],
    ['Hora de Inicio', Html::encode($horaInicio)],
    ['Fecha Final', Html::encode($formatFechaConDia($model->fecha_final) ?? 'N/A')],
    ['Hora Final', Html::encode($horaFinal)],
    ['Cantidad de Días', Html::encode($diasTxt)],
    ['Precio por Día', '₡' . number_format((float) ($model->precio_por_dia ?? 0), 2)],
    ['1/2 Día', $medioDiaActivo ? ('Sí (¢' . number_format((float) $model->medio_dia_valor, 2) . ')') : 'No'],
    ['Precio Total', '<strong style="color:#28a745;font-size:1.05rem;">₡' . number_format((float) ($model->total_precio ?? 0), 2) . '</strong>'],
    ['Estado de Pago', $estadoHtml],
    ['Lugar de Entrega', Html::encode($model->lugar_entrega ?: '—')],
    ['Lugar de Retiro', Html::encode($model->lugar_retiro ?: '—')],
    ['Correapartir', $model->correapartir_enabled ? 'Sí' : 'No'],
    ['Fecha Correapartir', Html::encode($fechaCorre)],
];

$rowsExtra = [
    ['Comprobante de Pago', Html::encode($model->comprobante_pago ?: '—')],
    ['Número de Factura', Html::encode($model->numero_factura ?: '—')],
    ['Fecha Factura', Html::encode($formatFechaConDia($model->fecha_factura) ?? '—')],
    ['Creado el', Html::encode($creadoEl)],
    ['Condiciones Especiales', $model->condiciones_especiales ? nl2br(Html::encode($model->condiciones_especiales)) : '—'],
    ['Choferes Autorizados', $model->choferes_autorizados ? nl2br(Html::encode($model->choferes_autorizados)) : '—'],
];

$historialCount = count($clientHistory);
?>
<div class="rental-modal-view" data-rental-id="<?= (int) $model->id ?>">
    <ul class="nav nav-tabs mb-3 flex-wrap" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="<?= $uid ?>-detalles-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-detalles" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">info</span>
                Detalles
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-pago-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-pago" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">payments</span>
                Pago
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-historial-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-historial" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">history</span>
                Historial del cliente
                <span class="badge bg-secondary ms-1"><?= (int) $historialCount ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-extra-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-extra" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">notes</span>
                Extra
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="<?= $uid ?>-acciones-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-acciones" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">settings</span>
                Acciones
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="<?= $uid ?>-detalles" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 rental-modal-detail-table">
                    <tbody>
                    <?php foreach ($rowsDetalles as [$label, $value]): ?>
                        <tr>
                            <th style="width:38%;white-space:nowrap;"><?= Html::encode($label) ?></th>
                            <td><?= $value ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="<?= $uid ?>-historial" role="tabpanel">
            <?php if ($historialCount === 0): ?>
                <div class="text-center text-muted py-4">
                    <span class="material-symbols-outlined" style="font-size:40px;opacity:.45;">history</span>
                    <p class="mb-0 mt-2">No hay historial de alquileres para este cliente.</p>
                </div>
            <?php else: ?>
                <p class="small text-muted mb-2">
                    Últimos <?= (int) $historialCount ?> alquileres del cliente
                    <?= $model->client ? '<strong>' . Html::encode($model->client->full_name) . '</strong>' : '' ?>.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Vehículo</th>
                                <th>Período</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($clientHistory as $hist): ?>
                            <?php
                            $histCode = !empty($hist->rental_id) ? $hist->rental_id : ('R' . $hist->id);
                            $histEstado = $hist->estado_pago ?? 'pendiente';
                            $histEstadoHtml = $estadoBadges[$histEstado]
                                ?? ('<span class="badge bg-secondary">' . Html::encode($histEstado) . '</span>');
                            $histCar = $hist->car
                                ? ($hist->car->nombre . (!empty($hist->car->placa) ? ' (' . $hist->car->placa . ')' : ''))
                                : '—';
                            $isCurrent = ((int) $hist->id === (int) $model->id);
                            ?>
                            <tr class="<?= $isCurrent ? 'table-info' : '' ?>">
                                <td>
                                    <strong><?= Html::encode($histCode) ?></strong>
                                    <?php if ($isCurrent): ?>
                                        <span class="badge bg-primary ms-1">Actual</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($histCar) ?></td>
                                <td class="small text-nowrap">
                                    <?= Html::encode($formatFechaCorta($hist->fecha_inicio)) ?>
                                    →
                                    <?= Html::encode($formatFechaCorta($hist->fecha_final)) ?>
                                </td>
                                <td><?= $histEstadoHtml ?></td>
                                <td class="text-end text-nowrap">₡<?= number_format((float) ($hist->total_precio ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="<?= $uid ?>-extra" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 rental-modal-detail-table">
                    <tbody>
                    <?php foreach ($rowsExtra as [$label, $value]): ?>
                        <tr>
                            <th style="width:38%;white-space:nowrap;"><?= Html::encode($label) ?></th>
                            <td><?= $value ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="<?= $uid ?>-pago" role="tabpanel">
            <?php
            $estadoLabelMap = [
                'pendiente' => 'Pendiente',
                'pagado' => 'Pagado',
                'reservado' => 'Reservado',
                'finalizado' => 'Finalizado',
                'cancelado' => 'Cancelado',
            ];
            $estadoLabel = $estadoLabelMap[$estado] ?? ucfirst((string) $estado);
            $defaultNewStatus = ($estado === 'pendiente') ? 'pagado' : $estado;
            $abonosVisible = ($defaultNewStatus === 'reservado');
            ?>
            <form id="<?= $uid ?>-pago-form" class="rmv-pago-form" enctype="multipart/form-data"
                  data-rental-id="<?= (int) $model->id ?>"
                  data-rental-code="<?= Html::encode($rentalCode) ?>"
                  onsubmit="return false;">
                <input type="hidden" name="rentalId" value="<?= (int) $model->id ?>">

                <div class="row mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label" for="<?= $uid ?>-pago-code">ID Alquiler</label>
                        <input type="text" class="form-control" id="<?= $uid ?>-pago-code"
                               value="<?= Html::encode($rentalCode) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="<?= $uid ?>-pago-current">Estado actual</label>
                        <input type="text" class="form-control" id="<?= $uid ?>-pago-current"
                               value="<?= Html::encode($estadoLabel) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="<?= $uid ?>-pago-new">Nuevo estado de pago</label>
                    <select class="form-select" id="<?= $uid ?>-pago-new" name="newStatus" required
                            onchange="rmvTogglePayAbonos('<?= $uid ?>')">
                        <option value="">Seleccione un estado</option>
                        <option value="pagado"<?= $defaultNewStatus === 'pagado' ? ' selected' : '' ?>>Pagado</option>
                        <option value="reservado"<?= $defaultNewStatus === 'reservado' ? ' selected' : '' ?>>Reservado</option>
                        <option value="pendiente"<?= $defaultNewStatus === 'pendiente' ? ' selected' : '' ?>>Pendiente</option>
                        <option value="finalizado"<?= $defaultNewStatus === 'finalizado' ? ' selected' : '' ?>>Finalizado</option>
                        <option value="cancelado"<?= $defaultNewStatus === 'cancelado' ? ' selected' : '' ?>>Cancelado</option>
                    </select>
                </div>

                <?php
                $metodosPago = [
                    'Sinpe Móvil' => 'Sinpe Móvil',
                    'Transferencia - BCR' => 'Transferencia - BCR',
                    'Transferencia - BAC' => 'Transferencia - BAC',
                    'Transferencia - BN' => 'Transferencia - BN',
                    'Pago en efectivo' => 'Pago en efectivo',
                    'Tarjeta de crédito' => 'Tarjeta de crédito',
                ];
                $metodoActual = (string) ($model->comprobante_pago ?? '');
                $metodoEsOpcion = isset($metodosPago[$metodoActual]);
                $tieneArchivoComprobante = method_exists($model, 'hasComprobante') && $model->hasComprobante();
                ?>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label" for="<?= $uid ?>-pago-metodo">Comprobante de Pago (método de pago)</label>
                        <select class="form-select" id="<?= $uid ?>-pago-metodo" name="comprobante_pago">
                            <option value="">Seleccionar método de pago</option>
                            <?php foreach ($metodosPago as $val => $label): ?>
                                <option value="<?= Html::encode($val) ?>"<?= $metodoEsOpcion && $metodoActual === $val ? ' selected' : '' ?>>
                                    <?= Html::encode($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="<?= $uid ?>-pago-factura">Número de Factura</label>
                        <input type="text" class="form-control" id="<?= $uid ?>-pago-factura"
                               name="numero_factura"
                               value="<?= Html::encode((string) ($model->numero_factura ?? '')) ?>"
                               placeholder="Ej: 001-001-00001234">
                    </div>
                </div>

                <div id="<?= $uid ?>-pago-abonos" class="mb-3" style="display:<?= $abonosVisible ? 'block' : 'none' ?>;">
                    <div class="card">
                        <div class="card-header bg-info text-white py-2">
                            <strong>
                                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">payments</span>
                                Abonos
                            </strong>
                        </div>
                        <div class="card-body">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php
                                $abonoDesc = $model->{"abono{$i}_descripcion"} ?? '';
                                $abonoMonto = $model->{"abono{$i}_monto"} ?? '';
                                ?>
                                <div class="row mb-2">
                                    <div class="col-md-8 mb-1 mb-md-0">
                                        <input type="text" class="form-control form-control-sm"
                                               name="abono<?= $i ?>_descripcion"
                                               value="<?= Html::encode((string) $abonoDesc) ?>"
                                               placeholder="Abono <?= $i ?> descripción">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control form-control-sm"
                                               name="abono<?= $i ?>_monto" step="0.01"
                                               value="<?= $abonoMonto !== null && $abonoMonto !== '' ? Html::encode((string) $abonoMonto) : '' ?>"
                                               placeholder="Monto ₡">
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <?php if ($tieneArchivoComprobante): ?>
                    <div class="mb-3">
                        <label class="form-label">Archivo de comprobante actual</label>
                        <div>
                            <a href="<?= Html::encode($model->getComprobanteUrl()) ?>"
                               target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">attach_file</span>
                                Ver comprobante
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="<?= $uid ?>-pago-file">Adjuntar archivo de comprobante</label>
                    <input type="file" class="form-control" id="<?= $uid ?>-pago-file" name="comprobanteFile"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <div class="form-text">JPG, PNG, PDF, DOC, DOCX (máx. 10MB). Opcional; no reemplaza el método de pago.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="<?= $uid ?>-pago-obs">Observaciones (opcional)</label>
                    <textarea class="form-control" id="<?= $uid ?>-pago-obs" name="observaciones" rows="2"
                              placeholder="Detalle del pago, referencia Sinpe, etc."></textarea>
                </div>

                <div id="<?= $uid ?>-pago-error" class="alert alert-danger d-none mb-3"></div>
                <div id="<?= $uid ?>-pago-ok" class="alert alert-success d-none mb-3"></div>

                <button type="button" class="btn btn-primary" id="<?= $uid ?>-pago-save"
                        onclick="rmvSavePaymentStatus('<?= $uid ?>')">
                    <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">save</span>
                    Guardar estado de pago
                </button>
            </form>
        </div>

        <div class="tab-pane fade" id="<?= $uid ?>-acciones" role="tabpanel">
            <div class="row g-2">
                <div class="col-6">
                    <a href="<?= Url::to(['/rental/update', 'id' => $model->id]) ?>" class="btn btn-primary w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">edit</span>
                        Editar
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?= Url::to(['/pdf/rental-order', 'id' => $model->id]) ?>" class="btn btn-info text-white w-100" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">picture_as_pdf</span>
                        Orden PDF
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?= Url::to(['/rental/view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary w-100">
                        <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">open_in_new</span>
                        Página completa
                    </a>
                </div>
                <div class="col-6">
                    <?= Html::a(
                        '<span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">delete</span>Eliminar',
                        ['/rental/delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-danger w-100',
                            'data' => [
                                'confirm' => '¿Estás seguro de que quieres eliminar este alquiler?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
