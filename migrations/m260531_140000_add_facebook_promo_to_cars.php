<?php

use yii\db\Migration;

/**
 * Agrega campos de promoción Facebook por vehículo.
 */
class m260531_140000_add_facebook_promo_to_cars extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('cars');
        if (!$table) {
            echo "    > Tabla cars no encontrada.\n";
            return;
        }

        if (!$table->getColumn('facebook_banner')) {
            $this->addColumn('cars', 'facebook_banner', $this->string(255)->null()->after('imagen'));
        }
        if (!$table->getColumn('facebook_promo_enabled')) {
            $this->addColumn('cars', 'facebook_promo_enabled', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('facebook_banner'));
        }
        if (!$table->getColumn('facebook_promo_slug')) {
            $this->addColumn('cars', 'facebook_promo_slug', $this->string(120)->null()->after('facebook_promo_enabled'));
            $this->createIndex('idx_cars_facebook_promo_slug', 'cars', 'facebook_promo_slug', true);
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('cars');
        if (!$table) {
            return;
        }

        if ($table->getColumn('facebook_promo_slug')) {
            $this->dropIndex('idx_cars_facebook_promo_slug', 'cars');
            $this->dropColumn('cars', 'facebook_promo_slug');
        }
        if ($table->getColumn('facebook_promo_enabled')) {
            $this->dropColumn('cars', 'facebook_promo_enabled');
        }
        if ($table->getColumn('facebook_banner')) {
            $this->dropColumn('cars', 'facebook_banner');
        }
    }
}
