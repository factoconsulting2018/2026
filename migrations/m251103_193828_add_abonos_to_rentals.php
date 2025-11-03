<?php

use yii\db\Migration;

class m251103_193828_add_abonos_to_rentals extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('rentals', 'abono1_descripcion', $this->string(255)->null());
        $this->addColumn('rentals', 'abono1_monto', $this->decimal(10, 2)->null());
        $this->addColumn('rentals', 'abono2_descripcion', $this->string(255)->null());
        $this->addColumn('rentals', 'abono2_monto', $this->decimal(10, 2)->null());
        $this->addColumn('rentals', 'abono3_descripcion', $this->string(255)->null());
        $this->addColumn('rentals', 'abono3_monto', $this->decimal(10, 2)->null());
        $this->addColumn('rentals', 'abono4_descripcion', $this->string(255)->null());
        $this->addColumn('rentals', 'abono4_monto', $this->decimal(10, 2)->null());
        $this->addColumn('rentals', 'abono5_descripcion', $this->string(255)->null());
        $this->addColumn('rentals', 'abono5_monto', $this->decimal(10, 2)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('rentals', 'abono1_descripcion');
        $this->dropColumn('rentals', 'abono1_monto');
        $this->dropColumn('rentals', 'abono2_descripcion');
        $this->dropColumn('rentals', 'abono2_monto');
        $this->dropColumn('rentals', 'abono3_descripcion');
        $this->dropColumn('rentals', 'abono3_monto');
        $this->dropColumn('rentals', 'abono4_descripcion');
        $this->dropColumn('rentals', 'abono4_monto');
        $this->dropColumn('rentals', 'abono5_descripcion');
        $this->dropColumn('rentals', 'abono5_monto');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251103_193828_add_abonos_to_rentals cannot be reverted.\n";

        return false;
    }
    */
}
