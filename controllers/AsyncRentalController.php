<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Rental;
use app\models\Client;
use app\models\Car;

class AsyncRentalController extends Controller
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
        $dataProvider = new ActiveDataProvider([
            'query' => Rental::find()
                ->with(['client', 'car'])
                ->where(['is_async' => 1])
                ->orderBy(['fecha_inicio' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Rental();
        $model->is_async = 1;

        if ($model->load(Yii::$app->request->post())) {
            $model->is_async = 1;
            $this->normalizeCorreapartir($model);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', '✅ Orden asincrónica creada correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al crear la orden asincrónica. Verifique los datos ingresados.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'clients' => Client::find()->orderBy(['full_name' => SORT_ASC])->all(),
            'cars' => Car::find()->orderBy(['nombre' => SORT_ASC])->all(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->is_async = 1;
            $this->normalizeCorreapartir($model);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', '✅ Orden asincrónica actualizada correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al actualizar la orden asincrónica.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'clients' => Client::find()->orderBy(['full_name' => SORT_ASC])->all(),
            'cars' => Car::find()->orderBy(['nombre' => SORT_ASC])->all(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', '🗑️ Orden asincrónica eliminada correctamente.');
        return $this->redirect(['index']);
    }

    public function actionView($id)
    {
        return $this->redirect(['/rental/view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = Rental::find()->where(['id' => $id, 'is_async' => 1])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La orden asincrónica solicitada no existe.');
    }

    private function normalizeCorreapartir(Rental $model): void
    {
        if (!empty($model->fecha_correapartir)) {
            $value = $model->fecha_correapartir;
            if (strpos($value, 'T') !== false) {
                $value = str_replace('T', ' ', $value);
            }
            if (strlen($value) === 16) {
                $value .= ':00';
            }
            $model->fecha_correapartir = $value;
        }
    }
}

