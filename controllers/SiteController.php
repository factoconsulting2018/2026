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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'logs'],
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
     * Displays homepage (Dashboard).
     *
     * @return string
     */
    public function actionIndex()
    {
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
            $recentRentalsQuery = Rental::find();
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
