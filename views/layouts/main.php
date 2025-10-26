<?php
/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => 'UTF-8'], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => 'Sistema de Gestión de Alquiler de Vehículos']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?> - Facto Rent a Car</title>
    <?php $this->head() ?>
    
    <!-- Sistema de Tema Light -->
    <?php $this->registerCssFile('@web/css/themes.css'); ?>
    
    <!-- Sistema de validación visual de formularios -->
    <?php $this->registerCssFile('@web/css/form-validation.css'); ?>
    <?php $this->registerJsFile('@web/js/form-validation.js'); ?>
    
    <!-- Menú de acciones -->
    <?php $this->registerCssFile('@web/css/actions-menu.css'); ?>
    <?php $this->registerJsFile('@web/js/actions-menu.js'); ?>
    
    <!-- Navigation Drawer -->
    <?php $this->registerJsFile('@web/js/navigation-drawer.js'); ?>
    
    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --primary-color: #3fa9f5;
            --secondary-color: #0d001e;
            --accent-color: #1b305b;
            --surface-color: #22487a;
            --drawer-width: 280px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        /* Floating Menu Toggle */
        .menu-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, var(--surface-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            font-size: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 12px rgba(13, 0, 30, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .menu-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(13, 0, 30, 0.4);
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--secondary-color) 100%);
        }
        
        
        
        
        
        
        
        
        /* Navigation Drawer */
        .drawer {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--drawer-width);
            height: 100vh;
            background: #1F2937;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 999;
            overflow-y: auto;
        }
        
        .drawer.open {
            transform: translateX(0);
        }
        
        .drawer-header {
            padding: 20px;
            background: #1F2937;
            color: white;
            text-align: center;
        }
        
        .drawer-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .drawer-subtitle {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .drawer-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin: 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            min-height: 48px;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-left-color: var(--primary-color);
            font-weight: 600;
        }
        
        .nav-icon {
            font-size: 20px;
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #FFFFFF;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
        }
        
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
        
        .nav-text {
            font-size: 16px;
            font-weight: 500;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 24px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.drawer-open {
            margin-left: var(--drawer-width);
        }
        
        /* Overlay */
        .drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .drawer-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 16px 24px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Breadcrumbs */
        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 24px;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #6c757d;
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            .drawer {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: var(--drawer-width);
            }
        }
        
        @media (max-width: 767px) {
            .drawer {
                width: 100%;
                max-width: 320px;
            }
            
            .main-content.drawer-open {
                margin-left: 0;
            }
            
            .menu-toggle {
                top: 15px;
                left: 15px;
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
        }
        
        /* Logout Button Style */
        .logout-button {
            background: none;
            border: none;
            color: #ff6b6b;
            width: 100%;
            text-align: left;
            padding: 16px 24px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .logout-button:hover {
            background-color: rgba(255, 107, 107, 0.1);
            color: #ff5252;
        }
        
        /* Botón de Regreso */
        .back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(63, 169, 245, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(63, 169, 245, 0.4);
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            color: white;
            text-decoration: none;
        }
        
        .back-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(63, 169, 245, 0.3);
        }
        
        .back-button .material-symbols-outlined {
            font-size: 18px;
        }
        
        /* Responsive para botón de regreso */
        @media (max-width: 768px) {
            .back-button {
                top: 15px;
                right: 15px;
                padding: 10px 14px;
                font-size: 13px;
            }
            
            .back-button .material-symbols-outlined {
                font-size: 16px;
            }
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<?php if (!Yii::$app->user->isGuest): ?>
<!-- Floating Menu Toggle -->
<button class="menu-toggle" onclick="toggleDrawer()" title="Menú">
    <span class="material-symbols-outlined">menu</span>
</button>

<!-- Navigation Drawer -->
<nav class="drawer" id="drawer">
    <div class="drawer-header">
        <div class="drawer-title">Facto Rent a Car</div>
    </div>
    <div class="drawer-nav">
        
        <!-- Navigation Menu -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/site/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">dashboard</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'rental' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/rental/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">receipt_long</span>
                    <span class="nav-text">Alquileres</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'client' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/client/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">group</span>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'car' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/car/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">directions_car</span>
                    <span class="nav-text">Vehículos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'order' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/order/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">shopping_cart</span>
                    <span class="nav-text">Órdenes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'reports' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/reports/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">assessment</span>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'notes' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/notes/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">sticky_note_2</span>
                    <span class="nav-text">Notas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= Yii::$app->controller->id === 'config' ? 'active' : '' ?>" 
                   href="<?= \yii\helpers\Url::to(['/config/index']) ?>">
                    <span class="nav-icon material-symbols-outlined">settings</span>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
        </ul>
        
        
        <!-- Logout Section -->
        <div style="margin-top: auto; padding: 0; border-top: 1px solid rgba(255, 255, 255, 0.2);">
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline w-100']) ?>
            <?= Html::submitButton('<span class="material-symbols-outlined" style="font-size: 20px; margin-right: 8px;">logout</span>Cerrar Sesión', [
                'class' => 'logout-button'
            ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</nav>

<!-- Drawer Overlay -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- Botón de Regreso -->
<?php if (!Yii::$app->user->isGuest): ?>
<a href="javascript:history.back()" class="back-button" title="Regresar a la página anterior">
    <span class="material-symbols-outlined">arrow_back</span>
    Regresar
</a>
<?php endif; ?>

<!-- Main Content -->
<main class="main-content fade-in" id="mainContent">
    <?= Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) ?>
    <?= Alert::widget() ?>
    <?= $content ?>
</main>

<?php else: ?>
<!-- Login Page Layout -->
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-12">
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="mt-auto py-4 bg-light text-center text-muted" style="margin-top: auto !important;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                &copy; Facto Rent a Car <?= date('Y') ?>
            </div>
            <div class="col-md-6">
                Desarrollado por Ing.Ronald Rojas Castro
            </div>
        </div>
    </div>
</footer>


<!-- Modal Initialization - Cargar después de Bootstrap -->
<script src="<?= Yii::getAlias('@web/js/modal-init.js') ?>"></script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
