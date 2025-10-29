<?php
namespace app\controllers;

use Yii;
use app\models\Order;
use app\models\Rental;
use app\models\Client;
use app\models\Car;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class Order2Controller extends Controller
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
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Order();
        $model->estado_pago = 'pendiente';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                // Generar ZIP automáticamente en background
                $this->generateOrderZip($model->id);
                
                Yii::$app->session->setFlash('success', '✅ Alquiler creado exitosamente. El archivo ZIP se está generando en segundo plano.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al crear alquiler: ' . json_encode($model->errors));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'clients' => Client::find()->where(['status' => 'active'])->all(),
            'cars' => Car::find()->where(['status' => 'disponible'])->all(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ Alquiler actualizado exitosamente');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'clients' => Client::find()->where(['status' => 'active'])->all(),
            'cars' => Car::find()->all(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', '🗑️ Alquiler eliminado exitosamente');
        return $this->redirect(['index']);
    }

    /**
     * Generar ZIP con PDF de la orden en segundo plano
     */
    protected function generateOrderZip($rentalId)
    {
        // Generar ZIP en background usando una llamada HTTP asíncrona
        // Esto no bloquea la respuesta al usuario
        try {
            $url = \yii\helpers\Url::to(['/pdf/generate-zip-async', 'id' => $rentalId], true);
            
            // Hacer llamada HTTP asíncrona en background
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout muy corto, no esperar respuesta
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_exec($ch);
            curl_close($ch);
            
            Yii::info('ZIP generation initiated for rental ID: ' . $rentalId, 'order2');
        } catch (\Exception $e) {
            Yii::error('Error initiating ZIP generation: ' . $e->getMessage(), 'order2');
        }
    }

    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

