<?php
/** @var yii\web\View $this */
/** @var app\models\Car $model */

use yii\helpers\Html;
use yii\helpers\Url;

// Definir colores de estado según Material Design 3
$statusConfig = [
    'disponible' => ['color' => '#4CAF50', 'bg' => '#E8F5E8', 'text' => 'Disponible'],
    'alquilado' => ['color' => '#FF9800', 'bg' => '#FFF3E0', 'text' => 'Alquilado'],
    'mantenimiento' => ['color' => '#2196F3', 'bg' => '#E3F2FD', 'text' => 'Mantenimiento'],
    'fuera_servicio' => ['color' => '#F44336', 'bg' => '#FFEBEE', 'text' => 'Fuera de Servicio']
];

$currentStatus = $statusConfig[$model->status] ?? $statusConfig['fuera_servicio'];
?>

<div class="material-card">
    <!-- Card Header -->
    <div class="material-card-header">
        <div class="card-header-content">
            <div class="vehicle-icon">
                <span class="material-symbols-outlined">directions_car</span>
            </div>
            <div class="vehicle-info">
                <h3 class="vehicle-name"><?= Html::encode($model->nombre) ?></h3>
                <p class="vehicle-plate"><?= Html::encode($model->placa) ?></p>
            </div>
        </div>
        <div class="status-badge" style="background-color: <?= $currentStatus['bg'] ?>; color: <?= $currentStatus['color'] ?>;">
            <span class="material-symbols-outlined status-icon"><?= $model->status === 'disponible' ? 'check_circle' : ($model->status === 'alquilado' ? 'schedule' : ($model->status === 'mantenimiento' ? 'build' : 'error')) ?></span>
            <?= $currentStatus['text'] ?>
        </div>
    </div>

    <!-- Card Body -->
    <div class="material-card-body">
        <!-- Basic Information -->
        <div class="info-section">
            <div class="info-item">
                <span class="material-symbols-outlined info-icon">confirmation_number</span>
                <div class="info-content">
                    <span class="info-label">VIN</span>
                    <span class="info-value"><?= Html::encode($model->vin ?: 'N/A') ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="material-symbols-outlined info-icon">group</span>
                <div class="info-content">
                    <span class="info-label">Pasajeros</span>
                    <span class="info-value"><?= Html::encode($model->cantidad_pasajeros) ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="material-symbols-outlined info-icon">business</span>
                <div class="info-content">
                    <span class="info-label">Empresa</span>
                    <span class="info-value"><?= Html::encode($model->empresa) ?></span>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <?php if ($model->caracteristicas || $model->empresa_seguro): ?>
        <div class="additional-info">
            <?php if ($model->caracteristicas): ?>
            <div class="info-section">
                <h4 class="section-title">
                    <span class="material-symbols-outlined">description</span>
                    Características
                </h4>
                <p class="section-content"><?= Html::encode($model->caracteristicas) ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($model->empresa_seguro): ?>
            <div class="info-section">
                <h4 class="section-title">
                    <span class="material-symbols-outlined">security</span>
                    Seguro
                </h4>
                <p class="section-content">
                    <?= Html::encode($model->empresa_seguro) ?>
                    <?php if ($model->telefono_seguro): ?>
                    <br><span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">phone</span>
                    <?= Html::encode($model->telefono_seguro) ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Card Footer -->
    <div class="material-card-footer">
        <div class="footer-info">
            <span class="material-symbols-outlined" style="font-size: 16px;">calendar_today</span>
            <span class="footer-text">Registrado: <?= Yii::$app->formatter->asDate($model->created_at) ?></span>
        </div>
        
        <div class="card-actions">
            <div class="actions-menu">
                <button type="button" class="actions-menu-toggle" style="padding: 6px 10px; font-size: 13px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">more_vert</span>
                    <span>Acciones</span>
                    <span class="material-symbols-outlined toggle-arrow" style="font-size: 16px;">expand_more</span>
                </button>
                <div class="actions-dropdown">
                    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="actions-dropdown-item action-view">
                        <span class="material-symbols-outlined">visibility</span>
                        <span class="action-text">Ver Detalles</span>
                    </a>
                    <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="actions-dropdown-item action-edit">
                        <span class="material-symbols-outlined">edit</span>
                        <span class="action-text">Editar</span>
                    </a>
                    <a href="<?= Url::to(['/rental/create', 'car_id' => $model->id]) ?>" class="actions-dropdown-item action-edit" style="color: #198754;">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span class="action-text">Nuevo Alquiler</span>
                    </a>
                    <div class="actions-dropdown-divider"></div>
                    <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" 
                       class="actions-dropdown-item action-delete" 
                       data-confirm="¿Estás seguro de eliminar este vehículo?" 
                       data-method="post">
                        <span class="material-symbols-outlined">delete</span>
                        <span class="action-text">Eliminar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.material-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    margin-bottom: 24px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.material-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.material-card-header {
    padding: 20px 20px 16px 20px;
    background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.card-header-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.vehicle-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vehicle-icon .material-symbols-outlined {
    font-size: 24px;
}

.vehicle-info {
    flex: 1;
}

.vehicle-name {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 4px 0;
    line-height: 1.2;
}

.vehicle-plate {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
    font-weight: 500;
}

.status-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-icon {
    font-size: 16px;
}

.material-card-body {
    padding: 20px;
    flex: 1;
}

.info-section {
    margin-bottom: 20px;
}

.info-section:last-child {
    margin-bottom: 0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-icon {
    font-size: 20px;
    color: #3fa9f5;
    width: 24px;
    text-align: center;
}

.info-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-label {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 14px;
    color: #333;
    font-weight: 600;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
}

.section-title .material-symbols-outlined {
    font-size: 18px;
    color: #3fa9f5;
}

.section-content {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
    margin: 0;
}

.material-card-footer {
    padding: 16px 20px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-info {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 12px;
}

.footer-text {
    font-weight: 500;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.action-button {
    width: 36px;
    height: 36px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-button .material-symbols-outlined {
    font-size: 18px;
}

.view-button {
    background: rgba(63, 169, 245, 0.1);
    color: #3fa9f5;
}

.view-button:hover {
    background: rgba(63, 169, 245, 0.2);
    color: #22487a;
}

.edit-button {
    background: rgba(34, 72, 122, 0.1);
    color: #22487a;
}

.edit-button:hover {
    background: rgba(34, 72, 122, 0.2);
    color: #1b305b;
}

.delete-button {
    background: #ffebee;
    color: #d32f2f;
}

.delete-button:hover {
    background: #ffcdd2;
    color: #b71c1c;
}

@media (max-width: 768px) {
    .material-card-header {
        padding: 16px;
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .card-header-content {
        justify-content: center;
    }
    
    .status-badge {
        align-self: center;
    }
    
    .material-card-body {
        padding: 16px;
    }
    
    .material-card-footer {
        padding: 12px 16px;
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .footer-info {
        justify-content: center;
    }
    
    .card-actions {
        justify-content: center;
    }
}
</style>