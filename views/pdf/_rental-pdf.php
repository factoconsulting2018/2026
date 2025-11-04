<?php
/**
 * VISTA PDF DE ORDEN DE ALQUILER - PÁGINA 1
 * 
 * Diseño optimizado para incluir TODO el contenido operativo en una sola página,
 * con layout de dos columnas, tipografías compactas y control de altura estricto.
 * La PÁGINA 2 se genera separadamente con condiciones (sin cambios).
 */

$rentalId = $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
$client = $model->client;
$car = $model->car;

// Calcular valores financieros
$medioDiaEnabled = intval($model->medio_dia_enabled ?? 0);
$medioDiaValor = floatval($model->medio_dia_valor ?? 0);
$medioDiaActivo = ($medioDiaEnabled >= 1) && ($medioDiaValor > 0);
$subtotalDias = $model->cantidad_dias * $model->precio_por_dia;
$totalFinal = $model->total_precio;
if (empty($totalFinal) || $totalFinal == 0) {
    $totalFinal = $model->calculateTotalPrice();
}

// Obtener vencimientos del cliente
$vencimientoLicencia = '';
$vencimientoCedula = '';
if ($client) {
    if (!empty($client->fecha_vencimiento_licencia)) {
        try {
            $fecha = new DateTime($client->fecha_vencimiento_licencia);
            $vencimientoLicencia = $fecha->format('d/m/Y');
        } catch (Exception $e) {}
    }
    if (!empty($client->fecha_vencimiento_cedula)) {
        try {
            $fecha = new DateTime($client->fecha_vencimiento_cedula);
            $vencimientoCedula = $fecha->format('d/m/Y');
        } catch (Exception $e) {}
    }
}

// Formatear fechas
function formatDateCompact($date, $time = '') {
    try {
        $dt = new DateTime($date . ' ' . $time);
    } catch (Exception $e) {
        return $date;
    }
    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $diaSemana = $dias[(int)$dt->format('N')] ?? '';
    $dia = $dt->format('d');
    $mes = strtoupper($meses[(int)$dt->format('n')] ?? '');
    $anio = $dt->format('Y');
    if (!empty($time)) {
        $hora = strtolower($dt->format('h:i a'));
        return "$diaSemana $dia de $mes $hora";
    }
    return "$dia de $mes $anio";
}

// Obtener licencias choferes
$licenciasChoferes = '';
if ($client && !empty($client->licencias_choferes)) {
    $choferesDecoded = json_decode($client->licencias_choferes, true);
    if (is_array($choferesDecoded) && !empty($choferesDecoded)) {
        $licenciasInfo = [];
        foreach ($choferesDecoded as $chofer) {
            if (is_array($chofer)) {
                $choferInfo = [];
                if (isset($chofer['nombre'])) $choferInfo[] = htmlspecialchars($chofer['nombre']);
                if (isset($chofer['licencia'])) $choferInfo[] = htmlspecialchars($chofer['licencia']);
                if (!empty($choferInfo)) {
                    $licenciasInfo[] = implode(' - Lic: ', $choferInfo);
                }
            } else {
                $licenciasInfo[] = htmlspecialchars($chofer);
            }
        }
        $licenciasChoferes = implode(', ', $licenciasInfo);
    } else {
        $licenciasChoferes = htmlspecialchars($client->licencias_choferes);
    }
}

// Variables de datos para el template
$clienteNombre = htmlspecialchars($client ? $client->full_name : 'N/A');
$clienteCedula = htmlspecialchars($client ? $client->cedula_fisica : 'N/A');
$clienteTelefono = htmlspecialchars($client && !empty($client->whatsapp) ? $client->whatsapp : ($client && !empty($client->telefono) ? $client->telefono : 'N/A'));
$entregaLugar = htmlspecialchars($model->lugar_entrega ?: 'San Ramón');
$fechaInicio = formatDateCompact($model->fecha_inicio);
$fechaFin = formatDateCompact($model->fecha_final);
$fechaRetiro = formatDateCompact($model->fecha_inicio, $model->hora_inicio);
$fechaDevolucion = formatDateCompact($model->fecha_final, $model->hora_final);
$lugarRetiro = htmlspecialchars($model->lugar_retiro ?: 'San Ramón');

// Formatear fecha de correapartir si está habilitado
$fechaCorreapartir = '';
if ($model->correapartir_enabled && !empty($model->fecha_correapartir)) {
    try {
        // fecha_correapartir puede venir como "YYYY-MM-DD HH:MM:SS" o "YYYY-MM-DD HH:MM"
        // Separar fecha y hora para formatear correctamente
        $parts = explode(' ', $model->fecha_correapartir);
        $fechaPart = $parts[0] ?? $model->fecha_correapartir;
        $horaPart = $parts[1] ?? '';
        
        // Si tiene hora, formatearla con hora, sino solo fecha
        if (!empty($horaPart)) {
            $fechaCorreapartir = formatDateCompact($fechaPart, $horaPart);
        } else {
            $fechaCorreapartir = formatDateCompact($fechaPart);
        }
    } catch (Exception $e) {
        $fechaCorreapartir = '';
    }
}
$vehiculoDesc = htmlspecialchars($car ? $car->nombre : 'N/A');
$capacidad = htmlspecialchars($car ? ($car->cantidad_pasajeros ?: 5) : '5');
$cantidadDias = str_pad($model->cantidad_dias, 2, '0', STR_PAD_LEFT);
$cantidadVehiculos = 1;
$tarifaDia = number_format($model->precio_por_dia, 0, '.', ',');
$tarifaMedioDia = number_format($medioDiaValor, 0, '.', ',');
$total = number_format($totalFinal, 0, '.', ',');
$subtotalTarifaDia = number_format($subtotalDias, 0, '.', ',');

// Logo
$logoPath = '';
if (!empty($companyInfo['logo'])) {
    $logoPath = Yii::getAlias('@webroot' . str_replace(Yii::getAlias('@web'), '', $companyInfo['logo']));
    if (!file_exists($logoPath)) {
        $logoPath = '';
    } else {
        $logoPath = $companyInfo['logo'];
    }
}

// Bancos
$ibanBcr = 'CR75015201001050506181';
$ibanBn = 'CR49015102020010977051';
$simpe = '83670937';
$montoReserva = $total;
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
                <img src="<?= htmlspecialchars($logoPath) ?>" height="140" />
            <?php endif; ?>
        </td>
    </tr>
</table>

<table class="meta thin">
    <tr>
        <!-- Columna Izquierda -->
        <td style="width:50%; padding-right:8px;">
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
            <div><?= nl2br(htmlspecialchars($model->choferes_autorizados)) ?></div>
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
            <div style="font-weight: bold; padding: 2px 6px; display: inline-block;"><?= htmlspecialchars($car->placa) ?></div>
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