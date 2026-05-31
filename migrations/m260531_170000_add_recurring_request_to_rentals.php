<?php

use yii\db\Migration;

/**
 * Permite solicitudes de alquiler de clientes recurrentes sin vehículo asignado.
 */
class m260531_170000_add_recurring_request_to_rentals extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('rentals');
        if (!$table) {
            echo "    > Tabla rentals no encontrada.\n";
            return;
        }

        if (!$table->getColumn('tipo_auto_solicitado')) {
            $this->addColumn('rentals', 'tipo_auto_solicitado', $this->string(80)->null()->after('car_id'));
        }
        if (!$table->getColumn('is_recurring_request')) {
            $this->addColumn('rentals', 'is_recurring_request', $this->tinyInteger(1)->notNull()->defaultValue(0)->after('tipo_auto_solicitado'));
        }

        $carCol = $this->db->schema->getTableSchema('rentals')->getColumn('car_id');
        if ($carCol !== null && !$carCol->allowNull) {
            // Quitar FK antes de alterar columna (MySQL).
            try {
                $this->dropForeignKey('rentals_ibfk_2', 'rentals');
            } catch (\Throwable $e) {
                try {
                    $this->dropForeignKey('fk_rentals_car_id', 'rentals');
                } catch (\Throwable $e2) {
                    echo "    > No se pudo eliminar FK de car_id: " . $e2->getMessage() . "\n";
                }
            }

            $this->alterColumn('rentals', 'car_id', $this->integer()->null());

            try {
                $this->addForeignKey(
                    'fk_rentals_car_id',
                    'rentals',
                    'car_id',
                    'cars',
                    'id',
                    'SET NULL',
                    'CASCADE'
                );
            } catch (\Throwable $e) {
                echo "    > No se pudo recrear FK car_id: " . $e->getMessage() . "\n";
            }
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('rentals');
        if (!$table) {
            return;
        }

        if ($table->getColumn('is_recurring_request')) {
            $this->dropColumn('rentals', 'is_recurring_request');
        }
        if ($table->getColumn('tipo_auto_solicitado')) {
            $this->dropColumn('rentals', 'tipo_auto_solicitado');
        }

        // No revertimos car_id a NOT NULL por riesgo de datos existentes NULL.
    }
}
