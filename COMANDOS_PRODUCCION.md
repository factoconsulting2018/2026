# 🚀 Comandos para Actualizar en Producción - Facto Rent a Car

## 📦 Información del Repositorio
- **Repositorio**: https://github.com/factoconsulting2018/2026
- **Branch**: master

---

## 🎯 Método Rápido (Recomendado)

### **Opción 1: Usar Script Automático (Más Seguro)**

#### En Linux/Mac:
```bash
chmod +x deploy-produccion.sh
./deploy-produccion.sh
```

#### En Windows PowerShell:
```powershell
.\deploy-produccion.ps1
```

---

## 📝 Método Manual (Comandos Individuales)

### **1. Conectarse al Servidor de Producción**

```bash
# Por SSH
ssh usuario@tu-servidor-produccion

# Ir al directorio del proyecto
cd /var/www/html  # o donde tengas el proyecto
```

### **2. Actualizar el Código desde GitHub**

```bash
# Hacer pull de los últimos cambios
git pull origin master
```

### **3. Detener y Reconstruir Contenedores**

```bash
# Detener contenedores actuales
docker-compose down

# Reconstruir la imagen
docker-compose build --no-cache app

# Iniciar contenedores
docker-compose up -d
```

### **4. Actualizar Dependencias**

```bash
# Actualizar Composer
docker-compose exec app composer install --no-dev --optimize-autoloader
```

### **5. Ejecutar Migraciones**

```bash
# Migraciones automáticas (sin confirmación)
docker-compose exec app php yii migrate --interactive=0

# O con confirmación
docker-compose exec app php yii migrate
```

### **6. Limpiar Caché**

```bash
# Limpiar caché de Yii2
docker-compose exec app php yii cache/flush-all
```

### **7. Verificar Estado**

```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f app

# Ver logs de todos los servicios
docker-compose logs
```

---

## 🔄 Comandos Útiles Adicionales

### **Ver Logs**
```bash
# Logs de la aplicación
docker-compose logs -f app

# Logs de MySQL
docker-compose logs -f mysql

# Últimas 50 líneas
docker-compose logs --tail=50

# Buscar errores en logs
docker-compose logs | grep -i error
```

### **Backup de Base de Datos**
```bash
# Crear backup
docker-compose exec mysql mysqldump -uroot -proot123 factorentacar_db > backup_$(date +%Y%m%d).sql

# En PowerShell (Windows)
docker-compose exec mysql mysqldump -uroot -proot123 factorentacar_db | Out-File -Encoding utf8 backup.sql
```

### **Restaurar Base de Datos**
```bash
# Linux/Mac
cat backup.sql | docker-compose exec -T mysql mysql -uroot -proot123 factorentacar_db

# Windows PowerShell
Get-Content backup.sql | docker-compose exec -T mysql mysql -uroot -proot123 factorentacar_db
```

### **Acceder al Contenedor**
```bash
# Entrar al contenedor de la aplicación
docker-compose exec app bash

# Entrar al contenedor de MySQL
docker-compose exec mysql bash
```

### **Reiniciar Servicios**
```bash
# Reiniciar solo la aplicación
docker-compose restart app

# Reiniciar MySQL
docker-compose restart mysql

# Reiniciar todo
docker-compose restart
```

### **Verificar Uso de Recursos**
```bash
# Estadísticas de recursos
docker stats

# Información de la red
docker network ls
docker network inspect

# Espacio usado en disco
docker system df
```

---

## 🚨 Troubleshooting

### **Problema: Error al hacer git pull**
```bash
# Verificar estado de git
git status

# Si hay cambios locales, hacer stash
git stash
git pull origin master
git stash pop
```

### **Problema: Contenedores no inician**
```bash
# Ver logs detallados
docker-compose logs

# Verificar configuración
docker-compose config

# Limpiar y reconstruir
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### **Problema: Error de permisos**
```bash
# Dar permisos a directorios
docker-compose exec app chmod -R 777 runtime
docker-compose exec app chmod -R 777 web/assets

# Dar propiedad al usuario www-data
docker-compose exec app chown -R www-data:www-data /var/www/html
```

### **Problema: Error de conexión a base de datos**
```bash
# Verificar que MySQL está corriendo
docker-compose ps mysql

# Probar conexión
docker-compose exec app php -r "new PDO('mysql:host=mysql;dbname=factorentacar_db', 'factorentacar_user', 'factorenta2024!');"
```

### **Problema: Cambios no se reflejan**
```bash
# Limpiar caché opcode
docker-compose exec app php yii cache/flush-all

# Limpiar cache de assets
docker-compose exec app rm -rf runtime/cache/*

# Reconstruir sin caché
docker-compose down
docker-compose build --no-cache app
docker-compose up -d
```

---

## 📊 Monitoreo Post-Despliegue

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Verificar salud de contenedores
docker-compose ps

# Estadísticas en tiempo real
docker stats

# Ver logs de errores
docker-compose logs app | grep -i error

# Ver logs de acceso
docker-compose logs app | grep GET
```

---

## ✅ Checklist de Despliegue

Antes de actualizar:
- [ ] Hacer backup de la base de datos
- [ ] Verificar que los cambios en GitHub son correctos
- [ ] Avisar a los usuarios sobre mantenimiento (si es necesario)

Durante la actualización:
- [ ] Conectarse al servidor por SSH
- [ ] Ejecutar script de despliegue o comandos manuales
- [ ] Verificar que no hay errores en los logs

Después de la actualización:
- [ ] Verificar que los contenedores están corriendo
- [ ] Probar funcionalidades críticas
- [ ] Verificar que no hay errores en logs
- [ ] Comunicar que el sistema está disponible

---

## 🔐 Seguridad

### **Variables de Entorno**
Las contraseñas están en `docker-compose.yml`. Para mayor seguridad:
1. Crear archivo `.env` en producción
2. No versionar el archivo `.env`
3. Usar contraseñas fuertes

### **Firewall**
```bash
# Cerrar puerto de phpMyAdmin en producción
# O proteger con autenticación adicional
```

### **Backups Automáticos**
```bash
# Agregar a crontab para backups diarios
0 2 * * * cd /ruta/proyecto && docker-compose exec mysql mysqldump -uroot -proot123 factorentacar_db > backups/backup_$(date +\%Y\%m\%d).sql
```

---

## 📞 Contacto

- **Repositorio**: https://github.com/factoconsulting2018/2026
- **Servidor**: Tu configuración de producción
- **Puerto App**: 8083
- **Puerto MySQL**: 3309
- **Puerto phpMyAdmin**: 8085

---

**Última Actualización**: 2025-01-27

