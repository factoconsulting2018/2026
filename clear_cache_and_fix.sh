#!/bin/bash

# SOLUCIÓN DEFINITIVA - CACHE Y OPCODE
echo "=== ELIMINANDO CACHE Y OPCODE ==="

cd /var/www/html/app/factorentacar

# 1. Eliminar cache de Yii2
echo "1. Eliminando cache de runtime..."
sudo rm -rf runtime/cache/*
sudo rm -rf runtime/temp/*

# 2. Limpiar opcache
echo "2. Limpiando opcache..."
sudo docker-compose exec -T app php -r "opcache_reset();"

# 3. Forzar reinicio completo del contenedor
echo "3. Reiniciando contenedor completamente..."
sudo docker-compose down
sudo docker-compose up -d

# 4. Verificar archivo
echo "4. Verificando archivo..."
sudo grep -n "const overlay" views/order/index.php

echo "=== PROCESO COMPLETADO ==="
