<?php
/** @var array $rentals */
/** @var string $reportNumber */

use yii\helpers\Html;

// Calcular totales
$totalGeneral = 0;
$totalAbonos = 0;
foreach ($rentals as $rental) {
    $totalGeneral += ($rental->total_precio ?: 0);
    for ($i = 1; $i <= 5; $i++) {
        $monto = $rental->{"abono{$i}_monto"} ?: 0;
        $totalAbonos += $monto;
    }
}
$saldoPendiente = $totalGeneral - $totalAbonos;
?>
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 8px;
    line-height: 1.4;
    margin: 0;
    padding: 20px;
}

.header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #17a2b8;
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
    color: #17a2b8;
}

.report-info {
    font-size: 10px;
    color: #666;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    font-size: 7px;
}

.table th,
.table td {
    border: 1px solid #333;
    padding: 3px;
    text-align: left;
}

.table th {
    background-color: #b3d9ff;
    font-weight: bold;
    font-size: 7px;
}

.table .number {
    text-align: right;
}

.total-section {
    margin-top: 15px;
    border-top: 2px solid #17a2b8;
    padding-top: 10px;
    font-size: 10px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 10px;
    border-bottom: 1px solid #ccc;
}

.total-row.total-final {
    font-weight: bold;
    font-size: 12px;
    border-bottom: 2px solid #17a2b8;
}
</style>

<div class="header">
    <div class="company-name">🚗 FACTO RENT A CAR</div>
    <div class="report-title">REPORTE DE RESERVAS</div>
    <div class="report-info">
        <strong>Número de Reporte:</strong> <?= date('YmdHis') ?><br>
        <strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total de Reservas:</strong> <?= count($rentals) ?>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width: 5%;">ID</th>
            <th style="width: 10%;">Nombre</th>
            <th style="width: 5%;">Cédula</th>
            <th style="width: 6%;">Teléfono</th>
            <th style="width: 8%;">Vehículo</th>
            <th style="width: 5%;">Placa</th>
            <th style="width: 5%;">Inicio</th>
            <th style="width: 5%;">Fin</th>
            <th style="width: 6%;">Total</th>
            <th style="width: 5%;">Desc.1</th>
            <th style="width: 5%;">Mto.1</th>
            <th style="width: 5%;">Desc.2</th>
            <th style="width: 5%;">Mto.2</th>
            <th style="width: 5%;">Desc.3</th>
            <th style="width: 5%;">Mto.3</th>
            <th style="width: 5%;">Desc.4</th>
            <th style="width: 5%;">Mto.4</th>
            <th style="width: 5%;">Desc.5</th>
            <th style="width: 5%;">Mto.5</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rentals as $rental): ?>
        <tr>
            <td class="number"><?= $rental->rental_id ?: 'R' . str_pad($rental->id, 6, '0', STR_PAD_LEFT) ?></td>
            <td><?= $rental->client ? Html::encode($rental->client->full_name) : 'N/A' ?></td>
            <td><?= $rental->client ? ($rental->client->cedula_fisica ?: 'N/A') : 'N/A' ?></td>
            <td><?= $rental->client ? ($rental->client->whatsapp ?: ($rental->client->telefono ?: 'N/A')) : 'N/A' ?></td>
            <td><?= $rental->car ? Html::encode($rental->car->nombre) : 'N/A' ?></td>
            <td><?= $rental->car ? ($rental->car->placa ?: 'N/A') : 'N/A' ?></td>
            <td><?= $rental->fecha_inicio ? date('d/m/Y', strtotime($rental->fecha_inicio)) : 'N/A' ?></td>
            <td><?= $rental->fecha_final ? date('d/m/Y', strtotime($rental->fecha_final)) : 'N/A' ?></td>
            <td class="number">₡<?= number_format($rental->total_precio ?: 0, 2) ?></td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php 
                $descripcion = $rental->{"abono{$i}_descripcion"} ?: '';
                $monto = $rental->{"abono{$i}_monto"} ?: '';
                ?>
                <td><?= $descripcion ?: 'N/A' ?></td>
                <td class="number"><?= $monto ? '₡' . number_format($monto, 2) : 'N/A' ?></td>
            <?php endfor; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-section">
    <div class="total-row">
        <span><strong>TOTAL GENERAL:</strong></span>
        <span><strong>₡<?= number_format($totalGeneral, 2) ?></strong></span>
    </div>
    <div class="total-row">
        <span><strong>TOTAL ABONOS:</strong></span>
        <span><strong>₡<?= number_format($totalAbonos, 2) ?></strong></span>
    </div>
    <div class="total-row total-final">
        <span><strong>SALDO PENDIENTE:</strong></span>
        <span><strong>₡<?= number_format($saldoPendiente, 2) ?></strong></span>
    </div>
</div>

<div style="margin-top: 30px; text-align: center; font-size: 8px; color: #666;">
    <p>Este reporte fue generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres</p>
    <p>FACTO RENT A CAR - Sistema de Gestión</p>
</div>
