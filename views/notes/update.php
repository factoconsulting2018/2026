<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Note */

$this->title = 'Editar Nota: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Notas', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="note-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('👁️ Ver', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('← Volver', ['list'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📝 Editar Información de la Nota</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'needs-validation', 'novalidate' => true]
                    ]); ?>

                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'title')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Ingresa el título de la nota...',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'status')->dropDownList([
                                'pending' => '⏳ Pendiente',
                                'processing' => '🔄 Procesando', 
                                'completed' => '✅ Completada'
                            ], [
                                'class' => 'form-select',
                                'prompt' => 'Seleccionar estado...'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'color')->dropDownList([
                                'yellow' => '🟡 Amarillo',
                                'blue' => '🔵 Azul',
                                'green' => '🟢 Verde',
                                'red' => '🔴 Rojo',
                                'orange' => '🟠 Naranja',
                                'purple' => '🟣 Morado',
                                'pink' => '🩷 Rosa',
                                'gray' => '⚫ Gris',
                                'lightblue' => '🔵 Azul Claro',
                                'lightgreen' => '🟢 Verde Claro'
                            ], [
                                'class' => 'form-select',
                                'prompt' => 'Seleccionar color...'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vista Previa del Color</label>
                                <div id="colorPreview" class="p-3 rounded border" style="background-color: <?= $model->getColorValue() ?>; min-height: 50px;">
                                    <span class="text-white fw-bold">Vista previa</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?= $form->field($model, 'content')->textarea([
                        'rows' => 6,
                        'placeholder' => 'Escribe el contenido de la nota aquí...',
                        'class' => 'form-control'
                    ]) ?>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('💾 Guardar Cambios', [
                            'class' => 'btn btn-success btn-lg me-2'
                        ]) ?>
                        <?= Html::a('❌ Cancelar', ['list'], [
                            'class' => 'btn btn-secondary btn-lg'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Información de la Nota</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID de la Nota:</label>
                        <p class="form-control-plaintext">#<?= $model->id ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado Actual:</label>
                        <div>
                            <span class="badge <?= $model->getStatusClass() ?>">
                                <?= $model->getStatusIcon() ?> <?= $model->getStatusName() ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Color Actual:</label>
                        <div>
                            <span class="badge" style="background-color: <?= $model->getColorValue() ?>; color: white;">
                                <?= $model->getColorName() ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de Creación:</label>
                        <p class="form-control-plaintext"><?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Última Actualización:</label>
                        <p class="form-control-plaintext"><?= Yii::$app->formatter->asDate($model->updated_at) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">🎨 Colores Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="color-option" data-color="yellow" style="background-color: #ffeb3b; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Amarillo"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="blue" style="background-color: #2196f3; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Azul"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="green" style="background-color: #4caf50; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Verde"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="red" style="background-color: #f44336; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Rojo"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="orange" style="background-color: #ff9800; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Naranja"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="purple" style="background-color: #9c27b0; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Morado"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="pink" style="background-color: #e91e63; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Rosa"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="gray" style="background-color: #9e9e9e; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Gris"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="lightblue" style="background-color: #03a9f4; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Azul Claro"></div>
                        </div>
                        <div class="col-6">
                            <div class="color-option" data-color="lightgreen" style="background-color: #8bc34a; height: 30px; border-radius: 4px; cursor: pointer; border: 2px solid transparent;" title="Verde Claro"></div>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Haz clic en un color para seleccionarlo</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar vista previa del color cuando cambie el select
    const colorSelect = document.querySelector('select[name="Note[color]"]');
    const colorPreview = document.getElementById('colorPreview');
    
    if (colorSelect && colorPreview) {
        colorSelect.addEventListener('change', function() {
            const selectedColor = this.value;
            if (selectedColor) {
                const colorMap = {
                    'yellow': '#ffeb3b',
                    'blue': '#2196f3',
                    'green': '#4caf50',
                    'red': '#f44336',
                    'orange': '#ff9800',
                    'purple': '#9c27b0',
                    'pink': '#e91e63',
                    'gray': '#9e9e9e',
                    'lightblue': '#03a9f4',
                    'lightgreen': '#8bc34a',
                };
                colorPreview.style.backgroundColor = colorMap[selectedColor] || '#ffeb3b';
            }
        });
    }
    
    // Manejar clic en opciones de color
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.dataset.color;
            colorSelect.value = color;
            colorPreview.style.backgroundColor = this.style.backgroundColor;
            
            // Actualizar selección visual
            colorOptions.forEach(opt => opt.style.border = '2px solid transparent');
            this.style.border = '2px solid #000';
        });
    });
    
    // Marcar el color actual como seleccionado
    const currentColor = colorSelect.value;
    if (currentColor) {
        const currentOption = document.querySelector(`.color-option[data-color="${currentColor}"]`);
        if (currentOption) {
            currentOption.style.border = '2px solid #000';
        }
    }
});
</script>

<style>
.color-option:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>
