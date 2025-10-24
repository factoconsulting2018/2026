# 🌍 Configuración Automática de Entorno
## Facto Rent a Car - Sistema de Alquiler de Vehículos

Este sistema detecta automáticamente el entorno de ejecución y configura la conexión a la base de datos apropiadamente.

## 🎯 Entornos Soportados

### 1. 🐳 **Docker (Desarrollo)**
- **Detección**: Archivo `/.dockerenv` existe
- **Host BD**: `mysql`
- **Puerto**: `3306`
- **Usuario**: `factorentacar_user`
- **Contraseña**: `factorenta2024!`

### 2. 🐧 **Linux (Producción)**
- **Detección**: Sistema Linux + nginx + systemd
- **Host BD**: `localhost`
- **Puerto**: `3306`
- **Usuario**: `factorentacar_user`
- **Contraseña**: Variable de entorno `DB_PASSWORD` o valor por defecto

### 3. 🪟 **Windows (Desarrollo Local)**
- **Detección**: Sistema Windows
- **Host BD**: `localhost`
- **Puerto**: `3309` (puerto externo de Docker)
- **Usuario**: `factorentacar_user`
- **Contraseña**: `factorenta2024!`

## 🚀 Uso Rápido

### Verificar Entorno Actual
```bash
php check-environment.php
```

### Desplegar Aplicación
```bash
chmod +x deploy.sh
./deploy.sh
```

## ⚙️ Configuración Manual

### Para Producción (Linux)

1. **Configurar variables de entorno**:
```bash
export APP_ENV=production
export DB_PASSWORD=tu_contraseña_segura
```

2. **O crear archivo `.env`**:
```bash
cp production.env.example .env
nano .env  # Editar con tus credenciales
```

3. **Configurar permisos**:
```bash
chmod 600 .env
chmod -R 750 runtime/
chmod -R 750 web/assets/
```

### Para Docker

1. **Usar docker-compose**:
```bash
docker-compose up -d --build
```

2. **Verificar contenedores**:
```bash
docker-compose ps
```

### Para Windows (Desarrollo)

1. **Iniciar Docker Desktop**
2. **Ejecutar contenedores**:
```bash
docker-compose up -d
```

3. **Acceder a la aplicación**:
```
http://localhost:8082
```

## 🔧 Archivos de Configuración

### `config/db.php`
- Contiene la clase `EnvironmentDetector`
- Detecta automáticamente el entorno
- Configura la conexión a BD según el entorno

### `check-environment.php`
- Script de verificación
- Muestra información del sistema
- Prueba la conexión a la base de datos

### `deploy.sh`
- Script de despliegue automático
- Configura permisos según el entorno
- Verifica archivos críticos

### `production.env.example`
- Plantilla para configuración de producción
- **NO subir al repositorio**
- Contiene credenciales sensibles

## 🛠️ Solución de Problemas

### Error de Conexión a BD

1. **Verificar entorno**:
```bash
php check-environment.php
```

2. **Verificar MySQL**:
```bash
# Docker
docker-compose logs mysql

# Linux
sudo systemctl status mysql
```

3. **Verificar puertos**:
```bash
# Ver puertos en uso
netstat -tulpn | grep :3306
```

### Error de Permisos

1. **Configurar permisos**:
```bash
chmod -R 755 runtime/
chmod -R 755 web/assets/
chmod -R 755 web/uploads/
```

2. **Propietario correcto**:
```bash
chown -R www-data:www-data /var/www/html/
```

### Variables de Entorno No Detectadas

1. **Verificar archivo `.env`**:
```bash
cat .env
```

2. **Cargar variables manualmente**:
```bash
source .env
```

## 📊 Logs de Debugging

Los logs de detección de entorno se guardan en:
- **Desarrollo**: `runtime/logs/app.log`
- **Producción**: `/var/log/nginx/error.log`

## 🔒 Seguridad

### Producción
- ✅ Cambiar contraseñas por defecto
- ✅ Usar variables de entorno para credenciales
- ✅ Configurar permisos restrictivos
- ✅ No subir archivos `.env` al repositorio

### Desarrollo
- ✅ Usar credenciales de desarrollo
- ✅ Permisos más permisivos para debugging
- ✅ Logs detallados habilitados

## 📞 Soporte

Si tienes problemas con la detección de entorno:

1. Ejecuta `php check-environment.php`
2. Revisa los logs en `runtime/logs/`
3. Verifica la configuración de MySQL
4. Contacta al equipo de desarrollo

---

**🚗 Facto Rent a Car - Sistema de Gestión Vehicular**

© 2024 Facto Consulting
