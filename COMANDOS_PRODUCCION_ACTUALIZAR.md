# Comandos para Actualizar Producción

## Ruta del Proyecto en Producción
```bash
cd /var/www/html/app/factorentacar
```

## 1. Actualizar Código desde Git
```bash
sudo git pull origin master
```

## 2. Actualizar Dependencias de Composer (si hay cambios)
```bash
sudo docker-compose exec app composer update --no-interaction
```

## 3. Ejecutar Migraciones de Base de Datos
```bash
sudo docker-compose exec app php /var/www/html/yii migrate --interactive=0
```

## 4. Limpiar Caché de la Aplicación
```bash
sudo docker-compose exec app php /var/www/html/yii cache/flush-all
```

## 5. Asegurar Permisos Correctos
```bash
sudo docker-compose exec app chmod -R 775 /var/www/html/runtime
sudo docker-compose exec app chmod -R 775 /var/www/html/web/assets
```

## 6. Reiniciar Contenedores (si es necesario)
```bash
sudo docker-compose restart app
```

## Secuencia Completa (Copiar y Pegar)
```bash
cd /var/www/html/app/factorentacar && \
sudo git pull origin master && \
sudo docker-compose exec app composer update --no-interaction && \
sudo docker-compose exec app php /var/www/html/yii migrate --interactive=0 && \
sudo docker-compose exec app php /var/www/html/yii cache/flush-all && \
sudo docker-compose exec app chmod -R 775 /var/www/html/runtime && \
sudo docker-compose exec app chmod -R 775 /var/www/html/web/assets && \
sudo docker-compose restart app
```

## Verificar Versión Actual
```bash
sudo docker-compose exec app php -r "require '/var/www/html/config/version.php'; print_r(require '/var/www/html/config/version.php');"
```

---

**Versión Actual:** 1.137  
**Fecha de Actualización:** 2025-01-02

**Cambios en esta versión:**
- Validación de formulario de clientes por pestañas
- Mensaje de error arriba del formulario cuando hay errores
- Resaltado de pestañas con errores de validación
- Campos de biblioteca de archivos ahora son opcionales
- Mejoras en la búsqueda del botón de subir archivo
- Corrección de error con campo `description` en ClientFile
- Corrección de error con campo `updated_at` en ClientFile
