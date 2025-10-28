# Script de Despliegue en Producción - PowerShell
# Facto Rent a Car - Sistema de Alquiler de Vehículos

param(
    [switch]$SkipBackup
)

$ErrorActionPreference = "Stop"

Write-Host "🚀 Iniciando proceso de actualización en producción..." -ForegroundColor Green
Write-Host "📦 Repositorio: https://github.com/factoconsulting2018/2026" -ForegroundColor Cyan
Write-Host ""

# Verificar que estamos en el directorio correcto
if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "✗ No se encuentra docker-compose.yml" -ForegroundColor Red
    Write-Host "✓ Asegúrate de estar en el directorio raíz del proyecto" -ForegroundColor Green
    exit 1
}

# 1. Backup de la base de datos (opcional)
if (-not $SkipBackup) {
    Write-Host "✓ Creando backup de la base de datos..." -ForegroundColor Green
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    New-Item -ItemType Directory -Force -Path "backups" | Out-Null
    
    $backupCmd = "docker-compose exec -T mysql mysqldump -uroot -proot123 factorentacar_db"
    Invoke-Expression $backupCmd 2>$null | Out-File "backups/backup_$timestamp.sql"
    Write-Host "✓ Backup guardado en: backups/backup_$timestamp.sql" -ForegroundColor Green
}

# 2. Detener contenedores
Write-Host "✓ Deteniendo contenedores actuales..." -ForegroundColor Green
docker-compose down

# 3. Actualizar código desde GitHub
Write-Host "✓ Actualizando código desde GitHub..." -ForegroundColor Green
try {
    git pull origin master
    Write-Host "✓ Código actualizado exitosamente" -ForegroundColor Green
} catch {
    Write-Host "✗ No se pudo actualizar el código. Verifica tu conexión a GitHub." -ForegroundColor Red
}

# 4. Reconstruir la imagen Docker
Write-Host "✓ Reconstruyendo imagen Docker..." -ForegroundColor Green
docker-compose build --no-cache app

# 5. Iniciar contenedores
Write-Host "✓ Iniciando contenedores..." -ForegroundColor Green
docker-compose up -d

# Esperar a que los contenedores estén listos
Write-Host "✓ Esperando a que los contenedores estén listos..." -ForegroundColor Green
Start-Sleep -Seconds 10

# 6. Verificar que los contenedores estén corriendo
$containers = docker-compose ps
if ($containers -notmatch "Up") {
    Write-Host "✗ Algunos contenedores no se iniciaron correctamente" -ForegroundColor Red
    docker-compose logs
    exit 1
}

# 7. Actualizar dependencias de Composer
Write-Host "✓ Actualizando dependencias de Composer..." -ForegroundColor Green
docker-compose exec app composer install --no-dev --optimize-autoloader --no-interaction

# 8. Ejecutar migraciones de base de datos
Write-Host "✓ Ejecutando migraciones de base de datos..." -ForegroundColor Green
try {
    docker-compose exec app php yii migrate --interactive=0
} catch {
    Write-Host "⚠ No se pudieron ejecutar migraciones automáticamente" -ForegroundColor Yellow
}

# 9. Limpiar caché
Write-Host "✓ Limpiando caché de la aplicación..." -ForegroundColor Green
try {
    docker-compose exec app php yii cache/flush-all
} catch {
    Write-Host "⚠ No se pudo limpiar caché" -ForegroundColor Yellow
}

# 10. Verificar estado final
Write-Host "✓ Verificando estado final..." -ForegroundColor Green
docker-compose ps

# 11. Mostrar logs recientes
Write-Host "✓ Mostrando logs recientes (últimas 20 líneas)..." -ForegroundColor Green
docker-compose logs --tail=20

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "✅ Actualización completada exitosamente!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "🌐 Tu aplicación debería estar disponible en:" -ForegroundColor Green
Write-Host "   http://tu-servidor:8083" -ForegroundColor Yellow
Write-Host ""
Write-Host "📊 Estado de contenedores:"
docker-compose ps
Write-Host ""
Write-Host "📝 Para ver logs en tiempo real, ejecuta:" -ForegroundColor Yellow
Write-Host "   docker-compose logs -f" -ForegroundColor White
Write-Host ""

