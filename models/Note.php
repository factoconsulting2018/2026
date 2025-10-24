<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "notes".
 *
 * @property int $id
 * @property string $title
 * @property string|null $content
 * @property string $color
 * @property string $status
 * @property int|null $position_x
 * @property int|null $position_y
 * @property int|null $order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Note extends ActiveRecord
{
    // Colores disponibles para los stickers
    const COLOR_YELLOW = 'yellow';
    const COLOR_BLUE = 'blue';
    const COLOR_GREEN = 'green';
    const COLOR_RED = 'red';
    const COLOR_ORANGE = 'orange';
    const COLOR_PURPLE = 'purple';
    const COLOR_PINK = 'pink';
    const COLOR_GRAY = 'gray';
    const COLOR_LIGHT_BLUE = 'lightblue';
    const COLOR_LIGHT_GREEN = 'lightgreen';

    const COLORS = [
        self::COLOR_YELLOW => 'Amarillo',
        self::COLOR_BLUE => 'Azul',
        self::COLOR_GREEN => 'Verde',
        self::COLOR_RED => 'Rojo',
        self::COLOR_ORANGE => 'Naranja',
        self::COLOR_PURPLE => 'Morado',
        self::COLOR_PINK => 'Rosa',
        self::COLOR_GRAY => 'Gris',
        self::COLOR_LIGHT_BLUE => 'Azul Claro',
        self::COLOR_LIGHT_GREEN => 'Verde Claro',
    ];

    // Estados disponibles
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_PROCESSING => 'Procesando',
        self::STATUS_COMPLETED => 'Completada',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notes';
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['content'], 'string'],
            [['position_x', 'position_y', 'order', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 20],
            [['color'], 'in', 'range' => array_keys(self::COLORS)],
            [['status'], 'in', 'range' => array_keys(self::STATUSES)],
            [['position_x'], 'default', 'value' => 100],
            [['position_y'], 'default', 'value' => 100],
            [['order'], 'default', 'value' => 0],
            [['color'], 'default', 'value' => 'yellow'],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Título',
            'content' => 'Contenido',
            'color' => 'Color',
            'status' => 'Estado',
            'position_x' => 'Posición X',
            'position_y' => 'Posición Y',
            'order' => 'Orden',
            'created_by' => 'Creado por',
            'updated_by' => 'Actualizado por',
            'created_at' => 'Fecha de creación',
            'updated_at' => 'Fecha de actualización',
        ];
    }

    /**
     * Obtiene el nombre del color
     */
    public function getColorName()
    {
        return self::COLORS[$this->color] ?? $this->color;
    }

    /**
     * Obtiene el nombre del estado
     */
    public function getStatusName()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Obtiene la clase CSS para el color
     */
    public function getColorClass()
    {
        return 'sticker-' . $this->color;
    }

    /**
     * Obtiene la clase CSS para el estado
     */
    public function getStatusClass()
    {
        return 'status-' . $this->status;
    }

    /**
     * Obtiene el icono para el estado
     */
    public function getStatusIcon()
    {
        $icons = [
            self::STATUS_PENDING => '⏳',
            self::STATUS_PROCESSING => '🔄',
            self::STATUS_COMPLETED => '✅',
        ];
        return $icons[$this->status] ?? '❓';
    }

    /**
     * Verifica si la nota está en estado completado
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la nota está en estado pendiente
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la nota está en estado procesando
     */
    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Marca la nota como completada
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save(false);
    }

    /**
     * Marca la nota como procesando
     */
    public function markAsProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        return $this->save(false);
    }

    /**
     * Marca la nota como pendiente
     */
    public function markAsPending()
    {
        $this->status = self::STATUS_PENDING;
        return $this->save(false);
    }

    /**
     * Obtiene las notas por estado
     */
    public static function getByStatus($status)
    {
        return self::find()->where(['status' => $status])->orderBy(['created_at' => SORT_DESC])->all();
    }

    /**
     * Obtiene las notas por color
     */
    public static function getByColor($color)
    {
        return self::find()->where(['color' => $color])->orderBy(['created_at' => SORT_DESC])->all();
    }

    /**
     * Obtiene todas las notas ordenadas por fecha de creación
     */
    public static function getAllNotes()
    {
        return self::find()->orderBy(['created_at' => SORT_DESC])->all();
    }

    /**
     * Obtiene estadísticas de las notas
     */
    public static function getStats()
    {
        return [
            'total' => self::find()->count(),
            'pending' => self::find()->where(['status' => self::STATUS_PENDING])->count(),
            'processing' => self::find()->where(['status' => self::STATUS_PROCESSING])->count(),
            'completed' => self::find()->where(['status' => self::STATUS_COMPLETED])->count(),
        ];
    }

    /**
     * Obtiene el siguiente orden para un estado dado
     */
    public static function getNextOrder($status)
    {
        $maxOrder = self::find()
            ->where(['status' => $status])
            ->max('`order`');
        
        return ($maxOrder ?: 0) + 1;
    }

    /**
     * Obtiene notas agrupadas por estado con orden
     */
    public static function getNotesByStatusWithOrder()
    {
        return [
            self::STATUS_PENDING => self::find()
                ->where(['status' => self::STATUS_PENDING])
                ->orderBy(['order' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all(),
            self::STATUS_PROCESSING => self::find()
                ->where(['status' => self::STATUS_PROCESSING])
                ->orderBy(['order' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all(),
            self::STATUS_COMPLETED => self::find()
                ->where(['status' => self::STATUS_COMPLETED])
                ->orderBy(['order' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all(),
        ];
    }

    /**
     * Antes de guardar, asignar orden automático si es nuevo
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && !$this->order) {
                $this->order = self::getNextOrder($this->status);
            }
            return true;
        }
        return false;
    }

    /**
     * Obtener el valor del color para CSS
     */
    public function getColorValue()
    {
        $colorMap = [
            self::COLOR_YELLOW => '#ffeb3b',
            self::COLOR_BLUE => '#2196f3',
            self::COLOR_GREEN => '#4caf50',
            self::COLOR_RED => '#f44336',
            self::COLOR_ORANGE => '#ff9800',
            self::COLOR_PURPLE => '#9c27b0',
            self::COLOR_PINK => '#e91e63',
            self::COLOR_GRAY => '#9e9e9e',
            self::COLOR_LIGHT_BLUE => '#03a9f4',
            self::COLOR_LIGHT_GREEN => '#8bc34a',
        ];
        
        return $colorMap[$this->color] ?? '#ffeb3b';
    }

}
