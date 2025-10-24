<?php
/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">
    <div class="text-center">
        <h1 class="display-1">❌</h1>
        <h1><?= Html::encode($this->title) ?></h1>
        
        <div class="alert alert-danger mt-4">
            <?= nl2br(Html::encode($message)) ?>
        </div>

        <p class="text-muted">
            El error anterior ocurrió mientras el servidor procesaba tu solicitud.
        </p>
        
        <p class="text-muted">
            Por favor contacta con nosotros si crees que esto es un error del servidor. Gracias.
        </p>

        <div class="mt-4">
            <a href="<?= \yii\helpers\Url::home() ?>" class="btn btn-primary">
                🏠 Volver al inicio
            </a>
        </div>
    </div>
</div>
