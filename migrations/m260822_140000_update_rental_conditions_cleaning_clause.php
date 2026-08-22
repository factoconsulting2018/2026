<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Actualiza el punto 2 de "SOBRE LA ENTREGA" en rental_conditions_html
 * (página 2 del PDF de orden de alquiler) con la cláusula de limpieza.
 */
class m260822_140000_update_rental_conditions_cleaning_clause extends Migration
{
    private const CONFIG_KEY = 'rental_conditions_html';

    private const OLD_POINT = 'Recuerde revisar el estado del vehículo antes de entregarlo.';

    private const NEW_POINT = 'Recuerde revisar el estado y la limpieza del vehículo antes de entregarlo. Si el vehículo presenta suciedad excesiva, manchas, residuos, fuertes olores u otras condiciones que requieran una limpieza profunda o el uso de equipo especializado, se aplicará un cargo adicional por limpieza. Este cargo podrá variar entre ¢25.000 y ¢100.000, dependiendo del nivel de suciedad y del tratamiento requerido para devolver el vehículo a condiciones adecuadas.';

    public function safeUp()
    {
        $row = (new Query())
            ->from('{{%company_config}}')
            ->where(['config_key' => self::CONFIG_KEY])
            ->one($this->db);

        if (!$row) {
            return true;
        }

        $html = (string) ($row['config_value'] ?? '');
        if ($html === '' || strpos($html, self::OLD_POINT) === false) {
            // Ya actualizado o texto distinto (editado en Config).
            if (strpos($html, 'cargo adicional por limpieza') !== false) {
                return true;
            }
            return true;
        }

        $updated = str_replace(self::OLD_POINT, self::NEW_POINT, $html);
        $this->update('{{%company_config}}', [
            'config_value' => $updated,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['config_key' => self::CONFIG_KEY]);

        return true;
    }

    public function safeDown()
    {
        $row = (new Query())
            ->from('{{%company_config}}')
            ->where(['config_key' => self::CONFIG_KEY])
            ->one($this->db);

        if (!$row) {
            return true;
        }

        $html = (string) ($row['config_value'] ?? '');
        if ($html === '' || strpos($html, self::NEW_POINT) === false) {
            return true;
        }

        $updated = str_replace(self::NEW_POINT, self::OLD_POINT, $html);
        $this->update('{{%company_config}}', [
            'config_value' => $updated,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['config_key' => self::CONFIG_KEY]);

        return true;
    }
}
