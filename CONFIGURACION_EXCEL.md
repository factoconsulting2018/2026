# 📊 Configuración - Plantillas Excel para Clientes

## 🎯 Descripción
Sistema de importación y exportación de datos de clientes mediante archivos Excel, permitiendo la carga masiva de información.

## 🚀 Funcionalidades

### ✅ Exportar Plantilla
- **Ubicación**: Configuración > Gestión de Clientes
- **Formato**: Archivo Excel (.xlsx)
- **Contenido**: Plantilla con encabezados y datos de ejemplo
- **Uso**: Descargar, completar y reimportar

### ✅ Importar Clientes
- **Formatos soportados**: .xlsx, .xls
- **Validación**: Campos requeridos y duplicados
- **Resultado**: Carga masiva de clientes al sistema

## 📋 Estructura de la Plantilla

| Columna | Campo | Descripción | Requerido | Ejemplo |
|---------|-------|-------------|-----------|---------|
| A | Nombre Completo | Nombre y apellidos | ✅ Sí | Juan Pérez González |
| B | Cédula Física | Número de identificación | ✅ Sí | 123456789 |
| C | Email | Correo electrónico | ⚠️ Recomendado | juan@email.com |
| D | Teléfono | Número principal | ⚠️ Recomendado | 8888-8888 |
| E | WhatsApp | Número de WhatsApp | ⚪ Opcional | 8888-8888 |
| F | Dirección | Dirección física | ⚪ Opcional | San José, Costa Rica |
| G | Es Cliente Facto | 1=Sí, 0=No | ⚪ Opcional | 1 |
| H | Es Aliado | 1=Sí, 0=No | ⚪ Opcional | 0 |
| I | Estado | active/inactive | ⚪ Opcional | active |
| J | Notas | Información adicional | ⚪ Opcional | Cliente preferencial |

## 🔧 Instalación de Dependencias

### PhpSpreadsheet
```bash
composer require phpoffice/phpspreadsheet:^1.29 --ignore-platform-reqs
```

### Verificación
```bash
php -r "require 'vendor/autoload.php'; echo 'PhpSpreadsheet OK';"
```

## 📁 Archivos Creados

### Controlador
- `yii2-app/controllers/ConfigController.php`
  - `actionIndex()` - Página principal
  - `actionExportClientTemplate()` - Exportar plantilla
  - `actionImportClients()` - Importar clientes

### Vista
- `yii2-app/views/config/index.php`
  - Interfaz de usuario completa
  - Formulario de importación
  - Documentación integrada

### Menú
- Actualizado en `yii2-app/views/layouts/main.php`
- Enlace funcional a Configuración

## 🎨 Características de la Interfaz

### Diseño Moderno
- ✅ Cards con gradientes
- ✅ Iconos Material Design
- ✅ Responsive design
- ✅ Alertas informativas

### Validaciones
- ✅ Campos requeridos
- ✅ Duplicados de cédula
- ✅ Formatos de archivo
- ✅ Mensajes de error claros

### Experiencia de Usuario
- ✅ Plantilla con ejemplos
- ✅ Tabla de estructura
- ✅ Instrucciones paso a paso
- ✅ Estadísticas del sistema

## 🚀 Uso del Sistema

### 1. Acceder a Configuración
```
Menú Principal > Configuración
```

### 2. Descargar Plantilla
```
Gestión de Clientes > Descargar Plantilla Excel
```

### 3. Completar Datos
- Abrir archivo Excel descargado
- Completar filas con datos de clientes
- Guardar en formato .xlsx

### 4. Importar Clientes
```
Gestión de Clientes > Seleccionar archivo > Importar Clientes
```

## ⚠️ Consideraciones Importantes

### Campos Obligatorios
- **Nombre Completo**: No puede estar vacío
- **Cédula Física**: Debe ser única en el sistema

### Validaciones Automáticas
- ✅ Verificación de duplicados por cédula
- ✅ Validación de formato de email
- ✅ Control de estados válidos
- ✅ Manejo de errores detallado

### Límites del Sistema
- **Tamaño de archivo**: Máximo 10MB
- **Formatos**: Solo .xlsx y .xls
- **Memoria**: Optimizado para archivos grandes

## 🔍 Solución de Problemas

### Error: "PhpSpreadsheet no encontrado"
```bash
composer install --ignore-platform-reqs
```

### Error: "Archivo no válido"
- Verificar que sea .xlsx o .xls
- Comprobar que no esté corrupto

### Error: "Cliente ya existe"
- Verificar cédula en el sistema
- Usar cédula diferente o actualizar existente

## 📈 Beneficios

### Para el Usuario
- ✅ Carga masiva de clientes
- ✅ Plantilla predefinida
- ✅ Validación automática
- ✅ Interfaz intuitiva

### Para el Sistema
- ✅ Integración completa
- ✅ Manejo de errores robusto
- ✅ Escalabilidad
- ✅ Mantenimiento fácil

## 🎉 Estado del Proyecto

- ✅ **Controlador**: Completado
- ✅ **Vista**: Completada
- ✅ **Dependencias**: Instaladas
- ✅ **Menú**: Actualizado
- ✅ **Documentación**: Completa
- ✅ **Pruebas**: Exitosas

**¡Sistema listo para usar!** 🚀
