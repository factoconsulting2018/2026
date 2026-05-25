<?php
/**
 * VISTA PDF DE ORDEN DE ALQUILER — Formato General (actual).
 * Página 2: condiciones (controlada desde PdfController / RentalController).
 */
require __DIR__ . '/_rental-pdf-setup.php';
?>
<style>
    * { font-family: helvetica, sans-serif; }
    body { font-size: 10pt; line-height: 1.12; margin: 0; padding: 0; }
    h1, h2, h3 { margin: 0; line-height: 1.1; }
    .title { font-size: 18pt; font-weight: bold; text-align: center; margin: 4px 0 8px; }
    .meta { font-size: 10pt; line-height: 1.12; }
    table { width: 100%; border-spacing: 0; border-collapse: collapse; }
    td { padding: 2px 3px; vertical-align: top; }
    .thin { line-height: 1.12; }
    .bank { border: 0.5pt solid #666; padding: 10px 8px; margin-top: 12px; }
    .sign { border: 0.5pt solid #666; padding: 8px; margin-top: 8px; }
    .r { text-align: right; }
    .b { font-weight: bold; }
    .muted { color: #333; }
    .sep { padding-top: 2px; margin-top: 2px; border-top: 0.5pt solid #aaa; }
</style>

<div class="title">ORDEN DE ALQUILER</div>

<?php if ($model->isReplacement() && $model->parentRental): ?>
<div style="background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:0 0 10px;text-align:center;font-weight:bold;font-size:11pt;">
    ORDEN DE CAMBIO DE VEHÍCULO — Referencia: <?= pdf_escape($model->parentRental->rental_id ?? ('R' . $model->parent_rental_id)) ?>
    <?php if (!empty($model->parentRental->swap_reason)): ?>
    <br><span style="font-weight:normal;">Motivo: <?= pdf_escape($model->parentRental->swap_reason) ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<table class="meta" style="margin-bottom:6px;">
    <tr>
        <td style="width:55%; text-align:left;">
            <div class="b">FACTO RENT A CAR</div>
            <div class="b">FACTO AUTOS DE ALQUILER S.A.</div>
            <div>3-101-880789</div>
            <div>San Ramón, Alajuela, Costa Rica</div>
        </td>
        <td style="width:45%; text-align:right;">
            <?php if (!empty($logoPath)): ?>
                <img src="<?= pdf_escape($logoPath) ?>" height="140" />
            <?php endif; ?>
        </td>
    </tr>
</table>

<table class="meta thin">
    <tr>
        <!-- Columna Izquierda -->
        <td style="width:50%; padding-right:8px;">
            <div class="b" style="margin-bottom:2px;"><span style="background-color: #FF6600; color: #FFFFFF; padding: 4px 8px; display: inline-block; border-radius: 3px; font-weight: bold;">Orden de Alquiler: <?= pdf_escape($rentalId) ?></span></div>
            <div class="b">Cliente</div>
            <div style="background-color: #0066CC; color: #FFFFFF; padding: 4px 8px; display: inline-block; border-radius: 3px;"><?= $clienteNombre ?></div>
            <?php if (!empty($vencimientoLicencia) || !empty($vencimientoCedula)): ?>
            <div class="b">Fechas de vencimiento:</div>
            <?php if (!empty($vencimientoCedula)): ?>
            <div>Cédula: <?= $vencimientoCedula ?></div>
            <?php endif; ?>
            <?php if (!empty($vencimientoLicencia)): ?>
            <div>Licencia de conducir: <?= $vencimientoLicencia ?></div>
            <?php endif; ?>
            <?php endif; ?>
            <div class="sep"></div>
            <div class="b">Cédula / Teléfono</div>
            <div><?= $clienteCedula ?> • <?= $clienteTelefono ?></div>
            <div class="sep"></div>
            <div class="b">Entrega del vehículo</div>
            <div><?= $entregaLugar ?></div>
            <div class="b">Fechas</div>
            <?php if (!empty($fechaCorreapartir)): ?>
            <div class="b" style="color: #FF6600; font-weight: bold;">Correapartir: <?= $fechaCorreapartir ?></div>
            <?php endif; ?>
            <div>Alquiler: <?= $fechaInicio ?></div>
            <div><?= $fechaFin ?></div>
            <div>Retiro: <?= $fechaRetiro ?> • <?= $lugarRetiro ?></div>
            <?php if (!empty($model->choferes_autorizados)): ?>
            <div class="sep"></div>
            <div class="b">Choferes Autorizados</div>
            <div><?= nl2br(pdf_escape($model->choferes_autorizados ?? '')) ?></div>
            <?php endif; ?>
        </td>
        
        <!-- Columna Derecha -->
        <td style="width:50%; padding-left:8px;">
            <div class="b">Devolución</div>
            <div><?= $fechaDevolucion ?> • <?= $lugarRetiro ?></div>
            <div class="sep"></div>
            <?php if (!empty($licenciasChoferes)): ?>
            <div class="b">Licencias / Choferes</div>
            <div><?= $licenciasChoferes ?></div>
            <div class="sep"></div>
            <?php endif; ?>
            <div class="b">Vehículo</div>
            <div><?= $vehiculoDesc ?> • <?= $capacidad ?> pasajeros</div>
            <?php if (!empty($car->placa)): ?>
            <div class="b">Placa</div>
            <div style="font-weight: bold; padding: 2px 6px; display: inline-block;"><?= pdf_escape($car->placa) ?></div>
            <?php endif; ?>
            <div class="sep"></div>
            <div class="b">Cantidades</div>
            <div>Días: <?= $cantidadDias ?> • Vehículos: <?= $cantidadVehiculos ?></div>
            <div class="sep"></div>
            <table style="width:100%;">
                <tr>
                    <td class="b">Tarifa día (<?= $model->cantidad_dias ?> x ¢<?= $tarifaDia ?>)</td>
                    <td class="r">¢<?= $subtotalTarifaDia ?></td>
                </tr>
                <?php if ($medioDiaActivo): ?>
                <tr>
                    <td class="b">1/2 día</td>
                    <td class="r">¢<?= $tarifaMedioDia ?></td>
                </tr>
                <?php endif; ?>
                <tr style="border-top: 2pt solid #000;">
                    <td class="b">Total</td>
                    <td class="r b">¢<?= $total ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="bank">
    <div class="thin">
        <span class="b">Cuentas:</span>
        BCR¢ IBAN <?= $ibanBcr ?> | BN¢ IBAN <?= $ibanBn ?>
    </div>
    <div class="thin"><span class="b">Monto de la reservación:</span> ¢<?= $montoReserva ?> — Reservación firme contra depósito.</div>
</div>

<div class="sign">
    <div class="b" style="margin-bottom:24px;">Firmas / Observaciones</div>
    <table style="width:100%;">
        <tr>
            <td style="width:50%; text-align:center;">
                <div style="border-top:0.6pt solid #000; margin:0 24px 4px;"></div>
                Cliente
            </td>
            <td style="width:50%; text-align:center;">
                <div style="border-top:0.6pt solid #000; margin:0 24px 4px;"></div>
                Empresa
            </td>
        </tr>
    </table>
    <div style="margin-top:12px; font-size:14pt; text-align:center;">SIMPEMÓVIL: 8367-0937</div>
</div>