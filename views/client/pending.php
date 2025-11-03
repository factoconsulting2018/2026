<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $tab */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Nuevos Clientes';
$this->params['breadcrumbs'][] = $this->title;

$currentTab = $tab ?? 'pending';
?>

<div class="client-pending">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">person_add</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">group</span>Ver Todos los Clientes', ['index'], ['class' => 'btn btn-primary']) ?>
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
            setTimeout(function() {
                const alert = document.querySelector('.alert-success');
                if (alert) alert.remove();
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

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <?= Html::a('Pendientes', ['pending', 'tab' => 'pending'], [
                'class' => 'nav-link ' . ($currentTab === 'pending' ? 'active' : ''),
                'aria-selected' => $currentTab === 'pending' ? 'true' : 'false'
            ]) ?>
        </li>
        <li class="nav-item" role="presentation">
            <?= Html::a('Rechazados', ['pending', 'tab' => 'rejected'], [
                'class' => 'nav-link ' . ($currentTab === 'rejected' ? 'active' : ''),
                'aria-selected' => $currentTab === 'rejected' ? 'true' : 'false'
            ]) ?>
        </li>
    </ul>

    <?php Pjax::begin(); ?>
    
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => function($model, $key, $index, $widget) use ($currentTab) {
            $buttons = '';
            
            if ($currentTab === 'pending') {
                // Botones para clientes pendientes
                $buttons = Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">check_circle</span>Aprobar', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success me-2',
                    'data-confirm' => '¿Está seguro que desea aprobar este cliente?',
                    'data-method' => 'post'
                ]);
                
                $buttons .= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">cancel</span>Rechazar', ['reject', 'id' => $model->id], [
                    'class' => 'btn btn-warning me-2',
                    'data-confirm' => '¿Está seguro que desea rechazar este cliente?',
                    'data-method' => 'post'
                ]);
                
                $buttons .= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">delete</span>Eliminar', ['delete-permanently', 'id' => $model->id], [
                    'class' => 'btn btn-danger me-2',
                    'data-confirm' => '¿Está seguro que desea eliminar permanentemente este cliente? Esta acción no se puede deshacer.',
                    'data-method' => 'post'
                ]);
            }
            
            $buttons .= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">visibility</span>Ver', ['view', 'id' => $model->id], ['class' => 'btn btn-primary']);
            
            $borderColor = $currentTab === 'rejected' ? '#dc3545' : '#ff9800';
            $iconColor = $currentTab === 'rejected' ? '#dc3545' : '#ff9800';
            
            return '<div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">
                                        <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px; color: ' . $iconColor . ';">person</span>
                                        ' . Html::encode($model->full_name) . '
                                    </h5>
                                    <p class="card-text mb-2">
                                        <strong>Cédula:</strong> ' . Html::encode($model->cedula_fisica) . '<br>
                                        ' . (!empty($model->email) ? '<strong>Email:</strong> ' . Html::encode($model->email) . '<br>' : '') . '
                                        ' . (!empty($model->whatsapp) ? '<strong>WhatsApp:</strong> ' . Html::encode($model->whatsapp) . '<br>' : '') . '
                                        <strong>Fecha de Registro:</strong> ' . date('d/m/Y H:i', strtotime($model->created_at)) . '
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    ' . $buttons . '
                                </div>
                            </div>
                        </div>
                    </div>';
        },
        'layout' => "{items}\n<div class='d-flex justify-content-center mt-4'>{pager}</div>",
        'emptyText' => '<div class="alert alert-info text-center">
                          <span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">' . ($currentTab === 'rejected' ? 'block' : 'check_circle') . '</span>
                          <h5>' . ($currentTab === 'rejected' ? 'Sin clientes rechazados' : '¡Excelente!') . '</h5>
                          <p>' . ($currentTab === 'rejected' ? 'No hay clientes rechazados.' : 'No hay clientes pendientes de aprobación.') . '</p>
                        </div>',
        'emptyTextOptions' => ['class' => 'empty-result'],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

<style>
.client-pending .card {
    border-left: 4px solid #ff9800;
}
.client-pending .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}
.nav-tabs .nav-link.active {
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}
</style>
