<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $statusFilter */

use app\models\MaintenanceOrder;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Mantenimiento';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<'CSS'
.maintenance-row-pendiente td {
    background-color: #f8d7da !important;
    color: #842029;
}
.maintenance-row-en-proceso td {
    background-color: #d1e7dd !important;
    color: #0f5132;
}
.maintenance-row-atendida td {
    background-color: #ffffff !important;
    color: #212529;
}
.maintenance-row-pendiente:hover td,
.maintenance-row-en-proceso:hover td,
.maintenance-row-atendida:hover td {
    filter: brightness(0.97);
}
.maintenance-car-thumb-wrap {
    width: 80px;
    text-align: right;
    vertical-align: middle;
}
.maintenance-car-thumb {
    width: 72px;
    height: 52px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    background: #fff;
    display: inline-block;
}
.maintenance-car-thumb-placeholder {
    width: 72px;
    height: 52px;
    border-radius: 6px;
    border: 1px dashed rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 0.6);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}
.maintenance-car-thumb-placeholder .material-symbols-outlined {
    font-size: 28px;
}
CSS);
?>

<div class="maintenance-order-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #0d6efd;">build</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a(
            '<span class="material-symbols-outlined align-middle" style="font-size: 20px;">add</span> Nueva orden',
            ['create'],
            ['class' => 'btn btn-primary', 'encode' => false]
        ) ?>
    </div>

    <p class="small text-muted mb-3">
        <span class="badge me-2" style="background:#f8d7da;color:#842029;">&nbsp;</span> Pendiente
        <span class="badge me-2" style="background:#d1e7dd;color:#0f5132;">&nbsp;</span> En proceso
        <span class="badge border me-2" style="background:#fff;">&nbsp;</span> Atendida
    </p>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label mb-0" for="maintenance-search">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
                        <input type="text" name="search" id="maintenance-search" class="form-control"
                               value="<?= Html::encode($search) ?>"
                               placeholder="Nº orden, vehículo, placa, notas…">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" for="maintenance-status">Estado</label>
                    <select name="status" id="maintenance-status" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (MaintenanceOrder::statusList() as $value => $label): ?>
                            <option value="<?= Html::encode($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>>
                                <?= Html::encode($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Vehículo</th>
                            <th class="d-none d-md-table-cell">Placa</th>
                            <th>Fecha</th>
                            <th class="d-none d-lg-table-cell">Notas</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                            <th class="text-end" style="width: 88px;">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dataProvider->getTotalCount() === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No hay órdenes de mantenimiento.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($dataProvider->getModels() as $row): ?>
                            <?php /** @var MaintenanceOrder $row */ ?>
                            <tr class="<?= Html::encode($row->getRowClass()) ?>">
                                <td class="fw-semibold"><?= Html::encode($row->order_id) ?></td>
                                <td><?= Html::encode($row->car->nombre ?? '—') ?></td>
                                <td class="d-none d-md-table-cell"><?= Html::encode($row->car->placa ?? '—') ?></td>
                                <td><?= Html::encode(Yii::$app->formatter->asDate($row->order_date, 'php:d/m/Y')) ?></td>
                                <td class="d-none d-lg-table-cell small text-truncate" style="max-width:220px;">
                                    <?= Html::encode(mb_strimwidth((string) $row->notes, 0, 80, '…')) ?>
                                </td>
                                <td><?= Html::encode($row->getStatusLabel()) ?></td>
                                <td class="text-end text-nowrap">
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size:18px;">visibility</span>',
                                        ['view', 'id' => $row->id],
                                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver', 'encode' => false]
                                    ) ?>
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size:18px;">edit</span>',
                                        ['update', 'id' => $row->id],
                                        ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Editar', 'encode' => false]
                                    ) ?>
                                </td>
                                <td class="maintenance-car-thumb-wrap">
                                    <?php
                                    $car = $row->car;
                                    $imgUrl = $car ? $car->getImagenUrl() : null;
                                    if ($imgUrl): ?>
                                        <?= Html::img($imgUrl, [
                                            'alt' => Html::encode($car->nombre ?? 'Vehículo'),
                                            'class' => 'maintenance-car-thumb',
                                            'loading' => 'lazy',
                                        ]) ?>
                                    <?php else: ?>
                                        <span class="maintenance-car-thumb-placeholder" title="Sin foto">
                                            <span class="material-symbols-outlined">directions_car</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($dataProvider->pagination->pageCount > 1): ?>
            <div class="card-footer">
                <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
