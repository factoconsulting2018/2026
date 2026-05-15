<?php
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Car $model */

use yii\helpers\Html;

$imagenUrl = $model->getImagenUrl();
?>
<div class="car-imagen-field mb-3">
    <?= $form->field($model, 'imagenFile')->fileInput([
        'accept' => 'image/png,image/jpeg,image/jpg,image/webp,image/gif',
        'class' => 'form-control',
    ])->hint('Formatos: JPG, PNG, WEBP o GIF. Máximo 5 MB.') ?>

    <?php if ($imagenUrl): ?>
        <div class="mt-2">
            <p class="text-muted small mb-1">Imagen actual:</p>
            <img src="<?= Html::encode($imagenUrl) ?>" alt="Vehículo" class="img-thumbnail" style="max-width: 220px; max-height: 140px; object-fit: contain;">
        </div>
    <?php endif; ?>
</div>
