<?php

use yii\db\Migration;

class m260519_120000_add_factura_fields_to_rentals extends Migration
{
    public function safeUp()
    {
        $this->addColumn('rentals', 'numero_factura', $this->string(100)->null()->comment('Número de factura fiscal'));
        $this->addColumn('rentals', 'fecha_factura', $this->date()->null()->comment('Fecha de emisión de la factura'));
    }

    public function safeDown()
    {
        $this->dropColumn('rentals', 'fecha_factura');
        $this->dropColumn('rentals', 'numero_factura');
    }
}
