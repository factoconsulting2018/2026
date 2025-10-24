<?php
use yii\helpers\Html;

$this->title = 'Ver Nota: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Notas', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="note-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📝 <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('✏️ Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('← Volver', ['list'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Información de la Nota</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Título:</strong> <?= Html::encode($model->title) ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge <?= $model->getStatusClass() ?>">
                                    <?= $model->getStatusIcon() ?> <?= $model->getStatusName() ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Color:</strong> 
                                <span class="badge" style="background-color: <?= $model->getColorValue() ?>; color: white;">
                                    <?= $model->getColorName() ?>
                                </span>
                            </p>
                            <p><strong>Fecha:</strong> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                        </div>
                    </div>
                    
                    <?php if ($model->content): ?>
                    <div class="mt-3">
                        <strong>Contenido:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?= nl2br(Html::encode($model->content)) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
