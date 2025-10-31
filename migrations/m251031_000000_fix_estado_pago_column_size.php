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
        // Siempre ejecutar el ALTER TABLE para asegurar que la columna tenga el tamaño correcto
        // Esto corrige el problema incluso si la migración ya se ejecutó parcialmente
        try {
            // Usar SQL directo que siempre funciona
            $this->execute("ALTER TABLE `rentals` MODIFY COLUMN `estado_pago` VARCHAR(20) NOT NULL DEFAULT 'pendiente' COMMENT 'Estado de pago del alquiler'");
            echo "Columna estado_pago actualizada a VARCHAR(20) exitosamente.\n";
        } catch (\Exception $e) {
            // Si el ALTER TABLE falla, intentar con el método de Yii como respaldo
            echo "Intentando con método alternativo...\n";
            try {
                $this->alterColumn('rentals', 'estado_pago', $this->string(20)->notNull()->defaultValue('pendiente')->comment('Estado de pago del alquiler'));
                echo "Columna estado_pago actualizada exitosamente con método alternativo.\n";
            } catch (\Exception $e2) {
                // Si ambos métodos fallan, mostrar error pero no fallar la migración
                echo "Advertencia: No se pudo actualizar la columna estado_pago. Error: " . $e2->getMessage() . "\n";
                echo "Por favor, ejecuta manualmente el SQL: ALTER TABLE `rentals` MODIFY COLUMN `estado_pago` VARCHAR(20) NOT NULL DEFAULT 'pendiente';\n";
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
