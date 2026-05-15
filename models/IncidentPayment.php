<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Abono sobre un incidente (choque).
 *
 * @property int $id
 * @property int $incident_id
 * @property string $amount
 * @property string $payment_date
 * @property string|null $note
 * @property string $created_at
 *
 * @property Incident $incident
 */
class IncidentPayment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%incident_payments}}';
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return true;
    }

    public function rules()
    {
        return [
            [['incident_id', 'amount', 'payment_date'], 'required'],
            [['incident_id'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['payment_date'], 'date', 'format' => 'php:Y-m-d'],
            [['note'], 'string', 'max' => 255],
            [['incident_id'], 'exist', 'skipOnError' => true, 'targetClass' => Incident::class, 'targetAttribute' => ['incident_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'amount' => 'Monto del abono',
            'payment_date' => 'Fecha del abono',
            'note' => 'Nota',
        ];
    }

    public function getIncident()
    {
        return $this->hasOne(Incident::class, ['id' => 'incident_id']);
    }
}
