<?php
/** @var yii\web\View $this */
/** @var app\models\Client $model */
/** @var bool $hideHeader Ocultar título duplicado (p. ej. en acordeón móvil donde el nombre va en el botón) */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$hideHeader = !empty($hideHeader);
?>
<div class="client-mobile-pane">
    <?php if (!$hideHeader): ?>
    <div class="mb-3 text-center">
        <h5 class="mb-2">
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
    </div>
    <?php else: ?>
    <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
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
    </div>
    <?php endif; ?>
    <div class="client-mobile-details small">
        <p class="mb-2"><strong>🆔 Cédula:</strong> <?= Html::encode($model->cedula_fisica) ?></p>
        <p class="mb-2"><strong>📱 WhatsApp:</strong> <?= Html::encode($model->whatsapp) ?></p>
        <p class="mb-2"><strong>📧 Email:</strong> <?= Html::encode($model->email) ?></p>
        <p class="mb-2"><strong>📅 Registro:</strong> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
        <?php if ($model->tipo_identificacion): ?>
            <p class="mb-0 text-muted">
                <strong>🏛️ Hacienda:</strong>
                <?= Html::encode($model->tipo_identificacion) ?> |
                <?= Html::encode($model->situacion_tributaria) ?>
            </p>
        <?php endif; ?>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center mt-4 client-mobile-actions">
        <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-sm btn-info" title="Ver Detalles">
            <span class="material-symbols-outlined" style="font-size: 18px;">visibility</span>
        </a>
        <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-sm btn-primary" title="Editar">
            <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
        </a>
        <a href="<?= Url::to(['/rental/create', 'client_id' => $model->id]) ?>" class="btn btn-sm btn-success" title="Nuevo Alquiler">
            <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
        </a>
        <button type="button" class="btn btn-sm btn-warning" title="Compartir"
                onclick='shareClient(<?= (int) $model->id ?>, <?= Json::encode($model->fullNameUppercase) ?>, <?= Json::encode($model->cedula_fisica) ?>, <?= Json::encode($model->whatsapp) ?>, <?= Json::encode($model->email) ?>)'>
            <span class="material-symbols-outlined" style="font-size: 18px;">share</span>
        </button>
        <?php if ($model->status === 'inactive'): ?>
            <a href="<?= Url::to(['reactivate', 'id' => $model->id]) ?>"
               class="btn btn-sm btn-secondary"
               title="Reactivar"
               data-confirm="¿Estás seguro de reactivar este cliente?"
               data-method="post">
                <span class="material-symbols-outlined" style="font-size: 18px;">replay</span>
            </a>
        <?php else: ?>
            <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>"
               class="btn btn-sm btn-danger"
               title="Eliminar"
               data-confirm="¿Estás seguro de eliminar este cliente?"
               data-method="post">
                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
            </a>
        <?php endif; ?>
    </div>
</div>
