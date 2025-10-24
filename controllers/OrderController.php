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
        $query = Order::find()->with(['client']);
        
        $sale_mode = Yii::$app->request->get('sale_mode');
        if ($sale_mode) {
            $query->andWhere(['sale_mode' => $sale_mode]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        // También obtener alquileres para mostrar como órdenes
        $rentalsQuery = Rental::find()->with(['client', 'car']);
        $rentalsDataProvider = new ActiveDataProvider([
            'query' => $rentalsQuery,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        // Obtener contadores por estado de pago para los alquileres
        $paymentCounters = Rental::find()
            ->select(['estado_pago', 'COUNT(*) as count'])
            ->groupBy('estado_pago')
            ->asArray()
            ->all();

        // Convertir a un array asociativo más fácil de usar
        $counters = [];
        foreach ($paymentCounters as $counter) {
            $counters[$counter['estado_pago']] = $counter['count'];
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'rentalsDataProvider' => $rentalsDataProvider,
            'status' => $sale_mode,
            'paymentCounters' => $counters,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Order();
        $model->sale_mode = 'retail';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '✅ Venta creada exitosamente');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al crear venta: ' . json_encode($model->errors));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ Venta actualizada exitosamente');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', '🗑️ Venta eliminada exitosamente');
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

