<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo de Cliente
 * Tabla: clients
 *
 * @property int $id
 * @property string $client_id
 * @property string $nombre
 * @property string $apellido
 * @property string $full_name
 * @property string $cedula_fisica
 * @property string $whatsapp
 * @property string $email
 * @property string $address
 * @property string $notes
 * @property string $licencias_choferes
 * @property int $es_aliado
 * @property int $es_cliente_facto
 * @property string $status
 * @property string $tipo_identificacion
 * @property string $situacion_tributaria
 * @property string $regimen_tributario
 * @property string $actividad_economica_codigo
 * @property string $actividad_economica_descripcion
 * @property string $created_at
 * @property string $updated_at
 */
class Client extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_name', 'cedula_fisica'], 'required'],
            [['es_aliado', 'es_cliente_facto'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['client_id', 'nombre', 'apellido', 'full_name', 'cedula_fisica', 'email'], 'string', 'max' => 255],
            [['whatsapp'], 'string', 'max' => 20],
            [['address', 'notes', 'licencias_choferes'], 'string'],
            [['status'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => ['active', 'inactive']],
            [['tipo_identificacion', 'situacion_tributaria', 'regimen_tributario'], 'string', 'max' => 255],
            [['actividad_economica_codigo'], 'string', 'max' => 50],
            [['actividad_economica_descripcion'], 'string', 'max' => 500],
            ['email', 'email', 'skipOnEmpty' => true],
            ['cedula_fisica', 'unique', 'targetClass' => self::class, 'filter' => function ($query) {
                // Excluir el registro actual cuando se está actualizando
                if (!$this->isNewRecord && $this->id) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
                return $query;
            }, 'message' => 'La cédula física "{value}" ya está registrada en el sistema'],
        ];
    }

    /**
     * Convierte campos vacíos a NULL antes de guardar y maneja la generación de IDs
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Generar client_id si es nuevo
            if ($insert && empty($this->client_id)) {
                $this->client_id = $this->generateClientId();
            }
            
            // Convertir nombres a mayúsculas y dividir full_name en nombre y apellido
            if (!empty($this->full_name)) {
                $this->full_name = strtoupper(trim($this->full_name));
                $parts = preg_split('/\s+/', $this->full_name, 2);
                $this->nombre = $parts[0] ?? '';
                $this->apellido = $parts[1] ?? '';
            }
            
            // Asegurar que nombre y apellido estén en mayúsculas
            if (!empty($this->nombre)) {
                $this->nombre = strtoupper(trim($this->nombre));
            }
            if (!empty($this->apellido)) {
                $this->apellido = strtoupper(trim($this->apellido));
            }
            
            // Convertir campos vacíos a NULL para evitar conflictos de unicidad
            if (empty($this->email)) {
                $this->email = null;
            }
            if (empty($this->whatsapp)) {
                $this->whatsapp = null;
            }
            if (empty($this->address)) {
                $this->address = null;
            }
            if (empty($this->notes)) {
                $this->notes = null;
            }
            
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'ID de Cliente',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'full_name' => 'Nombre Completo',
            'cedula_fisica' => 'Cédula Física',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'address' => 'Dirección',
            'notes' => 'Notas',
            'licencias_choferes' => 'Licencias de Choferes',
            'es_aliado' => '¿Es Aliado?',
            'es_cliente_facto' => '¿Es Cliente Facto?',
            'status' => 'Estado',
            'tipo_identificacion' => 'Tipo de Identificación',
            'situacion_tributaria' => 'Situación Tributaria',
            'regimen_tributario' => 'Régimen Tributario',
            'actividad_economica_codigo' => 'Código de Actividad Económica',
            'actividad_economica_descripcion' => 'Descripción de Actividad Económica',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }


    /**
     * Genera un ID único para el cliente
     * @return string
     */
    protected function generateClientId()
    {
        $prefix = 'CLI';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    /**
     * Obtiene el nombre en mayúsculas
     * @return string
     */
    public function getNombreUppercase()
    {
        return strtoupper($this->nombre ?? '');
    }
    
    /**
     * Obtiene el apellido en mayúsculas
     * @return string
     */
    public function getApellidoUppercase()
    {
        return strtoupper($this->apellido ?? '');
    }
    
    /**
     * Obtiene el nombre completo en mayúsculas
     * @return string
     */
    public function getFullNameUppercase()
    {
        return strtoupper($this->full_name ?? '');
    }

    /**
     * Obtiene los choferes asociados al cliente
     * @return array
     */
    public function getChoferes()
    {
        if (empty($this->licencias_choferes)) {
            return [];
        }
        
        $choferes = json_decode($this->licencias_choferes, true);
        return is_array($choferes) ? $choferes : [];
    }

    /**
     * Establece los choferes del cliente
     * @param array $choferes
     */
    public function setChoferes($choferes)
    {
        $this->licencias_choferes = json_encode($choferes, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene los alquileres del cliente
     * @return \yii\db\ActiveQuery
     */
    public function getRentals()
    {
        return $this->hasMany(Rental::class, ['cliente_id' => 'id']);
    }

    /**
     * Obtiene los archivos del cliente
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(ClientFile::class, ['client_id' => 'id']);
    }

    /**
     * Formatea el número de WhatsApp
     * @param string $number
     * @return string
     */
    public static function formatWhatsApp($number)
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        
        if (strlen($clean) === 8) {
            $clean = '506' . $clean;
        } elseif (strlen($clean) === 11 && substr($clean, 0, 3) === '506') {
            // Ya tiene el código de país
        } else {
            $clean = '506' . $clean;
        }
        
        return $clean;
    }
}

