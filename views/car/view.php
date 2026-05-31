<?php
/** @var yii\web\View $this */
/** @var app\models\Car $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Vehículo: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Vehículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="car-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>🚗 <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('✏️ Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('← Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🚗 Información del Vehículo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>🆔 Placa:</strong> <?= Html::encode($model->placa) ?></p>
                            <?php if ($model->vin): ?>
                            <p><strong>🔢 VIN:</strong> <?= Html::encode($model->vin) ?></p>
                            <?php endif; ?>
                            <p><strong>👥 Pasajeros:</strong> <?= Html::encode($model->cantidad_pasajeros) ?></p>
                            <p><strong>🏢 Empresa:</strong> <?= Html::encode($model->empresa) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>📊 Estado:</strong> 
                                <span class="badge <?= $model->status === 'disponible' ? 'bg-success' : ($model->status === 'alquilado' ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= Html::encode($model->status) ?>
                                </span>
                            </p>
                            <p><strong>📅 Registro:</strong> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                            <p><strong>🔄 Actualizado:</strong> <?= Yii::$app->formatter->asDate($model->updated_at) ?></p>
                            <?php if ($model->marca): ?>
                            <p><strong>🏷️ Marca:</strong> <?= Html::encode($model->marca->name) ?></p>
                            <?php elseif ($model->marca_id): ?>
                            <p><strong>🏷️ Marca ID:</strong> <?= Html::encode($model->marca_id) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->caracteristicas): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Características</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(Html::encode($model->caracteristicas)) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($model->empresa_seguro): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🛡️ Información de Seguro</h5>
                </div>
                <div class="card-body">
                    <p><strong>🏢 Empresa:</strong> <?= Html::encode($model->empresa_seguro) ?></p>
                    <?php if ($model->telefono_seguro): ?>
                    <p><strong>📞 Teléfono:</strong> <?= Html::encode($model->telefono_seguro) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('✏️ Editar Vehículo', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('📋 Nuevo Alquiler', ['/rental/create', 'car_id' => $model->id], ['class' => 'btn btn-success']) ?>
                        <?= Html::a('📊 Ver Alquileres', ['/rental/index', 'car_id' => $model->id], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('🗑️ Eliminar', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar este vehículo?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>

            <?php $imagenUrl = $model->getImagenUrl(); ?>
            <?php if ($imagenUrl): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🖼️ Imagen</h5>
                </div>
                <div class="card-body">
                    <img src="<?= Html::encode($imagenUrl) ?>" class="img-fluid" alt="Imagen del vehículo">
                </div>
            </div>
            <?php endif; ?>

            <?php if ((int) $model->facebook_promo_enabled === 1): ?>
            <?php
                $bannerUrl = $model->getFacebookBannerUrl();
                $promoUrl = $model->getFacebookPromoUrl();
            ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📢 Promoción Facebook</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Estado:</strong>
                        <span class="badge bg-success">Activada</span>
                    </p>
                    <?php if ($bannerUrl): ?>
                        <p class="text-muted small mb-1">Banner del anuncio:</p>
                        <img src="<?= Html::encode($bannerUrl) ?>" class="img-fluid mb-3" alt="Banner Facebook" style="max-height: 200px; object-fit: contain;">
                    <?php else: ?>
                        <p class="text-muted small mb-3">Sin banner subido — en la landing se usará el banner genérico de FACTO RENT A CAR.</p>
                    <?php endif; ?>

                    <?php if ($promoUrl): ?>
                        <label class="form-label small">Enlace para anuncios</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="car-view-promo-url" readonly value="<?= Html::encode($promoUrl) ?>">
                            <button type="button" class="btn btn-outline-secondary" id="car-view-promo-copy">Copiar</button>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Guarda el vehículo con promoción activada para generar el enlace.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $this->registerJs(<<<'JS'
(function () {
    var btn = document.getElementById('car-view-promo-copy');
    var input = document.getElementById('car-view-promo-url');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
        input.select();
        var text = input.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                btn.textContent = 'Copiado';
                setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
            });
        }
    });
})();
JS
            );
            ?>
            <?php endif; ?>
        </div>
    </div>
</div>
