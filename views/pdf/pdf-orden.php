<?php
/**
 * Orden de alquiler — plantilla mPDF (formato moderna).
 *
 * @var array $d Datos de la orden (ver PdfController::buildRentalOrderPdfDatos)
 * @var string $condicionesHtml HTML de condiciones (página 2); vacío = sin segunda página
 */
$fmt = static fn ($n) => '¢' . number_format((float) $n, 0, '.', ',');
$h = static fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$vehImgMaxW = (int) ($d['vehiculo']['img_max_w'] ?? 170);
$vehImgMaxH = (int) ($d['vehiculo']['img_max_h'] ?? 90);
$vehImgColW = $vehImgMaxW + 10;
?>
<style>
    @page { margin: 10mm 12mm; }
    body { font-family: dejavusans, sans-serif; font-size: 10pt; color: #222; margin: 0; }

    .header {
        background-color: #0b1f4a;
        color: #ffffff;
        padding: 22px 20px 26px;
    }
    .header table,
    .header td,
    .header th {
        color: #ffffff;
        background-color: transparent;
    }
    .header h1 {
        font-size: 39pt;
        margin: 0 0 6px;
        font-weight: 800;
        letter-spacing: 1px;
        line-height: 1.1;
        color: #ffffff;
    }
    .header .subtitle {
        font-size: 48pt;
        font-weight: 700;
        line-height: 1.05;
        margin: 4px 0 8px;
        color: #ffffff;
    }
    .header .meta {
        font-size: 27pt;
        margin-top: 6px;
        line-height: 1.2;
        color: #ffffff;
    }
    .header .meta b { color: #ffffff; }
    .header table { width: 100%; border-collapse: collapse; }
    .header .logo-cell { width: 110px; vertical-align: middle; }
    .header .logo-cell img { width: 90px; height: 90px; }

    .empresa-band { background: #f3f5f8; padding: 12px 20px; }
    .empresa-band table { width: 100%; border-collapse: collapse; }
    .empresa-band .info { vertical-align: middle; }
    .empresa-band .info b { font-size: 12pt; color: #0b1f4a; }
    .empresa-band .info p { margin: 2px 0; font-size: 9pt; line-height: 1.4; }
    .empresa-band .veh-img { width: <?= $vehImgColW ?>px; text-align: right; vertical-align: middle; }
    .empresa-band .veh-img img { max-width: <?= $vehImgMaxW ?>px; max-height: <?= $vehImgMaxH ?>px; }

    .cuadro { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .cuadro th {
        background: #2a64c8;
        color: #fff;
        text-align: left;
        padding: 7px 10px;
        font-size: 10pt;
    }
    .cuadro td {
        padding: 6px 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 9.5pt;
    }

    .tres-col { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .tres-col th {
        background: #4ea24a;
        color: #fff;
        padding: 7px 10px;
        text-align: left;
        font-size: 10pt;
        width: 33.33%;
    }
    .tres-col td {
        padding: 8px 10px;
        vertical-align: top;
        border-bottom: 1px solid #e5e7eb;
        font-size: 9.5pt;
        width: 33.33%;
    }
    .tres-col .destacado { font-weight: 700; color: #0b1f4a; }

    .bancos {
        background: #0b1f4a;
        color: #fff;
        padding: 14px 20px;
        margin-top: 18px;
        font-size: 9.5pt;
        line-height: 1.5;
    }
    .bancos b { display: block; margin-bottom: 4px; font-size: 10pt; }
    .bancos .marker { display: inline-block; width: 8px; height: 8px; background: #fff; margin: 0 6px 0 4px; }

    .firmas { margin-top: 60px; width: 100%; border-collapse: collapse; }
    .firmas td { width: 50%; text-align: center; vertical-align: top; padding: 0 30px; }
    .firmas .line { border-top: 1px solid #333; margin: 0 30px 6px; }
    .firmas .label { font-size: 9pt; color: #555; }
    .firmas .nombre { font-size: 11pt; margin-top: 4px; font-weight: 600; }
    .firmas .ced { font-size: 9pt; color: #555; }

    .t-title {
        background: #0b1f4a;
        color: #fff;
        padding: 16px 20px;
        font-size: 20pt;
        font-weight: 800;
        margin-bottom: 16px;
    }
    .cond-body { font-size: 10pt; line-height: 1.45; }
</style>

<div class="header">
    <table>
        <tr>
            <td class="logo-cell">
                <?php if (!empty($d['empresa']['logo_fs'])): ?>
                    <img src="<?= $h($d['empresa']['logo_fs']) ?>" alt="logo">
                <?php endif; ?>
            </td>
            <td style="color: #ffffff;">
                <h1>ORDEN DE ALQUILER</h1>
                <div class="subtitle"><?= $h($d['vehiculo']['modelo']) ?></div>
                <div class="meta">
                    No. <b><?= $h($d['numero_orden']) ?></b> &nbsp;|&nbsp;
                    <?= $h($d['fecha_emision']) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="empresa-band">
    <table>
        <tr>
            <td class="info">
                <b><?= $h($d['empresa']['nombre']) ?></b>
                <p><?= $h($d['empresa']['razon_social']) ?> | Cédula Jurídica <?= $h($d['empresa']['cedula']) ?></p>
                <p><?= $h($d['empresa']['direccion']) ?></p>
                <p>WhatsApp: <?= $h($d['empresa']['whatsapp']) ?> | <?= $h($d['empresa']['telefono']) ?> | <?= $h($d['empresa']['web']) ?></p>
            </td>
            <td class="veh-img">
                <?php if (!empty($d['vehiculo']['imagen_fs'])): ?>
                    <img src="<?= $h($d['vehiculo']['imagen_fs']) ?>" alt="vehículo" style="max-width: <?= $vehImgMaxW ?>px; max-height: <?= $vehImgMaxH ?>px;">
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<table class="cuadro">
    <tr>
        <th>CLIENTE</th>
        <th>VEHÍCULO</th>
    </tr>
    <tr>
        <td><?= $h($d['cliente']['nombre']) ?></td>
        <td><?= $h($d['vehiculo']['modelo']) ?> • <?= (int) $d['vehiculo']['pasajeros'] ?> pasajeros</td>
    </tr>
    <tr>
        <td>Cédula: <?= $h($d['cliente']['cedula']) ?></td>
        <td>Placa: <?= $h($d['vehiculo']['placa']) ?></td>
    </tr>
    <tr>
        <td>Teléfono: <?= $h($d['cliente']['telefono']) ?></td>
        <td>Transmisión <?= $h($d['vehiculo']['transmision']) ?></td>
    </tr>
    <tr>
        <td>Licencia vence: <?= $h($d['cliente']['licencia_vence']) ?></td>
        <td>Cobertura: <?= $h($d['vehiculo']['cobertura']) ?></td>
    </tr>
    <tr>
        <td>Cédula vence: <?= $h($d['cliente']['cedula_vence']) ?></td>
        <td>Tarifa diaria: <?= $fmt($d['vehiculo']['tarifa_diaria']) ?></td>
    </tr>
    <?php if (!empty($d['vehiculo']['medio_dia'])): ?>
        <tr>
            <td></td>
            <td>Medio día: <?= $fmt($d['vehiculo']['medio_dia']) ?></td>
        </tr>
    <?php endif; ?>
</table>

<table class="tres-col">
    <tr>
        <th>ENTREGA</th>
        <th>DEVOLUCIÓN</th>
        <th>RESUMEN</th>
    </tr>
    <tr>
        <td>
            <span class="destacado"><?= $h($d['entrega']['fecha']) ?></span><br>
            <?= $h($d['entrega']['hora']) ?> • <?= $h($d['entrega']['sucursal']) ?>
        </td>
        <td>
            <span class="destacado"><?= $h($d['devolucion']['fecha']) ?></span><br>
            <?= $h($d['devolucion']['hora']) ?> • <?= $h($d['devolucion']['sucursal']) ?>
        </td>
        <td><?= $h($d['resumen']['texto_dias']) ?></td>
    </tr>
    <tr>
        <td>
            <b>Corre a partir:</b><br>
            <?= $h($d['entrega']['corre_desde']) ?>
        </td>
        <td><?= $h($d['devolucion']['tipo']) ?></td>
        <td>Subtotal: <?= $fmt($d['resumen']['subtotal']) ?></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td>IVA: <?= $fmt($d['resumen']['iva']) ?></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td><b>Total: <?= $fmt($d['resumen']['total']) ?></b></td>
    </tr>
</table>

<div class="bancos">
    <b>CUENTAS BANCARIAS PARA DEPÓSITO</b>
    <?php foreach ($d['bancos'] as $b): ?>
        <?= $h($b['banco']) ?> <span class="marker"></span> IBAN: <?= $h($b['iban']) ?><br>
    <?php endforeach; ?>
    SINPE Móvil: <?= $h($d['sinpe']) ?><br><br>
    <b>Monto de la reservación: <?= $fmt($d['monto_reservacion']) ?></b> — Reservación firme contra depósito.
</div>

<table class="firmas">
    <tr>
        <td>
            <div class="line"></div>
            <div class="label">Firma del Cliente</div>
            <div class="nombre"><?= $h($d['cliente']['nombre']) ?></div>
            <div class="ced">Cédula: <?= $h($d['cliente']['cedula']) ?></div>
        </td>
        <td>
            <div class="line"></div>
            <div class="label">Firma de Facto Rent a Car</div>
            <div class="nombre"><?= $h($d['ejecutivo']) ?></div>
        </td>
    </tr>
</table>

<?php if (trim((string) $condicionesHtml) !== ''): ?>
<pagebreak />
<div class="t-title">TÉRMINOS Y CONDICIONES DEL ALQUILER</div>
<div class="cond-body"><?= $condicionesHtml ?></div>
<?php endif; ?>
