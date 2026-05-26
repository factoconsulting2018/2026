<?php

namespace app\components;

use Yii;
use app\models\CompanyConfig;
use app\models\Rental;
use app\controllers\PdfController;

/**
 * Cliente para la API WhatsApp Multi-Session (descargapro.com / Baileys).
 *
 * Documentacion de referencia: descargapro_API_Rutas.pdf v5.0.0 (rutas actualizadas).
 *
 * Endpoints utilizados:
 *  - POST   /session/crear/{id}
 *  - GET    /session/{id}/status        => { status: "connected"|"connecting"|"not_found", qr: bool }
 *  - GET    /session/{id}/qr            => { qr: "data:image/png;base64,..." }
 *  - GET    /session/{id}/qr-image      => PNG directo (no usado aqui; usamos JSON)
 *  - DELETE /session/{id}
 *  - GET    /sessions
 *  - POST   /session/{id}/send          { number, message }
 *  - POST   /session/{id}/send-image    { number, url, caption? }
 *  - POST   /session/{id}/send-document { number, url, filename? }
 */
class WhatsAppNotifier
{
    /** Timeout corto para llamadas interactivas (status/qr/start). */
    const TIMEOUT_INTERACTIVE = 12;

    /** Timeout amplio para envios (texto/documento). */
    const TIMEOUT_SEND = 25;

    /** Carpeta publica donde se copian PDFs temporales para que la API los descargue. */
    const OUTBOX_DIR = 'uploads/whatsapp_outbox/';

    /** Horas de retencion de archivos en la outbox antes de purgarlos. */
    const OUTBOX_RETENTION_HOURS = 48;

    /**
     * Realiza una llamada HTTP a la API y devuelve la respuesta decodificada.
     *
     * @return array{ok:bool, status:int, body:array<string,mixed>|null, error:string|null}
     */
    public static function request(
        string $method,
        string $url,
        ?array $payload = null,
        int $timeout = self::TIMEOUT_INTERACTIVE
    ): array {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'status' => 0, 'body' => null, 'error' => 'cURL no esta disponible en este servidor.'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(8, $timeout));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $headers = ['Accept: application/json'];
        if ($payload !== null) {
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            $errMsg = $err ?: ('cURL error #' . $errno);
            try {
                Yii::warning('WhatsApp API ' . $method . ' ' . $url . ' fallo: ' . $errMsg, 'whatsapp');
            } catch (\Throwable $e) {
                // ignore
            }
            return ['ok' => false, 'status' => $status, 'body' => null, 'error' => $errMsg ?: 'Sin respuesta del servidor.'];
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $decoded = ['raw' => substr((string) $raw, 0, 500)];
        }

        $apiFailed = false;
        if (is_array($decoded)) {
            $bodyStatus = isset($decoded['status']) ? strtolower((string) $decoded['status']) : '';
            $apiFailed = in_array($bodyStatus, ['error', 'failed', 'fail'], true)
                || (array_key_exists('success', $decoded) && $decoded['success'] === false)
                || (array_key_exists('ok', $decoded) && $decoded['ok'] === false);
        }

        $ok = $status >= 200 && $status < 300 && !$apiFailed;
        if (!$ok) {
            try {
                Yii::warning('WhatsApp API ' . $method . ' ' . $url . ' HTTP ' . $status . ': ' . substr((string) $raw, 0, 300), 'whatsapp');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return [
            'ok' => $ok,
            'status' => $status,
            'body' => $decoded,
            'error' => $ok ? null : ($decoded['error'] ?? $decoded['message'] ?? ('HTTP ' . $status)),
        ];
    }

    public static function getStatus(string $apiUrl, string $sessionId): array
    {
        return self::request('GET', rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId) . '/status');
    }

    public static function getQr(string $apiUrl, string $sessionId): array
    {
        return self::request('GET', rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId) . '/qr');
    }

    public static function startSession(string $apiUrl, string $sessionId): array
    {
        return self::request('POST', rtrim($apiUrl, '/') . '/session/crear/' . rawurlencode($sessionId));
    }

    public static function deleteSession(string $apiUrl, string $sessionId): array
    {
        return self::request('DELETE', rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId));
    }

    public static function sendText(string $apiUrl, string $sessionId, string $number, string $message): array
    {
        $res = self::request(
            'POST',
            rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId) . '/send',
            ['number' => $number, 'message' => $message],
            self::TIMEOUT_SEND
        );
        if ($res['ok']) {
            return $res;
        }

        // Compatibilidad con la documentacion v5: POST /send/text + sessionId en body.
        return self::request(
            'POST',
            rtrim($apiUrl, '/') . '/send/text',
            ['sessionId' => $sessionId, 'number' => $number, 'message' => $message],
            self::TIMEOUT_SEND
        );
    }

    public static function sendImage(
        string $apiUrl,
        string $sessionId,
        string $number,
        string $publicUrl,
        string $caption = ''
    ): array {
        $payload = ['number' => $number, 'url' => $publicUrl];
        if ($caption !== '') {
            $payload['caption'] = $caption;
        }
        $res = self::request(
            'POST',
            rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId) . '/send-image',
            $payload,
            self::TIMEOUT_SEND
        );
        if ($res['ok']) {
            return $res;
        }

        // Compatibilidad con la documentacion v5: POST /send/image + sessionId en body.
        $payload['sessionId'] = $sessionId;
        return self::request(
            'POST',
            rtrim($apiUrl, '/') . '/send/image',
            $payload,
            self::TIMEOUT_SEND
        );
    }

    public static function sendDocument(
        string $apiUrl,
        string $sessionId,
        string $number,
        string $publicUrl,
        string $filename,
        string $mimetype = 'application/pdf'
    ): array {
        $payload = [
            'number' => $number,
            'url' => $publicUrl,
            'filename' => $filename,
        ];

        $res = self::request(
            'POST',
            rtrim($apiUrl, '/') . '/session/' . rawurlencode($sessionId) . '/send-document',
            $payload,
            self::TIMEOUT_SEND
        );
        if ($res['ok']) {
            return $res;
        }

        // Compatibilidad con la documentacion v5: POST /send/document + sessionId en body.
        $payload['sessionId'] = $sessionId;
        $payload['mimetype'] = $mimetype;
        return self::request(
            'POST',
            rtrim($apiUrl, '/') . '/send/document',
            $payload,
            self::TIMEOUT_SEND
        );
    }

    /**
     * Indica si una respuesta de /status corresponde a "conectado".
     *
     * @param array{ok:bool, body:array<string,mixed>|null, status:int, error:string|null} $statusResponse
     */
    public static function isConnected(array $statusResponse): bool
    {
        if (empty($statusResponse['ok'])) {
            return false;
        }
        $body = $statusResponse['body'] ?? [];
        return is_array($body) && isset($body['status']) && $body['status'] === 'connected';
    }

    /**
     * Convierte un telefono al formato E.164 simple esperado por la API: solo digitos, con codigo de pais.
     *
     * @return string|null Numero normalizado o null si no se puede normalizar.
     */
    public static function normalizeNumber(?string $raw, string $defaultCountry = '506'): ?string
    {
        if ($raw === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '' || $digits === null) {
            return null;
        }

        if (strpos($raw, '+') === 0) {
            return $digits;
        }

        $defaultCountry = preg_replace('/\D+/', '', $defaultCountry) ?: '506';

        // Si ya empieza con el codigo de pais y tiene una longitud razonable, dejarlo.
        if ($defaultCountry !== '' && strpos($digits, $defaultCountry) === 0 && strlen($digits) >= strlen($defaultCountry) + 7) {
            return $digits;
        }

        // Costa Rica: numeros locales de 8 digitos -> prepender 506.
        if ($defaultCountry === '506' && strlen($digits) === 8) {
            return '506' . $digits;
        }

        // Caso general: si parece local, prependiar pais.
        if (strlen($digits) >= 7 && strlen($digits) <= 10) {
            return $defaultCountry . $digits;
        }

        return $digits;
    }

    /**
     * Lista normalizada de telefonos administradores configurados.
     *
     * @return string[]
     */
    public static function getAdminNumbers(?array $config = null): array
    {
        $config = $config ?: CompanyConfig::getWhatsAppConfig();
        $out = [];
        foreach ($config['admin_phones'] as $raw) {
            $n = self::normalizeNumber($raw, $config['country_code']);
            if ($n !== null && $n !== '') {
                $out[] = $n;
            }
        }
        return array_values(array_unique($out));
    }

    /**
     * Copia un PDF de runtime/ a la carpeta publica de outbox y devuelve la URL accesible publicamente.
     *
     * @return array{public_url:string, filename:string}|null
     */
    public static function publishPdfFromRuntime(string $runtimeFilename, ?string $publicBaseUrl = null): ?array
    {
        $runtimePath = Yii::getAlias('@app') . '/runtime/' . $runtimeFilename;
        if (!is_file($runtimePath)) {
            return null;
        }

        $outboxRel = self::OUTBOX_DIR;
        $outboxAbs = Yii::getAlias('@webroot/' . $outboxRel);
        if (!is_dir($outboxAbs)) {
            @mkdir($outboxAbs, 0775, true);
        }

        self::purgeOutbox($outboxAbs);

        $base = pathinfo($runtimeFilename, PATHINFO_FILENAME);
        $ext = pathinfo($runtimeFilename, PATHINFO_EXTENSION) ?: 'pdf';
        $token = bin2hex(random_bytes(8));
        $publicName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $base) . '_' . $token . '.' . $ext;
        $publicPath = $outboxAbs . $publicName;

        if (!@copy($runtimePath, $publicPath)) {
            return null;
        }

        $publicBaseUrl = $publicBaseUrl !== null ? trim($publicBaseUrl) : '';
        if ($publicBaseUrl === '') {
            $publicBaseUrl = self::detectPublicBaseUrl();
        }
        $publicBaseUrl = rtrim($publicBaseUrl, '/');

        $relUrl = '/' . ltrim($outboxRel, '/') . $publicName;
        $url = $publicBaseUrl !== '' ? $publicBaseUrl . $relUrl : $relUrl;

        return ['public_url' => $url, 'filename' => $publicName];
    }

    /**
     * Devuelve una URL pública (accesible desde Internet) para la imagen del vehículo.
     * - Si el campo Car::imagen es una URL externa (http/https), la devuelve tal cual.
     * - Si es una ruta local (uploads/cars/...), la copia al outbox público con un token
     *   y devuelve la URL absoluta (igual que el PDF de la orden).
     *
     * @return array{public_url:string, filename:string}|null
     */
    public static function publishCarImage(?\app\models\Car $car, ?string $publicBaseUrl = null): ?array
    {
        if ($car === null) {
            return null;
        }
        $raw = trim((string) ($car->imagen ?? ''));
        if ($raw === '') {
            return null;
        }

        // Si ya es una URL pública (legacy), devolvérsela directamente.
        if (preg_match('#^https?://#i', $raw)) {
            return [
                'public_url' => $raw,
                'filename' => basename(parse_url($raw, PHP_URL_PATH) ?: 'foto.jpg'),
            ];
        }

        // Ruta local: copiar al outbox público con token (igual estrategia que el PDF).
        $rel = ltrim(str_replace('\\', '/', $raw), '/');
        $src = Yii::getAlias('@webroot/' . $rel);
        if (!is_file($src)) {
            return null;
        }

        $outboxRel = self::OUTBOX_DIR;
        $outboxAbs = Yii::getAlias('@webroot/' . $outboxRel);
        if (!is_dir($outboxAbs)) {
            @mkdir($outboxAbs, 0775, true);
        }

        $base = pathinfo($rel, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION) ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'jpg';
        }
        $token = bin2hex(random_bytes(8));
        $publicName = 'car_' . preg_replace('/[^A-Za-z0-9_\-]+/', '_', $base) . '_' . $token . '.' . $ext;
        $publicPath = $outboxAbs . $publicName;

        if (!@copy($src, $publicPath)) {
            return null;
        }

        $publicBaseUrl = $publicBaseUrl !== null ? trim($publicBaseUrl) : '';
        if ($publicBaseUrl === '') {
            $publicBaseUrl = self::detectPublicBaseUrl();
        }
        $publicBaseUrl = rtrim($publicBaseUrl, '/');

        $relUrl = '/' . ltrim($outboxRel, '/') . $publicName;
        $url = $publicBaseUrl !== '' ? $publicBaseUrl . $relUrl : $relUrl;

        return ['public_url' => $url, 'filename' => $publicName];
    }

    private static function detectPublicBaseUrl(): string
    {
        try {
            $hostInfo = Yii::$app->request->hostInfo;
            if (is_string($hostInfo) && $hostInfo !== '') {
                // Forzar https si la peticion entrante lo es o si parece estar tras un proxy.
                $isSecure = Yii::$app->request->isSecureConnection
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && stripos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false);
                if ($isSecure && strpos($hostInfo, 'http://') === 0) {
                    $hostInfo = 'https://' . substr($hostInfo, 7);
                }
                return $hostInfo;
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return '';
    }

    private static function purgeOutbox(string $absDir): void
    {
        if (!is_dir($absDir)) {
            return;
        }
        $cutoff = time() - (self::OUTBOX_RETENTION_HOURS * 3600);
        foreach (glob($absDir . '*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /**
     * Envia notificacion de orden de alquiler creada a todos los telefonos administradores.
     *
     * Es defensivo: nunca debe romper el flujo principal de creacion. Cualquier excepcion
     * se loguea y se devuelve un reporte estructurado.
     *
     * @return array{enabled:bool, attempted:int, sent:int, errors:array<string>, skipped_reason:?string}
     */
    public static function notifyRentalCreated(Rental $rental): array
    {
        $report = [
            'enabled' => false,
            'attempted' => 0,
            'sent' => 0,
            'errors' => [],
            'skipped_reason' => null,
        ];

        try {
            $cfg = CompanyConfig::getWhatsAppConfig();
            Yii::info(
                'notifyRentalCreated start; rental_id=' . ($rental->id ?? '?')
                . ' enabled=' . (int) $cfg['enabled']
                . ' notify_on_create=' . (int) $cfg['notify_on_create']
                . ' phones=' . count(array_filter($cfg['admin_phones'])),
                'whatsapp'
            );

            if (!$cfg['enabled']) {
                $report['skipped_reason'] = 'Integración WhatsApp desactivada en configuración.';
                return $report;
            }
            if (!$cfg['notify_on_create']) {
                $report['skipped_reason'] = 'Aviso automático al crear orden desactivado.';
                return $report;
            }
            $report['enabled'] = true;

            $numbers = self::getAdminNumbers($cfg);
            if (empty($numbers)) {
                $msg = 'No hay teléfonos administradores configurados.';
                Yii::warning($msg, 'whatsapp');
                $report['errors'][] = $msg;
                $report['skipped_reason'] = $msg;
                return $report;
            }

            // Verificar que la sesion este conectada antes de gastar timeouts en envios.
            $status = self::getStatus($cfg['api_url'], $cfg['session_id']);
            if (!self::isConnected($status)) {
                $bodyStatus = is_array($status['body'] ?? null) ? ($status['body']['status'] ?? 'unknown') : 'unknown';
                $msg = $status['error'] ?? ('Sesión WhatsApp no conectada (estado: ' . $bodyStatus . ')');
                Yii::warning('WhatsApp omitido (sesión no conectada): ' . $msg, 'whatsapp');
                $report['errors'][] = $msg;
                $report['skipped_reason'] = $msg;
                return $report;
            }

            $message = self::buildRentalMessage($rental);
            Yii::info('WhatsApp message preparado (' . strlen($message) . ' chars) para ' . count($numbers) . ' destinatario(s)', 'whatsapp');

            // Publicar foto del vehículo (se enviará como primer envío con el mensaje como caption).
            $publicImage = self::publishCarImage($rental->car ?? null, $cfg['public_base_url'] ?: null);
            if ($publicImage !== null) {
                Yii::info('Foto del vehículo publicada: ' . $publicImage['public_url'], 'whatsapp');
            }

            // Publicar PDF de la orden si existe.
            $pdfFilename = PdfController::rentalOrderPdfFilename($rental);
            $publicPdf = self::publishPdfFromRuntime($pdfFilename, $cfg['public_base_url'] ?: null);
            if ($publicPdf === null) {
                Yii::warning('PDF no encontrado o no se pudo publicar para enviar por WhatsApp: ' . $pdfFilename, 'whatsapp');
            } else {
                Yii::info('PDF publicado en outbox: ' . $publicPdf['public_url'], 'whatsapp');
            }

            foreach ($numbers as $number) {
                $report['attempted']++;
                try {
                    $messageSent = false;

                    // 1) Foto del vehículo + texto como caption (si hay imagen),
                    //    o solo texto si no hay imagen.
                    if ($publicImage !== null) {
                        $imgRes = self::sendImage($cfg['api_url'], $cfg['session_id'], $number, $publicImage['public_url'], $message);
                        if (!$imgRes['ok']) {
                            // Si la imagen falla, intentar al menos enviar el texto para no perder el aviso.
                            Yii::warning(
                                'WhatsApp imagen fallida — ' . $number . ': ' . ($imgRes['error'] ?? 'fallo')
                                . ' — fallback a sendText',
                                'whatsapp'
                            );
                            $textRes = self::sendText($cfg['api_url'], $cfg['session_id'], $number, $message);
                            if (!$textRes['ok']) {
                                $err = $number . ': texto ' . ($textRes['error'] ?? 'fallo');
                                Yii::warning('WhatsApp envío fallido — ' . $err, 'whatsapp');
                                $report['errors'][] = $err;
                                continue;
                            }
                            $messageSent = true;
                        } else {
                            $messageSent = true;
                        }
                    } else {
                        $textRes = self::sendText($cfg['api_url'], $cfg['session_id'], $number, $message);
                        if (!$textRes['ok']) {
                            $err = $number . ': texto ' . ($textRes['error'] ?? 'fallo');
                            Yii::warning('WhatsApp envío fallido — ' . $err, 'whatsapp');
                            $report['errors'][] = $err;
                            continue;
                        }
                        $messageSent = true;
                    }

                    if ($messageSent) {
                        $report['sent']++;
                    }

                    // 2) PDF de la orden.
                    if ($publicPdf !== null) {
                        $docRes = self::sendDocument(
                            $cfg['api_url'],
                            $cfg['session_id'],
                            $number,
                            $publicPdf['public_url'],
                            $publicPdf['filename'],
                            'application/pdf'
                        );
                        if (!$docRes['ok']) {
                            $err = $number . ': pdf ' . ($docRes['error'] ?? 'fallo');
                            Yii::warning('WhatsApp PDF fallido — ' . $err, 'whatsapp');
                            $report['errors'][] = $err;
                        }
                    }
                    Yii::info('WhatsApp enviado correctamente a ' . $number, 'whatsapp');
                } catch (\Throwable $e) {
                    $report['errors'][] = $number . ': ' . $e->getMessage();
                    Yii::error('WhatsApp envío excepción a ' . $number . ': ' . $e->getMessage(), 'whatsapp');
                }
            }
        } catch (\Throwable $e) {
            Yii::error('notifyRentalCreated: ' . $e->getMessage(), 'whatsapp');
            $report['errors'][] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Construye el texto del mensaje a enviar para una orden de alquiler.
     * Incluye: cliente, tipo de vehículo, matrícula, periodo de alquiler y estado de pago.
     */
    public static function buildRentalMessage(Rental $rental): string
    {
        $company = CompanyConfig::getCompanyInfo();
        $companyName = $company['name'] ?? 'Renta de Vehículos';

        $orderId = $rental->rental_id ?: ('R' . $rental->id);

        // ----- Cliente -----
        $client = $rental->client ?? null;
        $clientName = '—';
        $clientPhone = '';
        if ($client) {
            $clientName = trim((string) $client->full_name);
            if ($clientName === '') {
                $clientName = trim(((string) ($client->nombre ?? '')) . ' ' . ((string) ($client->apellido ?? '')));
            }
            if ($clientName === '') {
                $clientName = '—';
            }
            // Tomar el primer teléfono disponible: WhatsApp -> celular -> teléfono.
            foreach (['whatsapp', 'celular', 'telefono'] as $f) {
                $val = trim((string) ($client->{$f} ?? ''));
                if ($val !== '') {
                    $clientPhone = $val;
                    break;
                }
            }
        }
        $clientPhoneDigits = $clientPhone !== '' ? preg_replace('/\D+/', '', $clientPhone) : '';

        // ----- Vehículo (tipo + matrícula) -----
        $car = $rental->car ?? null;
        $carType = '—';
        $plate = '—';
        if ($car) {
            $plateRaw = trim((string) ($car->placa ?? ''));
            $plate = $plateRaw !== '' ? $plateRaw : '—';

            // Tipo = Marca + Nombre/Modelo del vehículo
            $brandName = '';
            try {
                if (!empty($car->marca_id) && $car->marca) {
                    $brandName = trim((string) ($car->marca->name ?? ''));
                }
            } catch (\Throwable $e) {
                $brandName = '';
            }
            $modelLabel = trim((string) ($car->nombre ?? ''));
            // Evitar duplicar la marca si ya está al inicio del nombre
            if ($brandName !== '' && $modelLabel !== '' && stripos($modelLabel, $brandName) === 0) {
                $carType = $modelLabel;
            } else {
                $carType = trim($brandName . ' ' . $modelLabel);
            }
            if ($carType === '') {
                $carType = '—';
            }
        }

        // ----- Periodo de alquiler -----
        $startDate = !empty($rental->fecha_inicio) ? date('d/m/Y', strtotime((string) $rental->fecha_inicio)) : '—';
        $endDate = !empty($rental->fecha_final) ? date('d/m/Y', strtotime((string) $rental->fecha_final)) : '—';
        $startTime = self::formatTime12h($rental->hora_inicio ?? null);
        $endTime = self::formatTime12h($rental->hora_final ?? null);

        $startLabel = $startDate . ($startTime !== '' ? ' ' . $startTime : '');
        $endLabel = $endDate . ($endTime !== '' ? ' ' . $endTime : '');

        $days = (int) ($rental->cantidad_dias ?? 0);

        // ----- Corre apartir (si está habilitado) -----
        $correaLine = null;
        $correaEnabled = !empty($rental->correapartir_enabled);
        $correaRaw = trim((string) ($rental->fecha_correapartir ?? ''));
        if ($correaEnabled && $correaRaw !== '' && $correaRaw !== '0000-00-00 00:00:00') {
            $ts = strtotime($correaRaw);
            if ($ts !== false) {
                // Si trae hora distinta de 00:00, mostrar fecha + hora 12h
                $hasTime = (bool) preg_match('/\s\d{2}:\d{2}/', $correaRaw);
                $correaLine = $hasTime
                    ? date('d/m/Y h:i A', $ts)
                    : date('d/m/Y', $ts);
            } else {
                $correaLine = $correaRaw;
            }
        }

        // ----- Estado de pago -----
        $payStatusKey = (string) ($rental->estado_pago ?? '');
        $payStatusLabels = [
            'pendiente' => '🟡 Pendiente',
            'pagado' => '✅ Pagado',
            'reservado' => '📌 Reservado',
            'cancelado' => '❌ Cancelado',
            'finalizado' => '🏁 Finalizado',
        ];
        $payStatus = $payStatusLabels[$payStatusKey] ?? ($payStatusKey !== '' ? ucfirst($payStatusKey) : '—');

        // ----- Total -----
        $total = number_format((float) ($rental->total_precio ?? 0), 0, '.', ',');
        $currency = '₡';

        $lines = [];
        $lines[] = '*🚗 Nueva orden de alquiler*';
        $lines[] = $companyName;
        $lines[] = '';
        $lines[] = 'Orden: *' . $orderId . '*';
        $lines[] = '';
        $lines[] = '👤 *Cliente:* ' . $clientName;
        if ($clientPhone !== '') {
            // Mostrar número y un enlace wa.me para abrir el chat directamente desde WhatsApp.
            $lines[] = '📱 *WhatsApp:* ' . $clientPhone;
            if ($clientPhoneDigits !== '' && strlen($clientPhoneDigits) >= 7) {
                $lines[] = 'https://wa.me/' . $clientPhoneDigits;
            }
        } else {
            $lines[] = '📱 *WhatsApp:* —';
        }
        $lines[] = '';
        $lines[] = '🚙 *Vehículo:* ' . $carType;
        $lines[] = '🔖 *Matrícula:* ' . $plate;
        $lines[] = '';
        $lines[] = '📅 *Periodo del alquiler*';
        $lines[] = 'Desde: ' . $startLabel;
        $lines[] = 'Hasta: ' . $endLabel;
        if ($days > 0) {
            $lines[] = 'Duración: ' . $days . ' día' . ($days === 1 ? '' : 's');
        }
        if ($correaLine !== null) {
            $lines[] = '⏰ *Corre apartir:* ' . $correaLine;
        }
        $lines[] = '';
        $lines[] = '💵 *Estado de pago:* ' . $payStatus;
        $lines[] = '💰 *Total:* ' . $currency . ' ' . $total;
        $lines[] = '';
        $lines[] = 'Se adjunta la orden en PDF.';

        return implode("\n", $lines);
    }

    /**
     * Convierte HH:MM(:SS) (24h) a "h:MM AM/PM" (12h). Devuelve '' si no se puede.
     */
    private static function formatTime12h($raw): string
    {
        if ($raw === null || $raw === '') return '';
        $s = trim((string) $raw);
        // Soportar "HH:MM" o "HH:MM:SS"
        if (!preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $s, $m)) {
            return '';
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($h < 0 || $h > 23 || $min < 0 || $min > 59) return '';
        $period = $h >= 12 ? 'PM' : 'AM';
        $h12 = $h % 12;
        if ($h12 === 0) $h12 = 12;
        return $h12 . ':' . str_pad((string) $min, 2, '0', STR_PAD_LEFT) . ' ' . $period;
    }
}
