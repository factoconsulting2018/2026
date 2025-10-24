<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Client $model */

$this->title = 'Crear Cliente';
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px; color: #3fa9f5;">person_add</span><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">arrow_back</span>Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php
    // Mostrar flash messages
    $session = Yii::$app->session;
    
    // Flash message normal de error
    if ($session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">error</span>
            <?= Html::encode($session->getFlash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php
    // Flash message de cédula duplicada (oculto, para JavaScript)
    if ($session->hasFlash('cedula_duplicate')): 
        $cedulaData = $session->getFlash('cedula_duplicate');
    ?>
        <div data-flash="cedula_duplicate" style="display: none;"><?= json_encode($cedulaData) ?></div>
    <?php endif; ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
