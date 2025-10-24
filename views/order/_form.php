<?php
/** @var yii\web\View $this */
/** @var app\models\Order $model */
/** @var yii\widgets\ActiveForm $form */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="order-form">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'template' => '<div class="row"><div class="col-md-2">{label}</div><div class="col-md-10">{input}{error}</div></div>',
            'labelOptions' => ['class' => 'form-label'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <h5 class="mb-3">📦 Información de la Venta</h5>
            
            <?= $form->field($model, 'ticket_id')->textInput(['maxlength' => true, 'placeholder' => 'ID del ticket']) ?>
            
            <?= $form->field($model, 'article_id')->textInput(['type' => 'number', 'placeholder' => 'ID del artículo']) ?>
            
            <?= $form->field($model, 'client_id')->dropDownList(
                \yii\helpers\ArrayHelper::map(
                    \app\models\Client::find()->where(['status' => 'active'])->all(),
                    'id',
                    'full_name'
                ),
                ['prompt' => 'Seleccionar cliente...']
            ) ?>
            
            <?= $form->field($model, 'sale_mode')->dropDownList([
                'retail' => '🏪 Retail',
                'wholesale' => '📦 Wholesale',
                'auction' => '🔨 Auction'
            ]) ?>
        </div>

        <div class="col-md-6">
            <h5 class="mb-3">💰 Información de Precios</h5>
            
            <?= $form->field($model, 'quantity')->textInput(['type' => 'number', 'placeholder' => 'Cantidad']) ?>
            
            <?= $form->field($model, 'unit_price')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => '0.00']) ?>
            
            <?= $form->field($model, 'total_price')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => '0.00']) ?>
            
            <?= $form->field($model, 'store_id')->textInput(['type' => 'number', 'placeholder' => 'ID de la tienda']) ?>
            
            <?= $form->field($model, 'notes')->textarea(['rows' => 3, 'placeholder' => 'Notas adicionales']) ?>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex justify-content-between">
            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::submitButton('💾 Guardar Cambios', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
