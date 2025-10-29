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
            // Verificar si la FK ya existe
            $foreignKeys = $this->db->schema->getTableSchema($tableName)->foreignKeys ?? [];
            $fkExists = false;
            foreach ($foreignKeys as $fk) {
                if (isset($fk['client_id']) && $fk[0] === 'clients') {
                    $fkExists = true;
                    break;
                }
            }
            
            if (!$fkExists) {
                try {
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
                    echo "Advertencia: No se pudo crear la clave foránea (puede que ya exista): " . $e->getMessage() . "\n";
                }
            }
        } else {
            // La tabla ya existe, verificar y agregar columnas/índices que falten
            echo "La tabla 'client_files' ya existe. Verificando estructura...\n";
            
            $existingColumns = array_keys($tableSchema->columns);
            $requiredColumns = ['id', 'client_id', 'file_name', 'original_name', 'file_path', 'file_type', 'file_size', 'description', 'created_at', 'updated_at'];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $existingColumns)) {
                    echo "Columna '$column' no existe. Esta migración no puede agregarla automáticamente.\n";
                }
            }
            
            // Verificar índices
            try {
                $indexes = $this->db->schema->findIndexes($tableName);
                if (!isset($indexes['idx_client_files_client_id'])) {
                    $this->createIndex('idx_client_files_client_id', $tableName, 'client_id');
                }
                if (!isset($indexes['idx_client_files_file_name'])) {
                    $this->createIndex('idx_client_files_file_name', $tableName, 'file_name');
                }
                if (!isset($indexes['idx_client_files_created_at'])) {
                    $this->createIndex('idx_client_files_created_at', $tableName, 'created_at');
                }
            } catch (\Exception $e) {
                echo "Advertencia al verificar índices: " . $e->getMessage() . "\n";
            }
            
            echo "La tabla ya existe. Migración marcada como completada.\n";
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

