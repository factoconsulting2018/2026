<?php

namespace app\controllers;

use Yii;
use app\models\Note;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

/**
 * NotesController implements the CRUD actions for Note model.
 */
class NotesController extends Controller
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
                        'roles' => ['@'], // Solo usuarios autenticados
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'update-status' => ['POST'],
                    'update-position' => ['POST'],
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Note models.
     * @return mixed
     */
    public function actionIndex()
    {
        $notesByStatus = Note::getNotesByStatusWithOrder();
        $stats = Note::getStats();

        return $this->render('index', [
            'notesByStatus' => $notesByStatus,
            'stats' => $stats,
        ]);
    }

    /**
     * Dashboard view for notes with dynamic interface.
     * @return mixed
     */
    public function actionDashboard()
    {
        $notesByStatus = [
            'pending' => Note::find()->where(['status' => 'pending'])->orderBy(['created_at' => SORT_DESC])->all(),
            'processing' => Note::find()->where(['status' => 'processing'])->orderBy(['created_at' => SORT_DESC])->all(),
            'completed' => Note::find()->where(['status' => 'completed'])->orderBy(['created_at' => SORT_DESC])->all(),
        ];

        $stats = [
            'total' => Note::find()->count(),
            'pending' => count($notesByStatus['pending']),
            'processing' => count($notesByStatus['processing']),
            'completed' => count($notesByStatus['completed']),
        ];

        return $this->render('dashboard', [
            'notesByStatus' => $notesByStatus,
            'stats' => $stats,
        ]);
    }

    /**
     * Lists all Note models in table format.
     * @return mixed
     */
    public function actionList()
    {
        $search = Yii::$app->request->get('search', '');
        $status = Yii::$app->request->get('status', '');
        $color = Yii::$app->request->get('color', '');
        
        $query = Note::find();
        
        // Aplicar filtros
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'title', $search],
                ['like', 'content', $search]
            ]);
        }
        
        if (!empty($status)) {
            $query->andWhere(['status' => $status]);
        }
        
        if (!empty($color)) {
            $query->andWhere(['color' => $color]);
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
        
        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'status' => $status,
            'color' => $color,
        ]);
    }

    /**
     * Creates a new Note model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Note();

        if ($model->load(Yii::$app->request->post())) {
            // Debug: Log de los datos recibidos
            Yii::info('DEBUG - Datos POST recibidos: ' . json_encode(Yii::$app->request->post()), 'notes');
            Yii::info('DEBUG - Modelo cargado - title: ' . $model->title . ', content: ' . $model->content . ', color: ' . $model->color . ', status: ' . $model->status, 'notes');
            
            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'Nota creada exitosamente',
                        'note' => $model->toArray(),
                    ];
                }
                Yii::$app->session->setFlash('success', 'Nota creada exitosamente.');
                return $this->redirect(['index']);
            } else {
                // Debug: Log de errores de validación
                Yii::info('DEBUG - Errores de validación: ' . json_encode($model->errors), 'notes');
                
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Error al crear la nota: ' . implode(', ', array_map(function($errors) { return implode(', ', $errors); }, $model->errors)),
                        'errors' => $model->errors,
                    ];
                }
            }
        } else {
            // Debug: Log si no se cargaron los datos
            Yii::info('DEBUG - No se pudieron cargar los datos del POST', 'notes');
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => false,
                'message' => 'No se recibieron datos válidos',
                'debug' => [
                    'post_data' => Yii::$app->request->post(),
                    'model_attributes' => $model->attributes,
                    'model_errors' => $model->errors
                ]
            ];
        }

        // Si no es AJAX, redirigir al índice
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Note model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id = null)
    {
        // Si no se pasa ID por URL, intentar obtenerlo del POST
        if (!$id) {
            $id = Yii::$app->request->post('Note')['id'] ?? null;
        }
        
        if (!$id) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'message' => 'ID de nota no proporcionado',
                ];
            }
            Yii::$app->session->setFlash('error', 'ID de nota no proporcionado.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Nota actualizada exitosamente',
                    'note' => $model->toArray(),
                ];
            }
            Yii::$app->session->setFlash('success', 'Nota actualizada exitosamente.');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => false,
                'message' => 'Error al actualizar la nota',
                'errors' => $model->errors,
            ];
        }

        // Si no es AJAX, renderizar la vista de edición
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Note model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'message' => 'Nota eliminada exitosamente',
            ];
        }

        Yii::$app->session->setFlash('success', 'Nota eliminada exitosamente.');
        return $this->redirect(['index']);
    }

    /**
     * Updates the status of a note
     * @param integer $id
     * @return mixed
     */
    public function actionUpdateStatus($id)
    {
        $model = $this->findModel($id);
        $status = Yii::$app->request->post('status');

        if (in_array($status, array_keys(Note::STATUSES))) {
            $model->status = $status;
            if ($model->save(false)) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'Estado actualizado exitosamente',
                        'status' => $status,
                        'statusName' => $model->getStatusName(),
                        'statusIcon' => $model->getStatusIcon(),
                    ];
                }
                Yii::$app->session->setFlash('success', 'Estado actualizado exitosamente.');
                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado',
            ];
        }

        Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
        return $this->redirect(['index']);
    }

    /**
     * Updates the status of a note (alternative endpoint)
     * @return mixed
     */
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        
        if (!$id || !$status) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'message' => 'ID y estado son requeridos',
                ];
            }
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $model->status = $status;
        
        if ($model->save(false)) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente',
                    'status' => $status,
                    'statusName' => $model->getStatusName(),
                    'statusIcon' => $model->getStatusIcon(),
                ];
            }
            Yii::$app->session->setFlash('success', 'Estado actualizado exitosamente.');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado',
            ];
        }

        Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
        return $this->redirect(['index']);
    }

    /**
     * Updates the position of a note
     * @return mixed
     */
    public function actionUpdatePosition()
    {
        try {
            $id = Yii::$app->request->post('id');
            $positionX = Yii::$app->request->post('position_x');
            $positionY = Yii::$app->request->post('position_y');

            // Validar parámetros
            if (!$id || $positionX === null || $positionY === null) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'ID y posición son requeridos',
                    ];
                }
                return $this->redirect(['index']);
            }

            // Actualización directa con SQL para evitar problemas con el modelo
            $db = Yii::$app->db;
            $command = $db->createCommand(
                'UPDATE notes SET position_x = :x, position_y = :y WHERE id = :id',
                [
                    ':x' => (int)$positionX,
                    ':y' => (int)$positionY,
                    ':id' => (int)$id
                ]
            );
            
            $result = $command->execute();

            if ($result > 0) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'Posición actualizada exitosamente',
                    ];
                }
                Yii::$app->session->setFlash('success', 'Posición actualizada exitosamente.');
                return $this->redirect(['index']);
            } else {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'No se encontró la nota o no se pudo actualizar',
                    ];
                }
                Yii::$app->session->setFlash('error', 'No se encontró la nota.');
                return $this->redirect(['index']);
            }

        } catch (Exception $e) {
            // Log del error
            Yii::error('Error en actionUpdatePosition: ' . $e->getMessage(), __METHOD__);
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'message' => 'Error interno del servidor: ' . $e->getMessage(),
                ];
            }
            Yii::$app->session->setFlash('error', 'Error interno del servidor.');
            return $this->redirect(['index']);
        }
    }

    /**
     * Gets notes data for AJAX requests
     * @return mixed
     */
    public function actionGetNotes()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $notes = Note::getAllNotes();
            $data = [];
            
            foreach ($notes as $note) {
                $data[] = [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => $note->content,
                    'color' => $note->color,
                    'colorName' => $note->getColorName(),
                    'colorClass' => $note->getColorClass(),
                    'status' => $note->status,
                    'statusName' => $note->getStatusName(),
                    'statusClass' => $note->getStatusClass(),
                    'statusIcon' => $note->getStatusIcon(),
                    'position_x' => $note->position_x,
                    'position_y' => $note->position_y,
                    'created_at' => Yii::$app->formatter->asRelativeTime($note->created_at),
                    'created_at_full' => $note->created_at,
                ];
            }
            
            return [
                'success' => true,
                'notes' => $data,
                'stats' => Note::getStats(),
            ];
        }

        throw new NotFoundHttpException('Página no encontrada.');
    }

    /**
     * Gets statistics for AJAX requests
     * @return mixed
     */
    public function actionGetStats()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'stats' => Note::getStats(),
            ];
        }

        throw new NotFoundHttpException('Página no encontrada.');
    }

    /**
     * Gets a specific note data for AJAX requests
     * @param integer $id
     * @return mixed
     */
    public function actionGetNote($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            try {
                $note = $this->findModel($id);
                return [
                    'success' => true,
                    'note' => [
                        'id' => $note->id,
                        'title' => $note->title,
                        'content' => $note->content,
                        'color' => $note->color,
                        'status' => $note->status,
                        'position_x' => $note->position_x,
                        'position_y' => $note->position_y,
                        'created_at' => $note->created_at,
                        'updated_at' => $note->updated_at,
                    ]
                ];
            } catch (NotFoundHttpException $e) {
                return [
                    'success' => false,
                    'message' => 'Nota no encontrada',
                ];
            }
        }

        throw new NotFoundHttpException('Página no encontrada.');
    }

    /**
     * Finds the Note model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Note the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Note::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La nota solicitada no existe.');
    }
}
