<?php

use yii\db\Migration;

/**
 * Class m251131_120000_add_medio_dia_fields_to_rentals
 */
class m251131_120000_add_medio_dia_fields_to_rentals extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Agregar campo para habilitar medio día
        $this->addColumn('rentals', 'medio_dia_enabled', $this->integer()->defaultValue(0)->notNull()->after('precio_por_dia'));
        
        // Agregar campo para el valor del medio día
        $this->addColumn('rentals', 'medio_dia_valor', $this->decimal(10, 2)->defaultValue(0)->after('medio_dia_enabled'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('rentals', 'medio_dia_valor');
        $this->dropColumn('rentals', 'medio_dia_enabled');
    }
}

