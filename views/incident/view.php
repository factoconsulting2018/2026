<?php
/** @var yii\web\View $this */
/** @var app\models\Incident $model */
/** @var app\models\IncidentPayment $paymentModel */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Insidente #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Insidentes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$balance = $model->getBalance();
$paid = $model->getPaidTotal();
$isOpen = $model->isOpen();
$isPaid = ($balance < 0.01);
$mainCardBorder = $isPaid ? 'border-success' : 'border-danger';
?>

<div class="incident-view">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #dc3545;">car_crash</span>
            <?= Html::encode($this->title) ?>
            <?php if ($isOpen): ?>
                <span class="badge bg-warning text-dark ms-2">Abierto</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2">Cerrado</span>
            <?php endif; ?>
            <?php if ($isPaid): ?>
                <span class="badge bg-success ms-1">Pagado</span>
            <?php else: ?>
                <span class="badge bg-danger ms-1">Saldo pendiente</span>
            <?php endif; ?>
        </h1>
        <div class="d-flex gap-2">
            <?= Html::a('← Listado', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php if ($isOpen): ?>
                <?= Html::a('Nuevo Insidente', ['create', 'client_id' => $model->client_id], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4 <?= $mainCardBorder ?>">
        <div class="card-header fw-bold <?= $isPaid ? 'bg-success text-white' : 'bg-danger text-white' ?>">
            <?= Html::encode($model->client->full_name ?? '') ?>
            <?php if (!empty($model->client->cedula_fisica)): ?>
                <span class="text-white-50 fw-normal"> · <?= Html::encode($model->client->cedula_fisica) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="text-muted small">Monto total del choque</div>
                        <div class="fs-4 fw-bold">¢<?= number_format((float) $model->total_amount, 2) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="text-muted small">Total abonado</div>
                        <div class="fs-4 fw-bold text-success">¢<?= number_format($paid, 2) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded border <?= $isPaid ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-25 border-warning' ?>">
                        <div class="text-muted small">Saldo pendiente</div>
                        <div class="fs-4 fw-bold <?= $isPaid ? 'text-success' : 'text-danger' ?>">¢<?= number_format($balance, 2) ?></div>
                    </div>
                </div>
            </div>
            <?php if (!empty($model->notes)): ?>
                <p class="text-muted"><strong>Notas del caso:</strong> <?= nl2br(Html::encode($model->notes)) ?></p>
            <?php endif; ?>

            <?php if ($isOpen && $balance < 0.01): ?>
                <div class="alert alert-success">
                    Este caso está saldado. Puede cerrar el insidente formalmente.
                    <?php $cf = Html::beginForm(['close', 'id' => $model->id], 'post', ['class' => 'd-inline']); ?>
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <?= Html::submitButton('Cerrar insidente', ['class' => 'btn btn-sm btn-success ms-2']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>

            <?php if ($isOpen && $balance >= 0.01): ?>
                <hr>
                <h5 class="mb-3">
                    <span class="material-symbols-outlined align-middle">payments</span>
                    Nuevo abono
                </h5>
                <?php
                $paymentModel->incident_id = $model->id;
                $pf = ActiveForm::begin([
                    'action' => ['add-payment'],
                    'method' => 'post',
                    'options' => ['class' => 'row g-3 align-items-end'],
                ]); ?>
                <?= $pf->field($paymentModel, 'incident_id')->hiddenInput()->label(false) ?>
                <div class="col-md-3">
                    <?= $pf->field($paymentModel, 'payment_date')->textInput([
                        'type' => 'date',
                        'value' => date('Y-m-d'),
                        'class' => 'form-control',
                    ])->label('Fecha del abono') ?>
                </div>
                <div class="col-md-3">
                    <?= $pf->field($paymentModel, 'amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0.01',
                        'max' => (string) $balance,
                        'class' => 'form-control',
                        'placeholder' => 'Monto en colones',
                    ])->label('Monto del abono (¢)') ?>
                </div>
                <div class="col-md-4">
                    <?= $pf->field($paymentModel, 'note')->textInput(['maxlength' => 255, 'placeholder' => 'Opcional: referencia, recibo…'])->label('Nota') ?>
                </div>
                <div class="col-md-2">
                    <?= Html::submitButton('Nuevo abono', ['class' => 'btn btn-success w-100']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            <?php elseif (!$isOpen): ?>
                <div class="alert alert-secondary mb-0">Este insidente está cerrado; no se pueden registrar más abonos.</div>
            <?php endif; ?>

            <hr>
            <h6 class="mb-2">Historial de abonos</h6>
            <?php $payments = $model->payments; ?>
            <?php if (count($payments) === 0): ?>
                <p class="text-muted mb-0">Aún no hay abonos registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th>Nota</th>
                                <th>Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= Html::encode(Yii::$app->formatter->asDate($p->payment_date)) ?></td>
                                    <td class="text-end">¢<?= number_format((float) $p->amount, 2) ?></td>
                                    <td><?= Html::encode($p->note ?? '') ?></td>
                                    <td class="text-muted small"><?= Html::encode($p->created_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-danger">
        <div class="card-header bg-danger text-white fw-bold">Eliminar insidente</div>
        <div class="card-body">
            <?php $df = Html::beginForm(['delete', 'id' => $model->id], 'post'); ?>
            <p class="text-muted small mb-3">Elimina este caso y <strong>todos los abonos</strong>. La acción no se puede deshacer. Ingrese la contraseña de autorización.</p>
            <div class="row g-2 align-items-end flex-wrap">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="delete_password_view">Contraseña</label>
                    <input type="password" name="delete_password" id="delete_password_view" class="form-control" required autocomplete="off" placeholder="Contraseña">
                </div>
                <div class="col-md-auto">
                    <?= Html::submitButton('Eliminar insidente', ['class' => 'btn btn-danger']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
