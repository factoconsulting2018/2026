<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Venta/Orden
 * Tabla: sales
 *
 * @property int $id
 * @property string $ticket_id
 * @property int $article_id
 * @property int $client_id
 * @property string $sale_mode
 * @property int $store_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property string $notes
 * @property string $created_at
 * @property string $updated_at
 */
class Order extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rentals';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'car_id', 'fecha_inicio', 'cantidad_dias'], 'required'],
            [['client_id', 'car_id', 'correapartir_enabled', 'cantidad_dias'], 'integer'],
            [['fecha_inicio', 'fecha_final', 'hora_inicio', 'hora_final', 'fecha_correapartir', 'created_at', 'updated_at'], 'safe'],
            [['precio_por_dia'], 'number'],
            [['rental_id', 'lugar_entrega', 'lugar_retiro', 'estado_pago', 'ejecutivo', 'ejecutivo_otro'], 'string', 'max' => 255],
            [['comprobante_pago'], 'string', 'max' => 500],
            [['condiciones_especiales', 'choferes_autorizados'], 'string'],
            [['estado_pago'], 'in', 'range' => ['pendiente', 'pagado', 'reservado', 'cancelado']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rental_id' => 'ID del Alquiler',
            'client_id' => 'Cliente',
            'car_id' => 'Vehículo',
            'correapartir_enabled' => 'Correapartir Habilitado',
            'fecha_correapartir' => 'Fecha Correapartir',
            'fecha_inicio' => 'Fecha de Inicio',
            'hora_inicio' => 'Hora de Inicio',
            'fecha_final' => 'Fecha Final',
            'hora_final' => 'Hora Final',
            'lugar_entrega' => 'Lugar de Entrega',
            'lugar_retiro' => 'Lugar de Retiro',
            'cantidad_dias' => 'Cantidad de Días',
            'precio_por_dia' => 'Precio por Día',
            'total_precio' => 'Precio Total',
            'condiciones_especiales' => 'Condiciones Especiales',
            'choferes_autorizados' => 'Choferes Autorizados',
            'estado_pago' => 'Estado de Pago',
            'comprobante_pago' => 'Comprobante de Pago',
            'ejecutivo' => 'Ejecutivo',
            'ejecutivo_otro' => 'Ejecutivo Otro',
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
            if ($insert && empty($this->rental_id)) {
                $this->rental_id = $this->generateRentalId();
            }
            return true;
        }
        return false;
    }

    /**
     * Genera un ID único para el alquiler
     * @return string
     */
    protected function generateRentalId()
    {
        $prefix = 'RENT';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    /**
     * Obtiene el cliente asociado
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Obtiene el vehículo asociado
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(\app\models\Car::class, ['id' => 'car_id']);
    }
}

