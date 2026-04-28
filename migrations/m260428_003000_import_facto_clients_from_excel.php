<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Importa clientes desde el archivo Clientes_FactoRentaCar.xls sin duplicarlos.
 */
class m260428_003000_import_facto_clients_from_excel extends Migration
{
    private const IMPORT_MARKER = 'IMPORT_FACTO_CLIENTES_XLS_2026_04_28';
    private $existingNormalizedNames = null;

    public function safeUp()
    {
        $tableSchema = $this->db->getTableSchema('clients', true);
        if ($tableSchema === null) {
            throw new \RuntimeException("La tabla 'clients' no existe.");
        }

        $availableColumns = array_fill_keys(array_keys($tableSchema->columns), true);
        $rows = $this->getSourceRows();
        $seenCedulas = [];
        $seenNames = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $fullName = $this->normalizeName($row['full_name'] ?? '');
            if ($fullName === '') {
                $skipped++;
                continue;
            }

            $cedula = $this->normalizeDigits($row['cedula'] ?? '');
            $normalizedName = $this->normalizeLookupValue($fullName);

            if ($cedula !== '') {
                if (isset($seenCedulas[$cedula]) || $this->existsByCedula($cedula)) {
                    $skipped++;
                    continue;
                }
            } else {
                if (isset($seenNames[$normalizedName]) || $this->existsByName($fullName)) {
                    $skipped++;
                    continue;
                }
            }

            $phoneRaw = $this->cleanNullableText($row['telefono'] ?? '');
            $licencias = $this->cleanNullableText($row['licencias'] ?? '');
            $fechaVencimientoLicencia = $this->parseDate($row['venc_licencia'] ?? '');
            $fechaVencimientoCedula = $this->parseDate($row['venc_cedula'] ?? '');
            [$nombre, $apellido] = $this->splitFullName($fullName);
            $timestamp = date('Y-m-d H:i:s');

            $insertData = [
                'client_id' => $this->generateClientId($cedula, $normalizedName, $inserted),
                'nombre' => $nombre,
                'apellido' => $apellido,
                'full_name' => $fullName,
                'cedula_fisica' => $cedula !== '' ? $cedula : null,
                'whatsapp' => $this->normalizeWhatsapp($phoneRaw),
                'status' => 'active',
                'es_cliente_facto' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (isset($availableColumns['telefono'])) {
                $insertData['telefono'] = $phoneRaw;
            }
            if (isset($availableColumns['celular'])) {
                $insertData['celular'] = $phoneRaw;
            }
            if (isset($availableColumns['activo'])) {
                $insertData['activo'] = 1;
            }
            if (isset($availableColumns['approval_status'])) {
                $insertData['approval_status'] = 'approved';
            }
            if (isset($availableColumns['fecha_vencimiento_licencia'])) {
                $insertData['fecha_vencimiento_licencia'] = $fechaVencimientoLicencia;
            }
            if (isset($availableColumns['fecha_vencimiento_cedula'])) {
                $insertData['fecha_vencimiento_cedula'] = $fechaVencimientoCedula;
            }
            if (isset($availableColumns['licencias_choferes'])) {
                $insertData['licencias_choferes'] = $licencias;
            }
            if (isset($availableColumns['notes'])) {
                $insertData['notes'] = self::IMPORT_MARKER;
            }
            if (isset($availableColumns['notas'])) {
                $insertData['notas'] = 'Importado desde Clientes_FactoRentaCar.xls';
            }

            $insertData = array_intersect_key($insertData, $availableColumns);
            $this->insert('clients', $insertData);

            if ($cedula !== '') {
                $seenCedulas[$cedula] = true;
            } else {
                $seenNames[$normalizedName] = true;
            }

            $inserted++;
        }

        echo "Clientes importados: {$inserted}\n";
        echo "Clientes omitidos por duplicado o datos invalidos: {$skipped}\n";
    }

    public function safeDown()
    {
        $tableSchema = $this->db->getTableSchema('clients', true);
        if ($tableSchema === null) {
            return true;
        }

        $conditions = [];
        if ($tableSchema->getColumn('notes')) {
            $conditions['notes'] = self::IMPORT_MARKER;
        }

        if (empty($conditions)) {
            echo "No se encontro una columna marcador para revertir esta importacion.\n";
            return false;
        }

        $deleted = $this->delete('clients', $conditions);
        echo "Clientes eliminados: {$deleted}\n";

        return true;
    }

    private function getSourceRows()
    {
        $payload = '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';
        $json = @zlib_decode(base64_decode($payload));

        if ($json === false || $json === null) {
            throw new \RuntimeException('No se pudo descomprimir el lote de clientes a importar.');
        }

        $rows = json_decode($json, true);
        if (!is_array($rows)) {
            throw new \RuntimeException('No se pudo decodificar el JSON de clientes a importar.');
        }

        return $rows;
    }

    private function existsByCedula($cedula)
    {
        return (new Query())
            ->from('clients')
            ->where(['cedula_fisica' => $cedula])
            ->exists($this->db);
    }

    private function existsByName($fullName)
    {
        $rows = (new Query())
            ->select(['full_name'])
            ->from('clients')
            ->where(['full_name' => $fullName])
            ->limit(1)
            ->all($this->db);

        if (!empty($rows)) {
            return true;
        }

        $this->hydrateExistingNormalizedNames();
        return isset($this->existingNormalizedNames[$this->normalizeLookupValue($fullName)]);
    }

    private function normalizeName($value)
    {
        $value = $this->cleanNullableText($value);
        if ($value === null) {
            return '';
        }

        return strtoupper($value);
    }

    private function cleanNullableText($value)
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        if ($value === '' || strtoupper($value) === 'N/A' || strtoupper($value) === 'XXXXXXX') {
            return null;
        }

        return $value;
    }

    private function normalizeDigits($value)
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        return $digits ?? '';
    }

    private function normalizeWhatsapp($phoneRaw)
    {
        if ($phoneRaw === null) {
            return null;
        }

        $digits = $this->normalizeDigits($phoneRaw);
        if ($digits === '') {
            return null;
        }

        return strlen($digits) <= 20 ? $digits : substr($digits, 0, 20);
    }

    private function normalizeLookupValue($value)
    {
        $value = strtoupper(trim((string) $value));
        return preg_replace('/\s+/u', ' ', $value);
    }

    private function hydrateExistingNormalizedNames()
    {
        if (is_array($this->existingNormalizedNames)) {
            return;
        }

        $this->existingNormalizedNames = [];
        $allNames = (new Query())
            ->select(['full_name'])
            ->from('clients')
            ->where(['is not', 'full_name', null])
            ->column($this->db);

        foreach ($allNames as $existingName) {
            $normalized = $this->normalizeLookupValue($existingName);
            if ($normalized !== '') {
                $this->existingNormalizedNames[$normalized] = true;
            }
        }
    }

    private function splitFullName($fullName)
    {
        $parts = preg_split('/\s+/u', trim($fullName), 2);
        return [
            $parts[0] ?? $fullName,
            $parts[1] ?? '',
        ];
    }

    private function parseDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{2})\/(\d{2})(\d{4})$/', $value, $matches)) {
            $value = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }

        $date = \DateTime::createFromFormat('d/m/Y', $value);
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        return null;
    }

    private function generateClientId($cedula, $normalizedName, $offset)
    {
        $seed = $cedula !== '' ? $cedula : $normalizedName . '|' . $offset;
        return 'CLIIMP' . strtoupper(substr(sha1($seed), 0, 12));
    }
}
