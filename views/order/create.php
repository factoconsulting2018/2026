<?php
/** @var yii\web\View $this */
/** @var app\models\Order $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Crear Venta/Orden';
$this->params['breadcrumbs'][] = ['label' => 'Ventas/Órdenes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="order-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>➕ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="card">
        <div class="card-body">
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
                    
                    <?= $form->field($model, 'article_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(
                            \app\models\Article::find()->where(['status' => 'available'])->all(),
                            'id',
                            function($article) {
                                return $article->article_id . ' - ' . $article->name;
                            }
                        ),
                        ['prompt' => 'Seleccionar artículo...']
                    ) ?>
                    
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
                    <?= Html::submitButton('💾 Guardar Venta', ['class' => 'btn btn-success']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
// JavaScript para calcular el total automáticamente
$this->registerJs('
    $(document).ready(function() {
        var $quantity = $("input[name=\'Order[quantity]\']]");
        var $unitPrice = $("input[name=\'Order[unit_price]\']]");
        var $totalPrice = $("input[name=\'Order[total_price]\']]");
        
        function calculateTotal() {
            var quantity = parseFloat($quantity.val()) || 0;
            var unitPrice = parseFloat($unitPrice.val()) || 0;
            var total = quantity * unitPrice;
            $totalPrice.val(total.toFixed(2));
        }
        
        $quantity.on("input", calculateTotal);
        $unitPrice.on("input", calculateTotal);
        
        // Cargar precio del artículo cuando se selecciona
        $("select[name=\'Order[article_id]\']").on("change", function() {
            var articleId = $(this).val();
            if (articleId) {
                $.ajax({
                    url: "/order/get-article-price",
                    data: { id: articleId },
                    dataType: "json",
                    success: function(data) {
                        if (data.price) {
                            $unitPrice.val(data.price);
                            calculateTotal();
                        }
                    }
                });
            }
        });
    });
');
?>
