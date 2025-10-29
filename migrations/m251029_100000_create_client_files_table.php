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
        $this->createTable('{{%client_files}}', [
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

        // Crear índices
        $this->createIndex('idx_client_files_client_id', '{{%client_files}}', 'client_id');
        $this->createIndex('idx_client_files_file_name', '{{%client_files}}', 'file_name');
        $this->createIndex('idx_client_files_created_at', '{{%client_files}}', 'created_at');

        // Clave foránea
        $this->addForeignKey(
            'fk_client_files_client',
            '{{%client_files}}',
            'client_id',
            '{{%clients}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
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

