<?php

use yii\db\Migration;

class m260515_140000_add_taller_to_maintenance_orders extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%maintenance_orders}}', 'taller', $this->string(255)->null()->after('order_date'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%maintenance_orders}}', 'taller');
    }
}
