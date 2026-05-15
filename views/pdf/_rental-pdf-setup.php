<?php
/**
 * Variables compartidas para _rental-pdf.php y _rental-pdf-modern.php.
 * Requiere: $model (Rental), $companyInfo (array)
 */

if (!function_exists('pdf_escape')) {
    function pdf_escape($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$rentalId = $model->rental_id ?: ('R' . str_pad($model->id, 6, '0', STR_PAD_LEFT));
$client = $model->client;
$car = $model->car;

$medioDiaEnabled = (int) ($model->medio_dia_enabled ?? 0);
$medioDiaValor = (float) ($model->medio_dia_valor ?? 0);
$medioDiaActivo = ($medioDiaEnabled >= 1) && ($medioDiaValor > 0);
$subtotalDias = $model->cantidad_dias * $model->precio_por_dia;
$totalFinal = $model->total_precio;
if (empty($totalFinal) || $totalFinal == 0) {
    $totalFinal = $model->calculateTotalPrice();
}

$vencimientoLicencia = '';
$vencimientoCedula = '';
if ($client) {
    if (!empty($client->fecha_vencimiento_licencia)) {
        try {
            $fecha = new DateTime($client->fecha_vencimiento_licencia);
            $vencimientoLicencia = $fecha->format('d/m/Y');
        } catch (Exception $e) {
        }
    }
    if (!empty($client->fecha_vencimiento_cedula)) {
        try {
            $fecha = new DateTime($client->fecha_vencimiento_cedula);
            $vencimientoCedula = $fecha->format('d/m/Y');
        } catch (Exception $e) {
        }
    }
}

if (!function_exists('formatDateCompact')) {
    function formatDateCompact($date, $time = '')
    {
        try {
            $dt = new DateTime($date . ' ' . $time);
        } catch (Exception $e) {
            return $date;
        }
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $diaSemana = $dias[(int) $dt->format('N')] ?? '';
        $dia = $dt->format('d');
        $mes = strtoupper($meses[(int) $dt->format('n')] ?? '');
        $anio = $dt->format('Y');
        if (!empty($time)) {
            $hora = strtolower($dt->format('h:i a'));
            $hora = str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $hora);

            return "$diaSemana $dia de $mes $hora";
        }

        return "$dia de $mes $anio";
    }
}

if (!function_exists('formatFechaConDiaSemana')) {
    /** Ej.: Miércoles 20 de mayo de 2026 */
    function formatFechaConDiaSemana($date)
    {
        try {
            $dt = new DateTime($date);
        } catch (Exception $e) {
            return $date;
        }
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $diaSemana = $dias[(int) $dt->format('N')] ?? '';
        $dia = (int) $dt->format('j');
        $mes = $meses[(int) $dt->format('n')] ?? '';
        $anio = $dt->format('Y');

        return "$diaSemana $dia de $mes de $anio";
    }
}

if (!function_exists('formatHoraSpanish')) {
    /** Ej.: 08:00 a.m. */
    function formatHoraSpanish($hhmm)
    {
        if ($hhmm === null || $hhmm === '') {
            return '';
        }
        $hhmm = substr((string) $hhmm, 0, 5);
        $dt = DateTime::createFromFormat('H:i', $hhmm);
        if ($dt === false) {
            return htmlspecialchars($hhmm, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        $s = strtolower($dt->format('g:i a'));

        return str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $s);
    }
}

$licenciasChoferes = '';
if ($client && !empty($client->licencias_choferes)) {
    $choferesDecoded = json_decode($client->licencias_choferes, true);
    if (is_array($choferesDecoded) && !empty($choferesDecoded)) {
        $licenciasInfo = [];
        foreach ($choferesDecoded as $chofer) {
            if (is_array($chofer)) {
                $choferInfo = [];
                if (isset($chofer['nombre'])) {
                    $choferInfo[] = pdf_escape($chofer['nombre'] ?? '');
                }
                if (isset($chofer['licencia'])) {
                    $choferInfo[] = pdf_escape($chofer['licencia'] ?? '');
                }
                if (!empty($choferInfo)) {
                    $licenciasInfo[] = implode(' - Lic: ', $choferInfo);
                }
            } else {
                $licenciasInfo[] = pdf_escape($chofer);
            }
        }
        $licenciasChoferes = implode(', ', $licenciasInfo);
    } else {
        $licenciasChoferes = pdf_escape($client->licencias_choferes);
    }
}

$clienteNombre = pdf_escape($client ? ($client->full_name ?? 'N/A') : 'N/A');
$clienteCedula = pdf_escape($client ? ($client->cedula_fisica ?? 'N/A') : 'N/A');
$clienteTelefono = pdf_escape($client && !empty($client->whatsapp) ? $client->whatsapp : ($client && !empty($client->telefono) ? $client->telefono : 'N/A'));
$entregaLugar = pdf_escape($model->lugar_entrega ?: 'Base 1');
$fechaInicio = formatDateCompact($model->fecha_inicio);
$fechaFin = formatDateCompact($model->fecha_final);
$fechaRetiro = formatDateCompact($model->fecha_inicio, $model->hora_inicio);
$fechaDevolucion = formatDateCompact($model->fecha_final, $model->hora_final);
$lugarRetiro = pdf_escape($model->lugar_retiro ?: 'Base 1');

if (!function_exists('formatDateCorreapartir')) {
    function formatDateCorreapartir($date, $time = '')
    {
        try {
            $dt = new DateTime($date . ' ' . $time);
        } catch (Exception $e) {
            return $date;
        }
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $diaSemana = $dias[(int) $dt->format('N')] ?? '';
        $dia = $dt->format('d');
        $mes = ucfirst($meses[(int) $dt->format('n')] ?? '');
        if (!empty($time)) {
            $hora = strtolower($dt->format('h:i a'));
            $hora = str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $hora);

            return "$diaSemana $dia de $mes $hora";
        }

        return "$dia de $mes " . $dt->format('Y');
    }
}

$fechaCorreapartir = '';
if ($model->correapartir_enabled && !empty($model->fecha_correapartir)) {
    try {
        $parts = explode(' ', $model->fecha_correapartir);
        $fechaPart = $parts[0] ?? $model->fecha_correapartir;
        $horaPart = $parts[1] ?? '';
        if (!empty($horaPart)) {
            $fechaCorreapartir = formatDateCorreapartir($fechaPart, $horaPart);
        } else {
            $fechaCorreapartir = formatDateCorreapartir($fechaPart);
        }
    } catch (Exception $e) {
        $fechaCorreapartir = '';
    }
}

$vehiculoDesc = pdf_escape($car ? ($car->nombre ?? 'N/A') : 'N/A');
$capacidad = pdf_escape($car ? ($car->cantidad_pasajeros ?: 5) : '5');
$cantidadDias = str_pad((string) $model->cantidad_dias, 2, '0', STR_PAD_LEFT);
$cantidadVehiculos = 1;
$tarifaDia = number_format((float) $model->precio_por_dia, 0, '.', ',');
$tarifaMedioDia = number_format($medioDiaValor, 0, '.', ',');
$total = number_format((float) $totalFinal, 0, '.', ',');
$subtotalTarifaDia = number_format((float) $subtotalDias, 0, '.', ',');

$logoPath = '';
if (!empty($companyInfo['logo'])) {
    $logoPath = Yii::getAlias('@webroot' . str_replace(Yii::getAlias('@web'), '', $companyInfo['logo']));
    if (!file_exists($logoPath)) {
        $logoPath = '';
    } else {
        $logoPath = $companyInfo['logo'];
    }
}

$ibanBcr = 'CR75015201001050506181';
$ibanBn = 'CR49015102020010977051';
$accounts = $companyInfo['bank_accounts'] ?? [];
if (is_array($accounts)) {
    foreach ($accounts as $acc) {
        $bank = strtoupper((string) ($acc['bank'] ?? ''));
        $acct = (string) ($acc['account'] ?? '');
        if ($bank === 'BCR' && stripos($acct, 'CR') !== false) {
            $ibanBcr = preg_replace('/\s+/', '', preg_replace('/^IBAN:?\s*/i', '', $acct)) ?: $ibanBcr;
        }
        if (($bank === 'BN' || $bank === 'BANCO NACIONAL') && stripos($acct, 'CR') !== false) {
            $ibanBn = preg_replace('/\s+/', '', preg_replace('/^IBAN:?\s*/i', '', $acct)) ?: $ibanBn;
        }
    }
}

$simpeRaw = preg_replace('/\D/', '', (string) ($companyInfo['simemovil'] ?? '83670937'));
$simpe = $simpeRaw !== '' ? $simpeRaw : '83670937';
$montoReserva = $total;

$subtotalNum = (float) $subtotalDias + ($medioDiaActivo ? $medioDiaValor : 0);
$ivaNum = 0.0;
$totalNum = (float) $totalFinal;
