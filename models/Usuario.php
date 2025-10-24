<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Modelo de Usuario
 * Tabla: usuarios
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string $rol
 * @property int $activo
 * @property string $created_at
 * @property string $updated_at
 * @property string $auth_key
 * @property string $access_token
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'nombre', 'email'], 'required'],
            [['activo'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['username', 'email'], 'string', 'max' => 255],
            [['username', 'email'], 'unique'],
            ['email', 'email'],
            [['password'], 'string', 'min' => 6],
            [['nombre', 'apellido'], 'string', 'max' => 100],
            [['rol'], 'string', 'max' => 50],
            [['rol'], 'in', 'range' => ['SUPERADMIN', 'ADMIN', 'USER', 'VIEWER']],
            [['auth_key', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Nombre de Usuario',
            'password' => 'Contraseña',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'email' => 'Email',
            'rol' => 'Rol',
            'activo' => 'Activo',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'activo' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'activo' => 1]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'activo' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->hasAttribute('auth_key') ? $this->auth_key : null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->hasAttribute('auth_key') ? $this->auth_key === $authKey : true;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        if ($this->hasAttribute('auth_key')) {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }
    }

    /**
     * Generates new access token
     */
    public function generateAccessToken()
    {
        if ($this->hasAttribute('access_token')) {
            $this->access_token = Yii::$app->security->generateRandomString();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && $this->hasAttribute('auth_key')) {
                $this->generateAuthKey();
            }
            if ($insert && $this->hasAttribute('access_token')) {
                $this->generateAccessToken();
            }
            return true;
        }
        return false;
    }

    /**
     * Obtiene el nombre completo del usuario
     * @return string
     */
    public function getNombreCompleto()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        $rol = strtoupper($this->rol);
        
        $permissions = [
            'SUPERADMIN' => ['*'], // Acceso a todo
            'ADMIN' => [
                'manage_clients', 'manage_cars', 'manage_rentals', 
                'manage_orders', 'view_reports', 'manage_users'
            ],
            'USER' => [
                'manage_clients', 'manage_cars', 'manage_rentals', 
                'manage_orders', 'view_reports'
            ],
            'VIEWER' => ['view_reports'],
        ];
        
        if ($rol === 'SUPERADMIN') {
            return true;
        }
        
        return isset($permissions[$rol]) && in_array($permission, $permissions[$rol]);
    }
}

