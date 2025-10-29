#!/bin/bash

# VERIFICACIÓN COMPLETA Y CORRECCIÓN
echo "=== VERIFICACIÓN Y CORRECCIÓN COMPLETA ==="

cd /var/www/html/app/factorentacar

echo "1. Verificando archivo en servidor:"
ls -lah views/order/index.php

echo ""
echo "2. Contenido real de las líneas problemáticas:"
sed -n '2619,2625p' views/order/index.php

echo ""
echo "3. Verificando si hay caracteres ocultos:"
hexdump -C views/order/index.php | grep -A 5 "2620"

echo ""
echo "4. Buscando todas las ocurrencias de 'const overlay':"
grep -n "const overlay" views/order/index.php

echo ""
echo "5. Buscando 'captureFullScreen' en todo el proyecto:"
find . -name "*.php" -exec grep -l "captureFullScreen" {} \;

echo ""
echo "6. Verificando git status:"
git status views/order/index.php

echo ""
echo "7. Diferencias entre local y remoto:"
git diff HEAD origin/master -- views/order/index.php | head -30
