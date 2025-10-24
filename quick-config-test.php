<?php
/**
 * Prueba rápida de configuración
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Yii2
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/config/web.php';

$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));

echo "<h1>🔧 Prueba Rápida de Configuración</h1>\n";

try {
    // Probar el modelo
    $companyInfo = \app\models\CompanyConfig::getCompanyInfo();
    echo "✅ Modelo CompanyConfig funciona<br>\n";
    
    // Mostrar información básica
    echo "<h3>Información de la Empresa:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Nombre:</strong> " . htmlspecialchars($companyInfo['name']) . "</li>\n";
    echo "<li><strong>Dirección:</strong> " . htmlspecialchars($companyInfo['address']) . "</li>\n";
    echo "<li><strong>Teléfono:</strong> " . htmlspecialchars($companyInfo['phone']) . "</li>\n";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($companyInfo['email']) . "</li>\n";
    echo "<li><strong>SIMPEMOVIL:</strong> " . htmlspecialchars($companyInfo['simemovil']) . "</li>\n";
    echo "</ul>\n";
    
    // Probar cuentas bancarias
    $bankAccounts = \app\models\CompanyConfig::getBankAccounts();
    echo "<h3>Cuentas Bancarias:</h3>\n";
    echo "<ul>\n";
    foreach ($bankAccounts as $account) {
        if (is_array($account)) {
            echo "<li><strong>" . htmlspecialchars($account['bank']) . ":</strong> " . htmlspecialchars($account['account']) . "</li>\n";
        }
    }
    echo "</ul>\n";
    
    echo "<h2>🎯 Prueba Exitosa</h2>\n";
    echo "<p>La configuración está funcionando correctamente.</p>\n";
    echo "<p><a href='/config/index'>Ir a Configuración de la Empresa</a></p>\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
?>
