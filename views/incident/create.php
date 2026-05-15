<?php
/** @var yii\web\View $this */
/** @var app\models\Incident $model */
/** @var app\models\Client[] $clients */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nuevo Insidente';
$this->params['breadcrumbs'][] = ['label' => 'Insidentes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="incident-create">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="mb-0">
            <span class="material-symbols-outlined align-middle me-2" style="font-size: 36px; color: #dc3545;">add_moderator</span>
            <?= Html::encode($this->title) ?>
        </h1>
        <?= Html::a('← Volver al listado', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <p class="text-muted">Seleccione el cliente, indique el monto total a cobrar por el choque y las notas del caso.</p>

    <div class="card border-primary">
        <div class="card-header bg-primary text-white fw-bold">Datos del insidente</div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'post',
                'options' => ['class' => 'row g-3'],
            ]); ?>

            <div class="col-md-6">
                <?= $form->field($model, 'client_id')->dropDownList(
                    ArrayHelper::map(
                        $clients,
                        'id',
                        function ($c) {
                            $t = $c->full_name;
                            if (!empty($c->cedula_fisica)) {
                                $t .= ' (' . $c->cedula_fisica . ')';
                            }
                            return $t;
                        }
                    ),
                    ['prompt' => '— Seleccione un cliente —', 'class' => 'form-select']
                )->label('Cliente') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'total_amount')->textInput([
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '0.01',
                    'class' => 'form-control',
                    'placeholder' => 'Ej: 150000.00',
                ])->label('Monto total a cobrar (¢)') ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'notes')->textarea([
                    'rows' => 4,
                    'placeholder' => 'Vehículo, póliza, detalle del choque…',
                ])->label('Notas') ?>
            </div>
            <div class="col-12">
                <?= Html::submitButton('Registrar Insidente', ['class' => 'btn btn-primary btn-lg']) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-link']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
