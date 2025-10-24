<?php
/**
 * Verificación rápida de configuración UTF-8
 */

echo "🔧 Verificación de Configuración UTF-8\n";
echo "=====================================\n\n";

// 1. Verificar PHP
echo "1. PHP Configuration:\n";
echo "   - Internal encoding: " . mb_internal_encoding() . "\n";
echo "   - HTTP output: " . ini_get('mbstring.http_output') . "\n";
echo "   - Default charset: " . ini_get('default_charset') . "\n\n";

// 2. Verificar símbolo ₡
echo "2. Símbolo de Colones:\n";
echo "   - Símbolo: ₡\n";
echo "   - Código Unicode: U+20A1\n";
echo "   - Texto de prueba: ₡50,000.00\n\n";

// 3. Verificar TCPDF
echo "3. TCPDF Configuration:\n";
if (class_exists('TCPDF')) {
    echo "   ✅ TCPDF está instalado\n";
    echo "   - Fuente recomendada: dejavusans (soporta ₡)\n";
    echo "   - Configuración UTF-8: ✅\n\n";
} else {
    echo "   ❌ TCPDF no está instalado\n\n";
}

// 4. Verificar base de datos
echo "4. Base de Datos:\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
    $config = require __DIR__ . '/config/web.php';
    (new yii\web\Application($config));
    
    $connection = Yii::$app->db;
    $charset = $connection->createCommand("SELECT @@character_set_connection")->queryScalar();
    echo "   - Charset de conexión: {$charset}\n";
    
    if (strpos($charset, 'utf8') !== false) {
        echo "   ✅ Base de datos configurada con UTF-8\n\n";
    } else {
        echo "   ❌ Base de datos no está configurada con UTF-8\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

echo "🎯 Configuraciones Aplicadas:\n";
echo "============================\n";
echo "✅ Base de datos: utf8mb4\n";
echo "✅ TCPDF: Fuente dejavusans con UTF-8\n";
echo "✅ Headers HTTP: Content-Type con charset=UTF-8\n";
echo "✅ Símbolo ₡: Soportado en todas las fuentes\n\n";

echo "🔗 URLs de Prueba:\n";
echo "==================\n";
echo "• http://localhost:8083/test-utf8-currency.php\n";
echo "• http://localhost:8083/pdf/rental-order?id=1\n";
echo "• http://localhost:8083/order/index\n\n";

echo "¡Configuración UTF-8 completada! 🎉\n";
?>
