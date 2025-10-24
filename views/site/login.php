<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Iniciar Sesión';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="row justify-content-center mt-5">
        <div class="col-12 col-lg-6 col-xl-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <span class="material-symbols-outlined login-icon">directions_car</span>
                        <h2 class="login-title">Facto Rent a Car</h2>
                        <p class="text-muted">Sistema de Gestión de Alquiler de Vehículos</p>
                    </div>

                    <h4 class="text-center mb-4">Iniciar Sesión</h4>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'form-label'],
                            'inputOptions' => ['class' => 'form-control'],
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ],
                    ]); ?>

                        <?= $form->field($model, 'username')->textInput([
                            'autofocus' => true,
                            'placeholder' => 'Ingresa tu usuario'
                        ])->label('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">person</span>Usuario') ?>

                        <?= $form->field($model, 'password')->passwordInput([
                            'placeholder' => 'Ingresa tu contraseña'
                        ])->label('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">lock</span>Contraseña') ?>

                        <?= $form->field($model, 'rememberMe')->checkbox([
                            'template' => "<div class=\"form-check\">{input} {label}</div>\n{error}",
                            'labelOptions' => ['class' => 'form-check-label'],
                        ])->label('Recordarme') ?>

                        <div class="form-group mt-4">
                            <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">login</span>Ingresar', [
                                'class' => 'btn btn-primary w-100 py-3',
                                'name' => 'login-button'
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>

                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            Si no tienes cuenta, contacta al administrador del sistema
                        </small>
                    </div>

                    <?php if (YII_ENV_DEV): ?>
                    <div class="alert alert-info mt-4" role="alert">
                        <strong><span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">info</span>Modo Desarrollo:</strong><br>
                        Usuario: <code>admin</code><br>
                        Contraseña: <code>admin123</code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-4 text-muted">
                <small>&copy; <?= date('Y') ?> Facto Rent a Car. Todos los derechos reservados.</small>
            </div>
        </div>
    </div>
</div>

<style>
.site-login {
    background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
    min-height: 100vh;
    padding: 40px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.site-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
    z-index: -1;
}

.site-login .row {
    width: 100%;
    max-width: 100%;
    margin: 0;
    position: relative;
    z-index: 1;
}

.site-login .col-lg-6 {
    max-width: 600px;
    margin: 0 auto;
}

.site-login .col-xl-5 {
    max-width: 500px;
    margin: 0 auto;
}

.site-login .card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.login-icon {
    font-size: 64px !important;
    color: #3fa9f5;
    margin-bottom: 20px;
    display: block;
}

.login-title {
    color: #333;
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 2rem;
}

.btn-primary {
    background: linear-gradient(135deg, #22487a 0%, #0d001e 100%);
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    text-transform: none;
    letter-spacing: 0;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(34, 72, 122, 0.4);
    background: linear-gradient(135deg, #1b305b 0%, #14183d 100%);
}

.btn-primary:active {
    transform: translateY(-1px);
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3fa9f5;
    box-shadow: 0 0 0 0.2rem rgba(63, 169, 245, 0.25);
    background-color: #fff;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-check-input:checked {
    background-color: #3fa9f5;
    border-color: #3fa9f5;
}

.form-check-input {
    border-radius: 6px;
    border: 2px solid #dee2e6;
}

.alert-info {
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
}

.alert-info strong {
    color: #0c5460;
}

/* Responsive para escritorio */
@media (min-width: 992px) {
    .site-login {
        padding: 60px 80px;
    }
    
    .site-login .col-lg-6 {
        max-width: 600px;
        flex: 0 0 auto;
    }
    
    .site-login .card {
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
    }
    
    .login-icon {
        font-size: 80px !important;
    }
    
    .login-title {
        font-size: 2.5rem;
    }
}

/* Responsive para pantallas muy grandes */
@media (min-width: 1400px) {
    .site-login {
        padding: 80px 150px;
    }
    
    .site-login .col-xl-5 {
        max-width: 550px;
    }
    
    .login-icon {
        font-size: 100px !important;
    }
    
    .login-title {
        font-size: 3rem;
    }
}

/* Responsive para pantallas ultra anchas */
@media (min-width: 1920px) {
    .site-login {
        padding: 100px 200px;
    }
    
    .site-login .col-xl-5 {
        max-width: 650px;
    }
    
    .site-login .card {
        border-radius: 30px;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.2);
    }
    
    .login-icon {
        font-size: 120px !important;
    }
    
    .login-title {
        font-size: 3.5rem;
    }
}

/* Asegurar que el fondo cubra toda la pantalla */
html, body {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    overflow-x: hidden;
}

/* Responsive para tableta */
@media (max-width: 991px) and (min-width: 769px) {
    .site-login {
        padding: 30px 40px;
    }
    
    .site-login .col-lg-5 {
        max-width: 400px;
    }
    
    .login-icon {
        font-size: 64px !important;
    }
    
    .login-title {
        font-size: 2rem;
    }
}

/* Responsive para móvil */
@media (max-width: 768px) {
    .site-login {
        padding: 20px 15px;
        min-height: 100vh;
    }
    
    .site-login .row {
        width: 100%;
        margin: 0;
    }
    
    .site-login .col-lg-5 {
        max-width: 100%;
        padding: 0 10px;
    }
    
    .site-login .card {
        border-radius: 16px;
        margin: 0;
        width: 100%;
    }
    
    .login-icon {
        font-size: 48px !important;
    }
    
    .login-title {
        font-size: 1.75rem;
    }
    
    .site-login .card-body {
        padding: 2rem 1.5rem;
    }
}

/* Responsive para móviles pequeños */
@media (max-width: 480px) {
    .site-login {
        padding: 15px 10px;
    }
    
    .site-login .col-lg-5 {
        padding: 0 5px;
    }
    
    .site-login .card-body {
        padding: 1.5rem 1rem;
    }
    
    .login-icon {
        font-size: 40px !important;
    }
    
    .login-title {
        font-size: 1.5rem;
    }
}
</style>
