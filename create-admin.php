<?php
/**
 * Script para crear usuario administrador inicial
 * Ejecutar: php create-admin.php
 */

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/web.php');
$application = new yii\web\Application($config);

echo "🚀 Creando usuario administrador...\n\n";

$user = new \app\models\Usuario();
$user->username = 'admin';
$user->email = 'admin@factorentacar.com';
$user->nombre = 'Administrador';
$user->apellido = 'Sistema';
$user->rol = 'SUPERADMIN';
$user->activo = 1;
$user->setPassword('admin123');
$user->generateAuthKey();
$user->generateAccessToken();

if ($user->save()) {
    echo "✅ Usuario creado exitosamente!\n\n";
    echo "📋 CREDENCIALES:\n";
    echo "─────────────────────────────\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Rol: SUPERADMIN\n";
    echo "─────────────────────────────\n\n";
    echo "🌐 Acceder a:\n";
    echo "http://localhost:8082/login\n\n";
    echo "⚠️  IMPORTANTE: Cambia la contraseña después del primer login\n";
} else {
    echo "❌ Error al crear usuario:\n";
    print_r($user->errors);
}

$application->end();

