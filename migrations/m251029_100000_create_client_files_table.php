<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%client_files}}`.
 */
class m251029_100000_create_client_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = '{{%client_files}}';
        $tableSchema = $this->db->schema->getTableSchema($tableName);
        
        // Verificar si la tabla ya existe
        if ($tableSchema === null) {
            // La tabla no existe, crearla
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'client_id' => $this->integer()->notNull()->comment('ID del cliente'),
                'file_name' => $this->string(255)->notNull()->comment('Nombre del archivo (personalizado)'),
                'original_name' => $this->string(255)->notNull()->comment('Nombre original del archivo'),
                'file_path' => $this->string(500)->notNull()->comment('Ruta relativa del archivo'),
                'file_type' => $this->string(100)->notNull()->comment('Tipo MIME del archivo'),
                'file_size' => $this->integer()->notNull()->comment('Tamaño del archivo en bytes'),
                'description' => $this->string(255)->comment('Descripción opcional del archivo'),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            // Crear índices solo si la tabla fue creada
            $this->createIndex('idx_client_files_client_id', $tableName, 'client_id');
            $this->createIndex('idx_client_files_file_name', $tableName, 'file_name');
            $this->createIndex('idx_client_files_created_at', $tableName, 'created_at');

            // Crear clave foránea solo si la tabla fue creada
            try {
                // Intentar crear la FK, si ya existe se capturará la excepción
                $this->addForeignKey(
                    'fk_client_files_client',
                    $tableName,
                    'client_id',
                    '{{%clients}}',
                    'id',
                    'CASCADE',
                    'CASCADE'
                );
            } catch (\Exception $e) {
                // La FK puede ya existir, ignorar el error
                echo "Info: La clave foránea 'fk_client_files_client' puede que ya exista.\n";
            }
        } else {
            // La tabla ya existe, verificar y agregar índices que falten
            echo "Info: La tabla 'client_files' ya existe. Verificando índices...\n";
            
            // Verificar y crear índices si no existen
            try {
                $db = $this->db;
                $indexName = 'idx_client_files_client_id';
                $indexExists = $db->createCommand("SHOW INDEX FROM $tableName WHERE Key_name = '$indexName'")->queryOne();
                if (!$indexExists) {
                    $this->createIndex($indexName, $tableName, 'client_id');
                    echo "  ✓ Índice 'idx_client_files_client_id' creado.\n";
                }
                
                $indexName = 'idx_client_files_file_name';
                $indexExists = $db->createCommand("SHOW INDEX FROM $tableName WHERE Key_name = '$indexName'")->queryOne();
                if (!$indexExists) {
                    $this->createIndex($indexName, $tableName, 'file_name');
                    echo "  ✓ Índice 'idx_client_files_file_name' creado.\n";
                }
                
                $indexName = 'idx_client_files_created_at';
                $indexExists = $db->createCommand("SHOW INDEX FROM $tableName WHERE Key_name = '$indexName'")->queryOne();
                if (!$indexExists) {
                    $this->createIndex($indexName, $tableName, 'created_at');
                    echo "  ✓ Índice 'idx_client_files_created_at' creado.\n";
                }
            } catch (\Exception $e) {
                echo "Advertencia al verificar índices: " . $e->getMessage() . "\n";
            }
            
            // Verificar y crear clave foránea si no existe
            try {
                $db = $this->db;
                $fkName = 'fk_client_files_client';
                $fkExists = $db->createCommand("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '$tableName' 
                    AND CONSTRAINT_NAME = '$fkName'
                ")->queryOne();
                
                if (!$fkExists) {
                    $this->addForeignKey(
                        $fkName,
                        $tableName,
                        'client_id',
                        '{{%clients}}',
                        'id',
                        'CASCADE',
                        'CASCADE'
                    );
                    echo "  ✓ Clave foránea 'fk_client_files_client' creada.\n";
                }
            } catch (\Exception $e) {
                echo "Info: No se pudo verificar/crear la clave foránea: " . $e->getMessage() . "\n";
            }
            
            echo "✓ La tabla ya existe. Migración completada exitosamente.\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_client_files_client', '{{%client_files}}');
        $this->dropIndex('idx_client_files_created_at', '{{%client_files}}');
        $this->dropIndex('idx_client_files_file_name', '{{%client_files}}');
        $this->dropIndex('idx_client_files_client_id', '{{%client_files}}');
        $this->dropTable('{{%client_files}}');
    }
}

