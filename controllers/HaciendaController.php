<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\HaciendaApi;

/**
 * HaciendaController maneja las consultas al API de Hacienda
 */
class HaciendaController extends Controller
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
        ];
    }

    /**
     * Consulta una cédula en el API de Hacienda
     * @return array
     */
    public function actionConsultar()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $request = Yii::$app->request;
        $cedula = null;
        
        // Obtener cédula desde POST o GET
        if ($request->isPost) {
            $postData = json_decode($request->getRawBody(), true);
            $cedula = $postData['cedula'] ?? null;
        } else {
            $cedula = $request->get('id');
        }
        
        if (empty($cedula)) {
            return [
                'success' => false,
                'message' => 'Parámetro "cedula" requerido',
                'data' => null,
            ];
        }
        
        try {
            // Obtener datos directamente de la API
            $rawData = HaciendaApi::consultarCedula($cedula);
            
            if ($rawData && !empty($rawData)) {
                // Formatear los datos usando el método formatResponse
                $formattedData = HaciendaApi::formatResponse($rawData);
                
                if ($formattedData && $formattedData['ok']) {
                    return [
                        'success' => true,
                        'message' => 'Consulta exitosa',
                        'data' => [
                            'cedula' => $cedula,
                            'nombre' => $formattedData['nombre'] ?? '',
                            'tipoIdentificacion' => $formattedData['tipoIdentificacion'] ?? '',
                            'situacionTributaria' => $formattedData['situacionTributaria'] ?? '',
                            'regimenTributario' => $formattedData['regimenTributario'] ?? '',
                            'actividadEconomica' => [
                                'codigo' => $formattedData['actividadEconomica']['codigo'] ?? '',
                                'descripcion' => $formattedData['actividadEconomica']['descripcion'] ?? ''
                            ]
                        ]
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'No se encontró información para esta cédula',
                'data' => null,
            ];
            
        } catch (\Exception $e) {
            \Yii::error('Error en HaciendaController: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al consultar: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }
}

