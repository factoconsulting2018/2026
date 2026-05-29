<?php

use yii\db\Migration;

/**
 * Agrega la marca "JAC" a la tabla `brands` si no existe.
 */
class m260529_071500_add_jac_brand extends Migration
{
    public function safeUp()
    {
        $existing = (new \yii\db\Query())
            ->from('brands')
            ->where(['UPPER([[name]])' => 'JAC'])
            ->limit(1)
            ->one($this->db);

        if ($existing) {
            echo "    > La marca 'JAC' ya existe (id={$existing['id']}). Nada que hacer.\n";
            return;
        }

        $now = new \yii\db\Expression('NOW()');
        $this->insert('brands', [
            'name' => 'JAC',
            'description' => 'JAC Motors',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "    > Marca 'JAC' agregada.\n";
    }

    public function safeDown()
    {
        $this->delete('brands', ['name' => 'JAC']);
    }
}
