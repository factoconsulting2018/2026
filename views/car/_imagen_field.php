<?php
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Car $model */

use yii\helpers\Html;

$imagenUrl = $model->getImagenUrl();

$this->registerJsFile(
    '@web/js/image-compress.js',
    ['depends' => [\yii\web\YiiAsset::class], 'position' => \yii\web\View::POS_END]
);
?>
<div class="car-imagen-field mb-3">
    <?= $form->field($model, 'imagenFile')->fileInput([
        'accept' => 'image/png,image/jpeg,image/jpg,image/webp,image/gif',
        'class' => 'form-control',
        'data-compress' => '1',
        'data-max-side' => '1600',
        'data-quality' => '0.85',
        'data-threshold' => '500000',
        'data-mime' => 'image/jpeg',
    ])->hint('Formatos: JPG, PNG, WEBP o GIF. El sistema optimiza automáticamente las imágenes grandes en tu navegador antes de subirlas.') ?>

    <?php if ($imagenUrl): ?>
        <div class="mt-2">
            <p class="text-muted small mb-1">Imagen actual:</p>
            <img src="<?= Html::encode($imagenUrl) ?>" alt="Vehículo" class="img-thumbnail" style="max-width: 220px; max-height: 140px; object-fit: contain;">
        </div>
    <?php endif; ?>
</div>
