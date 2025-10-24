<?php
/**
 * Script para diagnosticar y posiblemente corregir el problema del tab de logo
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Yii2
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));

echo "<!DOCTYPE html>\n";
echo "<html lang='es'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Corrección Tab de Logo</title>\n";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".fix-box { border: 2px solid #28a745; padding: 20px; margin: 10px 0; background: #f8fff9; }\n";
echo ".error-box { border: 2px solid #dc3545; padding: 20px; margin: 10px 0; background: #fff8f8; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<h1>🔧 Corrección del Tab de Logo</h1>\n";

try {
    // Verificar si hay algún problema específico
    echo "<div class='fix-box'>\n";
    echo "<h2>1. Verificación del Sistema</h2>\n";
    
    $companyInfo = \app\models\CompanyConfig::getCompanyInfo();
    echo "<p>✅ Sistema funcionando correctamente</p>\n";
    echo "<p><strong>Empresa:</strong> " . htmlspecialchars($companyInfo['name']) . "</p>\n";
    echo "</div>\n";
    
    // Crear una versión corregida del tab de logo
    echo "<div class='fix-box'>\n";
    echo "<h2>2. Tab de Logo Corregido</h2>\n";
    echo "<p>Esta es una versión corregida del tab de logo. Si funciona aquí, el problema está en el archivo original.</p>\n";
    
    echo "<ul class='nav nav-tabs' id='fixedTabs' role='tablist'>\n";
    echo "<li class='nav-item' role='presentation'>\n";
    echo "<button class='nav-link active' id='fixed-info-tab' data-bs-toggle='tab' data-bs-target='#fixed-info' type='button' role='tab' aria-controls='fixed-info' aria-selected='true'>\n";
    echo "<i class='fas fa-building'></i> Información de la Empresa\n";
    echo "</button>\n";
    echo "</li>\n";
    echo "<li class='nav-item' role='presentation'>\n";
    echo "<button class='nav-link' id='fixed-logo-tab' data-bs-toggle='tab' data-bs-target='#fixed-logo' type='button' role='tab' aria-controls='fixed-logo' aria-selected='false'>\n";
    echo "<i class='fas fa-image'></i> Logo\n";
    echo "</button>\n";
    echo "</li>\n";
    echo "<li class='nav-item' role='presentation'>\n";
    echo "<button class='nav-link' id='fixed-files-tab' data-bs-toggle='tab' data-bs-target='#fixed-files' type='button' role='tab' aria-controls='fixed-files' aria-selected='false'>\n";
    echo "<i class='fas fa-folder'></i> Archivos\n";
    echo "</button>\n";
    echo "</li>\n";
    echo "</ul>\n";
    
    echo "<div class='tab-content' id='fixedTabsContent'>\n";
    
    // Tab de Información
    echo "<div class='tab-pane fade show active' id='fixed-info' role='tabpanel' aria-labelledby='fixed-info-tab'>\n";
    echo "<div class='p-4'>\n";
    echo "<h3>Información de la Empresa</h3>\n";
    echo "<p>Esta es la pestaña de información.</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Tab de Logo (corregido)
    echo "<div class='tab-pane fade' id='fixed-logo' role='tabpanel' aria-labelledby='fixed-logo-tab'>\n";
    echo "<div class='p-4'>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-12'>\n";
    echo "<div class='card'>\n";
    echo "<div class='card-header'>\n";
    echo "<h4 class='card-title'>\n";
    echo "<i class='fas fa-image'></i> Gestión del Logo de la Empresa\n";
    echo "</h4>\n";
    echo "<p class='card-subtitle text-muted'>Sube y gestiona el logo que aparecerá en las órdenes PDF</p>\n";
    echo "</div>\n";
    echo "<div class='card-body'>\n";
    
    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<div class='card'>\n";
    echo "<div class='card-header'>\n";
    echo "<h5><i class='fas fa-eye'></i> Logo Actual</h5>\n";
    echo "</div>\n";
    echo "<div class='card-body text-center'>\n";
    
    if ($companyInfo['logo']) {
        echo "<div class='mb-3'>\n";
        echo "<img src='" . htmlspecialchars($companyInfo['logo']) . "' alt='Logo actual' class='img-fluid' style='max-height: 200px; border: 2px solid #ddd; padding: 15px; border-radius: 10px; background: white;'>\n";
        echo "<p class='text-muted mt-2'><small>Logo actual (150x150px)</small></p>\n";
        echo "</div>\n";
        echo "<div class='d-flex gap-2 justify-content-center'>\n";
        echo "<a href='/config/preview-logo' class='btn btn-outline-info btn-sm' target='_blank'>\n";
        echo "<i class='fas fa-external-link-alt'></i> Ver Completo\n";
        echo "</a>\n";
        echo "<a href='/config/delete-logo' class='btn btn-outline-danger btn-sm' data-confirm='¿Estás seguro?' data-method='post'>\n";
        echo "<i class='fas fa-trash'></i> Eliminar\n";
        echo "</a>\n";
        echo "</div>\n";
    } else {
        echo "<div class='text-center text-muted py-4'>\n";
        echo "<i class='fas fa-image fa-4x mb-3'></i>\n";
        echo "<p class='h5'>No hay logo configurado</p>\n";
        echo "<p>Sube un logo para que aparezca en las órdenes PDF</p>\n";
        echo "</div>\n";
    }
    
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    echo "<div class='col-md-6'>\n";
    echo "<div class='card'>\n";
    echo "<div class='card-header'>\n";
    echo "<h5><i class='fas fa-upload'></i> Subir Nuevo Logo</h5>\n";
    echo "</div>\n";
    echo "<div class='card-body'>\n";
    
    echo "<div class='alert alert-info'>\n";
    echo "<i class='fas fa-info-circle'></i>\n";
    echo "<strong>Requisitos del Logo:</strong>\n";
    echo "<ul class='mb-0 mt-2'>\n";
    echo "<li><strong>Dimensiones:</strong> Cualquier tamaño (se redimensionará a 150x150px)</li>\n";
    echo "<li><strong>Formatos:</strong> PNG, JPG, JPEG, GIF, SVG</li>\n";
    echo "<li><strong>Tamaño máximo:</strong> 2MB</li>\n";
    echo "<li><strong>Procesamiento:</strong> Redimensionamiento automático</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<form action='/config/upload-logo' method='post' enctype='multipart/form-data'>\n";
    echo "<div class='mb-3'>\n";
    echo "<label for='logoFile' class='form-label'>Seleccionar Archivo de Logo</label>\n";
    echo "<input type='file' class='form-control' id='logoFile' name='CompanyConfig[logoFile]' accept='image/*' required>\n";
    echo "</div>\n";
    echo "<button type='submit' class='btn btn-primary btn-lg w-100'>\n";
    echo "<i class='fas fa-upload'></i> Subir y Procesar Logo\n";
    echo "</button>\n";
    echo "</form>\n";
    
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Tab de Archivos
    echo "<div class='tab-pane fade' id='fixed-files' role='tabpanel' aria-labelledby='fixed-files-tab'>\n";
    echo "<div class='p-4'>\n";
    echo "<h3>Archivos</h3>\n";
    echo "<p>Esta es la pestaña de archivos (solo condiciones).</p>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Botones de prueba
    echo "<div class='mt-4'>\n";
    echo "<h3>Pruebas de Funcionamiento</h3>\n";
    echo "<div class='btn-group' role='group'>\n";
    echo "<button type='button' class='btn btn-outline-success' onclick=\"document.getElementById('fixed-info-tab').click()\">\n";
    echo "Activar Info\n";
    echo "</button>\n";
    echo "<button type='button' class='btn btn-outline-success' onclick=\"document.getElementById('fixed-logo-tab').click()\">\n";
    echo "Activar Logo\n";
    echo "</button>\n";
    echo "<button type='button' class='btn btn-outline-success' onclick=\"document.getElementById('fixed-files-tab').click()\">\n";
    echo "Activar Archivos\n";
    echo "</button>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Enlaces
    echo "<div class='mt-4'>\n";
    echo "<h3>Enlaces</h3>\n";
    echo "<a href='/config/index' class='btn btn-primary'>Ir a Configuración Original</a>\n";
    echo "<a href='/test-minimal-logo-tab.php' class='btn btn-info'>Prueba Mínima</a>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error-box'>\n";
    echo "<h2>❌ Error</h2>\n";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "</div>\n";
}

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "<script>\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    console.log('Página de corrección cargada');\n";
echo "    \n";
echo "    // Verificar Bootstrap\n";
echo "    if (typeof bootstrap !== 'undefined') {\n";
echo "        console.log('✅ Bootstrap funcionando');\n";
echo "    } else {\n";
echo "        console.log('❌ Bootstrap no funciona');\n";
echo "    }\n";
echo "    \n";
echo "    // Agregar listeners para debug\n";
echo "    var tabs = document.querySelectorAll('#fixedTabs button');\n";
echo "    tabs.forEach(function(tab) {\n";
echo "        tab.addEventListener('click', function() {\n";
echo "            console.log('Tab clickeado:', this.textContent.trim());\n";
echo "        });\n";
echo "        tab.addEventListener('shown.bs.tab', function() {\n";
echo "            console.log('Tab mostrado:', this.textContent.trim());\n";
echo "        });\n";
echo "    });\n";
echo "});\n";
echo "</script>\n";

echo "</body>\n";
echo "</html>\n";
?>
