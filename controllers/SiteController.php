<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Usuario;
use app\models\Client;
use app\models\Car;
use app\models\Rental;
use app\models\Order;

class SiteController extends Controller
{
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
            // Ventas de hoy (usando rentals como "ventas")
            $todaySales = Rental::find()
                ->where(['>=', 'created_at', date('Y-m-d 00:00:00')])
                ->andWhere(['<', 'created_at', date('Y-m-d 23:59:59')])
                ->count();
            
            // Ventas del mes actual (usando rentals como "ventas")
            $monthStart = date('Y-m-01 00:00:00');
            $monthEnd = date('Y-m-t 23:59:59');
            $monthSales = Rental::find()
                ->where(['>=', 'created_at', $monthStart])
                ->andWhere(['<=', 'created_at', $monthEnd])
                ->sum('total_precio') ?: 0;
            
            // Órdenes pendientes (alquileres con estado pendiente)
            $pendingOrders = Rental::find()
                ->where(['estado_pago' => 'pendiente'])
                ->count();
            
            $stats = [
                'total_clients' => Client::find()->count(),
                'total_cars' => Car::find()->count(),
                'active_rentals' => Rental::find()->count(),
                'total_users' => Usuario::find()->where(['activo' => 1])->count(),
                'today_sales' => $todaySales, // Ventas de hoy
                'month_revenue' => $monthSales, // Ventas del mes
                'pending_orders' => $pendingOrders, // Órdenes pendientes
            ];
            
            // Últimos alquileres (sin relaciones por ahora)
            $recentRentals = Rental::find()
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(5)
                ->all();
                
        } catch (\Exception $e) {
            // Si hay error, usar valores por defecto
            $stats = [
                'total_clients' => 0,
                'total_cars' => 0,
                'active_rentals' => 0,
                'total_users' => 1,
                'today_sales' => 0,
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
