<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Client;
use app\components\HaciendaApi;

/**
 * Comando para actualizar información tributaria de clientes de forma masiva
 */
class UpdateTributariaController extends Controller
{
    /**
     * Actualiza la información tributaria de todos los clientes que tienen cédula pero no tienen información tributaria
     * 
     * @param int $delay Segundos de espera entre consultas (por defecto 2)
     * @param bool $force Si es true, actualiza todos los clientes incluso si ya tienen información
     * @return int Exit code
     */
    public function actionIndex($delay = 2, $force = false)
    {
        $this->stdout("🚀 Iniciando actualización masiva de información tributaria...\n\n", \yii\helpers\Console::FG_GREEN);
        
        // Buscar clientes que necesitan actualización
        $query = Client::find()
            ->where(['not', ['cedula_fisica' => null]])
            ->andWhere(['!=', 'cedula_fisica', '']);
        
        if (!$force) {
            // Solo clientes sin información tributaria
            $query->andWhere([
                'or',
                ['situacion_tributaria' => null],
                ['situacion_tributaria' => ''],
                ['regimen_tributario' => null],
                ['regimen_tributario' => '']
            ]);
        }
        
        $clientes = $query->all();
        $total = count($clientes);
        
        if ($total === 0) {
            $this->stdout("✅ No hay clientes que necesiten actualización.\n", \yii\helpers\Console::FG_GREEN);
            return ExitCode::OK;
        }
        
        $this->stdout("📊 Total de clientes a procesar: {$total}\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("⏱️  Delay entre consultas: {$delay} segundos\n\n", \yii\helpers\Console::FG_YELLOW);
        
        $exitosos = 0;
        $fallidos = 0;
        $sinInfo = 0;
        $errores = [];
        
        foreach ($clientes as $index => $cliente) {
            $numero = $index + 1;
            $this->stdout("[{$numero}/{$total}] ", \yii\helpers\Console::FG_CYAN);
            $this->stdout("Procesando: {$cliente->full_name} (Cédula: {$cliente->cedula_fisica})... ");
            
            try {
                // Consultar API de Hacienda
                $rawData = HaciendaApi::consultarCedula($cliente->cedula_fisica);
                
                if ($rawData && !empty($rawData)) {
                    // Formatear los datos
                    $formattedData = HaciendaApi::formatResponse($rawData);
                    
                    if ($formattedData && isset($formattedData['ok']) && $formattedData['ok']) {
                        // Actualizar campos del cliente
                        $actualizado = false;
                        
                        if (!empty($formattedData['tipoIdentificacion'])) {
                            $cliente->tipo_identificacion = $formattedData['tipoIdentificacion'];
                            $actualizado = true;
                        }
                        
                        if (!empty($formattedData['situacionTributaria'])) {
                            $cliente->situacion_tributaria = $formattedData['situacionTributaria'];
                            $actualizado = true;
                        }
                        
                        if (!empty($formattedData['regimenTributario'])) {
                            $cliente->regimen_tributario = $formattedData['regimenTributario'];
                            $actualizado = true;
                        }
                        
                        if (!empty($formattedData['actividadEconomica']['codigo'])) {
                            $cliente->actividad_economica_codigo = $formattedData['actividadEconomica']['codigo'];
                            $actualizado = true;
                        }
                        
                        if (!empty($formattedData['actividadEconomica']['descripcion'])) {
                            $cliente->actividad_economica_descripcion = $formattedData['actividadEconomica']['descripcion'];
                            $actualizado = true;
                        }
                        
                        if ($actualizado) {
                            if ($cliente->save(false)) {
                                $this->stdout("✅ Actualizado\n", \yii\helpers\Console::FG_GREEN);
                                $exitosos++;
                            } else {
                                $this->stdout("⚠️  Error al guardar\n", \yii\helpers\Console::FG_YELLOW);
                                $fallidos++;
                                $errores[] = "Cliente ID {$cliente->id}: Error al guardar - " . implode(', ', $cliente->getFirstErrors());
                            }
                        } else {
                            $this->stdout("ℹ️  Sin cambios\n", \yii\helpers\Console::FG_BLUE);
                            $sinInfo++;
                        }
                    } else {
                        $this->stdout("❌ Sin información en Hacienda\n", \yii\helpers\Console::FG_RED);
                        $sinInfo++;
                    }
                } else {
                    $this->stdout("❌ No se encontró información\n", \yii\helpers\Console::FG_RED);
                    $sinInfo++;
                }
                
            } catch (\Exception $e) {
                $this->stdout("❌ Error: {$e->getMessage()}\n", \yii\helpers\Console::FG_RED);
                $fallidos++;
                $errores[] = "Cliente ID {$cliente->id}: {$e->getMessage()}";
            }
            
            // Esperar entre consultas para no saturar la API
            if ($numero < $total) {
                sleep($delay);
            }
        }
        
        // Mostrar resumen
        $this->stdout("\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("═══════════════════════════════════════\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("📊 RESUMEN DE ACTUALIZACIÓN\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("═══════════════════════════════════════\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("Total procesados: {$total}\n");
        $this->stdout("✅ Actualizados exitosamente: {$exitosos}\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("❌ Fallidos: {$fallidos}\n", \yii\helpers\Console::FG_RED);
        $this->stdout("ℹ️  Sin información disponible: {$sinInfo}\n", \yii\helpers\Console::FG_BLUE);
        
        if (!empty($errores)) {
            $this->stdout("\n⚠️  ERRORES DETALLADOS:\n", \yii\helpers\Console::FG_YELLOW);
            foreach ($errores as $error) {
                $this->stdout("  - {$error}\n", \yii\helpers\Console::FG_RED);
            }
        }
        
        $this->stdout("\n✅ Proceso completado.\n", \yii\helpers\Console::FG_GREEN);
        
        return ExitCode::OK;
    }
    
    /**
     * Muestra estadísticas de clientes con y sin información tributaria
     */
    public function actionStats()
    {
        $this->stdout("📊 Estadísticas de Información Tributaria\n\n", \yii\helpers\Console::FG_CYAN);
        
        $totalClientes = Client::find()->count();
        $conCedula = Client::find()
            ->where(['not', ['cedula_fisica' => null]])
            ->andWhere(['!=', 'cedula_fisica', ''])
            ->count();
        
        $sinInfoTributaria = Client::find()
            ->where(['not', ['cedula_fisica' => null]])
            ->andWhere(['!=', 'cedula_fisica', ''])
            ->andWhere([
                'or',
                ['situacion_tributaria' => null],
                ['situacion_tributaria' => ''],
                ['regimen_tributario' => null],
                ['regimen_tributario' => '']
            ])
            ->count();
        
        $conInfoTributaria = $conCedula - $sinInfoTributaria;
        
        $this->stdout("Total de clientes: {$totalClientes}\n");
        $this->stdout("Con cédula: {$conCedula}\n");
        $this->stdout("✅ Con información tributaria: {$conInfoTributaria}\n", \yii\helpers\Console::FG_GREEN);
        $this->stdout("❌ Sin información tributaria: {$sinInfoTributaria}\n", \yii\helpers\Console::FG_RED);
        
        if ($sinInfoTributaria > 0) {
            $porcentaje = round(($sinInfoTributaria / $conCedula) * 100, 2);
            $this->stdout("\nPorcentaje pendiente: {$porcentaje}%\n", \yii\helpers\Console::FG_YELLOW);
        }
        
        return ExitCode::OK;
    }
}

