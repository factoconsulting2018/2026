<?php
/**
 * Vista PDF para Ventas 2 - Reporte con colores y diseño mejorado
 * 
 * @var array $rentalsByCompany
 * @var array $totalsByCompany
 * @var float $totalAmount
 * @var string $reportNumber
 * @var string $currentDate
 */

use yii\helpers\Html;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas 2 - Reporte de Alquileres</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #2E86AB 0%, #A23B72 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header .subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 15px;
            background: linear-gradient(135deg, #F18F01 0%, #1a365d 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .report-info .info-item {
            text-align: center;
        }
        
        .report-info .info-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .report-info .info-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .company-section {
            margin-bottom: 40px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .company-header {
            background: linear-gradient(135deg, #A23B72 0%, #F18F01 100%);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #F18F01 0%, #1a365d 100%);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .data-table td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        
        .data-table tr:nth-child(even) {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .data-table tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        .company-total {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .grand-total {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #2E86AB 0%, #1a5490 100%);
            color: white;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .grand-total h2 {
            font-size: 24px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .grand-total .amount {
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border-radius: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background: #1a365d;
            color: white;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        .status-pagado { color: #28a745; font-weight: bold; }
        .status-pendiente { color: #ffc107; font-weight: bold; }
        .status-reservado { color: #17a2b8; font-weight: bold; }
        .status-cancelado { color: #1a365d; font-weight: bold; }
        
        @media print {
            body {
                background: white;
            }
            
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .data-table tr:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎯 VENTAS - REPORTE DE ALQUILERES</h1>
            <div class="subtitle">Reporte con colores y diseño mejorado</div>
        </div>
        
        <!-- Información del reporte -->
        <div class="report-info">
            <div class="info-item">
                <div class="info-label">Número de Reporte</div>
                <div class="info-value"><?= Html::encode($reportNumber) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha de Generación</div>
                <div class="info-value"><?= Html::encode($currentDate) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Total de Registros</div>
                <div class="info-value"><?= array_sum(array_map('count', $rentalsByCompany)) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Total General</div>
                <div class="info-value">₡<?= number_format($totalAmount, 2, '.', ',') ?></div>
            </div>
        </div>
        
        <!-- Secciones por empresa -->
        <?php foreach ($rentalsByCompany as $company => $rentals): ?>
            <?php if (empty($rentals)) continue; ?>
            
            <div class="company-section">
                <!-- Encabezado de empresa -->
                <div class="company-header">
                    🏢 <?= Html::encode($company) ?> - <?= count($rentals) ?> alquileres
                </div>
                
                <!-- Tabla de datos -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">N°</th>
                            <th style="width: 10%;">Fecha</th>
                            <th style="width: 20%;">Cliente</th>
                            <th style="width: 15%;">Vehículo</th>
                            <th style="width: 8%;">Placa</th>
                            <th style="width: 12%;">Monto (₡)</th>
                            <th style="width: 10%;">Método Pago</th>
                            <th style="width: 10%;">Ejecutivo</th>
                            <th style="width: 5%;">Días</th>
                            <th style="width: 5%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($rentals as $rental): ?>
                            <tr>
                                <td><?= $counter ?></td>
                                <td><?= date('d/m/Y', strtotime($rental->created_at)) ?></td>
                                <td style="text-align: left; font-weight: bold;">
                                    <?= Html::encode($rental->client ? $rental->client->full_name : 'N/A') ?>
                                </td>
                                <td style="text-align: left;">
                                    <?= Html::encode($rental->car ? $rental->car->nombre : 'N/A') ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= Html::encode($rental->car ? $rental->car->placa : 'N/A') ?>
                                    </span>
                                </td>
                                <td style="font-weight: bold; color: #2E86AB;">
                                    ₡<?= number_format($rental->total_precio ?? 0, 2, '.', ',') ?>
                                </td>
                                <td>
                                    <?php
                                    $comprobante = isset($rental->comprobante_pago) && !empty($rental->comprobante_pago) ? $rental->comprobante_pago : 'N/A';
                                    $badgeClass = 'badge-info';
                                    if (strpos($comprobante, 'Sinpe') !== false) $badgeClass = 'badge-success';
                                    elseif (strpos($comprobante, 'Transferencia') !== false) $badgeClass = 'badge-warning';
                                    elseif (strpos($comprobante, 'Efectivo') !== false) $badgeClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= Html::encode($comprobante) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $ejecutivo = isset($rental->ejecutivo) && !empty($rental->ejecutivo) ? $rental->ejecutivo : 'N/A';
                                    ?>
                                    <span class="badge badge-info">
                                        <?= Html::encode($ejecutivo) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <?= $rental->cantidad_dias ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'status-pendiente';
                                    if ($rental->estado_pago === 'pagado') $statusClass = 'status-pagado';
                                    elseif ($rental->estado_pago === 'reservado') $statusClass = 'status-reservado';
                                    elseif ($rental->estado_pago === 'cancelado') $statusClass = 'status-cancelado';
                                    ?>
                                    <span class="<?= $statusClass ?>">
                                        <?= Html::encode($rental->estado_pago) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php $counter++; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Total de la empresa -->
                <div class="company-total">
                    💰 TOTAL <?= Html::encode($company) ?>: ₡<?= number_format($totalsByCompany[$company], 2, '.', ',') ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Total general -->
        <div class="grand-total">
            <h2>🎯 TOTAL GENERAL</h2>
            <div class="amount">₡<?= number_format($totalAmount, 2, '.', ',') ?></div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Ventas - Reporte de Alquileres con Colores</strong></p>
            <p>Generado el <?= $currentDate ?> | Sistema de Gestión Facto Rent A Car</p>
        </div>
    </div>
</body>
</html>
