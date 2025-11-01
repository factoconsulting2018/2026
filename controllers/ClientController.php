<?php
namespace app\controllers;

use Yii;
use app\models\Client;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * ClientController maneja las operaciones CRUD para el modelo Client.
 */
class ClientController extends Controller
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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'upload-file' => ['POST'],
                    'delete-file' => ['POST'],
                    'list-files' => ['GET', 'POST'],
                    'download-file' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Lista todos los clientes.
     * @return string
     */
    public function actionIndex()
    {
        $query = Client::find();
        
        // Filtro por estado (por defecto todos)
        $estado = Yii::$app->request->get('estado');
        if ($estado === 'all' || $estado === '') {
            // Mostrar todos los clientes (por defecto)
        } elseif ($estado === 'active') {
            $query->where(['status' => 'active']);
        } elseif ($estado === 'inactive') {
            $query->where(['status' => 'inactive']);
        }
        
        // Búsqueda
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'full_name', $search],
                ['like', 'cedula_fisica', $search],
                ['like', 'email', $search],
                ['like', 'whatsapp', $search],
            ]);
        }
        
        // Filtro por tipo
        $tipo = Yii::$app->request->get('tipo');
        if ($tipo === 'facto') {
            $query->andWhere(['es_cliente_facto' => 1]);
        } elseif ($tipo === 'aliado') {
            $query->andWhere(['es_aliado' => 1]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'tipo' => $tipo,
            'estado' => $estado,
        ]);
    }

    /**
     * Muestra un solo modelo de Cliente.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException si el modelo no puede ser encontrado
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuevo modelo de Cliente.
     * Si la creación es exitosa, el navegador será redirigido a la página 'view'.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Client();

        if ($model->load(Yii::$app->request->post())) {
            // Limpiar mensajes flash antes de procesar (por si acaso hay mensajes previos de intentos anteriores)
            $flashTypes = ['success', 'error', 'warning', 'info', 'cedula_duplicate'];
            foreach ($flashTypes as $type) {
                Yii::$app->session->removeFlash($type);
            }
            
            // Procesar actividad económica
            $actividad = Yii::$app->request->post('actividad_economica');
            if (!empty($actividad) && strpos($actividad, ' - ') !== false) {
                $parts = explode(' - ', $actividad, 2);
                $model->actividad_economica_codigo = trim($parts[0]);
                $model->actividad_economica_descripcion = trim($parts[1]);
            }
            
            // Formatear WhatsApp
            if (!empty($model->whatsapp)) {
                $model->whatsapp = Client::formatWhatsApp($model->whatsapp);
            }
            
            if ($model->save()) {
                // Establecer mensaje de éxito (ya limpiamos arriba)
                Yii::$app->session->setFlash('success', 'Creado con éxito!');
                Yii::info('Cliente creado exitosamente con ID: ' . $model->id, 'client');
                return $this->redirect(['index']);
            } else {
                Yii::error('Error al crear cliente: ' . json_encode($model->errors), 'client');
                // Verificar si el error es de cédula duplicada
                $hasCedulaError = false;
                if (isset($model->errors['cedula_fisica'])) {
                    foreach ($model->errors['cedula_fisica'] as $error) {
                        if (strpos($error, 'ya está registrada') !== false || strpos($error, 'has already been taken') !== false) {
                            $hasCedulaError = true;
                            break;
                        }
                    }
                }
                
                if ($hasCedulaError) {
                    // En caso de cédula duplicada, redirigir al listado con mensaje
                    Yii::$app->session->setFlash('error', 'La cédula ' . $model->cedula_fisica . ' ya está registrada en el sistema.');
                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', '❌ Error al crear cliente: ' . json_encode($model->errors));
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un modelo de Cliente existente.
     * Si la actualización es exitosa, el navegador será redirigido a la página 'view'.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException si el modelo no puede ser encontrado
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        Yii::info('=== INICIO ACTUALIZACIÓN CLIENTE ID: ' . $id . ' ===', 'client');
        Yii::info('POST recibido: ' . json_encode(Yii::$app->request->post()), 'client');
        Yii::info('Estado inicial del modelo: ' . json_encode($model->attributes), 'client');

        if ($model->load(Yii::$app->request->post())) {
            Yii::info('Modelo cargado con POST exitosamente', 'client');
            Yii::info('Datos del modelo después de load: ' . json_encode($model->attributes), 'client');
            
            // Limpiar mensajes flash antes de procesar (por si acaso hay mensajes previos de intentos anteriores)
            $flashTypes = ['success', 'error', 'warning', 'info', 'cedula_duplicate'];
            foreach ($flashTypes as $type) {
                Yii::$app->session->removeFlash($type);
            }
            
            // Procesar actividad económica
            $actividad = Yii::$app->request->post('actividad_economica');
            if (!empty($actividad) && strpos($actividad, ' - ') !== false) {
                $parts = explode(' - ', $actividad, 2);
                $model->actividad_economica_codigo = trim($parts[0]);
                $model->actividad_economica_descripcion = trim($parts[1]);
                Yii::info('Actividad económica procesada: ' . $model->actividad_economica_codigo . ' - ' . $model->actividad_economica_descripcion, 'client');
            }
            
            // Formatear WhatsApp
            if (!empty($model->whatsapp)) {
                $whatsappOriginal = $model->whatsapp;
                $model->whatsapp = Client::formatWhatsApp($model->whatsapp);
                Yii::info('WhatsApp formateado: ' . $whatsappOriginal . ' -> ' . $model->whatsapp, 'client');
            }
            
            Yii::info('Validando modelo antes de guardar...', 'client');
            if ($model->validate()) {
                Yii::info('Validación exitosa', 'client');
            } else {
                Yii::error('Errores de validación: ' . json_encode($model->errors), 'client');
            }
            
            if ($model->save()) {
                Yii::info('Modelo guardado exitosamente', 'client');
                Yii::info('Datos finales del modelo: ' . json_encode($model->attributes), 'client');
                
                // Establecer mensaje de éxito (ya limpiamos arriba)
                Yii::$app->session->setFlash('success', 'Cliente actualizado exitosamente');
                Yii::info('Cliente actualizado exitosamente con ID: ' . $model->id, 'client');
                Yii::info('=== FIN ACTUALIZACIÓN CLIENTE ID: ' . $id . ' (ÉXITO) ===', 'client');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('ERROR: No se pudo guardar el modelo', 'client');
                Yii::error('Errores del modelo: ' . json_encode($model->errors), 'client');
                Yii::error('Datos del modelo que intentó guardarse: ' . json_encode($model->attributes), 'client');
                Yii::$app->session->setFlash('error', '❌ Error al actualizar cliente: ' . json_encode($model->errors));
                Yii::info('=== FIN ACTUALIZACIÓN CLIENTE ID: ' . $id . ' (ERROR) ===', 'client');
            }
        } else {
            Yii::error('ERROR: No se pudo cargar el modelo con POST', 'client');
            Yii::error('POST recibido: ' . json_encode(Yii::$app->request->post()), 'client');
            Yii::info('=== FIN ACTUALIZACIÓN CLIENTE ID: ' . $id . ' (NO SE CARGÓ POST) ===', 'client');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Elimina un modelo de Cliente existente.
     * Si la eliminación es exitosa, el navegador será redirigido a la página 'index'.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException si el modelo no puede ser encontrado
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = 'inactive';
        $model->save(false);

        Yii::$app->session->setFlash('success', '🗑️ Cliente eliminado exitosamente');

        return $this->redirect(['index']);
    }

    /**
     * Reactiva un cliente inactivo
     * @param int $id ID del cliente
     * @return \yii\web\Response
     * @throws NotFoundHttpException si el modelo no puede ser encontrado
     */
    public function actionReactivate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->status === 'inactive') {
            $model->status = 'active';
            $model->save(false);
            
            Yii::$app->session->setFlash('success', '✅ Cliente reactivado exitosamente');
        } else {
            Yii::$app->session->setFlash('warning', '⚠️ El cliente ya está activo');
        }

        return $this->redirect(['index']);
    }

    /**
     * Elimina un cliente por cédula
     * @return array
     */
    public function actionDeleteByCedula()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $cedula = Yii::$app->request->post('cedula');
        
        if (empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Cédula requerida'
            ];
        }
        
        try {
            $clientes = Client::find()->where(['cedula_fisica' => $cedula])->all();
            
            if (empty($clientes)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron clientes con esta cédula'
                ];
            }
            
            $eliminados = 0;
            foreach ($clientes as $cliente) {
                if ($cliente->delete()) {
                    $eliminados++;
                }
            }
            
            if ($eliminados > 0) {
                return [
                    'success' => true,
                    'message' => "Se eliminaron $eliminados cliente(s) exitosamente",
                    'count' => $eliminados
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudieron eliminar los clientes'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Subir archivo para un cliente
     * @param int $id ID del cliente
     * @return array JSON
     */
    public function actionUploadFile($id)
    {
        // Desactivar validación CSRF para esta acción (archivos se suben via AJAX)
        $this->enableCsrfValidation = false;
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            // Log para debugging
            Yii::info('Iniciando subida de archivo para cliente ID: ' . $id, 'client');
            
            $client = $this->findModel($id);
            
            $file = UploadedFile::getInstanceByName('file');
            $file_name = Yii::$app->request->post('file_name');
            $description = Yii::$app->request->post('description');
            
            if (!$file) {
                return [
                    'success' => false,
                    'message' => 'No se proporcionó ningún archivo'
                ];
            }
            
            // Validar tipo de archivo (validación más flexible por extensión también)
            $allowedMimeTypes = [
                'application/pdf',
                'image/png',
                'image/jpeg',
                'image/jpg',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
                'application/vnd.ms-excel', // XLS
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
                'application/msword', // DOC
                'application/octet-stream' // Para algunos archivos que el navegador no identifica correctamente
            ];
            
            $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'docx', 'doc'];
            
            // Validar por tipo MIME
            $mimeValid = in_array($file->type, $allowedMimeTypes);
            
            // Validar por extensión como fallback
            $extensionValid = in_array(strtolower($file->extension), $allowedExtensions);
            
            Yii::info('Validando archivo - Tipo MIME: ' . $file->type . ', Extensión: ' . $file->extension, 'client');
            
            if (!$mimeValid && !$extensionValid) {
                return [
                    'success' => false,
                    'message' => 'Tipo de archivo no permitido. Tipo recibido: ' . $file->type . '. Solo se permiten: PDF, PNG, JPG, XLSX, DOCX'
                ];
            }
            
            // Validar tamaño (máximo 10MB)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file->size > $maxSize) {
                return [
                    'success' => false,
                    'message' => 'El archivo es demasiado grande. Tamaño máximo: 10MB'
                ];
            }
            
            // Crear directorio si no existe
            $uploadDir = Yii::getAlias('@app/web/uploads/clients/' . $client->id);
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0777, true)) {
                    $error = error_get_last();
                    throw new \Exception('Error al crear directorio: ' . ($error['message'] ?? 'Permiso denegado'));
                }
            }
            
            // Verificar permisos de escritura
            if (!is_writable($uploadDir)) {
                @chmod($uploadDir, 0777);
                if (!is_writable($uploadDir)) {
                    throw new \Exception('El directorio no tiene permisos de escritura: ' . $uploadDir);
                }
            }
            
            // Generar nombre único para el archivo
            $extension = $file->extension;
            $uniqueName = uniqid('file_', true) . '.' . $extension;
            $filePath = 'uploads/clients/' . $client->id . '/' . $uniqueName;
            $fullPath = Yii::getAlias('@app/web/' . $filePath);
            
            // Guardar archivo
            if (!$file->saveAs($fullPath)) {
                return [
                    'success' => false,
                    'message' => 'Error al guardar el archivo'
                ];
            }
            
            // Crear registro en base de datos
            $clientFile = new \app\models\ClientFile();
            $clientFile->client_id = $client->id;
            $clientFile->file_name = $file_name ?: $file->baseName;
            $clientFile->original_name = $file->name;
            $clientFile->file_path = $filePath;
            $clientFile->file_type = $file->type;
            $clientFile->file_size = $file->size;
            $clientFile->description = $description ?: null;
            
            Yii::info('Intentando guardar registro en BD - Client ID: ' . $client->id . ', File: ' . $file->name, 'client');
            
            if (!$clientFile->save()) {
                @unlink($fullPath); // Eliminar archivo si falla el guardado
                $errors = json_encode($clientFile->errors);
                Yii::error('Error al guardar registro ClientFile: ' . $errors, 'client');
                return [
                    'success' => false,
                    'message' => 'Error al guardar el registro en la base de datos: ' . $errors,
                    'errors' => $clientFile->errors
                ];
            }
            
            Yii::info('Registro guardado exitosamente - File ID: ' . $clientFile->id, 'client');
            
            return [
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'file' => [
                    'id' => $clientFile->id,
                    'file_name' => $clientFile->file_name,
                    'original_name' => $clientFile->original_name,
                    'file_type' => $clientFile->file_type,
                    'file_size' => $clientFile->file_size,
                    'formatted_size' => $clientFile->getFormattedSize(),
                    'description' => $clientFile->description,
                    'url' => $clientFile->getUrl(),
                    'icon' => $clientFile->getFileIcon(),
                    'created_at' => $clientFile->created_at
                ]
            ];
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            
            Yii::error('Error subiendo archivo de cliente ID ' . $id . ': ' . $errorMessage, 'client');
            Yii::error('Stack trace: ' . $errorTrace, 'client');
            
            // Log de información adicional para debugging
            Yii::error('POST data: ' . json_encode(Yii::$app->request->post()), 'client');
            Yii::error('FILES data: ' . json_encode($_FILES), 'client');
            
            return [
                'success' => false,
                'message' => 'Error: ' . $errorMessage,
                'error_details' => YII_DEBUG ? $errorTrace : null
            ];
        }
    }

    /**
     * Listar archivos de un cliente
     * @param int $id ID del cliente
     * @return array JSON
     */
    public function actionListFiles($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $client = $this->findModel($id);
            $search = Yii::$app->request->get('search');
            
            $query = \app\models\ClientFile::find()
                ->where(['client_id' => $client->id]);
            
            if ($search) {
                $query->andWhere([
                    'or',
                    ['like', 'file_name', $search],
                    ['like', 'original_name', $search],
                    ['like', 'description', $search]
                ]);
            }
            
            $files = $query->orderBy(['created_at' => SORT_DESC])->all();
            
            $data = [];
            foreach ($files as $file) {
                $data[] = [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'original_name' => $file->original_name,
                    'file_type' => $file->file_type,
                    'file_size' => $file->file_size,
                    'formatted_size' => $file->getFormattedSize(),
                    'description' => $file->description,
                    'url' => $file->getUrl(),
                    'icon' => $file->getFileIcon(),
                    'created_at' => $file->created_at
                ];
            }
            
            return [
                'success' => true,
                'count' => count($data),
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            Yii::error('Error listando archivos de cliente: ' . $e->getMessage(), 'client');
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar archivo de cliente
     * @param int $id ID del archivo
     * @return array JSON
     */
    public function actionDeleteFile($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $file = \app\models\ClientFile::findOne($id);
            
            if (!$file) {
                return [
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ];
            }
            
            if ($file->delete()) {
                return [
                    'success' => true,
                    'message' => 'Archivo eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el archivo'
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error('Error eliminando archivo: ' . $e->getMessage(), 'client');
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Descargar archivo de cliente
     * @param int $id ID del archivo
     */
    public function actionDownloadFile($id)
    {
        $file = \app\models\ClientFile::findOne($id);
        
        if (!$file) {
            throw new NotFoundHttpException('Archivo no encontrado');
        }
        
        $filePath = Yii::getAlias('@app/web/' . $file->file_path);
        
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('El archivo no existe en el servidor');
        }
        
        // Limpiar buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Enviar archivo
        header('Content-Type: ' . $file->file_type);
        header('Content-Disposition: attachment; filename="' . $file->original_name . '"');
        header('Content-Length: ' . $file->file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($filePath);
        exit;
    }

    /**
     * Encuentra el modelo de Cliente basado en su valor de clave primaria.
     * Si el modelo no es encontrado, una excepción HTTP 404 será lanzada.
     * @param int $id ID
     * @return Client el modelo cargado
     * @throws NotFoundHttpException si el modelo no puede ser encontrado
     */
    protected function findModel($id)
    {
        if (($model = Client::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}

