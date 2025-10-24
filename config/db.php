<?php
/**
 * Configuración de Base de Datos para Yii2
 * Facto Rent a Car - Sistema de Alquiler de Vehículos
 * 
 * Detección automática de entorno:
 * - Docker: Contenedor de desarrollo
 * - Linux: Servidor de producción
 * - Windows: Desarrollo local
 */

class EnvironmentDetector
{
    /**
     * Detecta si estamos en un contenedor Docker
     */
    public static function isDocker()
    {
        return file_exists('/.dockerenv') || 
               getenv('DOCKER_CONTAINER') === 'true' ||
               getenv('COMPOSE_PROJECT_NAME') !== false ||
               (getenv('HOSTNAME') && strpos(getenv('HOSTNAME'), 'docker') !== false);
    }
    
    /**
     * Detecta si estamos en un servidor Linux de producción
     */
    public static function isLinuxProduction()
    {
        return PHP_OS_FAMILY === 'Linux' && 
               !self::isDocker() && 
               (getenv('APP_ENV') === 'production' || 
                file_exists('/etc/systemd/system') ||
                getenv('SERVER_SOFTWARE') === 'nginx' ||
                strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false);
    }
    
    /**
     * Detecta si estamos en Windows (desarrollo local)
     */
    public static function isWindows()
    {
        return PHP_OS_FAMILY === 'Windows';
    }
    
    /**
     * Obtiene la configuración de base de datos según el entorno
     */
    public static function getDatabaseConfig()
    {
        if (self::isDocker()) {
            // Entorno Docker - Desarrollo
            return [
                'host' => 'mysql',
                'port' => '3306',
                'username' => 'factorentacar_user',
                'password' => 'factorenta2024!',
                'dbname' => 'factorentacar_db',
                'environment' => 'docker'
            ];
        } elseif (self::isLinuxProduction()) {
            // Entorno Linux - Producción
            return [
                'host' => 'localhost',
                'port' => '3306',
                'username' => 'factorentacar_user',
                'password' => getenv('DB_PASSWORD') ?: 'TU_CONTRASEÑA_SEGURA_PRODUCCION',
                'dbname' => 'factorentacar_db',
                'environment' => 'production'
            ];
        } else {
            // Entorno Windows - Desarrollo local
            return [
                'host' => 'localhost',
                'port' => '3309', // Puerto externo de Docker
                'username' => 'factorentacar_user',
                'password' => 'factorenta2024!',
                'dbname' => 'factorentacar_db',
                'environment' => 'windows'
            ];
        }
    }
}

// Obtener configuración según el entorno detectado
$dbConfig = EnvironmentDetector::getDatabaseConfig();

// Log para debugging (solo en desarrollo)
if ($dbConfig['environment'] !== 'production') {
    error_log("🔍 Entorno detectado: " . $dbConfig['environment']);
    error_log("🔗 Host BD: " . $dbConfig['host'] . ":" . $dbConfig['port']);
}

$host = $dbConfig['host'];
$port = $dbConfig['port'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];
$dbname = $dbConfig['dbname'];

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host=$host;port=$port;dbname=$dbname",
    'username' => $username,
    'password' => $password,
    'charset' => 'utf8mb4',
    
    // Opciones de caché de esquema (solo en producción)
    'enableSchemaCache' => $dbConfig['environment'] === 'production',
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
    
    // Configuraciones adicionales para UTF-8
    'attributes' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
    
    'on afterOpen' => function($event) {
        // Asegurar UTF-8 en cada conexión
        $event->sender->createCommand("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci")->execute();
        $event->sender->createCommand("SET CHARACTER SET utf8mb4")->execute();
    },
];
