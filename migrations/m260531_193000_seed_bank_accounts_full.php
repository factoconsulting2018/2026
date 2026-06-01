<?php

use yii\db\Migration;
use app\models\CompanyConfig;

/**
 * Carga la lista completa de cuentas bancarias y datos de contacto de FACTO.
 */
class m260531_193000_seed_bank_accounts_full extends Migration
{
    public function safeUp()
    {
        $accounts = CompanyConfig::getDefaultBankAccounts();
        $payload = json_encode($accounts, JSON_UNESCAPED_UNICODE);

        CompanyConfig::setConfig('bank_accounts', $payload, 'Cuentas bancarias');
        CompanyConfig::setConfig('simemovil_number', '83670937', 'Numero SINPE Movil');
        CompanyConfig::setConfig('company_phone', '4070-0485', 'Telefono');
        CompanyConfig::setConfig('company_razon_social', 'Facto Autos de Alquiler SA', 'Razon social');

        echo "    > Cuentas bancarias actualizadas (" . count($accounts) . " cuentas).\n";
    }

    public function safeDown()
    {
        echo "    > No se revierten datos de company_config (bank_accounts, simemovil, telefono, razon_social).\n";
    }
}
