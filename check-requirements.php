<?php
/**
 * Script de Verificación de Requisitos del Sistema
 * Ejecutar desde la línea de comandos: php check-requirements.php
 */

echo "====================================\n";
echo "VERIFICACIÓN DE REQUISITOS DEL SISTEMA\n";
echo "====================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar versión de PHP
echo "1. Verificando versión de PHP...\n";
$phpVersion = phpversion();
echo "   Versión PHP: $phpVersion\n";
if (version_compare($phpVersion, '8.2.0', '<')) {
    $errors[] = "PHP 8.2.0 o superior es requerido. Versión actual: $phpVersion";
    echo "   ❌ ERROR: PHP 8.2.0+ requerido\n\n";
} else {
    $success[] = "PHP $phpVersion cumple con los requisitos";
    echo "   ✅ PHP version OK\n\n";
}

// 2. Verificar extensión ZIP
echo "2. Verificando extensión ZIP...\n";
if (extension_loaded('zip')) {
    $zipVersion = phpversion('zip');
    echo "   Versión ZIP: " . ($zipVersion ?: 'instalada') . "\n";
    $success[] = "Extensión ZIP instalada";
    echo "   ✅ Extensión ZIP OK\n\n";
} else {
    $errors[] = "Extensión ZIP no está instalada. Requerida para generar archivos ZIP.";
    echo "   ❌ ERROR: Extensión ZIP no instalada\n\n";
}

// 3. Verificar extensión cURL (para llamadas HTTP asíncronas)
echo "3. Verificando extensión cURL...\n";
if (extension_loaded('curl')) {
    $curlVersion = curl_version();
    echo "   Versión cURL: " . ($curlVersion['version'] ?? 'instalada') . "\n";
    $success[] = "Extensión cURL instalada";
    echo "   ✅ Extensión cURL OK\n\n";
} else {
    $warnings[] = "Extensión cURL no está instalada. Recomendada para generación ZIP asíncrona.";
    echo "   ⚠️ ADVERTENCIA: Extensión cURL no instalada\n\n";
}

// 4. Verificar extensión GD (para imágenes)
echo "4. Verificando extensión GD...\n";
if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    echo "   GD Info: Instalada\n";
    $success[] = "Extensión GD instalada";
    echo "   ✅ Extensión GD OK\n\n";
} else {
    $warnings[] = "Extensión GD no está instalada. Puede afectar el procesamiento de imágenes.";
    echo "   ⚠️ ADVERTENCIA: Extensión GD no instalada\n\n";
}

// 5. Verificar Composer y dependencias
echo "5. Verificando Composer y dependencias...\n";
$vendorDir = __DIR__ . '/vendor';
if (is_dir($vendorDir)) {
    echo "   Directorio vendor: Existe\n";
    
    // Verificar autoload
    $autoloadFile = $vendorDir . '/autoload.php';
    if (file_exists($autoloadFile)) {
        echo "   autoload.php: Existe\n";
        require_once $autoloadFile;
        
        // Verificar mPDF
        $mpdfPath = $vendorDir . '/mpdf/mpdf/src/Mpdf.php';
        if (file_exists($mpdfPath)) {
            echo "   mPDF: Instalado\n";
            $success[] = "mPDF está instalado";
            echo "   ✅ mPDF OK\n\n";
        } else {
            $errors[] = "mPDF no está instalado. Ejecutar: composer require mpdf/mpdf";
            echo "   ❌ ERROR: mPDF no instalado\n\n";
        }
    } else {
        $errors[] = "autoload.php no existe. Ejecutar: composer install";
        echo "   ❌ ERROR: autoload.php no existe\n\n";
    }
} else {
    $errors[] = "Directorio vendor no existe. Ejecutar: composer install";
    echo "   ❌ ERROR: Directorio vendor no existe\n\n";
}

// 6. Verificar directorios críticos y permisos
echo "6. Verificando directorios y permisos...\n";
$directories = [
    'runtime' => 'Directorio runtime',
    'runtime/cache' => 'Directorio cache',
    'runtime/logs' => 'Directorio logs',
    'runtime/pdfs' => 'Directorio PDFs',
    'runtime/zips' => 'Directorio ZIPs',
    'runtime/mpdf_temp' => 'Directorio temporal mPDF',
    'web/assets' => 'Directorio assets',
];

foreach ($directories as $dir => $description) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        echo "   Creando directorio: $dir\n";
        @mkdir($fullPath, 0777, true);
    }
    
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath);
        $readable = is_readable($fullPath);
        
        echo "   $description: ";
        if ($writable && $readable) {
            echo "✅ OK (lectura/escritura)\n";
            $success[] = "$description es escribible";
        } elseif ($readable) {
            echo "⚠️ Solo lectura\n";
            $warnings[] = "$description no es escribible. Ejecutar: chmod -R 777 $dir";
        } else {
            echo "❌ Sin permisos\n";
            $errors[] = "$description no tiene permisos. Ejecutar: chmod -R 777 $dir";
        }
    } else {
        $errors[] = "No se puede crear $description";
        echo "   ❌ ERROR: No se puede crear directorio\n";
    }
}
echo "\n";

// 7. Verificar clase ZipArchive
echo "7. Verificando clase ZipArchive...\n";
if (class_exists('ZipArchive')) {
    try {
        $zip = new ZipArchive();
        echo "   ZipArchive: Disponible\n";
        $success[] = "Clase ZipArchive disponible";
        echo "   ✅ ZipArchive OK\n\n";
    } catch (Exception $e) {
        $errors[] = "Error al instanciar ZipArchive: " . $e->getMessage();
        echo "   ❌ ERROR: " . $e->getMessage() . "\n\n";
    }
} else {
    $errors[] = "Clase ZipArchive no disponible. Instalar extensión ZIP.";
    echo "   ❌ ERROR: ZipArchive no disponible\n\n";
}

// 8. Verificar configuración de base de datos
echo "8. Verificando configuración de base de datos...\n";
try {
    $config = require __DIR__ . '/config/db.php';
    if (isset($config['dsn'])) {
        echo "   DSN configurado: Sí\n";
        
        // Intentar conexión
        if (class_exists('PDO')) {
            try {
                $db = new PDO($config['dsn'], $config['username'] ?? '', $config['password'] ?? '');
                echo "   Conexión DB: ✅ OK\n";
                $success[] = "Base de datos conectada";
                echo "   ✅ Conexión DB OK\n\n";
            } catch (PDOException $e) {
                $warnings[] = "No se puede conectar a la base de datos: " . $e->getMessage();
                echo "   ⚠️ ADVERTENCIA: Error de conexión\n\n";
            }
        } else {
            $errors[] = "PDO no está disponible";
            echo "   ❌ ERROR: PDO no disponible\n\n";
        }
    } else {
        $warnings[] = "DSN no configurado en config/db.php";
        echo "   ⚠️ ADVERTENCIA: DSN no configurado\n\n";
    }
} catch (Exception $e) {
    $warnings[] = "No se puede leer config/db.php: " . $e->getMessage();
    echo "   ⚠️ ADVERTENCIA: No se puede leer configuración\n\n";
}

// 9. Verificar archivos de configuración importantes
echo "9. Verificando archivos de configuración...\n";
$configFiles = [
    'config/web.php' => 'Configuración web',
    'config/db.php' => 'Configuración base de datos',
    'config/params.php' => 'Parámetros',
    'config/version.php' => 'Versión',
    'controllers/Order2Controller.php' => 'Controlador Order2',
    'controllers/PdfController.php' => 'Controlador PDF',
];

foreach ($configFiles as $file => $description) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   $description: ✅ Existe\n";
    } else {
        $errors[] = "$description no existe: $file";
        echo "   $description: ❌ No existe\n";
    }
}
echo "\n";

// 10. Verificar espacio en disco
echo "10. Verificando espacio en disco...\n";
$freeSpace = disk_free_space(__DIR__);
$totalSpace = disk_total_space(__DIR__);
$freeSpaceMB = round($freeSpace / 1024 / 1024, 2);
$totalSpaceMB = round($totalSpace / 1024 / 1024, 2);
$percentFree = round(($freeSpace / $totalSpace) * 100, 2);

echo "   Espacio libre: {$freeSpaceMB} MB ({$percentFree}%)\n";
if ($freeSpaceMB < 100) {
    $warnings[] = "Poco espacio en disco: {$freeSpaceMB} MB. Se requiere al menos 100 MB.";
    echo "   ⚠️ ADVERTENCIA: Poco espacio disponible\n\n";
} else {
    $success[] = "Espacio en disco suficiente";
    echo "   ✅ Espacio en disco OK\n\n";
}

// Resumen
echo "====================================\n";
echo "RESUMEN DE VERIFICACIÓN\n";
echo "====================================\n\n";

echo "✅ ÉXITOS (" . count($success) . "):\n";
foreach ($success as $msg) {
    echo "   - $msg\n";
}
echo "\n";

if (count($warnings) > 0) {
    echo "⚠️ ADVERTENCIAS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   - $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORES (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   - $msg\n";
    }
    echo "\n";
    echo "ACCIONES REQUERIDAS:\n";
    echo "1. Instalar extensiones PHP faltantes:\n";
    echo "   sudo apt-get update\n";
    echo "   sudo apt-get install php8.2-zip php8.2-curl php8.2-gd\n";
    echo "   sudo docker-compose restart app\n";
    echo "\n";
    echo "2. Instalar dependencias Composer:\n";
    echo "   composer install --no-interaction\n";
    echo "\n";
    echo "3. Corregir permisos:\n";
    echo "   chmod -R 777 runtime web/assets\n";
    echo "   chown -R www-data:www-data runtime web/assets\n";
    echo "\n";
    
    exit(1);
} else {
    echo "✅ TODOS LOS REQUISITOS ESTÁN CUMPLIDOS\n\n";
    exit(0);
}

