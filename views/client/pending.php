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
    <ul class="nav nav-tabs mb-4 client-pending-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?= Html::a(
                '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">hourglass_empty</span>Pendientes',
                ['pending', 'tab' => 'pending'],
                [
                    'class' => 'nav-link tab-pending ' . ($currentTab === 'pending' ? 'active' : ''),
                    'aria-selected' => $currentTab === 'pending' ? 'true' : 'false'
                ]
            ) ?>
        </li>
        <li class="nav-item" role="presentation">
            <?= Html::a(
                '<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">block</span>Rechazados',
                ['pending', 'tab' => 'rejected'],
                [
                    'class' => 'nav-link tab-rejected ' . ($currentTab === 'rejected' ? 'active' : ''),
                    'aria-selected' => $currentTab === 'rejected' ? 'true' : 'false'
                ]
            ) ?>
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
                
                $buttons .= '<button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="openRejectModal(' . $model->id . ', \'' . Html::encode(addslashes($model->full_name)) . '\')">
                    <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">cancel</span>Rechazar
                </button>';
                
                $buttons .= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">delete</span>Eliminar', ['delete-permanently', 'id' => $model->id], [
                    'class' => 'btn btn-danger me-2',
                    'data-confirm' => '¿Está seguro que desea eliminar permanentemente este cliente? Esta acción no se puede deshacer.',
                    'data-method' => 'post'
                ]);
            } else if ($currentTab === 'rejected') {
                // Botones para clientes rechazados: permitir devolverlos a Pendientes.
                $buttons .= Html::a('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">undo</span>Mover a Pendientes', ['restore-to-pending', 'id' => $model->id], [
                    'class' => 'btn btn-warning me-2',
                    'data-confirm' => '¿Sacar este cliente de la lista de rechazados y volver a marcarlo como pendiente?',
                    'data-method' => 'post',
                    'title' => 'Quitar del rechazo y devolverlo a Pendientes'
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

<!-- Modal para Rechazar Cliente -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <span class="material-symbols-outlined" style="font-size: 24px; vertical-align: middle; margin-right: 8px;">cancel</span>
                    Rechazar Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reject-form" method="post" action="">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">warning</span>
                        <strong id="reject-client-name"></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_rechazo" class="form-label">
                            <strong>Motivo del Rechazo *</strong>
                        </label>
                        <textarea class="form-control" id="motivo_rechazo" name="motivo_rechazo" rows="4" required placeholder="Ingrese el motivo por el cual se rechaza este cliente..."></textarea>
                        <small class="text-muted">Este motivo aparecerá en el reporte de clientes rechazados.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">close</span>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 4px;">cancel</span>
                        Rechazar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.client-pending .card {
    border-left: 4px solid #ff9800;
}
.client-pending .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

/* ===== Tabs coloreados con texto blanco ===== */
.client-pending-tabs {
    border-bottom: 2px solid #dee2e6;
    gap: 4px;
}
.client-pending-tabs .nav-link {
    color: #ffffff !important;
    border: 1px solid transparent;
    border-bottom: none;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    padding: 10px 18px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.client-pending-tabs .nav-link.tab-pending {
    background-color: #ff9800;
}
.client-pending-tabs .nav-link.tab-pending:hover {
    background-color: #fb8c00;
}
.client-pending-tabs .nav-link.tab-pending.active {
    background-color: #e65100;
    border-color: #e65100;
    box-shadow: 0 -3px 0 #ff9800 inset;
}
.client-pending-tabs .nav-link.tab-rejected {
    background-color: #dc3545;
}
.client-pending-tabs .nav-link.tab-rejected:hover {
    background-color: #c82333;
}
.client-pending-tabs .nav-link.tab-rejected.active {
    background-color: #a71d2a;
    border-color: #a71d2a;
    box-shadow: 0 -3px 0 #dc3545 inset;
}
.client-pending-tabs .nav-link .material-symbols-outlined {
    color: #ffffff;
}
</style>

<script>
function openRejectModal(clientId, clientName) {
    document.getElementById('reject-client-name').innerHTML = '¿Está seguro que desea rechazar a <strong>' + clientName + '</strong>?';
    document.getElementById('reject-form').action = '<?= Url::to(['reject']) ?>/' + clientId;
    document.getElementById('motivo_rechazo').value = '';
}
</script>
