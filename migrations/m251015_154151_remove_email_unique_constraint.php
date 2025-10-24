<?php

use yii\db\Migration;

/**
 * Class m251015_154151_remove_email_unique_constraint
 */
class m251015_154151_remove_email_unique_constraint extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Eliminar la restricción de unicidad del campo email
        $this->dropIndex('email', 'clients');
        
        // Crear un índice normal (no único) para mejorar el rendimiento de búsquedas
        $this->createIndex('idx_clients_email', 'clients', 'email');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Restaurar la restricción de unicidad del campo email
        $this->dropIndex('idx_clients_email', 'clients');
        $this->createIndex('email', 'clients', 'email', true);
    }
}
