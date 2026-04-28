<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use app\components\ApiKeyAuth;
use app\models\Client;
use app\models\Car;
use app\models\Rental;
use app\models\Note;
use app\components\HaciendaApi;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * ApiController maneja todos los endpoints REST de la API
 */
class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $allowedOrigins = Yii::$app->params['corsAllowedOrigins'] ?? [];
        if (!is_array($allowedOrigins) || $allowedOrigins === []) {
            $allowedOrigins = ['https://app.factorentacar.com'];
        }

        return [
            // CORS debe ir primero: responde OPTIONS sin pasar por autenticación.
            'cors' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => $allowedOrigins,
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-client' => ['POST'],
                    'update-client' => ['PUT', 'PATCH'],
                    'delete-client' => ['DELETE'],
                    'create-rental' => ['POST'],
                    'update-rental' => ['PUT', 'PATCH'],
                    'create-note' => ['POST'],
                ],
            ],
            'authenticator' => [
                'class' => ApiKeyAuth::class,
                'except' => ['health'], // Health check no requiere autenticación
            ],
        ];
    }

    /**
     * Desactivar CSRF para API
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Formatea respuesta exitosa
     */
    private function successResponse($data, $meta = null)
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];
        
        if ($meta !== null && !empty($meta)) {
            $response['meta'] = $meta;
        }
        
        return $response;
    }

    /**
     * Formatea respuesta de error
     */
    private function errorResponse($message, $code = 'ERROR', $details = [], $statusCode = 400)
    {
        Yii::$app->response->statusCode = $statusCode;
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ];
    }

    /**
     * Health check - No requiere autenticación
     */
    public function actionHealth()
    {
        return $this->successResponse([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => Yii::$app->params['appVersion'] ?? '1.0',
        ]);
    }

    // ==================== CLIENTES ====================

    /**
     * GET /api/v1/clients
     * Listar clientes
     */
    public function actionClients()
    {
        try {
            $query = Client::find();
            
            // Filtros
            $status = Yii::$app->request->get('status');
            if ($status) {
                $query->andWhere(['status' => $status]);
            }
            
            $search = Yii::$app->request->get('search');
            if ($search) {
                $query->andWhere([
                    'or',
                    ['like', 'full_name', $search],
                    ['like', 'cedula_fisica', $search],
                    ['like', 'email', $search],
                ]);
            }
            
            // Paginación
            $page = (int)Yii::$app->request->get('page', 1);
            $perPage = (int)Yii::$app->request->get('per_page', 20);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'page' => $page - 1,
                    'pageSize' => $perPage,
                ],
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                ],
            ]);
            
            $clients = [];
            foreach ($dataProvider->getModels() as $client) {
                $clients[] = $this->formatClient($client);
            }
            
            return $this->successResponse($clients, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $dataProvider->totalCount,
                'total_pages' => ceil($dataProvider->totalCount / $perPage),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionClients: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener clientes', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/clients/{id}
     * Obtener cliente específico
     */
    public function actionClient($id)
    {
        try {
            $client = Client::findOne($id);
            if (!$client) {
                return $this->errorResponse('Cliente no encontrado', 'NOT_FOUND', [], 404);
            }
            
            return $this->successResponse($this->formatClient($client));
            
        } catch (\Exception $e) {
            Yii::error('Error en actionClient: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener cliente', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * POST /api/v1/clients
     * Crear cliente
     */
    public function actionCreateClient()
    {
        try {
            $model = new Client();
            $data = Yii::$app->request->post();
            
            if ($model->load($data, '') && $model->save()) {
                Yii::$app->response->statusCode = 201;
                return $this->successResponse($this->formatClient($model));
            }
            
            return $this->errorResponse('Error al crear cliente', 'VALIDATION_ERROR', $model->errors, 422);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCreateClient: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al crear cliente', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * PUT /api/v1/clients/{id}
     * Actualizar cliente
     */
    public function actionUpdateClient($id)
    {
        try {
            $model = Client::findOne($id);
            if (!$model) {
                return $this->errorResponse('Cliente no encontrado', 'NOT_FOUND', [], 404);
            }
            
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->save()) {
                return $this->successResponse($this->formatClient($model));
            }
            
            return $this->errorResponse('Error al actualizar cliente', 'VALIDATION_ERROR', $model->errors, 422);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateClient: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al actualizar cliente', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * DELETE /api/v1/clients/{id}
     * Eliminar cliente
     */
    public function actionDeleteClient($id)
    {
        try {
            $model = Client::findOne($id);
            if (!$model) {
                return $this->errorResponse('Cliente no encontrado', 'NOT_FOUND', [], 404);
            }
            
            if ($model->delete()) {
                return $this->successResponse(['message' => 'Cliente eliminado exitosamente']);
            }
            
            return $this->errorResponse('Error al eliminar cliente', 'DELETE_ERROR', [], 500);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionDeleteClient: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al eliminar cliente', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Formatea un cliente para la respuesta
     */
    private function formatClient($client)
    {
        return [
            'id' => $client->id,
            'client_id' => $client->client_id,
            'full_name' => $client->full_name,
            'nombre' => $client->nombre,
            'apellido' => $client->apellido,
            'cedula_fisica' => $client->cedula_fisica,
            'email' => $client->email,
            'telefono' => $client->telefono,
            'celular' => $client->celular,
            'whatsapp' => $client->whatsapp,
            'address' => $client->address,
            'status' => $client->status,
            'tipo_identificacion' => $client->tipo_identificacion,
            'situacion_tributaria' => $client->situacion_tributaria,
            'regimen_tributario' => $client->regimen_tributario,
            'actividad_economica_codigo' => $client->actividad_economica_codigo,
            'actividad_economica_descripcion' => $client->actividad_economica_descripcion,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
        ];
    }

    // ==================== VEHÍCULOS ====================

    /**
     * GET /api/v1/cars
     * Listar vehículos
     */
    public function actionCars()
    {
        try {
            $query = Car::find();
            
            // Filtros
            $status = Yii::$app->request->get('status');
            if ($status) {
                $query->andWhere(['status' => $status]);
            }
            
            $empresa = Yii::$app->request->get('empresa');
            if ($empresa) {
                $query->andWhere(['empresa' => $empresa]);
            }
            
            $search = Yii::$app->request->get('search');
            if ($search) {
                $query->andWhere([
                    'or',
                    ['like', 'nombre', $search],
                    ['like', 'placa', $search],
                    ['like', 'vin', $search],
                ]);
            }
            
            // Paginación
            $page = (int)Yii::$app->request->get('page', 1);
            $perPage = (int)Yii::$app->request->get('per_page', 20);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'page' => $page - 1,
                    'pageSize' => $perPage,
                ],
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                ],
            ]);
            
            $cars = [];
            foreach ($dataProvider->getModels() as $car) {
                $cars[] = $this->formatCar($car);
            }
            
            return $this->successResponse($cars, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $dataProvider->totalCount,
                'total_pages' => ceil($dataProvider->totalCount / $perPage),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCars: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener vehículos', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/cars/{id}
     * Obtener vehículo específico
     */
    public function actionCar($id)
    {
        try {
            $car = Car::findOne($id);
            if (!$car) {
                return $this->errorResponse('Vehículo no encontrado', 'NOT_FOUND', [], 404);
            }
            
            return $this->successResponse($this->formatCar($car));
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCar: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener vehículo', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/cars/available
     * Vehículos disponibles
     */
    public function actionAvailableCars()
    {
        try {
            $fechaInicio = Yii::$app->request->get('fecha_inicio');
            $fechaFinal = Yii::$app->request->get('fecha_final');
            
            $query = Car::find()->where(['status' => 'disponible']);
            
            // Si se proporcionan fechas, verificar disponibilidad
            if ($fechaInicio && $fechaFinal) {
                // Buscar vehículos que tengan alquileres en ese rango
                $rentedCarIds = Rental::find()
                    ->select('car_id')
                    ->where(['and',
                        ['<=', 'fecha_inicio', $fechaFinal],
                        ['>=', 'fecha_final', $fechaInicio],
                    ])
                    ->column();
                
                if (!empty($rentedCarIds)) {
                    $query->andWhere(['not in', 'id', $rentedCarIds]);
                }
            }
            
            $cars = $query->all();
            $formatted = [];
            foreach ($cars as $car) {
                $formatted[] = $this->formatCar($car);
            }
            
            return $this->successResponse($formatted);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionAvailableCars: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener vehículos disponibles', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Formatea un vehículo para la respuesta
     */
    private function formatCar($car)
    {
        return [
            'id' => $car->id,
            'car_id' => $car->car_id,
            'nombre' => $car->nombre,
            'placa' => $car->placa,
            'vin' => $car->vin,
            'marca_id' => $car->marca_id,
            'cantidad_pasajeros' => $car->cantidad_pasajeros,
            'caracteristicas' => $car->caracteristicas,
            'empresa_seguro' => $car->empresa_seguro,
            'telefono_seguro' => $car->telefono_seguro,
            'empresa' => $car->empresa,
            'status' => $car->status,
            'created_at' => $car->created_at,
            'updated_at' => $car->updated_at,
        ];
    }

    // ==================== RESERVAS/ALQUILERES ====================

    /**
     * GET /api/v1/rentals
     * Listar reservas/alquileres
     */
    public function actionRentals()
    {
        try {
            $query = Rental::find()->with(['client', 'car']);
            
            // Filtros
            $estadoPago = Yii::$app->request->get('estado_pago');
            if ($estadoPago) {
                $query->andWhere(['estado_pago' => $estadoPago]);
            }
            
            $startDate = Yii::$app->request->get('start_date');
            if ($startDate) {
                $query->andWhere(['>=', 'created_at', $startDate]);
            }
            
            $endDate = Yii::$app->request->get('end_date');
            if ($endDate) {
                $query->andWhere(['<=', 'created_at', $endDate]);
            }
            
            $clientId = Yii::$app->request->get('client_id');
            if ($clientId) {
                $query->andWhere(['client_id' => $clientId]);
            }
            
            $carId = Yii::$app->request->get('car_id');
            if ($carId) {
                $query->andWhere(['car_id' => $carId]);
            }
            
            // Paginación
            $page = (int)Yii::$app->request->get('page', 1);
            $perPage = (int)Yii::$app->request->get('per_page', 20);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'page' => $page - 1,
                    'pageSize' => $perPage,
                ],
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                ],
            ]);
            
            $rentals = [];
            foreach ($dataProvider->getModels() as $rental) {
                $rentals[] = $this->formatRental($rental);
            }
            
            return $this->successResponse($rentals, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $dataProvider->totalCount,
                'total_pages' => ceil($dataProvider->totalCount / $perPage),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionRentals: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener reservas', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/rentals/{id}
     * Obtener reserva específica
     */
    public function actionRental($id)
    {
        try {
            $rental = Rental::find()->with(['client', 'car'])->where(['id' => $id])->one();
            if (!$rental) {
                return $this->errorResponse('Reserva no encontrada', 'NOT_FOUND', [], 404);
            }
            
            return $this->successResponse($this->formatRental($rental));
            
        } catch (\Exception $e) {
            Yii::error('Error en actionRental: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener reserva', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * POST /api/v1/rentals
     * Crear reserva
     */
    public function actionCreateRental()
    {
        try {
            $model = new Rental();
            $data = Yii::$app->request->post();
            
            if ($model->load($data, '') && $model->save()) {
                $model->refresh();
                
                // Actualizar estado del vehículo
                if ($model->car) {
                    $model->car->status = 'alquilado';
                    $model->car->save(false);
                }
                
                Yii::$app->response->statusCode = 201;
                return $this->successResponse($this->formatRental($model));
            }
            
            return $this->errorResponse('Error al crear reserva', 'VALIDATION_ERROR', $model->errors, 422);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCreateRental: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al crear reserva', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * PUT /api/v1/rentals/{id}
     * Actualizar reserva
     */
    public function actionUpdateRental($id)
    {
        try {
            $model = Rental::findOne($id);
            if (!$model) {
                return $this->errorResponse('Reserva no encontrada', 'NOT_FOUND', [], 404);
            }
            
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->save()) {
                return $this->successResponse($this->formatRental($model));
            }
            
            return $this->errorResponse('Error al actualizar reserva', 'VALIDATION_ERROR', $model->errors, 422);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateRental: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al actualizar reserva', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Formatea una reserva para la respuesta
     */
    private function formatRental($rental)
    {
        return [
            'id' => $rental->id,
            'rental_id' => $rental->rental_id,
            'client_id' => $rental->client_id,
            'client' => $rental->client ? [
                'id' => $rental->client->id,
                'full_name' => $rental->client->full_name,
                'cedula_fisica' => $rental->client->cedula_fisica,
            ] : null,
            'car_id' => $rental->car_id,
            'car' => $rental->car ? [
                'id' => $rental->car->id,
                'nombre' => $rental->car->nombre,
                'placa' => $rental->car->placa,
            ] : null,
            'fecha_inicio' => $rental->fecha_inicio,
            'hora_inicio' => $rental->hora_inicio,
            'fecha_final' => $rental->fecha_final,
            'hora_final' => $rental->hora_final,
            'cantidad_dias' => $rental->cantidad_dias,
            'precio_por_dia' => (float)($rental->precio_por_dia ?? 0),
            'total_precio' => (float)($rental->total_precio ?? 0),
            'estado_pago' => $rental->estado_pago,
            'comprobante_pago' => $rental->comprobante_pago,
            'lugar_entrega' => $rental->lugar_entrega,
            'lugar_retiro' => $rental->lugar_retiro,
            'ejecutivo' => $rental->ejecutivo,
            'created_at' => $rental->created_at,
            'updated_at' => $rental->updated_at,
        ];
    }

    // ==================== REPORTES ====================

    /**
     * GET /api/v1/reports/sales
     * Reporte de ventas
     */
    public function actionReportsSales()
    {
        try {
            $startDate = Yii::$app->request->get('start_date');
            $endDate = Yii::$app->request->get('end_date');
            
            $query = Rental::find()->with(['client', 'car']);
            
            if ($startDate) {
                $query->andWhere(['>=', 'created_at', $startDate]);
            }
            if ($endDate) {
                $query->andWhere(['<=', 'created_at', $endDate]);
            }
            
            $rentals = $query->all();
            
            $totalRevenue = 0;
            $sales = [];
            
            foreach ($rentals as $rental) {
                $totalRevenue += $rental->total_precio ?? 0;
                $sales[] = [
                    'id' => $rental->id,
                    'rental_id' => $rental->rental_id,
                    'client_name' => $rental->client ? $rental->client->full_name : null,
                    'car_name' => $rental->car ? $rental->car->nombre : null,
                    'fecha_inicio' => $rental->fecha_inicio,
                    'fecha_final' => $rental->fecha_final,
                    'total_precio' => (float)($rental->total_precio ?? 0),
                    'estado_pago' => $rental->estado_pago,
                    'created_at' => $rental->created_at,
                ];
            }
            
            return $this->successResponse([
                'total_revenue' => $totalRevenue,
                'total_sales' => count($sales),
                'sales' => $sales,
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionReportsSales: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al generar reporte de ventas', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/reports/clients
     * Reporte de clientes
     */
    public function actionReportsClients()
    {
        try {
            $status = Yii::$app->request->get('status', 'active');
            
            $query = Client::find()->where(['status' => $status]);
            $clients = $query->all();
            
            $formatted = [];
            foreach ($clients as $client) {
                $formatted[] = [
                    'id' => $client->id,
                    'full_name' => $client->full_name,
                    'cedula_fisica' => $client->cedula_fisica,
                    'email' => $client->email,
                    'whatsapp' => $client->whatsapp,
                    'status' => $client->status,
                    'created_at' => $client->created_at,
                ];
            }
            
            return $this->successResponse([
                'total_clients' => count($formatted),
                'clients' => $formatted,
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionReportsClients: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al generar reporte de clientes', 'SERVER_ERROR', [], 500);
        }
    }

    // ==================== ESTADÍSTICAS ====================

    /**
     * GET /api/v1/statistics
     * Estadísticas generales
     */
    public function actionStatistics()
    {
        try {
            $totalClients = Client::find()->count();
            $activeClients = Client::find()->where(['status' => 'active'])->count();
            $totalCars = Car::find()->count();
            $availableCars = Car::find()->where(['status' => 'disponible'])->count();
            $totalRentals = Rental::find()->count();
            
            $totalRevenue = (float)Rental::find()
                ->where(['estado_pago' => 'pagado'])
                ->sum('total_precio') ?? 0;
            
            return $this->successResponse([
                'clients' => [
                    'total' => $totalClients,
                    'active' => $activeClients,
                ],
                'cars' => [
                    'total' => $totalCars,
                    'available' => $availableCars,
                ],
                'rentals' => [
                    'total' => $totalRentals,
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                ],
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionStatistics: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener estadísticas', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * GET /api/v1/statistics/metrics
     * Métricas detalladas
     */
    public function actionStatisticsMetrics()
    {
        try {
            $startDate = Yii::$app->request->get('start_date');
            $endDate = Yii::$app->request->get('end_date');
            
            $query = Rental::find();
            
            if ($startDate) {
                $query->andWhere(['>=', 'created_at', $startDate]);
            }
            if ($endDate) {
                $query->andWhere(['<=', 'created_at', $endDate]);
            }
            
            $allRentals = $query->all();
            
            $metrics = [
                'total_rentals' => count($allRentals),
                'total_revenue' => 0,
                'rentals_by_status' => [],
                'rentals_by_month' => [],
            ];
            
            foreach ($allRentals as $rental) {
                $metrics['total_revenue'] += $rental->total_precio ?? 0;
                
                $status = $rental->estado_pago ?? 'pendiente';
                if (!isset($metrics['rentals_by_status'][$status])) {
                    $metrics['rentals_by_status'][$status] = 0;
                }
                $metrics['rentals_by_status'][$status]++;
                
                if ($rental->created_at) {
                    $month = date('Y-m', strtotime($rental->created_at));
                    if (!isset($metrics['rentals_by_month'][$month])) {
                        $metrics['rentals_by_month'][$month] = 0;
                    }
                    $metrics['rentals_by_month'][$month]++;
                }
            }
            
            return $this->successResponse($metrics);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionStatisticsMetrics: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener métricas', 'SERVER_ERROR', [], 500);
        }
    }

    // ==================== NOTAS ====================

    /**
     * GET /api/v1/notes
     * Listar notas
     */
    public function actionNotes()
    {
        try {
            $query = Note::find();
            
            $status = Yii::$app->request->get('status');
            if ($status) {
                $query->andWhere(['status' => $status]);
            }
            
            $page = (int)Yii::$app->request->get('page', 1);
            $perPage = (int)Yii::$app->request->get('per_page', 20);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'page' => $page - 1,
                    'pageSize' => $perPage,
                ],
                'sort' => [
                    'defaultOrder' => ['created_at' => SORT_DESC],
                ],
            ]);
            
            $notes = [];
            foreach ($dataProvider->getModels() as $note) {
                $notes[] = $this->formatNote($note);
            }
            
            return $this->successResponse($notes, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $dataProvider->totalCount,
                'total_pages' => ceil($dataProvider->totalCount / $perPage),
            ]);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionNotes: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al obtener notas', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * POST /api/v1/notes
     * Crear nota
     */
    public function actionCreateNote()
    {
        try {
            $model = new Note();
            $data = Yii::$app->request->post();
            
            if ($model->load($data, '') && $model->save()) {
                Yii::$app->response->statusCode = 201;
                return $this->successResponse($this->formatNote($model));
            }
            
            return $this->errorResponse('Error al crear nota', 'VALIDATION_ERROR', $model->errors, 422);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionCreateNote: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al crear nota', 'SERVER_ERROR', [], 500);
        }
    }

    /**
     * Formatea una nota para la respuesta
     */
    private function formatNote($note)
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'color' => $note->color,
            'status' => $note->status,
            'position_x' => $note->position_x,
            'position_y' => $note->position_y,
            'order' => $note->order,
            'created_at' => $note->created_at,
            'updated_at' => $note->updated_at,
        ];
    }

    // ==================== HACIENDA ====================

    /**
     * GET /api/v1/hacienda/{cedula}
     * Consultar información de Hacienda
     */
    public function actionHacienda($cedula)
    {
        try {
            if (empty($cedula)) {
                return $this->errorResponse('Cédula requerida', 'VALIDATION_ERROR', [], 400);
            }
            
            $rawData = HaciendaApi::consultarCedula($cedula);
            
            if ($rawData && !empty($rawData)) {
                $formattedData = HaciendaApi::formatResponse($rawData);
                
                if ($formattedData && isset($formattedData['ok']) && $formattedData['ok']) {
                    return $this->successResponse([
                        'cedula' => $cedula,
                        'nombre' => $formattedData['nombre'] ?? '',
                        'tipo_identificacion' => $formattedData['tipoIdentificacion'] ?? '',
                        'situacion_tributaria' => $formattedData['situacionTributaria'] ?? '',
                        'regimen_tributario' => $formattedData['regimenTributario'] ?? '',
                        'actividad_economica' => $formattedData['actividadEconomica'] ?? [],
                    ]);
                }
            }
            
            return $this->errorResponse('No se encontró información para esta cédula', 'NOT_FOUND', [], 404);
            
        } catch (\Exception $e) {
            Yii::error('Error en actionHacienda: ' . $e->getMessage(), 'api');
            return $this->errorResponse('Error al consultar Hacienda', 'SERVER_ERROR', [], 500);
        }
    }
}

