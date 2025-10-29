#!/bin/bash

# Script para incrementar automáticamente la versión
# Se ejecuta antes de cada commit

VERSION_FILE="config/version.php"
CURRENT_VERSION=$(grep "'version'" $VERSION_FILE | cut -d"'" -f4)

# Extraer número de versión (ej: "1.0" -> "1")
MAJOR=$(echo $CURRENT_VERSION | cut -d'.' -f1)
MINOR=$(echo $CURRENT_VERSION | cut -d'.' -f2)

# Incrementar versión menor
NEW_MINOR=$((MINOR + 1))
NEW_VERSION="$MAJOR.$NEW_MINOR"

# Actualizar archivo de versión
sed -i "s/'version' => '$CURRENT_VERSION'/'version' => '$NEW_VERSION'/" $VERSION_FILE
sed -i "s/'build' => '[^']*'/'build' => '$(date +%Y-%m-%d)'/" $VERSION_FILE

echo "✅ Versión actualizada de $CURRENT_VERSION a $NEW_VERSION"
echo "📅 Build: $(date +%Y-%m-%d)"

# Agregar archivo de versión al commit
git add $VERSION_FILE
