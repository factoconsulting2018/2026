<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */
/** @var app\models\Client[] $clients */
/** @var app\models\Car[] $cars */

$this->title = 'Crear Renta Asincrónica';
$this->params['breadcrumbs'][] = ['label' => 'Rentas Asincrónicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="async-rental-create">
    <h1>
        <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #ff6600;">
            history_edu
        </span>
        <?= Html::encode($this->title) ?>
    </h1>

    <p class="text-muted">
        Use este formulario para registrar órdenes históricas sin afectar la disponibilidad actual de los vehículos.
    </p>

    <?= $this->render('_form', [
        'model' => $model,
        'clients' => $clients,
        'cars' => $cars,
    ]) ?>
</div>

