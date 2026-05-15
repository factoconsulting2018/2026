<?php
namespace app\controllers;

use Yii;
use app\models\Car;
use app\models\Brand;
use app\models\CarAvailability;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
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

    /**
     * Listado de vehículos sin renta activa en la fecha indicada (por defecto hoy).
     */
    public function actionDisponibles()
    {
        $fecha = Yii::$app->request->get('fecha');
        if ($fecha === null || $fecha === '') {
            $fecha = date('Y-m-d');
        }
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $fecha);
        if ($dt === false || $dt->format('Y-m-d') !== $fecha) {
            $fecha = date('Y-m-d');
        }

        $cars = CarAvailability::getCarsAvailableOnDate($fecha);

        return $this->render('disponibles', [
            'cars' => $cars,
            'fecha' => $fecha,
        ]);
    }

    public function actionIndex()
    {
        $query = Car::find()->with(['marca']);

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->joinWith(['marca'])
                ->andWhere([
                    'or',
                    ['like', 'cars.nombre', $search],
                    ['like', 'cars.placa', $search],
                    ['like', 'cars.vin', $search],
                    ['like', 'brands.name', $search],
                ]);
        }
        
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['cars.status' => $status]);
        }

        $empresa = Yii::$app->request->get('empresa');
        if ($empresa) {
            $query->andWhere(['cars.empresa' => $empresa]);
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
            'empresa' => Yii::$app->request->get('empresa', ''),
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

        if ($this->loadAndSaveCar($model)) {
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

        if ($this->loadAndSaveCar($model)) {
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

    /**
     * Carga POST, sube imagen si corresponde y guarda el vehículo.
     */
    protected function loadAndSaveCar(Car $model): bool
    {
        if (!$model->load(Yii::$app->request->post())) {
            return false;
        }

        $model->imagenFile = UploadedFile::getInstance($model, 'imagenFile');

        if (!$model->validate()) {
            return false;
        }

        $hasNewImage = $model->imagenFile instanceof UploadedFile
            && $model->imagenFile->error !== UPLOAD_ERR_NO_FILE;

        if (!$model->save(false)) {
            return false;
        }

        if ($hasNewImage) {
            if (!$model->uploadImagenFile()) {
                return false;
            }
            $model->save(false, ['imagen']);
        }

        return true;
    }

    protected function findModel($id)
    {
        if (($model = Car::find()->with(['marca'])->where(['id' => $id])->one()) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

