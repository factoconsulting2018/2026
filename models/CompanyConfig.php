<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "company_config".
 *
 * @property int $id
 * @property string $config_key
 * @property string $config_value
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class CompanyConfig extends ActiveRecord
{
    // Claves de configuración
    const LOGO_FILE = 'logo_file';
    const RENTAL_CONDITIONS_FILE = 'rental_conditions_file';
    const COMPANY_NAME = 'company_name';
    const COMPANY_ADDRESS = 'company_address';
    const COMPANY_PHONE = 'company_phone';
    const COMPANY_EMAIL = 'company_email';
    const BANK_ACCOUNTS = 'bank_accounts';
    const SIMPEMOVIL_NUMBER = 'simemovil_number';

    // Directorios para archivos
    const UPLOAD_DIR = 'uploads/company/';
    const LOGO_DIR = 'uploads/company/logo/';
    const CONDITIONS_DIR = 'uploads/company/conditions/';

    public $logoFile;
    public $conditionsFile;
    public $clientsFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%company_config}}';
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
            [['config_key'], 'required'],
            [['config_value', 'description'], 'string'],
            [['config_key'], 'string', 'max' => 100],
            [['config_key'], 'unique'],
            [['logoFile'], 'file', 'extensions' => 'png, jpg, jpeg, gif, svg, PNG, JPG, JPEG, GIF, SVG', 'maxSize' => 2 * 1024 * 1024, 'skipOnEmpty' => true], // 2MB
            [['logoFile'], 'validateLogoFile', 'skipOnEmpty' => true],
            [['conditionsFile'], 'file', 'extensions' => 'pdf, doc, docx, txt', 'maxSize' => 5 * 1024 * 1024], // 5MB
            [['clientsFile'], 'file', 'extensions' => 'xlsx, xls', 'maxSize' => 10 * 1024 * 1024], // 10MB
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'config_key' => 'Clave de Configuración',
            'config_value' => 'Valor de Configuración',
            'description' => 'Descripción',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
            'logoFile' => 'Logo de la Empresa',
            'conditionsFile' => 'Condiciones de Alquiler',
        ];
    }

    /**
     * Obtener configuración por clave
     */
    public static function getConfig($key, $default = null)
    {
        $config = self::findOne(['config_key' => $key]);
        return $config ? $config->config_value : $default;
    }

    /**
     * Establecer configuración
     */
    public static function setConfig($key, $value, $description = null)
    {
        $config = self::findOne(['config_key' => $key]);
        
        if (!$config) {
            $config = new self();
            $config->config_key = $key;
        }
        
        $config->config_value = $value;
        if ($description) {
            $config->description = $description;
        }
        
        return $config->save();
    }

    /**
     * Obtener todos los archivos de configuración
     */
    public static function getFileConfigs()
    {
        return [
            self::LOGO_FILE => [
                'label' => 'Logo de la Empresa',
                'description' => 'Logo principal de Facto Rent a Car (PNG, JPG, SVG)',
                'directory' => self::LOGO_DIR,
                'extensions' => ['png', 'jpg', 'jpeg', 'gif', 'svg'],
                'maxSize' => 2 * 1024 * 1024,
                'currentFile' => self::getConfig(self::LOGO_FILE),
            ],
            self::RENTAL_CONDITIONS_FILE => [
                'label' => 'Condiciones de Alquiler',
                'description' => 'Documento con términos y condiciones de alquiler (PDF, DOC)',
                'directory' => self::CONDITIONS_DIR,
                'extensions' => ['pdf', 'doc', 'docx', 'txt'],
                'maxSize' => 5 * 1024 * 1024,
                'currentFile' => self::getConfig(self::RENTAL_CONDITIONS_FILE),
            ],
        ];
    }

    /**
     * Obtener información de la empresa
     */
    public static function getCompanyInfo()
    {
        return [
            'name' => self::getConfig(self::COMPANY_NAME, 'FACTO RENT A CAR'),
            'address' => self::getConfig(self::COMPANY_ADDRESS, '3-101-880789, San Ramón, Alajuela, Costa Rica'),
            'phone' => self::getConfig(self::COMPANY_PHONE, ''),
            'email' => self::getConfig(self::COMPANY_EMAIL, ''),
            'logo' => self::getLogoPath(),
            'conditions' => self::getConditionsPath(),
            'bank_accounts' => self::getBankAccounts(),
            'simemovil' => self::getConfig(self::SIMPEMOVIL_NUMBER, '83670937'),
        ];
    }

    /**
     * Obtener ruta del logo
     */
    public static function getLogoPath()
    {
        $logoFile = self::getConfig(self::LOGO_FILE);
        if ($logoFile && file_exists(Yii::getAlias('@webroot/' . self::LOGO_DIR . $logoFile))) {
            return Yii::getAlias('@web/' . self::LOGO_DIR . $logoFile);
        }
        return null;
    }

    /**
     * Obtener ruta de las condiciones
     */
    public static function getConditionsPath()
    {
        $conditionsFile = self::getConfig(self::RENTAL_CONDITIONS_FILE);
        if ($conditionsFile && file_exists(Yii::getAlias('@webroot/' . self::CONDITIONS_DIR . $conditionsFile))) {
            return Yii::getAlias('@web/' . self::CONDITIONS_DIR . $conditionsFile);
        }
        return null;
    }

    /**
     * Obtener cuentas bancarias
     */
    public static function getBankAccounts()
    {
        $accounts = self::getConfig(self::BANK_ACCOUNTS);
        if ($accounts) {
            // Si es un string simple, convertirlo a array
            if (is_string($accounts) && !json_decode($accounts)) {
                return [
                    [
                        'bank' => 'BCR',
                        'account' => 'IBAN:CR75015201001050506181',
                        'currency' => '₡'
                    ],
                    [
                        'bank' => 'BN',
                        'account' => 'IBAN: CR49015102020010977051',
                        'currency' => '₡'
                    ]
                ];
            }
            return json_decode($accounts, true) ?: [];
        }
        
        // Cuentas por defecto basadas en el formato de orden
        return [
            [
                'bank' => 'BCR',
                'account' => 'IBAN:CR75015201001050506181',
                'currency' => '₡'
            ],
            [
                'bank' => 'BN',
                'account' => 'IBAN: CR49015102020010977051',
                'currency' => '₡'
            ]
        ];
    }

    /**
     * Subir archivo
     */
    public function uploadFile($file, $configKey)
    {
        $configs = self::getFileConfigs();
        
        if (!isset($configs[$configKey])) {
            return false;
        }
        
        $config = $configs[$configKey];
        $directory = Yii::getAlias('@webroot/' . $config['directory']);
        
        // Crear directorio si no existe
        if (!is_dir($directory)) {
            FileHelper::createDirectory($directory);
        }
        
        // Generar nombre único
        $extension = $file->extension;
        $fileName = $configKey . '_' . time() . '.' . $extension;
        $filePath = $directory . $fileName;
        
        if ($file->saveAs($filePath)) {
            // Eliminar archivo anterior si existe
            $oldFile = self::getConfig($configKey);
            if ($oldFile && file_exists($directory . $oldFile)) {
                unlink($directory . $oldFile);
            }
            
            // Guardar nueva configuración
            self::setConfig($configKey, $fileName, $config['description']);
            
            return $fileName;
        }
        
        return false;
    }

    /**
     * Eliminar archivo
     */
    public function deleteFile($configKey)
    {
        $configs = self::getFileConfigs();
        
        if (!isset($configs[$configKey])) {
            return false;
        }
        
        $config = $configs[$configKey];
        $fileName = self::getConfig($configKey);
        
        if ($fileName) {
            $filePath = Yii::getAlias('@webroot/' . $config['directory'] . $fileName);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Limpiar configuración
        self::setConfig($configKey, null);
        
        return true;
    }

    /**
     * Validar archivo de logo
     */
    public function validateLogoFile($attribute, $params)
    {
        if ($this->logoFile && !$this->logoFile->hasError) {
            $tempPath = $this->logoFile->tempName;
            
            // Verificar que el archivo temporal existe
            if (!file_exists($tempPath)) {
                $this->addError($attribute, 'Error al procesar el archivo temporal.');
                return;
            }
            
            // Validar extensión de manera más flexible
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
            $fileExtension = strtolower(pathinfo($this->logoFile->name, PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->addError($attribute, 'Sólo se aceptan archivos con las siguientes extensiones: ' . implode(', ', $allowedExtensions));
                return;
            }
            
            // Para archivos SVG, solo validar que sea texto
            if ($fileExtension === 'svg') {
                $content = file_get_contents($tempPath);
                if (strpos($content, '<svg') === false) {
                    $this->addError($attribute, 'El archivo SVG no es válido.');
                    return;
                }
            } else {
                // Para otros formatos de imagen, validar con getimagesize
                $imageInfo = getimagesize($tempPath);
                
                if ($imageInfo === false) {
                    $this->addError($attribute, 'El archivo no es una imagen válida.');
                    return;
                }
                
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                
                // Permitir cualquier tamaño, se redimensionará automáticamente
                // Solo mostrar advertencia si es muy pequeño
                if ($width < 50 || $height < 50) {
                    $this->addError($attribute, "La imagen es muy pequeña. Mínimo recomendado: 100x100 píxeles. Actual: {$width}x{$height}px");
                }
            }
        }
    }

    /**
     * Procesar y redimensionar logo a 90x90px
     */
    public function processLogo($file, $configKey)
    {
        $configs = self::getFileConfigs();
        
        if (!isset($configs[$configKey])) {
            return false;
        }
        
        $config = $configs[$configKey];
        $directory = Yii::getAlias('@webroot/' . $config['directory']);
        
        // Crear directorio si no existe
        if (!is_dir($directory)) {
            FileHelper::createDirectory($directory);
        }
        
        // Generar nombre único
        $extension = 'png'; // Siempre guardar como PNG para mejor calidad
        $fileName = 'logo_90x90_' . time() . '.' . $extension;
        $filePath = $directory . $fileName;
        
        // Procesar imagen
        $tempPath = $file->tempName;
        $imageInfo = getimagesize($tempPath);
        
        if ($imageInfo === false) {
            return false;
        }
        
        // Crear imagen desde archivo temporal
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($tempPath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($tempPath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($tempPath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        // Crear imagen de destino 90x90
        $destImage = imagecreatetruecolor(90, 90);
        
        // Mantener transparencia para PNG
        if ($imageInfo[2] == IMAGETYPE_PNG) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefill($destImage, 0, 0, $transparent);
        } else {
            // Fondo blanco para otros formatos
            $white = imagecolorallocate($destImage, 255, 255, 255);
            imagefill($destImage, 0, 0, $white);
        }
        
        // Redimensionar manteniendo proporción
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        
        // Calcular dimensiones para mantener proporción
        $ratio = min(90 / $sourceWidth, 90 / $sourceHeight);
        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);
        
        // Centrar la imagen
        $offsetX = (90 - $newWidth) / 2;
        $offsetY = (90 - $newHeight) / 2;
        
        // Redimensionar
        imagecopyresampled(
            $destImage, $sourceImage,
            $offsetX, $offsetY,
            0, 0,
            $newWidth, $newHeight,
            $sourceWidth, $sourceHeight
        );
        
        // Guardar como PNG
        $result = imagepng($destImage, $filePath, 9); // Máxima calidad
        
        // Limpiar memoria
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        if ($result) {
            // Eliminar archivo anterior si existe
            $oldFile = self::getConfig($configKey);
            if ($oldFile && file_exists($directory . $oldFile)) {
                unlink($directory . $oldFile);
            }
            
            // Guardar nueva configuración
            self::setConfig($configKey, $fileName, $config['description']);
            
            return $fileName;
        }
        
        return false;
    }

    /**
     * Crear directorios necesarios
     */
    public static function createDirectories()
    {
        $directories = [
            Yii::getAlias('@webroot/' . self::UPLOAD_DIR),
            Yii::getAlias('@webroot/' . self::LOGO_DIR),
            Yii::getAlias('@webroot/' . self::CONDITIONS_DIR),
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }
        }
    }
}
