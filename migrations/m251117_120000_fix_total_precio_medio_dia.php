<?php

use yii\db\Migration;

/**
 * Class m251117_120000_fix_total_precio_medio_dia
 * Corrige el cálculo de total_precio: cuando medio_dia_enabled = 1, 
 * el total debe ser solo medio_dia_valor (tarifa fija), sin multiplicar días
 */
class m251117_120000_fix_total_precio_medio_dia extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Verificar si total_precio es una columna generada
        $tableSchema = $this->db->getTableSchema('rentals');
        if ($tableSchema && isset($tableSchema->columns['total_precio'])) {
            // Verificar si es una columna generada consultando INFORMATION_SCHEMA
            $isGenerated = $this->db->createCommand("
                SELECT COLUMN_TYPE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'rentals' 
                AND COLUMN_NAME = 'total_precio'
            ")->queryScalar();
            
            // Si contiene 'GENERATED' o 'AS', es una columna generada
            if (strpos(strtoupper($isGenerated), 'GENERATED') !== false || strpos($isGenerated, 'AS') !== false) {
                // Modificar la columna: si medio_dia_enabled = 1, usar solo medio_dia_valor
                // Si no, usar cantidad_dias * precio_por_dia
                $this->execute("
                    ALTER TABLE `rentals` 
                    MODIFY COLUMN `total_precio` DECIMAL(10,2) 
                    GENERATED ALWAYS AS (
                        IF(`medio_dia_enabled` = 1 AND `medio_dia_valor` > 0, 
                            `medio_dia_valor`, 
                            (`cantidad_dias` * `precio_por_dia`)
                        )
                    ) STORED NOT NULL
                ");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir a la definición anterior (suma de días + medio día)
        $tableSchema = $this->db->getTableSchema('rentals');
        if ($tableSchema && isset($tableSchema->columns['total_precio'])) {
            $isGenerated = $this->db->createCommand("
                SELECT COLUMN_TYPE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'rentals' 
                AND COLUMN_NAME = 'total_precio'
            ")->queryScalar();
            
            if (strpos(strtoupper($isGenerated), 'GENERATED') !== false || strpos($isGenerated, 'AS') !== false) {
                $this->execute("
                    ALTER TABLE `rentals` 
                    MODIFY COLUMN `total_precio` DECIMAL(10,2) 
                    GENERATED ALWAYS AS (
                        (`cantidad_dias` * `precio_por_dia`) + 
                        IF(`medio_dia_enabled` = 1 AND `medio_dia_valor` > 0, `medio_dia_valor`, 0)
                    ) STORED NOT NULL
                ");
            }
        }
    }
}

