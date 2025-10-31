<?php
/**
 * Script para corregir el tamaño de la columna estado_pago en la tabla rentals
 * 
 * Uso:
 *   php fix-estado-pago.php
 * 
 * O desde el navegador (solo en desarrollo/local):
 *   http://tu-dominio/fix-estado-pago.php
 */

// Cargar Yii2
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

try {
    $db = Yii::$app->db;
    
    // Verificar el tamaño actual de la columna
    $schema = $db->schema->getTableSchema('rentals');
    if (!$schema) {
        throw new Exception('La tabla rentals no existe');
    }
    
    $column = $schema->getColumn('estado_pago');
    if (!$column) {
        throw new Exception('La columna estado_pago no existe');
    }
    
    $currentSize = $column->size ?? 0;
    
    echo "Tamaño actual de estado_pago: " . ($currentSize > 0 ? $currentSize : "indefinido") . "\n";
    
    // Corregir el tamaño de la columna
    if ($currentSize < 20) {
        echo "Corrigiendo tamaño de estado_pago a VARCHAR(20)...\n";
        
        $sql = "ALTER TABLE `rentals` MODIFY COLUMN `estado_pago` VARCHAR(20) NOT NULL DEFAULT 'pendiente' COMMENT 'Estado de pago del alquiler'";
        
        $db->createCommand($sql)->execute();
        
        echo "✅ Columna estado_pago actualizada exitosamente a VARCHAR(20)\n";
        
        // Verificar que se aplicó correctamente
        $db->schema->refresh();
        $schema = $db->schema->getTableSchema('rentals');
        $column = $schema->getColumn('estado_pago');
        $newSize = $column->size ?? 0;
        
        echo "Nuevo tamaño de estado_pago: $newSize\n";
        
        if ($newSize >= 20) {
            echo "✅ Corrección aplicada correctamente. El error debería estar resuelto.\n";
        } else {
            echo "⚠️ Advertencia: El tamaño no se actualizó correctamente. Por favor, ejecuta el SQL manualmente.\n";
        }
    } else {
        echo "✅ La columna estado_pago ya tiene un tamaño adecuado ($currentSize caracteres).\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
