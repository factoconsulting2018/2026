<?php

use yii\db\Migration;

/**
 * Class m251104_023751_create_api_keys_table
 * Crea la tabla api_keys para almacenar las API Keys del sistema
 */
class m251104_023751_create_api_keys_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('api_keys', [
            'id' => $this->primaryKey(),
            'key' => $this->string(64)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'is_active' => $this->integer(1)->defaultValue(1)->notNull(),
            'last_used_at' => $this->datetime()->null(),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Crear índices para búsqueda rápida
        $this->createIndex('idx_api_keys_key', 'api_keys', 'key');
        $this->createIndex('idx_api_keys_is_active', 'api_keys', 'is_active');
        
        echo "✅ Tabla 'api_keys' creada exitosamente\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_api_keys_is_active', 'api_keys');
        $this->dropIndex('idx_api_keys_key', 'api_keys');
        $this->dropTable('api_keys');
        
        echo "✅ Tabla 'api_keys' eliminada\n";
    }
}

