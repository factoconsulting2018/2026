<?php
namespace app\controllers;

use Yii;
use app\models\Order;
use app\models\Rental;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class OrderController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $query = Order::find()->with(['client', 'car']);
        
        $estado_pago = Yii::$app->request->get('estado_pago');
        if ($estado_pago) {
            $query->andWhere(['estado_pago' => $estado_pago]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        // Obtener contadores por estado_pago
        $paymentCounters = Order::find()
            ->select(['estado_pago', 'COUNT(*) as count'])
            ->groupBy('estado_pago')
            ->asArray()
            ->all();

        // Convertir a un array asociativo
        $counters = [];
        foreach ($paymentCounters as $counter) {
            $counters[$counter['estado_pago']] = $counter['count'];
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'paymentCounters' => $counters,
            'status' => $estado_pago,
        ]);
    }

    public function actionView($id)
    {
        // Redirigir a la vista de rental en lugar de order
        return $this->redirect(['/rental/view', 'id' => $id]);
    }

    public function actionCreate()
    {
        $model = new Order();
        $model->estado_pago = 'pendiente';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '✅ Alquiler creado exitosamente');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al crear alquiler: ' . json_encode($model->errors));
            }
        }

        // Redirigir a la vista de creación de rentals
        return $this->redirect(['/rental/create']);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ Alquiler actualizado exitosamente');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Redirigir a la vista de actualización de rentals
        return $this->redirect(['/rental/update', 'id' => $id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', '🗑️ Alquiler eliminado exitosamente');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

