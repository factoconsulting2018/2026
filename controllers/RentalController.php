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
use app\models\CompanyConfig;
use app\controllers\PdfController;
use app\components\WhatsAppNotifier;
use Mpdf\Mpdf;

class RentalController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['public-view'],
                        'allow' => true,
                        'roles' => ['?'], // Permitir usuarios no autenticados
                    ],
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
                    'swap-vehicle' => ['POST'],
                    'swap-vehicle-data' => ['GET'],
                    'undo-swap' => ['POST'],
                    'pdf-choices' => ['GET'],
                    'overdue-rentals' => ['GET'],
                    'conflicting-rentals' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Finaliza ordenes pagadas vencidas antes de consultar disponibilidad/listados.
     */
    private function autoFinalizeCompletedRentals(): int
    {
        try {
            $finalized = Rental::autoFinalizeCompleted();
            if ($finalized > 0) {
                Car::syncAllStatuses();
            }

            return $finalized;
        } catch (\Throwable $e) {
            Yii::error('autoFinalizeCompleted falló: ' . $e->getMessage(), 'rental');
            return 0;
        }
    }

    public function actionIndex()
    {
        $this->autoFinalizeCompletedRentals();

        // Crear query base
        $query = Rental::find()
            ->where(['is_async' => 0])
            ->orderBy(['id' => SORT_DESC]);
        
        // Aplicar filtro de estado si existe
        $estado_pago = Yii::$app->request->get('estado_pago');
        if ($estado_pago) {
            $query->andWhere(['estado_pago' => $estado_pago]);
        }
        
        // Aplicar búsqueda si existe
        $search = Yii::$app->request->get('search');
        if (!empty($search)) {
            $query->joinWith(['client', 'car']);
            $query->andWhere([
                'or',
                ['like', Rental::tableName() . '.rental_id', $search],
                ['like', Rental::tableName() . '.id', $search],
                ['like', Client::tableName() . '.nombre', $search],
                ['like', Client::tableName() . '.apellido', $search],
                ['like', Client::tableName() . '.cedula_fisica', $search],
                ['like', Client::tableName() . '.telefono', $search],
                ['like', Client::tableName() . '.celular', $search],
                ['like', Client::tableName() . '.whatsapp', $search],
                ['like', Car::tableName() . '.nombre', $search],
                ['like', Car::tableName() . '.placa', $search],
            ]);
        } else {
            // Solo hacer eager loading si no hay búsqueda
            $query->with(['client', 'car', 'parentRental', 'replacementRental', 'replacementRental.car']);
        }
        
        // Asegurar que todos los alquileres tengan rental_id
        $this->ensureRentalIds();
        
        // Crear DataProvider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
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

    /**
     * Vista pública de orden de alquiler (para QR)
     */
    public function actionPublicView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('public-view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Rental();

        if ($model->load(Yii::$app->request->post())) {
            $model->is_async = 0;
            // Debug: Log de los datos recibidos
            Yii::info('DEBUG - Datos POST recibidos: ' . json_encode(Yii::$app->request->post()), 'rental');
            Yii::info('DEBUG - Modelo cargado - car_id: ' . $model->car_id . ', fecha_inicio: ' . $model->fecha_inicio . ', cantidad_dias: ' . $model->cantidad_dias, 'rental');
            
            if ($model->save()) {
                // Refrescar el modelo para obtener el total_precio calculado por la columna generada
                $model->refresh();
                
                // Sincronizar estado del carro con las rentas activas (alquilado si cubre hoy)
                if (!$model->is_async && $model->car_id) {
                    Car::syncStatusFromRentals((int) $model->car_id);
                }
                
                // Generar PDF automáticamente al crear la orden
                $this->generateOrderPdf($model->id);
                
                // Generar ZIP automáticamente en background (sin bloquear)
                $this->generateOrderZip($model->id);

                // Enviar aviso por WhatsApp a teléfonos administradores (no debe romper el flujo)
                try {
                    $waReport = WhatsAppNotifier::notifyRentalCreated($model);
                    if ($waReport['enabled']) {
                        if ($waReport['sent'] > 0) {
                            $msg = '📲 Aviso por WhatsApp enviado a ' . $waReport['sent']
                                . ' de ' . $waReport['attempted'] . ' teléfono(s) administrativo(s).';
                            if (!empty($waReport['errors'])) {
                                $msg .= ' Algunos fallaron: ' . implode(' | ', $waReport['errors']);
                                Yii::$app->session->setFlash('warning', $msg);
                            } else {
                                Yii::$app->session->setFlash('info', $msg);
                            }
                        } else {
                            $errs = !empty($waReport['errors']) ? implode(' | ', $waReport['errors']) : 'sin detalle';
                            Yii::$app->session->setFlash(
                                'warning',
                                '⚠️ No se pudo enviar el aviso por WhatsApp: ' . $errs
                                . ' — Revise Configuración → WhatsApp (sesión conectada, teléfonos, URL pública del sitio).'
                            );
                            Yii::warning('WhatsApp notify errors: ' . $errs, 'whatsapp');
                        }
                    } elseif (!empty($waReport['skipped_reason'])) {
                        Yii::info('WhatsApp omitido: ' . $waReport['skipped_reason'], 'whatsapp');
                    }
                } catch (\Throwable $e) {
                    Yii::error('WhatsApp notify exception: ' . $e->getMessage(), 'whatsapp');
                }
                
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
        $previousCarId = (int) $model->car_id;
        
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
                // Refrescar el modelo para obtener el total_precio calculado por la columna generada
                $model->refresh();
                
                // Sincronizar estado del carro actual y del anterior si cambió
                Car::syncStatusFromRentals((int) $model->car_id);
                if ($previousCarId && $previousCarId !== (int) $model->car_id) {
                    Car::syncStatusFromRentals($previousCarId);
                }

                // Regenerar el PDF con los datos actualizados para que el aviso por
                // WhatsApp adjunte siempre la versión más reciente.
                try {
                    $this->generateOrderPdf($model->id);
                } catch (\Throwable $e) {
                    Yii::warning('No se pudo regenerar PDF al actualizar orden #' . $model->id . ': ' . $e->getMessage(), 'rental');
                }

                // Enviar aviso de actualización por WhatsApp (no debe romper el flujo).
                try {
                    $waReport = WhatsAppNotifier::notifyRentalUpdated($model);
                    if ($waReport['enabled']) {
                        if ($waReport['sent'] > 0) {
                            $msg = '📲 Aviso de actualización enviado por WhatsApp a ' . $waReport['sent']
                                . ' de ' . $waReport['attempted'] . ' teléfono(s) administrativo(s).';
                            if (!empty($waReport['errors'])) {
                                $msg .= ' Algunos fallaron: ' . implode(' | ', $waReport['errors']);
                                Yii::$app->session->setFlash('warning', $msg);
                            } else {
                                Yii::$app->session->setFlash('info', $msg);
                            }
                        } else {
                            $errs = !empty($waReport['errors']) ? implode(' | ', $waReport['errors']) : 'sin detalle';
                            Yii::$app->session->setFlash(
                                'warning',
                                '⚠️ No se pudo enviar el aviso de actualización por WhatsApp: ' . $errs
                                . ' — Revise Configuración → WhatsApp.'
                            );
                            Yii::warning('WhatsApp notify (update) errors: ' . $errs, 'whatsapp');
                        }
                    } elseif (!empty($waReport['skipped_reason'])) {
                        Yii::info('WhatsApp omitido (update): ' . $waReport['skipped_reason'], 'whatsapp');
                    }
                } catch (\Throwable $e) {
                    Yii::error('WhatsApp notify (update) exception: ' . $e->getMessage(), 'whatsapp');
                }

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
        $transaction = Yii::$app->db->beginTransaction();
        $cancelledSnapshot = null;
        try {
            $model = $this->findModel($id);
            $deletedCarId = !$model->is_async ? (int) $model->car_id : 0;

            // Tomamos un snapshot del modelo (con sus relaciones cargadas) antes de eliminarlo
            // para poder enviar la notificación de WhatsApp con los datos completos.
            try {
                // Forzamos la carga de relaciones que usa el mensaje.
                $model->client; // @phpstan-ignore-line
                $model->car;    // @phpstan-ignore-line
                $cancelledSnapshot = clone $model;
                // Estado de pago anulado para el mensaje (no se guarda).
                $cancelledSnapshot->estado_pago = 'cancelado';
            } catch (\Throwable $e) {
                Yii::warning('No se pudo clonar el modelo para notificación de anulación: ' . $e->getMessage(), 'rental');
                $cancelledSnapshot = null;
            }

            if (!$model->delete()) {
                throw new \Exception('No se pudo eliminar el alquiler');
            }

            $transaction->commit();

            // Sincronizar estado del carro: vuelve a disponible solo si no hay otra renta activa hoy
            if ($deletedCarId) {
                Car::syncStatusFromRentals($deletedCarId);
            }

            // Notificación de anulación por WhatsApp (siempre a admins; al cliente si flag activo).
            if ($cancelledSnapshot !== null) {
                try {
                    $report = WhatsAppNotifier::notifyRentalCancelled($cancelledSnapshot);
                    if (!empty($report['enabled']) && ($report['sent'] ?? 0) > 0) {
                        Yii::$app->session->setFlash('info', '📲 Aviso de anulación enviado por WhatsApp a ' . $report['sent'] . ' destinatario(s).');
                    } elseif (!empty($report['skipped_reason'])) {
                        Yii::info('WhatsApp anulación omitido: ' . $report['skipped_reason'], 'whatsapp');
                    } elseif (!empty($report['errors'])) {
                        Yii::warning('WhatsApp anulación con errores: ' . implode(' | ', $report['errors']), 'whatsapp');
                    }
                } catch (\Throwable $e) {
                    Yii::error('Error enviando WhatsApp de anulación: ' . $e->getMessage(), 'whatsapp');
                }
            }

            Yii::$app->session->setFlash('success', '🗑️ El alquiler fue anulado correctamente');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', '❌ Error al anular el alquiler: ' . $e->getMessage());
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'rental');
        }
        
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
            $allowedStatuses = ['pendiente', 'pagado', 'reservado', 'cancelado', 'finalizado'];
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
            
            // Actualizar abonos si el estado es "reservado"
            if ($newStatus === 'reservado') {
                for ($i = 1; $i <= 5; $i++) {
                    $descripcion = Yii::$app->request->post("abono{$i}_descripcion");
                    $monto = Yii::$app->request->post("abono{$i}_monto");
                    
                    $model->{"abono{$i}_descripcion"} = $descripcion ?: null;
                    $model->{"abono{$i}_monto"} = $monto ?: null;
                }
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
                
                // Sincronizar estado del vehículo con sus rentas activas hoy
                if ($model->car_id) {
                    Car::syncStatusFromRentals((int) $model->car_id);
                }

                // Si el nuevo estado es "cancelado" (anulación vía cambio de estado),
                // enviar notificación WhatsApp de anulación (siempre a admins; al cliente si flag activo).
                if ($newStatus === 'cancelado' && $oldStatus !== 'cancelado') {
                    try {
                        WhatsAppNotifier::notifyRentalCancelled($model);
                    } catch (\Throwable $e) {
                        Yii::error('Error enviando WhatsApp de anulación (update-payment-status): ' . $e->getMessage(), 'whatsapp');
                    }
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
            
            $result = [
                'success' => true
            ];
            
            if ($model->hasComprobante()) {
                $result['comprobante'] = [
                    'url' => $model->getComprobanteUrl(),
                    'fileName' => $model->getComprobanteFileName(),
                    'sizeFormatted' => $model->getComprobanteSizeFormatted(),
                    'isImage' => $model->isComprobanteImage()
                ];
            }
            
            // Agregar información de abonos
            $result['abonos'] = [];
            for ($i = 1; $i <= 5; $i++) {
                $result['abonos'][] = [
                    'descripcion' => $model->{"abono{$i}_descripcion"} ?: '',
                    'monto' => $model->{"abono{$i}_monto"} ?: ''
                ];
            }
            
            return $result;
            
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
            $this->autoFinalizeCompletedRentals();

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
            
            // Sincronizar status de todos los vehículos para que no contaminen el filtro
            // (rentas viejas en "reservado" sin cerrar dejaban carros como "alquilado").
            try {
                Car::syncAllStatuses();
            } catch (\Throwable $e) {
                Yii::error('syncAllStatuses falló: ' . $e->getMessage(), 'rental');
            }

            // include_busy=1 devuelve TODOS los vehículos (excepto fuera_servicio y
            // mantenimiento) marcando cuáles están ocupados en el rango. Esto permite
            // al cliente ofrecer una acción rápida sobre los conflictos.
            $includeBusy = (int) Yii::$app->request->get('include_busy', 0) === 1;
            $excludeRentalId = (int) Yii::$app->request->get('exclude_rental_id', 0) ?: null;

            $allCars = Car::find()
                ->where(['not in', 'status', ['fuera_servicio', 'mantenimiento']])
                ->orderBy(['nombre' => SORT_ASC])
                ->all();

            $availableCars = [];
            $busyCars = [];
            foreach ($allCars as $car) {
                $isAvailable = \app\models\CarAvailability::isCarAvailable(
                    $car->id,
                    $startDate,
                    $endDate,
                    $excludeRentalId
                );

                $entry = [
                    'id' => $car->id,
                    'nombre' => $car->nombre,
                    'placa' => $car->placa,
                    'status' => $car->status,
                    'empresa' => (string) ($car->empresa ?? ''),
                    'disponible' => $isAvailable,
                ];

                if ($isAvailable) {
                    $availableCars[] = $entry;
                } elseif ($includeBusy) {
                    $busyCars[] = $entry;
                }
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'available_cars' => $availableCars,
                    'busy_cars' => $busyCars,
                    'search_date' => $startDate,
                    'search_end_date' => $endDate,
                    'duration' => $duration,
                ],
            ];

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener vehículos disponibles: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Listado JSON de órdenes que chocan con un rango para un vehículo dado.
     * Usado por el modal de cambio de vehículo cuando el usuario elige un vehículo
     * ocupado, para ofrecerle cancelar/editar las órdenes conflictivas.
     */
    public function actionConflictingRentals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $this->autoFinalizeCompletedRentals();

        $carId = (int) Yii::$app->request->get('car_id', 0);
        $startDate = trim((string) Yii::$app->request->get('start_date', ''));
        $endDate = trim((string) Yii::$app->request->get('end_date', ''));
        $excludeRentalId = (int) Yii::$app->request->get('exclude_rental_id', 0) ?: null;

        if ($carId <= 0 || $startDate === '' || $endDate === '') {
            return ['success' => false, 'message' => 'Parámetros incompletos.'];
        }

        $startDay = substr($startDate, 0, 10);
        $endDay = substr($endDate, 0, 10);

        $query = Rental::find()
            ->with(['client', 'car'])
            ->where(['car_id' => $carId])
            ->andWhere(['is_async' => 0])
            ->andWhere(['not in', 'estado_pago', ['cancelado', 'finalizado']])
            ->andWhere(['swapped_to_rental_id' => null])
            ->andWhere([
                'not',
                [
                    'or',
                    ['>=', new \yii\db\Expression(
                        'IF(correapartir_enabled = 1 AND fecha_correapartir IS NOT NULL, DATE(fecha_correapartir), DATE(fecha_inicio))'
                    ), $endDay],
                    ['<=', new \yii\db\Expression('DATE(fecha_final)'), $startDay],
                ],
            ]);

        if ($excludeRentalId) {
            $query->andWhere(['!=', 'id', $excludeRentalId]);
        }

        $rentals = $query->orderBy(['fecha_inicio' => SORT_ASC])->all();

        $items = [];
        foreach ($rentals as $rental) {
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
                'view_url' => \yii\helpers\Url::to(['view', 'id' => $rental->id]),
                'update_url' => \yii\helpers\Url::to(['update', 'id' => $rental->id]),
            ];
        }

        return [
            'success' => true,
            'car_id' => $carId,
            'start_date' => $startDay,
            'end_date' => $endDay,
            'count' => count($items),
            'rentals' => $items,
        ];
    }

    /**
     * Listado JSON de órdenes vencidas (fecha_final < hoy, no canceladas, no pagadas
     * y no reemplazadas). Sirve al modal "Órdenes que requieren atención".
     */
    public function actionOverdueRentals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $this->autoFinalizeCompletedRentals();

        $today = date('Y-m-d');
        $rentals = Rental::find()
            ->with(['client', 'car'])
            ->where(['is_async' => 0])
            ->andWhere(['not in', 'estado_pago', ['cancelado', 'pagado', 'finalizado']])
            ->andWhere(['swapped_to_rental_id' => null])
            ->andWhere(['<', 'fecha_final', $today])
            ->orderBy(['fecha_final' => SORT_ASC])
            ->all();

        $items = [];
        $hoy = new \DateTime($today);
        foreach ($rentals as $rental) {
            $fin = \DateTime::createFromFormat('Y-m-d', substr((string) $rental->fecha_final, 0, 10));
            if (!$fin) {
                continue;
            }
            $diasVencido = (int) $hoy->diff($fin)->days;
            $client = $rental->client;
            $car = $rental->car;
            $items[] = [
                'id' => $rental->id,
                'rental_id' => $rental->rental_id ?: ('R' . $rental->id),
                'fecha_inicio' => $rental->fecha_inicio,
                'fecha_final' => $rental->fecha_final,
                'estado_pago' => $rental->estado_pago,
                'total_precio' => (float) $rental->total_precio,
                'dias_vencido' => $diasVencido,
                'client_name' => $client ? ($client->full_name ?? $client->nombre) : 'Sin cliente',
                'client_phone' => $client ? ($client->whatsapp ?: $client->telefono ?: $client->celular ?: '') : '',
                'car_name' => $car ? $car->nombre : 'N/A',
                'car_placa' => $car ? (string) ($car->placa ?? '') : '',
                'view_url' => \yii\helpers\Url::to(['view', 'id' => $rental->id]),
                'update_url' => \yii\helpers\Url::to(['update', 'id' => $rental->id]),
                'pdf_url' => \yii\helpers\Url::to(['/pdf/rental-order', 'id' => $rental->id]),
            ];
        }

        return [
            'success' => true,
            'today' => $today,
            'count' => count($items),
            'rentals' => $items,
        ];
    }

    /**
     * Datos para el modal de cambio de vehículo (GET JSON)
     */
    public function actionSwapVehicleData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $original = $this->findModel($id);

        if (!$original->canSwapVehicle()) {
            return [
                'success' => false,
                'message' => 'Esta orden no permite cambio de vehículo (ya fue reemplazada, es un reemplazo, está cancelada o es asincrónica).',
            ];
        }

        $original->populateRelation('car', $original->car ?? Car::findOne($original->car_id));
        $original->populateRelation('client', $original->client ?? Client::findOne($original->client_id));

        return [
            'success' => true,
            'rental' => [
                'id' => $original->id,
                'rental_id' => $original->rental_id ?: ('R' . $original->id),
                'car_id' => $original->car_id,
                'car_name' => $original->car ? ($original->car->nombre . ' (' . $original->car->placa . ')') : '',
                'client_name' => $original->client ? ($original->client->full_name ?? $original->client->nombre) : '',
                'fecha_inicio' => $original->fecha_inicio,
                'fecha_final' => $original->fecha_final,
                'precio_por_dia' => $original->precio_por_dia,
                'lugar_entrega' => $original->lugar_entrega,
                'lugar_retiro' => $original->lugar_retiro,
                'comprobante_pago' => $original->comprobante_pago,
                'ejecutivo' => $original->ejecutivo,
                'cantidad_dias' => $original->cantidad_dias,
            ],
            'swap_vehicle_url' => \yii\helpers\Url::to(['swap-vehicle', 'id' => $original->id]),
            'available_cars_url' => \yii\helpers\Url::to(['get-available-cars']),
        ];
    }

    /**
     * Registrar cambio de vehículo: nueva orden hija + enlace en la original
     */
    public function actionSwapVehicle($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $original = $this->findModel($id);

        if (!$original->canSwapVehicle()) {
            return ['success' => false, 'message' => 'Esta orden no permite cambio de vehículo.'];
        }

        $post = Yii::$app->request->post();
        $newCarId = (int) ($post['new_car_id'] ?? 0);
        $swapDate = trim((string) ($post['swap_date'] ?? ''));
        $swapReason = trim((string) ($post['swap_reason'] ?? ''));

        if ($newCarId <= 0) {
            return ['success' => false, 'message' => 'Seleccione el vehículo de reemplazo.'];
        }
        if ($swapDate === '') {
            return ['success' => false, 'message' => 'Indique la fecha del cambio.'];
        }
        if ($swapReason === '') {
            return ['success' => false, 'message' => 'Indique el motivo del cambio.'];
        }
        if ($newCarId === (int) $original->car_id) {
            return ['success' => false, 'message' => 'El vehículo nuevo debe ser distinto al actual.'];
        }

        $fechaFinal = trim((string) ($post['fecha_final'] ?? $original->fecha_final));
        if (strtotime($swapDate) < strtotime(substr((string) $original->fecha_inicio, 0, 10))) {
            return ['success' => false, 'message' => 'La fecha de cambio no puede ser anterior al inicio del alquiler.'];
        }
        if (strtotime($swapDate) > strtotime(substr((string) $fechaFinal, 0, 10))) {
            return ['success' => false, 'message' => 'La fecha de cambio no puede ser posterior al fin del alquiler.'];
        }

        if (!CarAvailability::isCarAvailable($newCarId, $swapDate, $fechaFinal)) {
            return ['success' => false, 'message' => 'El vehículo seleccionado no está disponible desde la fecha de cambio hasta el fin del alquiler.'];
        }

        $oldCarId = (int) $original->car_id;
        $inicio = new \DateTime($swapDate);
        $fin = new \DateTime($fechaFinal);
        $cantidadDias = max(1, (int) $inicio->diff($fin)->days + 1);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $overrides = [
                'car_id' => $newCarId,
                'fecha_inicio' => $swapDate,
                'fecha_final' => $fechaFinal,
                'cantidad_dias' => $cantidadDias,
                'precio_por_dia' => $post['precio_por_dia'] ?? $original->precio_por_dia,
                'lugar_entrega' => $post['lugar_entrega'] ?? $original->lugar_entrega,
                'lugar_retiro' => $post['lugar_retiro'] ?? $original->lugar_retiro,
                'comprobante_pago' => $post['comprobante_pago'] ?? $original->comprobante_pago,
                'ejecutivo' => $post['ejecutivo'] ?? $original->ejecutivo,
            ];

            $replacement = Rental::createReplacementFrom($original, $overrides);
            if (!$replacement->save()) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'No se pudo crear la orden de reemplazo: ' . implode(' ', $replacement->getFirstErrors()),
                ];
            }

            $original->swapped_to_rental_id = $replacement->id;
            $original->swap_date = $swapDate;
            $original->swap_reason = $swapReason;
            if (!$original->save(false)) {
                $transaction->rollBack();
                return ['success' => false, 'message' => 'No se pudo actualizar la orden original.'];
            }

            $transaction->commit();

            $this->syncCarStatusAfterSwap($oldCarId, $newCarId);
            $this->generateOrderPdf($replacement->id);

            $oldCar = Car::findOne($oldCarId);
            $newCar = Car::findOne($newCarId);
            $msg = sprintf(
                'Cambio registrado: %s reemplazado por %s desde %s. Nueva orden %s.',
                $oldCar ? $oldCar->nombre : 'vehículo anterior',
                $newCar ? $newCar->nombre : 'vehículo nuevo',
                date('d/m/Y', strtotime($swapDate)),
                $replacement->rental_id ?: ('R' . $replacement->id)
            );
            Yii::$app->session->setFlash('success', '✅ ' . $msg);

            return [
                'success' => true,
                'message' => $msg,
                'replacement_id' => $replacement->id,
                'replacement_rental_id' => $replacement->rental_id,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('swap-vehicle: ' . $e->getMessage(), 'rental');
            return ['success' => false, 'message' => 'Error al registrar el cambio: ' . $e->getMessage()];
        }
    }

    /**
     * Deshace el cambio de vehiculo cuando el precio por dia coincide con la
     * orden original (no implica venta adicional). El reemplazo se conserva
     * como historial pero se marca cancelado para liberar disponibilidad.
     */
    public function actionUndoSwap($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $original = $this->findModel($id);

        if (!$original->isSwapped()) {
            return ['success' => false, 'message' => 'Esta orden no tiene un cambio de vehículo pendiente de deshacer.'];
        }

        $replacement = $original->replacementRental;
        if (!$replacement) {
            return ['success' => false, 'message' => 'No se encontró la orden de reemplazo.'];
        }

        if ((float) $replacement->precio_por_dia !== (float) $original->precio_por_dia) {
            return [
                'success' => false,
                'message' => 'No se puede deshacer: el reemplazo tiene un precio distinto al original y ya cuenta como una venta separada.',
            ];
        }

        if (!empty($replacement->numero_factura)) {
            return [
                'success' => false,
                'message' => 'No se puede deshacer: la orden de reemplazo ya fue facturada.',
            ];
        }

        $oldCarId = (int) $original->car_id;
        $newCarId = (int) $replacement->car_id;
        $previousReason = (string) ($original->swap_reason ?? '');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $replacement->estado_pago = 'cancelado';
            $undoNote = 'Deshecho desde orden ' . ($original->rental_id ?: ('R' . $original->id))
                . ' el ' . date('Y-m-d H:i')
                . ($previousReason !== '' ? ' — Motivo original: ' . $previousReason : '');
            $replacement->swap_reason = trim(($replacement->swap_reason ? $replacement->swap_reason . ' | ' : '') . $undoNote);
            if (!$replacement->save(false)) {
                $transaction->rollBack();
                return ['success' => false, 'message' => 'No se pudo actualizar la orden de reemplazo.'];
            }

            $original->swapped_to_rental_id = null;
            $original->swap_date = null;
            $original->swap_reason = null;
            if (!$original->save(false)) {
                $transaction->rollBack();
                return ['success' => false, 'message' => 'No se pudo actualizar la orden original.'];
            }

            $transaction->commit();

            try {
                Car::syncStatusFromRentals($oldCarId);
                Car::syncStatusFromRentals($newCarId);
            } catch (\Throwable $e) {
                Yii::error('undo-swap sync: ' . $e->getMessage(), 'rental');
            }

            $msg = sprintf(
                'Cambio deshecho: la orden %s vuelve al vehículo original. El reemplazo se conservó como cancelado en el historial.',
                $original->rental_id ?: ('R' . $original->id)
            );
            Yii::$app->session->setFlash('success', '✅ ' . $msg);

            return ['success' => true, 'message' => $msg];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('undo-swap: ' . $e->getMessage(), 'rental');
            return ['success' => false, 'message' => 'Error al deshacer el cambio: ' . $e->getMessage()];
        }
    }

    /**
     * URLs de PDF original y de cambio para el modal de descarga
     */
    public function actionPdfChoices($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        $buildUrl = static function (int $rentalPk): string {
            // URL relativa: hereda el esquema (http/https) de la pagina actual.
            // Evita mixed-content cuando el sitio es HTTPS y request->hostInfo
            // reporta http detras de un proxy/Cloudflare.
            return \yii\helpers\Url::to(['/pdf/rental-order', 'id' => $rentalPk]);
        };

        if ($model->isSwapped()) {
            $replacement = Rental::findOne($model->swapped_to_rental_id);
            if (!$replacement) {
                return ['success' => false, 'message' => 'No se encontró la orden de reemplazo.'];
            }
            return [
                'success' => true,
                'has_swap' => true,
                'original_pdf_url' => $buildUrl($model->id),
                'swap_pdf_url' => $buildUrl($replacement->id),
                'original_label' => $model->rental_id ?: ('R' . $model->id),
                'swap_label' => $replacement->rental_id ?: ('R' . $replacement->id),
            ];
        }

        if ($model->isReplacement() && $model->parentRental) {
            $parent = $model->parentRental;
            return [
                'success' => true,
                'has_swap' => true,
                'original_pdf_url' => $buildUrl($parent->id),
                'swap_pdf_url' => $buildUrl($model->id),
                'original_label' => $parent->rental_id ?: ('R' . $parent->id),
                'swap_label' => $model->rental_id ?: ('R' . $model->id),
            ];
        }

        return [
            'success' => true,
            'has_swap' => false,
            'original_pdf_url' => $buildUrl($model->id),
        ];
    }

    /**
     * Actualiza estado disponible/alquilado según rentas activas hoy (X y Y).
     */
    private function syncCarStatusAfterSwap(int $oldCarId, int $newCarId): void
    {
        Car::syncStatusFromRentals($oldCarId);
        Car::syncStatusFromRentals($newCarId);
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
            
            // Refrescar el modelo para obtener el total_precio calculado por la columna generada
            $rental->refresh();
            
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
            // El autoloader de Composer ya está cargado por Yii2 en el entry point
            
            // Crear directorio temporal personalizado para mPDF
            $tempDir = Yii::getAlias('@app') . '/runtime/mpdf_temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $isModernRentalPdf = CompanyConfig::getRentalOrderPdfFormat() === 'moderna';
            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => $isModernRentalPdf ? 'A4' : 'Letter',
                'orientation' => 'P',
                'margin_left' => $isModernRentalPdf ? 12 : 14,
                'margin_right' => $isModernRentalPdf ? 12 : 14,
                'margin_top' => $isModernRentalPdf ? 10 : 12,
                'margin_bottom' => $isModernRentalPdf ? 10 : 14,
                'default_font' => 'dejavusans',
                'default_font_size' => 10,
                'tempDir' => $tempDir,
            ]);

            $pdfCtrl = new PdfController('pdf', Yii::$app);
            $customConditions = $rental->condiciones_especiales ?? '';
            $globalConditions = CompanyConfig::getConfig('rental_conditions_html', '');
            $hasCond = !empty($customConditions) || !empty($globalConditions) || !empty($companyInfo['conditions']);
            $includeCond = $isModernRentalPdf ? $hasCond : false;
            $html = $pdfCtrl->generateRentalOrderHtml($rental, $companyInfo, $includeCond);
            if (!$isModernRentalPdf) {
                $conditionsHtml = CompanyConfig::wrapRentalConditionsHtml($pdfCtrl->generateConditionsHtml($companyInfo, $customConditions ?: $globalConditions));
                $html .= '<div style="page-break-before: always;"></div>' . $conditionsHtml;
            }

            // Escribir HTML al PDF
            $pdf->WriteHTML($html);
            
            // Generar nombre del archivo
            $filename = PdfController::rentalOrderPdfFilename($rental);
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
     * Generar HTML para condiciones de alquiler
     */
    private function generateConditionsHtml($companyInfo, $customHtml = null)
    {
        $pdfController = new \app\controllers\PdfController('pdf', \Yii::$app);
        return $pdfController->generateConditionsHtml($companyInfo, $customHtml);
    }
}

