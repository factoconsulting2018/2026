<?php
/** @var array $salesByClient */
/** @var string $reportNumber */
?>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 11px;
    line-height: 1.3;
    margin: 0;
    padding: 15px;
}

.header {
    text-align: center;
    margin-bottom: 25px;
    border-bottom: 2px solid #333;
    padding-bottom: 15px;
}

.company-name {
    font-size: 20px;
    font-weight: bold;
    color: #22487a;
    margin-bottom: 8px;
}

.report-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 8px;
}

.report-info {
    font-size: 11px;
    color: #666;
}

.client-section {
    margin-bottom: 25px;
    page-break-inside: avoid;
}

.client-header {
    background-color: #f8f9fa;
    padding: 10px;
    border-left: 4px solid #22487a;
    margin-bottom: 10px;
}

.client-name {
    font-size: 14px;
    font-weight: bold;
    color: #22487a;
}

.client-summary {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    font-size: 10px;
}

.table th,
.table td {
    border: 1px solid #333;
    padding: 6px;
    text-align: left;
}

.table th {
    background-color: #f5f5f5;
    font-weight: bold;
    font-size: 10px;
}

.table .number {
    text-align: right;
}

.table .center {
    text-align: center;
}

.status-pendiente {
    color: #dc3545;
    font-weight: bold;
}

.status-pagado {
    color: #28a745;
    font-weight: bold;
}

.status-reservado {
    color: #007bff;
    font-weight: bold;
}

.status-cancelado {
    color: #6c757d;
    font-weight: bold;
}

.total-section {
    margin-top: 20px;
    text-align: right;
    border-top: 2px solid #333;
    padding-top: 10px;
}

.total-amount {
    font-size: 14px;
    font-weight: bold;
    color: #22487a;
}
</style>

<div class="header">
    <div class="company-name">🚗 FACTO RENT A CAR</div>
    <div class="report-title">REPORTE DE VENTAS POR CLIENTE</div>
    <div class="report-info">
        <strong>Número de Reporte:</strong> <?= $reportNumber ?><br>
        <strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total de Clientes:</strong> <?= count($salesByClient) ?>
    </div>
</div>

<?php 
$grandTotal = 0;
foreach ($salesByClient as $clientId => $clientData): 
    $client = $clientData['client'];
    $orders = $clientData['orders'];
    $grandTotal += $clientData['total_amount'];
?>
<div class="client-section">
    <div class="client-header">
        <div class="client-name"><?= $client ? strtoupper($client->full_name) : 'CLIENTE #' . $clientId ?></div>
        <div class="client-summary">
            Total de Alquileres: <?= $clientData['total_rentals'] ?> | 
            Monto Total: ₡<?= number_format($clientData['total_amount'], 2) ?>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 10%;">ID Alquiler</th>
                <th style="width: 12%;">Fecha Creación</th>
                <th style="width: 10%;">Fecha Inicio</th>
                <th style="width: 10%;">Fecha Final</th>
                <th style="width: 8%;">Días</th>
                <th style="width: 15%;">Tipo de Carro</th>
                <th style="width: 10%;">Monto (₡)</th>
                <th style="width: 9%;">Estado Pago</th>
                <th style="width: 10%;">Método Pago</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $index => $order): ?>
            <tr>
                <td class="center"><?= $index + 1 ?></td>
                <td><?= $order->rental_id ?></td>
                <td><?= date('d/m/Y', strtotime($order->created_at)) ?></td>
                <td><?= $order->fecha_inicio ? date('d/m/Y', strtotime($order->fecha_inicio)) : 'N/A' ?></td>
                <td><?= $order->fecha_final ? date('d/m/Y', strtotime($order->fecha_final)) : 'N/A' ?></td>
                <td class="number"><?= $order->cantidad_dias ?></td>
                <td><?= $order->car ? $order->car->nombre : 'N/A' ?></td>
                <td class="number">₡<?= number_format($order->total_precio, 2) ?></td>
                <td class="center status-<?= $order->estado_pago ?>"><?= strtoupper($order->estado_pago) ?></td>
                <td class="center"><?= $order->comprobante_pago ?: 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<div class="total-section">
    <div class="total-amount">
        TOTAL GENERAL: ₡<?= number_format($grandTotal, 2) ?>
    </div>
</div>

<div style="margin-top: 30px; text-align: center; font-size: 9px; color: #666;">
    <p>Este reporte fue generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres</p>
    <p>FACTO RENT A CAR - Sistema de Gestión</p>
</div>
