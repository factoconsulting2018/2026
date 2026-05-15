<?php
/** @var yii\web\View $this */
/** @var app\models\MaintenanceOrder $model */
/** @var app\models\Car[] $cars */

use yii\helpers\Html;

$this->title = 'Nueva orden de mantenimiento';
$this->params['breadcrumbs'][] = ['label' => 'Mantenimiento', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="maintenance-order-create">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px;">add_circle</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a('← Volver al listado', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <p class="text-muted">Seleccione el vehículo, confirme la fecha y describa las notas pendientes. Al guardar se generará el número de orden automáticamente.</p>

    <div class="card border-primary">
        <div class="card-header bg-primary text-white fw-bold">Datos de la orden</div>
        <div class="card-body">
            <?= $this->render('_form', ['model' => $model, 'cars' => $cars, 'isUpdate' => false]) ?>
        </div>
    </div>
</div>
