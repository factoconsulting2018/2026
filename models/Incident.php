<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Choque / daño a cobrar a un cliente.
 *
 * @property int $id
 * @property int $client_id
 * @property string $total_amount
 * @property string|null $notes
 * @property string $status open|closed
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Client $client
 * @property IncidentPayment[] $payments
 */
class Incident extends ActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public static function tableName()
    {
        return '{{%incidents}}';
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
            [['client_id', 'total_amount'], 'required'],
            [['client_id'], 'integer'],
            [['total_amount'], 'number', 'min' => 0.01],
            [['notes'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_OPEN, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client_id' => 'Cliente',
            'total_amount' => 'Monto a pagar (total del choque)',
            'notes' => 'Notas / descripción',
            'status' => 'Estado',
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getPayments()
    {
        return $this->hasMany(IncidentPayment::class, ['incident_id' => 'id'])->orderBy(['payment_date' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getPaidTotal(): float
    {
        $sum = IncidentPayment::find()->where(['incident_id' => $this->id])->sum('amount');
        return $sum !== null ? (float) $sum : 0.0;
    }

    public function getBalance(): float
    {
        return max(0, (float) $this->total_amount - $this->getPaidTotal());
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
