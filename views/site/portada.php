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

        /* Botón Sobre la empresa: estilo neutro (no compite con verde/celeste) */
        a.btn-action-about {
            background: rgba(255,255,255,0.14) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.32) !important;
            backdrop-filter: blur(6px);
        }
        a.btn-action-about:hover {
            background: rgba(255,255,255,0.26) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
        }

        /* Modal "Sobre la empresa" */
        .about-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(5,2,18,0.72);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 24px;
            animation: aboutFade .25s ease;
        }
        .about-backdrop.is-open { display: flex; }
        @keyframes aboutFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .about-modal {
            background: linear-gradient(160deg, #1b1530 0%, #0d001e 100%);
            color: #fff;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 640px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: aboutSlide .3s ease;
        }
        @keyframes aboutSlide {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .about-modal-header {
            padding: 24px 28px 8px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .about-modal-header img {
            max-height: 64px;
            margin-bottom: 10px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.4));
        }
        .about-modal-header h2 {
            margin: 0 0 4px;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .about-modal-header .tagline {
            font-size: 13px;
            opacity: 0.8;
            margin-bottom: 18px;
        }
        .about-modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }
        .about-modal-close:hover {
            background: rgba(255,255,255,0.18);
            transform: scale(1.05);
        }
        .about-modal-body {
            padding: 18px 28px 24px;
        }
        .about-info-list {
            list-style: none;
            padding: 0;
            margin: 0 0 18px;
            display: grid;
            gap: 10px;
        }
        .about-info-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
        }
        .about-info-list li .material-symbols-outlined {
            font-size: 22px;
            color: #38bdf8;
            margin-top: 1px;
        }
        .about-info-list li .wa-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
            margin-top: 1px;
        }
        .about-info-list li .wa-logo svg { display: block; }
        .about-info-list a {
            color: #38bdf8;
            text-decoration: none;
        }
        .about-info-list a:hover { text-decoration: underline; }

        /* Cuentas bancarias */
        .about-banks-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin: 14px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .about-banks-title .material-symbols-outlined { font-size: 18px; }
        .about-banks {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }
        .bank-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 14px;
            padding: 10px 12px;
            transition: background .15s ease, border-color .15s ease;
        }
        .bank-card:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.18);
        }
        .bank-logo {
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.15), 0 4px 10px rgba(0,0,0,0.25);
            padding: 6px;
            overflow: hidden;
        }
        .bank-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        .bank-logo.sinpe {
            background: #ffffff;
            padding: 4px;
        }
        .bank-meta {
            flex: 1;
            min-width: 0;
        }
        .bank-name {
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bank-currency {
            background: rgba(255,255,255,0.14);
            border-radius: 8px;
            padding: 1px 8px;
            font-size: 11px;
            font-weight: 600;
        }
        .bank-currency.colones {
            background: rgba(34, 197, 94, 0.25);
            color: #86efac;
        }
        .bank-currency.dolares {
            background: rgba(250, 204, 21, 0.25);
            color: #fde68a;
        }
        .bank-account {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 12.5px;
            opacity: 0.92;
            word-break: break-all;
            margin-top: 2px;
        }
        .bank-account-line {
            display: block;
            line-height: 1.45;
        }
        .bank-account-line + .bank-account-line {
            margin-top: 4px;
        }
        .bank-account-line .lbl {
            opacity: 0.6;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 4px;
        }
        .bank-copy {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .bank-copy:hover {
            background: rgba(255,255,255,0.20);
            transform: translateY(-1px);
        }
        .bank-copy.copied {
            background: rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.5);
        }
        .bank-copy .material-symbols-outlined {
            font-size: 14px;
        }

        .about-requirements-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin: 8px 0 8px;
        }
        .about-requirements {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 13.5px;
            line-height: 1.55;
            max-height: 220px;
            overflow-y: auto;
        }
        .about-requirements * { color: #fff; }
        .about-requirements ul,
        .about-requirements ol { padding-left: 22px; margin: 6px 0; }
        @media (max-width: 640px) {
            .about-modal-header { padding: 18px 18px 6px; }
            .about-modal-body { padding: 14px 18px 18px; }
            .about-modal-header h2 { font-size: 1.2rem; }
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
        .hero-card .contact-row a .wa-logo-sm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
        }
        .hero-card .contact-row a .wa-logo-sm svg { display: block; }
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
                <a href="#" class="btn-pill btn-action-about" data-open-about="1">
                    <span class="material-symbols-outlined" style="font-size:18px;">apartment</span>
                    Sobre la empresa
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
                    <a href="#" class="btn-secondary-cta btn-action-about" data-open-about="1">
                        <span class="material-symbols-outlined" style="font-size:20px;">apartment</span>
                        Sobre la empresa
                    </a>
                </div>

                <div class="contact-row">
                    <a href="tel:+50640700485">
                        <span class="material-symbols-outlined" style="font-size:16px;">call</span>
                        4070-0485
                    </a>
                    <a href="https://wa.me/50683670937" target="_blank" rel="noopener" title="Consulte Disponibilidad">
                        <span class="wa-logo-sm" aria-hidden="true">
                            <svg viewBox="0 0 32 32" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#25D366" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4z"/>
                                <path fill="#fff" d="M23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                            </svg>
                        </span>
                        8367-0937
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

    <?php
        $address = trim((string) ($companyInfo['address'] ?? ''));
        $phone = trim((string) ($companyInfo['phone'] ?? ''));
        $email = trim((string) ($companyInfo['email'] ?? ''));
        $requirementsHtml = (string) ($companyInfo['requirements'] ?? '');
        $hasRequirements = trim(strip_tags($requirementsHtml)) !== '';
        $phoneDigits = $phone !== '' ? preg_replace('/\D+/', '', $phone) : '';

        $cedulaJuridica = '';
        if (preg_match('/\b(\d-\d{3}-\d{6})\b/', $address, $mCed)) {
            $cedulaJuridica = $mCed[1];
        }

        $bankAccounts = is_array($companyInfo['bank_accounts'] ?? null) ? $companyInfo['bank_accounts'] : [];
        $razonSocial = trim((string) ($companyInfo['razon_social'] ?? CompanyConfig::getConfig(CompanyConfig::COMPANY_RAZON_SOCIAL, '')));
        $simpemovilRaw = trim((string) ($companyInfo['simemovil'] ?? ''));
        $simpemovilDigits = $simpemovilRaw !== '' ? preg_replace('/\D+/', '', $simpemovilRaw) : '';
        $simpemovilDisplay = $simpemovilDigits !== '' && strlen($simpemovilDigits) === 8
            ? substr($simpemovilDigits, 0, 4) . '-' . substr($simpemovilDigits, 4)
            : ($simpemovilRaw !== '' ? $simpemovilRaw : '');
    ?>
    <div class="about-backdrop" id="about-backdrop" role="dialog" aria-modal="true" aria-labelledby="about-title" aria-hidden="true">
        <div class="about-modal" id="about-modal">
            <button type="button" class="about-modal-close" id="about-close" aria-label="Cerrar">
                <span class="material-symbols-outlined" style="font-size:20px;">close</span>
            </button>
            <div class="about-modal-header">
                <?php if ($logoPath): ?>
                    <img src="<?= Html::encode($logoPath) ?>" alt="<?= Html::encode($companyName) ?>">
                <?php endif; ?>
                <h2 id="about-title"><?= Html::encode($companyName) ?></h2>
                <?php if ($razonSocial !== ''): ?>
                    <p class="razon-social" style="font-size:13px;opacity:0.78;margin:0 0 4px;"><?= Html::encode($razonSocial) ?></p>
                <?php endif; ?>
                <p class="tagline">Renta de vehículos en Costa Rica</p>
                <?php if ($cedulaJuridica !== ''): ?>
                    <div style="font-size:12.5px; opacity:0.85; margin-bottom:6px;">
                        <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">badge</span>
                        Cédula Jurídica: <strong><?= Html::encode($cedulaJuridica) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
            <div class="about-modal-body">
                <ul class="about-info-list">
                    <?php if ($address !== ''): ?>
                        <li>
                            <span class="material-symbols-outlined">location_on</span>
                            <span><?= Html::encode($address) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                        <li>
                            <span class="material-symbols-outlined">call</span>
                            <span>
                                <a href="tel:<?= Html::encode($phoneDigits ?: $phone) ?>"><?= Html::encode($phone) ?></a>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php
                        // WhatsApp: usar el número de SIMPEMOVIL si está disponible, si no caer al teléfono
                        $waDigits = $simpemovilDigits !== '' ? $simpemovilDigits : $phoneDigits;
                        $waDisplay = $simpemovilDisplay !== ''
                            ? $simpemovilDisplay
                            : ($phoneDigits !== '' && strlen($phoneDigits) === 8
                                ? substr($phoneDigits, 0, 4) . '-' . substr($phoneDigits, 4)
                                : ($phone ?: $waDigits));
                        // Costa Rica country code para wa.me
                        $waFullNumber = $waDigits !== '' && strpos($waDigits, '506') !== 0 && strlen($waDigits) === 8
                            ? '506' . $waDigits
                            : $waDigits;
                    ?>
                    <?php if ($waDigits !== ''): ?>
                        <li>
                            <span class="wa-logo" aria-hidden="true">
                                <svg viewBox="0 0 32 32" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#25D366" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4z"/>
                                    <path fill="#fff" d="M23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                                </svg>
                            </span>
                            <span>
                                <a href="https://wa.me/<?= Html::encode($waFullNumber) ?>" target="_blank" rel="noopener">
                                    <?= Html::encode($waDisplay) ?>
                                </a>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li>
                            <span class="material-symbols-outlined">mail</span>
                            <span><a href="mailto:<?= Html::encode($email) ?>"><?= Html::encode($email) ?></a></span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <span class="material-symbols-outlined">language</span>
                        <span><a href="https://factorentacar.com" target="_blank" rel="noopener">factorentacar.com</a></span>
                    </li>
                </ul>

                <?php if (!empty($bankAccounts) || $simpemovilDisplay !== ''): ?>
                    <div class="about-banks-title">
                        <span class="material-symbols-outlined">account_balance</span>
                        Cuentas Bancarias
                    </div>
                    <div class="about-banks">
                        <?php foreach ($bankAccounts as $acc):
                            $bankRaw = strtoupper(trim((string) ($acc['bank'] ?? '')));
                            $currency = trim((string) ($acc['currency'] ?? ''));
                            $accountNumber = trim((string) ($acc['account_number'] ?? ''));
                            $iban = trim((string) ($acc['iban'] ?? ''));
                            if ($iban === '' && !empty($acc['account'])) {
                                $iban = preg_replace('/^IBAN\s*:?\s*/i', '', trim((string) $acc['account']));
                                $iban = strtoupper(preg_replace('/\s+/', '', $iban));
                            }
                            $copyValue = $iban !== '' ? $iban : $accountNumber;

                            $logoClass = '';
                            $logoText = $bankRaw;
                            $logoFile = '';
                            $bankFullName = $bankRaw ?: 'Banco';
                            if (strpos($bankRaw, 'BCR') !== false) {
                                $logoClass = 'bcr';
                                $logoText = 'BCR';
                                $logoFile = 'bcr.png';
                                $bankFullName = 'Banco de Costa Rica';
                            } elseif ($bankRaw === 'BN' || strpos($bankRaw, 'NACIONAL') !== false) {
                                $logoClass = 'bn';
                                $logoText = 'BN';
                                $logoFile = 'bn.png';
                                $bankFullName = 'Banco Nacional';
                            } elseif (strpos($bankRaw, 'BAC') !== false || strpos($bankRaw, 'CREDOMATIC') !== false) {
                                $logoClass = 'bac';
                                $logoText = 'BAC';
                                $logoFile = 'bac.png';
                                $bankFullName = 'BAC Credomatic';
                            }

                            $currencyClass = 'colones';
                            if ($currency === '$' || stripos($currency, 'usd') !== false || stripos($currency, 'dolar') !== false) {
                                $currencyClass = 'dolares';
                            }
                        ?>
                            <div class="bank-card">
                                <div class="bank-logo <?= $logoClass ?>" aria-hidden="true">
                                    <?php if ($logoFile !== ''): ?>
                                        <img src="<?= Html::encode(Url::to('@web/img/banks/' . $logoFile)) ?>" alt="<?= Html::encode($bankFullName) ?>">
                                    <?php else: ?>
                                        <span style="color:#1b305b;font-weight:800;font-size:13px;"><?= Html::encode($logoText) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="bank-meta">
                                    <div class="bank-name">
                                        <?= Html::encode($bankFullName) ?>
                                        <?php if ($currency !== ''): ?>
                                            <span class="bank-currency <?= $currencyClass ?>"><?= Html::encode($currency) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="bank-account">
                                        <?php if ($accountNumber !== ''): ?>
                                            <span class="bank-account-line">
                                                <span class="lbl">Cta</span><?= Html::encode($accountNumber) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($iban !== ''): ?>
                                            <span class="bank-account-line">
                                                <span class="lbl">IBAN</span><?= Html::encode($iban) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($accountNumber === '' && $iban === '' && !empty($acc['account'])): ?>
                                            <span class="bank-account-line"><?= Html::encode($acc['account']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button type="button" class="bank-copy" data-copy="<?= Html::encode($copyValue) ?>">
                                    <span class="material-symbols-outlined">content_copy</span>
                                    Copiar
                                </button>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($simpemovilDisplay !== ''): ?>
                            <div class="bank-card">
                                <div class="bank-logo sinpe" aria-hidden="true">
                                    <img src="<?= Html::encode(Url::to('@web/img/banks/sinpe-movil.png')) ?>" alt="SINPE Móvil">
                                </div>
                                <div class="bank-meta">
                                    <div class="bank-name">SINPE Móvil</div>
                                    <div class="bank-account"><?= Html::encode($simpemovilDisplay) ?></div>
                                </div>
                                <button type="button" class="bank-copy" data-copy="<?= Html::encode($simpemovilDigits ?: $simpemovilDisplay) ?>">
                                    <span class="material-symbols-outlined">content_copy</span>
                                    Copiar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($hasRequirements): ?>
                    <div class="about-requirements-title">Requisitos para alquilar</div>
                    <div class="about-requirements"><?= $requirementsHtml ?></div>
                <?php endif; ?>
            </div>
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

        (function () {
            const backdrop = document.getElementById('about-backdrop');
            const closeBtn = document.getElementById('about-close');
            const triggers = document.querySelectorAll('[data-open-about]');
            if (!backdrop) return;

            function openAbout(e) {
                if (e) e.preventDefault();
                backdrop.classList.add('is-open');
                backdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function closeAbout() {
                backdrop.classList.remove('is-open');
                backdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            triggers.forEach(function (t) { t.addEventListener('click', openAbout); });
            if (closeBtn) closeBtn.addEventListener('click', closeAbout);
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeAbout();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && backdrop.classList.contains('is-open')) closeAbout();
            });

            backdrop.querySelectorAll('.bank-copy').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const value = btn.getAttribute('data-copy') || '';
                    if (!value) return;
                    const done = function () {
                        const original = btn.innerHTML;
                        btn.classList.add('copied');
                        btn.innerHTML = '<span class="material-symbols-outlined">check</span> Copiado';
                        setTimeout(function () {
                            btn.classList.remove('copied');
                            btn.innerHTML = original;
                        }, 1600);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value).then(done).catch(function () {
                            const ta = document.createElement('textarea');
                            ta.value = value;
                            document.body.appendChild(ta);
                            ta.select();
                            try { document.execCommand('copy'); done(); } catch (e) {}
                            document.body.removeChild(ta);
                        });
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = value;
                        document.body.appendChild(ta);
                        ta.select();
                        try { document.execCommand('copy'); done(); } catch (e) {}
                        document.body.removeChild(ta);
                    }
                });
            });
        })();
    </script>
</body>
</html>
