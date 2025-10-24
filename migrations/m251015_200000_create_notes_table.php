<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notes}}`.
 */
class m251015_200000_create_notes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notes}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull()->comment('Título de la nota'),
            'content' => $this->text()->comment('Contenido de la nota'),
            'color' => $this->string(20)->notNull()->defaultValue('yellow')->comment('Color del sticker'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('Estado: pending, processing, completed'),
            'position_x' => $this->integer()->defaultValue(100)->comment('Posición X en el panel'),
            'position_y' => $this->integer()->defaultValue(100)->comment('Posición Y en el panel'),
            'created_by' => $this->integer()->comment('ID del usuario que creó la nota'),
            'updated_by' => $this->integer()->comment('ID del usuario que actualizó la nota'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Fecha de creación'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Fecha de actualización'),
        ]);

        // Crear índices para optimizar consultas
        $this->createIndex('idx_notes_status', '{{%notes}}', 'status');
        $this->createIndex('idx_notes_color', '{{%notes}}', 'color');
        $this->createIndex('idx_notes_created_by', '{{%notes}}', 'created_by');
        $this->createIndex('idx_notes_created_at', '{{%notes}}', 'created_at');

        // Agregar clave foránea si existe tabla de usuarios
        // $this->addForeignKey('fk_notes_created_by', '{{%notes}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        // $this->addForeignKey('fk_notes_updated_by', '{{%notes}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar claves foráneas si existen
        // $this->dropForeignKey('fk_notes_created_by', '{{%notes}}');
        // $this->dropForeignKey('fk_notes_updated_by', '{{%notes}}');
        
        // Eliminar índices
        $this->dropIndex('idx_notes_created_at', '{{%notes}}');
        $this->dropIndex('idx_notes_created_by', '{{%notes}}');
        $this->dropIndex('idx_notes_color', '{{%notes}}');
        $this->dropIndex('idx_notes_status', '{{%notes}}');
        
        $this->dropTable('{{%notes}}');
    }
}
