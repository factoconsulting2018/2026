#!/bin/bash

# CORRECCIÓN DIRECTA EN EL SERVIDOR
echo "=== CORRIGIENDO ERROR PHP EN EL SERVIDOR ==="

cd /var/www/html/app/factorentacar

echo "1. Haciendo backup..."
sudo cp views/order/index.php views/order/index.php.backup

echo "2. Corrigiendo línea 2583..."
sudo sed -i '2583s/register]s(/registerJs(/' views/order/index.php

echo "3. Verificando corrección..."
echo "Línea 2583:"
sudo sed -n '2583p' views/order/index.php

echo "4. Reiniciando contenedor..."
sudo docker-compose restart app

echo "=== PROCESO COMPLETADO ==="
