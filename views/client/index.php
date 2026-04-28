<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $tipo */
/** @var string $estado */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Clientes';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .client-index-mobile .accordion-button {
        white-space: normal;
        line-height: 1.3;
        font-size: 0.95rem;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }
    .client-index-mobile .accordion-button:not(.collapsed) {
        font-weight: 600;
    }

    .client-pagination-bar {
        padding: 14px 18px;
        background: #fff;
        border: 1px solid #e6ecf3;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .client-pagination-summary {
        font-size: 0.95rem;
        color: #5b6b82;
        font-weight: 500;
    }

    .pagination-modern {
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-modern .page-item .page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 1px solid #d9e2ec;
        background: #ffffff;
        color: #1b305b;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: none;
    }

    .pagination-modern .page-item .page-link:hover {
        background: #eef4ff;
        border-color: #b8cae6;
        color: #163055;
    }

    .pagination-modern .page-item.active .page-link {
        background: linear-gradient(135deg, #1b305b 0%, #22487a 100%);
        border-color: #1b305b;
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(27, 48, 91, 0.22);
    }

    .pagination-modern .page-item.disabled .page-link {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #9aa8bc;
    }

    @media (max-width: 768px) {
        .client-pagination-bar {
            padding: 12px;
        }

        .pagination-modern .page-item .page-link {
            min-width: 42px;
            height: 42px;
            border-radius: 10px;
        }

        .client-pagination-summary {
            font-size: 0.9rem;
        }
    }
');

$pagination = $dataProvider->pagination;
$start = $pagination->totalCount > 0 ? $pagination->offset + 1 : 0;
$end = $pagination->totalCount > 0
    ? min($pagination->offset + $pagination->limit, $pagination->totalCount)
    : 0;

$pagerConfig = [
    'pagination' => $pagination,
    'options' => ['class' => 'pagination pagination-modern mb-0'],
    'linkOptions' => ['class' => 'page-link'],
    'pageCssClass' => 'page-item',
    'prevPageCssClass' => 'page-item',
    'nextPageCssClass' => 'page-item',
    'activePageCssClass' => 'active',
    'disabledPageCssClass' => 'disabled',
    'prevPageLabel' => '«',
    'nextPageLabel' => '»',
    'maxButtonCount' => 5,
];
?>

<div class="client-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">group</span><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">add</span>Nuevo Cliente', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php
    // Mostrar flash messages
    $session = Yii::$app->session;
    
    if ($session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">check_circle</span>
            <?= Html::encode($session->getFlash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            // Auto-remover después de 3 segundos
            setTimeout(function() {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        </script>
    <?php endif; ?>
    
    <?php if ($session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">error</span>
            <?= Html::encode($session->getFlash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= Html::encode($search) ?>" 
                           placeholder="Nombre, cédula, email, teléfono...">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">category</span>Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="facto" <?= $tipo === 'facto' ? 'selected' : '' ?>>Clientes Facto</option>
                        <option value="aliado" <?= $tipo === 'aliado' ? 'selected' : '' ?>>Aliados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">toggle_on</span>Estado</label>
                    <select name="estado" class="form-select">
                        <option value="all" <?= $estado === 'all' || $estado === '' ? 'selected' : '' ?>>Todos</option>
                        <option value="active" <?= $estado === 'active' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactive" <?= $estado === 'inactive' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">search</span>Buscar</button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary"><span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">clear</span>Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <div class="d-none d-md-block">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_list_item',
            'layout' => "{items}\n<div class='client-pagination-bar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4'><div class='client-pagination-summary'>Mostrando {$start} - {$end} de {$pagination->totalCount} clientes</div>{pager}</div>",
            'itemOptions' => ['class' => ''],
            'emptyText' => '<p class="text-muted text-center py-4 mb-0">No se encontraron clientes con los filtros seleccionados.</p>',
            'pager' => $pagerConfig,
        ]); ?>
    </div>

    <div class="d-md-none client-index-mobile">
        <?php
        $models = $dataProvider->getModels();
        if (count($models) === 0): ?>
            <p class="text-muted text-center py-4 mb-0">No se encontraron clientes con los filtros seleccionados.</p>
        <?php else: ?>
            <div class="accordion accordion-flush border rounded overflow-hidden" id="clientMobileAccordion">
                <?php foreach ($models as $i => $model):
                    $accId = 'client-acc-' . $model->id;
                    $headingId = 'client-acc-heading-' . $model->id;
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="<?= Html::encode($headingId) ?>">
                            <button class="accordion-button <?= $i !== 0 ? 'collapsed' : '' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= Html::encode($accId) ?>"
                                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                    aria-controls="<?= Html::encode($accId) ?>">
                                <?= Html::encode($model->fullNameUppercase) ?>
                            </button>
                        </h2>
                        <div id="<?= Html::encode($accId) ?>"
                             class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                             aria-labelledby="<?= Html::encode($headingId) ?>"
                             data-bs-parent="#clientMobileAccordion">
                            <div class="accordion-body bg-white px-2 py-3">
                                <?= $this->render('_list_item_mobile_pane', [
                                    'model' => $model,
                                    'hideHeader' => true,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="client-pagination-bar d-flex flex-column align-items-center gap-3 mt-4">
            <div class="client-pagination-summary text-center">
                Mostrando <?= $start ?> - <?= $end ?> de <?= $pagination->totalCount ?> clientes
            </div>
            <?= LinkPager::widget($pagerConfig) ?>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>

<script>
function shareClient(clientId, nombre, cedula, whatsapp, email) {
    // Crear el texto con la información del cliente
    const clientInfo = `**${nombre}**
🆔 Cédula: ${cedula}
📱 WhatsApp: ${whatsapp}
📧 Email: ${email}

Información compartida desde FACTO RENT A CAR - Sistema de Gestión`;
    
    // Título del mensaje
    const title = `Información del Cliente - ${nombre}`;
    
    // Verificar si el navegador soporta Web Share API
    if (navigator.share) {
        navigator.share({
            title: title,
            text: clientInfo
        }).then(() => {
            showNotification('✅ Información del cliente compartida exitosamente', 'success');
        }).catch((error) => {
            console.log('Error al compartir:', error);
            // Fallback a copiar al portapapeles
            copyToClipboard(clientInfo);
        });
    } else {
        // Fallback: copiar información al portapapeles
        copyToClipboard(clientInfo);
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('✅ Información del cliente copiada al portapapeles', 'success');
        }).catch((error) => {
            console.error('Error al copiar:', error);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showNotification('✅ Información del cliente copiada al portapapeles', 'success');
        } else {
            showNotification('❌ Error al copiar información', 'error');
        }
    } catch (err) {
        console.error('Error en fallback copy:', err);
        showNotification('❌ Error al copiar información', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>

