<?php

use yii\db\Migration;

/**
 * Tabla de plantillas de mensajes para campañas de marketing por WhatsApp.
 * Permite guardar el último mensaje (texto + imagen pública opcional) y reutilizarlo.
 */
class m260601_010000_create_marketing_templates_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('marketing_templates') !== null) {
            echo "    > La tabla marketing_templates ya existe.\n";
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('marketing_templates', [
            'id' => $this->primaryKey(),
            'name' => $this->string(160)->notNull(),
            'message_html' => $this->text()->null(),
            'message_text' => $this->text()->null(),
            'image_public_url' => $this->string(500)->null(),
            'image_filename' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_marketing_templates_name', 'marketing_templates', 'name');
        $this->createIndex('idx_marketing_templates_updated_at', 'marketing_templates', 'updated_at');
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('marketing_templates') !== null) {
            $this->dropTable('marketing_templates');
        }
    }
}
