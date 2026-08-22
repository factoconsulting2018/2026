<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * Modelo de Vehículo
 * Tabla: cars
 *
 * @property int $id
 * @property string $car_id
 * @property string $nombre
 * @property string $imagen
 * @property string|null $facebook_banner
 * @property int $facebook_promo_enabled
 * @property string|null $facebook_promo_slug
 * @property int|null $marca_id
 * @property-read Brand|null $marca Relación con la tabla brands
 * @property string $placa
 * @property string $vin
 * @property int $cantidad_pasajeros
 * @property string $caracteristicas
 * @property string $empresa_seguro
 * @property string $telefono_seguro
 * @property string $empresa
 * @property int $skip_priority
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Car extends ActiveRecord
{
    /** Ruta relativa bajo @webroot donde se guardan fotos de vehículos */
    public const IMAGE_UPLOAD_DIR = 'uploads/cars';

    /** Ruta relativa bajo @webroot donde se guardan banners de Facebook */
    public const FACEBOOK_BANNER_UPLOAD_DIR = 'uploads/cars/banners';

    /** @var UploadedFile|null Archivo subido en el formulario (no se persiste en BD) */
    public $imagenFile;

    /** @var UploadedFile|null Banner de anuncio Facebook (no se persiste en BD) */
    public $facebookBannerFile;

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
            [['marca_id', 'cantidad_pasajeros', 'facebook_promo_enabled', 'skip_priority'], 'integer'],
            [['facebook_promo_enabled'], 'default', 'value' => 0],
            [['facebook_promo_enabled'], 'in', 'range' => [0, 1]],
            [['skip_priority'], 'default', 'value' => 0],
            [['skip_priority'], 'in', 'range' => [0, 1]],
            [['created_at', 'updated_at'], 'safe'],
            [['car_id', 'nombre', 'imagen', 'facebook_banner', 'facebook_promo_slug', 'placa', 'vin'], 'string', 'max' => 255],
            [['facebook_promo_slug'], 'string', 'max' => 120],
            [['facebook_promo_slug'], 'unique'],
            [
                ['imagenFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                'maxSize' => 5 * 1024 * 1024,
                'tooBig' => 'La imagen no puede superar 5 MB.',
            ],
            [
                ['facebookBannerFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp', 'gif'],
                'maxSize' => 5 * 1024 * 1024,
                'tooBig' => 'El banner no puede superar 5 MB.',
            ],
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
            'imagenFile' => 'Foto del vehículo',
            'facebook_banner' => 'Banner de anuncio Facebook',
            'facebookBannerFile' => 'Banner de anuncio Facebook',
            'facebook_promo_enabled' => 'Facebook promoción',
            'facebook_promo_slug' => 'Enlace promocional (slug)',
            'marca_id' => 'Marca ID',
            'placa' => 'Placa',
            'vin' => 'VIN',
            'cantidad_pasajeros' => 'Cantidad de Pasajeros',
            'caracteristicas' => 'Características',
            'empresa_seguro' => 'Empresa de Seguro',
            'telefono_seguro' => 'Teléfono de Seguro',
            'empresa' => 'Empresa',
            'skip_priority' => 'Saltar prioridad Facto',
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

            $this->facebook_promo_enabled = (int) (bool) $this->facebook_promo_enabled;
            $this->skip_priority = (int) (bool) $this->skip_priority;

            if (!$this->facebook_promo_enabled) {
                // Mantener slug y banner por si se reactiva; solo desactiva la promo.
            } elseif (empty($this->facebook_promo_slug)) {
                $this->ensureFacebookPromoSlug();
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
     * ¿Es vehículo Moviliza? (tolerante a mayúsculas por beforeSave).
     */
    public function isMoviliza(): bool
    {
        return strcasecmp(trim((string) ($this->empresa ?? '')), 'Moviliza') === 0;
    }

    /**
     * ¿Se excluye de la prioridad Facto en condicionales?
     * (vehículos opcionales: camión, etc.)
     */
    public function skipsPriority(): bool
    {
        return (int) ($this->skip_priority ?? 0) === 1;
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
     * Marca del vehículo (tabla brands).
     */
    public function getMarca()
    {
        return $this->hasOne(Brand::class, ['id' => 'marca_id']);
    }

    /**
     * Texto para columna "Modelo": resto del nombre quitando la marca al inicio, o el nombre completo.
     */
    public function getDisplayModelo(): string
    {
        $nombre = trim((string) $this->nombre);
        if ($nombre === '') {
            return '';
        }
        $brand = $this->marca;
        if ($brand !== null && $brand->name !== '') {
            $prefix = trim($brand->name);
            if ($prefix !== '' && stripos($nombre, $prefix) === 0) {
                $rest = trim(mb_substr($nombre, mb_strlen($prefix)));
                return $rest !== '' ? $rest : $nombre;
            }
        }
        return $nombre;
    }

    /**
     * Año extraído del nombre del vehículo (ej. "BEAT 2018") si existe.
     */
    public function getDisplayAnio(): string
    {
        if (preg_match('/\b(19[89]\d|20\d{2})\b/', (string) $this->nombre, $m)) {
            return $m[1];
        }
        return '';
    }

    /**
     * URL pública para mostrar la imagen (ruta local o URL externa legacy).
     */
    public function getImagenUrl(): ?string
    {
        $imagen = trim((string) $this->imagen);
        if ($imagen === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $imagen)) {
            return $imagen;
        }
        $rel = ltrim(str_replace('\\', '/', $imagen), '/');
        $full = Yii::getAlias('@webroot/' . $rel);
        if (is_file($full)) {
            return Yii::getAlias('@web/' . $rel);
        }

        return $imagen;
    }

    /**
     * URL pública del banner de Facebook (ruta local o URL externa legacy).
     */
    public function getFacebookBannerUrl(): ?string
    {
        $banner = trim((string) $this->facebook_banner);
        if ($banner === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $banner)) {
            return $banner;
        }
        $rel = ltrim(str_replace('\\', '/', $banner), '/');
        $full = Yii::getAlias('@webroot/' . $rel);
        if (is_file($full)) {
            return Yii::getAlias('@web/' . $rel);
        }

        return $banner;
    }

    /**
     * Ruta absoluta en disco del banner de Facebook.
     */
    public function getFacebookBannerFilesystemPath(): ?string
    {
        $banner = trim((string) $this->facebook_banner);
        if ($banner === '' || preg_match('#^https?://#i', $banner)) {
            return null;
        }
        $rel = ltrim(str_replace('\\', '/', $banner), '/');
        $full = Yii::getAlias('@webroot/' . $rel);

        return is_file($full) ? str_replace('\\', '/', $full) : null;
    }

    /**
     * URL pública absoluta de la landing promo de este vehículo.
     */
    public function getFacebookPromoUrl(): ?string
    {
        if (!(int) $this->facebook_promo_enabled) {
            return null;
        }
        $slug = trim((string) $this->facebook_promo_slug);
        if ($slug === '') {
            return null;
        }

        return Url::to(['/promo/' . $slug], true);
    }

    /**
     * Etiqueta legible para selector promo. Solo nombre + placa.
     * Se omite la marca a propósito para no duplicarla cuando el nombre ya la contiene
     * (ej.: "NISSAN FRONTIER AZUL").
     */
    public function getPromoDisplayLabel(): string
    {
        $name = trim((string) $this->nombre);
        if ($name === '') {
            $name = 'Vehículo';
        }
        $plate = trim((string) $this->placa);
        if ($plate !== '') {
            $name .= ' (' . $plate . ')';
        }

        return $name;
    }

    /**
     * Vehículos con promoción Facebook activa y slug válido.
     *
     * @return self[]
     */
    public static function findActivePromos(): array
    {
        return self::find()
            ->with(['marca'])
            ->where(['facebook_promo_enabled' => 1])
            ->andWhere(['not', ['facebook_promo_slug' => null]])
            ->andWhere(['<>', 'facebook_promo_slug', ''])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();
    }

    /**
     * Guarda el banner subido en {@see facebookBannerFile} y actualiza {@see facebook_banner}.
     */
    public function uploadFacebookBannerFile(): bool
    {
        if (!$this->facebookBannerFile instanceof UploadedFile || $this->facebookBannerFile->error === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (!$this->validate(['facebookBannerFile'])) {
            return false;
        }

        $dir = Yii::getAlias('@webroot/' . self::FACEBOOK_BANNER_UPLOAD_DIR);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            $this->addError('facebookBannerFile', 'No se pudo crear la carpeta de banners.');
            return false;
        }

        $this->deleteStoredFacebookBannerFile();

        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) $this->placa);
        if ($base === '' || $base === '_') {
            $base = 'banner';
        }
        $fileName = 'fb_' . strtolower($base) . '_' . time() . '.' . strtolower($this->facebookBannerFile->extension);
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

        if (!$this->facebookBannerFile->saveAs($filePath)) {
            $this->addError('facebookBannerFile', 'No se pudo guardar el banner en el servidor.');
            return false;
        }

        $this->facebook_banner = '/' . self::FACEBOOK_BANNER_UPLOAD_DIR . '/' . $fileName;

        return true;
    }

    /**
     * Elimina el archivo local del banner (no borra URLs externas).
     */
    public function deleteStoredFacebookBannerFile(): void
    {
        $path = $this->getFacebookBannerFilesystemPath();
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Genera y asigna un slug único para la promo Facebook.
     */
    protected function ensureFacebookPromoSlug(): void
    {
        $brandName = '';
        try {
            if (!empty($this->marca_id)) {
                $marca = $this->marca ?? Brand::findOne($this->marca_id);
                if ($marca) {
                    $brandName = (string) $marca->name;
                }
            }
        } catch (\Throwable $e) {
            $brandName = '';
        }

        $base = self::slugify($brandName . '-' . $this->nombre . '-' . $this->placa);
        if ($base === '' || $base === '-') {
            $base = 'vehiculo-' . ($this->id ?: time());
        }

        $slug = $base;
        $i = 2;
        while (self::find()
            ->where(['facebook_promo_slug' => $slug])
            ->andWhere(['<>', 'id', (int) $this->id])
            ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        $this->facebook_promo_slug = $slug;
    }

    /**
     * Normaliza texto a slug URL-safe.
     */
    protected static function slugify(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        $text = mb_strtolower($text, 'UTF-8');
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');

        return $text;
    }

    /**
     * Ruta absoluta en disco para PDF u otros usos internos.
     */
    public function getImagenFilesystemPath(): ?string
    {
        $imagen = trim((string) $this->imagen);
        if ($imagen === '' || preg_match('#^https?://#i', $imagen)) {
            return null;
        }
        $rel = ltrim(str_replace('\\', '/', $imagen), '/');
        $full = Yii::getAlias('@webroot/' . $rel);

        return is_file($full) ? str_replace('\\', '/', $full) : null;
    }

    /**
     * Guarda el archivo subido en {@see imagenFile} y actualiza {@see imagen}.
     */
    public function uploadImagenFile(): bool
    {
        if (!$this->imagenFile instanceof UploadedFile || $this->imagenFile->error === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (!$this->validate(['imagenFile'])) {
            return false;
        }

        $dir = Yii::getAlias('@webroot/' . self::IMAGE_UPLOAD_DIR);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            $this->addError('imagenFile', 'No se pudo crear la carpeta de imágenes.');
            return false;
        }

        $this->deleteStoredImagenFile();

        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) $this->placa);
        if ($base === '' || $base === '_') {
            $base = 'vehiculo';
        }
        $fileName = strtolower($base) . '_' . time() . '.' . strtolower($this->imagenFile->extension);
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

        if (!$this->imagenFile->saveAs($filePath)) {
            $this->addError('imagenFile', 'No se pudo guardar la imagen en el servidor.');
            return false;
        }

        $this->imagen = '/' . self::IMAGE_UPLOAD_DIR . '/' . $fileName;

        return true;
    }

    /**
     * Elimina el archivo local asociado a {@see imagen} (no borra URLs externas).
     */
    public function deleteStoredImagenFile(): void
    {
        $path = $this->getImagenFilesystemPath();
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
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

    /**
     * Sincroniza el campo `status` del vehículo con las rentas activas hoy.
     * - Si tiene una renta síncrona vigente hoy (no cancelada, swap_date respetado): 'alquilado'.
     * - Si no: 'disponible'.
     * Respeta los estados manuales 'fuera_servicio' y 'mantenimiento' (no los cambia).
     *
     * @return bool true si hubo cambio de estado.
     */
    public static function syncStatusFromRentals(int $carId): bool
    {
        $car = self::findOne($carId);
        if (!$car || in_array($car->status, ['fuera_servicio', 'mantenimiento'], true)) {
            return false;
        }

        $today = date('Y-m-d');
        $available = CarAvailability::isCarAvailable($carId, $today, $today);
        $expected = $available ? 'disponible' : 'alquilado';

        if ($car->status === $expected) {
            return false;
        }

        $car->status = $expected;
        return (bool) $car->save(false, ['status', 'updated_at']);
    }

    /**
     * Sincroniza el estado de TODOS los vehículos (excepto fuera_servicio/mantenimiento).
     *
     * @return int cantidad de vehículos cuyo estado cambió.
     */
    public static function syncAllStatuses(): int
    {
        $changed = 0;
        $query = self::find()
            ->select(['id', 'status'])
            ->where(['not in', 'status', ['fuera_servicio', 'mantenimiento']]);

        foreach ($query->each(100) as $car) {
            if (self::syncStatusFromRentals((int) $car->id)) {
                $changed++;
            }
        }

        return $changed;
    }
}

