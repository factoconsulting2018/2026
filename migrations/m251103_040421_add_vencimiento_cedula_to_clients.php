<?php

use yii\db\Migration;

class m251103_040421_add_vencimiento_cedula_to_clients extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        // Verificar y agregar fecha_vencimiento_cedula si no existe
        if (!$tableSchema->getColumn('fecha_vencimiento_cedula')) {
            $this->addColumn('clients', 'fecha_vencimiento_cedula', $this->date()->null()->after('cedula_fisica'));
            echo "✅ Columna 'fecha_vencimiento_cedula' agregada\n";
        } else {
            echo "ℹ️ Columna 'fecha_vencimiento_cedula' ya existe\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->getDb()->getTableSchema('clients')->getColumn('fecha_vencimiento_cedula')) {
            $this->dropColumn('clients', 'fecha_vencimiento_cedula');
            echo "✅ Columna 'fecha_vencimiento_cedula' eliminada\n";
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251103_040421_add_vencimiento_cedula_to_clients cannot be reverted.\n";

        return false;
    }
    */
}
