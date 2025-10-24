<?php
namespace app\components;

/**
 * Componente para consultar API de Hacienda Costa Rica
 */
class HaciendaApi
{
    /**
     * Consulta información de una cédula en Hacienda
     * @param string $cedula
     * @return array|null
     */
    public static function consultarCedula($cedula)
    {
        // Limpiar la cédula
        $cedula = preg_replace('/[^0-9]/', '', trim($cedula));
        
        if (strlen($cedula) < 9) {
            \Yii::error("Cédula muy corta: $cedula", __METHOD__);
            return null;
        }
        
        $url = 'https://api.hacienda.go.cr/fe/ae?identificacion=' . $cedula;
        \Yii::info("Consultando Hacienda para cédula: $cedula", __METHOD__);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: FactoRentACar/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curlError)) {
            \Yii::error("Error cURL: $curlError", __METHOD__);
            return null;
        }
        
        if ($httpCode !== 200) {
            \Yii::error("HTTP Error: $httpCode", __METHOD__);
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Yii::error("Error JSON: " . json_last_error_msg(), __METHOD__);
            return null;
        }
        
        if (empty($data)) {
            \Yii::warning("Respuesta vacía de Hacienda", __METHOD__);
            return null;
        }
        
        \Yii::info("Datos recibidos de Hacienda: " . json_encode($data), __METHOD__);
        return $data;
    }
    
    /**
     * Formatea la respuesta de Hacienda para uso en el sistema
     * @param array $data
     * @return array
     */
    public static function formatResponse($data)
    {
        if (empty($data)) {
            \Yii::warning("Datos vacíos en formatResponse", __METHOD__);
            return [
                'ok' => false,
                'msg' => 'No se encontró información',
            ];
        }
        
        \Yii::info("Formateando datos de Hacienda: " . json_encode($data), __METHOD__);
        
        $nombre = $data['nombre'] ?? '';
        $tipo = $data['tipoIdentificacion'] ?? '';
        
        // Situación tributaria - manejar diferentes formatos
        $situacion = '';
        if (isset($data['situacion']) && is_array($data['situacion'])) {
            $moroso = $data['situacion']['moroso'] ?? '';
            $omiso = $data['situacion']['omiso'] ?? '';
            
            if ($moroso === 'NO' && $omiso === 'NO') {
                $situacion = 'Al día';
            } else {
                $situacion = 'Moroso: ' . $moroso . ', Omiso: ' . $omiso;
            }
        }
        
        // Régimen
        $regimen = '';
        if (isset($data['regimen']) && is_array($data['regimen'])) {
            $regimen = $data['regimen']['descripcion'] ?? '';
        }
        
        // Primera actividad económica
        $actividadCodigo = '';
        $actividadDescripcion = '';
        if (isset($data['actividades']) && is_array($data['actividades']) && count($data['actividades']) > 0) {
            $actividadCodigo = $data['actividades'][0]['codigo'] ?? '';
            $actividadDescripcion = $data['actividades'][0]['descripcion'] ?? '';
        }
        
        $formattedData = [
            'ok' => true,
            'msg' => 'Información encontrada',
            'nombre' => $nombre,
            'tipoIdentificacion' => $tipo,
            'situacionTributaria' => $situacion,
            'regimenTributario' => $regimen,
            'actividadEconomica' => [
                'codigo' => $actividadCodigo,
                'descripcion' => $actividadDescripcion
            ],
            'actividades' => $data['actividades'] ?? [],
            'obligaciones' => $data['obligaciones'] ?? [],
        ];
        
        \Yii::info("Datos formateados: " . json_encode($formattedData), __METHOD__);
        return $formattedData;
    }
}

