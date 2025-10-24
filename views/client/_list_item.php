<?php
/** @var yii\web\View $this */
/** @var app\models\Client $model */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="card">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="card-title mb-2">
                    <?= Html::encode($model->fullNameUppercase) ?>
                    <?php if ($model->status === 'active'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                    <?php if ($model->es_cliente_facto): ?>
                        <span class="badge bg-primary">Facto</span>
                    <?php endif; ?>
                    <?php if ($model->es_aliado): ?>
                        <span class="badge bg-info">Aliado</span>
                    <?php endif; ?>
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>🆔 Cédula:</strong> <?= Html::encode($model->cedula_fisica) ?></p>
                        <p class="mb-1"><strong>📱 WhatsApp:</strong> <?= Html::encode($model->whatsapp) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>📧 Email:</strong> <?= Html::encode($model->email) ?></p>
                        <p class="mb-1"><strong>📅 Registro:</strong> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                    </div>
                </div>
                <?php if ($model->tipo_identificacion): ?>
                <div class="mt-2">
                    <small class="text-muted">
                        <strong>🏛️ Hacienda:</strong> 
                        <?= Html::encode($model->tipo_identificacion) ?> | 
                        <?= Html::encode($model->situacion_tributaria) ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                    <!-- READ - Ver detalles -->
                    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-sm btn-info" title="Ver Detalles">
                        <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                    </a>
                    
                    <!-- UPDATE - Editar -->
                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary" title="Editar">
                        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                    </a>
                    
                    <!-- CREATE - Nuevo Alquiler -->
                    <a href="<?= Url::to(['/rental/create', 'client_id' => $model->id]) ?>" class="btn btn-sm btn-success" title="Nuevo Alquiler">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add_circle</span>
                    </a>
                    
                    <!-- SHARE - Compartir -->
                    <button type="button" class="btn btn-sm btn-warning" title="Compartir" onclick="shareClient(<?= $model->id ?>, '<?= Html::encode($model->fullNameUppercase) ?>', '<?= Html::encode($model->cedula_fisica) ?>', '<?= Html::encode($model->whatsapp) ?>', '<?= Html::encode($model->email) ?>')">
                        <span class="material-symbols-outlined" style="font-size: 16px;">share</span>
                    </button>
                    
                    <!-- DELETE - Eliminar/Reactivar -->
                    <?php if ($model->status === 'inactive'): ?>
                        <a href="<?= Url::to(['reactivate', 'id' => $model->id]) ?>" 
                           class="btn btn-sm btn-secondary" 
                           title="Reactivar"
                           data-confirm="¿Estás seguro de reactivar este cliente?" 
                           data-method="post">
                            <span class="material-symbols-outlined" style="font-size: 16px;">replay</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" 
                           class="btn btn-sm btn-danger" 
                           title="Eliminar"
                           data-confirm="¿Estás seguro de eliminar este cliente?" 
                           data-method="post">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

