<?php

use yii\db\Migration;

class m251017_011455_add_ejecutivo_fields_to_rentals extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('rentals', 'ejecutivo', $this->string(255)->null()->comment('Ejecutivo responsable del alquiler'));
        $this->addColumn('rentals', 'ejecutivo_otro', $this->string(255)->null()->comment('Nombre del ejecutivo cuando se selecciona "Otro"'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('rentals', 'ejecutivo');
        $this->dropColumn('rentals', 'ejecutivo_otro');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251017_011455_add_ejecutivo_fields_to_rentals cannot be reverted.\n";

        return false;
    }
    */
}
