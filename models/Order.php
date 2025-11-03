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
            [['sale_mode', 'notes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['ticket_id'], 'string', 'max' => 50],
            [['sale_mode'], 'in', 'range' => ['retail', 'wholesale', 'auction']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'ID Ticket',
            'article_id' => 'Artículo',
            'client_id' => 'Cliente',
            'sale_mode' => 'Modo de Venta',
            'store_id' => 'Tienda',
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
            // Calcular total_price si no está establecido
            if (empty($this->total_price)) {
                $this->total_price = $this->quantity * $this->unit_price;
            }
            return true;
        }
        return false;
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
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }
}

