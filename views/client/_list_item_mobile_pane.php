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
        <?php
        $waDigits = $model->getWhatsAppWaDigits();
        if ($waDigits !== null):
            $waHref = 'https://wa.me/' . $waDigits;
        ?>
            <a href="<?= Html::encode($waHref) ?>"
               class="btn btn-sm d-inline-flex align-items-center justify-content-center"
               style="background-color: #25d366; border-color: #25d366; color: #fff;"
               title="Escríbele por WhatsApp"
               target="_blank"
               rel="noopener noreferrer">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
        <?php endif; ?>
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
