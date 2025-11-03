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
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px; 
            padding: 0;
            line-height: 1.4;
        }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
        }
        .company-name { 
            font-size: 18px; 
            font-weight: bold; 
            margin-bottom: 10px; 
        }
        .order-title { 
            font-size: 14px; 
            font-weight: bold; 
            margin: 20px 0; 
            text-align: center;
        }
        .section { 
            margin: 15px 0; 
        }
        .section-title { 
            font-size: 12px; 
            font-weight: bold; 
            margin-bottom: 10px; 
            background-color: #f0f0f0;
            padding: 5px;
        }
        .info-row { 
            margin-bottom: 5px; 
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        .table td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
        }
        .table-header { 
            font-weight: bold; 
            text-align: center; 
            background-color: #f0f0f0;
        }
        .total-row { 
            font-weight: bold; 
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name"><?= htmlspecialchars($companyInfo['name'] ?? 'FACTO AUTOS DE ALQUILER S.A') ?></div>
        <div>San Ramón, Alajuela, Costa Rica</div>
    </div>
    
    <div class="order-title">
        ORDEN DE ALQUILER: <?= htmlspecialchars($rentalId) ?>
    </div>
    
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; padding-right: 10px; vertical-align: top;">
                <div class="info-row"><strong>Nombre:</strong> <?= htmlspecialchars($client ? $client->full_name : 'N/A') ?></div>
                <div class="info-row"><strong>Cédula:</strong> <?= htmlspecialchars($client ? $client->cedula_fisica : 'N/A') ?></div>
                <?php 
                // Mostrar información de licencias de choferes del cliente
                $choferesClient = [];
                if ($client && !empty($client->licencias_choferes)) {
                    $choferesDecoded = json_decode($client->licencias_choferes, true);
                    if (is_array($choferesDecoded)) {
                        $choferesClient = $choferesDecoded;
                    } else {
                        // Si no es JSON válido, mostrar como texto
                        $choferesClient = [$client->licencias_choferes];
                    }
                }
                if (!empty($choferesClient)):
                ?>
                <div class="info-row"><strong>Licencias Choferes:</strong><br>
                    <?php foreach ($choferesClient as $chofer): ?>
                        <?php if (is_array($chofer)): ?>
                            <?php 
                            $choferInfo = [];
                            if (isset($chofer['nombre'])) $choferInfo[] = htmlspecialchars($chofer['nombre']);
                            if (isset($chofer['licencia'])) $choferInfo[] = 'Lic: ' . htmlspecialchars($chofer['licencia']);
                            if (isset($chofer['cedula'])) $choferInfo[] = 'Céd: ' . htmlspecialchars($chofer['cedula']);
                            echo implode(' - ', $choferInfo);
                            ?>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($chofer)) ?>
                        <?php endif; ?><br>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="display: table-cell; width: 50%; padding-left: 10px; vertical-align: top;">
                <div class="info-row"><strong>Teléfono:</strong> <?= htmlspecialchars($client && !empty($client->whatsapp) ? $client->whatsapp : ($client && !empty($client->telefono) ? $client->telefono : 'N/A')) ?></div>
                <?php if (!empty($model->choferes_autorizados)): ?>
                <div class="info-row"><strong>Choferes Autorizados:</strong><br><?= nl2br(htmlspecialchars($model->choferes_autorizados)) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL VEHÍCULO</div>
        <div class="info-row"><strong>Vehículo:</strong> <?= htmlspecialchars($car ? $car->nombre : 'N/A') ?></div>
        <div class="info-row"><strong>Pasajeros:</strong> <?= htmlspecialchars($car ? ($car->cantidad_pasajeros ?: 5) : 'N/A') ?></div>
    </div>
    
    <div class="section">
        <div class="section-title">FECHAS DE ALQUILER</div>
        <div class="info-row"><strong>Fecha inicio:</strong> <?= date('d/m/Y H:i', strtotime($model->fecha_inicio . ' ' . $model->hora_inicio)) ?></div>
        <div class="info-row"><strong>Fecha fin:</strong> <?= date('d/m/Y H:i', strtotime($model->fecha_final . ' ' . $model->hora_final)) ?></div>
        <div class="info-row"><strong>Días:</strong> <?= $model->cantidad_dias ?> días</div>
    </div>
    
    <table class="table">
        <tr class="table-header">
            <td colspan="5">DETALLE DE COSTOS</td>
        </tr>
        <tr>
            <td><strong>Concepto</strong></td>
            <td><strong>Precio por día</strong></td>
            <td><strong>Cantidad</strong></td>
            <td><strong>Días</strong></td>
            <td><strong>Total</strong></td>
        </tr>
        <tr>
            <td>Alquiler de vehículo</td>
            <td>¢<?= number_format($model->precio_por_dia, 0) ?></td>
            <td>1 unidad</td>
            <td><?= $model->cantidad_dias ?></td>
            <td>¢<?= number_format($model->total_precio ?? 0, 0) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="4"><strong>TOTAL A PAGAR:</strong></td>
            <td><strong>¢<?= number_format($model->total_precio ?? 0, 0) ?></strong></td>
        </tr>
    </table>
    
    <div class="section">
        <div class="section-title">CONDICIONES</div>
        <div class="info-row">• El cliente se compromete a devolver el vehículo en las condiciones recibidas.</div>
        <div class="info-row">• Cualquier daño será responsabilidad del cliente.</div>
        <div class="info-row">• El pago debe realizarse antes de la entrega del vehículo.</div>
    </div>
    
    <div style="margin-top: 30px; text-align: center;">
        <div>_________________________</div>
        <div>Firma del Cliente</div>
    </div>
</body>
</html>
