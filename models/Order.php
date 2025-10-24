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
        return 'sales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'article_id', 'quantity', 'unit_price', 'total_price'], 'required'],
            [['article_id', 'client_id', 'store_id', 'quantity'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['ticket_id'], 'string', 'max' => 50],
            [['sale_mode'], 'in', 'range' => ['retail', 'wholesale', 'auction']],
            [['notes'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'ID de Ticket',
            'article_id' => 'ID de Artículo',
            'client_id' => 'ID de Cliente',
            'sale_mode' => 'Modo de Venta',
            'store_id' => 'ID de Tienda',
            'quantity' => 'Cantidad',
            'unit_price' => 'Precio Unitario',
            'total_price' => 'Precio Total',
            'notes' => 'Notas',
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
            if ($insert && empty($this->ticket_id)) {
                $this->ticket_id = $this->generateTicketId();
            }
            return true;
        }
        return false;
    }

    /**
     * Genera un ID único para el ticket
     * @return string
     */
    protected function generateTicketId()
    {
        $prefix = 'TKT';
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
     * Obtiene el artículo asociado
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(\app\models\Article::class, ['id' => 'article_id']);
    }

    /**
     * Calcula el precio total
     * @return float
     */
    public function calculateTotal()
    {
        return $this->quantity * $this->unit_price;
    }
}

