<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Plantilla de mensaje para campañas de marketing por WhatsApp.
 *
 * @property int $id
 * @property string $name
 * @property string|null $message_html
 * @property string|null $message_text
 * @property string|null $image_public_url
 * @property string|null $image_filename
 * @property string $created_at
 * @property string $updated_at
 */
class MarketingTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%marketing_templates}}';
    }

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

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 160],
            [['message_html', 'message_text'], 'string'],
            [['image_public_url'], 'string', 'max' => 500],
            [['image_filename'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'message_html' => 'Mensaje (HTML)',
            'message_text' => 'Mensaje (texto plano)',
            'image_public_url' => 'URL pública de la imagen',
            'image_filename' => 'Archivo de imagen',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
        ];
    }

    /**
     * Verifica si la tabla existe. Si no, devuelve un array vacío.
     * @return self[]
     */
    public static function findAllSafe()
    {
        try {
            $schema = Yii::$app->db->getTableSchema(self::tableName(), true);
            if ($schema === null) {
                return [];
            }
            return self::find()->orderBy(['updated_at' => SORT_DESC])->all();
        } catch (\Throwable $e) {
            Yii::warning('MarketingTemplate::findAllSafe error: ' . $e->getMessage(), 'marketing');
            return [];
        }
    }
}
