<?php
/**
 * Orden de alquiler — Formato Moderna (referencia ordendereferencia.pdf).
 */
require __DIR__ . '/_rental-pdf-setup.php';

$companyNombre = pdf_escape($companyInfo['name'] ?? 'Facto Rent a Car');
$companyAddress = pdf_escape($companyInfo['address'] ?? 'San Ramón, Alajuela, Costa Rica');

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
        $a = pdf_escape(preg_replace('/^IBAN:?\s*/i', 'IBAN: ', $a));
        if ($b !== '' && $a !== '') {
            $cuentasBancoHtml .= '<div class="m-bank-line"><strong>' . $b . '</strong> ■ ' . $a . '</div>';
        }
    }
}
if ($cuentasBancoHtml === '') {
    $cuentasBancoHtml = '<div class="m-bank-line"><strong>BCR</strong> ■ IBAN: ' . pdf_escape($ibanBcr) . '</div>'
        . '<div class="m-bank-line"><strong>BN</strong> ■ IBAN: ' . pdf_escape($ibanBn) . '</div>';
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
?>
<style>
    * { font-family: helvetica, sans-serif; }
    body { font-size: 10pt; line-height: 1.35; margin: 0; padding: 0; color: #111; }
    .m-title { font-size: 16pt; font-weight: bold; text-align: center; letter-spacing: 0.5px; margin: 0 0 4px; }
    .m-no { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 2px; }
    .m-date-doc { text-align: center; font-size: 10pt; margin-bottom: 10px; }
    .m-brand { text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 2px; }
    .m-sub { text-align: center; font-size: 9.5pt; margin-bottom: 2px; }
    .m-contact { text-align: center; font-size: 9.5pt; margin-bottom: 14px; }
    .m-h { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin: 12px 0 6px; border-bottom: 1pt solid #333; padding-bottom: 2px; }
    .m-bank-line { font-size: 9.5pt; margin: 2px 0; }
    .m-note { font-size: 9.5pt; margin-top: 8px; }
    .m-block { margin-bottom: 8px; }
    .m-row { margin: 3px 0; font-size: 10pt; }
    .m-label { font-weight: bold; display: inline-block; min-width: 120px; }
    .m-entrega-fecha { font-weight: bold; margin-top: 4px; }
    .m-hora-lugar { margin-left: 0; }
    .m-corre { margin-top: 4px; font-size: 9.5pt; }
    .m-resumen { margin-top: 10px; font-size: 10pt; }
    .m-firma-row { width: 100%; margin-top: 16px; }
    .m-firma-row td { width: 50%; text-align: center; font-size: 9pt; vertical-align: bottom; padding-top: 28px; }
    .m-line { border-top: 0.6pt solid #000; margin: 0 12px 4px; }
    .m-totals { margin-top: 12px; font-size: 10pt; }
    .m-totals table { width: 100%; max-width: 280px; margin-left: auto; }
    .m-totals td { padding: 2px 0; }
    .m-totals .r { text-align: right; }
    .m-footer-note { margin-top: 14px; font-size: 9.5pt; text-align: center; }
</style>

<div class="m-title">ORDEN DE ALQUILER</div>
<div class="m-no">No. <?= pdf_escape($rentalId) ?></div>
<div class="m-date-doc"><?= pdf_escape($fechaEmisionDoc) ?></div>

<div class="m-brand"><?= $companyNombre ?></div>
<div class="m-sub"><?= $companyAddress ?></div>
<div class="m-contact">WhatsApp: <?= $whatsappLine ?></div>

<div class="m-h">Cuentas bancarias para depósito</div>
<div class="m-block"><?= $cuentasBancoHtml ?></div>
<div class="m-note"><strong>SINPE Móvil:</strong> <?= pdf_escape($simpeDisplay) ?></div>
<div class="m-note"><strong>Monto de la reservación:</strong> ¢<?= $total ?> — Reservación firme contra depósito.</div>

<div class="m-h">Cliente</div>
<div class="m-block">
    <div class="m-row"><strong><?= $clienteNombre ?></strong></div>
    <div class="m-row">Cédula: <?= $clienteCedula ?></div>
    <div class="m-row">Teléfono: <?= $clienteTelefono ?></div>
    <?php if (!empty($vencimientoLicencia)): ?>
        <div class="m-row">Licencia vence: <?= pdf_escape($vencimientoLicencia) ?></div>
    <?php endif; ?>
    <?php if (!empty($vencimientoCedula)): ?>
        <div class="m-row">Cédula vence: <?= pdf_escape($vencimientoCedula) ?></div>
    <?php endif; ?>
</div>

<div class="m-h">Entrega</div>
<div class="m-block">
    <div class="m-entrega-fecha"><?= pdf_escape($fechaEntregaLarga) ?></div>
    <div class="m-hora-lugar"><?= pdf_escape($horaEntregaTxt) ?> • <?= $entregaLugar ?></div>
    <?php if (!empty($fechaCorreapartir)): ?>
        <div class="m-corre"><strong>Corre a partir:</strong><br><?= pdf_escape($fechaCorreapartir) ?></div>
    <?php endif; ?>
</div>

<div class="m-h">Devolución</div>
<div class="m-block">
    <div class="m-entrega-fecha"><?= pdf_escape($fechaDevLarga) ?></div>
    <div class="m-hora-lugar"><?= pdf_escape($horaDevTxt) ?> • <?= $lugarRetiro ?></div>
    <div class="m-row"><?= pdf_escape($retiroTexto) ?></div>
</div>

<div class="m-h">Vehículo</div>
<div class="m-block">
    <div class="m-row"><?= $vehiculoLinea ?></div>
    <div class="m-row">Placa: <?= $placaLinea ?></div>
    <div class="m-row"><?= pdf_escape($transmisionTxt) ?></div>
    <div class="m-row">Cobertura: <?= pdf_escape($coberturaTxt) ?></div>
    <div class="m-row">Tarifa diaria: ¢<?= $tarifaDia ?></div>
    <?php if ($medioDiaActivo): ?>
        <div class="m-row">Medio día: ¢<?= $tarifaMedioDia ?></div>
    <?php endif; ?>
</div>

<div class="m-h">Resumen</div>
<div class="m-resumen"><?= pdf_escape($resumenTxt) ?></div>

<table class="m-firma-row"><tr>
    <td><div class="m-line"></div>Firma del Cliente</td>
    <td><div class="m-line"></div>Firma de Facto Rent a Car</td>
</tr></table>

<div class="m-totals">
    <table>
        <tr><td>Subtotal:</td><td class="r">¢<?= $subtotalFmt ?></td></tr>
        <tr><td>IVA:</td><td class="r">¢<?= $ivaFmt ?></td></tr>
        <tr><td><strong>Total:</strong></td><td class="r"><strong>¢<?= $totalFmt ?></strong></td></tr>
    </table>
</div>

<div class="m-footer-note">
    <?= $clienteNombre ?><br>
    Cédula: <?= $clienteCedula ?><br>
    <?= $ejecutivoLinea ?><br>
    <?= pdf_escape($car ? ($car->nombre ?? '') : '') ?>
</div>
