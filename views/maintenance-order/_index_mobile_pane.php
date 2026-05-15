<?php
/** @var yii\web\View $this */
/** @var app\models\MaintenanceOrder $model */

use app\models\MaintenanceOrder;
use yii\helpers\Html;

$tabUid = 'maint-tabs-' . (int) $model->id;
$statusBadges = [
    MaintenanceOrder::STATUS_PENDIENTE => 'bg-danger',
    MaintenanceOrder::STATUS_EN_PROCESO => 'bg-success',
    MaintenanceOrder::STATUS_ATENDIDA => 'bg-light text-dark border',
];
$statusBadgeClass = $statusBadges[$model->status] ?? 'bg-secondary';
$car = $model->car;
$imgUrl = $car ? $car->getImagenUrl() : null;
?>
<div class="maint-mobile-pane">
    <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
        <span class="badge <?= $statusBadgeClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
    </div>

    <?php if ($imgUrl): ?>
    <div class="text-center mb-3">
        <?= Html::img($imgUrl, [
            'alt' => Html::encode($car->nombre ?? 'Vehículo'),
            'class' => 'maintenance-car-thumb maintenance-car-thumb-mobile',
            'loading' => 'lazy',
        ]) ?>
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs nav-fill maint-mobile-tabs" id="<?= Html::encode($tabUid) ?>" role="tablist">
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
                    id="<?= Html::encode($tabUid) ?>-detalle-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?= Html::encode($tabUid) ?>-detalle"
                    type="button"
                    role="tab"
                    aria-controls="<?= Html::encode($tabUid) ?>-detalle"
                    aria-selected="false">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">description</span>
                Detalle
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

    <div class="tab-content maint-mobile-tab-content pt-3" id="<?= Html::encode($tabUid) ?>-content">
        <div class="tab-pane fade show active"
             id="<?= Html::encode($tabUid) ?>-general"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-general-tab">
            <div class="maint-mobile-kv">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="maint-mobile-label">Nº orden</div>
                        <div class="maint-mobile-value fw-semibold"><?= Html::encode($model->order_id) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="maint-mobile-label">Vehículo</div>
                        <div class="maint-mobile-value"><?= Html::encode($car->nombre ?? '—') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="maint-mobile-label">Placa</div>
                        <div class="maint-mobile-value"><?= Html::encode($car->placa ?? '—') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="maint-mobile-label">Fecha</div>
                        <div class="maint-mobile-value">
                            <?= Html::encode(Yii::$app->formatter->asDate($model->order_date, 'php:d/m/Y')) ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="maint-mobile-label">Taller</div>
                        <div class="maint-mobile-value"><?= Html::encode($model->taller ?: '—') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-detalle"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-detalle-tab">
            <div class="maint-mobile-kv">
                <div class="maint-mobile-label mb-1">Notas pendientes</div>
                <div class="maint-mobile-notes border rounded p-2 bg-white">
                    <?= $model->notes
                        ? nl2br(Html::encode($model->notes))
                        : '<span class="text-muted">Sin notas</span>' ?>
                </div>
                <div class="row g-2 mt-2 small text-muted">
                    <div class="col-12">
                        <span class="maint-mobile-label d-inline">Registrado</span>
                        <?= Html::encode($model->created_at) ?>
                    </div>
                    <div class="col-12">
                        <span class="maint-mobile-label d-inline">Actualizado</span>
                        <?= Html::encode($model->updated_at) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-acciones"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-acciones-tab">
            <div class="d-grid gap-2 maint-mobile-actions">
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">visibility</span> Ver orden',
                    ['view', 'id' => $model->id],
                    ['class' => 'btn btn-outline-primary', 'encode' => false]
                ) ?>
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">edit</span> Editar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn btn-outline-secondary', 'encode' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>
