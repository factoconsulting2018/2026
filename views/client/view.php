<?php
/** @var yii\web\View $this */
/** @var app\models\Client $model */
/** @var app\models\Rental[] $rentalHistory */
/** @var int $clientLibraryFileCount */

use yii\helpers\Html;

$this->title = 'Cliente: ' . $model->fullNameUppercase;
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$rentalHistory = $rentalHistory ?? [];
$clientLibraryFileCount = (int) ($clientLibraryFileCount ?? 0);
?>

<div class="client-view">
    <div class="card client-view-card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <?= $this->render('_view_tabs_content', [
                'model' => $model,
                'rentalHistory' => $rentalHistory,
                'clientLibraryFileCount' => $clientLibraryFileCount,
                'uid' => 'cvpage' . (int) $model->id,
                'embedInModal' => false,
            ]) ?>
        </div>
    </div>
</div>

<style>
.client-view-hero {
    background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
    color: #fff;
    border-radius: 12px;
    padding: 20px 22px;
}

.client-view-card {
    border-radius: 14px;
}

/* Tabs con color y texto blanco (anulan .nav-link del menú lateral) */
.client-view .client-colored-tabs,
#clientViewModal .client-colored-tabs {
    border-bottom: 2px solid #dee2e6;
    gap: 6px;
}

.client-view .client-colored-tabs .nav-item,
#clientViewModal .client-colored-tabs .nav-item {
    margin-bottom: 6px;
}

.client-view .client-colored-tabs .nav-link,
#clientViewModal .client-colored-tabs .nav-link {
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    padding: 8px 14px !important;
    min-height: auto !important;
    border: none !important;
    border-left: none !important;
    border-radius: 8px !important;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 0.9rem;
    background: #6c757d !important;
    opacity: 0.88;
    text-decoration: none;
    white-space: nowrap;
}

.client-view .client-colored-tabs .nav-link .material-symbols-outlined,
#clientViewModal .client-colored-tabs .nav-link .material-symbols-outlined {
    color: #ffffff !important;
    font-size: 16px !important;
}

.client-view .client-colored-tabs .nav-link:hover,
#clientViewModal .client-colored-tabs .nav-link:hover {
    color: #ffffff !important;
    opacity: 1;
}

.client-view .client-colored-tabs .nav-link.active,
#clientViewModal .client-colored-tabs .nav-link.active {
    color: #ffffff !important;
    opacity: 1;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
}

.client-view .client-colored-tabs .nav-link[id$="-datos-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-datos-tab"] {
    background: #22487a !important;
}
.client-view .client-colored-tabs .nav-link[id$="-hacienda-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-hacienda-tab"] {
    background: #6f42c1 !important;
}
.client-view .client-colored-tabs .nav-link[id$="-archivos-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-archivos-tab"] {
    background: #198754 !important;
}
.client-view .client-colored-tabs .nav-link[id$="-historial-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-historial-tab"] {
    background: #0d6efd !important;
}
.client-view .client-colored-tabs .nav-link[id$="-notas-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-notas-tab"] {
    background: #20c997 !important;
}
.client-view .client-colored-tabs .nav-link[id$="-acciones-tab"],
#clientViewModal .client-colored-tabs .nav-link[id$="-acciones-tab"] {
    background: #fd7e14 !important;
}

.client-view .client-colored-tabs .nav-link .badge,
#clientViewModal .client-colored-tabs .nav-link .badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    font-weight: 700;
}
</style>

<?php
$this->registerJsFile('@web/js/client-form.js', ['depends' => [yii\web\JqueryAsset::class]]);
$this->registerJs("
    window.currentClientId = {$model->id};
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof loadFiles === 'function') {
            loadFiles({$model->id}, '');
        }
    });
", \yii\web\View::POS_READY);
?>
