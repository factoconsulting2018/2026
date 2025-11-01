<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Modelo para archivos de clientes
 * Tabla: client_files
 *
 * @property int $id
 * @property int $client_id
 * @property string $file_name
 * @property string $original_name
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class ClientFile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'file_name', 'original_name', 'file_path', 'file_type', 'file_size'], 'required'],
            [['client_id', 'file_size'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['file_name', 'original_name', 'file_path'], 'string', 'max' => 500],
            [['file_type'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 500, 'skipOnEmpty' => true], // description es opcional
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }
    
    /**
     * Atributos permitidos para asignación masiva
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        // Asegurar que description esté en la lista de atributos disponibles
        if (!in_array('description', $attributes)) {
            $attributes[] = 'description';
        }
        // Asegurar que created_at y updated_at estén en la lista si existen en la tabla
        $schema = static::getTableSchema();
        if ($schema) {
            if ($schema->getColumn('created_at') && !in_array('created_at', $attributes)) {
                $attributes[] = 'created_at';
            }
            if ($schema->getColumn('updated_at') && !in_array('updated_at', $attributes)) {
                $attributes[] = 'updated_at';
            }
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Cliente',
            'file_name' => 'Nombre del Archivo',
            'original_name' => 'Nombre Original',
            'file_path' => 'Ruta del Archivo',
            'file_type' => 'Tipo de Archivo',
            'file_size' => 'Tamaño (bytes)',
            'description' => 'Descripción',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Obtiene el cliente relacionado
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * Obtiene el tamaño del archivo formateado
     * @return string
     */
    public function getFormattedSize()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Obtiene la URL del archivo
     * @return string
     */
    public function getUrl()
    {
        return Yii::getAlias('@web') . '/' . $this->file_path;
    }

    /**
     * Obtiene el icono según el tipo de archivo
     * @return string
     */
    public function getFileIcon()
    {
        $icon = 'description'; // Por defecto
        
        if (strpos($this->file_type, 'pdf') !== false) {
            $icon = 'picture_as_pdf';
        } elseif (strpos($this->file_type, 'image') !== false) {
            $icon = 'image';
        } elseif (strpos($this->file_type, 'word') !== false || strpos($this->file_type, 'document') !== false) {
            $icon = 'description';
        } elseif (strpos($this->file_type, 'excel') !== false || strpos($this->file_type, 'spreadsheet') !== false) {
            $icon = 'table_chart';
        }
        
        return $icon;
    }

    /**
     * Antes de guardar, establecer timestamps y manejar description
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Si description es una cadena vacía, convertir a null
            if (isset($this->description) && $this->description === '') {
                $this->description = null;
            }
            
            // NO asignar created_at ni updated_at manualmente
            // La migración ya los define con:
            // - created_at: CURRENT_TIMESTAMP (se asigna automáticamente en INSERT)
            // - updated_at: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP (se actualiza automáticamente)
            // Dejar que MySQL los maneje automáticamente para evitar conflictos
            
            return true;
        }
        return false;
    }

    /**
     * Antes de eliminar, eliminar el archivo físico
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $filePath = Yii::getAlias('@app') . '/web/' . $this->file_path;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            return true;
        }
        return false;
    }
}

