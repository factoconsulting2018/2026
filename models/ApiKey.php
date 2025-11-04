<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Security;

/**
 * Modelo de API Key
 * Tabla: api_keys
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $description
 * @property int $is_active
 * @property string $last_used_at
 * @property string $created_at
 * @property string $updated_at
 */
class ApiKey extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api_keys';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['key'], 'string', 'length' => [32, 64], 'skipOnEmpty' => true],
            [['key'], 'unique', 'skipOnEmpty' => true],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['is_active'], 'integer'],
            [['is_active'], 'default', 'value' => 1],
            [['last_used_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'API Key',
            'name' => 'Nombre',
            'description' => 'Descripción',
            'is_active' => 'Activo',
            'last_used_at' => 'Último Uso',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Genera una nueva API Key aleatoria
     * @return string
     */
    public static function generateKey()
    {
        // Generar key segura: prefijo + timestamp + random
        $prefix = 'frc_'; // Facto Rent a Car
        $timestamp = time();
        $random = bin2hex(random_bytes(16)); // 32 caracteres hexadecimales
        return $prefix . $timestamp . '_' . $random;
    }

    /**
     * Valida una API Key
     * @param string $key
     * @return ApiKey|null
     */
    public static function validateKey($key)
    {
        if (empty($key)) {
            return null;
        }

        $apiKey = self::findOne(['key' => $key, 'is_active' => 1]);
        
        if ($apiKey) {
            // Registrar último uso
            $apiKey->last_used_at = date('Y-m-d H:i:s');
            $apiKey->save(false);
        }

        return $apiKey;
    }

    /**
     * Antes de validar, generar key si es nuevo y está vacío
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            // Si es nuevo registro y no tiene key, generarla antes de validar
            if ($this->isNewRecord && empty($this->key)) {
                $this->key = self::generateKey();
            }
            return true;
        }
        return false;
    }

    /**
     * Antes de guardar, asegurar que la key existe
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Asegurar que siempre tenga key (por si acaso)
            if (empty($this->key)) {
                $this->key = self::generateKey();
            }
            return true;
        }
        return false;
    }

    /**
     * Obtiene el estado activo/inactivo como texto
     * @return string
     */
    public function getStatusText()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Obtiene el estado activo/inactivo como badge HTML
     * @return string
     */
    public function getStatusBadge()
    {
        if ($this->is_active) {
            return '<span class="badge bg-success">Activo</span>';
        }
        return '<span class="badge bg-secondary">Inactivo</span>';
    }

    /**
     * Formatea la fecha de último uso
     * @return string
     */
    public function getFormattedLastUsed()
    {
        if (empty($this->last_used_at)) {
            return 'Nunca';
        }
        return Yii::$app->formatter->asDatetime($this->last_used_at);
    }
}

