<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_config}}`.
 */
class m251015_220000_create_company_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_config}}', [
            'id' => $this->primaryKey(),
            'config_key' => $this->string(100)->notNull()->unique()->comment('Clave de configuración'),
            'config_value' => $this->text()->comment('Valor de configuración'),
            'description' => $this->text()->comment('Descripción de la configuración'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Crear índices
        $this->createIndex('idx_company_config_key', '{{%company_config}}', 'config_key');
        $this->createIndex('idx_company_config_created_at', '{{%company_config}}', 'created_at');

        // Insertar configuraciones por defecto
        $this->insert('{{%company_config}}', [
            'config_key' => 'company_name',
            'config_value' => 'FACTO RENT A CAR',
            'description' => 'Nombre de la empresa',
        ]);

        $this->insert('{{%company_config}}', [
            'config_key' => 'company_address',
            'config_value' => '3-101-880789, San Ramón, Alajuela, Costa Rica',
            'description' => 'Dirección de la empresa',
        ]);

        $this->insert('{{%company_config}}', [
            'config_key' => 'company_phone',
            'config_value' => '',
            'description' => 'Teléfono de la empresa',
        ]);

        $this->insert('{{%company_config}}', [
            'config_key' => 'company_email',
            'config_value' => '',
            'description' => 'Email de la empresa',
        ]);

        $this->insert('{{%company_config}}', [
            'config_key' => 'simemovil_number',
            'config_value' => '83670937',
            'description' => 'Número de SIMPEMOVIL para pagos',
        ]);

        $this->insert('{{%company_config}}', [
            'config_key' => 'bank_accounts',
            'config_value' => json_encode([
                [
                    'bank' => 'BCR',
                    'account' => 'IBAN:CR75015201001050506181',
                    'currency' => '₡'
                ],
                [
                    'bank' => 'BN',
                    'account' => 'IBAN: CR49015102020010977051',
                    'currency' => '₡'
                ]
            ]),
            'description' => 'Cuentas bancarias de la empresa',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_company_config_created_at', '{{%company_config}}');
        $this->dropIndex('idx_company_config_key', '{{%company_config}}');
        $this->dropTable('{{%company_config}}');
    }
}
