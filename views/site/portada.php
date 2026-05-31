<?php
/** @var yii\web\View $this */
/** @var string $backgroundUrl */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\CompanyConfig;

$companyInfo = CompanyConfig::getCompanyInfo();
$logoPath = $companyInfo['logo'] ?? null;
$companyName = $companyInfo['name'] ?? 'Facto Rent a Car';

$this->title = $companyName . ' — Renta de Vehículos';

$year = date('Y');
$today = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= Html::encode(Url::to('@web/css/material-symbols.css')) ?>" />
    <style>
        :root {
            --brand-dark: #0d001e;
            --brand-mid: #22487a;
            --brand-light: #2e6faa;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            position: relative;
            background-color: var(--brand-dark);
            color: #fff;
        }
        /* Capa de fondo con la imagen del día */
        .bg-image {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('<?= Html::encode($backgroundUrl) ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.45) saturate(1.05);
            z-index: 0;
            transition: opacity .8s ease;
            opacity: 0;
        }
        .bg-image.loaded { opacity: 1; }
        /* Gradiente encima del fondo para legibilidad */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background:
                linear-gradient(135deg, rgba(13,0,30,0.55) 0%, rgba(34,72,122,0.35) 50%, rgba(13,0,30,0.65) 100%);
            z-index: 1;
        }
        .page {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            gap: 12px;
        }
        .topbar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
        .topbar .brand img {
            height: 36px;
            width: auto;
        }
        .topbar .nav-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .topbar a.btn-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.12);
            color: #fff;
            text-decoration: none;
            border-radius: 999px;
            padding: 7px 16px;
            font-size: 14px;
            font-weight: 600;
            transition: background .15s ease, transform .15s ease;
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(8px);
        }
        .topbar a.btn-pill:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-1px);
        }
        .topbar a.btn-pill.primary {
            background: linear-gradient(135deg, var(--brand-mid) 0%, var(--brand-dark) 100%);
            border-color: rgba(255,255,255,0.25);
        }
        .topbar a.btn-pill.primary:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }

        /* Acciones principales: verde (alquiler) y celeste (membresía) */
        a.btn-action-rent {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.30) !important;
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
        }
        a.btn-action-rent:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
            box-shadow: 0 8px 22px rgba(34, 197, 94, 0.55);
            transform: translateY(-2px);
        }
        a.btn-action-membership {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.30) !important;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
        }
        a.btn-action-membership:hover {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
            box-shadow: 0 8px 22px rgba(14, 165, 233, 0.55);
            transform: translateY(-2px);
        }
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .hero-card {
            background: rgba(13,0,30,0.78);
            color: #fff;
            backdrop-filter: blur(14px);
            border-radius: 22px;
            box-shadow: 0 18px 60px rgba(0,0,0,0.55);
            border: 1px solid rgba(255,255,255,0.10);
            max-width: 720px;
            width: 100%;
            padding: 44px 36px;
            text-align: center;
        }
        .hero-card .logo-wrap {
            margin-bottom: 20px;
        }
        .hero-card .logo-wrap img {
            max-height: 110px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
        }
        .hero-card h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px;
            letter-spacing: 0.5px;
        }
        .hero-card .tagline {
            font-size: 1rem;
            opacity: 0.85;
            margin-bottom: 28px;
        }
        .hero-card .date-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .hero-card .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin: 24px 0 28px;
        }
        .hero-card .feature {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            transition: background .15s ease, transform .15s ease;
        }
        .hero-card .feature:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }
        .hero-card .feature .icon {
            font-size: 28px;
            margin-bottom: 6px;
        }
        .hero-card .feature .label {
            font-size: 13px;
            font-weight: 600;
        }
        .hero-card .cta {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-card .cta a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
            font-size: 15px;
        }
        .hero-card .cta a.btn-primary-cta {
            background: linear-gradient(135deg, var(--brand-mid) 0%, var(--brand-dark) 100%);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
        }
        .hero-card .cta a.btn-primary-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.5);
        }
        .hero-card .cta a.btn-secondary-cta {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.20);
        }
        .hero-card .cta a.btn-secondary-cta:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-2px);
        }
        .hero-card .contact-row {
            margin-top: 22px;
            font-size: 13px;
            opacity: 0.85;
            display: flex;
            gap: 18px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-card .contact-row a {
            color: #ffffff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .hero-card .contact-row a:hover {
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            padding: 16px 12px;
            font-size: 12px;
            opacity: 0.7;
        }
        .footer .divider {
            margin: 0 8px;
            opacity: 0.5;
        }
        @media (max-width: 640px) {
            .hero-card { padding: 32px 22px; border-radius: 18px; }
            .hero-card h1 { font-size: 1.5rem; }
            .hero-card .features { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .hero-card .feature { padding: 10px 6px; }
            .hero-card .feature .icon { font-size: 24px; }
            .hero-card .feature .label { font-size: 11px; }
            .topbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="bg-image" id="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="page">
        <div class="topbar">
            <a href="<?= Url::to(['/site/index']) ?>" class="brand">
                <?php if ($logoPath): ?>
                    <img src="<?= Html::encode($logoPath) ?>" alt="<?= Html::encode($companyName) ?>">
                <?php endif; ?>
                <span><?= Html::encode($companyName) ?></span>
            </a>
            <div class="nav-actions">
                <a href="<?= Url::to(['/solicitud-membresia']) ?>" class="btn-pill btn-action-membership">
                    <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
                    Solicitar Membresía
                </a>
                <a href="<?= Url::to(['/realizar-alquiler']) ?>" class="btn-pill btn-action-rent">
                    <span class="material-symbols-outlined" style="font-size:18px;">directions_car</span>
                    Realizar alquiler
                </a>
                <a href="<?= Url::to(['/site/login']) ?>" class="btn-pill" title="Iniciar sesión administrativa" style="padding:7px 12px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                </a>
            </div>
        </div>

        <div class="hero">
            <div class="hero-card">
                <?php if ($logoPath): ?>
                    <div class="logo-wrap">
                        <img src="<?= Html::encode($logoPath) ?>" alt="Logo">
                    </div>
                <?php endif; ?>

                <div class="date-pill">
                    <span class="material-symbols-outlined" style="font-size:16px;">calendar_today</span>
                    <?= Html::encode($today) ?>
                </div>

                <h1><?= Html::encode($companyName) ?></h1>
                <p class="tagline">Renta de vehículos en Costa Rica · Tu próxima aventura empieza aquí</p>

                <div class="features">
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">verified</span></div>
                        <div class="label">Disponibilidad inmediata</div>
                    </div>
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">support_agent</span></div>
                        <div class="label">Atención personalizada</div>
                    </div>
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">paid</span></div>
                        <div class="label">Precios competitivos</div>
                    </div>
                </div>

                <div class="cta">
                    <a href="<?= Url::to(['/realizar-alquiler']) ?>" class="btn-primary-cta btn-action-rent">
                        <span class="material-symbols-outlined" style="font-size:20px;">directions_car</span>
                        Realizar alquiler
                    </a>
                    <a href="<?= Url::to(['/solicitud-membresia']) ?>" class="btn-secondary-cta btn-action-membership">
                        <span class="material-symbols-outlined" style="font-size:20px;">person_add</span>
                        Solicitar Membresía
                    </a>
                </div>

                <div class="contact-row">
                    <a href="tel:+50640700485">
                        <span class="material-symbols-outlined" style="font-size:16px;">call</span>
                        4070-0485
                    </a>
                    <a href="https://wa.me/50683670937" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="font-size:16px;">chat</span>
                        WhatsApp 8367-0937
                    </a>
                    <a href="https://www.factorentacar.com" target="_blank" rel="noopener">
                        <span class="material-symbols-outlined" style="font-size:16px;">language</span>
                        factorentacar.com
                    </a>
                </div>
            </div>
        </div>

        <div class="footer">
            &copy; <?= $year ?> <?= Html::encode($companyName) ?>. Todos los derechos reservados.
            <span class="divider">·</span>
            Desarrollado por Ing. Ronald Rojas Castro
        </div>
    </div>

    <script>
        (function () {
            const bg = document.getElementById('bg-image');
            if (!bg) return;
            const cssUrl = bg.style.backgroundImage.replace(/^url\(['"]?/, '').replace(/['"]?\)$/, '');
            const url = cssUrl || <?= json_encode($backgroundUrl) ?>;
            const img = new Image();
            img.onload = function () { bg.classList.add('loaded'); };
            img.onerror = function () { bg.classList.add('loaded'); };
            img.src = url;
        })();
    </script>
</body>
</html>
