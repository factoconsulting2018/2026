<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\models\CompanyConfig;
use app\components\WhatsAppNotifier;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * MarketingController — Campañas de WhatsApp dirigidas a clientes.
 *
 * - actionIndex(): pantalla principal de campaña.
 * - actionUploadImage(): sube imagen embebida o adjunta y devuelve URL pública.
 * - actionSend(): envía la campaña a los clientes seleccionados de forma controlada.
 */
class MarketingController extends Controller
{
    /** Máximo de destinatarios por petición de envío (resto se procesa con paginación del cliente). */
    const MAX_RECIPIENTS_PER_REQUEST = 500;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'send' => ['post'],
                    'upload-image' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Pantalla principal con buscador de clientes, editor del mensaje y envío.
     */
    public function actionIndex()
    {
        $clients = Client::find()
            ->select(['id', 'full_name', 'cedula_fisica', 'whatsapp', 'telefono', 'email'])
            ->where(['or', ['approval_status' => 'approved'], ['approval_status' => null]])
            ->andWhere(['or', ['not', ['whatsapp' => null]], ['not', ['telefono' => null]]])
            ->orderBy(['full_name' => SORT_ASC])
            ->asArray()
            ->all();

        $rows = [];
        foreach ($clients as $c) {
            $rawPhone = trim((string) ($c['whatsapp'] ?? '')) !== ''
                ? (string) $c['whatsapp']
                : (string) ($c['telefono'] ?? '');
            $rawPhone = trim($rawPhone);
            if ($rawPhone === '') {
                continue;
            }
            $digits = preg_replace('/\D+/', '', $rawPhone);
            if (strlen($digits) < 8) {
                continue;
            }
            $rows[] = [
                'id' => (int) $c['id'],
                'name' => (string) $c['full_name'],
                'cedula' => (string) ($c['cedula_fisica'] ?? ''),
                'phone' => $rawPhone,
                'email' => (string) ($c['email'] ?? ''),
            ];
        }

        $waConfig = CompanyConfig::getWhatsAppConfig();
        $mkConfig = CompanyConfig::getMarketingConfig();

        $status = ['status' => 'unknown', 'qr' => false];
        try {
            $status = WhatsAppNotifier::getStatus($waConfig['api_url'], $waConfig['session_id']);
        } catch (\Throwable $e) {
            // ignore — se mostrará como desconectado
        }
        $connected = WhatsAppNotifier::isConnected($status);

        return $this->render('index', [
            'clients' => $rows,
            'waConfig' => $waConfig,
            'mkConfig' => $mkConfig,
            'connected' => $connected,
        ]);
    }

    /**
     * Sube una imagen y devuelve la URL pública. Acepta multipart con `image`.
     */
    public function actionUploadImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('image');
        if (!$file) {
            return ['success' => false, 'message' => 'No se recibió ningún archivo.'];
        }

        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        $ext = strtolower((string) $file->extension);
        if (!in_array($ext, $allowed, true)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido. Use PNG/JPG/JPEG/GIF/WEBP.'];
        }
        if ($file->size > 8 * 1024 * 1024) {
            return ['success' => false, 'message' => 'La imagen excede el tamaño máximo de 8 MB.'];
        }

        $dirRel = CompanyConfig::MARKETING_DIR;
        $dirAbs = Yii::getAlias('@webroot/' . $dirRel);
        if (!is_dir($dirAbs)) {
            @mkdir($dirAbs, 0775, true);
        }

        $name = 'mk_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $absolute = rtrim($dirAbs, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!$file->saveAs($absolute)) {
            return ['success' => false, 'message' => 'No se pudo guardar la imagen.'];
        }

        $webUrl = Yii::getAlias('@web/' . $dirRel . $name);
        $publicBase = (string) (CompanyConfig::getWhatsAppConfig()['public_base_url'] ?? '');
        if ($publicBase !== '') {
            $publicUrl = rtrim($publicBase, '/') . '/' . ltrim($dirRel . $name, '/');
        } else {
            $publicUrl = Url::to($webUrl, true);
        }

        return [
            'success' => true,
            'url' => $webUrl,
            'public_url' => $publicUrl,
            'filename' => $name,
        ];
    }

    /**
     * Envía la campaña a los clientes seleccionados.
     * Si la cantidad supera MAX_RECIPIENTS_PER_REQUEST, se procesa por lotes desde el cliente.
     *
     * Espera: client_ids[], message (texto plano del editor), image_public_url (opcional), interval_seconds (opcional)
     */
    public function actionSend()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        $waConfig = CompanyConfig::getWhatsAppConfig();
        $mkConfig = CompanyConfig::getMarketingConfig();

        if (!$waConfig['enabled']) {
            return ['success' => false, 'message' => 'La integración de WhatsApp está deshabilitada en Configuración.'];
        }

        try {
            $status = WhatsAppNotifier::getStatus($waConfig['api_url'], $waConfig['session_id']);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'No se pudo verificar el estado de la sesión: ' . $e->getMessage()];
        }
        if (!WhatsAppNotifier::isConnected($status)) {
            return ['success' => false, 'message' => 'La sesión de WhatsApp no está conectada. Escanee el QR primero.'];
        }

        $ids = (array) $req->post('client_ids', []);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        })));
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No seleccionó ningún cliente.'];
        }

        $message = trim((string) $req->post('message', ''));
        $imagePublicUrl = trim((string) $req->post('image_public_url', ''));
        $imageCaption = trim((string) $req->post('image_caption', ''));
        $interval = (int) $req->post('interval_seconds', $mkConfig['interval_seconds']);
        $interval = max(1, min(120, $interval));

        if ($message === '' && $imagePublicUrl === '') {
            return ['success' => false, 'message' => 'Debe escribir un mensaje o adjuntar una imagen.'];
        }

        if (count($ids) > self::MAX_RECIPIENTS_PER_REQUEST) {
            return [
                'success' => false,
                'message' => 'Demasiados destinatarios en una sola petición. Envíe en lotes de hasta '
                    . self::MAX_RECIPIENTS_PER_REQUEST . ' contactos.',
            ];
        }

        $signature = trim((string) $mkConfig['signature']);
        if ($signature !== '') {
            $message = rtrim($message) . "\n\n" . $signature;
        }

        $clients = Client::find()
            ->where(['id' => $ids])
            ->all();

        $sent = 0;
        $failed = 0;
        $details = [];

        // Tiempo máximo de ejecución conservador (PHP CLI/HTTP)
        @set_time_limit(0);

        $startedAt = microtime(true);
        $last = 0.0;

        foreach ($clients as $client) {
            $rawPhone = trim((string) $client->whatsapp) !== ''
                ? (string) $client->whatsapp
                : (string) $client->telefono;
            $number = WhatsAppNotifier::normalizeNumber($rawPhone, $waConfig['country_code']);
            if ($number === null || $number === '') {
                $failed++;
                $details[] = [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'phone' => $rawPhone,
                    'ok' => false,
                    'error' => 'Número inválido',
                ];
                continue;
            }

            // Personalización simple: {nombre}, {cedula}.
            $personalMessage = strtr($message, [
                '{nombre}' => (string) $client->full_name,
                '{cedula}' => (string) ($client->cedula_fisica ?? ''),
                '{Nombre}' => (string) $client->full_name,
                '{NOMBRE}' => strtoupper((string) $client->full_name),
            ]);

            // Throttling: separar cada envío por al menos $interval segundos.
            $elapsed = microtime(true) - $last;
            if ($last > 0 && $elapsed < $interval) {
                usleep((int) (($interval - $elapsed) * 1000000));
            }

            if ($imagePublicUrl !== '') {
                $caption = $personalMessage !== '' ? $personalMessage : $imageCaption;
                $res = WhatsAppNotifier::sendImage(
                    $waConfig['api_url'],
                    $waConfig['session_id'],
                    $number,
                    $imagePublicUrl,
                    $caption
                );
            } else {
                $res = WhatsAppNotifier::sendText(
                    $waConfig['api_url'],
                    $waConfig['session_id'],
                    $number,
                    $personalMessage
                );
            }

            $last = microtime(true);

            if (!empty($res['ok'])) {
                $sent++;
                $details[] = [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'phone' => $number,
                    'ok' => true,
                ];
            } else {
                $failed++;
                $details[] = [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'phone' => $number,
                    'ok' => false,
                    'error' => (string) ($res['error'] ?? 'fallo'),
                ];
            }
        }

        CompanyConfig::setConfig(CompanyConfig::MARKETING_LAST_CAMPAIGN_AT, date('Y-m-d H:i:s'), 'Última campaña de marketing enviada');

        $elapsedTotal = number_format(microtime(true) - $startedAt, 1);

        return [
            'success' => $sent > 0,
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($clients),
            'elapsed_seconds' => $elapsedTotal,
            'details' => $details,
            'message' => $failed === 0
                ? "Campaña enviada a {$sent} contacto(s) en {$elapsedTotal}s."
                : "Enviado a {$sent} de " . count($clients) . " contactos. Fallaron {$failed}.",
        ];
    }
}
