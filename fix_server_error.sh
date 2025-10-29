#!/bin/bash

# Script para corregir el error JavaScript en el servidor
echo "=== Corrigiendo error JavaScript en el servidor ==="

# Navegar al directorio del proyecto
cd /var/www/html/app/factorentacar

echo "Directorio actual: $(pwd)"

# Verificar estado de git
echo "=== Estado de Git ==="
git status

# Hacer pull de los cambios
echo "=== Haciendo pull de GitHub ==="
git pull origin master

# Verificar que el archivo esté corregido
echo "=== Verificando corrección en línea 2620 ==="
sed -n '2620p' views/order/index.php

# Reiniciar el contenedor
echo "=== Reiniciando contenedor ==="
sudo docker-compose restart app

echo "=== Proceso completado ==="
