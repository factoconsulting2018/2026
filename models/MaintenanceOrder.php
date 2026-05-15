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
}
