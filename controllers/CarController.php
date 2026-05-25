<?php
namespace app\controllers;

use Yii;
use app\models\Car;
use app\models\Brand;
use app\models\CarAvailability;
use app\models\Rental;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
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
        try {
            Rental::autoFinalizeCompleted();
        } catch (\Throwable $e) {
            Yii::error('Error finalizando alquileres vencidos: ' . $e->getMessage(), 'car');
        }

        // Sincronizar Disponible/Alquilado con las rentas activas antes de listar.
        // Respeta estados manuales 'fuera_servicio' y 'mantenimiento'.
        try {
            Car::syncAllStatuses();
        } catch (\Throwable $e) {
            Yii::error('Error sincronizando estados de vehículos: ' . $e->getMessage(), 'car');
        }

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

    /**
     * Retorna las rentas activas que cubren la fecha actual para un vehículo.
     * Usado por el modal del listado al hacer clic en el badge "Alquilado".
     */
    public function actionActiveRentals($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            Rental::autoFinalizeCompleted();
            Car::syncStatusFromRentals((int) $id);
        } catch (\Throwable $e) {
            Yii::error('Error finalizando alquileres vencidos para vehículo: ' . $e->getMessage(), 'car');
        }

        $car = $this->findModel($id);
        $today = date('Y-m-d');

        $rentals = Rental::find()
            ->with(['client'])
            ->where(['car_id' => $car->id])
            ->andWhere(['is_async' => 0])
            ->andWhere(['not in', 'estado_pago', ['cancelado', 'finalizado']])
            ->andWhere(['<=', 'fecha_inicio', $today])
            ->andWhere(['>=', 'fecha_final', $today])
            ->orderBy(['fecha_inicio' => SORT_DESC])
            ->all();

        $items = [];
        foreach ($rentals as $rental) {
            if ($rental->isSwapped() && !empty($rental->swap_date) && $rental->swap_date <= $today) {
                continue;
            }
            $client = $rental->client;
            $items[] = [
                'id' => $rental->id,
                'rental_id' => $rental->rental_id ?: ('R' . $rental->id),
                'fecha_inicio' => $rental->fecha_inicio,
                'fecha_final' => $rental->fecha_final,
                'estado_pago' => $rental->estado_pago,
                'total_precio' => (float) $rental->total_precio,
                'client_name' => $client ? ($client->full_name ?? $client->nombre) : 'Sin cliente',
                'client_phone' => $client ? ($client->whatsapp ?: $client->telefono ?: $client->celular ?: '') : '',
                'view_url' => \yii\helpers\Url::to(['/rental/view', 'id' => $rental->id]),
                'pdf_url' => \yii\helpers\Url::to(['/pdf/rental-order', 'id' => $rental->id]),
                'is_replacement' => $rental->isReplacement(),
            ];
        }

        return [
            'success' => true,
            'car' => [
                'id' => $car->id,
                'nombre' => $car->nombre,
                'placa' => $car->placa,
            ],
            'today' => $today,
            'rentals' => $items,
        ];
    }

    protected function findModel($id)
    {
        if (($model = Car::find()->with(['marca'])->where(['id' => $id])->one()) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

