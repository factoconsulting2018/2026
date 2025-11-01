<?php
$rentalId = $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
$client = $model->client;
$car = $model->car;

// Formateador de fechas en español con día de la semana y hora en 12h am/pm
if (!function_exists('formatDatetimeEs')) {
    function formatDatetimeEs(string $datetime): string {
        try {
            $dt = new DateTime($datetime);
        } catch (Exception $e) {
            return $datetime; // fallback
        }
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $diaSemana = $dias[(int)$dt->format('N')] ?? '';
        $dia = $dt->format('d');
        $mes = $meses[(int)$dt->format('n')] ?? '';
        $anio = $dt->format('Y');
        $hora = strtolower($dt->format('h:i a')); // 12h con am/pm en minúscula
        return "$diaSemana $dia de $mes de $anio $hora";
    }
}

// Calcular valores para desglose
$medioDiaEnabled = intval($model->medio_dia_enabled ?? 0);
$medioDiaValor = floatval($model->medio_dia_valor ?? 0);
$medioDiaActivo = ($medioDiaEnabled >= 1) && ($medioDiaValor > 0);
$isPorHoras = ($model->fecha_inicio === $model->fecha_final || strtotime($model->fecha_inicio) === strtotime($model->fecha_final));
$unidad = $isPorHoras ? 'horas' : 'días';
$subtotalDias = $model->cantidad_dias * $model->precio_por_dia;
$totalFinal = $model->total_precio;
if (empty($totalFinal) || $totalFinal == 0) {
    $totalFinal = $model->calculateTotalPrice();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 8mm 15mm;
            size: A4 portrait;
        }
        body { 
            font-family: 'Times New Roman', Georgia, serif; 
            font-size: 10px; 
            margin: 0; 
            padding: 0;
            line-height: 1.5;
            color: #333;
        }
        .header-section {
            margin-bottom: 12px;
            padding-bottom: 0;
            margin-top: 0;
        }
        .company-name { 
            font-size: 20px; 
            font-weight: bold; 
            font-style: italic; 
            margin-bottom: 6px; 
            margin-top: 0;
            color: #000;
            font-family: 'Times New Roman', Georgia, serif;
            text-align: left;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .company-legal { 
            font-size: 12px; 
            margin-bottom: 6px;
            margin-top: 0;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.3;
            text-align: left;
        }
        .company-address { 
            font-size: 10px; 
            margin-bottom: 0;
            margin-top: 0;
            line-height: 1.6;
            text-align: left;
        }
        .company-address .line {
            display: block;
            margin-bottom: 3px;
        }
        .order-header {
            background-color: #f5f5f5;
            padding: 8px 10px;
            margin: 12px 0 10px 0;
            border-left: 4px solid #22487a;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .client-section {
            background-color: #f9f9f9;
            padding: 8px 10px;
            margin: 10px 0;
            border-left: 3px solid #4CAF50;
        }
        .client-info {
            margin: 3px 0;
            font-size: 10px;
        }
        .client-label {
            font-weight: bold;
            display: inline-block;
            width: 130px;
        }
        .section-container {
            margin: 12px 0;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #fafafa;
        }
        .section-title { 
            font-size: 11px; 
            font-weight: bold; 
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
            color: #22487a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row { 
            margin: 5px 0;
            font-size: 10px;
            padding: 3px 0;
        }
        .info-label { 
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        .info-value {
            color: #333;
        }
        .client-label {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 9px;
        }
        .vehicle-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            border: 2px solid #000;
            background-color: #fff;
        }
        .vehicle-table td { 
            border: 1px solid #000; 
            padding: 8px 6px;
            text-align: center; 
            font-size: 10px;
        }
        .vehicle-header { 
            background-color: #22487a;
            color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 13px;
            padding: 12px 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .vehicle-quantity {
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .price-detail-row {
            background-color: #fff;
            border-top: 1px dashed #ccc;
        }
        .price-detail-row td {
            padding: 6px 8px;
            font-size: 10px;
            text-align: center;
        }
        .total-row { 
            background-color: #e8e8e8;
            border-top: 2px solid #000;
            font-weight: bold;
        }
        .total-row td {
            padding: 10px 8px;
            font-size: 11px;
            text-align: center;
        }
        .total-row strong {
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-section {
            margin-top: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
        }
        .payment-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #22487a;
            border-bottom: 1px solid #22487a;
            padding-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-info {
            font-size: 9px;
            margin: 4px 0;
            line-height: 1.5;
        }
        .payment-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
        }
        .separator {
            margin: 10px 0;
            border-top: 1px dashed #ccc;
        }
        .info-label {
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Encabezado de la empresa -->
    <div class="header-section">
        <div class="company-name"><?= htmlspecialchars($companyInfo['name'] ?? 'FACTO RENT A CAR') ?></div>
        <div class="company-legal">FACTO AUTOS DE ALQUILER S.A</div>
        <div class="company-address">
            <span class="line">3-101-880789</span>
            <span class="line">San Ramón, Alajuela.</span>
            <span class="line">Costa Rica</span>
        </div>
    </div>
    
    <!-- Información de la orden -->
    <div class="order-header">
        Orden de Alquiler: <span style="color: #dc3545; font-weight: bold;"><?= htmlspecialchars($rentalId) ?></span> - <?= htmlspecialchars($car ? $car->nombre : 'N/A') ?>
    </div>
    
    <!-- Información del cliente -->
    <div class="client-section">
        <div class="section-title">Información del Cliente</div>
        <div class="client-info">
            <span class="client-label">Nombre:</span>
            <span><?= htmlspecialchars($client ? $client->full_name : 'N/A') ?></span>
        </div>
        <div class="client-info">
            <span class="client-label">Cédula:</span>
            <span><?= htmlspecialchars($client ? $client->cedula_fisica : 'N/A') ?></span>
        </div>
        <?php if ($client && $client->telefono): ?>
        <div class="client-info">
            <span class="client-label">Teléfono:</span>
            <span><?= htmlspecialchars($client->telefono) ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Entrega del vehículo -->
    <div class="section-container">
        <div class="section-title">Entrega del Vehículo</div>
        <?php if ($model->correapartir_enabled && $model->fecha_correapartir): ?>
        <div class="info-row">
            <span class="info-label">Correapartir (Cortesía):</span>
            <span class="info-value"><?= formatDatetimeEs($model->fecha_correapartir) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Fecha de alquiler:</span>
            <span class="info-value"><?= formatDatetimeEs($model->fecha_inicio . ' ' . $model->hora_inicio) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha recoge vehículo:</span>
            <span class="info-value"><?= formatDatetimeEs($model->fecha_final . ' ' . $model->hora_final) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Lugar de entrega:</span>
            <span class="info-value"><?= htmlspecialchars($model->lugar_entrega ?: 'San Ramón') ?></span>
        </div>
    </div>
    
    <!-- Devolución del vehículo -->
    <div class="section-container">
        <div class="section-title">Devolución del Vehículo</div>
        <div class="info-row">
            <span class="info-label">Fecha de entrega:</span>
            <span class="info-value"><?= formatDatetimeEs($model->fecha_final . ' ' . $model->hora_final) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Lugar de retiro:</span>
            <span class="info-value"><?= htmlspecialchars($model->lugar_retiro ?: 'San Ramón') ?></span>
        </div>
    </div>
    
    <!-- Detalles del vehículo y precios -->
    <table class="vehicle-table">
        <tr>
            <td class="vehicle-header" colspan="5">
                Tipo de Vehículo: <?= htmlspecialchars($car ? ($car->nombre . ' - ' . ($car->cantidad_pasajeros ?: 5) . ' pasajeros') : 'N/A') ?>
            </td>
        </tr>
        <tr>
            <td class="vehicle-quantity" colspan="5">
                Cantidad de <?= $unidad ?>: <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?> | 
                Cantidad de vehículos: 1 unidad
            </td>
        </tr>
        
        <!-- Desglose de precios -->
        <tr class="price-detail-row">
            <td colspan="5" style="padding: 8px 10px; text-align: center;">
                <strong>Cantidad días: <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?> <?= $unidad ?> = ₡<?= number_format($subtotalDias, 0, '.', ',') ?></strong>
            </td>
        </tr>
        <?php if ($medioDiaActivo): ?>
        <tr class="price-detail-row">
            <td colspan="5" style="padding: 8px 10px; text-align: center;">
                <strong>1/2 día: ₡<?= number_format($medioDiaValor, 0, '.', ',') ?></strong>
            </td>
        </tr>
        <?php endif; ?>
        
        <!-- Total -->
        <tr class="total-row">
            <td colspan="3" style="text-align: center;">
                <strong>Monto Total de la Orden:</strong>
            </td>
            <td colspan="2" style="text-align: center;">
                <strong style="font-size: 13px;">₡<?= number_format($totalFinal, 0, '.', ',') ?> colones</strong>
            </td>
        </tr>
    </table>
    
    <!-- Información de pago -->
    <div class="payment-section">
        <div class="payment-title">Información de Pago</div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
            <tr>
                <td style="padding: 4px 0; font-weight: bold; width: 120px; vertical-align: top;">BCR®:</td>
                <td style="padding: 4px 0; vertical-align: top;">IBAN: CR75015201001050506181</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; font-weight: bold; vertical-align: top;">BN®:</td>
                <td style="padding: 4px 0; vertical-align: top;">IBAN: CR49015102020010977051</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; font-weight: bold; vertical-align: top;">SINPE MÓVIL:</td>
                <td style="padding: 4px 0; vertical-align: top;">83670937</td>
            </tr>
        </table>
    </div>
</body>
</html>
