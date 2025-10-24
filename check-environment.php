<?php
/**
 * Script de Verificación de Entorno
 * Facto Rent a Car - Sistema de Alquiler de Vehículos
 * 
 * Este script detecta automáticamente el entorno y muestra la configuración
 * que se utilizará para la conexión a la base de datos.
 */

// Incluir la clase de detección de entorno
require_once __DIR__ . '/config/db.php';

echo "🚗 FACTO RENT A CAR - VERIFICACIÓN DE ENTORNO\n";
echo "==============================================\n\n";

// Mostrar información del sistema
echo "📋 INFORMACIÓN DEL SISTEMA:\n";
echo "OS Family: " . PHP_OS_FAMILY . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Hostname: " . (getenv('HOSTNAME') ?: gethostname()) . "\n\n";

// Verificar archivos de detección
echo "🔍 ARCHIVOS DE DETECCIÓN:\n";
echo "/.dockerenv existe: " . (file_exists('/.dockerenv') ? '✅ SÍ' : '❌ NO') . "\n";
echo "/etc/systemd/system existe: " . (file_exists('/etc/systemd/system') ? '✅ SÍ' : '❌ NO') . "\n\n";

// Verificar variables de entorno
echo "🌍 VARIABLES DE ENTORNO:\n";
echo "DOCKER_CONTAINER: " . (getenv('DOCKER_CONTAINER') ?: 'No definida') . "\n";
echo "COMPOSE_PROJECT_NAME: " . (getenv('COMPOSE_PROJECT_NAME') ?: 'No definida') . "\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'No definida') . "\n";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? '***DEFINIDA***' : 'No definida') . "\n\n";

// Detectar entorno usando la clase
echo "🎯 DETECCIÓN DE ENTORNO:\n";
$dbConfig = EnvironmentDetector::getDatabaseConfig();

echo "Entorno detectado: " . strtoupper($dbConfig['environment']) . "\n";
echo "Host: " . $dbConfig['host'] . "\n";
echo "Puerto: " . $dbConfig['port'] . "\n";
echo "Base de datos: " . $dbConfig['dbname'] . "\n";
echo "Usuario: " . $dbConfig['username'] . "\n";
echo "Contraseña: " . (strlen($dbConfig['password']) > 0 ? '***DEFINIDA***' : '❌ NO DEFINIDA') . "\n\n";

// Construir DSN
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
echo "🔗 DSN de conexión: " . $dsn . "\n\n";

// Intentar conexión de prueba
echo "🧪 PRUEBA DE CONEXIÓN:\n";
try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ CONEXIÓN EXITOSA\n";
    echo "Versión MySQL: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
    echo "Charset actual: " . $pdo->query('SELECT @@character_set_connection')->fetchColumn() . "\n";
    
    // Verificar tablas principales
    $tables = $pdo->query("SHOW TABLES LIKE 'rentals'")->fetchAll();
    if (count($tables) > 0) {
        echo "✅ Tabla 'rentals' encontrada\n";
    } else {
        echo "⚠️  Tabla 'rentals' no encontrada\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
    echo "💡 Verifica que:\n";
    echo "   - El servidor MySQL esté ejecutándose\n";
    echo "   - Las credenciales sean correctas\n";
    echo "   - El puerto esté disponible\n";
    echo "   - La base de datos exista\n";
}

echo "\n==============================================\n";
echo "✅ Verificación completada\n";
?>
