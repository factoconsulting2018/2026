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
        $query = Order::find()->with(['client', 'article']);
        
        $sale_mode = Yii::$app->request->get('sale_mode');
        if ($sale_mode) {
            $query->andWhere(['sale_mode' => $sale_mode]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        // Obtener contadores por sale_mode
        $modeCounters = Order::find()
            ->select(['sale_mode', 'COUNT(*) as count'])
            ->groupBy('sale_mode')
            ->asArray()
            ->all();

        // Convertir a un array asociativo
        $counters = [];
        foreach ($modeCounters as $counter) {
            $counters[$counter['sale_mode']] = $counter['count'];
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'modeCounters' => $counters,
            'status' => $sale_mode,
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

    public function actionGetArticlePrice($id)
    {
        $article = \app\models\Article::findOne(['id' => $id]);
        if ($article) {
            return $this->asJson(['price' => $article->price]);
        }
        return $this->asJson(['price' => 0]);
    }

    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

