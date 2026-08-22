<?php

use yii\db\Migration;

/**
 * Marca de anti-duplicado para el aviso WhatsApp 2h antes de correapartir.
 */
class m260822_150000_add_correapartir_reminder_sent_at_to_rentals extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%rentals}}',
            'correapartir_reminder_sent_at',
            $this->dateTime()->null()->after('fecha_correapartir')
        );
        $this->createIndex(
            'idx_rentals_correapartir_reminder',
            '{{%rentals}}',
            ['correapartir_enabled', 'fecha_correapartir', 'correapartir_reminder_sent_at']
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx_rentals_correapartir_reminder', '{{%rentals}}');
        $this->dropColumn('{{%rentals}}', 'correapartir_reminder_sent_at');
    }
}
