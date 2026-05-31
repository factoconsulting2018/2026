<?php

use yii\db\Migration;

/**
 * Tabla de tracking de visitas a los enlaces /promo/{slug} (anuncios Facebook).
 */
class m260531_142000_create_promo_visits_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('promo_visits') !== null) {
            echo "    > La tabla promo_visits ya existe.\n";
            return;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('promo_visits', [
            'id' => $this->primaryKey(),
            'car_id' => $this->integer()->null(),
            'slug' => $this->string(120)->notNull(),
            'ip' => $this->string(64)->null(),
            'user_agent' => $this->string(255)->null(),
            'referer' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_promo_visits_slug', 'promo_visits', 'slug');
        $this->createIndex('idx_promo_visits_car_id', 'promo_visits', 'car_id');
        $this->createIndex('idx_promo_visits_created_at', 'promo_visits', 'created_at');

        try {
            $this->addForeignKey(
                'fk_promo_visits_car',
                'promo_visits',
                'car_id',
                'cars',
                'id',
                'SET NULL',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            echo "    > No se pudo crear FK promo_visits.car_id -> cars.id: " . $e->getMessage() . "\n";
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('promo_visits') === null) {
            return;
        }
        try {
            $this->dropForeignKey('fk_promo_visits_car', 'promo_visits');
        } catch (\Throwable $e) {
            // ignore
        }
        $this->dropTable('promo_visits');
    }
}
