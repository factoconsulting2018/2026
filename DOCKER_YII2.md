# 🐳 Docker - Facto Rent a Car Yii2

## ✅ ESTADO: CONFIGURADO Y FUNCIONANDO

---

## 📦 CONTENEDORES CORRIENDO

### 1. **factorentacar_yii2** (Aplicación Yii2)
- **Imagen**: Custom (PHP 8.0-FPM + Nginx)
- **Puerto**: 8083 → 80
- **Estado**: ✅ Funcionando
- **Healthcheck**: Activo

### 2. **factorentacar_yii2_mysql** (Base de Datos)
- **Imagen**: MySQL 8.0
- **Puerto**: 3309 → 3306
- **Estado**: ✅ Funcionando
- **Base de datos**: factorentacar_db
- **Charset**: utf8mb4

### 3. **factorentacar_yii2_phpmyadmin** (Administrador BD)
- **Imagen**: phpMyAdmin
- **Puerto**: 8085 → 80
- **Estado**: ✅ Funcionando

---

## 🌐 RUTAS DE ACCESO

### **Aplicación Yii2** ⭐
```
http://localhost:8083
http://localhost:8083/login
```

**Credenciales**:
- Usuario: `admin` o `ronald`
- Password: (tu contraseña actual del sistema)

### **phpMyAdmin**
```
http://localhost:8085
```

**Credenciales**:
- Usuario: `root`
- Password: `root123`

### **MySQL Directo**
```
Host: localhost
Puerto: 3309
Base de datos: factorentacar_db
Usuario: factorentacar_user
Password: factorenta2024!
```

---

## 🎯 MÓDULOS DISPONIBLES

Una vez iniciado sesión en http://localhost:8083:

| Módulo | Ruta | Descripción |
|--------|------|-------------|
| 🏠 Dashboard | `/` | Panel principal con estadísticas |
| 👥 Clientes | `/client` | Gestión de clientes |
| 🚗 Vehículos | `/car` | Gestión de vehículos |
| 📋 Alquileres | `/rental` | Gestión de alquileres |
| 💰 Órdenes | `/order` | Gestión de órdenes |
| 🏛️ Hacienda | `/hacienda/consultar?id=112610049` | API Hacienda |

---

## 🔧 COMANDOS DOCKER ÚTILES

### Iniciar contenedores
```bash
cd yii2-app
docker-compose up -d
```

### Detener contenedores
```bash
docker-compose down
```

### Ver logs
```bash
# Todos los contenedores
docker-compose logs

# Solo la aplicación
docker-compose logs app

# Solo MySQL
docker-compose logs mysql

# Seguir logs en tiempo real
docker-compose logs -f app
```

### Ver estado
```bash
docker-compose ps
```

### Reiniciar un servicio
```bash
docker-compose restart app
docker-compose restart mysql
```

### Acceder al contenedor
```bash
# Acceder a la aplicación
docker-compose exec app bash

# Acceder a MySQL
docker-compose exec mysql bash
```

### Ejecutar comandos Yii2
```bash
# Limpiar caché
docker-compose exec app php yii cache/flush-all

# Ver rutas
docker-compose exec app php yii help

# Ejecutar migraciones
docker-compose exec app php yii migrate
```

### Importar/Exportar base de datos
```bash
# Exportar
docker-compose exec mysql mysqldump -uroot -proot123 factorentacar_db > backup.sql

# Importar (PowerShell)
Get-Content backup.sql | docker-compose exec -T mysql mysql -uroot -proot123 factorentacar_db
```

### Limpiar todo (CUIDADO)
```bash
# Detener y eliminar contenedores
docker-compose down

# Detener y eliminar contenedores + volúmenes
docker-compose down -v
```

---

## 📊 CONFIGURACIÓN DE PUERTOS

| Servicio | Puerto Externo | Puerto Interno | Conflicto |
|----------|----------------|----------------|-----------|
| App Yii2 | 8083 | 80 | ❌ Ninguno |
| MySQL | 3309 | 3306 | ❌ Ninguno |
| phpMyAdmin | 8085 | 80 | ❌ Ninguno |

**Nota**: Los puertos fueron configurados para evitar conflictos con el proyecto anterior (8082, 3306).

---

## 🗄️ BASE DE DATOS

### Tablas Principales
- ✅ `usuarios` - Usuarios del sistema
- ✅ `clients` - Clientes
- ✅ `cars` - Vehículos
- ✅ `rentals` - Alquileres
- ✅ `orders` - Órdenes (si existe)

### Configuración
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB
- **Timezone**: UTC

---

## 🔒 SEGURIDAD

### Variables de Entorno
Las contraseñas están en `docker-compose.yml`. Para producción:

1. Crear archivo `.env`:
```env
MYSQL_ROOT_PASSWORD=tu_password_seguro
MYSQL_PASSWORD=tu_password_seguro
```

2. Actualizar `docker-compose.yml`:
```yaml
environment:
  MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
  MYSQL_PASSWORD: ${MYSQL_PASSWORD}
```

### Firewall
En producción, cerrar puertos:
- MySQL (3309) - Solo acceso interno
- phpMyAdmin (8085) - Desactivar o proteger

---

## 🚀 DESPLIEGUE A PRODUCCIÓN

### 1. Actualizar configuración
```bash
# Editar config/db.php
# Cambiar a credenciales de producción
```

### 2. Optimizar
```bash
# Deshabilitar debug
# En config/web.php:
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
```

### 3. Iniciar en producción
```bash
docker-compose -f docker-compose.prod.yml up -d
```

---

## 🐛 TROUBLESHOOTING

### Problema: Contenedor no inicia
```bash
# Ver logs
docker-compose logs app

# Verificar permisos
docker-compose exec app ls -la runtime
docker-compose exec app chmod -R 777 runtime web/assets
```

### Problema: No conecta a MySQL
```bash
# Verificar que MySQL esté corriendo
docker-compose ps mysql

# Verificar conexión
docker-compose exec app php -r "new PDO('mysql:host=mysql;dbname=factorentacar_db', 'factorentacar_user', 'factorenta2024!');"
```

### Problema: Error 502 Bad Gateway
```bash
# Reiniciar PHP-FPM
docker-compose exec app supervisorctl restart php-fpm

# O reiniciar todo el contenedor
docker-compose restart app
```

### Problema: Cambios no se reflejan
```bash
# Reconstruir imagen
docker-compose build --no-cache app
docker-compose up -d
```

---

## 📈 MONITOREO

### Ver uso de recursos
```bash
docker stats
```

### Ver logs en tiempo real
```bash
docker-compose logs -f
```

### Inspeccionar contenedor
```bash
docker inspect factorentacar_yii2
```

---

## 🎉 RESUMEN

### ✅ Todo Configurado:
- ✅ 3 contenedores corriendo
- ✅ Base de datos importada
- ✅ Usuarios existentes
- ✅ Aplicación accesible
- ✅ phpMyAdmin disponible
- ✅ UTF-8 configurado
- ✅ Nginx optimizado
- ✅ PHP-FPM funcionando

### 🌐 Accede Ahora:
```
http://localhost:8083/login
```

### 👤 Usuarios:
- `admin` o `ronald`
- (tu contraseña actual)

---

**Estado**: ✅ Producción Ready  
**Fecha**: 2025  
**Docker Compose**: v3.8  
**PHP**: 8.0-FPM  
**MySQL**: 8.0  
**Nginx**: Latest  

🚀 **¡Tu aplicación Yii2 está corriendo en Docker!**

