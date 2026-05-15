<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\models\Incident;

$this->title = 'Insidentes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="incident-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #dc3545;">car_crash</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a(
            '<span class="material-symbols-outlined align-middle" style="font-size: 20px;">add</span> Nuevo Insidente',
            ['create'],
            ['class' => 'btn btn-primary', 'encode' => false]
        ) ?>
    </div>

    <p class="small text-muted mb-3">
        <span class="badge bg-danger me-1">&nbsp;</span> Saldo pendiente por pagar
        <span class="badge bg-success ms-2 me-1">&nbsp;</span> Pagado (sin saldo)
    </p>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label mb-0" for="incident-search">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
                        <input type="text" name="search" id="incident-search" class="form-control"
                               value="<?= Html::encode($search) ?>"
                               placeholder="Cliente, cédula, notas o número de caso…">
                    </div>
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
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th class="d-none d-md-table-cell">Cédula</th>
                            <th class="text-end">Total (¢)</th>
                            <th class="text-end">Abonado (¢)</th>
                            <th class="text-end">Saldo (¢)</th>
                            <th>Estado</th>
                            <th class="d-none d-lg-table-cell">Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $row): ?>
                            <?php
                            /** @var Incident $row */
                            $paid = 0.0;
                            foreach ($row->payments as $p) {
                                $paid += (float) $p->amount;
                            }
                            $bal = max(0, (float) $row->total_amount - $paid);
                            $isPaid = ($bal < 0.01);
                            $rowClass = $isPaid ? 'table-success' : 'table-danger';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= (int) $row->id ?></td>
                                <td><?= Html::encode($row->client->full_name ?? '—') ?></td>
                                <td class="d-none d-md-table-cell"><?= Html::encode($row->client->cedula_fisica ?? '—') ?></td>
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
            <?php if ($dataProvider->getTotalCount() === 0): ?>
                <div class="p-4 text-center text-muted">
                    No hay insidentes registrados<?= $search !== '' ? ' que coincidan con la búsqueda' : '' ?>.
                    <?= Html::a('Registrar el primero', ['create'], ['class' => 'btn btn-link']) ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($dataProvider->pagination->getPageCount() > 1): ?>
            <div class="card-footer">
                <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalDeleteIncident" tabindex="-1" aria-labelledby="modalDeleteIncidentLabel" aria-hidden="true">
    <div class="modal-dialog">
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
