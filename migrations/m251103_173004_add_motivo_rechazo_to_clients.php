<?php

use yii\db\Migration;

/**
 * Class m251103_173004_add_motivo_rechazo_to_clients
 * Agrega campo de motivo de rechazo a la tabla clients
 */
class m251103_173004_add_motivo_rechazo_to_clients extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        // Verificar y agregar motivo_rechazo si no existe
        if (!$tableSchema->getColumn('motivo_rechazo')) {
            $this->addColumn('clients', 'motivo_rechazo', $this->text()->null()->after('approval_status'));
            echo "✅ Columna 'motivo_rechazo' agregada\n";
        } else {
            echo "ℹ️ Columna 'motivo_rechazo' ya existe\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        if ($tableSchema->getColumn('motivo_rechazo')) {
            $this->dropColumn('clients', 'motivo_rechazo');
        }
    }
}

