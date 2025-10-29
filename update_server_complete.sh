#!/bin/bash

echo "=== SCRIPT DE ACTUALIZACIÓN COMPLETA DEL SERVIDOR ==="
echo "Fecha: $(date)"
echo ""

# Navegar al directorio del proyecto
echo "1. Navegando al directorio del proyecto..."
cd /var/www/html/app/factorentacar || {
    echo "❌ Error: No se pudo encontrar el directorio del proyecto"
    echo "Buscando directorio alternativo..."
    find /var/www -name "composer.json" -type f 2>/dev/null | head -3
    exit 1
}

echo "✅ Directorio actual: $(pwd)"
echo ""

# Verificar estado de Git
echo "2. Verificando estado de Git..."
git status --porcelain
echo ""

# Actualizar código
echo "3. Actualizando código desde GitHub..."
git fetch origin
git reset --hard origin/master
echo "✅ Código actualizado"
echo ""

# Limpiar cache completamente
echo "4. Limpiando cache..."
rm -rf runtime/cache/*
rm -rf web/assets/*
rm -rf runtime/logs/*.log
echo "✅ Cache limpiado"
echo ""

# Crear directorios necesarios
echo "5. Creando directorios necesarios..."
mkdir -p runtime/pdfs
mkdir -p runtime/mpdf_temp
mkdir -p web/assets
echo "✅ Directorios creados"
echo ""

# Configurar permisos
echo "6. Configurando permisos..."
chmod -R 777 runtime/
chmod -R 777 web/assets/
chown -R www-data:www-data runtime/
chown -R www-data:www-data web/assets/
echo "✅ Permisos configurados"
echo ""

# Reiniciar servicios Docker
echo "7. Reiniciando servicios Docker..."
docker-compose restart app
echo "✅ Servicios reiniciados"
echo ""

# Verificar que los servicios estén funcionando
echo "8. Verificando servicios..."
sleep 5
docker-compose ps
echo ""

# Verificar archivos clave
echo "9. Verificando archivos clave..."
echo "CSS del botón PDF2:"
if [ -f "web/css/rental-accordion.css" ]; then
    grep -A 5 "pdf2-icon" web/css/rental-accordion.css
else
    echo "❌ Archivo CSS no encontrado"
fi
echo ""

echo "Controlador PDF:"
if [ -f "controllers/PdfController.php" ]; then
    grep -c "actionGenerateMpdfAsync" controllers/PdfController.php
    echo "✅ Método asíncrono encontrado"
else
    echo "❌ Controlador no encontrado"
fi
echo ""

echo "Vista de órdenes:"
if [ -f "views/order/index.php" ]; then
    grep -c "downloadPdfAsync" views/order/index.php
    echo "✅ Función JavaScript encontrada"
else
    echo "❌ Vista no encontrada"
fi
echo ""

echo "=== ACTUALIZACIÓN COMPLETADA ==="
echo "Fecha: $(date)"
echo ""
echo "🔍 Para verificar los cambios:"
echo "1. Refresca la página con Ctrl+F5"
echo "2. Verifica que el botón PDF2 sea negro"
echo "3. Prueba la descarga asíncrona del PDF"
echo ""
