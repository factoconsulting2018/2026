#!/bin/bash

# SOLUCIÓN DIRECTA - EDITAR ARCHIVO EN EL SERVIDOR
echo "=== SOLUCIÓN DIRECTA - EDITANDO ARCHIVO ==="

cd /var/www/html/app/factorentacar

echo "1. Haciendo backup..."
sudo cp views/order/index.php views/order/index.php.backup

echo "2. Corrigiendo línea 2620 directamente..."
sudo sed -i '2620s/const overlay document.getElementById/const overlay = document.getElementById/' views/order/index.php

echo "3. Corrigiendo línea 2622 directamente..."
sudo sed -i '2622s/if (loverlay)/if (!overlay)/' views/order/index.php

echo "4. Verificando correcciones..."
echo "Línea 2620:"
sudo sed -n '2620p' views/order/index.php
echo "Línea 2621:"
sudo sed -n '2621p' views/order/index.php
echo "Línea 2622:"
sudo sed -n '2622p' views/order/index.php

echo "5. Reiniciando contenedor..."
sudo docker-compose restart app

echo "=== PROCESO COMPLETADO ==="
