#!/bin/bash

# SOLUCIÓN ULTRA AGRESIVA - ELIMINAR Y RECREAR ARCHIVO
echo "=== SOLUCIÓN ULTRA AGRESIVA ==="

cd /var/www/html/app/factorentacar

echo "1. Haciendo backup del archivo problemático..."
sudo cp views/order/index.php views/order/index.php.backup

echo "2. Eliminando archivo problemático..."
sudo rm views/order/index.php

echo "3. Forzando reset completo..."
sudo git reset --hard HEAD
sudo git clean -fd

echo "4. Haciendo pull forzado..."
sudo git fetch origin master
sudo git reset --hard origin/master

echo "5. Verificando que el archivo existe..."
if [ -f "views/order/index.php" ]; then
    echo "✅ Archivo existe"
else
    echo "❌ Archivo no existe, restaurando desde backup..."
    sudo cp views/order/index.php.backup views/order/index.php
fi

echo "6. Verificando líneas problemáticas..."
echo "Línea 2620:"
sudo sed -n '2620p' views/order/index.php
echo "Línea 2621:"
sudo sed -n '2621p' views/order/index.php
echo "Línea 2622:"
sudo sed -n '2622p' views/order/index.php

echo "7. Reiniciando contenedor..."
sudo docker-compose restart app

echo "=== PROCESO COMPLETADO ==="
