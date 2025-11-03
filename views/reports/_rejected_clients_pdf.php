<?php
/** @var array $clients */
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
    border-bottom: 2px solid #dc3545;
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
    color: #dc3545;
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
    background-color: #ffb6c6;
    font-weight: bold;
}

.table .number {
    text-align: right;
}

.total-section {
    margin-top: 20px;
    text-align: center;
    border-top: 2px solid #dc3545;
    padding-top: 10px;
}

.total-clients {
    font-size: 16px;
    font-weight: bold;
    color: #dc3545;
}
</style>

<div class="header">
    <div class="company-name">🚗 FACTO RENT A CAR</div>
    <div class="report-title">REPORTE DE CLIENTES RECHAZADOS</div>
    <div class="report-info">
        <strong>Número de Reporte:</strong> <?= $reportNumber ?><br>
        <strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total de Registros:</strong> <?= count($clients) ?>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width: 8%;">Número de Reporte</th>
            <th style="width: 6%;">ID</th>
            <th style="width: 20%;">Nombre Completo</th>
            <th style="width: 12%;">Cédula</th>
            <th style="width: 12%;">WhatsApp</th>
            <th style="width: 18%;">Email</th>
            <th style="width: 14%;">Dirección</th>
            <th style="width: 10%;">Fecha Rechazo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client): ?>
        <tr>
            <td><?= $reportNumber ?></td>
            <td class="number"><?= $client->id ?></td>
            <td><?= $client->full_name ?></td>
            <td><?= $client->cedula_fisica ?: 'N/A' ?></td>
            <td><?= $client->whatsapp ?: 'N/A' ?></td>
            <td><?= $client->email ?: 'N/A' ?></td>
            <td><?= $client->address ?: 'N/A' ?></td>
            <td><?= date('d/m/Y', strtotime($client->updated_at)) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-section">
    <div class="total-clients">
        TOTAL DE CLIENTES RECHAZADOS: <?= count($clients) ?>
    </div>
</div>

<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
    <p>Este reporte fue generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres</p>
    <p>FACTO RENT A CAR - Sistema de Gestión</p>
</div>

