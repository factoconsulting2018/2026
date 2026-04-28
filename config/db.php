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

if (!class_exists('EnvironmentDetector', false)) {
class EnvironmentDetector
{
    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_PORT = '3306';
    private const DEFAULT_USER = 'factorentacar_user';
    private const DEFAULT_DB = 'factorentacar_db';
    private const DEFAULT_PASSWORD = 'Ardillita60+';
    private const PRODUCTION_PASSWORD = 'Ardillita60+';

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
        $envHost = getenv('DB_HOST');
        $envPort = getenv('DB_PORT');
        $envUser = getenv('DB_USERNAME') ?: getenv('DB_USER');
        $envPassword = getenv('DB_PASSWORD');
        $envDbName = getenv('DB_DATABASE') ?: getenv('DB_NAME');

        // Priorizar variables de entorno cuando existan para evitar detecciones ambiguas.
        if ($envHost || $envPort || $envUser || $envPassword || $envDbName) {
            return [
                'host' => $envHost ?: self::DEFAULT_HOST,
                'port' => $envPort ?: self::DEFAULT_PORT,
                'username' => $envUser ?: self::DEFAULT_USER,
                'password' => $envPassword ?: self::PRODUCTION_PASSWORD,
                'dbname' => $envDbName ?: self::DEFAULT_DB,
                'environment' => getenv('APP_ENV') ?: 'env'
            ];
        }

        if (self::isDocker()) {
            // Entorno Docker - Desarrollo
            return [
                'host' => 'mysql',
                'port' => self::DEFAULT_PORT,
                'username' => self::DEFAULT_USER,
                'password' => self::DEFAULT_PASSWORD,
                'dbname' => self::DEFAULT_DB,
                'environment' => 'docker'
            ];
        } elseif (self::isLinuxProduction()) {
            // Entorno Linux - Producción
            return [
                'host' => self::DEFAULT_HOST,
                'port' => self::DEFAULT_PORT,
                'username' => self::DEFAULT_USER,
                'password' => getenv('DB_PASSWORD') ?: self::PRODUCTION_PASSWORD,
                'dbname' => self::DEFAULT_DB,
                'environment' => 'production'
            ];
        } else {
            // Entorno Windows - Desarrollo local
            return [
                'host' => self::DEFAULT_HOST,
                'port' => '3309', // Puerto externo de Docker
                'username' => self::DEFAULT_USER,
                'password' => self::DEFAULT_PASSWORD,
                'dbname' => self::DEFAULT_DB,
                'environment' => 'windows'
            ];
        }
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
