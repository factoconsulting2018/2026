<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Vehículos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="car-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">directions_car</span><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nuevo Vehículo', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nombre, placa, VIN...">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="alquilado">Alquilado</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="fuera_servicio">Fuera de Servicio</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">business</span>Empresa</label>
                    <select name="empresa" class="form-select">
                        <option value="">Todas</option>
                        <option value="Facto Rent a Car">Facto Rent a Car</option>
                        <option value="Moviliza">Moviliza</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">clear</span>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>VIN</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Estado</th>
                            <th>Empresa</th>
                            <th>Precio/Día</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr>
                            <td><?= Html::encode($model->id) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2" style="font-size: 20px; color: #3fa9f5;">directions_car</span>
                                    <strong><?= Html::encode($model->nombre) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= Html::encode($model->placa) ?></span>
                            </td>
                            <td><?= Html::encode($model->vin ?? 'N/A') ?></td>
                            <td><?= Html::encode($model->marca ?? 'N/A') ?></td>
                            <td><?= Html::encode($model->modelo ?? 'N/A') ?></td>
                            <td><?= Html::encode($model->año ?? 'N/A') ?></td>
                            <td>
                                <?php
                                $statusConfig = [
                                    'disponible' => ['class' => 'bg-success', 'text' => 'Disponible', 'icon' => 'check_circle'],
                                    'alquilado' => ['class' => 'bg-warning', 'text' => 'Alquilado', 'icon' => 'schedule'],
                                    'mantenimiento' => ['class' => 'bg-info', 'text' => 'Mantenimiento', 'icon' => 'build'],
                                    'fuera_servicio' => ['class' => 'bg-danger', 'text' => 'Fuera de Servicio', 'icon' => 'error']
                                ];
                                $currentStatus = $statusConfig[$model->status] ?? $statusConfig['fuera_servicio'];
                                ?>
                                <span class="badge <?= $currentStatus['class'] ?>">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; margin-right: 4px;"><?= $currentStatus['icon'] ?></span>
                                    <?= $currentStatus['text'] ?>
                                </span>
                            </td>
                            <td><?= Html::encode($model->empresa ?? 'N/A') ?></td>
                            <td>
                                <strong>₡<?= number_format($model->precio_dia ?? 0, 2) ?></strong>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Ver detalles">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                    </a>
                                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Editar">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                    </a>
                                    <a href="<?= Url::to(['/rental/create', 'car_id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Nuevo alquiler">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">add_circle</span>
                                    </a>
                                    <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Eliminar"
                                       data-confirm="¿Estás seguro de eliminar este vehículo?" 
                                       data-method="post">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($dataProvider->getCount() == 0): ?>
            <div class="text-center py-5">
                <span class="material-symbols-outlined" style="font-size: 64px; color: #ccc;">directions_car</span>
                <h4 class="text-muted mt-3">No hay vehículos registrados</h4>
                <p class="text-muted">Comienza agregando tu primer vehículo al sistema.</p>
                <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Agregar Vehículo', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-4">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'options' => ['class' => 'pagination'],
            'linkOptions' => ['class' => 'page-link'],
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
        ]) ?>
    </div>

    <?php Pjax::end(); ?>
</div>