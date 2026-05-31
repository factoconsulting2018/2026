<?php
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Car $model */

use yii\helpers\Html;
use yii\helpers\Url;

$bannerUrl = $model->getFacebookBannerUrl();
$promoUrl = $model->getFacebookPromoUrl();
$isEnabled = (int) $model->facebook_promo_enabled === 1;

$this->registerJsFile(
    '@web/js/image-compress.js',
    ['depends' => [\yii\web\YiiAsset::class], 'position' => \yii\web\View::POS_END]
);
?>
<div class="car-facebook-promo-field mb-3 mt-4">
    <h5 class="mb-3">📢 Promoción en Facebook</h5>

    <?= $form->field($model, 'facebook_promo_enabled')->checkbox([
        'label' => 'Facebook promoción activada',
        'value' => 1,
        'uncheck' => 0,
    ])->hint('Si está activada, este vehículo aparece en el selector de la landing promo y tendrá su propio enlace para anuncios.') ?>

    <?= $form->field($model, 'facebookBannerFile')->fileInput([
        'accept' => 'image/png,image/jpeg,image/jpg,image/webp,image/gif',
        'class' => 'form-control',
        'data-compress' => '1',
        'data-max-side' => '1600',
        'data-quality' => '0.85',
        'data-threshold' => '500000',
        'data-mime' => 'image/jpeg',
    ])->hint('Banner del anuncio (ideal 1200×628 px). Se optimiza automáticamente antes de subir.') ?>

    <?php if ($bannerUrl): ?>
        <div class="mt-2 mb-3">
            <p class="text-muted small mb-1">Banner actual:</p>
            <img src="<?= Html::encode($bannerUrl) ?>" alt="Banner Facebook" class="img-thumbnail" style="max-width: 100%; max-height: 180px; object-fit: contain;">
        </div>
    <?php endif; ?>

    <div id="facebook-promo-url-box" class="<?= $isEnabled ? '' : 'd-none' ?>">
        <?php if ($promoUrl): ?>
            <label class="form-label">Enlace público para anuncios</label>
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="facebook-promo-url-input" readonly value="<?= Html::encode($promoUrl) ?>">
                <button type="button" class="btn btn-outline-secondary" id="facebook-promo-copy-btn" title="Copiar enlace">
                    Copiar
                </button>
            </div>
            <p class="text-muted small mb-0">
                Usa este enlace en cada anuncio de Facebook. Lleva a la solicitud de membresía con el banner de este vehículo.
            </p>
        <?php elseif ($isEnabled): ?>
            <p class="text-muted small mb-0">
                Guarda el vehículo para generar el enlace público de promoción.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
$js = <<<'JS'
(function () {
    var checkbox = document.querySelector('[name="Car[facebook_promo_enabled]"]');
    var urlBox = document.getElementById('facebook-promo-url-box');
    var copyBtn = document.getElementById('facebook-promo-copy-btn');
    var urlInput = document.getElementById('facebook-promo-url-input');

    function toggleUrlBox() {
        if (!checkbox || !urlBox) return;
        urlBox.classList.toggle('d-none', !checkbox.checked);
    }
    if (checkbox) {
        checkbox.addEventListener('change', toggleUrlBox);
        toggleUrlBox();
    }

    if (copyBtn && urlInput) {
        copyBtn.addEventListener('click', function () {
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            var text = urlInput.value;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    copyBtn.textContent = 'Copiado';
                    setTimeout(function () { copyBtn.textContent = 'Copiar'; }, 2000);
                }).catch(function () {
                    document.execCommand('copy');
                });
            } else {
                document.execCommand('copy');
                copyBtn.textContent = 'Copiado';
                setTimeout(function () { copyBtn.textContent = 'Copiar'; }, 2000);
            }
        });
    }
})();
JS;
$this->registerJs($js);
?>
