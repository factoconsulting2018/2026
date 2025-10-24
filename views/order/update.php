<?php
/** @var yii\web\View $this */
/** @var app\models\Order $model */

use yii\helpers\Html;

$this->title = 'Actualizar Venta: ' . $model->ticket_id;
$this->params['breadcrumbs'][] = ['label' => 'Ventas/Órdenes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->ticket_id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="order-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Volver', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
