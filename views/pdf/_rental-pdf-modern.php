<?php
/**
 * Orden de alquiler — Formato Moderna (referencia ordendereferencia.pdf).
 * Maquetación tipo carta: texto negro, secciones en mayúsculas con línea inferior.
 */
require __DIR__ . '/_rental-pdf-setup.php';

$companyNombre = pdf_escape($companyInfo['name'] ?? 'Facto Rent a Car');
$addrRaw = trim((string) ($companyInfo['address'] ?? 'San Ramón, Alajuela, Costa Rica'));
$addrParts = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $addrRaw))));
if (count($addrParts) >= 2) {
    $companyLineLegal = pdf_escape($addrParts[0]);
    $companyLineGeo = pdf_escape(implode(' ', array_slice($addrParts, 1)));
} elseif (preg_match('/^([\d\-]+)\s*,\s*(.+)$/u', $addrRaw, $m)) {
    $companyLineLegal = pdf_escape('Facto Autos de Alquiler S.A. | Cédula Jurídica ' . trim($m[1]));
    $companyLineGeo = pdf_escape(trim($m[2]));
} else {
    $companyLineLegal = pdf_escape($addrRaw !== '' ? $addrRaw : 'San Ramón, Alajuela, Costa Rica');
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

$cuentasBancoHtml = '';
if (is_array($accounts) && $accounts !== []) {
    foreach ($accounts as $acc) {
        $b = pdf_escape(trim((string) ($acc['bank'] ?? '')));
        $a = trim((string) ($acc['account'] ?? ''));
        $a = preg_replace('/^IBAN:?\s*/i', '', $a);
        $a = preg_replace('/\s+/', '', $a);
        if ($a !== '') {
            $a = 'IBAN: ' . $a;
        }
        $a = pdf_escape($a);
        if ($b !== '' && $a !== '') {
            $cuentasBancoHtml .= '<div class="d-line"><strong>' . $b . '</strong> ■ ' . $a . '</div>';
        }
    }
}
if ($cuentasBancoHtml === '') {
    $cuentasBancoHtml = '<div class="d-line"><strong>BCR</strong> ■ IBAN: ' . pdf_escape($ibanBcr) . '</div>'
        . '<div class="d-line"><strong>BN</strong> ■ IBAN: ' . pdf_escape($ibanBn) . '</div>';
}

$transmisionTxt = '—';
if ($car && !empty($car->caracteristicas)) {
    $cx = strtolower((string) $car->caracteristicas);
    if (strpos($cx, 'automática') !== false || strpos($cx, 'automatica') !== false || strpos($cx, 'cvt') !== false) {
        $transmisionTxt = 'Transmisión automática';
    } elseif (strpos($cx, 'manual') !== false) {
        $transmisionTxt = 'Transmisión manual';
    }
}
$coberturaTxt = 'Full cobertura';

$fechaEntregaLarga = formatFechaConDiaSemana($model->fecha_inicio);
$horaEntregaTxt = formatHoraSpanish($model->hora_inicio ?? '');
$fechaDevLarga = formatFechaConDiaSemana($model->fecha_final);
$horaDevTxt = formatHoraSpanish($model->hora_final ?? '');

$vehiculoLinea = pdf_escape($car ? ($car->nombre ?? 'N/A') : 'N/A') . ' • ' . $capacidad . ' pasajeros';
$placaLinea = $car && !empty($car->placa) ? pdf_escape($car->placa) : '—';

$resumenTxt = (int) $model->cantidad_dias . ' día' . ((int) $model->cantidad_dias === 1 ? '' : 's')
    . ' • ' . $cantidadVehiculos . ' vehículo' . ($cantidadVehiculos === 1 ? '' : 's');

$subtotalFmt = number_format($subtotalNum, 0, '.', ',');
$ivaFmt = number_format($ivaNum, 0, '.', ',');
$totalFmt = number_format($totalNum, 0, '.', ',');

$ejecutivoTxt = trim((string) ($model->ejecutivo ?? ''));
if ($ejecutivoTxt === '' && !empty($model->ejecutivo_otro)) {
    $ejecutivoTxt = trim((string) $model->ejecutivo_otro);
}
$ejecutivoLinea = pdf_escape($ejecutivoTxt !== '' ? $ejecutivoTxt : 'Ejecutivo de turno.');

$retiroTexto = 'Retiro en sucursal';
if (!empty($model->lugar_retiro)) {
    $retiroTexto = (string) $model->lugar_retiro;
}

/** Teléfono estilo 506 7265 6502 */
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
?>
<style>
    * { font-family: helvetica, sans-serif; }
    body { font-size: 10pt; line-height: 1.4; margin: 0; padding: 0; color: #000000; }
    .d-doc { max-width: 100%; }
    .d-title {
        font-size: 14pt;
        font-weight: bold;
        text-align: center;
        letter-spacing: 1px;
        margin: 0 0 4px;
    }
    .d-no { text-align: center; font-size: 10.5pt; font-weight: bold; margin: 0 0 2px; }
    .d-date { text-align: center; font-size: 10pt; margin: 0 0 14px; }
    .d-center { text-align: center; }
    .d-brand { font-size: 10.5pt; font-weight: bold; margin: 0 0 3px; }
    .d-small { font-size: 9.5pt; margin: 0 0 2px; }
    .d-contact { font-size: 9.5pt; margin: 0 0 16px; }
    .d-sec {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 12px 0 6px;
        padding-bottom: 3px;
        border-bottom: 0.75pt solid #000000;
        letter-spacing: 0.3px;
    }
    .d-block { margin: 0 0 4px; font-size: 10pt; }
    .d-line { margin: 2px 0; font-size: 9.5pt; }
    .d-name { font-size: 10.5pt; font-weight: bold; margin: 2px 0 6px; }
    .d-fecha-ent { font-weight: bold; margin-top: 2px; margin-bottom: 2px; }
    .d-corre-label { margin-top: 6px; margin-bottom: 0; font-weight: bold; }
    .d-corre-val { margin-top: 0; margin-bottom: 0; }
    .d-resumen { font-size: 10pt; margin: 4px 0 10px; }
    .d-sign { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
    .d-sign td { width: 50%; text-align: center; font-size: 9pt; vertical-align: bottom; padding: 22px 8px 4px; }
    .d-sign .d-rule { border-top: 0.5pt solid #000000; margin: 0 8px 4px; height: 1px; }
    .d-totals { width: 100%; max-width: 220px; margin: 10px 0 0; font-size: 10pt; border-collapse: collapse; }
    .d-totals td { padding: 2px 0; }
    .d-totals .r { text-align: right; white-space: nowrap; padding-left: 16px; }
    .d-footer { margin-top: 14px; font-size: 9.5pt; text-align: center; line-height: 1.45; }
</style>

<div class="d-doc">

<div class="d-title">ORDEN DE ALQUILER</div>
<div class="d-no">No. <?= pdf_escape($rentalId) ?></div>
<div class="d-date"><?= pdf_escape($fechaEmisionDoc) ?></div>

<div class="d-center">
    <div class="d-brand"><?= $companyNombre ?></div>
    <div class="d-small"><?= $companyLineLegal ?></div>
    <?php if ($companyLineGeo !== ''): ?>
        <div class="d-small"><?= $companyLineGeo ?></div>
    <?php endif; ?>
    <div class="d-contact">WhatsApp: <?= $whatsappLine ?></div>
</div>

<div class="d-sec">Cuentas bancarias para depósito</div>
<?= $cuentasBancoHtml ?>
<div class="d-line" style="margin-top:6px;"><strong>SINPE Móvil:</strong> <?= pdf_escape($simpeDisplay) ?></div>
<div class="d-line"><strong>Monto de la reservación:</strong> ¢<?= $total ?> — Reservación firme contra depósito.</div>

<div class="d-sec">Cliente</div>
<div class="d-name"><?= $clienteNombre ?></div>
<div class="d-block">Cédula: <?= $clienteCedula ?></div>
<div class="d-block">Teléfono: <?= $telDisplay ?></div>
<?php if (!empty($vencimientoLicencia)): ?>
    <div class="d-block">Licencia vence: <?= pdf_escape($vencimientoLicencia) ?></div>
<?php endif; ?>
<?php if (!empty($vencimientoCedula)): ?>
    <div class="d-block">Cédula vence: <?= pdf_escape($vencimientoCedula) ?></div>
<?php endif; ?>

<div class="d-sec">Entrega</div>
<div class="d-fecha-ent"><?= pdf_escape($fechaEntregaLarga) ?></div>
<div class="d-block"><?= pdf_escape($horaEntregaTxt) ?> • <?= $entregaLugar ?></div>
<?php if (!empty($fechaCorreapartir)): ?>
    <div class="d-corre-label">Corre a partir:</div>
    <div class="d-corre-val"><?= pdf_escape($fechaCorreapartir) ?></div>
<?php endif; ?>

<div class="d-sec">Devolución</div>
<div class="d-fecha-ent"><?= pdf_escape($fechaDevLarga) ?></div>
<div class="d-block"><?= pdf_escape($horaDevTxt) ?> • <?= $lugarRetiro ?></div>
<div class="d-block"><?= pdf_escape($retiroTexto) ?></div>

<div class="d-sec">Vehículo</div>
<div class="d-block"><?= $vehiculoLinea ?></div>
<div class="d-block">Placa: <?= $placaLinea ?></div>
<div class="d-block"><?= pdf_escape($transmisionTxt) ?></div>
<div class="d-block">Cobertura: <?= pdf_escape($coberturaTxt) ?></div>
<div class="d-block">Tarifa diaria: ¢<?= $tarifaDia ?></div>
<?php if ($medioDiaActivo): ?>
    <div class="d-block">Medio día: ¢<?= $tarifaMedioDia ?></div>
<?php endif; ?>

<div class="d-sec">Resumen</div>
<div class="d-resumen"><?= pdf_escape($resumenTxt) ?></div>

<table class="d-sign"><tr>
    <td>
        <div class="d-rule"></div>
        Firma del Cliente
    </td>
    <td>
        <div class="d-rule"></div>
        Firma de Facto Rent a Car
    </td>
</tr></table>

<table class="d-totals">
    <tr><td>Subtotal:</td><td class="r">¢<?= $subtotalFmt ?></td></tr>
    <tr><td>IVA:</td><td class="r">¢<?= $ivaFmt ?></td></tr>
    <tr><td><strong>Total:</strong></td><td class="r"><strong>¢<?= $totalFmt ?></strong></td></tr>
</table>

<div class="d-footer">
    <?= $clienteNombre ?><br>
    Cédula: <?= $clienteCedula ?><br>
    <?= $ejecutivoLinea ?><br>
    <?= pdf_escape($car ? ($car->nombre ?? '') : '') ?>
</div>

</div>
