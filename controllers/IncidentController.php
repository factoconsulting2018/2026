<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\models\Incident;
use app\models\IncidentPayment;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class IncidentController extends Controller
{
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
                    'create' => ['POST'],
                    'add-payment' => ['POST'],
                    'close' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $clients = Client::find()
            ->orderBy(['full_name' => SORT_ASC])
            ->all();

        $incidentId = (int) Yii::$app->request->get('incident_id');
        $clientId = (int) Yii::$app->request->get('client_id');

        $incident = null;
        if ($incidentId > 0) {
            $incident = Incident::find()
                ->where(['id' => $incidentId, 'status' => Incident::STATUS_OPEN])
                ->one();
            if ($incident) {
                $clientId = (int) $incident->client_id;
            }
        } elseif ($clientId > 0) {
            $incident = Incident::find()
                ->where(['client_id' => $clientId, 'status' => Incident::STATUS_OPEN])
                ->orderBy(['id' => SORT_DESC])
                ->one();
        }

        $openIncidents = [];
        if ($clientId > 0) {
            $openIncidents = Incident::find()
                ->where(['client_id' => $clientId, 'status' => Incident::STATUS_OPEN])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        }

        return $this->render('index', [
            'clients' => $clients,
            'incident' => $incident,
            'clientId' => $clientId,
            'openIncidents' => $openIncidents,
        ]);
    }

    public function actionCreate()
    {
        $model = new Incident();
        $model->status = Incident::STATUS_OPEN;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Incidente registrado. Puede registrar abonos abajo.');
                return $this->redirect(['index', 'incident_id' => $model->id]);
            }
        }

        Yii::$app->session->setFlash('error', 'No se pudo registrar el incidente: ' . implode(' ', $model->getFirstErrors()));
        return $this->redirect(['index', 'client_id' => (int) $model->client_id]);
    }

    public function actionAddPayment()
    {
        $incidentId = (int) Yii::$app->request->post('incident_id');
        $incident = Incident::find()
            ->where(['id' => $incidentId, 'status' => Incident::STATUS_OPEN])
            ->one();
        if (!$incident) {
            Yii::$app->session->setFlash('error', 'Incidente no encontrado o ya cerrado.');
            return $this->redirect(['index']);
        }

        $payment = new IncidentPayment();
        $payment->load(Yii::$app->request->post());

        if (!$payment->validate()) {
            Yii::$app->session->setFlash('error', 'Datos inválidos: ' . implode(' ', $payment->getFirstErrors()));
            return $this->redirect(['index', 'incident_id' => $incident->id]);
        }

        $balance = $incident->getBalance();
        $pay = (float) $payment->amount;
        if ($pay > $balance + 0.0001) {
            Yii::$app->session->setFlash('error', 'El abono no puede ser mayor al saldo pendiente (¢' . number_format($balance, 2) . ').');
            return $this->redirect(['index', 'incident_id' => $incident->id]);
        }

        $payment->incident_id = $incident->id;
        if ($payment->save(false)) {
            $newBalance = $incident->getBalance();
            if ($newBalance < 0.01) {
                $incident->status = Incident::STATUS_CLOSED;
                $incident->save(false);
                Yii::$app->session->setFlash('success', 'Abono registrado. El saldo quedó en cero; el incidente se cerró automáticamente.');
                return $this->redirect(['index', 'client_id' => $incident->client_id]);
            }
            Yii::$app->session->setFlash('success', 'Abono registrado. Saldo pendiente: ¢' . number_format($newBalance, 2));
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo guardar el abono.');
        }

        return $this->redirect(['index', 'incident_id' => $incident->id]);
    }

    public function actionClose($id)
    {
        $model = $this->findIncident($id);
        if ($model->getBalance() > 0.01) {
            Yii::$app->session->setFlash('error', 'No se puede cerrar un incidente con saldo pendiente.');
            return $this->redirect(['index', 'incident_id' => $model->id]);
        }
        $model->status = Incident::STATUS_CLOSED;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Incidente cerrado.');
        return $this->redirect(['index', 'client_id' => $model->client_id]);
    }

    protected function findIncident($id): Incident
    {
        $model = Incident::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Incidente no encontrado.');
        }
        return $model;
    }
}
