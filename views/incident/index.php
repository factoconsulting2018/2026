<?php
/** @var yii\web\View $this */
/** @var app\models\Client[] $clients */
/** @var app\models\Incident|null $incident */
/** @var int $clientId */
/** @var app\models\Incident[] $openIncidents */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Incident;
use app\models\IncidentPayment;

$this->title = 'Insidentes (choques)';
$this->params['breadcrumbs'][] = $this->title;

$newIncident = new Incident();
$paymentModel = new IncidentPayment();
?>

<div class="incident-index">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #dc3545;">car_crash</span>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <p class="text-muted">Registre el monto total por daño de choque y aplique abonos hasta saldar la deuda del cliente.</p>

    <div class="card mb-4">
        <div class="card-header fw-bold">Seleccionar cliente</div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Cliente</label>
                    <select name="client_id" class="form-select" onchange="this.form.submit()">
                        <option value="">— Elija un cliente —</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= (int) $c->id ?>" <?= $clientId === (int) $c->id ? 'selected' : '' ?>>
                                <?= Html::encode($c->full_name) ?>
                                <?php if (!empty($c->cedula_fisica)): ?>
                                    (<?= Html::encode($c->cedula_fisica) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </form>

            <?php if ($clientId > 0 && count($openIncidents) > 0): ?>
                <hr>
                <label class="form-label">Casos abiertos de este cliente</label>
                <div class="list-group">
                    <?php foreach ($openIncidents as $oi): ?>
                        <a href="<?= Url::to(['index', 'client_id' => $clientId, 'incident_id' => $oi->id]) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $incident && $incident->id === $oi->id ? 'active' : '' ?>">
                            <span>
                                Caso #<?= $oi->id ?> — Total ¢<?= number_format((float) $oi->total_amount, 2) ?>
                                — Saldo ¢<?= number_format($oi->getBalance(), 2) ?>
                            </span>
                            <span class="material-symbols-outlined">chevron_right</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($clientId > 0): ?>
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white fw-bold">Nuevo caso — monto a pagar por el choque</div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['create'],
                    'method' => 'post',
                    'options' => ['class' => 'row g-3'],
                ]); ?>
                <?= $form->field($newIncident, 'client_id')->hiddenInput(['value' => $clientId])->label(false) ?>
                <div class="col-md-4">
                    <?= $form->field($newIncident, 'total_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0.01',
                        'class' => 'form-control',
                        'placeholder' => 'Ej: 150000.00',
                    ])->label('Monto total a cobrar (¢)') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($newIncident, 'notes')->textarea(['rows' => 2, 'placeholder' => 'Vehículo, póliza, detalle del choque…'])->label('Notas') ?>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <?= Html::submitButton('Registrar incidente', ['class' => 'btn btn-primary w-100']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($incident): ?>
        <?php
        $balance = $incident->getBalance();
        $paid = $incident->getPaidTotal();
        ?>
        <div class="card mb-4 border-danger">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold">Caso #<?= $incident->id ?> — <?= Html::encode($incident->client->full_name ?? '') ?></span>
                <span class="badge bg-light text-dark">Abierto</span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <div class="text-muted small">Monto total del choque</div>
                            <div class="fs-4 fw-bold">¢<?= number_format((float) $incident->total_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <div class="text-muted small">Total abonado</div>
                            <div class="fs-4 fw-bold text-success">¢<?= number_format($paid, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-warning bg-opacity-25 rounded border border-warning">
                            <div class="text-muted small">Saldo pendiente</div>
                            <div class="fs-4 fw-bold text-danger">¢<?= number_format($balance, 2) ?></div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($incident->notes)): ?>
                    <p class="text-muted"><strong>Notas del caso:</strong> <?= nl2br(Html::encode($incident->notes)) ?></p>
                <?php endif; ?>

                <hr>
                <h5 class="mb-3">
                    <span class="material-symbols-outlined align-middle">payments</span>
                    Nuevo abono
                </h5>
                <?php if ($balance < 0.01): ?>
                    <div class="alert alert-success mb-0">Este caso está saldado. Puede cerrarlo desde la lista o registrar otro incidente.</div>
                <?php else: ?>
                    <?php
                    $paymentModel->incident_id = $incident->id;
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
                <?php endif; ?>

                <hr>
                <h6 class="mb-2">Historial de abonos</h6>
                <?php $payments = $incident->payments; ?>
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
    <?php elseif ($clientId > 0): ?>
        <div class="alert alert-info">
            Seleccione un caso abierto arriba o registre un <strong>nuevo incidente</strong> con el monto total a cobrar.
        </div>
    <?php endif; ?>
</div>
