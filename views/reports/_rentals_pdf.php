<?php
/** @var array $rentalsByCompany */
/** @var array $totalsByCompany */
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
    border-bottom: 2px solid #333;
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
    <div class="report-title">REPORTE DE VENTAS (ALQUILERES)</div>
    <div class="report-info">
        <strong>Número de Reporte:</strong> <?= $reportNumber ?><br>
        <strong>Fecha de Generación:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Total de Registros:</strong> <?= count($rentalsByCompany['Facto Rent a Car']) + count($rentalsByCompany['Moviliza']) ?><br>
        <strong>Facto Rent A Car:</strong> <?= count($rentalsByCompany['Facto Rent a Car']) ?> registros<br>
        <strong>Moviliza:</strong> <?= count($rentalsByCompany['Moviliza']) ?> registros
    </div>
</div>

<?php foreach ($rentalsByCompany as $company => $rentals): ?>
    <?php if (!empty($rentals)): ?>
        <div style="margin-bottom: 30px;">
            <h3 style="background-color: #22487a; color: white; padding: 10px; margin: 0 0 15px 0; text-align: center;">
                <?= $company ?>
            </h3>
            
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Número de Reporte</th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 20%;">Cliente</th>
                        <th style="width: 20%;">Vehículo</th>
                        <th style="width: 10%;">Monto (₡)</th>
                        <th style="width: 10%;">Monto Total (₡)</th>
                        <th style="width: 8%;">Método de Pago</th>
                        <th style="width: 8%;">Ejecutivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><?= $reportNumber ?></td>
                        <td><?= date('d/m/Y', strtotime($rental->created_at)) ?></td>
                        <td><?= $rental->client ? $rental->client->full_name : 'N/A' ?></td>
                        <td><?= $rental->car ? $rental->car->nombre . ' (' . $rental->car->placa . ')' : 'N/A' ?></td>
                        <td class="number">₡<?= number_format($rental->total_precio, 2) ?></td>
                        <td class="number">₡<?= number_format($rental->total_precio, 2) ?></td>
                        <td><?= $rental->comprobante_pago ?? 'N/A' ?></td>
                        <td><?= $rental->ejecutivo ?? 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="text-align: right; margin-top: 10px; font-weight: bold; font-size: 14px; color: #22487a;">
                TOTAL <?= $company ?>: ₡<?= number_format($totalsByCompany[$company], 2) ?>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="total-section">
    <div class="total-amount">
        TOTAL GENERAL: ₡<?= number_format($totalAmount, 2) ?>
    </div>
</div>

<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
    <p>Este reporte fue generado automáticamente el <?= date('d/m/Y H:i:s') ?> por el Sistema de Gestión de Alquileres</p>
    <p>FACTO RENT A CAR - Sistema de Gestión</p>
</div>
