<?php
namespace app\controllers;

use Yii;
use app\models\Car;
use app\models\Brand;
use app\models\CarAvailability;
use app\models\PromoVisit;
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

        // Conteo de órdenes (no canceladas) por carro, solo aquellas vigentes
        // o futuras a partir de la fecha consultada (no mostrar órdenes pasadas).
        $rentalsByCar = [];
        if (!empty($cars)) {
            $carIds = array_map(static function ($c) { return (int) $c->id; }, $cars);
            $rows = (new \yii\db\Query())
                ->from(Rental::tableName())
                ->select(['car_id', 'cnt' => 'COUNT(*)'])
                ->where(['car_id' => $carIds])
                ->andWhere(['<>', 'estado_pago', 'cancelado'])
                ->andWhere(['or',
                    ['>=', 'fecha_final', $fecha],
                    ['fecha_final' => null],
                ])
                ->groupBy(['car_id'])
                ->all();
            foreach ($rows as $r) {
                $rentalsByCar[(int) $r['car_id']] = (int) $r['cnt'];
            }
        }

        return $this->render('disponibles', [
            'cars' => $cars,
            'fecha' => $fecha,
            'rentalsByCar' => $rentalsByCar,
        ]);
    }

    /**
     * Devuelve las órdenes de alquiler de un vehículo específico (no canceladas),
     * con las más recientes primero. Para el modal del listado de "Disponibles".
     *
     * Respuesta JSON:
     * {
     *   "success": true,
     *   "car": { "id", "nombre", "placa" },
     *   "items": [ { id, rental_id, client_name, fecha_inicio, fecha_final,
     *                hora_inicio, hora_final, estado_pago, total_precio, view_url, update_url } ]
     * }
     */
    public function actionCarRentals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $carId = (int) Yii::$app->request->get('car_id', 0);
        if ($carId <= 0) {
            return ['success' => false, 'message' => 'car_id inválido', 'items' => []];
        }

        // "from" (opcional): excluir órdenes cuya fecha_final sea anterior. Por
        // defecto se usa la fecha actual para no mostrar alquileres ya pasados.
        $from = (string) Yii::$app->request->get('from', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-d');
        }

        $car = Car::findOne($carId);

        $rentals = Rental::find()
            ->with(['client'])
            ->where(['car_id' => $carId])
            ->andWhere(['<>', 'estado_pago', 'cancelado'])
            ->andWhere(['or',
                ['>=', 'fecha_final', $from],
                ['fecha_final' => null],
            ])
            ->orderBy(['fecha_inicio' => SORT_ASC, 'hora_inicio' => SORT_ASC])
            ->all();

        $items = [];
        foreach ($rentals as $r) {
            $client = $r->client ?? null;
            $clientName = '—';
            if ($client) {
                $clientName = trim((string) ($client->full_name ?? ''));
                if ($clientName === '') {
                    $clientName = trim(((string) ($client->nombre ?? '')) . ' ' . ((string) ($client->apellido ?? '')));
                }
                if ($clientName === '') {
                    $clientName = '—';
                }
            }
            $items[] = [
                'id' => (int) $r->id,
                'rental_id' => (string) ($r->rental_id ?: ('R' . $r->id)),
                'client_name' => $clientName,
                'fecha_inicio' => (string) $r->fecha_inicio,
                'fecha_final' => (string) $r->fecha_final,
                'hora_inicio' => (string) $r->hora_inicio,
                'hora_final' => (string) $r->hora_final,
                'estado_pago' => (string) $r->estado_pago,
                'total_precio' => (float) $r->total_precio,
                'view_url' => \yii\helpers\Url::to(['/rental/view', 'id' => $r->id]),
                'update_url' => \yii\helpers\Url::to(['/rental/update', 'id' => $r->id]),
            ];
        }

        return [
            'success' => true,
            'car' => $car ? [
                'id' => (int) $car->id,
                'nombre' => (string) ($car->nombre ?? ''),
                'placa' => (string) ($car->placa ?? ''),
            ] : null,
            'items' => $items,
        ];
    }

    /**
     * Devuelve un resumen agregado por día de alquileres activos para un mes.
     * Se considera "activo" en una fecha cuando fecha_inicio <= dia <= fecha_final.
     * Excluye estados 'cancelado'.
     *
     * Respuesta JSON:
     * {
     *   "month": "YYYY-MM",
     *   "days": {
     *     "YYYY-MM-DD": { "total": int, "by_status": { "pagado": n, "pendiente": n, ... } }
     *   }
     * }
     */
    public function actionCalendarRentals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $month = (string) Yii::$app->request->get('month', date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        // "from" indica desde qué fecha empezar a contar. Por defecto: primer día
        // del mes consultado; si se especifica (ej. la fecha del filtro), se usa
        // como límite inferior para excluir alquileres ya finalizados antes.
        $from = (string) Yii::$app->request->get('from', '');
        if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }

        try {
            $start = \DateTimeImmutable::createFromFormat('Y-m-d', $month . '-01');
            if ($start === false) {
                $start = new \DateTimeImmutable('first day of this month');
            }
            $end = $start->modify('last day of this month');
        } catch (\Throwable $e) {
            $start = new \DateTimeImmutable('first day of this month');
            $end = new \DateTimeImmutable('last day of this month');
        }

        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        // Si "from" cae dentro de este mes, restringe el rango visible.
        $rangeStart = ($from !== '' && $from > $startStr && $from <= $endStr) ? $from : $startStr;

        $rows = Rental::find()
            ->select(['id', 'fecha_inicio', 'fecha_final', 'estado_pago'])
            ->where(['<>', 'estado_pago', 'cancelado'])
            ->andWhere(['<=', 'fecha_inicio', $endStr])
            ->andWhere(['or',
                ['>=', 'fecha_final', $rangeStart],
                ['fecha_final' => null],
            ])
            ->asArray()
            ->all();

        $days = [];
        foreach ($rows as $r) {
            $ini = (string) ($r['fecha_inicio'] ?? '');
            $fin = (string) ($r['fecha_final'] ?? $ini);
            if ($ini === '') continue;

            $cursor = max($ini, $rangeStart);
            $stop = min(($fin !== '' ? $fin : $ini), $endStr);
            if (strtotime($cursor) === false || strtotime($stop) === false) continue;

            $cTs = strtotime($cursor);
            $sTs = strtotime($stop);
            for ($t = $cTs; $t <= $sTs; $t = strtotime('+1 day', $t)) {
                $key = date('Y-m-d', $t);
                if (!isset($days[$key])) {
                    $days[$key] = ['total' => 0, 'by_status' => []];
                }
                $days[$key]['total']++;
                $st = (string) ($r['estado_pago'] ?? 'pendiente');
                $days[$key]['by_status'][$st] = ($days[$key]['by_status'][$st] ?? 0) + 1;
            }
        }

        return [
            'success' => true,
            'month' => $start->format('Y-m'),
            'from' => $rangeStart,
            'days' => $days,
        ];
    }

    /**
     * Devuelve el detalle de los alquileres activos en una fecha específica.
     * "Activo" = fecha_inicio <= fecha <= fecha_final, excluyendo cancelados.
     *
     * Respuesta JSON:
     * {
     *   "success": true,
     *   "date": "YYYY-MM-DD",
     *   "items": [ { id, rental_id, client_name, car_name, car_placa, fecha_inicio, fecha_final,
     *                hora_inicio, hora_final, estado_pago, total_precio, view_url, update_url } ]
     * }
     */
    public function actionCalendarDay()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fecha = (string) Yii::$app->request->get('fecha', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $rentals = Rental::find()
            ->with(['client', 'car'])
            ->where(['<>', 'estado_pago', 'cancelado'])
            ->andWhere(['<=', 'fecha_inicio', $fecha])
            ->andWhere(['or',
                ['>=', 'fecha_final', $fecha],
                ['fecha_final' => null],
            ])
            ->orderBy(['fecha_inicio' => SORT_ASC, 'hora_inicio' => SORT_ASC])
            ->all();

        $items = [];
        foreach ($rentals as $r) {
            $client = $r->client ?? null;
            $car = $r->car ?? null;

            $clientName = '—';
            if ($client) {
                $clientName = trim((string) ($client->full_name ?? ''));
                if ($clientName === '') {
                    $clientName = trim(((string) ($client->nombre ?? '')) . ' ' . ((string) ($client->apellido ?? '')));
                }
                if ($clientName === '') {
                    $clientName = '—';
                }
            }

            $carName = $car ? trim((string) ($car->nombre ?? '')) : '';
            if ($carName === '') {
                $carName = '—';
            }
            $carPlaca = $car ? (string) ($car->placa ?? '') : '';

            $items[] = [
                'id' => (int) $r->id,
                'rental_id' => (string) ($r->rental_id ?: ('R' . $r->id)),
                'client_name' => $clientName,
                'car_name' => $carName,
                'car_placa' => $carPlaca,
                'fecha_inicio' => (string) $r->fecha_inicio,
                'fecha_final' => (string) $r->fecha_final,
                'hora_inicio' => (string) $r->hora_inicio,
                'hora_final' => (string) $r->hora_final,
                'estado_pago' => (string) $r->estado_pago,
                'total_precio' => (float) $r->total_precio,
                'view_url' => \yii\helpers\Url::to(['/rental/view', 'id' => $r->id]),
                'update_url' => \yii\helpers\Url::to(['/rental/update', 'id' => $r->id]),
            ];
        }

        return [
            'success' => true,
            'date' => $fecha,
            'items' => $items,
        ];
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
     * Reporte de campaña Facebook: visitas a los enlaces /promo/{slug},
     * alquileres completados por vehículo y top de visitas.
     */
    public function actionAnalytics()
    {
        $req = Yii::$app->request;

        // La tasa de conversión solo es válida si visitas y alquileres se
        // cuentan en la MISMA ventana. Como el tracking de visitas arrancó hoy,
        // por defecto el rango es [hoy → último día del mes]. El usuario puede
        // ampliar el rango con el filtro si quiere ver datos históricos de alquileres.
        $today = new \DateTimeImmutable('today');
        $defaultStart = $today->format('Y-m-d');
        $defaultEnd = $today->modify('last day of this month')->format('Y-m-d');

        $start = (string) $req->get('start', $defaultStart);
        $end = (string) $req->get('end', $defaultEnd);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
            $start = $defaultStart;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end = $defaultEnd;
        }
        if (strcmp($start, $end) > 0) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
        }

        // Vehículos con promoción Facebook (todos los que tengan slug),
        // independientemente de si están activos hoy, para no perder histórico.
        $promoCars = Car::find()
            ->with(['marca'])
            ->where(['not', ['facebook_promo_slug' => null]])
            ->andWhere(['<>', 'facebook_promo_slug', ''])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $visitsByCar = PromoVisit::countByCarInRange($start, $end);
        $visitsByDay = PromoVisit::countByDayInRange($start, $end);

        // Alquileres "completados" en el periodo: cualquier rental NO cancelado
        // cuya fecha_inicio caiga en el rango. (Métrica más útil para campañas
        // que solo contar 'finalizado'.)
        $rentalsByCar = [];
        if (!empty($promoCars)) {
            $carIds = array_map(static function ($c) { return (int) $c->id; }, $promoCars);
            $rows = (new \yii\db\Query())
                ->from(Rental::tableName())
                ->select(['car_id', 'cnt' => 'COUNT(*)'])
                ->where(['car_id' => $carIds])
                ->andWhere(['<>', 'estado_pago', 'cancelado'])
                ->andWhere(['between', 'fecha_inicio', $start, $end])
                ->groupBy(['car_id'])
                ->all();
            foreach ($rows as $r) {
                $rentalsByCar[(int) $r['car_id']] = (int) $r['cnt'];
            }
        }

        // Totales
        $totalVisits = array_sum($visitsByCar);
        $totalRentals = array_sum($rentalsByCar);
        $activePromos = 0;
        foreach ($promoCars as $c) {
            if ((int) $c->facebook_promo_enabled === 1) {
                $activePromos++;
            }
        }

        // Tabla y ranking
        $rows = [];
        foreach ($promoCars as $c) {
            $id = (int) $c->id;
            $visits = (int) ($visitsByCar[$id] ?? 0);
            $rentals = (int) ($rentalsByCar[$id] ?? 0);
            $rows[] = [
                'car' => $c,
                'visits' => $visits,
                'rentals' => $rentals,
                'conversion' => $visits > 0 ? round(($rentals / $visits) * 100, 1) : 0.0,
            ];
        }
        usort($rows, static function ($a, $b) {
            return $b['visits'] <=> $a['visits'];
        });
        $top = array_slice($rows, 0, 5);

        // Serie diaria para gráfico de línea
        $period = new \DatePeriod(
            new \DateTimeImmutable($start),
            new \DateInterval('P1D'),
            (new \DateTimeImmutable($end))->modify('+1 day')
        );
        $daily = [];
        foreach ($period as $d) {
            /** @var \DateTimeInterface $d */
            $k = $d->format('Y-m-d');
            $daily[$k] = (int) ($visitsByDay[$k] ?? 0);
        }

        return $this->render('analytics', [
            'start' => $start,
            'end' => $end,
            'rows' => $rows,
            'top' => $top,
            'daily' => $daily,
            'totalVisits' => (int) $totalVisits,
            'totalRentals' => (int) $totalRentals,
            'activePromos' => $activePromos,
            'totalPromos' => count($promoCars),
        ]);
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
        $model->facebookBannerFile = UploadedFile::getInstance($model, 'facebookBannerFile');

        if (!$model->validate()) {
            return false;
        }

        $hasNewImage = $model->imagenFile instanceof UploadedFile
            && $model->imagenFile->error !== UPLOAD_ERR_NO_FILE;
        $hasNewBanner = $model->facebookBannerFile instanceof UploadedFile
            && $model->facebookBannerFile->error !== UPLOAD_ERR_NO_FILE;

        if (!$model->save(false)) {
            return false;
        }

        if ($hasNewImage) {
            if (!$model->uploadImagenFile()) {
                return false;
            }
            $model->save(false, ['imagen']);
        }

        if ($hasNewBanner) {
            if (!$model->uploadFacebookBannerFile()) {
                return false;
            }
            $model->save(false, ['facebook_banner']);
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

