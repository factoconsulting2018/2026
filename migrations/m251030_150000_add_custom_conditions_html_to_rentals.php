<?php

use yii\db\Migration;

class m251030_150000_add_custom_conditions_html_to_rentals extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('rentals');
        if ($table && !$table->getColumn('custom_conditions_html')) {
            $this->addColumn('rentals', 'custom_conditions_html', $this->text()->null()->after('condiciones_especiales'));
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('rentals');
        if ($table && $table->getColumn('custom_conditions_html')) {
            $this->dropColumn('rentals', 'custom_conditions_html');
        }
    }
}


