<?php

use yii\db\Migration;

/**
 * Class m251202_000000_add_description_to_client_files_if_missing
 * Agrega la columna description a client_files si no existe
 */
class m251202_000000_add_description_to_client_files_if_missing extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableSchema = $this->getDb()->getTableSchema('client_files');
        
        // Verificar y agregar description si no existe
        if ($tableSchema && !$tableSchema->getColumn('description')) {
            $this->addColumn('client_files', 'description', $this->string(255)->null()->after('file_size'));
            echo "✅ Columna 'description' agregada a client_files\n";
        } else {
            echo "ℹ️ Columna 'description' ya existe en client_files\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableSchema = $this->getDb()->getTableSchema('client_files');
        
        if ($tableSchema && $tableSchema->getColumn('description')) {
            $this->dropColumn('client_files', 'description');
            echo "✅ Columna 'description' eliminada de client_files\n";
        }
    }
}

