<?php
/** @var yii\web\View $this */
/** @var string $backgroundUrl */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\CompanyConfig;

$companyInfo = CompanyConfig::getCompanyInfo();
$logoPath        = $companyInfo['logo'] ?? null;
$companyName     = $companyInfo['name'] ?? 'Facto Rent a Car';
$razonSocial     = trim((string) ($companyInfo['razon_social'] ?? CompanyConfig::getConfig(CompanyConfig::COMPANY_RAZON_SOCIAL, '')));
$companyAddress  = trim((string) ($companyInfo['address'] ?? ''));
$companyEmail    = trim((string) ($companyInfo['email'] ?? ''));
$companyPhoneRaw = trim((string) ($companyInfo['phone'] ?? '4070-0485'));
$companyPhoneDigits = preg_replace('/\D+/', '', $companyPhoneRaw);
$companyPhoneE164 = $companyPhoneDigits !== '' && strpos($companyPhoneDigits, '506') !== 0 && strlen($companyPhoneDigits) === 8
    ? '+506' . $companyPhoneDigits
    : '+' . $companyPhoneDigits;

$simpemovilRaw = trim((string) ($companyInfo['simemovil'] ?? '83670937'));
$simpemovilDigits = preg_replace('/\D+/', '', $simpemovilRaw);
$simpemovilDisplay = strlen($simpemovilDigits) === 8
    ? substr($simpemovilDigits, 0, 4) . '-' . substr($simpemovilDigits, 4)
    : $simpemovilRaw;
$waFullNumber = strlen($simpemovilDigits) === 8 && strpos($simpemovilDigits, '506') !== 0
    ? '506' . $simpemovilDigits
    : $simpemovilDigits;
$waLink = 'https://wa.me/' . $waFullNumber;

$socialLinks = [
    'facebook'  => 'https://www.facebook.com/factorentacar',
    'instagram' => 'https://www.instagram.com/factorentacar',
    'whatsapp'  => $waLink,
];

$siteHomeUrl   = Url::home(true);
$canonicalUrl  = rtrim($siteHomeUrl, '/') . '/info';
$ogImageUrl    = $logoPath ? Url::to($logoPath, true) : '';

$pageTitle     = $companyName . ' — Sobre la empresa · Renta de Vehículos en Costa Rica';
$pageDesc      = ($razonSocial !== '' ? $razonSocial : $companyName) . '. Renta de vehículos en Costa Rica. Reserve por WhatsApp ' . $simpemovilDisplay . ' o llame al ' . $companyPhoneRaw . '. Dirección, cuentas bancarias y datos de contacto oficiales.';
$keywords      = 'renta de vehiculos Costa Rica, alquiler de carros, FACTO Rent a Car, factorentacar, BAC Credomatic, BCR, BN, SINPE Movil, San Ramon Alajuela';

$this->title = $pageTitle;

$cedulaJuridica = '';
if (preg_match('/\b(\d-\d{3}-\d{6})\b/', $companyAddress, $mCed)) {
    $cedulaJuridica = $mCed[1];
}
$bankAccounts = is_array($companyInfo['bank_accounts'] ?? null) ? $companyInfo['bank_accounts'] : [];
$requirementsHtml = (string) ($companyInfo['requirements'] ?? '');
$hasRequirements  = trim(strip_tags($requirementsHtml)) !== '';

$ogImageExt = '';
if ($ogImageUrl !== '') {
    $ogImageExt = strtolower(pathinfo(parse_url($ogImageUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
}
$ogImageMime = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
][$ogImageExt] ?? 'image/png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>

    <meta name="description" content="<?= Html::encode($pageDesc) ?>">
    <meta name="keywords"    content="<?= Html::encode($keywords) ?>">
    <meta name="author"      content="<?= Html::encode($razonSocial !== '' ? $razonSocial : $companyName) ?>">
    <meta name="robots"      content="index, follow">
    <meta name="theme-color" content="#0d001e">
    <meta name="geo.region"  content="CR-A">
    <meta name="geo.placename" content="San Ramón, Alajuela, Costa Rica">
    <link rel="canonical" href="<?= Html::encode($canonicalUrl) ?>">

    <?php if ($logoPath): ?>
        <link rel="icon" type="image/png" href="<?= Html::encode($logoPath) ?>">
        <link rel="apple-touch-icon" href="<?= Html::encode($logoPath) ?>">
    <?php endif; ?>

    <!-- ==================== Open Graph (Facebook, WhatsApp, LinkedIn, Telegram) ==================== -->
    <meta property="og:site_name"    content="<?= Html::encode($companyName) ?>">
    <meta property="og:title"        content="<?= Html::encode($companyName . ' — Sobre la empresa') ?>">
    <meta property="og:description"  content="<?= Html::encode($pageDesc) ?>">
    <meta property="og:type"         content="business.business">
    <meta property="og:url"          content="<?= Html::encode($canonicalUrl) ?>">
    <meta property="og:locale"       content="es_CR">
    <meta property="og:locale:alternate" content="es_ES">
    <meta property="og:locale:alternate" content="en_US">
    <?php if ($ogImageUrl !== ''): ?>
        <meta property="og:image"            content="<?= Html::encode($ogImageUrl) ?>">
        <meta property="og:image:secure_url" content="<?= Html::encode($ogImageUrl) ?>">
        <meta property="og:image:type"       content="<?= Html::encode($ogImageMime) ?>">
        <meta property="og:image:width"      content="600">
        <meta property="og:image:height"     content="600">
        <meta property="og:image:alt"        content="Logo de <?= Html::encode($companyName) ?> — Renta de Vehículos en Costa Rica">
    <?php endif; ?>

    <!-- ==================== Open Graph: Business contact ==================== -->
    <meta property="business:contact_data:street_address" content="<?= Html::encode($companyAddress !== '' ? $companyAddress : 'San Ramón, Alajuela') ?>">
    <meta property="business:contact_data:locality"       content="San Ramón">
    <meta property="business:contact_data:region"         content="Alajuela">
    <meta property="business:contact_data:postal_code"    content="20201">
    <meta property="business:contact_data:country_name"   content="Costa Rica">
    <meta property="business:contact_data:phone_number"   content="<?= Html::encode($companyPhoneE164) ?>">
    <?php if ($companyEmail !== ''): ?>
        <meta property="business:contact_data:email"      content="<?= Html::encode($companyEmail) ?>">
    <?php endif; ?>
    <meta property="business:contact_data:website"        content="<?= Html::encode(rtrim($siteHomeUrl, '/') . '/') ?>">
    <meta property="place:location:latitude"              content="10.0888">
    <meta property="place:location:longitude"             content="-84.4717">

    <!-- ==================== Twitter Cards ==================== -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= Html::encode($companyName . ' — Sobre la empresa') ?>">
    <meta name="twitter:description" content="<?= Html::encode('WhatsApp ' . $simpemovilDisplay . ' · Tel ' . $companyPhoneRaw . ' · Renta de vehículos en Costa Rica') ?>">
    <meta name="twitter:url"         content="<?= Html::encode($canonicalUrl) ?>">
    <?php if ($ogImageUrl !== ''): ?>
        <meta name="twitter:image"     content="<?= Html::encode($ogImageUrl) ?>">
        <meta name="twitter:image:alt" content="Logo de <?= Html::encode($companyName) ?>">
    <?php endif; ?>
    <!-- ==================== Fin Open Graph ==================== -->

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
            margin: 0; padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body { position: relative; background-color: var(--brand-dark); color: #fff; }
        .bg-image {
            position: fixed; inset: 0;
            background-image: url('<?= Html::encode($backgroundUrl) ?>');
            background-size: cover; background-position: center; background-repeat: no-repeat;
            filter: brightness(0.4) saturate(1.05);
            z-index: 0; opacity: 0;
            transition: opacity .8s ease;
        }
        .bg-image.loaded { opacity: 1; }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(135deg, rgba(13,0,30,0.65) 0%, rgba(34,72,122,0.45) 50%, rgba(13,0,30,0.75) 100%);
            z-index: 1;
        }
        .page { position: relative; z-index: 2; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 24px; gap: 12px;
        }
        .topbar .brand {
            display: inline-flex; align-items: center; gap: 10px;
            color: #fff; text-decoration: none; font-weight: 700; letter-spacing: 0.3px;
        }
        .topbar .brand img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #fff; }
        .btn-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 999px; font-weight: 600; font-size: 13.5px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff; text-decoration: none;
            transition: background .15s ease, border-color .15s ease, transform .15s ease;
        }
        .btn-pill:hover { background: rgba(255,255,255,0.18); transform: translateY(-1px); color: #fff; }

        .container-info {
            max-width: 760px; width: 100%;
            margin: 24px auto 36px;
            padding: 0 16px;
        }
        .info-card {
            background: linear-gradient(160deg, rgba(27,21,48,0.92) 0%, rgba(13,0,30,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px;
            padding: 28px 26px 24px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        }
        .info-header { text-align: center; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.10); margin-bottom: 18px; }
        .info-header img.company-logo {
            width: 92px; height: 92px; object-fit: contain;
            background: #fff; border-radius: 18px;
            padding: 8px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.4);
            margin-bottom: 12px;
        }
        .info-header h1 { font-size: 1.6rem; margin: 0 0 4px; font-weight: 800; letter-spacing: 0.3px; }
        .info-header .razon-social { font-size: 13.5px; opacity: 0.82; margin: 0 0 6px; }
        .info-header .tagline { font-size: 14px; opacity: 0.88; margin: 0 0 8px; }
        .info-header .cedula-juridica {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12.5px; opacity: 0.88;
            background: rgba(255,255,255,0.08);
            padding: 4px 10px; border-radius: 999px;
        }

        .section-title {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; text-transform: uppercase; letter-spacing: 1px;
            opacity: 0.85; margin: 16px 0 10px;
        }
        .section-title .material-symbols-outlined { font-size: 18px; }

        .info-list { list-style: none; padding: 0; margin: 0 0 6px; }
        .info-list li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            transition: background .15s ease;
        }
        .info-list li:hover { background: rgba(255,255,255,0.05); }
        .info-list li .material-symbols-outlined { font-size: 20px; opacity: 0.85; margin-top: 2px; }
        .info-list li .wa-logo { display: inline-flex; width: 22px; height: 22px; align-items: center; justify-content: center; margin-top: 1px; }
        .info-list li a { color: #fff; text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,0.4); }
        .info-list li a:hover { border-bottom-color: #fff; }

        /* Bancos */
        .banks-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .bank-card {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 14px;
            padding: 10px 12px;
            transition: background .15s ease;
        }
        .bank-card:hover { background: rgba(255,255,255,0.10); }
        .bank-logo {
            width: 56px; height: 56px; flex: 0 0 56px;
            border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background-color: #ffffff !important;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.25);
            padding: 6px; overflow: hidden;
        }
        .bank-logo img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; background: #fff; }
        .bank-logo.sinpe { padding: 4px; }
        .bank-logo.bac   { padding: 2px; }
        .bank-logo.bac img { transform: scale(1.25); transform-origin: center; }
        .bank-meta { flex: 1; min-width: 0; }
        .bank-name { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .bank-currency { background: rgba(255,255,255,0.14); border-radius: 8px; padding: 1px 8px; font-size: 11px; font-weight: 600; }
        .bank-currency.colones { background: rgba(34,197,94,0.25); color: #86efac; }
        .bank-currency.dolares { background: rgba(250,204,21,0.25); color: #fde68a; }
        .bank-account { font-family: 'Consolas','Monaco',monospace; font-size: 12.5px; opacity: 0.92; word-break: break-all; margin-top: 2px; }
        .bank-account-line { display: block; line-height: 1.45; }
        .bank-account-line + .bank-account-line { margin-top: 4px; }
        .bank-account-line .lbl { opacity: 0.6; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 4px; }
        .bank-copy {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff; border-radius: 10px; padding: 6px 10px; font-size: 11px;
            font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
            transition: background .15s ease, transform .15s ease;
        }
        .bank-copy:hover { background: rgba(255,255,255,0.20); transform: translateY(-1px); }
        .bank-copy.copied { background: rgba(34,197,94,0.3); border-color: rgba(34,197,94,0.5); }
        .bank-copy .material-symbols-outlined { font-size: 14px; }

        /* Redes */
        .social-row { display: flex; gap: 14px; justify-content: center; align-items: center; margin: 8px 0 4px; flex-wrap: wrap; }
        .social-row a.social-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 46px; height: 46px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff; text-decoration: none;
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .social-row a.social-icon:hover { transform: translateY(-3px) scale(1.06); box-shadow: 0 10px 22px rgba(0,0,0,0.35); }
        .social-row a.social-icon svg { width: 22px; height: 22px; display: block; }
        .social-row a.social-icon.facebook:hover  { background: #1877F2; border-color: #1877F2; }
        .social-row a.social-icon.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-color: rgba(255,255,255,0.4);
        }
        .social-row a.social-icon.whatsapp:hover  { background: #25D366; border-color: #25D366; }

        /* Requisitos */
        .requirements-box {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 13.5px; line-height: 1.55;
        }
        .requirements-box ul, .requirements-box ol { margin: 0; padding-left: 20px; }
        .requirements-box li { margin-bottom: 4px; }

        /* CTAs inferiores */
        .info-actions {
            display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;
            margin-top: 20px; padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.10);
        }
        .info-actions a {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 18px; border-radius: 999px;
            font-weight: 600; font-size: 14px;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .info-actions a:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(0,0,0,0.35); }
        .btn-cta-rent { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; }
        .btn-cta-wa { background: #25D366; color: #fff; }
        .btn-cta-home { background: rgba(255,255,255,0.10); color: #fff; border: 1px solid rgba(255,255,255,0.22); }

        .footer {
            text-align: center; padding: 14px 12px;
            font-size: 12px; opacity: 0.7;
        }
        .footer .divider { margin: 0 8px; opacity: 0.5; }

        @media (max-width: 640px) {
            .topbar { display: none; }
            .info-card { padding: 22px 18px 18px; border-radius: 18px; }
            .info-header h1 { font-size: 1.35rem; }
        }
    </style>
</head>
<body>
    <div class="bg-image" id="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="page">
        <header class="topbar" role="banner">
            <a href="<?= Url::home() ?>" class="brand" rel="home">
                <?php if ($logoPath): ?>
                    <img src="<?= Html::encode($logoPath) ?>" alt="<?= Html::encode($companyName) ?>">
                <?php endif; ?>
                <span><?= Html::encode($companyName) ?></span>
            </a>
            <a href="<?= Url::home() ?>" class="btn-pill">
                <span class="material-symbols-outlined" style="font-size:18px;">home</span>
                Inicio
            </a>
        </header>

        <main class="container-info" role="main">
            <article class="info-card" itemscope itemtype="https://schema.org/AutoRental">
                <meta itemprop="name" content="<?= Html::encode($companyName) ?>">
                <meta itemprop="url" content="<?= Html::encode(rtrim($siteHomeUrl, '/') . '/') ?>">
                <?php if ($ogImageUrl !== ''): ?>
                    <meta itemprop="image" content="<?= Html::encode($ogImageUrl) ?>">
                <?php endif; ?>

                <header class="info-header">
                    <?php if ($logoPath): ?>
                        <img class="company-logo" src="<?= Html::encode($logoPath) ?>" alt="Logo de <?= Html::encode($companyName) ?>" itemprop="logo">
                    <?php endif; ?>
                    <h1 itemprop="legalName"><?= Html::encode($companyName) ?></h1>
                    <?php if ($razonSocial !== ''): ?>
                        <p class="razon-social"><?= Html::encode($razonSocial) ?></p>
                    <?php endif; ?>
                    <p class="tagline" itemprop="slogan">Renta de vehículos en Costa Rica</p>
                    <?php if ($cedulaJuridica !== ''): ?>
                        <div class="cedula-juridica">
                            <span class="material-symbols-outlined" style="font-size:14px;">badge</span>
                            Cédula Jurídica: <strong><?= Html::encode($cedulaJuridica) ?></strong>
                        </div>
                    <?php endif; ?>
                </header>

                <h2 class="section-title">
                    <span class="material-symbols-outlined">contact_page</span>
                    Datos de contacto
                </h2>
                <ul class="info-list">
                    <?php if ($companyAddress !== ''): ?>
                        <li>
                            <span class="material-symbols-outlined">location_on</span>
                            <span itemprop="address"><?= Html::encode($companyAddress) ?></span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <span class="material-symbols-outlined">call</span>
                        <span><a href="tel:<?= Html::encode($companyPhoneE164) ?>" itemprop="telephone"><?= Html::encode($companyPhoneRaw) ?></a></span>
                    </li>
                    <li>
                        <span class="wa-logo" aria-hidden="true">
                            <svg viewBox="0 0 32 32" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#25D366" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4z"/>
                                <path fill="#fff" d="M23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                            </svg>
                        </span>
                        <span><a href="<?= Html::encode($waLink) ?>" target="_blank" rel="noopener"><?= Html::encode($simpemovilDisplay) ?></a></span>
                    </li>
                    <?php if ($companyEmail !== ''): ?>
                        <li>
                            <span class="material-symbols-outlined">mail</span>
                            <span><a href="mailto:<?= Html::encode($companyEmail) ?>" itemprop="email"><?= Html::encode($companyEmail) ?></a></span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <span class="material-symbols-outlined">language</span>
                        <span><a href="<?= Html::encode(rtrim($siteHomeUrl, '/') . '/') ?>" itemprop="url">factorentacar.com</a></span>
                    </li>
                </ul>

                <h2 class="section-title">
                    <span class="material-symbols-outlined">share</span>
                    Redes Sociales
                </h2>
                <nav class="social-row" aria-label="Síguenos en redes sociales">
                    <a href="<?= Html::encode($socialLinks['facebook']) ?>" target="_blank" rel="noopener" class="social-icon facebook" title="Facebook" aria-label="Facebook" itemprop="sameAs">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.13 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.51 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.77l-.44 2.89h-2.33V22c4.78-.75 8.44-4.88 8.44-9.94z"/>
                        </svg>
                    </a>
                    <a href="<?= Html::encode($socialLinks['instagram']) ?>" target="_blank" rel="noopener" class="social-icon instagram" title="Instagram" aria-label="Instagram" itemprop="sameAs">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.77.13 4.9.34 4.14.63c-.79.31-1.46.72-2.13 1.39C1.34 2.69.93 3.36.63 4.14.34 4.9.13 5.77.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.28.27 2.15.56 2.91.31.79.72 1.46 1.39 2.13.67.67 1.34 1.08 2.13 1.39.76.29 1.63.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.28-.06 2.15-.27 2.91-.56.79-.31 1.46-.72 2.13-1.39.67-.67 1.08-1.34 1.39-2.13.29-.76.5-1.63.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.28-.27-2.15-.56-2.91-.31-.79-.72-1.46-1.39-2.13C21.31 1.34 20.64.93 19.86.63 19.1.34 18.23.13 16.95.07 15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 12 18.16 6.16 6.16 0 0 0 12 5.84zm0 10.16A4 4 0 1 1 12 8a4 4 0 0 1 0 8zm6.41-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>
                        </svg>
                    </a>
                    <a href="<?= Html::encode($socialLinks['whatsapp']) ?>" target="_blank" rel="noopener" class="social-icon whatsapp" title="WhatsApp" aria-label="WhatsApp" itemprop="sameAs">
                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4zM23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                        </svg>
                    </a>
                </nav>

                <?php if (!empty($bankAccounts) || $simpemovilDisplay !== ''): ?>
                    <h2 class="section-title">
                        <span class="material-symbols-outlined">account_balance</span>
                        Cuentas Bancarias
                    </h2>
                    <div class="banks-grid">
                        <?php foreach ($bankAccounts as $acc):
                            $bankRaw       = strtoupper(trim((string) ($acc['bank'] ?? '')));
                            $currency      = trim((string) ($acc['currency'] ?? ''));
                            $accountNumber = trim((string) ($acc['account_number'] ?? ''));
                            $iban          = trim((string) ($acc['iban'] ?? ''));
                            if ($iban === '' && !empty($acc['account'])) {
                                $iban = preg_replace('/^IBAN\s*:?\s*/i', '', trim((string) $acc['account']));
                                $iban = strtoupper(preg_replace('/\s+/', '', $iban));
                            }
                            $copyValue = $iban !== '' ? $iban : $accountNumber;

                            $logoClass    = '';
                            $logoFile     = '';
                            $bankFullName = $bankRaw ?: 'Banco';
                            if (strpos($bankRaw, 'BCR') !== false) {
                                $logoClass = 'bcr'; $logoFile = 'bcr.png'; $bankFullName = 'Banco de Costa Rica';
                            } elseif ($bankRaw === 'BN' || strpos($bankRaw, 'NACIONAL') !== false) {
                                $logoClass = 'bn'; $logoFile = 'bn.png'; $bankFullName = 'Banco Nacional';
                            } elseif (strpos($bankRaw, 'BAC') !== false || strpos($bankRaw, 'CREDOMATIC') !== false) {
                                $logoClass = 'bac'; $logoFile = 'bac.png'; $bankFullName = 'BAC Credomatic';
                            }

                            $currencyClass = 'colones';
                            if ($currency === '$' || stripos($currency, 'usd') !== false || stripos($currency, 'dolar') !== false) {
                                $currencyClass = 'dolares';
                            }

                            $logoUrl = '';
                            if ($logoFile !== '') {
                                $logoUrl = Url::to('@web/img/banks/' . $logoFile);
                                $logoAbs = Yii::getAlias('@webroot/img/banks/' . $logoFile);
                                if (is_file($logoAbs)) { $logoUrl .= '?v=' . filemtime($logoAbs); }
                            }
                        ?>
                            <div class="bank-card">
                                <div class="bank-logo <?= $logoClass ?>" aria-hidden="true">
                                    <?php if ($logoUrl !== ''): ?>
                                        <img src="<?= Html::encode($logoUrl) ?>" alt="<?= Html::encode($bankFullName) ?>">
                                    <?php else: ?>
                                        <span style="color:#1b305b;font-weight:800;font-size:13px;"><?= Html::encode($bankFullName) ?></span>
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
                                            <span class="bank-account-line"><span class="lbl">Cta</span><?= Html::encode($accountNumber) ?></span>
                                        <?php endif; ?>
                                        <?php if ($iban !== ''): ?>
                                            <span class="bank-account-line"><span class="lbl">IBAN</span><?= Html::encode($iban) ?></span>
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

                        <?php if ($simpemovilDisplay !== ''):
                            $sinpeFile = 'sinpe-movil.png';
                            $sinpeUrl  = Url::to('@web/img/banks/' . $sinpeFile);
                            $sinpeAbs  = Yii::getAlias('@webroot/img/banks/' . $sinpeFile);
                            if (is_file($sinpeAbs)) { $sinpeUrl .= '?v=' . filemtime($sinpeAbs); }
                        ?>
                            <div class="bank-card">
                                <div class="bank-logo sinpe" aria-hidden="true">
                                    <img src="<?= Html::encode($sinpeUrl) ?>" alt="SINPE Móvil">
                                </div>
                                <div class="bank-meta">
                                    <div class="bank-name">
                                        SINPE Móvil
                                        <span class="bank-currency colones">₡</span>
                                    </div>
                                    <div class="bank-account">
                                        <span class="bank-account-line"><span class="lbl">Tel</span><?= Html::encode($simpemovilDisplay) ?></span>
                                    </div>
                                </div>
                                <button type="button" class="bank-copy" data-copy="<?= Html::encode($simpemovilDigits) ?>">
                                    <span class="material-symbols-outlined">content_copy</span>
                                    Copiar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($hasRequirements): ?>
                    <h2 class="section-title">
                        <span class="material-symbols-outlined">checklist</span>
                        Requisitos para alquilar
                    </h2>
                    <div class="requirements-box">
                        <?= $requirementsHtml ?>
                    </div>
                <?php endif; ?>

                <div class="info-actions">
                    <a href="<?= Url::to(['/realizar-alquiler']) ?>" class="btn-cta-rent">
                        <span class="material-symbols-outlined" style="font-size:18px;">directions_car</span>
                        Realizar alquiler
                    </a>
                    <a href="<?= Html::encode($waLink) ?>" target="_blank" rel="noopener" class="btn-cta-wa">
                        <span class="material-symbols-outlined" style="font-size:18px;">chat</span>
                        WhatsApp
                    </a>
                    <a href="<?= Url::home() ?>" class="btn-cta-home">
                        <span class="material-symbols-outlined" style="font-size:18px;">home</span>
                        Volver al inicio
                    </a>
                </div>
            </article>
        </main>

        <footer class="footer" role="contentinfo">
            &copy; <?= date('Y') ?> <?= Html::encode($companyName) ?>. Todos los derechos reservados.
            <span class="divider">·</span>
            Desarrollado por Ing. Ronald Rojas Castro
        </footer>
    </div>

    <?php
        $jsonLd = [
            '@context'  => 'https://schema.org',
            '@type'     => 'AutoRental',
            'name'      => $companyName,
            'legalName' => $razonSocial !== '' ? $razonSocial : $companyName,
            'url'       => rtrim($siteHomeUrl, '/') . '/',
            'description' => $pageDesc,
            'telephone' => $companyPhoneE164,
            'priceRange' => '$$',
            'areaServed' => ['@type' => 'Country', 'name' => 'Costa Rica'],
        ];
        if ($ogImageUrl !== '') {
            $jsonLd['image'] = $ogImageUrl;
            $jsonLd['logo']  = $ogImageUrl;
        }
        if ($companyEmail !== '')   { $jsonLd['email']   = $companyEmail; }
        if ($companyAddress !== '') {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress'   => $companyAddress,
                'addressLocality' => 'San Ramón',
                'addressRegion'   => 'Alajuela',
                'addressCountry'  => 'CR',
            ];
        }
        $jsonLd['contactPoint'] = [
            [
                '@type'             => 'ContactPoint',
                'telephone'         => $companyPhoneE164,
                'contactType'       => 'customer service',
                'availableLanguage' => ['es', 'en'],
                'areaServed'        => 'CR',
            ],
            [
                '@type'             => 'ContactPoint',
                'telephone'         => '+' . $waFullNumber,
                'contactType'       => 'reservations',
                'contactOption'     => 'WhatsApp',
                'availableLanguage' => ['es', 'en'],
                'areaServed'        => 'CR',
            ],
        ];
        $jsonLd['sameAs'] = array_values(array_filter([
            $socialLinks['facebook']  ?? null,
            $socialLinks['instagram'] ?? null,
            $waLink,
            rtrim($siteHomeUrl, '/') . '/',
        ]));
    ?>
    <script type="application/ld+json">
    <?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <script>
        (function () {
            var bg = document.getElementById('bg-image');
            if (!bg) return;
            var url = <?= json_encode($backgroundUrl) ?>;
            var img = new Image();
            img.onload  = function () { bg.classList.add('loaded'); };
            img.onerror = function () { bg.classList.add('loaded'); };
            img.src = url;
        })();

        document.querySelectorAll('.bank-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var value = btn.getAttribute('data-copy') || '';
                if (!value) return;
                var done = function () {
                    var original = btn.innerHTML;
                    btn.classList.add('copied');
                    btn.innerHTML = '<span class="material-symbols-outlined">check</span> Copiado';
                    setTimeout(function () {
                        btn.classList.remove('copied');
                        btn.innerHTML = original;
                    }, 1600);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(value).then(done).catch(function () {
                        var ta = document.createElement('textarea');
                        ta.value = value; document.body.appendChild(ta); ta.select();
                        try { document.execCommand('copy'); done(); } catch (e) {}
                        document.body.removeChild(ta);
                    });
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = value; document.body.appendChild(ta); ta.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(ta);
                }
            });
        });
    </script>
</body>
</html>
