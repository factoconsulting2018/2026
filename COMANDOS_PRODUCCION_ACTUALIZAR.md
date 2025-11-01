# 🚀 Comandos para Actualizar en Producción

## 📋 Pasos para Actualizar

### 1. Conectarse al servidor y actualizar código
```bash
# Conectarse al servidor de producción por SSH
ssh usuario@tu-servidor-produccion

# Ir al directorio del proyecto
cd /var/www/html/app/factorentacar

# Actualizar código desde GitHub
sudo git pull origin master
```

### 2. Ejecutar migraciones (IMPORTANTE)
```bash
# Ejecutar TODAS las migraciones pendientes automáticamente
sudo docker-compose exec app php yii migrate --interactive=0
```

O si prefieres confirmar cada migración:
```bash
# Ejecutar migraciones con confirmación
sudo docker-compose exec app php yii migrate
```

### 3. Limpiar caché
```bash
# Limpiar caché de Yii2
sudo docker-compose exec app php yii cache/flush-all
```

### 4. Verificar estado (opcional)
```bash
# Ver estado de contenedores
sudo docker-compose ps

# Ver logs si hay algún problema
sudo docker-compose logs --tail=50 app
```

---

## ⚡ Resumen Rápido (Todo en uno)
```bash
cd /var/www/html/app/factorentacar
sudo git pull origin master
sudo docker-compose exec app php yii migrate --interactive=0
sudo docker-compose exec app php yii cache/flush-all
sudo docker-compose ps
```

---

## 📝 Notas Importantes

- **Siempre usar `sudo`** antes de los comandos git y docker-compose si hay problemas de permisos
- Las migraciones se ejecutan dentro del contenedor Docker
- Si los contenedores no están corriendo, iniciarlos con: `sudo docker-compose up -d`
- Si hay problemas, revisar logs con: `sudo docker-compose logs app`

