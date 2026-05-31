<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\models\Car;
use app\models\Rental;
use app\models\PromoVisit;
use app\components\WhatsAppNotifier;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\filters\AccessControl;

class PublicRegistrationController extends Controller
{
    public $layout = false; // Sin layout para vista pública
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true, // Permitir acceso público sin autenticación
                    ],
                ],
            ],
        ];
    }

    /**
     * Muestra el formulario de registro público (Solicitud de Membresía).
     */
    public function actionIndex()
    {
        $model = new Client();
        $model->approval_status = 'pending';

        if ($response = $this->handlePost($model, false)) {
            return $response;
        }

        return $this->renderRegistrationForm($model);
    }

    /**
     * Formulario público para realizar alquiler (clientes nuevos o recurrentes).
     */
    public function actionRealizarAlquiler()
    {
        $model = new Client();
        $model->approval_status = 'pending';

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $rentalDetails = $this->extractRentalDetails($post);
            $cedula = trim((string) ($post['Client']['cedula_fisica'] ?? ''));
            $existingClient = $this->findExistingClient($cedula);

            if ($existingClient !== null) {
                if ($response = $this->handleRecurringPost($existingClient, $rentalDetails)) {
                    return $response;
                }
                return $this->renderRegistrationForm($model, [
                    'isRecurringMode' => true,
                    'pageTitle' => 'Realizar alquiler',
                ]);
            }

            if ($response = $this->handlePost($model, true)) {
                return $response;
            }

            return $this->renderRegistrationForm($model, [
                'isRecurringMode' => true,
                'pageTitle' => 'Realizar alquiler',
            ]);
        }

        return $this->renderRegistrationForm($model, [
            'isRecurringMode' => true,
            'pageTitle' => 'Realizar alquiler',
        ]);
    }

    /**
     * Landing promo por vehículo (/promo/{slug}).
     */
    public function actionPromo($slug = null)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            Yii::$app->session->setFlash('info', 'Selecciona un vehículo para continuar con tu solicitud.');
            return $this->redirect(['/solicitud-membresia']);
        }

        $promoCar = Car::find()
            ->with(['marca'])
            ->where([
                'facebook_promo_slug' => $slug,
                'facebook_promo_enabled' => 1,
            ])
            ->one();

        if ($promoCar === null) {
            Yii::$app->session->setFlash('warning', 'La promoción solicitada no está disponible.');
            return $this->redirect(['/solicitud-membresia']);
        }

        if (!Yii::$app->request->isPost) {
            try {
                PromoVisit::recordVisit((int) $promoCar->id, $slug, [
                    'ip' => Yii::$app->request->userIP,
                    'user_agent' => (string) Yii::$app->request->userAgent,
                    'referer' => (string) Yii::$app->request->referrer,
                ]);
            } catch (\Throwable $e) {
                Yii::warning('No se pudo registrar visita promo: ' . $e->getMessage(), 'promo');
            }
        }

        $model = new Client();
        $model->approval_status = 'pending';

        if ($response = $this->handlePost($model, false)) {
            return $response;
        }

        return $this->renderRegistrationForm($model, [
            'promoCar' => $promoCar,
            'promos' => Car::findActivePromos(),
        ]);
    }

    /**
     * @param array<string,mixed> $extra
     */
    private function renderRegistrationForm(Client $model, array $extra = []): string
    {
        return $this->render('index', array_merge([
            'model' => $model,
            'promoCar' => null,
            'promos' => [],
            'isRecurringMode' => false,
            'pageTitle' => 'Registro de Nuevo Cliente',
        ], $extra));
    }

    /**
     * Procesa POST de cliente recurrente: crea Rental y notifica por WhatsApp.
     */
    private function handleRecurringPost(Client $client, array $rentalDetails): ?Response
    {
        $rental = $this->createRecurringRental($client, $rentalDetails);
        if ($rental === null) {
            Yii::$app->session->setFlash(
                'error',
                'No se pudo registrar tu solicitud de alquiler. Verifica las fechas e intenta de nuevo.'
            );
            return null;
        }

        try {
            $waReport = WhatsAppNotifier::notifyRecurringRentalRequest($client, $rental, $rentalDetails);
            if (!empty($waReport['skipped_reason'])) {
                Yii::info('WhatsApp recurring omitido: ' . $waReport['skipped_reason'], 'whatsapp');
            } elseif ($waReport['enabled'] && $waReport['sent'] === 0 && !empty($waReport['errors'])) {
                Yii::warning('WhatsApp recurring sin envíos: ' . implode(' | ', $waReport['errors']), 'whatsapp');
            }
        } catch (\Throwable $e) {
            Yii::error('WhatsApp recurring exception: ' . $e->getMessage(), 'whatsapp');
        }

        Yii::$app->session->setFlash(
            'success',
            '¡Tu solicitud de alquiler fue enviada! Te contactaremos pronto para confirmar los detalles.'
        );
        return $this->refresh();
    }

    /**
     * Procesa el POST del formulario público (registro completo). Devuelve Response si hubo éxito.
     */
    private function handlePost(Client $model, bool $fromRecurringForm): ?Response
    {
        if (!Yii::$app->request->isPost) {
            return null;
        }

        $model->load(Yii::$app->request->post());
        $model->approval_status = 'pending';

        $rentalDetails = $this->extractRentalDetails(Yii::$app->request->post());
        $rentalDetailsText = $this->buildRentalDetailsText($rentalDetails);
        if ($rentalDetailsText !== '') {
            $existingNotes = trim((string) $model->notes);
            $model->notes = $existingNotes !== ''
                ? $rentalDetailsText . "\n\n" . $existingNotes
                : $rentalDetailsText;
        }

        if ($model->save()) {
            try {
                $waReport = WhatsAppNotifier::notifyClientRegistered($model, $rentalDetails);
                if (!empty($waReport['skipped_reason'])) {
                    Yii::info('WhatsApp client-reg omitido: ' . $waReport['skipped_reason'], 'whatsapp');
                } elseif ($waReport['enabled'] && $waReport['sent'] === 0 && !empty($waReport['errors'])) {
                    Yii::warning('WhatsApp client-reg sin envíos: ' . implode(' | ', $waReport['errors']), 'whatsapp');
                }
            } catch (\Throwable $e) {
                Yii::error('WhatsApp client-reg exception: ' . $e->getMessage(), 'whatsapp');
            }

            $successMsg = $fromRecurringForm
                ? '¡Gracias! Tu solicitud fue enviada. Te contactaremos pronto para confirmar tu alquiler.'
                : '¡Gracias por registrarte! Tu solicitud está pendiente de aprobación. Te notificaremos cuando sea aprobada.';
            Yii::$app->session->setFlash('success', $successMsg);
            return $this->refresh();
        }

        Yii::error(
            'PublicRegistration save() falló. Errors: ' . json_encode($model->getErrors())
            . ' Attrs: ' . json_encode([
                'cedula' => $model->cedula_fisica,
                'full_name' => $model->full_name,
                'whatsapp' => $model->whatsapp,
                'email' => $model->email,
            ]),
            'public-registration'
        );
        Yii::$app->session->setFlash(
            'error',
            'No se pudo enviar tu solicitud. Revisa los datos marcados en rojo y vuelve a intentar.'
        );

        return null;
    }

    private function findExistingClient(string $cedula): ?Client
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return null;
        }

        $client = Client::find()->where(['cedula_fisica' => $cedula])->one();
        if ($client !== null) {
            return $client;
        }

        $digits = preg_replace('/\D+/', '', $cedula);
        if ($digits === '' || $digits === $cedula) {
            return null;
        }

        return Client::find()->where(['cedula_fisica' => $digits])->one();
    }

    /**
     * @param array{fecha_inicio:string, hora_inicio:string, fecha_final:string, hora_final:string, tipo_auto:string} $rentalDetails
     */
    private function createRecurringRental(Client $client, array $rentalDetails): ?Rental
    {
        $fechaInicio = trim($rentalDetails['fecha_inicio'] ?? '');
        $fechaFinal = trim($rentalDetails['fecha_final'] ?? '');
        if ($fechaInicio === '') {
            $fechaInicio = date('Y-m-d');
        }
        if ($fechaFinal === '') {
            $fechaFinal = $fechaInicio;
        }

        $rental = new Rental();
        $rental->client_id = (int) $client->id;
        $rental->car_id = null;
        $rental->is_recurring_request = 1;
        $rental->tipo_auto_solicitado = mb_substr(trim((string) ($rentalDetails['tipo_auto'] ?? '')), 0, 80);
        $rental->fecha_inicio = $fechaInicio;
        $rental->fecha_final = $fechaFinal;
        $rental->hora_inicio = trim((string) ($rentalDetails['hora_inicio'] ?? '')) ?: '08:00';
        $rental->hora_final = trim((string) ($rentalDetails['hora_final'] ?? '')) ?: '08:00';
        $rental->cantidad_dias = $this->calculateCantidadDias($fechaInicio, $fechaFinal);
        $rental->estado_pago = 'reservado';
        $rental->precio_por_dia = 0;
        $rental->condiciones_especiales = 'Solicitud de cliente recurrente — enviada desde formulario público.';

        try {
            if (!$rental->save(false)) {
                Yii::error('createRecurringRental save(false) falló', 'public-registration');
                return null;
            }
        } catch (\Throwable $e) {
            Yii::error('createRecurringRental exception: ' . $e->getMessage(), 'public-registration');
            return null;
        }

        return $rental;
    }

    private function calculateCantidadDias(string $fechaInicio, string $fechaFinal): int
    {
        try {
            $start = new \DateTimeImmutable($fechaInicio);
            $end = new \DateTimeImmutable($fechaFinal);
            $days = (int) $start->diff($end)->days;
            return max(1, $days > 0 ? $days : 1);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Extrae los detalles del alquiler enviados desde el paso 2 del formulario público.
     *
     * @return array{fecha_inicio:string, hora_inicio:string, fecha_final:string, hora_final:string, tipo_auto:string}
     */
    private function extractRentalDetails(array $post): array
    {
        $tipo = trim((string) ($post['rental_tipo_auto'] ?? ''));
        $tipoOtro = trim((string) ($post['rental_tipo_auto_otro'] ?? ''));
        if (strcasecmp($tipo, 'otro') === 0 && $tipoOtro !== '') {
            $tipo = $tipoOtro;
        }

        return [
            'fecha_inicio' => trim((string) ($post['rental_fecha_inicio'] ?? '')),
            'hora_inicio'  => trim((string) ($post['rental_hora_inicio'] ?? '')),
            'fecha_final'  => trim((string) ($post['rental_fecha_final'] ?? '')),
            'hora_final'   => trim((string) ($post['rental_hora_final'] ?? '')),
            'tipo_auto'    => $tipo,
        ];
    }

    /**
     * Convierte los detalles del alquiler a un bloque de texto legible para guardar en
     * Client::notes (referencia para el operador que apruebe la solicitud).
     */
    private function buildRentalDetailsText(array $details): string
    {
        $anyDate = $details['fecha_inicio'] !== '' || $details['fecha_final'] !== '';
        if (!$anyDate && $details['tipo_auto'] === '') {
            return '';
        }
        $fmt = function (string $d, string $h): string {
            if ($d === '') return '';
            $ts = strtotime($d . ($h !== '' ? ' ' . $h : ''));
            if ($ts === false) return trim($d . ' ' . $h);
            return date('d/m/Y', $ts) . ($h !== '' ? ' ' . date('h:i A', $ts) : '');
        };
        $lines = ['[Solicitud de alquiler]'];
        $ini = $fmt($details['fecha_inicio'], $details['hora_inicio']);
        $fin = $fmt($details['fecha_final'], $details['hora_final']);
        if ($ini !== '') $lines[] = 'Inicio: ' . $ini;
        if ($fin !== '') $lines[] = 'Fin: ' . $fin;
        if ($details['tipo_auto'] !== '') $lines[] = 'Tipo de auto: ' . $details['tipo_auto'];
        return implode("\n", $lines);
    }

    /**
     * Validación AJAX del formulario
     */
    public function actionValidate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Client();
        $model->approval_status = 'pending';

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }

        return [];
    }
}
