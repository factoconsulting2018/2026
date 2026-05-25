<?php

namespace app\controllers;

use Yii;
use app\models\Car;
use app\models\CompanyConfig;
use app\models\MaintenanceOrder;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class MaintenanceOrderController extends Controller
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
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $search = trim((string) Yii::$app->request->get('search', ''));
        $status = trim((string) Yii::$app->request->get('status', ''));

        // Generar recordatorios Dekra en segundo plano. No mostramos aviso ni
        // los recordatorios programados a futuro: solo aparecerán cuando llegue
        // su mes (ver filtro más abajo).
        try {
            MaintenanceOrder::ensureDekraReminders();
        } catch (\Throwable $e) {
            Yii::error('Error generando recordatorios Dekra: ' . $e->getMessage(), __METHOD__);
        }

        $query = MaintenanceOrder::find()
            ->alias('m')
            ->with(['car'])
            ->joinWith(['car c'])
            ->orderBy(['m.order_date' => SORT_DESC, 'm.id' => SORT_DESC]);

        // Ocultar recordatorios Dekra programados a futuro: solo se ven cuando
        // ya estamos en su mes (mes/año actual o anterior). Otras órdenes se
        // muestran sin filtro de fecha.
        try {
            $dekraTaller = (string) (CompanyConfig::getDekraConfig()['taller_name'] ?? 'Dekra (Revisión Vehicular)');
        } catch (\Throwable $e) {
            $dekraTaller = 'Dekra (Revisión Vehicular)';
        }
        $endOfCurrentMonth = date('Y-m-t');
        $query->andWhere([
            'or',
            ['!=', 'm.taller', $dekraTaller],
            ['is', 'm.taller', null],
            ['<=', 'm.order_date', $endOfCurrentMonth],
        ]);

        if ($search !== '') {
            $conds = [
                'or',
                ['like', 'm.order_id', $search],
                ['like', 'm.notes', $search],
                ['like', 'm.taller', $search],
                ['like', 'c.nombre', $search],
                ['like', 'c.placa', $search],
                ['like', 'c.car_id', $search],
            ];
            if (ctype_digit($search)) {
                $conds[] = ['m.id' => (int) $search];
            }
            $query->andWhere($conds);
        }

        if ($status !== '' && isset(MaintenanceOrder::statusList()[$status])) {
            $query->andWhere(['m.status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'statusFilter' => $status,
        ]);
    }

    public function actionCreate()
    {
        $model = new MaintenanceOrder();
        $model->order_date = date('Y-m-d');
        $model->status = MaintenanceOrder::STATUS_PENDIENTE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                'Orden de mantenimiento ' . $model->order_id . ' registrada correctamente.'
            );
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $carId = (int) Yii::$app->request->get('car_id');
        if ($carId > 0) {
            $model->car_id = $carId;
        }

        return $this->render('create', [
            'model' => $model,
            'cars' => $cars = $this->getCarsForDropdown(),
            'carItems' => MaintenanceOrder::buildCarDropdownList($cars),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Orden actualizada.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'cars' => $cars = $this->getCarsForDropdown(),
            'carItems' => MaintenanceOrder::buildCarDropdownList($cars),
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $orderId = $model->order_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Orden ' . $orderId . ' eliminada.');
        return $this->redirect(['index']);
    }

    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        $newStatus = (string) Yii::$app->request->post('status', '');
        if (!isset(MaintenanceOrder::statusList()[$newStatus])) {
            Yii::$app->session->setFlash('error', 'Estado no válido.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->status = $newStatus;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Estado actualizado a «' . $model->getStatusLabel() . '».');
        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $model->id]);
    }

    protected function findModel($id): MaintenanceOrder
    {
        $model = MaintenanceOrder::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('La orden de mantenimiento no existe.');
        }
        return $model;
    }

    /** @return Car[] */
    protected function getCarsForDropdown(): array
    {
        return Car::find()->orderBy(['nombre' => SORT_ASC])->all();
    }
}
