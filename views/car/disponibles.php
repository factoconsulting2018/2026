<?php
/** @var yii\web\View $this */
/** @var app\models\Car[] $cars */
/** @var string $fecha */

use yii\helpers\Html;

$this->title = 'Disponibles';
$this->params['breadcrumbs'][] = $this->title;

$fechaLabel = Yii::$app->formatter->asDate($fecha, 'long');
$hoy = date('Y-m-d');
$esHoy = ($fecha === $hoy);
?>

<div class="car-disponibles">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">
                <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">event_available</span>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted mb-0">
                Vehículos sin alquiler activo el <?= Html::encode($fechaLabel) ?>
                <?php if (!$esHoy): ?>
                    <span class="badge bg-secondary ms-1">no es hoy</span>
                <?php endif; ?>
            </p>
        </div>
        <form method="get" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label small mb-0">Consultar fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= Html::encode($fecha) ?>" max="2099-12-31">
            </div>
            <button type="submit" class="btn btn-primary">Ver</button>
            <?php if (!$esHoy): ?>
                <?= Html::a('Hoy', ['disponibles'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <?php if (count($cars) === 0): ?>
                <div class="p-5 text-center text-muted">
                    <span class="material-symbols-outlined d-block mb-2" style="font-size: 48px; opacity: 0.5;">no_crash</span>
                    No hay vehículos disponibles para esta fecha (o todos están en mantenimiento / fuera de servicio).
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehículo</th>
                                <th>Placa</th>
                                <th class="d-none d-md-table-cell">Pasajeros</th>
                                <th class="d-none d-md-table-cell">Estado en sistema</th>
                                <th class="d-none d-lg-table-cell">Empresa</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $labels = [
                                'disponible' => ['class' => 'success', 't' => 'Disponible'],
                                'alquilado' => ['class' => 'warning', 't' => 'Alquilado'],
                            ];
                            ?>
                            <?php foreach ($cars as $model): ?>
                                <?php $st = $labels[$model->status] ?? ['class' => 'secondary', 't' => $model->status]; ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="material-symbols-outlined align-middle me-1" style="font-size: 20px; color: #3fa9f5;">directions_car</span>
                                            <strong><?= Html::encode($model->nombre) ?></strong>
                                        </div>
                                        <div class="d-md-none mt-1">
                                            <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
                                            <?php if (!empty($model->cantidad_pasajeros)): ?>
                                                <span class="badge bg-light text-dark border ms-1">
                                                    <span class="material-symbols-outlined align-middle" style="font-size: 14px;">group</span>
                                                    <?= Html::encode((string) $model->cantidad_pasajeros) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= Html::encode($model->placa) ?></span></td>
                                    <td class="d-none d-md-table-cell"><?= Html::encode((string) ($model->cantidad_pasajeros ?? '—')) ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-<?= $st['class'] ?>"><?= Html::encode($st['t']) ?></span>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?= Html::encode($model->empresa ?? '—') ?></td>
                                    <td class="text-end text-nowrap">
                                        <?= Html::a(
                                            '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">visibility</span>',
                                            ['view', 'id' => $model->id],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver vehículo', 'encode' => false]
                                        ) ?>
                                        <?= Html::a(
                                            '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">add_circle</span>',
                                            ['/rental/create', 'car_id' => $model->id],
                                            ['class' => 'btn btn-sm btn-outline-success', 'title' => 'Nuevo alquiler', 'encode' => false]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted small">
                    Total: <?= count($cars) ?> vehículo(s) libres de renta en el día indicado.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
