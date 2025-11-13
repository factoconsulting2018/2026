<?php
/** @var array $orders */
/** @var float $totalAmount */
/** @var string $reportNumber */
?>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
    padding: 20px;
}

.header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #0f1d41;
    padding-bottom: 20px;
}

.company-name {
    font-size: 24px;
    font-weight: bold;
    color: #22487a;
    margin-bottom: 10px;
}

.report-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #0f1d41;
    text-transform: uppercase;
}

.report-info {
    font-size: 12px;
    color: #666;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.table th,
.table td {
    border: 1px solid #333;
    padding: 8px;
    text-align: left;
}

.table th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.table .number {
    text-align: right;
}

.tag-async {
    display: inline-block;
    padding: 2px 6px;
    background-color: #ff6600;
    color: #fff;
    border-radius: 4px;
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
}

.total-section {
    margin-top: 20px;
    text-align: right;
    border-top: 2px solid #333;
    padding-top: 10px;
}

.total-amount {
    font-size: 16px;
    font-weight: bold;
    color: #22487a;
}
</style>

<div class="header">
    <div class="company-name">🚗 FACTO RENT A CAR</div>
    <div class="report-title">Reporte de Órdenes Asincrónicas</div>
    <div class="report-info">
        <strong>Número de Reporte:</strong> <?= $reportNumber ?><br>
        <strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total de Registros:</strong> <?= count($orders) ?>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width: 10%;">ID</th>
            <th style="width: 15%;">ID Orden</th>
            <th style="width: 20%;">Cliente</th>
            <th style="width: 20%;">Vehículo</th>
            <th style="width: 10%;">Fecha Inicio</th>
            <th style="width: 10%;">Fecha Fin</th>
            <th style="width: 7%;">Días</th>
            <th style="width: 8%;">Total (₡)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <?php 
            $rentalId = $order->rental_id ?: 'R' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $client = $order->client ? $order->client->full_name : 'N/A';
            $car = $order->car ? $order->car->nombre : 'N/A';
        ?>
        <tr>
            <td><?= $order->id ?></td>
            <td>
                <?= $rentalId ?><br>
                <span class="tag-async">Asincrónica</span>
            </td>
            <td><?= $client ?></td>
            <td><?= $car ?></td>
            <td><?= $order->fecha_inicio ? date('d/m/Y', strtotime($order->fecha_inicio)) : 'N/A' ?></td>
            <td><?= $order->fecha_final ? date('d/m/Y', strtotime($order->fecha_final)) : 'N/A' ?></td>
            <td class="number"><?= $order->cantidad_dias ?></td>
            <td class="number">₡<?= number_format($order->total_precio ?: 0, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-section">
    <div class="total-amount">
        TOTAL GENERAL: ₡<?= number_format($totalAmount ?: 0, 2) ?>
    </div>
</div>

<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
    <p>Este reporte enumera órdenes registradas de forma asincrónica, usadas para reflejar rentas históricas sin afectar la disponibilidad de vehículos.</p>
    <p>Reporte generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres.</p>
</div>

