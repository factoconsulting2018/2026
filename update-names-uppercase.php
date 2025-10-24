<?php
// Script para actualizar nombres existentes a mayúsculas
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$config = require __DIR__ . '/config/web.php';

try {
    $app = new yii\web\Application($config);
    
    echo "=== ACTUALIZANDO NOMBRES A MAYÚSCULAS ===\n\n";
    
    // Obtener todos los clientes
    $clients = \app\models\Client::find()->all();
    $updated = 0;
    
    echo "🔍 Encontrados " . count($clients) . " clientes\n\n";
    
    foreach ($clients as $client) {
        $originalName = $client->full_name;
        $originalNombre = $client->nombre;
        $originalApellido = $client->apellido;
        
        // Convertir a mayúsculas
        $newFullName = strtoupper(trim($originalName));
        $newNombre = strtoupper(trim($originalNombre));
        $newApellido = strtoupper(trim($originalApellido));
        
        // Verificar si hay cambios
        if ($originalName !== $newFullName || $originalNombre !== $newNombre || $originalApellido !== $newApellido) {
            $client->full_name = $newFullName;
            $client->nombre = $newNombre;
            $client->apellido = $newApellido;
            
            if ($client->save(false)) {
                echo "✅ Actualizado: {$originalName} → {$newFullName}\n";
                $updated++;
            } else {
                echo "❌ Error actualizando: {$originalName}\n";
                foreach ($client->errors as $field => $errors) {
                    echo "   {$field}: " . implode(', ', $errors) . "\n";
                }
            }
        } else {
            echo "➡️ Sin cambios: {$originalName}\n";
        }
    }
    
    echo "\n=== ACTUALIZACIÓN COMPLETADA ===\n";
    echo "📊 Total actualizados: {$updated} clientes\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}




