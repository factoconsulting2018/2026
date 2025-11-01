<?php

use yii\db\Migration;

/**
 * Class m251201_000000_add_missing_columns_to_clients
 * Agrega las columnas faltantes a la tabla clients que están definidas en el modelo pero no existen en la BD
 */
class m251201_000000_add_missing_columns_to_clients extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        // Verificar y agregar telefono si no existe
        if (!$tableSchema->getColumn('telefono')) {
            $this->addColumn('clients', 'telefono', $this->string(255)->null()->after('whatsapp'));
            echo "✅ Columna 'telefono' agregada\n";
        } else {
            echo "ℹ️ Columna 'telefono' ya existe\n";
        }
        
        // Verificar y agregar celular si no existe
        if (!$tableSchema->getColumn('celular')) {
            $this->addColumn('clients', 'celular', $this->string(255)->null()->after('telefono'));
            echo "✅ Columna 'celular' agregada\n";
        } else {
            echo "ℹ️ Columna 'celular' ya existe\n";
        }
        
        // Verificar y agregar direccion si no existe (separada de address)
        if (!$tableSchema->getColumn('direccion')) {
            $this->addColumn('clients', 'direccion', $this->text()->null()->after('address'));
            echo "✅ Columna 'direccion' agregada\n";
        } else {
            echo "ℹ️ Columna 'direccion' ya existe\n";
        }
        
        // Verificar y agregar imagen_foto si no existe
        if (!$tableSchema->getColumn('imagen_foto')) {
            $this->addColumn('clients', 'imagen_foto', $this->string(255)->null()->after('direccion'));
            echo "✅ Columna 'imagen_foto' agregada\n";
        } else {
            echo "ℹ️ Columna 'imagen_foto' ya existe\n";
        }
        
        // Verificar y agregar licencia_conducir si no existe
        if (!$tableSchema->getColumn('licencia_conducir')) {
            $this->addColumn('clients', 'licencia_conducir', $this->string(255)->null()->after('imagen_foto'));
            echo "✅ Columna 'licencia_conducir' agregada\n";
        } else {
            echo "ℹ️ Columna 'licencia_conducir' ya existe\n";
        }
        
        // Verificar y agregar fecha_vencimiento_licencia si no existe
        if (!$tableSchema->getColumn('fecha_vencimiento_licencia')) {
            $this->addColumn('clients', 'fecha_vencimiento_licencia', $this->date()->null()->after('licencia_conducir'));
            echo "✅ Columna 'fecha_vencimiento_licencia' agregada\n";
        } else {
            echo "ℹ️ Columna 'fecha_vencimiento_licencia' ya existe\n";
        }
        
        // Verificar y agregar fecha_nacimiento si no existe
        if (!$tableSchema->getColumn('fecha_nacimiento')) {
            $this->addColumn('clients', 'fecha_nacimiento', $this->date()->null()->after('fecha_vencimiento_licencia'));
            echo "✅ Columna 'fecha_nacimiento' agregada\n";
        } else {
            echo "ℹ️ Columna 'fecha_nacimiento' ya existe\n";
        }
        
        // Verificar y agregar activo si no existe
        if (!$tableSchema->getColumn('activo')) {
            $this->addColumn('clients', 'activo', $this->integer(1)->defaultValue(1)->notNull()->after('es_cliente_facto'));
            echo "✅ Columna 'activo' agregada\n";
        } else {
            echo "ℹ️ Columna 'activo' ya existe\n";
        }
        
        // Verificar y agregar notas si no existe (notas es diferente de notes)
        if (!$tableSchema->getColumn('notas')) {
            $this->addColumn('clients', 'notas', $this->text()->null()->after('notes'));
            echo "✅ Columna 'notas' agregada\n";
        } else {
            echo "ℹ️ Columna 'notas' ya existe\n";
        }
        
        // Aumentar el tamaño de address si es muy pequeño (20 caracteres es muy poco)
        $addressColumn = $tableSchema->getColumn('address');
        if ($addressColumn && $addressColumn->dbType === 'varchar(20)') {
            $this->alterColumn('clients', 'address', $this->text()->null());
            echo "✅ Columna 'address' expandida a TEXT\n";
        } else {
            echo "ℹ️ Columna 'address' ya es TEXT o tiene tamaño adecuado\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir cambios
        if ($this->getDb()->getTableSchema('clients')->getColumn('notas')) {
            $this->dropColumn('clients', 'notas');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('activo')) {
            $this->dropColumn('clients', 'activo');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('fecha_nacimiento')) {
            $this->dropColumn('clients', 'fecha_nacimiento');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('fecha_vencimiento_licencia')) {
            $this->dropColumn('clients', 'fecha_vencimiento_licencia');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('licencia_conducir')) {
            $this->dropColumn('clients', 'licencia_conducir');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('imagen_foto')) {
            $this->dropColumn('clients', 'imagen_foto');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('direccion')) {
            $this->dropColumn('clients', 'direccion');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('celular')) {
            $this->dropColumn('clients', 'celular');
        }
        if ($this->getDb()->getTableSchema('clients')->getColumn('telefono')) {
            $this->dropColumn('clients', 'telefono');
        }
        
        // NO revertir address a varchar(20) porque puede tener datos más largos
        // Dejar address como TEXT para evitar errores de truncamiento
    }
}

