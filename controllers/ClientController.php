<?php
namespace app\controllers;

use Yii;
use app\models\Client;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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
                Yii::$app->session->setFlash('success', '✅ Cliente creado exitosamente');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
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
                    Yii::$app->session->setFlash('cedula_duplicate', [
                        'cedula' => $model->cedula_fisica,
                        'message' => 'La cédula ya existe en el sistema'
                    ]);
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

        if ($model->load(Yii::$app->request->post())) {
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
                Yii::$app->session->setFlash('success', '✅ Cliente actualizado exitosamente');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al actualizar cliente');
            }
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

