<?php

use yii\db\Migration;

/**
 * Cobro de choques a clientes: incidente (deuda) y abonos.
 */
class m260514_120000_create_incidents_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%incidents}}', [
            'id' => $this->primaryKey(),
            'client_id' => $this->integer()->notNull(),
            'total_amount' => $this->decimal(12, 2)->notNull(),
            'notes' => $this->text()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('open'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('idx_incidents_client', '{{%incidents}}', 'client_id');
        $this->createIndex('idx_incidents_status', '{{%incidents}}', 'status');
        $this->addForeignKey(
            'fk_incidents_client',
            '{{%incidents}}',
            'client_id',
            '{{%clients}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%incident_payments}}', [
            'id' => $this->primaryKey(),
            'incident_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(12, 2)->notNull(),
            'payment_date' => $this->date()->notNull(),
            'note' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('idx_incident_payments_incident', '{{%incident_payments}}', 'incident_id');
        $this->addForeignKey(
            'fk_incident_payments_incident',
            '{{%incident_payments}}',
            'incident_id',
            '{{%incidents}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_incident_payments_incident', '{{%incident_payments}}');
        $this->dropTable('{{%incident_payments}}');
        $this->dropForeignKey('fk_incidents_client', '{{%incidents}}');
        $this->dropTable('{{%incidents}}');
    }
}
