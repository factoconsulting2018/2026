<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Modelo de Marca
 * Tabla: brands
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class Brand extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'brands';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
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
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['is_active'], 'integer'],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre de la Marca',
            'description' => 'Descripción',
            'is_active' => 'Estado Activo',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Convierte campos vacíos a NULL antes de guardar
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Convertir nombre a mayúsculas
            if (!empty($this->name) && is_string($this->name)) {
                $this->name = strtoupper(trim($this->name));
            }
            
            // Establecer estado por defecto
            if ($insert && empty($this->is_active)) {
                $this->is_active = 1;
            }
            
            return true;
        }
        return false;
    }

    /**
     * Obtiene los vehículos de esta marca
     * @return \yii\db\ActiveQuery
     */
    public function getCars()
    {
        return $this->hasMany(Car::class, ['marca_id' => 'id']);
    }

    /**
     * Obtiene todas las marcas activas
     * @return array
     */
    public static function getActiveBrands()
    {
        return self::find()
            ->where(['is_active' => 1])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * Obtiene un array para dropdowns
     * @return array
     */
    public static function getBrandsForDropdown()
    {
        $brands = self::getActiveBrands();
        $dropdown = [];
        foreach ($brands as $brand) {
            $dropdown[$brand->id] = $brand->name;
        }
        return $dropdown;
    }
}
