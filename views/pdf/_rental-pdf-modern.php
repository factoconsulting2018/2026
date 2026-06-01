<?php
/**
 * Orden de alquiler — Formato moderna (plantilla factorentacar-pdf / mPDF de referencia).
 * Página 1: diseño con cabecera azul, tablas y banda de bancos. La página 2 (condiciones)
 * la añade PdfController / RentalController con el HTML configurado en el sistema.
 */
require __DIR__ . '/_rental-pdf-setup.php';

$fmtColones = static function ($n) {
    return '¢' . number_format((float) $n, 0, '.', ',');
};

$brandRaw = trim((string) ($companyInfo['name'] ?? 'Facto Rent a Car'));
if (function_exists('mb_convert_case')) {
    $companyNombre = pdf_escape(mb_convert_case($brandRaw, MB_CASE_TITLE, 'UTF-8'));
} else {
    $companyNombre = pdf_escape(ucwords(strtolower($brandRaw)));
}

$addrRaw = trim((string) ($companyInfo['address'] ?? 'San Ramón, Alajuela, Costa Rica'));
$addrParts = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $addrRaw))));
if (count($addrParts) >= 2) {
    $companyLineLegal = pdf_escape($addrParts[0]);
    $companyLineGeo = pdf_escape(implode(' ', array_slice($addrParts, 1)));
} elseif (preg_match('/^([\d\-]+)\s*,\s*(.+)$/u', $addrRaw, $m)) {
    $companyLineLegal = pdf_escape('Facto Autos de Alquiler S.A. | Cédula Jurídica ' . trim($m[1]));
    $companyLineGeo = pdf_escape(trim($m[2]));
} else {
    $companyLineLegal = pdf_escape($addrRaw !== '' ? $addrRaw : 'Facto Autos de Alquiler S.A. | Cédula Jurídica 3-101-880789');
    $companyLineGeo = '';
}

$rawPhone = (string) ($companyInfo['phone'] ?? '');
$partsPhone = array_values(array_filter(array_map('trim', preg_split('/[|\/,]+/', $rawPhone))));
$simpeDisplay = strlen($simpe) === 8 ? substr($simpe, 0, 4) . '-' . substr($simpe, 4) : $simpe;
$waParts = [$simpeDisplay];
foreach ($partsPhone as $p) {
    if ($p !== '' && stripos($p, 'www.') === false) {
        $digits = preg_replace('/\D/', '', $p);
        if (strlen($digits) >= 8) {
            $waParts[] = strlen($digits) === 8 ? substr($digits, 0, 4) . '-' . substr($digits, 4) : $p;
        }
    }
}
$waParts = array_values(array_unique($waParts));
$whatsappLine = pdf_escape(implode(' | ', $waParts) . ' | www.factorentacar.com');

try {
    $dtEm = new DateTime($model->created_at ?: date('Y-m-d'));
    $mesesEm = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaEmisionDoc = (int) $dtEm->format('j') . ' de ' . $mesesEm[(int) $dtEm->format('n')] . ' de ' . $dtEm->format('Y');
} catch (Exception $e) {
    $fechaEmisionDoc = date('j') . ' de ' . date('F') . ' de ' . date('Y');
}

$bancosList = [];
if (is_array($accounts) && $accounts !== []) {
    foreach ($accounts as $acc) {
        $b = strtoupper(trim((string) ($acc['bank'] ?? '')));
        $cur = trim((string) ($acc['currency'] ?? '₡'));
        $cuenta = trim((string) ($acc['account_number'] ?? ''));
        $iban = strtoupper(preg_replace('/\s+/', '', (string) ($acc['iban'] ?? '')));
        if ($iban === '' && !empty($acc['account'])) {
            $legacy = (string) $acc['account'];
            if (preg_match('/IBAN\s*:?\s*(CR[\d\s]+)/i', $legacy, $m)) {
                $iban = strtoupper(preg_replace('/\s+/', '', $m[1]));
            }
        }
        if ($b !== '' && ($iban !== '' || $cuenta !== '')) {
            $bancosList[] = [
                'banco' => pdf_escape($b),
                'moneda' => pdf_escape($cur),
                'cuenta' => pdf_escape($cuenta),
                'iban' => pdf_escape($iban),
            ];
        }
    }
}
if ($bancosList === []) {
    $bancosList = [
        ['banco' => 'BCR', 'moneda' => '₡', 'cuenta' => '', 'iban' => pdf_escape($ibanBcr)],
        ['banco' => 'BN',  'moneda' => '₡', 'cuenta' => '', 'iban' => pdf_escape($ibanBn)],
    ];
}

usort($bancosList, function ($a, $b) {
    $order = ['BN' => 1, 'BCR' => 2, 'BAC' => 3];
    $oa = $order[$a['banco']] ?? 9;
    $ob = $order[$b['banco']] ?? 9;
    if ($oa !== $ob) return $oa <=> $ob;
    $ma = ($a['moneda'] === '$') ? 2 : 1;
    $mb = ($b['moneda'] === '$') ? 2 : 1;
    return $ma <=> $mb;
});

$transmisionTxt = '—';
if ($car && !empty($car->caracteristicas)) {
    $cx = strtolower((string) $car->caracteristicas);
    if (strpos($cx, 'automática') !== false || strpos($cx, 'automatica') !== false || strpos($cx, 'cvt') !== false) {
        $transmisionTxt = 'Transmisión automática';
    } elseif (strpos($cx, 'manual') !== false) {
        $transmisionTxt = 'Transmisión manual';
    }
}
$transmisionCorta = '—';
if ($transmisionTxt !== '—') {
    $short = str_ireplace('Transmisión ', '', $transmisionTxt);
    $transmisionCorta = pdf_escape(function_exists('mb_strtolower') ? mb_strtolower($short, 'UTF-8') : strtolower($short));
}

$coberturaTxt = 'Full cobertura';

$fechaEntregaLarga = formatFechaConDiaSemana($model->fecha_inicio);
$horaEntregaTxt = formatHoraSpanish($model->hora_inicio ?? '');
$fechaDevLarga = formatFechaConDiaSemana($model->fecha_final);
$horaDevTxt = formatHoraSpanish($model->hora_final ?? '');

$vehiculoNombre = pdf_escape($car ? ($car->nombre ?? 'N/A') : 'N/A');
$pasajerosNum = (int) ($car ? ($car->cantidad_pasajeros ?: 5) : 5);
$placaLinea = $car && !empty($car->placa) ? pdf_escape($car->placa) : '—';

$resumenDiasTxt = (int) $model->cantidad_dias . ' día' . ((int) $model->cantidad_dias === 1 ? '' : 's')
    . ' • ' . $cantidadVehiculos . ' vehículo' . ($cantidadVehiculos === 1 ? '' : 's');

$subtotalFmt = number_format($subtotalNum, 0, '.', ',');
$ivaFmt = number_format($ivaNum, 0, '.', ',');
$totalFmt = number_format($totalNum, 0, '.', ',');

$telRaw = '';
if ($client) {
    $telRaw = (string) ($client->whatsapp ?: $client->telefono ?: '');
}
$telDisplay = pdf_escape($telRaw);
$digits = preg_replace('/\D/', '', $telRaw);
if (strlen($digits) === 8) {
    $telDisplay = pdf_escape('506 ' . substr($digits, 0, 4) . ' ' . substr($digits, 4, 4));
} elseif (strlen($digits) === 11 && substr($digits, 0, 3) === '506') {
    $telDisplay = pdf_escape('506 ' . substr($digits, 3, 4) . ' ' . substr($digits, 7, 4));
}
if ($telDisplay === '' || $telRaw === '') {
    $telDisplay = $clienteTelefono;
}

$licVence = !empty($vencimientoLicencia) ? pdf_escape($vencimientoLicencia) : '—';
$cedVence = !empty($vencimientoCedula) ? pdf_escape($vencimientoCedula) : '—';
$correApartirTxt = !empty($fechaCorreapartir) ? pdf_escape($fechaCorreapartir) : '—';

$vehiculoImgSrc = '';
if ($car && !empty($car->imagen)) {
    $im = trim((string) $car->imagen);
    if (preg_match('#^https?://#i', $im)) {
        $vehiculoImgSrc = $im;
    } elseif (strpos($im, '@') === 0) {
        $full = Yii::getAlias($im);
        if (is_string($full) && is_file($full)) {
            $vehiculoImgSrc = str_replace('\\', '/', $full);
        }
    } else {
        $webroot = str_replace('\\', '/', Yii::getAlias('@webroot'));
        $rel = ltrim(str_replace('\\', '/', $im), '/');
        $full = ($im !== '' && $im[0] === '/') ? ($webroot . $im) : ($webroot . '/' . $rel);
        if (is_file($full)) {
            $vehiculoImgSrc = str_replace('\\', '/', $full);
        }
    }
}

$tarifaDiaNum = (float) $model->precio_por_dia;
?>
<style>
    @page { margin: 10mm 12mm; }
    body { font-family: dejavusans, sans-serif; font-size: 10pt; color: #222; margin: 0; }

    .header {
        background: #0b1f4a;
        color: #fff;
        padding: 18px 20px;
        position: relative;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .header .logo-cell { width: 110px; vertical-align: middle; }
    .header .logo-cell img { width: 90px; height: 90px; }
    .header h1 { font-size: 26pt; margin: 0; font-weight: 800; letter-spacing: 1px; }
    .header .subtitle { font-size: 16pt; font-weight: 700; }
    .header .meta { font-size: 9pt; margin-top: 4px; }

    .empresa-band { background: #f3f5f8; padding: 12px 20px; }
    .empresa-band table { width: 100%; }
    .empresa-band .info { vertical-align: middle; }
    .empresa-band .info b { font-size: 12pt; color: #0b1f4a; }
    .empresa-band .info p { margin: 2px 0; font-size: 9pt; line-height: 1.4; }
    .empresa-band .veh-img { width: 180px; text-align: right; vertical-align: middle; }
    .empresa-band .veh-img img { max-width: 170px; max-height: 90px; }

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
    .bancos b { display: block; margin-bottom: 6px; font-size: 10pt; }
    .bancos .marker { display: inline-block; width: 8px; height: 8px; background: #fff; margin: 0 6px 0 4px; }
    .bancos .banks-table { width: 100%; border-collapse: collapse; color: #fff; font-size: 9pt; }
    .bancos .banks-table td { padding: 2px 6px; vertical-align: top; color: #fff; }
    .bancos .banks-table td.bank-name  { font-weight: 700; white-space: nowrap; width: 60px; }
    .bancos .banks-table td.bank-cur   { font-weight: 700; white-space: nowrap; width: 18px; }
    .bancos .banks-table td.bank-label { white-space: nowrap; width: 56px; opacity: 0.85; }
    .bancos .banks-table td.bank-value { font-family: dejavusansmono, monospace; font-size: 8.5pt; letter-spacing: 0.3px; }
    .bancos .sinpe-row { margin-top: 6px; }

    .firmas { margin-top: 60px; width: 100%; }
    .firmas td { width: 50%; text-align: center; vertical-align: top; padding: 0 30px; }
    .firmas .line { border-top: 1px solid #333; margin: 0 30px 6px; }
    .firmas .label { font-size: 9pt; color: #555; }
    .firmas .nombre { font-size: 11pt; margin-top: 4px; font-weight: 600; }
    .firmas .ced { font-size: 9pt; color: #555; }
</style>

<div class="header">
    <table>
        <tr>
            <td class="logo-cell">
                <?php if (!empty($logoPath)): ?>
                    <img src="<?= pdf_escape($logoPath) ?>" alt="logo">
                <?php endif; ?>
            </td>
            <td>
                <h1>ORDEN DE ALQUILER</h1>
                <div class="subtitle"><?= $vehiculoNombre ?></div>
                <div class="meta">
                    No. <b><?= pdf_escape($rentalId) ?></b> &nbsp;|&nbsp;
                    <?= pdf_escape($fechaEmisionDoc) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="empresa-band">
    <table>
        <tr>
            <td class="info">
                <b><?= $companyNombre ?></b>
                <p><?= $companyLineLegal ?></p>
                <?php if ($companyLineGeo !== ''): ?>
                    <p><?= $companyLineGeo ?></p>
                <?php endif; ?>
                <p>WhatsApp: <?= $whatsappLine ?></p>
            </td>
            <td class="veh-img">
                <?php if ($vehiculoImgSrc !== ''): ?>
                    <img src="<?= pdf_escape($vehiculoImgSrc) ?>" alt="vehículo">
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
        <td><?= $clienteNombre ?></td>
        <td><?= $vehiculoNombre ?> • <?= (int) $pasajerosNum ?> pasajeros</td>
    </tr>
    <tr>
        <td>Cédula: <?= $clienteCedula ?></td>
        <td>Placa: <?= $placaLinea ?></td>
    </tr>
    <tr>
        <td>Teléfono: <?= $telDisplay ?></td>
        <td>Transmisión <?= $transmisionCorta ?></td>
    </tr>
    <tr>
        <td>Licencia vence: <?= $licVence ?></td>
        <td>Cobertura: <?= pdf_escape($coberturaTxt) ?></td>
    </tr>
    <tr>
        <td>Cédula vence: <?= $cedVence ?></td>
        <td>Tarifa diaria: <?= $fmtColones($tarifaDiaNum) ?></td>
    </tr>
    <?php if ($medioDiaActivo): ?>
        <tr>
            <td></td>
            <td>Medio día: <?= $fmtColones($medioDiaValor) ?></td>
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
            <span class="destacado"><?= pdf_escape($fechaEntregaLarga) ?></span><br>
            <?= pdf_escape($horaEntregaTxt) ?> • <?= $entregaLugar ?>
        </td>
        <td>
            <span class="destacado"><?= pdf_escape($fechaDevLarga) ?></span><br>
            <?= pdf_escape($horaDevTxt) ?> • <?= $lugarRetiro ?>
        </td>
        <td><?= pdf_escape($resumenDiasTxt) ?></td>
    </tr>
    <tr>
        <td>
            <b>Corre a partir:</b><br>
            <?= $correApartirTxt ?>
        </td>
        <td>Retiro en sucursal</td>
        <td>Subtotal: ¢<?= $subtotalFmt ?></td>
    </tr>
    <?php if ((float) ($ivaNum ?? 0) > 0): ?>
    <tr>
        <td></td>
        <td></td>
        <td>IVA: ¢<?= $ivaFmt ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td></td>
        <td></td>
        <td><b>Total: ¢<?= $totalFmt ?></b></td>
    </tr>
</table>

<div class="bancos">
    <b>CUENTAS BANCARIAS PARA DEPÓSITO</b>
    <table class="banks-table">
        <?php foreach ($bancosList as $b):
            $bancoTxt = (string) ($b['banco'] ?? '');
            $monedaTxt = (string) ($b['moneda'] ?? '₡');
            $cuentaTxt = (string) ($b['cuenta'] ?? '');
            $ibanTxt = (string) ($b['iban'] ?? '');
        ?>
            <tr>
                <td class="bank-name"><?= $bancoTxt ?></td>
                <td class="bank-cur"><?= $monedaTxt ?></td>
                <?php if ($cuentaTxt !== ''): ?>
                    <td class="bank-label">Cuenta:</td>
                    <td class="bank-value"><?= $cuentaTxt ?></td>
                <?php else: ?>
                    <td class="bank-label"></td>
                    <td class="bank-value"></td>
                <?php endif; ?>
                <td class="bank-label">IBAN:</td>
                <td class="bank-value"><?= $ibanTxt ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="sinpe-row">SINPE Móvil: <?= pdf_escape($simpeDisplay) ?></div>
    <br>
    <b>Monto de la reservación: <?= $fmtColones($totalNum) ?></b> — Reservación firme contra depósito.
</div>

<table class="firmas">
    <tr>
        <td>
            <div class="line"></div>
            <div class="label">Firma del Cliente</div>
            <div class="nombre"><?= $clienteNombre ?></div>
            <div class="ced">Cédula: <?= $clienteCedula ?></div>
        </td>
        <td>
            <div class="line"></div>
            <div class="label">Firma de Facto Rent a Car</div>
            <div class="nombre">Ejecutivo de turno.</div>
        </td>
    </tr>
</table>
