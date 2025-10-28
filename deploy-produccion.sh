#!/bin/bash
# Script de Despliegue en Producción
# Facto Rent a Car - Sistema de Alquiler de Vehículos

set -e  # Detener si hay algún error

echo "🚀 Iniciando proceso de actualización en producción..."
echo "📦 Repositorio: https://github.com/factoconsulting2018/2026"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
log_info() {
    echo -e "${GREEN}✓${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "docker-compose.yml" ]; then
    log_error "No se encuentra docker-compose.yml"
    log_info "Asegúrate de estar en el directorio raíz del proyecto"
    exit 1
fi

# 1. Backup de la base de datos (recomendado)
log_info "Creando backup de la base de datos..."
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mkdir -p backups
docker-compose exec -T mysql mysqldump -uroot -proot123 factorentacar_db > backups/backup_$TIMESTAMP.sql 2>/dev/null || log_warning "No se pudo crear backup automático"
log_info "Backup guardado en: backups/backup_$TIMESTAMP.sql"

# 2. Detener contenedores
log_info "Deteniendo contenedores actuales..."
docker-compose down

# 3. Actualizar código desde GitHub
log_info "Actualizando código desde GitHub..."
git pull origin master || log_error "No se pudo actualizar el código. Verifica tu conexión a GitHub."

# 4. Reconstruir la imagen Docker
log_info "Reconstruyendo imagen Docker..."
docker-compose build --no-cache app

# 5. Iniciar contenedores
log_info "Iniciando contenedores..."
docker-compose up -d

# Esperar a que los contenedores estén listos
log_info "Esperando a que los contenedores estén listos..."
sleep 10

# 6. Verificar que los contenedores estén corriendo
if ! docker-compose ps | grep -q "Up"; then
    log_error "Algunos contenedores no se iniciaron correctamente"
    docker-compose logs
    exit 1
fi

# 7. Actualizar dependencias de Composer
log_info "Actualizando dependencias de Composer..."
docker-compose exec app composer install --no-dev --optimize-autoloader --no-interaction

# 8. Ejecutar migraciones de base de datos
log_info "Ejecutando migraciones de base de datos..."
docker-compose exec app php yii migrate --interactive=0 || log_warning "No se pudieron ejecutar migraciones automáticamente"

# 9. Limpiar caché
log_info "Limpiando caché de la aplicación..."
docker-compose exec app php yii cache/flush-all || log_warning "No se pudo limpiar caché"

# 10. Verificar estado final
log_info "Verificando estado final..."
docker-compose ps

# 11. Mostrar logs recientes
log_info "Mostrando logs recientes (últimas 20 líneas)..."
docker-compose logs --tail=20

echo ""
echo "=================================="
log_info "✅ Actualización completada exitosamente!"
echo "=================================="
echo ""
echo "🌐 Tu aplicación debería estar disponible en:"
echo "   http://tu-servidor:8083"
echo ""
echo "📊 Estado de contenedores:"
docker-compose ps
echo ""
echo "📝 Para ver logs en tiempo real, ejecuta:"
echo "   docker-compose logs -f"
echo ""

