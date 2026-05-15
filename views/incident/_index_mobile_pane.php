<?php
/** @var yii\web\View $this */
/** @var app\models\Incident $model */
/** @var float $paid */
/** @var float $balance */
/** @var bool $isPaid */

use app\models\Incident;
use yii\helpers\Html;
use yii\helpers\Url;

$tabUid = 'inc-tabs-' . (int) $model->id;
$statusCaseClass = $model->status === Incident::STATUS_OPEN ? 'bg-warning text-dark' : 'bg-secondary';
$statusCaseLabel = $model->status === Incident::STATUS_OPEN ? 'Abierto' : 'Cerrado';
$statusPayClass = $isPaid ? 'bg-success' : 'bg-danger';
$statusPayLabel = $isPaid ? 'Pagado' : 'Saldo pendiente';
?>
<div class="inc-mobile-pane">
    <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
        <span class="badge <?= $statusPayClass ?>"><?= Html::encode($statusPayLabel) ?></span>
        <span class="badge <?= $statusCaseClass ?>"><?= Html::encode($statusCaseLabel) ?></span>
    </div>

    <ul class="nav nav-tabs nav-fill inc-mobile-tabs" id="<?= Html::encode($tabUid) ?>" role="tablist">
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
                    id="<?= Html::encode($tabUid) ?>-montos-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?= Html::encode($tabUid) ?>-montos"
                    type="button"
                    role="tab"
                    aria-controls="<?= Html::encode($tabUid) ?>-montos"
                    aria-selected="false">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">payments</span>
                Montos
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

    <div class="tab-content inc-mobile-tab-content pt-3" id="<?= Html::encode($tabUid) ?>-content">
        <div class="tab-pane fade show active"
             id="<?= Html::encode($tabUid) ?>-general"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-general-tab">
            <div class="inc-mobile-kv">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="inc-mobile-label">Nº caso</div>
                        <div class="inc-mobile-value fw-semibold">#<?= (int) $model->id ?></div>
                    </div>
                    <div class="col-12">
                        <div class="inc-mobile-label">Cliente</div>
                        <div class="inc-mobile-value"><?= Html::encode($model->client->full_name ?? '—') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="inc-mobile-label">Cédula</div>
                        <div class="inc-mobile-value"><?= Html::encode($model->client->cedula_fisica ?? '—') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="inc-mobile-label">Registrado</div>
                        <div class="inc-mobile-value small">
                            <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i')) ?>
                        </div>
                    </div>
                    <?php if ($model->notes): ?>
                    <div class="col-12">
                        <div class="inc-mobile-label">Notas</div>
                        <div class="inc-mobile-notes border rounded p-2 bg-white"><?= nl2br(Html::encode($model->notes)) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-montos"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-montos-tab">
            <div class="inc-mobile-kv">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="inc-mobile-label">Total del caso</div>
                        <div class="inc-mobile-value fw-semibold">₡<?= number_format((float) $model->total_amount, 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="inc-mobile-label">Abonado</div>
                        <div class="inc-mobile-value">₡<?= number_format($paid, 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="inc-mobile-label">Saldo</div>
                        <div class="inc-mobile-value fw-bold <?= $isPaid ? 'text-success' : 'text-danger' ?>">
                            ₡<?= number_format($balance, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade"
             id="<?= Html::encode($tabUid) ?>-acciones"
             role="tabpanel"
             aria-labelledby="<?= Html::encode($tabUid) ?>-acciones-tab">
            <div class="d-grid gap-2 inc-mobile-actions">
                <?= Html::a(
                    '<span class="material-symbols-outlined align-middle" style="font-size:18px;">visibility</span> Ver caso',
                    ['view', 'id' => $model->id],
                    ['class' => 'btn btn-outline-primary', 'encode' => false]
                ) ?>
                <button type="button"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#modalDeleteIncident"
                        data-delete-url="<?= Html::encode(Url::to(['delete', 'id' => $model->id])) ?>">
                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">delete</span>
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
