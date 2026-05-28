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
    const COMPANY_REQUIREMENTS = 'company_requirements';
    const INCIDENT_NOTIF_ENABLED = 'incident_notifications_enabled';
    const INCIDENT_NOTIF_FREQUENCY_DAYS = 'incident_notifications_frequency_days';
    const RENTAL_ORDER_PDF_FORMAT = 'rental_order_pdf_format';
    const RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_W = 'rental_order_pdf_vehicle_img_max_w';
    const RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_H = 'rental_order_pdf_vehicle_img_max_h';
    const RENTAL_ORDER_PDF_TEXT_MODE = 'rental_order_pdf_text_mode';
    const RENTAL_ORDER_PDF_TEXT_SCALE = 'rental_order_pdf_text_scale';
    const RENTAL_ORDER_PDF_TEXT_HEADER_TITULO = 'rental_order_pdf_text_header_titulo';
    const RENTAL_ORDER_PDF_TEXT_HEADER_MODELO = 'rental_order_pdf_text_header_modelo';
    const RENTAL_ORDER_PDF_TEXT_HEADER_META = 'rental_order_pdf_text_header_meta';
    const RENTAL_ORDER_PDF_TEXT_EMPRESA_NOMBRE = 'rental_order_pdf_text_empresa_nombre';
    const RENTAL_ORDER_PDF_TEXT_EMPRESA_LINEA = 'rental_order_pdf_text_empresa_linea';

    // Recordatorios automáticos Dekra (Revisión Vehicular)
    const DEKRA_ENABLED = 'dekra_reminders_enabled';
    const DEKRA_YEARS_AHEAD = 'dekra_reminders_years_ahead';
    const DEKRA_PLATE_MONTH_MAP = 'dekra_plate_month_map';
    const DEKRA_TALLER_NAME = 'dekra_taller_name';
    const DEKRA_DAY_OF_MONTH = 'dekra_day_of_month';

    // WhatsApp API (descargapro.com - Multi-session Baileys)
    const WHATSAPP_ENABLED = 'whatsapp_enabled';
    const WHATSAPP_API_URL = 'whatsapp_api_url';
    const WHATSAPP_SESSION_ID = 'whatsapp_session_id';
    const WHATSAPP_COUNTRY_CODE = 'whatsapp_country_code';
    const WHATSAPP_NOTIFY_ON_CREATE = 'whatsapp_notify_on_create';
    const WHATSAPP_NOTIFY_CLIENT = 'whatsapp_notify_client';
    const WHATSAPP_DAILY_ENABLED = 'whatsapp_daily_enabled';
    const WHATSAPP_DAILY_TIME = 'whatsapp_daily_time';
    const WHATSAPP_DAILY_LAST_SENT = 'whatsapp_daily_last_sent';
    const WHATSAPP_ADMIN_PHONE_1 = 'whatsapp_admin_phone_1';
    const WHATSAPP_ADMIN_PHONE_2 = 'whatsapp_admin_phone_2';
    const WHATSAPP_ADMIN_PHONE_3 = 'whatsapp_admin_phone_3';
    const WHATSAPP_ADMIN_PHONE_4 = 'whatsapp_admin_phone_4';
    const WHATSAPP_ADMIN_PHONE_5 = 'whatsapp_admin_phone_5';
    const WHATSAPP_PUBLIC_BASE_URL = 'whatsapp_public_base_url';

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
     * Configuración de recordatorios automáticos de Dekra (Revisión Vehicular).
     *
     * @return array{
     *     enabled: bool,
     *     years_ahead: int,
     *     day_of_month: int,
     *     taller_name: string,
     *     plate_month_map: array<int,int>,
     * }
     */
    public static function getDekraConfig(): array
    {
        $defaultMap = self::getDekraDefaultPlateMonthMap();

        $rawMap = self::getConfig(self::DEKRA_PLATE_MONTH_MAP);
        $decoded = $rawMap ? json_decode((string) $rawMap, true) : null;
        $map = is_array($decoded) ? $decoded : [];

        $sanitized = [];
        for ($digit = 0; $digit <= 9; $digit++) {
            $month = isset($map[$digit]) ? (int) $map[$digit] : (int) ($map[(string) $digit] ?? $defaultMap[$digit]);
            if ($month < 1 || $month > 12) {
                $month = $defaultMap[$digit];
            }
            $sanitized[$digit] = $month;
        }

        return [
            'enabled' => self::getConfig(self::DEKRA_ENABLED, '1') === '1',
            'years_ahead' => max(0, min(20, (int) self::getConfig(self::DEKRA_YEARS_AHEAD, '3'))),
            'day_of_month' => max(1, min(28, (int) self::getConfig(self::DEKRA_DAY_OF_MONTH, '1'))),
            'taller_name' => (string) self::getConfig(self::DEKRA_TALLER_NAME, 'Dekra (Revisión Vehicular)'),
            'plate_month_map' => $sanitized,
        ];
    }

    /**
     * Mapa por defecto dígito → mes (1=enero, …, 8=agosto, 9=septiembre, 0=octubre).
     *
     * @return array<int,int>
     */
    public static function getDekraDefaultPlateMonthMap(): array
    {
        return [
            0 => 10,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
        ];
    }

    /**
     * Persistir configuración de recordatorios Dekra.
     *
     * @param array<int,int> $plateMonthMap Dígito (0-9) → mes (1-12).
     */
    public static function saveDekraConfig(
        bool $enabled,
        int $yearsAhead,
        int $dayOfMonth,
        string $tallerName,
        array $plateMonthMap
    ): void {
        $yearsAhead = max(0, min(20, $yearsAhead));
        $dayOfMonth = max(1, min(28, $dayOfMonth));
        $tallerName = trim($tallerName) !== '' ? trim($tallerName) : 'Dekra (Revisión Vehicular)';

        $defaultMap = self::getDekraDefaultPlateMonthMap();
        $sanitized = [];
        for ($digit = 0; $digit <= 9; $digit++) {
            $month = isset($plateMonthMap[$digit]) ? (int) $plateMonthMap[$digit] : $defaultMap[$digit];
            if ($month < 1 || $month > 12) {
                $month = $defaultMap[$digit];
            }
            $sanitized[$digit] = $month;
        }

        self::setConfig(self::DEKRA_ENABLED, $enabled ? '1' : '0', 'Activar recordatorios automáticos de Dekra');
        self::setConfig(self::DEKRA_YEARS_AHEAD, (string) $yearsAhead, 'Años hacia adelante a generar recordatorios Dekra');
        self::setConfig(self::DEKRA_DAY_OF_MONTH, (string) $dayOfMonth, 'Día del mes para programar recordatorio Dekra');
        self::setConfig(self::DEKRA_TALLER_NAME, $tallerName, 'Nombre del taller / etiqueta de orden Dekra');
        self::setConfig(self::DEKRA_PLATE_MONTH_MAP, (string) json_encode($sanitized), 'Mapeo dígito de placa → mes para recordatorios Dekra');
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
            'requirements' => self::getConfig(self::COMPANY_REQUIREMENTS, ''),
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

    /** general | moderna */
    public static function getRentalOrderPdfFormat(): string
    {
        $v = (string) self::getConfig(self::RENTAL_ORDER_PDF_FORMAT, 'general');

        return $v === 'moderna' ? 'moderna' : 'general';
    }

    public static function getRentalOrderPdfView(): string
    {
        return self::getRentalOrderPdfFormat() === 'moderna'
            ? '@app/views/pdf/pdf-orden'
            : '@app/views/pdf/_rental-pdf';
    }

    /** Ancho máximo (px) de la foto del vehículo en PDF formato moderna. */
    public static function getRentalOrderPdfVehicleImageMaxWidth(): int
    {
        $w = (int) self::getConfig(self::RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_W, '170');

        return max(40, min(400, $w));
    }

    /** Alto máximo (px) de la foto del vehículo en PDF formato moderna. */
    public static function getRentalOrderPdfVehicleImageMaxHeight(): int
    {
        $h = (int) self::getConfig(self::RENTAL_ORDER_PDF_VEHICLE_IMG_MAX_H, '90');

        return max(30, min(280, $h));
    }

    /** Tamaños base (pt) del PDF moderna cuando el modo es proporcional al 100%. */
    public static function getRentalOrderPdfTextBaseSizes(): array
    {
        return [
            'header_titulo' => 39,
            'header_modelo' => 48,
            'header_meta' => 27,
            'empresa_nombre' => 36,
            'empresa_linea' => 24,
        ];
    }

    /** proporcional | numeros */
    public static function getRentalOrderPdfTextMode(): string
    {
        $mode = (string) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_MODE, 'proporcional');

        return $mode === 'numeros' ? 'numeros' : 'proporcional';
    }

    public static function getRentalOrderPdfTextScalePercent(): int
    {
        return max(50, min(300, (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_SCALE, '100')));
    }

    /** Tamaños finales en pt para encabezado y banda de empresa (PDF moderna). */
    public static function getRentalOrderPdfTextSizes(): array
    {
        $base = self::getRentalOrderPdfTextBaseSizes();

        if (self::getRentalOrderPdfTextMode() === 'numeros') {
            return [
                'header_titulo' => self::clampPdfTextPt(
                    (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_HEADER_TITULO, (string) $base['header_titulo']),
                    $base['header_titulo']
                ),
                'header_modelo' => self::clampPdfTextPt(
                    (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_HEADER_MODELO, (string) $base['header_modelo']),
                    $base['header_modelo']
                ),
                'header_meta' => self::clampPdfTextPt(
                    (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_HEADER_META, (string) $base['header_meta']),
                    $base['header_meta']
                ),
                'empresa_nombre' => self::clampPdfTextPt(
                    (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_EMPRESA_NOMBRE, (string) $base['empresa_nombre']),
                    $base['empresa_nombre']
                ),
                'empresa_linea' => self::clampPdfTextPt(
                    (int) self::getConfig(self::RENTAL_ORDER_PDF_TEXT_EMPRESA_LINEA, (string) $base['empresa_linea']),
                    $base['empresa_linea']
                ),
            ];
        }

        $factor = self::getRentalOrderPdfTextScalePercent() / 100;
        $sizes = [];
        foreach ($base as $key => $pt) {
            $sizes[$key] = max(8, (int) round($pt * $factor));
        }

        return $sizes;
    }

    private static function clampPdfTextPt(int $value, int $default): int
    {
        if ($value <= 0) {
            $value = $default;
        }

        return max(8, min(120, $value));
    }

    /**
     * Configuracion de la integracion WhatsApp para notificaciones.
     *
     * @return array{
     *     enabled: bool,
     *     api_url: string,
     *     session_id: string,
     *     country_code: string,
     *     notify_on_create: bool,
     *     notify_client: bool,
     *     daily_enabled: bool,
     *     daily_time: string,
     *     daily_last_sent: string,
     *     admin_phones: array<int,string>,
     *     public_base_url: string
     * }
     */
    public static function getWhatsAppConfig(): array
    {
        $phones = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = constant('self::WHATSAPP_ADMIN_PHONE_' . $i);
            $phones[$i] = trim((string) self::getConfig($key, ''));
        }

        return [
            'enabled' => self::getConfig(self::WHATSAPP_ENABLED, '0') === '1',
            'api_url' => rtrim((string) self::getConfig(self::WHATSAPP_API_URL, 'https://descargapro.com'), '/'),
            'session_id' => trim((string) self::getConfig(self::WHATSAPP_SESSION_ID, 'facto_rent')),
            'country_code' => trim((string) self::getConfig(self::WHATSAPP_COUNTRY_CODE, '506')),
            'notify_on_create' => self::getConfig(self::WHATSAPP_NOTIFY_ON_CREATE, '1') === '1',
            'notify_client' => self::getConfig(self::WHATSAPP_NOTIFY_CLIENT, '0') === '1',
            'daily_enabled' => self::getConfig(self::WHATSAPP_DAILY_ENABLED, '0') === '1',
            'daily_time' => trim((string) self::getConfig(self::WHATSAPP_DAILY_TIME, '08:00')),
            'daily_last_sent' => trim((string) self::getConfig(self::WHATSAPP_DAILY_LAST_SENT, '')),
            'admin_phones' => $phones,
            'public_base_url' => rtrim((string) self::getConfig(self::WHATSAPP_PUBLIC_BASE_URL, ''), '/'),
        ];
    }

    /**
     * Guarda configuracion de WhatsApp (excluye telefonos individuales).
     */
    public static function saveWhatsAppConfig(
        bool $enabled,
        string $apiUrl,
        string $sessionId,
        string $countryCode,
        bool $notifyOnCreate,
        array $adminPhones,
        string $publicBaseUrl = '',
        bool $notifyClient = false,
        bool $dailyEnabled = false,
        string $dailyTime = '08:00'
    ): void {
        self::setConfig(self::WHATSAPP_PUBLIC_BASE_URL, rtrim(trim($publicBaseUrl), '/'), 'URL base publica (https) accesible desde la API WhatsApp');
        self::setConfig(self::WHATSAPP_ENABLED, $enabled ? '1' : '0', 'Activar integracion WhatsApp');
        self::setConfig(self::WHATSAPP_API_URL, rtrim(trim($apiUrl), '/'), 'URL base de la API WhatsApp');
        self::setConfig(self::WHATSAPP_SESSION_ID, trim($sessionId) !== '' ? trim($sessionId) : 'facto_rent', 'sessionId de WhatsApp');
        self::setConfig(self::WHATSAPP_COUNTRY_CODE, preg_replace('/\D/', '', $countryCode) !== '' ? preg_replace('/\D/', '', $countryCode) : '506', 'Codigo de pais por defecto');
        self::setConfig(self::WHATSAPP_NOTIFY_ON_CREATE, $notifyOnCreate ? '1' : '0', 'Notificar al crear orden de alquiler');
        self::setConfig(self::WHATSAPP_NOTIFY_CLIENT, $notifyClient ? '1' : '0', 'Notificar tambien al cliente (telefono del cliente)');
        self::setConfig(self::WHATSAPP_DAILY_ENABLED, $dailyEnabled ? '1' : '0', 'Enviar resumen diario (entregas, devoluciones, disponibles) por WhatsApp');
        $dailyTime = trim($dailyTime);
        if (!preg_match('/^\d{2}:\d{2}$/', $dailyTime)) {
            $dailyTime = '08:00';
        }
        self::setConfig(self::WHATSAPP_DAILY_TIME, $dailyTime, 'Hora programada (HH:MM) para el resumen diario por WhatsApp');

        for ($i = 1; $i <= 5; $i++) {
            $key = constant('self::WHATSAPP_ADMIN_PHONE_' . $i);
            $raw = isset($adminPhones[$i]) ? (string) $adminPhones[$i] : '';
            self::setConfig($key, trim($raw), 'Telefono administrador ' . $i . ' para notificaciones WhatsApp');
        }
    }

    public static function wrapRentalConditionsHtml(string $html): string
    {
        if (self::getRentalOrderPdfFormat() !== 'moderna') {
            return $html;
        }

        return '<div style="padding-top:8px;"><h2 style="font-size:13pt;text-align:center;margin:0 0 12px;font-family:helvetica,sans-serif;">TÉRMINOS Y CONDICIONES DEL<br>ALQUILER</h2>' . $html . '</div>';
    }
}
