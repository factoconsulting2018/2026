<?php

namespace app\components;

use Yii;
use app\models\CompanyConfig;
use app\models\Rental;
use app\controllers\PdfController;

/**
 * Cliente para la API WhatsApp Multi-Session (descargapro.com / Baileys).
 *
 * Documentacion de referencia: API_WhatsApp_Documentacion.html v5.0.0
 *
 * Endpoints utilizados:
 *  - POST   /session/start
 *  - GET    /session/qr/:sessionId
 *  - GET    /session/status/:sessionId
 *  - DELETE /session/delete/:sessionId
 *  - POST   /send/text
 *  - POST   /send/document
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

        $ok = $status >= 200 && $status < 300;
        if (!$ok) {
            try {
                Yii::warning('WhatsApp API ' . $method . ' ' . $url . ' HTTP ' . $status . ': ' . substr((string) $raw, 0, 300), 'whatsapp');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return ['ok' => $ok, 'status' => $status, 'body' => $decoded, 'error' => $ok ? null : ($decoded['message'] ?? ('HTTP ' . $status))];
    }

    public static function getStatus(string $apiUrl, string $sessionId): array
    {
        return self::request('GET', rtrim($apiUrl, '/') . '/session/status/' . rawurlencode($sessionId));
    }

    public static function getQr(string $apiUrl, string $sessionId): array
    {
        return self::request('GET', rtrim($apiUrl, '/') . '/session/qr/' . rawurlencode($sessionId));
    }

    public static function startSession(string $apiUrl, string $sessionId): array
    {
        return self::request('POST', rtrim($apiUrl, '/') . '/session/start', ['sessionId' => $sessionId]);
    }

    public static function deleteSession(string $apiUrl, string $sessionId): array
    {
        return self::request('DELETE', rtrim($apiUrl, '/') . '/session/delete/' . rawurlencode($sessionId));
    }

    public static function sendText(string $apiUrl, string $sessionId, string $number, string $message): array
    {
        return self::request(
            'POST',
            rtrim($apiUrl, '/') . '/send/text',
            ['sessionId' => $sessionId, 'number' => $number, 'message' => $message],
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
        return self::request(
            'POST',
            rtrim($apiUrl, '/') . '/send/document',
            [
                'sessionId' => $sessionId,
                'number' => $number,
                'url' => $publicUrl,
                'filename' => $filename,
                'mimetype' => $mimetype,
            ],
            self::TIMEOUT_SEND
        );
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
     * @return array{enabled:bool, attempted:int, sent:int, errors:array<string>}
     */
    public static function notifyRentalCreated(Rental $rental): array
    {
        $report = ['enabled' => false, 'attempted' => 0, 'sent' => 0, 'errors' => []];

        try {
            $cfg = CompanyConfig::getWhatsAppConfig();
            if (!$cfg['enabled'] || !$cfg['notify_on_create']) {
                return $report;
            }
            $report['enabled'] = true;

            $numbers = self::getAdminNumbers($cfg);
            if (empty($numbers)) {
                $report['errors'][] = 'No hay telefonos administradores configurados.';
                return $report;
            }

            // Verificar que la sesion este conectada antes de gastar timeouts en envios.
            $status = self::getStatus($cfg['api_url'], $cfg['session_id']);
            if (!$status['ok'] || empty($status['body']['isConnected'])) {
                $msg = $status['error'] ?? 'Sesion WhatsApp no conectada.';
                Yii::warning('WhatsApp omitido (sesion no conectada): ' . $msg, 'whatsapp');
                $report['errors'][] = $msg;
                return $report;
            }

            $message = self::buildRentalMessage($rental);

            // Publicar PDF de la orden si existe.
            $pdfFilename = PdfController::rentalOrderPdfFilename($rental);
            $publicPdf = self::publishPdfFromRuntime($pdfFilename, $cfg['public_base_url'] ?: null);

            foreach ($numbers as $number) {
                $report['attempted']++;
                try {
                    $textRes = self::sendText($cfg['api_url'], $cfg['session_id'], $number, $message);
                    if (!$textRes['ok']) {
                        $report['errors'][] = $number . ': texto ' . ($textRes['error'] ?? 'fallo');
                        continue;
                    }

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
                            $report['errors'][] = $number . ': pdf ' . ($docRes['error'] ?? 'fallo');
                            continue;
                        }
                    }
                    $report['sent']++;
                } catch (\Throwable $e) {
                    $report['errors'][] = $number . ': ' . $e->getMessage();
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
     */
    public static function buildRentalMessage(Rental $rental): string
    {
        $company = CompanyConfig::getCompanyInfo();
        $companyName = $company['name'] ?? 'Renta de Vehiculos';

        $orderId = $rental->rental_id ?: ('R' . $rental->id);
        $client = $rental->client ?? null;
        $clientName = $client ? trim((string) $client->full_name) : '—';
        $clientPhone = $client ? trim((string) ($client->whatsapp ?? '')) : '';

        $car = $rental->car ?? null;
        $carLabel = '—';
        if ($car) {
            $brand = trim((string) ($car->marca ?? ''));
            $model = trim((string) ($car->modelo ?? ''));
            $plate = trim((string) ($car->placa ?? ''));
            $carLabel = trim($brand . ' ' . $model . ($plate !== '' ? ' (' . $plate . ')' : ''));
            if ($carLabel === '') {
                $carLabel = '—';
            }
        }

        $startTs = !empty($rental->fecha_inicio) ? strtotime((string) $rental->fecha_inicio) : false;
        $endTs = !empty($rental->fecha_final) ? strtotime((string) $rental->fecha_final) : false;
        $start = $startTs ? date('d/m/Y h:i A', $startTs) : '—';
        $end = $endTs ? date('d/m/Y h:i A', $endTs) : '—';

        $days = (int) ($rental->cantidad_dias ?? 0);
        $total = number_format((float) ($rental->total_precio ?? 0), 0, '.', ',');
        $currency = trim((string) ($rental->moneda ?? '₡')) ?: '₡';

        $lines = [];
        $lines[] = '*Nueva orden de alquiler*';
        $lines[] = $companyName;
        $lines[] = '';
        $lines[] = 'Orden: *' . $orderId . '*';
        $lines[] = 'Cliente: ' . $clientName;
        if ($clientPhone !== '') {
            $lines[] = 'WhatsApp: ' . $clientPhone;
        }
        $lines[] = 'Vehiculo: ' . $carLabel;
        $lines[] = 'Inicio: ' . $start;
        $lines[] = 'Final: ' . $end;
        if ($days > 0) {
            $lines[] = 'Dias: ' . $days;
        }
        $lines[] = 'Total: ' . $currency . ' ' . $total;
        $lines[] = '';
        $lines[] = 'Se adjunta la orden en PDF.';

        return implode("\n", $lines);
    }
}
