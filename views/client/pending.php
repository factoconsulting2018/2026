<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = 'Nuevos Clientes - Pendientes de Aprobación';
$this->params['breadcrumbs'][] = $this->title;
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

    <?php Pjax::begin(); ?>
    
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => function($model, $key, $index, $widget) {
            return '<div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">
                                        <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px; color: #ff9800;">person</span>
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
                                    ' . Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">check_circle</span>Aprobar', ['approve', 'id' => $model->id], [
                                        'class' => 'btn btn-success me-2',
                                        'data-confirm' => '¿Está seguro que desea aprobar este cliente?',
                                        'data-method' => 'post'
                                    ]) . '
                                    ' . Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">visibility</span>Ver', ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) . '
                                </div>
                            </div>
                        </div>
                    </div>';
        },
        'layout' => "{items}\n<div class='d-flex justify-content-center mt-4'>{pager}</div>",
        'emptyText' => '<div class="alert alert-info text-center">
                          <span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 16px;">check_circle</span>
                          <h5>¡Excelente!</h5>
                          <p>No hay clientes pendientes de aprobación.</p>
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
</style>

