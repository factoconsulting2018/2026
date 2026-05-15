<?php

use yii\db\Migration;

/**
 * Órdenes de mantenimiento de vehículos.
 */
class m260515_120000_create_maintenance_orders_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%maintenance_orders}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->string(32)->notNull(),
            'car_id' => $this->integer()->notNull(),
            'order_date' => $this->date()->notNull(),
            'notes' => $this->text()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pendiente'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_maintenance_orders_order_id', '{{%maintenance_orders}}', 'order_id', true);
        $this->createIndex('idx_maintenance_orders_car', '{{%maintenance_orders}}', 'car_id');
        $this->createIndex('idx_maintenance_orders_status', '{{%maintenance_orders}}', 'status');
        $this->createIndex('idx_maintenance_orders_order_date', '{{%maintenance_orders}}', 'order_date');

        $this->addForeignKey(
            'fk_maintenance_orders_car',
            '{{%maintenance_orders}}',
            'car_id',
            '{{%cars}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_maintenance_orders_car', '{{%maintenance_orders}}');
        $this->dropTable('{{%maintenance_orders}}');
    }
}
