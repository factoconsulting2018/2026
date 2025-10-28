# 🚀 Guía de Despliegue en Producción - Facto Rent a Car

## 📦 **Repositorio en GitHub**
Tu código está en: [https://github.com/factoconsulting2018/2026](https://github.com/factoconsulting2018/2026)

---

## 📋 Proceso de Actualización en Producción

### 🔄 **Método Rápido: Usar Script Automático**

#### **En Linux/Mac:**
```bash
# Dar permisos de ejecución
chmod +x deploy-produccion.sh

# Ejecutar el script
./deploy-produccion.sh
```

#### **En Windows (PowerShell):**
```powershell
# Ejecutar el script
.\deploy-produccion.ps1
```

---

### 🔄 **Método Manual: Paso a Paso**

#### **Paso 1: Conectarse al Servidor de Producción**

```bash
# Conectarte por SSH al servidor de producción
ssh usuario@tu-servidor-produccion

# Ir al directorio del proyecto
cd /ruta/a/tu/proyecto
```

#### **Paso 2: Hacer Pull del Código Actualizado**

```bash
# Hacer pull de los últimos cambios desde GitHub
git pull origin master
```

### 🐳 **Paso 3: Reconstruir la Imagen Docker**

```bash
# Detener los contenedores actuales
docker-compose down

# Reconstruir la imagen con los nuevos cambios
docker-compose build --no-cache app

# Iniciar los contenedores
docker-compose up -d
```

### 🔄 **Paso 4: Actualizar Dependencias**

```bash
# Actualizar dependencias de Composer
docker-compose exec app composer install --no-dev --optimize-autoloader

# Si hay cambios en composer.json, actualizar
docker-compose exec app composer update --no-dev --optimize-autoloader
```

### 🔄 **Paso 5: Ejecutar Migraciones de Base de Datos**

```bash
# Ejecutar migraciones pendientes automáticamente
docker-compose exec app php yii migrate --interactive=0

# O si quieres confirmar primero
docker-compose exec app php yii migrate
```

### 🔄 **Paso 6: Limpiar Caché**

```bash
# Limpiar caché de Yii2
docker-compose exec app php yii cache/flush-all

# Limpiar caché de Twig (si usas)
docker-compose exec app php yii template/flush-cache
```

### 🔄 **Paso 7: Verificar que Todo Funciona**

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Ver estado de contenedores
docker-compose ps

# Verificar salud del contenedor
docker inspect factorentacar_yii2 | grep Health
```

---

## 📝 **Scripts de Despliegue Creados**

Ya existen scripts listos para usar:

### **`deploy-produccion.sh`** (Linux/Mac)
```bash
# Dar permisos de ejecución
chmod +x deploy-produccion.sh

# Ejecutar despliegue
./deploy-produccion.sh
```

### **`deploy-produccion.ps1`** (Windows PowerShell)
```powershell
# Ejecutar despliegue
.\deploy-produccion.ps1
```

Los scripts incluyen:
- ✅ Backup automático de la base de datos
- ✅ Actualización de código desde GitHub
- ✅ Reconstrucción de imagen Docker
- ✅ Actualización de dependencias
- ✅ Ejecución de migraciones
- ✅ Limpieza de caché
- ✅ Verificación de estado

---

## 🌐 **Despliegue desde Docker Hub/Registry**

Si estás usando un registro de Docker (Docker Hub, AWS ECR, etc.):

### **Push de la Imagen:**

```bash
# Construir y etiquetar la imagen
docker build -t tu-usuario/factorentacar:latest .

# Hacer push a Docker Hub
docker push tu-usuario/factorentacar:latest

# O a otro registry (ej: AWS ECR)
docker tag tu-usuario/factorentacar:latest 123456789.dkr.ecr.us-east-1.amazonaws.com/factorentacar:latest
docker push 123456789.dkr.ecr.us-east-1.amazonaws.com/factorentacar:latest
```

### **Pull en Producción:**

```bash
# Actualizar la imagen desde el registry
docker-compose pull app

# O hacer pull de una imagen específica
docker pull tu-usuario/factorentacar:latest

# Reiniciar con la nueva imagen
docker-compose down
docker-compose up -d
```

---

## 🔄 **Actualización sin Tiempo de Inactividad (Zero Downtime)**

### **Estrategia con Docker Swarm o Kubernetes:**

Si usas Docker Swarm:

```bash
# Actualizar el servicio con rolling update
docker service update --image tu-usuario/factorentacar:latest factorentacar_app
```

---

## 📝 **Configuración de Producción**

### **Variables de Entorno**

Crea un archivo `.env` para producción (no versionado):

```env
# .env
APP_ENV=production
DB_HOST=mysql
DB_NAME=factorentacar_db
DB_USER=factorentacar_user
DB_PASSWORD=TU_PASSWORD_SEGURO
```

### **Actualizar docker-compose.prod.yml:**

```yaml
services:
  app:
    image: tu-usuario/factorentacar:latest  # Usar imagen del registry
    restart: always
    env_file:
      - .env
    # ... resto de configuración
```

---

## 🆘 **Rollback (Revertir Cambios)**

Si algo sale mal:

```bash
# Ver versiones/historial de git
git log --oneline

# Volver a la versión anterior
git checkout <commit-anterior>

# O hacer rollback de la imagen
docker-compose down
docker pull tu-usuario/factorentacar:v1.0
docker-compose up -d
```

---

## 📊 **Monitoreo Post-Despliegue**

```bash
# Ver logs en tiempo real
docker-compose logs -f app

# Ver estadísticas de recursos
docker stats

# Verificar conectividad de base de datos
docker-compose exec app php yii
```

---

## ✅ **Checklist de Despliegue**

- [ ] Hacer backup de la base de datos antes de actualizar
- [ ] Verificar cambios en `git log`
- [ ] Ejecutar tests localmente (si existen)
- [ ] Actualizar código en producción
- [ ] Reconstruir imagen Docker
- [ ] Actualizar dependencias Composer
- [ ] Ejecutar migraciones
- [ ] Limpiar caché
- [ ] Verificar logs de errores
- [ ] Probar funcionalidades críticas
- [ ] Verificar rendimiento

---

## 🔒 **Seguridad en Producción**

1. **No exponer puertos innecesarios:**
   ```yaml
   # Cerrar phpMyAdmin en producción
   # O proteger con contraseña fuerte
   ```

2. **Usar contraseñas seguras:**
   ```bash
   # Generar contraseñas seguras
   openssl rand -base64 32
   ```

3. **Habilitar HTTPS:**
   ```yaml
   # Configurar SSL en Nginx
   ```

4. **Backups automáticos:**
   ```bash
   # Script de backup diario
   docker-compose exec mysql mysqldump -uroot -p factorentacar_db > backup_$(date +%Y%m%d).sql
   ```

---

## 💡 **Comandos Útiles**

```bash
# Ver logs específicos
docker-compose logs app | grep ERROR

# Entrar al contenedor
docker-compose exec app bash

# Ver uso de disco
docker system df

# Limpiar Docker (espacios no usados)
docker system prune -a

# Ver imágenes disponibles
docker images

# Ver contenedores corriendo
docker ps
```

---

**Estado**: ✅ Listo para Producción  
**Última Actualización**: 2025  
**Versión Docker**: 20.x+

