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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 15mm 15mm;
            size: A4 portrait;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px; 
            margin: 0; 
            padding: 0;
            line-height: 1.2;
            page-break-after: avoid;
        }
        .header { 
            margin-bottom: 8px; 
            page-break-after: avoid;
        }
        .company-info { 
            width: 100%; 
            margin-bottom: 6px; 
            page-break-after: avoid;
        }
        .company-name { 
            font-size: 16px; 
            font-weight: bold; 
            font-style: italic; 
            margin-bottom: 2px; 
        }
        .company-legal { 
            font-size: 11px; 
            margin-bottom: 4px; 
        }
        .company-address { 
            font-size: 9px; 
            margin-bottom: 6px; 
        }
        .order-title { 
            font-size: 11px; 
            font-weight: bold; 
            margin: 8px 0; 
            page-break-after: avoid;
        }
        .section-title { 
            font-size: 10px; 
            font-weight: bold; 
            margin-top: 8px; 
            margin-bottom: 3px; 
            page-break-after: avoid;
        }
        .info-row { 
            margin-bottom: 2px; 
            page-break-inside: avoid;
        }
        .vehicle-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 6px; 
            border: 1px solid #000; 
            page-break-inside: avoid;
        }
        .vehicle-table td { 
            border: 1px solid #000; 
            padding: 4px; 
            text-align: left; 
            font-size: 9px; 
        }
        .vehicle-header { 
            font-weight: bold; 
            text-align: center; 
        }
        .total-row { 
            font-weight: bold; 
        }
        * {
            page-break-after: auto;
            page-break-inside: auto;
        }
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
    <?php if ($model->correapartir_enabled && $model->fecha_correapartir): ?>
    <div class="info-row">
        <span class="info-label">Correapartir (Cortesía) desde:</span> 
        <?= formatDatetimeEs($model->fecha_correapartir) ?>
    </div>
    <?php endif; ?>
    <div class="info-row">
        <span class="info-label">Fecha de alquiler:</span> 
        <?= formatDatetimeEs($model->fecha_inicio . ' ' . $model->hora_inicio) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Fecha recoge vehículo:</span> 
        <?= formatDatetimeEs($model->fecha_final . ' ' . $model->hora_final) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Lugar:</span> 
        <?= htmlspecialchars($model->lugar_entrega ?: 'San Ramón') ?>
    </div>
    
    <div class="section-title">DEVOLUCIÓN DEL VEHÍCULO</div>
    <div class="info-row">
        <span class="info-label">Fecha de entrega:</span> 
        <?= formatDatetimeEs($model->fecha_final . ' ' . $model->hora_final) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Lugar:</span> 
        <?= htmlspecialchars($model->lugar_retiro ?: 'San Ramón') ?>
    </div>
    
    <table class="vehicle-table">
        <tr>
            <td class="vehicle-header" colspan="5">Tipo de Vehículo: <?= htmlspecialchars($car ? ($car->nombre . ' - ' . ($car->cantidad_pasajeros ?: 5) . ' pasajeros') : 'N/A') ?></td>
        </tr>
        <?php
        // Determinar si es alquiler por horas (mismo día) o por días
        $isPorHoras = ($model->fecha_inicio === $model->fecha_final || strtotime($model->fecha_inicio) === strtotime($model->fecha_final));
        $unidad = $isPorHoras ? 'horas' : 'días';
        ?>
        <tr>
            <td colspan="5" style="text-align: center;">Cantidad de <?= $unidad ?>: <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?></td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;">Cantidad de vehículos: 1 unidad</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: left; padding: 6px;">
                <?= str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT) ?> <?= $unidad ?> a ¢<?= number_format($model->precio_por_dia, 0) ?> 
                <strong>(¢<?= number_format($model->cantidad_dias * $model->precio_por_dia, 0) ?>)</strong>
            </td>
        </tr>
        <?php if (!empty($model->medio_dia_enabled) && $model->medio_dia_valor > 0): ?>
        <tr>
            <td colspan="5" style="text-align: left; padding: 6px;">
                + 1/2 día (<strong>¢<?= number_format($model->medio_dia_valor, 0) ?> colones</strong>)
            </td>
        </tr>
        <?php endif; ?>
        <tr class="total-row">
            <td colspan="3" style="padding: 6px;"></td>
            <td style="text-align: right; padding: 6px;"><strong>Monto total de la orden:</strong></td>
            <td style="text-align: right; padding: 6px;"><strong>¢<?= number_format($model->total_precio ?? 0, 0) ?> colones</strong></td>
        </tr>
    </table>
</body>
</html>

