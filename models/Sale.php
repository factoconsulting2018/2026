<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Venta/Orden
 * Tabla: sales
 *
 * @property int $id
 * @property string $sale_id
 * @property int $client_id
 * @property int $rental_id
 * @property float $total_amount
 * @property string $status
 * @property string $payment_method
 * @property string $created_at
 * @property string $updated_at
 */
class Sale extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sale_id', 'client_id'], 'required'],
            [['client_id', 'rental_id'], 'integer'],
            [['total_amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['sale_id'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 20],
            [['payment_method'], 'string', 'max' => 50],
            [['sale_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sale_id' => 'ID de Venta',
            'client_id' => 'Cliente',
            'rental_id' => 'Alquiler',
            'total_amount' => 'Monto Total',
            'status' => 'Estado',
            'payment_method' => 'Método de Pago',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Relación con el cliente
     */
    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Relación con el alquiler
     */
    public function getRental()
    {
        return $this->hasOne(Rental::class, ['id' => 'rental_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->sale_id = $this->sale_id ?: 'SALE' . date('Ymd') . sprintf('%04d', rand(1, 9999));
            }
            return true;
        }
        return false;
    }
}
