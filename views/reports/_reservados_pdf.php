<?php
/** @var array $rentals */
/** @var string $reportNumber */

use yii\helpers\Html;
?>
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 10px;
    line-height: 1.4;
    margin: 0;
    padding: 20px;
}

.header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #17a2b8;
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
    color: #17a2b8;
}

.report-info {
    font-size: 12px;
    color: #666;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 9px;
}

.table th,
.table td {
    border: 1px solid #333;
    padding: 4px;
    text-align: left;
}

.table th {
    background-color: #b3d9ff;
    font-weight: bold;
}

.table .number {
    text-align: right;
}

.total-section {
    margin-top: 20px;
    text-align: center;
    border-top: 2px solid #17a2b8;
    padding-top: 10px;
}

.total-rentals {
    font-size: 16px;
    font-weight: bold;
    color: #17a2b8;
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
            <th style="width: 8%;">ID</th>
            <th style="width: 15%;">Nombre Cliente</th>
            <th style="width: 8%;">Cédula</th>
            <th style="width: 8%;">Teléfono</th>
            <th style="width: 12%;">Vehículo</th>
            <th style="width: 8%;">Placa</th>
            <th style="width: 7%;">Inicio</th>
            <th style="width: 7%;">Fin</th>
            <th style="width: 8%;">Total</th>
            <th style="width: 9%;">Abono 1</th>
            <th style="width: 9%;">Abono 2</th>
            <th style="width: 9%;">Abono 3</th>
            <th style="width: 9%;">Abono 4</th>
            <th style="width: 9%;">Abono 5</th>
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
                <td><?= $descripcion && $monto ? "$descripcion: ₡" . number_format($monto, 2) : 'N/A' ?></td>
            <?php endfor; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-section">
    <div class="total-rentals">
        TOTAL DE RESERVAS: <?= count($rentals) ?>
    </div>
</div>

<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
    <p>Este reporte fue generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres</p>
    <p>FACTO RENT A CAR - Sistema de Gestión</p>
</div>

