<?php
namespace app\components;

use Yii;
use yii\filters\auth\AuthMethod;
use app\models\ApiKey;

/**
 * ApiKeyAuth es un filtro de autenticación que valida API Keys
 * 
 * Soporta autenticación mediante:
 * - Header: X-API-Key
 * - Query parameter: api_key
 */
class ApiKeyAuth extends AuthMethod
{
    /**
     * @var string el nombre del parámetro HTTP que contiene la API Key
     */
    public $headerName = 'X-API-Key';
    
    /**
     * @var string el nombre del parámetro de query que contiene la API Key
     */
    public $queryParam = 'api_key';

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $apiKey = null;

        // Intentar obtener la key del header
        $apiKeyString = $request->headers->get($this->headerName);
        
        // Si no está en el header, intentar del query parameter
        if (empty($apiKeyString)) {
            $apiKeyString = $request->get($this->queryParam);
        }

        if (empty($apiKeyString)) {
            $this->handleFailure($response);
            return null;
        }

        // Validar la key
        $apiKey = ApiKey::validateKey($apiKeyString);

        if ($apiKey === null) {
            $this->handleFailure($response);
            return null;
        }

        return $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', 'API Key');
    }

    /**
     * Maneja el fallo de autenticación
     */
    public function handleFailure($response)
    {
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'API Key inválida o no proporcionada',
                'details' => [
                    'hint' => 'Proporcione una API Key válida mediante el header X-API-Key o el parámetro api_key'
                ]
            ]
        ];
        $response->statusCode = 401;
        $response->send();
        Yii::$app->end();
    }
}

