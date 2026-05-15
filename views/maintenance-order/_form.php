<?php
/** @var yii\web\View $this */
/** @var app\models\MaintenanceOrder $model */
/** @var app\models\Car[] $cars */
/** @var array<int, string> $carItems */
/** @var bool $isUpdate */

use app\models\MaintenanceOrder;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$isUpdate = $isUpdate ?? false;
$carItems = $carItems ?? MaintenanceOrder::buildCarDropdownList($cars ?? []);
?>

<?php $form = ActiveForm::begin(['options' => ['class' => 'row g-3']]); ?>

<?php if ($isUpdate && !empty($model->order_id)): ?>
<div class="col-md-4">
    <label class="form-label">Número de orden</label>
    <input type="text" class="form-control" value="<?= Html::encode($model->order_id) ?>" readonly disabled>
</div>
<?php endif; ?>

<div class="col-md-<?= $isUpdate ? '4' : '6' ?>">
    <?php if ($carItems === []): ?>
        <div class="alert alert-warning mb-0">
            No hay vehículos registrados. <a href="<?= \yii\helpers\Url::to(['/car/create']) ?>">Agregar vehículo</a>
        </div>
    <?php endif; ?>
    <?= $form->field($model, 'car_id')->dropDownList(
        $carItems,
        ['prompt' => '— Seleccione un vehículo —', 'class' => 'form-select']
    ) ?>
</div>

<div class="col-md-<?= $isUpdate ? '4' : '3' ?>">
    <?= $form->field($model, 'order_date')->input('date', ['class' => 'form-control']) ?>
</div>

<?php if ($isUpdate): ?>
<div class="col-md-4">
    <?= $form->field($model, 'status')->dropDownList(
        MaintenanceOrder::statusList(),
        ['class' => 'form-select']
    ) ?>
</div>
<?php endif; ?>

<div class="col-12">
    <?= $form->field($model, 'notes')->textarea([
        'rows' => 5,
        'placeholder' => 'Describa trabajos pendientes, repuestos, observaciones…',
    ]) ?>
</div>

<div class="col-12">
    <?= Html::submitButton($isUpdate ? 'Guardar cambios' : 'Crear orden de mantenimiento', [
        'class' => 'btn btn-primary btn-lg',
    ]) ?>
    <?= Html::a('Cancelar', $isUpdate ? ['view', 'id' => $model->id] : ['index'], ['class' => 'btn btn-link']) ?>
</div>

<?php ActiveForm::end(); ?>
