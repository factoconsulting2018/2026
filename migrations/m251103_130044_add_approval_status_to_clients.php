<?php

use yii\db\Migration;

class m251103_130044_add_approval_status_to_clients extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('clients');
        
        // Verificar y agregar approval_status si no existe
        if (!$tableSchema->getColumn('approval_status')) {
            $this->addColumn('clients', 'approval_status', $this->string(20)->defaultValue('approved')->after('status'));
            echo "✅ Columna 'approval_status' agregada\n";
        } else {
            echo "ℹ️ Columna 'approval_status' ya existe\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->getDb()->getTableSchema('clients')->getColumn('approval_status')) {
            $this->dropColumn('clients', 'approval_status');
            echo "✅ Columna 'approval_status' eliminada\n";
        }
    }
}

