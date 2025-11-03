<?php

use yii\db\Migration;

/**
 * Class m251103_133157_add_situacion_financiera_to_clients
 * Agrega campos de situación financiera a la tabla clients
 */
class m251103_133157_add_situacion_financiera_to_clients extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        // Verificar y agregar situacion_financiera si no existe
        if (!$tableSchema->getColumn('situacion_financiera')) {
            $this->addColumn('clients', 'situacion_financiera', $this->string(50)->null()->after('fecha_vencimiento_cedula'));
            echo "✅ Columna 'situacion_financiera' agregada\n";
        } else {
            echo "ℹ️ Columna 'situacion_financiera' ya existe\n";
        }
        
        // Verificar y agregar situacion_financiera_detalle si no existe
        if (!$tableSchema->getColumn('situacion_financiera_detalle')) {
            $this->addColumn('clients', 'situacion_financiera_detalle', $this->text()->null()->after('situacion_financiera'));
            echo "✅ Columna 'situacion_financiera_detalle' agregada\n";
        } else {
            echo "ℹ️ Columna 'situacion_financiera_detalle' ya existe\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        if ($tableSchema->getColumn('situacion_financiera_detalle')) {
            $this->dropColumn('clients', 'situacion_financiera_detalle');
        }
        if ($tableSchema->getColumn('situacion_financiera')) {
            $this->dropColumn('clients', 'situacion_financiera');
        }
    }
}

