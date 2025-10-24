<?php

use yii\db\Migration;

/**
 * Handles adding order column to table `{{%notes}}`.
 */
class m251015_210000_add_order_to_notes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar columna order
        $this->addColumn('{{%notes}}', 'order', $this->integer()->defaultValue(0)->comment('Orden dentro del estado'));
        
        // Crear índice para mejorar rendimiento
        $this->createIndex(
            'idx_notes_status_order',
            '{{%notes}}',
            ['status', 'order']
        );
        
        // Asignar orden inicial a las notas existentes usando un enfoque diferente
        $this->execute("
            SET @row_number = 0;
            SET @prev_status = '';
            UPDATE notes 
            SET `order` = (
                CASE 
                    WHEN @prev_status = status THEN @row_number := @row_number + 1
                    ELSE @row_number := 1
                END
            ),
            @prev_status = status
            ORDER BY status, id;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Eliminar índice
        $this->dropIndex(
            'idx_notes_status_order',
            '{{%notes}}'
        );
        
        // Eliminar columna order
        $this->dropColumn('{{%notes}}', 'order');
    }
}
