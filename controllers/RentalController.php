<?php
namespace app\controllers;

use Yii;
use app\models\Rental;
use app\models\Client;
use app\models\Car;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\web\Response;
use app\models\CarAvailability;

class RentalController extends Controller
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
                    'update-payment-status' => ['POST'],
                    'get-available-cars' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        // Crear query base con relaciones
        $query = Rental::find()
            ->with(['client', 'car'])
            ->orderBy(['id' => SORT_DESC]);
        
        // Aplicar filtro de estado si existe
        $estado_pago = Yii::$app->request->get('estado_pago');
        if ($estado_pago) {
            $query->andWhere(['estado_pago' => $estado_pago]);
        }
        
        // Asegurar que todos los alquileres tengan rental_id
        $this->ensureRentalIds();
        
        // Crear DataProvider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'pageParam' => 'page',
                'pageSizeParam' => 'per-page'
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'rental_id',
                    'client_id',
                    'car_id',
                    'fecha_inicio',
                    'fecha_final',
                    'estado_pago',
                    'total_precio',
                    'created_at'
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $estado_pago,
        ]);
    }
    
    /**
     * Asegurar que todos los alquileres tengan rental_id
     */
    private function ensureRentalIds()
    {
        try {
            $rentals = Rental::find()->where(['or', ['rental_id' => null], ['rental_id' => '']])->all();
            
            foreach ($rentals as $rental) {
                // Generar nuevo rental_id
                $timestamp = substr(time(), -3);
                $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $newRentalId = 'R' . $timestamp . $random;
                
                // Verificar que no exista
                while (Rental::find()->where(['rental_id' => $newRentalId])->exists()) {
                    $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                    $newRentalId = 'R' . $timestamp . $random;
                }
                
                $rental->rental_id = $newRentalId;
                $rental->save(false);
            }
        } catch (Exception $e) {
            // Log error pero no interrumpir la ejecución
            Yii::error('Error al generar rental_ids: ' . $e->getMessage());
        }
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Rental();

        if ($model->load(Yii::$app->request->post())) {
            // Debug: Log de los datos recibidos
            Yii::info('DEBUG - Datos POST recibidos: ' . json_encode(Yii::$app->request->post()), 'rental');
            Yii::info('DEBUG - Modelo cargado - car_id: ' . $model->car_id . ', fecha_inicio: ' . $model->fecha_inicio . ', cantidad_dias: ' . $model->cantidad_dias, 'rental');
            
            if ($model->save()) {
                // Actualizar estado del carro
                if ($model->car) {
                    $model->car->status = 'alquilado';
                    $model->car->save(false);
                }
                
                // Generar PDF automáticamente al crear la orden
                $this->generateOrderPdf($model->id);
                
                // Generar ZIP automáticamente en background (sin bloquear)
                $this->generateOrderZip($model->id);
                
                Yii::$app->session->setFlash('success', '✅ Alquiler creado exitosamente. El archivo ZIP se está generando en segundo plano.');
                
                // Redirigir a la vista normal
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Debug: Log de errores de validación
                Yii::info('DEBUG - Errores de validación: ' . json_encode($model->errors), 'rental');
                Yii::$app->session->setFlash('error', '❌ Error al crear el alquiler. Verifique los datos ingresados.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'clients' => Client::find()->where(['status' => 'active'])->all(),
            'cars' => Car::find()->where(['status' => 'disponible'])->all(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        // Debug: Verificar los valores de la fecha antes de cargar el formulario
        Yii::info("DEBUG - Fecha de inicio cargada: " . $model->fecha_inicio, 'rental');
        Yii::info("DEBUG - Fecha final cargada: " . $model->fecha_final, 'rental');

        if ($model->load(Yii::$app->request->post())) {
            // Debug: Verificar los valores después de cargar el POST
            Yii::info("DEBUG - Fecha de inicio POST: " . $model->fecha_inicio, 'rental');
            
            // Validar que fecha_inicio no esté vacía antes de guardar
            if (empty($model->fecha_inicio) || $model->fecha_inicio === '0000-00-00') {
                $model->addError('fecha_inicio', 'La fecha de inicio es requerida.');
            } else {
                // Validar que la fecha no sea en el pasado
                if (strtotime($model->fecha_inicio) < strtotime('today')) {
                    $model->addError('fecha_inicio', 'La fecha de inicio no puede ser en el pasado.');
                }
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '✅ Alquiler actualizado exitosamente');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Mostrar errores de validación
                $errors = [];
                foreach ($model->errors as $field => $fieldErrors) {
                    $errors[] = $field . ': ' . implode(', ', $fieldErrors);
                }
                Yii::$app->session->setFlash('error', '❌ Error al actualizar alquiler: ' . implode('; ', $errors));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'clients' => Client::find()->where(['status' => 'active'])->all(),
            'cars' => Car::find()->all(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->estado_pago = 'cancelado';
        $model->save(false);
        
        // Liberar el carro
        if ($model->car) {
            $model->car->status = 'disponible';
            $model->car->save(false);
        }

        Yii::$app->session->setFlash('success', '🗑️ Alquiler cancelado exitosamente');
        return $this->redirect(['index']);
    }

    /**
     * Actualizar estado de pago y subir comprobante
     */
    public function actionUpdatePaymentStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $rentalId = Yii::$app->request->post('rentalId');
            $newStatus = Yii::$app->request->post('newStatus');
            $observaciones = Yii::$app->request->post('observaciones', '');
            
            if (!$rentalId || !$newStatus) {
                return [
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos'
                ];
            }
            
            // Buscar el alquiler
            $model = $this->findModel($rentalId);
            
            // Validar estados permitidos
            $allowedStatuses = ['pendiente', 'pagado', 'reservado', 'cancelado'];
            if (!in_array($newStatus, $allowedStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Estado de pago no válido'
                ];
            }
            
            // Obtener archivo de comprobante si se subió
            $comprobanteFile = UploadedFile::getInstanceByName('comprobanteFile');
            $comprobantePath = null;
            
            if ($comprobanteFile) {
                // Validar tipo de archivo
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!in_array($comprobanteFile->type, $allowedTypes)) {
                    return [
                        'success' => false,
                        'message' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG, PDF, DOC y DOCX.'
                    ];
                }
                
                // Validar tamaño (10MB máximo)
                if ($comprobanteFile->size > 10 * 1024 * 1024) {
                    return [
                        'success' => false,
                        'message' => 'El archivo es demasiado grande. El tamaño máximo es 10MB.'
                    ];
                }
                
                // Crear directorio si no existe
                $uploadDir = Yii::getAlias('@webroot/uploads/comprobantes');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generar nombre único para el archivo
                $extension = $comprobanteFile->extension;
                $fileName = 'comprobante_' . $model->id . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . '/' . $fileName;
                
                // Mover archivo
                if ($comprobanteFile->saveAs($filePath)) {
                    $comprobantePath = 'uploads/comprobantes/' . $fileName;
                    
                    // Eliminar comprobante anterior si existe
                    if ($model->comprobante_pago) {
                        $oldFilePath = Yii::getAlias('@webroot/' . $model->comprobante_pago);
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al guardar el archivo'
                    ];
                }
            }
            
            // Actualizar estado de pago
            $oldStatus = $model->estado_pago;
            $model->estado_pago = $newStatus;
            
            // Actualizar comprobante si se subió uno nuevo
            if ($comprobantePath) {
                $model->comprobante_pago = $comprobantePath;
            }
            
            // Guardar cambios
            if ($model->save(false)) {
                // Log del cambio
                $logMessage = sprintf(
                    'Estado de pago cambiado de "%s" a "%s" para alquiler ID: %s',
                    $oldStatus,
                    $newStatus,
                    $model->rental_id
                );
                
                if ($observaciones) {
                    $logMessage .= " - Observaciones: " . $observaciones;
                }
                
                Yii::info($logMessage, 'rental_payment_status_change');
                
                // Actualizar estado del vehículo si es necesario
                if ($model->car) {
                    if ($newStatus === 'pagado' || $newStatus === 'reservado') {
                        $model->car->status = 'alquilado';
                    } elseif ($newStatus === 'cancelado') {
                        $model->car->status = 'disponible';
                    }
                    $model->car->save(false);
                }
                
                return [
                    'success' => true,
                    'message' => 'Estado de pago actualizado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar los cambios'
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error('Error al actualizar estado de pago: ' . $e->getMessage(), 'rental_payment_status_error');
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Obtener información del comprobante actual
     */
    public function actionGetComprobanteInfo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $id = Yii::$app->request->get('id');
            
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID requerido'
                ];
            }
            
            $model = $this->findModel($id);
            
            if ($model->hasComprobante()) {
                return [
                    'success' => true,
                    'comprobante' => [
                        'url' => $model->getComprobanteUrl(),
                        'fileName' => $model->getComprobanteFileName(),
                        'sizeFormatted' => $model->getComprobanteSizeFormatted(),
                        'isImage' => $model->isComprobanteImage()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No hay comprobante'
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error('Error al obtener información del comprobante: ' . $e->getMessage(), 'rental_comprobante_info_error');
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Obtener disponibilidad de vehículos para un mes específico
     */
    public function actionAvailability()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $month = Yii::$app->request->get('month', date('Y-m'));
        $carId = Yii::$app->request->get('car_id');
        
        try {
            // Obtener alquileres activos para el mes
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $query = Rental::find()
                ->where(['and',
                    ['estado_pago' => ['pagado', 'reservado']],
                    ['or',
                        ['and',
                            ['<=', 'fecha_inicio', $endDate],
                            ['>=', 'fecha_final', $startDate]
                        ]
                    ]
                ]);
            
            if ($carId) {
                $query->andWhere(['car_id' => $carId]);
            }
            
            $rentals = $query->with(['car', 'client'])->all();
            
            // Procesar datos para el calendario
            $availabilityData = [];
            $cars = Car::find()->all();
            
            foreach ($cars as $car) {
                $availabilityData[$car->id] = [
                    'car' => $car,
                    'rentals' => [],
                    'available_dates' => [],
                    'occupied_dates' => []
                ];
            }
            
            // Marcar fechas ocupadas
            foreach ($rentals as $rental) {
                if (!isset($availabilityData[$rental->car_id])) {
                    continue;
                }
                
                $start = new \DateTime($rental->fecha_inicio);
                $end = new \DateTime($rental->fecha_final);
                
                while ($start <= $end) {
                    $dateStr = $start->format('Y-m-d');
                    $availabilityData[$rental->car_id]['occupied_dates'][] = $dateStr;
                    $availabilityData[$rental->car_id]['rentals'][] = [
                        'date' => $dateStr,
                        'rental' => $rental,
                        'client' => $rental->client->full_name
                    ];
                    $start->add(new \DateInterval('P1D'));
                }
            }
            
            return [
                'success' => true,
                'data' => $availabilityData,
                'month' => $month
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener disponibilidad: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar disponibilidad de un vehículo en fechas específicas
     */
    public function actionCheckAvailability()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $carId = Yii::$app->request->post('car_id');
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $excludeId = Yii::$app->request->post('exclude_id');
        
        if (!$carId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Faltan parámetros requeridos.'
            ];
        }
        
        try {
            $validation = CarAvailability::validateRentalDates($carId, $startDate, $endDate, $excludeId);
            
            if ($validation['valid']) {
                // Obtener el próximo período disponible si no está disponible
                $nextPeriod = CarAvailability::getNextAvailablePeriod($carId, 7); // 7 días por defecto
                
                return [
                    'success' => true,
                    'available' => true,
                    'message' => $validation['message'],
                    'next_available' => $nextPeriod
                ];
            } else {
                // Obtener el próximo período disponible
                $start = new \DateTime($startDate);
                $end = new \DateTime($endDate);
                $duration = $start->diff($end)->days + 1;
                
                $nextPeriod = CarAvailability::getNextAvailablePeriod($carId, $duration, $startDate);
                
                return [
                    'success' => true,
                    'available' => false,
                    'message' => $validation['message'],
                    'next_available' => $nextPeriod
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar disponibilidad: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener alquileres activos para un vehículo
     */
    public function actionGetCarRentals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $carId = Yii::$app->request->get('car_id');
        $month = Yii::$app->request->get('month', date('Y-m'));
        
        if (!$carId) {
            return [
                'success' => false,
                'message' => 'ID de vehículo requerido.'
            ];
        }
        
        try {
            $startOfMonth = $month . '-01 00:00:00';
            $endOfMonth = date('Y-m-t 23:59:59', strtotime($startOfMonth));
            
            $rentals = CarAvailability::getActiveRentals($carId, $startOfMonth, $endOfMonth);
            
            $formattedRentals = [];
            foreach ($rentals as $rental) {
                $formattedRentals[] = [
                    'id' => $rental->id,
                    'rental_id' => $rental->rental_id,
                    'client_name' => $rental->client ? $rental->client->full_name : 'N/A',
                    'start_date' => $rental->fecha_inicio,
                    'end_date' => $rental->fecha_final,
                    'status' => $rental->estado_pago,
                    'total_price' => $rental->total_precio
                ];
            }
            
            return [
                'success' => true,
                'data' => $formattedRentals
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener alquileres: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener opciones de vehículos para el selector
     */
    public function actionGetCarOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $cars = Car::find()->where(['!=', 'status', 'fuera_servicio'])->all();
            
            $carOptions = [];
            foreach ($cars as $car) {
                $carOptions[] = [
                    'id' => $car->id,
                    'nombre' => $car->nombre,
                    'placa' => $car->placa,
                    'status' => $car->status
                ];
            }
            
            return [
                'success' => true,
                'data' => $carOptions
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener vehículos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene vehículos disponibles para una fecha específica
     */
    public function actionGetAvailableCars()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $startDate = Yii::$app->request->get('start_date');
            $duration = Yii::$app->request->get('duration', 1);
            
            if (!$startDate) {
                return [
                    'success' => false,
                    'message' => 'Fecha de inicio requerida'
                ];
            }
            
            // Para duración de 1 día, la fecha final es la misma
            $endDate = $startDate;
            if ($duration > 1) {
                $startDateTime = new \DateTime($startDate);
                $startDateTime->add(new \DateInterval('P' . ($duration - 1) . 'D'));
                $endDate = $startDateTime->format('Y-m-d');
            }
            
            // Obtener todos los vehículos (excepto fuera de servicio)
            $allCars = Car::find()->where(['!=', 'status', 'fuera_servicio'])->all();
            
            $availableCars = [];
            foreach ($allCars as $car) {
                // Verificar disponibilidad usando CarAvailability
                $isAvailable = \app\models\CarAvailability::isCarAvailable(
                    $car->id, 
                    $startDate, 
                    $endDate
                );
                
                if ($isAvailable) {
                    $availableCars[] = [
                        'id' => $car->id,
                        'nombre' => $car->nombre,
                        'placa' => $car->placa,
                        'status' => $car->status,
                        'disponible' => true
                    ];
                }
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'available_cars' => $availableCars,
                    'search_date' => $startDate,
                    'search_end_date' => $endDate,
                    'duration' => $duration
                ]
            ];
            
            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener vehículos disponibles: ' . $e->getMessage()
            ];
        }
    }

    protected function findModel($id)
    {
        if (($model = Rental::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La página solicitada no existe.');
    }
    
    /**
     * Generar PDF de orden automáticamente
     */
    private function generateOrderPdf($rentalId)
    {
        try {
            $rental = Rental::findOne($rentalId);
            if (!$rental) {
                return;
            }
            
            $companyInfo = \app\models\CompanyConfig::getCompanyInfo();
            
            // Limpiar buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Desactivar compresión
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', 1);
            }
            @ini_set('zlib.output_compression', 0);
            @ini_set('output_buffering', 0);
            
            // Crear PDF usando mPDF
            require_once Yii::getAlias('@vendor/autoload.php');
            
            // Crear directorio temporal personalizado para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $pdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => $tempDir
            ]);
            
            // Generar contenido HTML
            $html = $this->generateRentalOrderHtml($rental, $companyInfo);
            
            // Agregar condiciones como página 2
            $customConditions = $rental->custom_conditions_html ?? '';
            $globalConditions = \app\models\CompanyConfig::getConfig('rental_conditions_html', '');
            if (!empty($customConditions) || !empty($globalConditions) || $companyInfo['conditions']) {
                $conditionsHtml = $this->generateConditionsHtml($companyInfo, $customConditions ?: $globalConditions);
                $html .= '<div style="page-break-before: always;"></div>' . $conditionsHtml;
            }
            
            // Escribir HTML al PDF
            $pdf->WriteHTML($html);
            
            // Generar nombre del archivo
            $filename = 'Orden_Alquiler_' . $rental->rental_id . '_' . date('Y-m-d') . '.pdf';
            $filepath = Yii::getAlias('@app') . '/runtime/' . $filename;
            
            // Guardar PDF en disco
            $pdf->Output($filepath, 'F');
        } catch (\Exception $e) {
            Yii::error('Error generating PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Generar ZIP con PDF de la orden en segundo plano
     */
    private function generateOrderZip($rentalId)
    {
        // Generar ZIP en background usando una llamada HTTP asíncrona
        // Esto no bloquea la respuesta al usuario
        try {
            $url = \yii\helpers\Url::to(['/pdf/generate-zip-async', 'id' => $rentalId], true);
            
            // Hacer llamada HTTP asíncrona en background
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout muy corto, no esperar respuesta
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_exec($ch);
            curl_close($ch);
            
            Yii::info('ZIP generation initiated for rental ID: ' . $rentalId, 'rental');
        } catch (\Exception $e) {
            Yii::error('Error initiating ZIP generation: ' . $e->getMessage(), 'rental');
        }
    }
    
    /**
     * Generar HTML para orden de alquiler
     */
    private function generateRentalOrderHtml($rental, $companyInfo)
    {
        // Unificar generación: usar la vista _rental-pdf para todas las órdenes
        return $this->renderPartial('@app/views/pdf/_rental-pdf', [
            'model' => $rental,
            'companyInfo' => $companyInfo,
        ], true);
    }
    
    /**
     * Generar HTML para condiciones de alquiler
     */
    private function generateConditionsHtml($companyInfo, $customHtml = null)
    {
        $pdfController = new \app\controllers\PdfController('pdf', \Yii::$app);
        return $pdfController->generateConditionsHtml($companyInfo, $customHtml);
    }
}

