<?php

use yii\db\Migration;

/**
 * Class m251131_130000_update_total_precio_generated_column
 * Actualiza la definición de total_precio para incluir medio_dia_valor
 */
class m251131_130000_update_total_precio_generated_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Primero, verificar si total_precio es una columna generada
        // Si es así, necesitamos eliminar la definición generada y recrearla
        
        // Paso 1: Obtener información sobre la columna actual
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
                // Modificar la columna para actualizar la expresión generada
                // La nueva expresión debe incluir medio_dia_valor
                $this->execute("
                    ALTER TABLE `rentals` 
                    MODIFY COLUMN `total_precio` DECIMAL(10,2) 
                    GENERATED ALWAYS AS (
                        (`cantidad_dias` * `precio_por_dia`) + 
                        IF(`medio_dia_enabled` = 1 AND `medio_dia_valor` > 0, `medio_dia_valor`, 0)
                    ) STORED
                ");
            } else {
                // Si no es generada, solo asegurarnos de que la columna existe con el tamaño correcto
                $this->alterColumn('rentals', 'total_precio', $this->decimal(10, 2)->notNull()->defaultValue(0));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revertir a la definición anterior (sin medio_dia_valor)
        // Esto asume que la definición anterior era solo cantidad_dias * precio_por_dia
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
                    GENERATED ALWAYS AS (`cantidad_dias` * `precio_por_dia`) STORED
                ");
            }
        }
    }
}

