<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Vehículo
 * Tabla: cars
 *
 * @property int $id
 * @property string $car_id
 * @property string $nombre
 * @property string $imagen
 * @property int $marca_id
 * @property string $placa
 * @property string $vin
 * @property int $cantidad_pasajeros
 * @property string $caracteristicas
 * @property string $empresa_seguro
 * @property string $telefono_seguro
 * @property string $empresa
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Car extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cars';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'placa'], 'required'],
            [['marca_id', 'cantidad_pasajeros'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['car_id', 'nombre', 'imagen', 'placa', 'vin'], 'string', 'max' => 255],
            [['caracteristicas'], 'string'],
            [['empresa_seguro'], 'string', 'max' => 255],
            [['telefono_seguro'], 'string', 'max' => 20],
            [['empresa'], 'in', 'range' => ['Facto Rent a Car', 'Moviliza']],
            [['status'], 'in', 'range' => ['disponible', 'alquilado', 'mantenimiento', 'fuera_servicio']],
            ['placa', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'car_id' => 'ID del Vehículo',
            'nombre' => 'Nombre',
            'imagen' => 'Imagen',
            'marca_id' => 'Marca ID',
            'placa' => 'Placa',
            'vin' => 'VIN',
            'cantidad_pasajeros' => 'Cantidad de Pasajeros',
            'caracteristicas' => 'Características',
            'empresa_seguro' => 'Empresa de Seguro',
            'telefono_seguro' => 'Teléfono de Seguro',
            'empresa' => 'Empresa',
            'status' => 'Estado',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Generar car_id si es nuevo
            if ($insert && empty($this->car_id)) {
                $this->car_id = $this->generateCarId();
            }
            
            // Convertir todos los campos de texto a mayúsculas
            $textFields = [
                'nombre',
                'placa', 
                'vin',
                'caracteristicas',
                'empresa_seguro',
                'telefono_seguro',
                'empresa'
            ];
            
            foreach ($textFields as $field) {
                if (!empty($this->$field) && is_string($this->$field)) {
                    $this->$field = strtoupper(trim($this->$field));
                }
            }
            
            return true;
        }
        return false;
    }

    /**
     * Genera un ID único para el vehículo
     * @return string
     */
    protected function generateCarId()
    {
        // Generar ID de máximo 6 caracteres
        $random = mt_rand(100, 999); // 3 dígitos
        $suffix = mt_rand(100, 999); // 3 dígitos más
        return $random . $suffix; // Total: 6 caracteres
    }

    /**
     * Obtiene los alquileres del vehículo
     * @return \yii\db\ActiveQuery
     */
    public function getRentals()
    {
        return $this->hasMany(Rental::class, ['car_id' => 'id']);
    }

    /**
     * Verifica si el vehículo está disponible
     * @return bool
     */
    public function isAvailable()
    {
        return $this->status === 'disponible';
    }

    /**
     * Obtiene el alquiler activo si existe
     * @return Rental|null
     */
    public function getActiveRental()
    {
        return $this->hasOne(Rental::class, ['car_id' => 'id'])
            ->where(['estado_pago' => 'pendiente'])
            ->one();
    }
}

