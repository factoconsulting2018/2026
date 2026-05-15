<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

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
                <table class="table table-hover table-striped mb-0">
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $row): ?>
                            <?php
                            /** @var app\models\Incident $row */
                            $paid = 0.0;
                            foreach ($row->payments as $p) {
                                $paid += (float) $p->amount;
                            }
                            $bal = max(0, (float) $row->total_amount - $paid);
                            ?>
                            <tr>
                                <td><?= (int) $row->id ?></td>
                                <td><?= Html::encode($row->client->full_name ?? '—') ?></td>
                                <td class="d-none d-md-table-cell"><?= Html::encode($row->client->cedula_fisica ?? '—') ?></td>
                                <td class="text-end"><?= number_format((float) $row->total_amount, 2) ?></td>
                                <td class="text-end text-success"><?= number_format($paid, 2) ?></td>
                                <td class="text-end fw-bold <?= $bal > 0.01 ? 'text-danger' : 'text-muted' ?>"><?= number_format($bal, 2) ?></td>
                                <td>
                                    <?php if ($row->status === \app\models\Incident::STATUS_OPEN): ?>
                                        <span class="badge bg-warning text-dark">Abierto</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Cerrado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell text-muted small"><?= Html::encode($row->created_at) ?></td>
                                <td class="text-nowrap">
                                    <?= Html::a(
                                        '<span class="material-symbols-outlined" style="font-size: 18px;">visibility</span>',
                                        ['view', 'id' => $row->id],
                                        ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver', 'encode' => false]
                                    ) ?>
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
