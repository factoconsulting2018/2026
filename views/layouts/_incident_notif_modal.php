<?php

/** @var yii\web\View $this */
/** @var app\models\Incident[] $incidents */
/** @var int $frequencyDays */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

$dismissUrl = Url::to(['/incident/notification-dismiss']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();
$dismissUrlJs = Json::encode($dismissUrl);
$csrfParamJs = Json::encode($csrfParam);
$csrfTokenJs = Json::encode($csrfToken);
?>

<div class="modal fade" id="incidentNotifModal" tabindex="-1" aria-labelledby="incidentNotifModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-25">
                <h5 class="modal-title" id="incidentNotifModalLabel">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size: 22px;">notifications_active</span>
                    Insidentes con saldo pendiente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="lead mb-3">Dar seguimiento al cobro del siguiente listado de insidentes.</p>
                <p class="text-muted small mb-3">Frecuencia configurada: <strong><?= (int) $frequencyDays ?></strong> día(s) de pausa en este navegador tras cerrar este aviso tres veces.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th class="text-end">Saldo pendiente</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $inc): ?>
                                <tr>
                                    <td><?= (int) $inc->id ?></td>
                                    <td><?= Html::encode($inc->client ? ($inc->client->full_name ?: 'Cliente #' . $inc->client_id) : ('Cliente #' . $inc->client_id)) ?></td>
                                    <td class="text-end">¢<?= number_format($inc->getBalance(), 2) ?></td>
                                    <td class="text-nowrap">
                                        <?= Html::a('Ver', ['/incident/view', 'id' => $inc->id], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function () {
    var dismissUrl = {$dismissUrlJs};
    var csrfParam = {$csrfParamJs};
    var csrfToken = {$csrfTokenJs};

    function start() {
        var modalEl = document.getElementById('incidentNotifModal');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: true });
        var posting = false;

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (posting) {
                return;
            }
            posting = true;
            var body = new URLSearchParams();
            body.append(csrfParam, csrfToken);
            fetch(dismissUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body,
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    posting = false;
                    if (data && data.ok && !data.stopped) {
                        setTimeout(function () { modalInstance.show(); }, 400);
                    }
                })
                .catch(function () {
                    posting = false;
                });
        });

        modalInstance.show();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
JS;
$this->registerJs($js, View::POS_END);
?>
