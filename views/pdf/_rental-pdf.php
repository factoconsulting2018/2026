<?php
$rentalId = $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
$client = $model->client;
$car = $model->car;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { margin-bottom: 15px; }
        .company-info { width: 100%; margin-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; font-style: italic; margin-bottom: 5px; }
        .company-legal { font-size: 12px; margin-bottom: 10px; }
        .company-address { font-size: 10px; margin-bottom: 10px; }
        .order-title { font-size: 12px; font-weight: bold; margin: 15px 0; }
        .section-title { font-size: 10px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; }
        .info-row { margin-bottom: 3px; }
        .vehicle-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
        .vehicle-table td { border: 1px solid #000; padding: 5px; text-align: left; font-size: 10px; }
        .vehicle-header { font-weight: bold; text-align: center; }
        .total-row { font-weight: bold; }
    </style>
</head>
<body>
    <div class="company-info">
        <div class="company-name"><?= htmlspecialchars($companyInfo['name']) ?></div>
        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
        <div class="company-address">
            3-101-880789<br>
            San Ramón, Alajuela.<br>
            Costa Rica
        </div>
    </div>
    
    <div class="order-title">
        Orden: <?= htmlspecialchars($rentalId) ?> - <?= htmlspecialchars($car ? $car->nombre : 'N/A') ?>
    </div>
    
    <div class="info-row">
        <span class="info-label">Nombre del cliente:</span> 
        <?= htmlspecialchars($client ? $client->full_name : 'N/A') ?>
    </div>
    <div class="info-row">
        <span class="info-label">Cédula:</span> 
        <?= htmlspecialchars($client ? $client->cedula_fisica : 'N/A') ?>
    </div>
    
    <div class="section-title">ENTREGA DEL VEHÍCULO</div>
    <div class="info-row">
        <span class="info-label">Fecha de alquiler:</span> 
        <?= date('d/m/Y', strtotime($model->fecha_inicio)) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Fecha recoge vehículo:</span> 
        <?= date('d/m/Y H:i', strtotime($model->fecha_inicio . ' ' . $model->hora_inicio)) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Lugar:</span> 
        <?= htmlspecialchars($model->lugar_entrega ?: 'San Ramón') ?>
    </div>
    
    <div class="section-title">DEVOLUCIÓN DEL VEHÍCULO</div>
    <div class="info-row">
        <span class="info-label">Fecha de entrega:</span> 
        <?= date('d/m/Y H:i', strtotime($model->fecha_final . ' ' . $model->hora_final)) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Lugar:</span> 
        <?= htmlspecialchars($model->lugar_retiro ?: 'San Ramón') ?>
    </div>
    
    <table class="vehicle-table">
        <tr>
            <td class="vehicle-header" colspan="5">Tipo de Vehículo: <?= htmlspecialchars($car ? ($car->nombre . ' - ' . ($car->cantidad_pasajeros ?: 5) . ' pasajeros') : 'N/A') ?></td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;">Cantidad de días: <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?></td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;">Cantidad de vehículos: 1 unidad</td>
        </tr>
        <tr class="total-row">
            <td>Precio:</td>
            <td>¢<?= number_format($model->precio_por_dia, 0) ?></td>
            <td>1 Unidad x <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?></td>
            <td style="text-align: right;">Total:</td>
            <td style="text-align: right;">¢<?= number_format($model->total_precio, 0) ?></td>
        </tr>
    </table>
</body>
</html>

