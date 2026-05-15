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

$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
$page = $pagination ? (int) $pagination->page : 0;
$pageSize = $pagination ? (int) $pagination->pageSize : count($dataProvider->getModels());
$start = $totalCount > 0 ? ($page * $pageSize) + 1 : 0;
$end = min(($page + 1) * $pageSize, $totalCount);

$pagerConfig = [
    'pagination' => $pagination,
    'options' => ['class' => 'pagination pagination-modern mb-0'],
    'linkContainerOptions' => ['class' => 'page-item'],
    'linkOptions' => ['class' => 'page-link'],
    'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
    'prevPageCssClass' => 'page-item',
    'nextPageCssClass' => 'page-item',
    'activePageCssClass' => 'active',
    'disabledPageCssClass' => 'disabled',
];

$mobileAccordionClasses = [
    MaintenanceOrder::STATUS_PENDIENTE => 'maint-mobile-item-pendiente',
    MaintenanceOrder::STATUS_EN_PROCESO => 'maint-mobile-item-en-proceso',
    MaintenanceOrder::STATUS_ATENDIDA => 'maint-mobile-item-atendida',
];

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
.maintenance-car-thumb-mobile {
    width: 100%;
    max-width: 200px;
    height: auto;
    max-height: 120px;
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

.maint-index-mobile .accordion-button {
    white-space: normal;
    line-height: 1.35;
    font-size: 0.92rem;
    padding: 0.65rem 0.85rem;
}
.maint-index-mobile .accordion-button:not(.collapsed) {
    font-weight: 600;
}
.maint-index-mobile .maint-acc-meta {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.15rem;
}
.maint-mobile-item-pendiente .accordion-button:not(.collapsed) {
    background-color: #f8d7da;
    color: #842029;
}
.maint-mobile-item-en-proceso .accordion-button:not(.collapsed) {
    background-color: #d1e7dd;
    color: #0f5132;
}
.maint-mobile-item-atendida .accordion-button:not(.collapsed) {
    background-color: #f8fafc;
    color: #212529;
}
.maint-index-mobile .accordion-body.maintenance-row-pendiente {
    background-color: #f8d7da;
    color: #842029;
}
.maint-index-mobile .accordion-body.maintenance-row-en-proceso {
    background-color: #d1e7dd;
    color: #0f5132;
}
.maint-index-mobile .accordion-body.maintenance-row-atendida {
    background-color: #ffffff;
    color: #212529;
}
.maint-mobile-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 0.15rem;
}
.maint-mobile-value {
    font-size: 0.95rem;
    color: #1e293b;
    word-break: break-word;
}
.maint-mobile-tabs .nav-link {
    font-size: 0.82rem;
    padding: 0.45rem 0.35rem;
    color: #475569;
}
.maint-mobile-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}
.maint-mobile-notes {
    font-size: 0.9rem;
    line-height: 1.45;
    max-height: 200px;
    overflow-y: auto;
}
.maint-pagination-bar {
    padding: 14px 18px;
    background: #fff;
    border: 1px solid #e6ecf3;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
}
.maint-pagination-summary {
    font-size: 0.95rem;
    color: #5b6b82;
    font-weight: 500;
}
.pagination-modern {
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}
.pagination-modern .page-item .page-link {
    min-width: 40px;
    height: 40px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    border: 1px solid #d9e2ec;
    background: #ffffff;
    color: #1b305b;
    font-weight: 600;
}
.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border-color: #0d6efd;
    color: #ffffff;
}

@media (max-width: 767.98px) {
    .maintenance-order-index .page-header-row {
        flex-direction: column;
        align-items: stretch !important;
    }
    .maintenance-order-index .page-header-row .btn {
        width: 100%;
        justify-content: center;
    }
    .maint-mobile-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .maint-mobile-tabs .nav-item {
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .maint-mobile-tabs .nav-link {
        font-size: 0.78rem;
    }
}
CSS);
?>

<div class="maintenance-order-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 page-header-row">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #0d6efd;">build</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a(
            '<span class="material-symbols-outlined align-middle" style="font-size: 20px;">add</span> Nueva orden',
            ['create'],
            ['class' => 'btn btn-primary d-inline-flex align-items-center justify-content-center gap-1', 'encode' => false]
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
                               placeholder="Nº orden, vehículo, placa, taller, notas…">
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

    <div class="d-none d-md-block card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>Fecha</th>
                            <th>Taller</th>
                            <th class="d-none d-lg-table-cell">Notas</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                            <th class="text-end" style="width: 88px;">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalCount === 0): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No hay órdenes de mantenimiento.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($dataProvider->getModels() as $row): ?>
                            <?php /** @var MaintenanceOrder $row */ ?>
                            <tr class="<?= Html::encode($row->getRowClass()) ?>">
                                <td class="fw-semibold"><?= Html::encode($row->order_id) ?></td>
                                <td><?= Html::encode($row->car->nombre ?? '—') ?></td>
                                <td><?= Html::encode($row->car->placa ?? '—') ?></td>
                                <td><?= Html::encode(Yii::$app->formatter->asDate($row->order_date, 'php:d/m/Y')) ?></td>
                                <td class="small"><?= Html::encode($row->taller ?: '—') ?></td>
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
        <?php if ($pagination && $pagination->pageCount > 1): ?>
            <div class="card-footer">
                <?= LinkPager::widget($pagerConfig) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-md-none maint-index-mobile">
        <?php
        $models = $dataProvider->getModels();
        if (count($models) === 0): ?>
            <p class="text-muted text-center py-4 mb-0">No hay órdenes de mantenimiento.</p>
        <?php else: ?>
            <div class="accordion accordion-flush border rounded overflow-hidden" id="maintMobileAccordion">
                <?php foreach ($models as $i => $row):
                    /** @var MaintenanceOrder $row */
                    $accId = 'maint-acc-' . $row->id;
                    $headingId = 'maint-acc-heading-' . $row->id;
                    $itemClass = $mobileAccordionClasses[$row->status] ?? '';
                    $carLabel = $row->car ? $row->car->nombre : 'Sin vehículo';
                    $fechaLabel = Yii::$app->formatter->asDate($row->order_date, 'php:d/m/Y');
                    ?>
                    <div class="accordion-item <?= Html::encode($itemClass) ?>">
                        <h2 class="accordion-header" id="<?= Html::encode($headingId) ?>">
                            <button class="accordion-button <?= $i !== 0 ? 'collapsed' : '' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= Html::encode($accId) ?>"
                                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                    aria-controls="<?= Html::encode($accId) ?>">
                                <div class="w-100">
                                    <div class="fw-semibold"><?= Html::encode($row->order_id) ?></div>
                                    <div class="maint-acc-meta">
                                        <?= Html::encode($carLabel) ?> · <?= Html::encode($fechaLabel) ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= Html::encode($accId) ?>"
                             class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             aria-labelledby="<?= Html::encode($headingId) ?>"
                             data-bs-parent="#maintMobileAccordion">
                            <div class="accordion-body px-2 py-3 <?= Html::encode($row->getRowClass()) ?>">
                                <?= $this->render('_index_mobile_pane', ['model' => $row]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="maint-pagination-bar d-flex flex-column align-items-center gap-3 mt-4">
            <div class="maint-pagination-summary text-center">
                Mostrando <?= $start ?> - <?= $end ?> de <?= $totalCount ?> órdenes
            </div>
            <?php if ($pagination && $pagination->pageCount > 1): ?>
                <?= LinkPager::widget($pagerConfig) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
