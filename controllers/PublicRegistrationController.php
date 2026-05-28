<?php

namespace app\controllers;

use Yii;
use app\models\Client;
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
     * Muestra el formulario de registro público
     */
    public function actionIndex()
    {
        $model = new Client();
        $model->approval_status = 'pending'; // Establecer como pendiente por defecto

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->approval_status = 'pending'; // Forzar pending para registros públicos

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

                Yii::$app->session->setFlash('success', '¡Gracias por registrarte! Tu solicitud está pendiente de aprobación. Te notificaremos cuando sea aprobada.');
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
        }

        return $this->render('index', [
            'model' => $model,
        ]);
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

