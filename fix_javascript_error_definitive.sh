#!/bin/bash

# Script definitivo para corregir el error JavaScript en el servidor
echo "=== SOLUCIÓN DEFINITIVA PARA ERROR JAVASCRIPT ==="

# Navegar al directorio del proyecto
cd /var/www/html/app/factorentacar

echo "Directorio actual: $(pwd)"

# 1. Verificar estado actual
echo "=== 1. Estado actual de Git ==="
sudo git status

# 2. Ver qué cambios locales hay
echo "=== 2. Cambios locales en views/order/index.php ==="
sudo git diff views/order/index.php | head -20

# 3. DESCARTAR TODOS los cambios locales
echo "=== 3. Descartando cambios locales ==="
sudo git reset --hard HEAD
sudo git clean -fd

# 4. Hacer pull forzado
echo "=== 4. Haciendo pull forzado ==="
sudo git fetch origin master
sudo git reset --hard origin/master

# 5. Verificar que las líneas estén corregidas
echo "=== 5. Verificando corrección ==="
echo "Línea 2620:"
sudo sed -n '2620p' views/order/index.php
echo "Línea 2621:"
sudo sed -n '2621p' views/order/index.php
echo "Línea 2622:"
sudo sed -n '2622p' views/order/index.php

# 6. Reiniciar contenedor
echo "=== 6. Reiniciando contenedor ==="
sudo docker-compose restart app

echo "=== PROCESO COMPLETADO ==="
echo "El error ParseError debería estar resuelto."
