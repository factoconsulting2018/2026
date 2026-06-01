<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Client;
use app\models\Car;
use app\models\Rental;
use app\models\Order;

class SiteController extends Controller
{
    /**
     * Retorna el primer nombre de columna existente en la tabla rentals.
     */
    private function resolveRentalColumn(array $candidates): ?string
    {
        try {
            $schema = Rental::getTableSchema();
            if ($schema === null) {
                return null;
            }
            foreach ($candidates as $column) {
                if ($schema->getColumn($column) !== null) {
                    return $column;
                }
            }
        } catch (\Throwable $e) {
            Yii::warning('No se pudo resolver columnas de rentals: ' . $e->getMessage(), __METHOD__);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'portada', 'info'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'logs'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Portada pública (landing). Mismo render que `index` cuando el usuario
     * no está autenticado. Mantenemos la action separada para tener una URL
     * estable a la que enlazar directamente.
     */
    public function actionPortada()
    {
        $this->layout = false;
        return $this->render('portada', [
            'backgroundUrl' => $this->pickDailyBackground(),
        ]);
    }

    /**
     * Página pública "Sobre la empresa" accesible directamente vía `/info`
     * (o `/sobre-la-empresa`). Pensada para compartir en redes sociales y
     * WhatsApp con su propio Open Graph (logo + descripción + contactos).
     */
    public function actionInfo()
    {
        $this->layout = false;
        return $this->render('info', [
            'backgroundUrl' => $this->pickDailyBackground(),
        ]);
    }

    /**
     * Selecciona una imagen de fondo (montaña/playa) de forma determinista
     * según el día del año. Cambia cada día y vuelve a empezar al año siguiente.
     */
    private function pickDailyBackground(): string
    {
        // Fotos libres en Unsplash (montaña + playa). Parámetros &w=1920&q=80
        // entregan ~250 KB optimizadas vía CDN.
        $photos = [
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
            'https://images.unsplash.com/photo-1444930694458-01babe71870e',
            'https://images.unsplash.com/photo-1502082553048-f009c37129b9',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4',
            'https://images.unsplash.com/photo-1418065460487-3956ef138a02',
            'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a',
            'https://images.unsplash.com/photo-1439066615861-d1af74d74000',
            'https://images.unsplash.com/photo-1518837695005-2083093ee35b',
            'https://images.unsplash.com/photo-1454496522488-7a8e488e8606',
            'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6',
            'https://images.unsplash.com/photo-1426604966848-d7adac402bff',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
            'https://images.unsplash.com/photo-1519046904884-53103b34b206',
            'https://images.unsplash.com/photo-1551918120-9739cb430c6d',
            'https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5',
            'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b',
            'https://images.unsplash.com/photo-1542401886-65d6c61db217',
            'https://images.unsplash.com/photo-1493558103817-58b2924bce98',
            'https://images.unsplash.com/photo-1520962880247-cfaf541c8724',
            'https://images.unsplash.com/photo-1502082553048-f009c37129b9',
            'https://images.unsplash.com/photo-1476610182048-b716b8518aae',
            'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800',
            'https://images.unsplash.com/photo-1500964757637-c85e8a162699',
            'https://images.unsplash.com/photo-1455218873509-8097305ee378',
            'https://images.unsplash.com/photo-1542401886-65d6c61db217',
            'https://images.unsplash.com/photo-1414235077428-338989a2e8c0',
            'https://images.unsplash.com/photo-1521295121783-8a321d551ad2',
            'https://images.unsplash.com/photo-1502082553048-f009c37129b9',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
            'https://images.unsplash.com/photo-1519046904884-53103b34b206',
        ];
        $dayOfYear = (int) date('z');
        $url = $photos[$dayOfYear % count($photos)];

        return $url . '?auto=format&fit=crop&w=1920&q=80';
    }

    /**
     * Displays homepage (Dashboard).
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->actionPortada();
        }

        try {
            $dateColumn = $this->resolveRentalColumn(['created_at', 'fecha_inicio', 'updated_at']);
            $amountColumn = $this->resolveRentalColumn(['total_precio', 'precio_por_dia']);
            $statusColumn = $this->resolveRentalColumn(['estado_pago', 'status']);
            $asyncColumn = $this->resolveRentalColumn(['is_async']);

            // Ventas de hoy (usando rentals como "ventas")
            $todaySalesQuery = Rental::find();
            if ($dateColumn !== null) {
                $todaySalesQuery
                    ->where(['>=', $dateColumn, date('Y-m-d 00:00:00')])
                    ->andWhere(['<', $dateColumn, date('Y-m-d 23:59:59')]);
            }
            $todaySales = $todaySalesQuery->count();
            $todayRevenue = $amountColumn !== null ? ($todaySalesQuery->sum($amountColumn) ?: 0) : 0;
            
            // Ventas del mes actual (usando rentals como "ventas")
            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd = date('Y-m-t 23:59:59');
            $monthSales = 0;
            if ($dateColumn !== null && $amountColumn !== null) {
                $monthSales = Rental::find()
                    ->where(['>=', $dateColumn, $monthStart])
                    ->andWhere(['<=', $dateColumn, $monthEnd])
                    ->sum($amountColumn) ?: 0;
            }
            
            // Órdenes pendientes (alquileres con estado pendiente)
            $pendingOrders = 0;
            if ($statusColumn !== null) {
                $pendingOrders = Rental::find()
                    ->where([$statusColumn => 'pendiente'])
                    ->count();
            }
            
            $stats = [
                'total_clients' => Client::find()->count(),
                'total_cars' => Car::find()->count(),
                'active_rentals' => Rental::find()->count(),
                'async_sales' => $asyncColumn !== null ? Rental::find()->where([$asyncColumn => 1])->count() : 0,
                'today_sales' => $todaySales, // Ventas de hoy (cantidad)
                'today_revenue' => $todayRevenue, // Ventas de hoy (monto)
                'month_revenue' => $monthSales, // Ventas del mes
                'pending_orders' => $pendingOrders, // Órdenes pendientes
            ];
            
            // Últimos alquileres (sin relaciones por ahora)
            $recentRentalsQuery = Rental::find()->with(['client', 'car']);
            if ($dateColumn !== null) {
                $recentRentalsQuery->orderBy([$dateColumn => SORT_DESC]);
            } else {
                $recentRentalsQuery->orderBy(['id' => SORT_DESC]);
            }
            $recentRentals = $recentRentalsQuery->limit(5)->all();
                
        } catch (\Exception $e) {
            // Si hay error, usar valores por defecto
            $stats = [
                'total_clients' => 0,
                'total_cars' => 0,
                'active_rentals' => 0,
                'async_sales' => 0,
                'today_sales' => 0,
                'today_revenue' => 0,
                'month_revenue' => 0,
                'pending_orders' => 0,
            ];
            $recentRentals = [];
            
            // Log del error para debug
            Yii::error('Error en dashboard: ' . $e->getMessage(), __METHOD__);
        }
        
        return $this->render('index', [
            'stats' => $stats,
            'recentRentals' => $recentRentals,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            \app\components\IncidentNotificationHelper::onSuccessfulLogin();
            return $this->redirect(['/site/index']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionLogs()
    {
        $logFile = Yii::getAlias('@app/runtime/logs/app.log');
        $content = '';

        if (file_exists($logFile)) {
            // Leer las últimas 1000 líneas del log
            $lines = file($logFile);
            $content = implode('', array_slice($lines, -1000));
        }

        return $this->render('logs', [
            'content' => $content,
            'logFile' => $logFile
        ]);
    }
}
