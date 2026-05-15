<?php
/** @var yii\web\View $this */
/** @var app\models\MaintenanceOrder $model */
/** @var app\models\Car[] $cars */

use yii\helpers\Html;

$this->title = 'Editar ' . $model->order_id;
$this->params['breadcrumbs'][] = ['label' => 'Mantenimiento', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->order_id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="maintenance-order-update">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Ver orden', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?= $this->render('_form', ['model' => $model, 'cars' => $cars, 'isUpdate' => true]) ?>
        </div>
    </div>
</div>
