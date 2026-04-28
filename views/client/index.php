<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $tipo */
/** @var string $estado */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Gestión de Clientes';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .client-index-mobile .nav-tabs .nav-link {
        font-size: 0.8rem;
        padding: 0.45rem 0.65rem;
        max-width: 42vw;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .client-index-mobile .nav-tabs .nav-link.active {
        font-weight: 600;
    }
    .client-mobile-tab-scroll {
        -webkit-overflow-scrolling: touch;
    }
');
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
            'layout' => "{items}\n<div class='d-flex justify-content-center mt-4'>{pager}</div>",
            'itemOptions' => ['class' => ''],
            'emptyText' => '<p class="text-muted text-center py-4 mb-0">No se encontraron clientes con los filtros seleccionados.</p>',
        ]); ?>
    </div>

    <div class="d-md-none client-index-mobile">
        <?php
        $models = $dataProvider->getModels();
        if (count($models) === 0): ?>
            <p class="text-muted text-center py-4 mb-0">No se encontraron clientes con los filtros seleccionados.</p>
        <?php else: ?>
            <ul class="nav nav-tabs client-mobile-tab-scroll flex-nowrap overflow-auto mb-0" role="tablist">
                <?php foreach ($models as $i => $model):
                    $full = (string) $model->fullNameUppercase;
                    $label = StringHelper::truncate($full, 24);
                    $tabId = 'client-m-tab-' . $model->id;
                    $paneId = 'client-m-pane-' . $model->id;
                    ?>
                    <li class="nav-item text-nowrap" role="presentation">
                        <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" id="<?= Html::encode($tabId) ?>"
                                data-bs-toggle="tab" data-bs-target="#<?= Html::encode($paneId) ?>"
                                type="button" role="tab" aria-controls="<?= Html::encode($paneId) ?>"
                                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
                            <?= Html::encode($label) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content border border-top-0 rounded-bottom bg-white px-2 py-3">
                <?php foreach ($models as $i => $model):
                    $paneId = 'client-m-pane-' . $model->id;
                    $tabId = 'client-m-tab-' . $model->id;
                    ?>
                    <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="<?= Html::encode($paneId) ?>"
                         role="tabpanel" aria-labelledby="<?= Html::encode($tabId) ?>">
                        <?= $this->render('_list_item_mobile_pane', ['model' => $model]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="d-flex justify-content-center mt-4">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'prevPageCssClass' => 'page-item',
                'nextPageCssClass' => 'page-item',
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>
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

