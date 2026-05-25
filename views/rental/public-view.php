<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Rental $model */

$this->title = 'Orden de Alquiler #' . $model->rental_id;
\yii\web\YiiAsset::register($this);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= Html::encode(Url::to('@web/css/material-symbols.css')) ?>" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
            color: white;
            padding: 25px;
        }
        .detail-view th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
            width: 40%;
        }
        .detail-view td {
            vertical-align: middle;
        }
        .badge {
            padding: 8px 16px;
            font-size: 14px;
        }
        .company-header {
            background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 15px;
        }
        .company-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .company-header p {
            margin: 5px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="company-header">
        <h2>FACTO RENT A CAR</h2>
        <p>Orden de Alquiler</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">
                <span class="material-symbols-outlined" style="font-size: 28px; vertical-align: middle; margin-right: 10px;">
                    receipt_long
                </span>
                Orden #<?= Html::encode($model->rental_id) ?>
            </h3>
        </div>
        <div class="card-body p-4">
            <table class="table table-bordered table-hover detail-view">
                <tbody>
                    <tr>
                        <th>ID del Alquiler</th>
                        <td><strong><?= Html::encode($model->rental_id) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Cliente</th>
                        <td><?= $model->client ? Html::encode($model->client->full_name . ' (' . $model->client->cedula_fisica . ')') : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Vehículo</th>
                        <td><?= $model->car ? Html::encode($model->car->nombre . ' (' . $model->car->placa . ')') : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de Inicio</th>
                        <td><?= Html::encode($model->fecha_inicio) ?></td>
                    </tr>
                    <tr>
                        <th>Hora de Inicio</th>
                        <td><?= Html::encode($model->hora_inicio) ?></td>
                    </tr>
                    <tr>
                        <th>Fecha Final</th>
                        <td><?= Html::encode($model->fecha_final) ?></td>
                    </tr>
                    <tr>
                        <th>Hora Final</th>
                        <td><?= Html::encode($model->hora_final) ?></td>
                    </tr>
                    <tr>
                        <th>Cantidad de Días</th>
                        <td>
                            <?php 
                            $texto = $model->cantidad_dias . ' días';
                            if ((!empty($model->medio_dia_enabled) || $model->medio_dia_enabled == 1) && !empty($model->medio_dia_valor) && $model->medio_dia_valor > 0) {
                                $texto .= ' + 1/2 día (¢' . number_format($model->medio_dia_valor, 0) . ')';
                            }
                            echo $texto;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Precio por Día</th>
                        <td>₡<?= number_format($model->precio_por_dia, 2) ?></td>
                    </tr>
                    <tr>
                        <th>1/2 Día</th>
                        <td>
                            <?php 
                            if ((!empty($model->medio_dia_enabled) || $model->medio_dia_enabled == 1) && !empty($model->medio_dia_valor) && $model->medio_dia_valor > 0) {
                                echo 'Sí (¢' . number_format($model->medio_dia_valor, 2) . ')';
                            } else {
                                echo 'No';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Precio Total</th>
                        <td><strong style="color: #28a745; font-size: 20px;">₡<?= number_format($model->total_precio ?? 0, 2) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Estado de Pago</th>
                        <td>
                            <?php 
                            $badges = [
                                'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
                                'pagado' => '<span class="badge bg-success">Pagado</span>',
                                'reservado' => '<span class="badge bg-info">Reservado</span>',
                                'finalizado' => '<span class="badge bg-dark">Finalizado</span>',
                                'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
                            ];
                            echo $badges[$model->estado_pago] ?? '<span class="badge bg-secondary">' . $model->estado_pago . '</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Lugar de Entrega</th>
                        <td><?= Html::encode($model->lugar_entrega) ?></td>
                    </tr>
                    <tr>
                        <th>Lugar de Retiro</th>
                        <td><?= Html::encode($model->lugar_retiro) ?></td>
                    </tr>
                    <tr>
                        <th>Correapartir Habilitado</th>
                        <td><?= $model->correapartir_enabled ? 'Sí' : 'No' ?></td>
                    </tr>
                    <?php if ($model->correapartir_enabled && !empty($model->fecha_correapartir)): ?>
                    <tr>
                        <th>Fecha Correapartir</th>
                        <td><?= Html::encode($model->fecha_correapartir) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Comprobante de Pago</th>
                        <td><?= Html::encode($model->comprobante_pago) ?></td>
                    </tr>
                    <?php if ($model->choferes_autorizados): ?>
                    <tr>
                        <th>Choferes Autorizados</th>
                        <td><?= Html::encode($model->choferes_autorizados) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($model->condiciones_especiales): ?>
            <div class="mt-4">
                <h5><strong>Condiciones Especiales:</strong></h5>
                <div class="alert alert-info">
                    <?= nl2br(Html::encode($model->condiciones_especiales)) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4 mb-4">
        <p style="color: white; font-size: 14px;">
            © <?= date('Y') ?> Facto Rent a Car. Todos los derechos reservados.
        </p>
        <p style="color: white; font-size: 12px; opacity: 0.8;">
            Desarrollado por Ing. Ronald Rojas Castro
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

