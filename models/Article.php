<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Artículo
 * Tabla: articles
 *
 * @property int $id
 * @property string $article_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $quantity
 * @property string $category
 * @property string $brand
 * @property string $status
 * @property string $container
 * @property string $created_at
 * @property string $updated_at
 */
class Article extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'price'], 'required'],
            [['quantity'], 'integer'],
            [['price'], 'number'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['article_id'], 'string', 'max' => 4],
            [['name', 'category', 'brand', 'container'], 'string', 'max' => 200],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => 'ID de Artículo',
            'name' => 'Nombre',
            'description' => 'Descripción',
            'price' => 'Precio',
            'quantity' => 'Cantidad',
            'category' => 'Categoría',
            'brand' => 'Marca',
            'status' => 'Estado',
            'container' => 'Contenedor',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }
}
