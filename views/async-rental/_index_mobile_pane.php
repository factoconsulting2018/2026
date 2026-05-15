<?php
/** @var yii\web\View $this */
/** @var app\models\Rental $model */

use yii\helpers\Html;

$orderId = $model->rental_id ?: ('R' . str_pad((string) $model->id, 6, '0', STR_PAD_LEFT));
$estado = strtolower(trim((string) ($model->estado_pago ?? '')));
$estadoColors = [
    'pendiente' => 'bg-warning text-dark',
    'pagado' => 'bg-success',
    'reservado' => 'bg-info text-dark',
    'cancelado' => 'bg-danger',
];
$estadoClass = $estadoColors[$estado] ?? 'bg-secondary';
$estadoLabel = $estado ? strtoupper($estado) : 'N/A';
$tabUid = 'async-tabs-' . (int) $model->id;
?>
<div class="async-mobile-pane">
    <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
        <span class="badge <?= $estadoClass ?>"><?= Html::encode($estadoLabel) ?></span>
        <span class="badge bg-light text-dark border">Asincrónica</span>
    </div>

    <ul class="nav nav-tabs nav-fill async-mobile-tabs" id="<?= Html::encode($tabUid) ?>" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active"
                    id="<?= Html::encode($tabUid) ?>-general-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?= Html::encode($tabUid) ?>-general"
                    type="button"
                    role="tab"
                    aria-controls="<?= Html::encode($tabUid) ?>-general"
                    aria-selected="true">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">info</span>
                General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                    id="<?= Html::encode($tabUid) ?>-fechas-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?= Html::encode($tabUid) ?>-fechas"
                    type="button"
                    role="tab"
                    aria-controls="<?= Html::encode($tabUid) ?>-fechas"
                    aria-selected="false">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">calendar_month</span>
                Fechas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                    id="<?= Html::encode($tabUid) ?>-acciones-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?= Html::encode($tabUid) ?>-acciones"
                    type="button"
                    role="tab"
                    aria-controls="<?= Html::encode($tabUid) ?>-acciones"
                    aria-selected="false">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">touch_app</span>
                Acciones
            </button>
        </li>
    </ul>

    <div class="tab-content async-mobile-tab-content pt-3" id="<?= Html::encode($tabUid) ?>-content">
        <div class="tab-pane fade show active"
             id="<?= Html::encode($tabUid) ?>-general"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-general-tab">
            <div class="async-mobile-kv">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="async-mobile-label">ID Orden</div>
                        <div class="async-mobile-value fw-semibold"><?= Html::encode($orderId) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="async-mobile-label">Cliente</div>
                        <div class="async-mobile-value"><?= Html::encode($model->client ? $model->client->full_name : 'N/A') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="async-mobile-label">Vehículo</div>
                        <div class="async-mobile-value">
                            <?= Html::encode($model->car ? $model->car->nombre : 'N/A') ?>
                            <?php if ($model->car && $model->car->placa): ?>
                                <span class="text-muted">(<?= Html::encode($model->car->placa) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="async-mobile-label">Días</div>
                        <div class="async-mobile-value"><?= Html::encode((string) ($model->cantidad_dias ?? '—')) ?></div>
                    </div>
                    <div class="col-8">
                        <div class="async-mobile-label">Precio / día</div>
                        <div class="async-mobile-value">₡<?= number_format((float) ($model->precio_por_dia ?? 0), 2) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="async-mobile-label">Total</div>
                        <div class="async-mobile-value fw-semibold text-primary">₡<?= number_format((float) ($model->total_precio ?? 0), 2) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-fechas"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-fechas-tab">
            <div class="async-mobile-kv">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="async-mobile-label">
                            <span class="material-symbols-outlined align-middle" style="font-size:16px;">calendar_today</span>
                            Fecha inicio
                        </div>
                        <div class="async-mobile-value">
                            <?= $model->fecha_inicio ? Html::encode(date('d/m/Y', strtotime($model->fecha_inicio))) : 'N/A' ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="async-mobile-label">
                            <span class="material-symbols-outlined align-middle" style="font-size:16px;">event</span>
                            Fecha fin
                        </div>
                        <div class="async-mobile-value">
                            <?= $model->fecha_final ? Html::encode(date('d/m/Y', strtotime($model->fecha_final))) : 'N/A' ?>
                        </div>
                    </div>
                    <?php if ($model->correapartir_enabled && $model->fecha_correapartir): ?>
                    <div class="col-12">
                        <div class="async-mobile-label">Corre a partir</div>
                        <div class="async-mobile-value"><?= Html::encode(date('d/m/Y', strtotime($model->fecha_correapartir))) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-acciones"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-acciones-tab">
            <div class="d-grid gap-2 async-mobile-actions">
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">visibility</span> Ver orden',
                    ['view', 'id' => $model->id],
                    ['class' => 'btn btn-outline-primary']
                ) ?>
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">edit</span> Editar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">delete</span> Eliminar',
                    ['delete', 'id' => $model->id],
                    [
                        'class' => 'btn btn-outline-danger',
                        'data' => [
                            'confirm' => '¿Está seguro que desea eliminar esta orden asincrónica?',
                            'method' => 'post',
                        ],
                    ]
                ) ?>
            </div>
        </div>
    </div>
</div>
