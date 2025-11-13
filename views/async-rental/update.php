<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$this->title = 'Actualizar Renta Asincrónica: ' . ($model->rental_id ?: $model->id);
$this->params['breadcrumbs'][] = ['label' => 'Rentas Asincrónicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Actualizar';
?>

<div class="async-rental-update">
    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #ff6600;">
            edit_note
        </span>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= $this->render('_form', [
        'model' => $model,
        'clients' => $clients,
        'cars' => $cars,
    ]) ?>
</div>

