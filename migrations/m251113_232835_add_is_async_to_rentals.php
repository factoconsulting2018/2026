<?php

use yii\db\Migration;

/**
 * Handles adding column `is_async` to table `rentals`.
 */
class m251113_232835_add_is_async_to_rentals extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('rentals', 'is_async', $this->boolean()->notNull()->defaultValue(0)->after('medio_dia_valor'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('rentals', 'is_async');
    }
}

