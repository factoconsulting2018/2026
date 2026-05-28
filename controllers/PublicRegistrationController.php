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
            
            if ($model->save()) {
                // Notificar a los teléfonos administrativos por WhatsApp.
                // Es defensivo: si falla no debe romper el registro del cliente.
                try {
                    $waReport = WhatsAppNotifier::notifyClientRegistered($model);
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
        }
        
        return $this->render('index', [
            'model' => $model,
        ]);
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

