<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */

use app\models\Incident;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Insidentes';
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
    'paid' => 'inc-mobile-item-pagado',
    'pending' => 'inc-mobile-item-pendiente',
];

$this->registerCss(<<<'CSS'
.inc-index-mobile .accordion-button {
    white-space: normal;
    line-height: 1.35;
    font-size: 0.92rem;
    padding: 0.65rem 0.85rem;
}
.inc-index-mobile .accordion-button:not(.collapsed) {
    font-weight: 600;
}
.inc-index-mobile .inc-acc-meta {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.15rem;
}
.inc-mobile-item-pendiente .accordion-button:not(.collapsed) {
    background-color: #f8d7da;
    color: #842029;
}
.inc-mobile-item-pagado .accordion-button:not(.collapsed) {
    background-color: #d1e7dd;
    color: #0f5132;
}
.inc-index-mobile .accordion-body.inc-body-pendiente {
    background-color: #f8d7da;
    color: #842029;
}
.inc-index-mobile .accordion-body.inc-body-pagado {
    background-color: #d1e7dd;
    color: #0f5132;
}
.inc-mobile-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 0.15rem;
}
.inc-mobile-value {
    font-size: 0.95rem;
    color: #1e293b;
    word-break: break-word;
}
.inc-mobile-tabs .nav-link {
    font-size: 0.82rem;
    padding: 0.45rem 0.35rem;
    color: #475569;
}
.inc-mobile-tabs .nav-link.active {
    color: #dc3545;
    font-weight: 600;
}
.inc-mobile-notes {
    font-size: 0.9rem;
    line-height: 1.45;
    max-height: 160px;
    overflow-y: auto;
}
.inc-mobile-actions .btn {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.inc-pagination-bar {
    padding: 14px 18px;
    background: #fff;
    border: 1px solid #e6ecf3;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
}
.inc-pagination-summary {
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
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
    border-color: #dc3545;
    color: #ffffff;
}
@media (max-width: 767.98px) {
    .incident-index .page-header-row {
        flex-direction: column;
        align-items: stretch !important;
    }
    .incident-index .page-header-row .btn {
        width: 100%;
        justify-content: center;
        min-height: 42px;
    }
    .incident-index .inc-search-actions {
        flex-direction: column;
    }
    .incident-index .inc-search-actions .btn {
        width: 100%;
        min-height: 42px;
    }
    .inc-mobile-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .inc-mobile-tabs .nav-item {
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .inc-mobile-tabs .nav-link {
        font-size: 0.78rem;
    }
    #modalDeleteIncident .modal-footer {
        flex-direction: column;
        gap: 0.5rem;
    }
    #modalDeleteIncident .modal-footer .btn {
        width: 100%;
        margin: 0;
    }
}
CSS);
?>

<div class="incident-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 page-header-row">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #dc3545;">car_crash</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a(
            '<span class="material-symbols-outlined align-middle" style="font-size: 20px;">add</span> Nuevo Insidente',
            ['create'],
            ['class' => 'btn btn-primary d-inline-flex align-items-center justify-content-center gap-1', 'encode' => false]
        ) ?>
    </div>

    <p class="small text-muted mb-3">
        <span class="badge bg-danger me-1">&nbsp;</span> Saldo pendiente por pagar
        <span class="badge bg-success ms-2 me-1">&nbsp;</span> Pagado (sin saldo)
    </p>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label mb-0" for="incident-search">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
                        <input type="text" name="search" id="incident-search" class="form-control"
                               value="<?= Html::encode($search) ?>"
                               placeholder="Cliente, cédula, notas o número de caso…">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2 inc-search-actions">
                    <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </form>
        </div>
    </div>

    <div class="d-none d-md-block card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Cédula</th>
                            <th class="text-end">Total (¢)</th>
                            <th class="text-end">Abonado (¢)</th>
                            <th class="text-end">Saldo (¢)</th>
                            <th>Estado</th>
                            <th class="d-none d-lg-table-cell">Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalCount === 0): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay insidentes registrados<?= $search !== '' ? ' que coincidan con la búsqueda' : '' ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($dataProvider->getModels() as $row): ?>
                            <?php
                            /** @var Incident $row */
                            $paid = $row->getPaidTotal();
                            $bal = $row->getBalance();
                            $isPaid = ($bal < 0.01);
                            $rowClass = $isPaid ? 'table-success' : 'table-danger';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= (int) $row->id ?></td>
                                <td><?= Html::encode($row->client->full_name ?? '—') ?></td>
                                <td><?= Html::encode($row->client->cedula_fisica ?? '—') ?></td>
                                <td class="text-end"><?= number_format((float) $row->total_amount, 2) ?></td>
                                <td class="text-end"><?= number_format($paid, 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format($bal, 2) ?></td>
                                <td>
                                    <?php if ($row->status === Incident::STATUS_OPEN): ?>
                                        <span class="badge bg-warning text-dark">Abierto</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Cerrado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell small"><?= Html::encode($row->created_at) ?></td>
                                <td class="text-end text-nowrap">
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size: 18px;">visibility</span>',
                                        ['view', 'id' => $row->id],
                                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver', 'encode' => false]
                                    ) ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                            data-bs-toggle="modal" data-bs-target="#modalDeleteIncident"
                                            data-delete-url="<?= Html::encode(Url::to(['delete', 'id' => $row->id])) ?>">
                                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                    </button>
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

    <div class="d-md-none inc-index-mobile">
        <?php
        $models = $dataProvider->getModels();
        if (count($models) === 0): ?>
            <p class="text-muted text-center py-4 mb-0">
                No hay insidentes registrados<?= $search !== '' ? ' que coincidan con la búsqueda' : '' ?>.
                <?= Html::a('Registrar el primero', ['create'], ['class' => 'btn btn-link']) ?>
            </p>
        <?php else: ?>
            <div class="accordion accordion-flush border rounded overflow-hidden" id="incMobileAccordion">
                <?php foreach ($models as $i => $row):
                    /** @var Incident $row */
                    $paid = $row->getPaidTotal();
                    $bal = $row->getBalance();
                    $isPaid = ($bal < 0.01);
                    $payKey = $isPaid ? 'paid' : 'pending';
                    $itemClass = $mobileAccordionClasses[$payKey];
                    $bodyClass = $isPaid ? 'inc-body-pagado' : 'inc-body-pendiente';
                    $accId = 'inc-acc-' . $row->id;
                    $headingId = 'inc-acc-heading-' . $row->id;
                    $clientLabel = $row->client->full_name ?? 'Sin cliente';
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
                                    <div class="fw-semibold">Caso #<?= (int) $row->id ?></div>
                                    <div class="inc-acc-meta">
                                        <?= Html::encode($clientLabel) ?> · Saldo ₡<?= number_format($bal, 2) ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= Html::encode($accId) ?>"
                             class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             aria-labelledby="<?= Html::encode($headingId) ?>"
                             data-bs-parent="#incMobileAccordion">
                            <div class="accordion-body px-2 py-3 <?= Html::encode($bodyClass) ?>">
                                <?= $this->render('_index_mobile_pane', [
                                    'model' => $row,
                                    'paid' => $paid,
                                    'balance' => $bal,
                                    'isPaid' => $isPaid,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="inc-pagination-bar d-flex flex-column align-items-center gap-3 mt-4">
            <div class="inc-pagination-summary text-center">
                Mostrando <?= $start ?> - <?= $end ?> de <?= $totalCount ?> casos
            </div>
            <?php if ($pagination && $pagination->pageCount > 1): ?>
                <?= LinkPager::widget($pagerConfig) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDeleteIncident" tabindex="-1" aria-labelledby="modalDeleteIncidentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger" id="modalDeleteIncidentLabel">Eliminar insidente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="post" id="form-delete-incident" action="">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="modal-body">
                    <p class="mb-3">Esta acción elimina el caso y todos los abonos. <strong>No se puede deshacer.</strong></p>
                    <label class="form-label" for="delete_password">Contraseña de autorización</label>
                    <input type="password" name="delete_password" id="delete_password" class="form-control" required autocomplete="off" placeholder="Ingrese la contraseña">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
document.getElementById('modalDeleteIncident').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    if (!btn || !btn.getAttribute('data-delete-url')) return;
    document.getElementById('form-delete-incident').setAttribute('action', btn.getAttribute('data-delete-url'));
    const pwd = document.getElementById('delete_password');
    if (pwd) pwd.value = '';
});
JS
);
?>
