<?php

use yii\db\Migration;

/**
 * Ajusta el tamaño de la columna estado_pago para permitir valores como 'cancelado' (8 caracteres)
 * y otros valores posibles: 'pendiente' (9), 'reservado' (9), 'pagado' (6)
 */
class m251031_000000_fix_estado_pago_column_size extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('rentals');
        if ($table && $table->getColumn('estado_pago')) {
            // Obtener el tamaño actual de la columna
            $column = $table->getColumn('estado_pago');
            $currentSize = $column->size ?? 0;
            
            // Si el tamaño es menor a 20, ajustarlo a VARCHAR(20)
            if ($currentSize < 20) {
                $this->alterColumn('rentals', 'estado_pago', $this->string(20)->notNull()->defaultValue('pendiente')->comment('Estado de pago del alquiler'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // No revertir el cambio porque podría causar problemas si hay datos con valores largos
        echo "Esta migración no puede ser revertida automáticamente.\n";
        return false;
    }
}
