<?php

use yii\db\Migration;

/**
 * Permite marcar vehículos opcionales (ej. camión) para que no cuenten
 * como prioridad Facto en la regla condicional vs Moviliza.
 */
class m260822_173000_add_skip_priority_to_cars extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%cars}}',
            'skip_priority',
            $this->tinyInteger()->notNull()->defaultValue(0)->after('empresa')
        );
        $this->createIndex('idx_cars_skip_priority', '{{%cars}}', 'skip_priority');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_cars_skip_priority', '{{%cars}}');
        $this->dropColumn('{{%cars}}', 'skip_priority');
    }
}
