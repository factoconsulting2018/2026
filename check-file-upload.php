<?php
/**
 * Script de diagnóstico para subida de archivos en biblioteca de clientes
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

echo "=== DIAGNÓSTICO DE SUBIDA DE ARCHIVOS ===\n\n";

// 1. Verificar tabla client_files
echo "1. Verificando tabla client_files...\n";
try {
    $tableExists = Yii::$app->db->schema->getTableSchema('client_files', true);
    if ($tableExists) {
        echo "   ✅ Tabla 'client_files' existe\n";
        $columns = $tableExists->columns;
        echo "   Columnas encontradas: " . implode(', ', array_keys($columns)) . "\n";
    } else {
        echo "   ❌ ERROR: Tabla 'client_files' NO existe\n";
        echo "   Solución: Ejecutar migración: php yii migrate\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Solución: Ejecutar migración: php yii migrate\n\n";
}

// 2. Verificar directorio de uploads
echo "\n2. Verificando directorio de uploads...\n";
$uploadBaseDir = Yii::getAlias('@app/web/uploads');
$uploadClientsDir = Yii::getAlias('@app/web/uploads/clients');

echo "   Directorio base: $uploadBaseDir\n";
if (!is_dir($uploadBaseDir)) {
    echo "   ❌ ERROR: Directorio base no existe\n";
    echo "   Creando directorio...\n";
    if (@mkdir($uploadBaseDir, 0777, true)) {
        echo "   ✅ Directorio creado\n";
    } else {
        echo "   ❌ ERROR: No se pudo crear el directorio\n";
    }
} else {
    echo "   ✅ Directorio base existe\n";
}

echo "   Directorio clientes: $uploadClientsDir\n";
if (!is_dir($uploadClientsDir)) {
    echo "   ⚠️ Directorio de clientes no existe (se creará automáticamente)\n";
} else {
    echo "   ✅ Directorio de clientes existe\n";
}

// Verificar permisos
if (is_dir($uploadBaseDir)) {
    $isWritable = is_writable($uploadBaseDir);
    echo "   Permisos de escritura: " . ($isWritable ? "✅ Sí" : "❌ No") . "\n";
    if (!$isWritable) {
        echo "   Intentando corregir permisos...\n";
        @chmod($uploadBaseDir, 0777);
        if (is_writable($uploadBaseDir)) {
            echo "   ✅ Permisos corregidos\n";
        } else {
            echo "   ❌ No se pudieron corregir los permisos\n";
        }
    }
}

// 3. Verificar configuración PHP
echo "\n3. Verificando configuración PHP...\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "   memory_limit: " . ini_get('memory_limit') . "\n";

$uploadMaxSize = ini_get('upload_max_filesize');
$uploadMaxBytes = return_bytes($uploadMaxSize);
$requiredBytes = 10 * 1024 * 1024; // 10MB

if ($uploadMaxBytes >= $requiredBytes) {
    echo "   ✅ upload_max_filesize es suficiente (>= 10MB)\n";
} else {
    echo "   ⚠️ ADVERTENCIA: upload_max_filesize es menor a 10MB\n";
}

// 4. Verificar modelo ClientFile
echo "\n4. Verificando modelo ClientFile...\n";
try {
    $model = new \app\models\ClientFile();
    echo "   ✅ Modelo ClientFile cargado correctamente\n";
    echo "   Tabla: " . $model::tableName() . "\n";
} catch (\Exception $e) {
    echo "   ❌ ERROR al cargar modelo: " . $e->getMessage() . "\n";
}

// 5. Verificar rutas en web.php
echo "\n5. Verificando rutas configuradas...\n";
$routeFiles = [
    'client/upload-file/<id:\d+>' => 'client/upload-file',
    'client/list-files/<id:\d+>' => 'client/list-files',
    'client/delete-file/<id:\d+>' => 'client/delete-file',
    'client/download-file/<id:\d+>' => 'client/download-file',
];

$webConfig = require __DIR__ . '/config/web.php';
$urlRules = $webConfig['components']['urlManager']['rules'] ?? [];

$routesFound = 0;
foreach ($routeFiles as $pattern => $route) {
    $found = false;
    foreach ($urlRules as $rule) {
        if (is_array($rule) && isset($rule[$pattern]) && $rule[$pattern] === $route) {
            $found = true;
            break;
        } elseif (is_string($rule) && $rule === $pattern) {
            $found = true;
            break;
        }
    }
    if ($found) {
        echo "   ✅ Ruta configurada: $pattern\n";
        $routesFound++;
    } else {
        echo "   ❌ Ruta NO configurada: $pattern\n";
    }
}

if ($routesFound === count($routeFiles)) {
    echo "   ✅ Todas las rutas están configuradas\n";
} else {
    echo "   ⚠️ ADVERTENCIA: Faltan rutas en config/web.php\n";
}

// 6. Verificar acciones en ClientController
echo "\n6. Verificando acciones en ClientController...\n";
$controllerFile = __DIR__ . '/controllers/ClientController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    $actions = [
        'actionUploadFile',
        'actionListFiles',
        'actionDeleteFile',
        'actionDownloadFile'
    ];
    
    foreach ($actions as $action) {
        if (strpos($content, "function $action") !== false) {
            echo "   ✅ Método $action existe\n";
        } else {
            echo "   ❌ Método $action NO existe\n";
        }
    }
} else {
    echo "   ❌ ERROR: ClientController.php no encontrado\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

