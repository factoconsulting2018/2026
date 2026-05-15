<?php

namespace app\controllers;

use Yii;
use app\models\Client;
use app\models\Incident;
use app\models\IncidentPayment;
use yii\data\ActiveDataProvider;
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
                    'add-payment' => ['POST'],
                    'close' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Listado de insidentes con buscador.
     */
    public function actionIndex()
    {
        $search = trim((string) Yii::$app->request->get('search', ''));

        $query = Incident::find()
            ->alias('i')
            ->with(['client', 'payments'])
            ->joinWith(['client c'])
            ->orderBy(['i.id' => SORT_DESC]);

        if ($search !== '') {
            $conds = [
                'or',
                ['like', 'c.full_name', $search],
                ['like', 'c.cedula_fisica', $search],
                ['like', 'i.notes', $search],
            ];
            if (ctype_digit($search)) {
                $conds[] = ['i.id' => (int) $search];
            }
            $query->andWhere($conds);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
        ]);
    }

    /**
     * Formulario nuevo insidente (GET) o guardar (POST).
     */
    public function actionCreate()
    {
        $model = new Incident();
        $model->status = Incident::STATUS_OPEN;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Insidente registrado. Puede registrar abonos en la ficha del caso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $cid = (int) Yii::$app->request->get('client_id');
            if ($cid > 0) {
                $model->client_id = $cid;
            }
        }

        return $this->render('create', [
            'model' => $model,
            'clients' => Client::find()->orderBy(['full_name' => SORT_ASC])->all(),
        ]);
    }

    /**
     * Detalle del insidente, abonos y saldo.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'paymentModel' => new IncidentPayment(),
        ]);
    }

    public function actionAddPayment()
    {
        $incidentId = (int) Yii::$app->request->post('incident_id');
        $incident = Incident::find()
            ->where(['id' => $incidentId, 'status' => Incident::STATUS_OPEN])
            ->one();
        if (!$incident) {
            Yii::$app->session->setFlash('error', 'Insidente no encontrado o ya cerrado.');
            return $this->redirect(['index']);
        }

        $payment = new IncidentPayment();
        $payment->load(Yii::$app->request->post());

        if (!$payment->validate()) {
            Yii::$app->session->setFlash('error', 'Datos inválidos: ' . implode(' ', $payment->getFirstErrors()));
            return $this->redirect(['view', 'id' => $incident->id]);
        }

        $balance = $incident->getBalance();
        $pay = (float) $payment->amount;
        if ($pay > $balance + 0.0001) {
            Yii::$app->session->setFlash('error', 'El abono no puede ser mayor al saldo pendiente (¢' . number_format($balance, 2) . ').');
            return $this->redirect(['view', 'id' => $incident->id]);
        }

        $payment->incident_id = $incident->id;
        if ($payment->save(false)) {
            $incident->refresh();
            $newBalance = $incident->getBalance();
            if ($newBalance < 0.01) {
                $incident->status = Incident::STATUS_CLOSED;
                $incident->save(false);
                Yii::$app->session->setFlash('success', 'Abono registrado. Saldo en cero; el insidente se cerró automáticamente.');
            } else {
                Yii::$app->session->setFlash('success', 'Abono registrado. Saldo pendiente: ¢' . number_format($newBalance, 2));
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo guardar el abono.');
        }

        return $this->redirect(['view', 'id' => $incident->id]);
    }

    public function actionClose($id)
    {
        $model = $this->findModel($id);
        if ($model->getBalance() > 0.01) {
            Yii::$app->session->setFlash('error', 'No se puede cerrar un insidente con saldo pendiente.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->status = Incident::STATUS_CLOSED;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Insidente cerrado.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    protected function findModel($id): Incident
    {
        $model = Incident::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Insidente no encontrado.');
        }
        return $model;
    }
}
