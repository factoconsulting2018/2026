<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Orden de mantenimiento de vehículo.
 *
 * @property int $id
 * @property string $order_id
 * @property int $car_id
 * @property string $order_date
 * @property string|null $taller
 * @property string|null $notes
 * @property string $status pendiente|en_proceso|atendida
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Car $car
 */
class MaintenanceOrder extends ActiveRecord
{
    public const STATUS_PENDIENTE = 'pendiente';
    public const STATUS_EN_PROCESO = 'en_proceso';
    public const STATUS_ATENDIDA = 'atendida';

    public const DEKRA_TALLER = 'Dekra (Revisión Vehicular)';

    public static function tableName()
    {
        return '{{%maintenance_orders}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['car_id', 'order_date'], 'required'],
            [['car_id'], 'integer'],
            [['order_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['taller'], 'string', 'max' => 255],
            [['order_id'], 'string', 'max' => 32],
            [['status'], 'in', 'range' => [
                self::STATUS_PENDIENTE,
                self::STATUS_EN_PROCESO,
                self::STATUS_ATENDIDA,
            ]],
            [['status'], 'default', 'value' => self::STATUS_PENDIENTE],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::class, 'targetAttribute' => ['car_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => 'Número de orden',
            'car_id' => 'Vehículo',
            'order_date' => 'Fecha',
            'taller' => 'Taller',
            'notes' => 'Notas pendientes',
            'status' => 'Estado',
            'created_at' => 'Registrado',
            'updated_at' => 'Actualizado',
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (empty($this->order_date)) {
                $this->order_date = date('Y-m-d');
            }
            return true;
        }
        return false;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->order_id)) {
            $this->order_id = self::generateOrderId();
        }

        return true;
    }

    public static function generateOrderId(): string
    {
        do {
            $id = 'OM_' . substr((string) time(), -3) . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::find()->where(['order_id' => $id])->exists());

        return $id;
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_PENDIENTE => 'Pendiente',
            self::STATUS_EN_PROCESO => 'En proceso',
            self::STATUS_ATENDIDA => 'Atendida',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusList()[$this->status] ?? $this->status;
    }

    /** Clase CSS de fila en listado según estado. */
    public function getRowClass(): string
    {
        return match ($this->status) {
            self::STATUS_EN_PROCESO => 'maintenance-row-en-proceso',
            self::STATUS_ATENDIDA => 'maintenance-row-atendida',
            default => 'maintenance-row-pendiente',
        };
    }

    public function getCar()
    {
        return $this->hasOne(Car::class, ['id' => 'car_id']);
    }

    public static function carDropdownLabel(Car $car): string
    {
        $parts = array_filter([
            $car->nombre,
            $car->placa ? 'Placa ' . $car->placa : null,
            $car->car_id ? '(' . $car->car_id . ')' : null,
        ]);

        return $parts !== [] ? implode(' — ', $parts) : 'Vehículo #' . $car->id;
    }

    /** @param Car[] $cars */
    public static function buildCarDropdownList(array $cars): array
    {
        $list = [];
        foreach ($cars as $car) {
            if ($car instanceof Car) {
                $list[$car->id] = self::carDropdownLabel($car);
            }
        }

        return $list;
    }

    /**
     * Crea órdenes automáticas de recordatorio de Dekra (Revisión Técnica
     * Vehicular) para cada vehículo durante el año en curso y los siguientes
     * años configurados. El mes asignado se toma del mapeo configurado
     * dígito → mes; por defecto 1→enero, 2→febrero, …, 9→septiembre, 0→octubre.
     * Si ya existe una orden Dekra para ese vehículo en un año dado, no se
     * vuelve a crear.
     *
     * @return int Cantidad de órdenes creadas en esta ejecución.
     */
    public static function ensureDekraReminders(?int $startYear = null): int
    {
        try {
            $schema = Yii::$app->db->getTableSchema(self::tableName(), true);
            if ($schema === null) {
                return 0;
            }
        } catch (\Throwable $e) {
            return 0;
        }

        $config = \app\models\CompanyConfig::getDekraConfig();
        if (!$config['enabled']) {
            return 0;
        }

        $startYear = $startYear ?: (int) date('Y');
        $endYear = $startYear + max(0, $config['years_ahead']);
        $tallerName = $config['taller_name'];
        $dayOfMonth = $config['day_of_month'];
        $map = $config['plate_month_map'];

        $created = 0;
        $cars = Car::find()->all();

        for ($year = $startYear; $year <= $endYear; $year++) {
            foreach ($cars as $car) {
                if (!$car instanceof Car || empty($car->placa)) {
                    continue;
                }

                $digit = self::lastDigitOfPlate((string) $car->placa);
                if ($digit === null) {
                    continue;
                }

                $month = $map[$digit] ?? null;
                if (!$month || $month < 1 || $month > 12) {
                    continue;
                }

                $orderDate = sprintf('%04d-%02d-%02d', $year, $month, $dayOfMonth);

                $exists = self::find()
                    ->where(['car_id' => $car->id])
                    ->andWhere(['taller' => $tallerName])
                    ->andWhere(['between', 'order_date', sprintf('%04d-01-01', $year), sprintf('%04d-12-31', $year)])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $monthName = self::monthName($month);

                $order = new self();
                $order->car_id = (int) $car->id;
                $order->order_date = $orderDate;
                $order->taller = $tallerName;
                $order->status = self::STATUS_PENDIENTE;
                $order->notes = sprintf(
                    "Recordatorio de Dekra (Revisión Vehicular).\nVehículo: %s\nPlaca: %s\nMes asignado por placa: %s %d (último dígito = %d).\nLa Revisión Técnica Vehicular se realiza una vez al año.",
                    $car->nombre ?: 'Sin nombre',
                    $car->placa,
                    $monthName,
                    $year,
                    $digit
                );

                if ($order->save()) {
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Devuelve el mes (1-12) que corresponde al último dígito numérico de la
     * placa según la configuración guardada, o null si la placa no contiene
     * dígitos.
     */
    public static function dekraMonthForPlate(string $plate): ?int
    {
        $digit = self::lastDigitOfPlate($plate);
        if ($digit === null) {
            return null;
        }

        $map = \app\models\CompanyConfig::getDekraConfig()['plate_month_map'];
        $month = $map[$digit] ?? null;
        return ($month && $month >= 1 && $month <= 12) ? (int) $month : null;
    }

    /** Extrae el último dígito numérico (0-9) de la placa o null si no hay. */
    public static function lastDigitOfPlate(string $plate): ?int
    {
        $digits = preg_replace('/\D+/', '', $plate);
        if ($digits === '' || $digits === null) {
            return null;
        }

        return (int) substr($digits, -1);
    }

    protected static function monthName(int $month): string
    {
        $names = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        return $names[$month] ?? '';
    }
}
