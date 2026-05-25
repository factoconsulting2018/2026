<?php

use yii\db\Migration;

/**
 * Cambio de vehículo en orden de renta: referencia a orden padre y orden de reemplazo.
 */
class m260524_220000_add_vehicle_swap_to_rentals extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%rentals}}', 'parent_rental_id', $this->integer()->null()->after('car_id'));
        $this->addColumn('{{%rentals}}', 'swapped_to_rental_id', $this->integer()->null()->after('parent_rental_id'));
        $this->addColumn('{{%rentals}}', 'swap_date', $this->date()->null()->after('swapped_to_rental_id'));
        $this->addColumn('{{%rentals}}', 'swap_reason', $this->text()->null()->after('swap_date'));

        $this->createIndex('idx_rentals_parent_rental_id', '{{%rentals}}', 'parent_rental_id');
        $this->createIndex('idx_rentals_swapped_to_rental_id', '{{%rentals}}', 'swapped_to_rental_id');

        $this->addForeignKey(
            'fk_rentals_parent_rental',
            '{{%rentals}}',
            'parent_rental_id',
            '{{%rentals}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_rentals_swapped_to_rental',
            '{{%rentals}}',
            'swapped_to_rental_id',
            '{{%rentals}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_rentals_swapped_to_rental', '{{%rentals}}');
        $this->dropForeignKey('fk_rentals_parent_rental', '{{%rentals}}');
        $this->dropIndex('idx_rentals_swapped_to_rental_id', '{{%rentals}}');
        $this->dropIndex('idx_rentals_parent_rental_id', '{{%rentals}}');
        $this->dropColumn('{{%rentals}}', 'swap_reason');
        $this->dropColumn('{{%rentals}}', 'swap_date');
        $this->dropColumn('{{%rentals}}', 'swapped_to_rental_id');
        $this->dropColumn('{{%rentals}}', 'parent_rental_id');
    }
}
