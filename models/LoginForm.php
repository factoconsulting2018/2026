<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm es el modelo para el formulario de login.
 *
 * @property-read Usuario|null $user
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @return array las reglas de validación.
     */
    public function rules()
    {
        return [
            // username y password son requeridos
            [['username', 'password'], 'required', 'message' => 'Este campo no puede estar vacío'],
            // rememberMe debe ser un booleano
            ['rememberMe', 'boolean'],
            // password es validado por validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Valida el password.
     * Este método sirve como validador inline para el password.
     *
     * @param string $attribute el atributo actualmente siendo validado
     * @param array $params los pares de nombre-valor adicionales dados en la regla
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Usuario o contraseña incorrectos.');
            } elseif ($user->activo != 1) {
                $this->addError($attribute, 'Esta cuenta ha sido desactivada.');
            }
        }
    }

    /**
     * Inicia sesión del usuario usando el username y password proporcionados.
     * @return bool si el modelo de login es exitoso
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Encuentra el usuario por [[username]]
     *
     * @return Usuario|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Usuario::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nombre de Usuario',
            'password' => 'Contraseña',
            'rememberMe' => 'Recordarme',
        ];
    }
}
