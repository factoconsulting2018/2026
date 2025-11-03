<?php

namespace app\controllers;

use Yii;
use app\models\Client;
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
        $model = new Client();
        $model->approval_status = 'pending';
        
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        
        return [];
    }
}

