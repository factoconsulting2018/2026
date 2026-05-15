<?php
/** @var yii\web\View $this */
/** @var app\models\MaintenanceOrder $model */

use app\models\MaintenanceOrder;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->order_id;
$this->params['breadcrumbs'][] = ['label' => 'Mantenimiento', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$rowClass = $model->getRowClass();
$this->registerCss(<<<CSS
.maintenance-detail-card.{$rowClass} {
    border-width: 2px;
}
.maintenance-row-pendiente.maintenance-detail-card { border-color: #dc3545; background: #f8d7da; }
.maintenance-row-en-proceso.maintenance-detail-card { border-color: #198754; background: #d1e7dd; }
.maintenance-row-atendida.maintenance-detail-card { border-color: #dee2e6; background: #fff; }
CSS);
?>

<div class="maintenance-order-view">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2">build</span>
            <?= Html::encode($model->order_id) ?>
        </h1>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Listado', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="card maintenance-detail-card <?= Html::encode($rowClass) ?> mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Vehículo</div>
                    <div class="fw-semibold"><?= Html::encode($model->car ? MaintenanceOrder::carDropdownLabel($model->car) : '—') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Fecha</div>
                    <div class="fw-semibold"><?= Html::encode(Yii::$app->formatter->asDate($model->order_date, 'php:d/m/Y')) ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Estado</div>
                    <div class="fw-semibold"><?= Html::encode($model->getStatusLabel()) ?></div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Taller</div>
                    <div class="fw-semibold"><?= Html::encode($model->taller ?: '—') ?></div>
                    <p class="small text-muted mb-0 mt-1">Taller o talleres donde se programará el envío del vehículo.</p>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Notas pendientes</div>
                    <div class="border rounded p-3 bg-white bg-opacity-75" style="white-space: pre-wrap;"><?= Html::encode($model->notes ?: '—') ?></div>
                </div>
                <div class="col-md-6 small text-muted">
                    Registrado: <?= Html::encode($model->created_at) ?>
                </div>
                <div class="col-md-6 small text-muted text-md-end">
                    Actualizado: <?= Html::encode($model->updated_at) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-bold">Cambiar estado</div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'action' => ['change-status', 'id' => $model->id],
                'method' => 'post',
                'options' => ['class' => 'd-flex flex-wrap gap-2 align-items-end'],
            ]); ?>
            <div>
                <label class="form-label mb-1">Estado</label>
                <select name="status" class="form-select" style="min-width: 180px;">
                    <?php foreach (MaintenanceOrder::statusList() as $value => $label): ?>
                        <option value="<?= Html::encode($value) ?>" <?= $model->status === $value ? 'selected' : '' ?>>
                            <?= Html::encode($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?= Html::submitButton('Actualizar estado', ['class' => 'btn btn-success']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
    <?= Html::submitButton('Eliminar orden', [
        'class' => 'btn btn-outline-danger',
        'data' => [
            'confirm' => '¿Eliminar esta orden de mantenimiento?',
            'method' => 'post',
        ],
    ]) ?>
    <?= Html::endForm() ?>
</div>
