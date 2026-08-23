<?php
/**
 * Contenido del modal de detalle de alquiler (con tabs).
 *
 * @var app\models\Rental $model
 */

use yii\helpers\Html;
use yii\helpers\Url;

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
$estadoBadges = [
    'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
    'pagado' => '<span class="badge bg-success">Pagado</span>',
    'reservado' => '<span class="badge bg-info text-dark">Reservado</span>',
    'finalizado' => '<span class="badge bg-dark">Finalizado</span>',
    'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
];
$estadoHtml = $estadoBadges[$estado] ?? ('<span class="badge bg-secondary">' . Html::encode($estado) . '</span>');

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

$uid = 'rmv' . (int) $model->id;
?>
<div class="rental-modal-view" data-rental-id="<?= (int) $model->id ?>">
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="<?= $uid ?>-detalles-tab" data-bs-toggle="tab"
                    data-bs-target="#<?= $uid ?>-detalles" type="button" role="tab">
                <span class="material-symbols-outlined" style="font-size:16px;vertical-align:middle;">info</span>
                Detalles
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

        <div class="tab-pane fade" id="<?= $uid ?>-acciones" role="tabpanel">
            <div class="d-grid gap-2">
                <a href="<?= Url::to(['/rental/update', 'id' => $model->id]) ?>" class="btn btn-primary">
                    <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">edit</span>
                    Editar
                </a>
                <a href="<?= Url::to(['/pdf/rental-order', 'id' => $model->id]) ?>" class="btn btn-info text-white" target="_blank" rel="noopener">
                    <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">picture_as_pdf</span>
                    Orden PDF
                </a>
                <a href="<?= Url::to(['/rental/view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
                    <span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">open_in_new</span>
                    Abrir página completa
                </a>
                <?= Html::a(
                    '<span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;margin-right:4px;">delete</span>Eliminar Alquiler',
                    ['/rental/delete', 'id' => $model->id],
                    [
                        'class' => 'btn btn-danger',
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
