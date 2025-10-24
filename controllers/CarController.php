<?php
namespace app\controllers;

use Yii;
use app\models\Car;
use app\models\Brand;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class CarController extends Controller
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
        $query = Car::find();
        
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'brand', $search],
                ['like', 'model', $search],
                ['like', 'plate', $search],
            ]);
        }
        
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'status' => $status,
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
        $model = new Car();
        $model->status = 'disponible';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ Vehículo creado exitosamente');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $brands = Brand::getBrandsForDropdown();

        return $this->render('create', [
            'model' => $model,
            'brands' => $brands
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '✅ Vehículo actualizado exitosamente');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $brands = Brand::getBrandsForDropdown();

        return $this->render('update', [
            'model' => $model,
            'brands' => $brands
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = 'inactive';
        $model->save(false);

        Yii::$app->session->setFlash('success', '🗑️ Vehículo eliminado exitosamente');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Car::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

