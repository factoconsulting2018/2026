<?php
/** @var yii\web\View $this */
/** @var string $backgroundUrl */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\CompanyConfig;

$companyInfo = CompanyConfig::getCompanyInfo();
$logoPath = $companyInfo['logo'] ?? null;
$companyName = $companyInfo['name'] ?? 'Facto Rent a Car';
$razonSocial = trim((string) ($companyInfo['razon_social'] ?? CompanyConfig::getConfig(CompanyConfig::COMPANY_RAZON_SOCIAL, '')));
$companyAddress = trim((string) ($companyInfo['address'] ?? ''));
$companyEmail = trim((string) ($companyInfo['email'] ?? ''));
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

$siteHomeUrl = Url::home(true);
$canonicalUrl = rtrim($siteHomeUrl, '/') . '/';
$ogImageUrl = $logoPath ? Url::to($logoPath, true) : '';

$seoTitle = $companyName . ' — Renta de Vehículos en Costa Rica · Reserve por WhatsApp ' . $simpemovilDisplay;
$seoDescription = $companyName . ': renta de vehículos en Costa Rica. Sedanes, SUV, Pickup 4x4 y más. Reserve por WhatsApp ' . $simpemovilDisplay . ' o llame al ' . $companyPhoneRaw . '. Atención personalizada, disponibilidad inmediata y precios competitivos.';
$seoKeywords = 'renta de vehiculos Costa Rica, alquiler de carros Costa Rica, rent a car San Ramon, alquiler SUV, alquiler pickup 4x4, alquiler sedan, FACTO Rent a Car, factorentacar';

$this->title = $seoTitle;

$year = date('Y');
$today = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>

    <meta name="description" content="<?= Html::encode($seoDescription) ?>">
    <meta name="keywords" content="<?= Html::encode($seoKeywords) ?>">
    <meta name="author" content="<?= Html::encode($razonSocial !== '' ? $razonSocial : $companyName) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0d001e">
    <meta name="geo.region" content="CR-A">
    <meta name="geo.placename" content="San Ramón, Alajuela, Costa Rica">
    <link rel="canonical" href="<?= Html::encode($canonicalUrl) ?>">

    <?php if ($logoPath): ?>
        <link rel="icon" type="image/png" href="<?= Html::encode($logoPath) ?>">
        <link rel="apple-touch-icon" href="<?= Html::encode($logoPath) ?>">
    <?php endif; ?>

    <?php
        $ogTitle = $companyName . ' — Renta de Vehículos en Costa Rica';
        $ogDescription = 'Reserve su vehículo por WhatsApp ' . $simpemovilDisplay . ' o al teléfono ' . $companyPhoneRaw . '. Sedanes, SUV, Pickup 4x4, Camiones y Busetas. Atención personalizada, disponibilidad inmediata y precios competitivos en todo Costa Rica.';
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
    <!-- ==================== Open Graph (Facebook, WhatsApp, LinkedIn, Telegram) ==================== -->
    <meta property="og:site_name"    content="<?= Html::encode($companyName) ?>">
    <meta property="og:title"        content="<?= Html::encode($ogTitle) ?>">
    <meta property="og:description"  content="<?= Html::encode($ogDescription) ?>">
    <meta property="og:type"         content="business.business">
    <meta property="og:url"          content="<?= Html::encode($canonicalUrl) ?>">
    <meta property="og:locale"       content="es_CR">
    <meta property="og:locale:alternate" content="es_ES">
    <meta property="og:locale:alternate" content="en_US">
    <meta property="og:determiner"   content="">
    <?php if ($ogImageUrl !== ''): ?>
        <meta property="og:image"            content="<?= Html::encode($ogImageUrl) ?>">
        <meta property="og:image:secure_url" content="<?= Html::encode($ogImageUrl) ?>">
        <meta property="og:image:type"       content="<?= Html::encode($ogImageMime) ?>">
        <meta property="og:image:width"      content="600">
        <meta property="og:image:height"     content="600">
        <meta property="og:image:alt"        content="Logo de <?= Html::encode($companyName) ?> — Renta de Vehículos en Costa Rica">
    <?php endif; ?>

    <!-- ==================== Open Graph: Business contact (Facebook Business / WhatsApp) ==================== -->
    <meta property="business:contact_data:street_address"   content="<?= Html::encode($companyAddress !== '' ? $companyAddress : 'San Ramón, Alajuela') ?>">
    <meta property="business:contact_data:locality"         content="San Ramón">
    <meta property="business:contact_data:region"           content="Alajuela">
    <meta property="business:contact_data:postal_code"      content="20201">
    <meta property="business:contact_data:country_name"     content="Costa Rica">
    <meta property="business:contact_data:phone_number"     content="<?= Html::encode($companyPhoneE164) ?>">
    <?php if ($companyEmail !== ''): ?>
        <meta property="business:contact_data:email"        content="<?= Html::encode($companyEmail) ?>">
    <?php endif; ?>
    <meta property="business:contact_data:website"          content="<?= Html::encode($canonicalUrl) ?>">
    <meta property="place:location:latitude"                content="10.0888">
    <meta property="place:location:longitude"               content="-84.4717">

    <!-- ==================== Twitter Cards (X / Twitter) ==================== -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= Html::encode($ogTitle) ?>">
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
            margin: 0;
            line-height: 1.25;
            color: inherit;
            letter-spacing: 0;
        }
        /* Fallback por si Bootstrap no aplica (visually-hidden ya existe en Bootstrap 5) */
        .visually-hidden {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
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
            background-color: #ffffff !important;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.25);
            padding: 6px;
            overflow: hidden;
        }
        .bank-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
            background-color: #ffffff;
        }
        .bank-logo.sinpe,
        .bank-logo.bcr,
        .bank-logo.bn,
        .bank-logo.bac {
            background-color: #ffffff !important;
        }
        .bank-logo.sinpe { padding: 4px; }
        /* Ajuste específico para el logo BAC: la imagen tiene espacio en blanco
           alrededor del logo, así que reducimos padding y ampliamos para que se vea mejor. */
        .bank-logo.bac { padding: 2px; }
        .bank-logo.bac img {
            transform: scale(1.25);
            transform-origin: center center;
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

        /* Redes sociales */
        .social-row {
            display: flex;
            gap: 14px;
            justify-content: center;
            align-items: center;
            margin-top: 18px;
            flex-wrap: wrap;
        }
        .social-row a.social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            position: relative;
        }
        .social-row a.social-icon:hover {
            transform: translateY(-3px) scale(1.06);
            box-shadow: 0 10px 22px rgba(0,0,0,0.35);
        }
        .social-row a.social-icon svg {
            width: 22px;
            height: 22px;
            display: block;
        }
        .social-row a.social-icon.facebook:hover  { background: #1877F2; border-color: #1877F2; }
        .social-row a.social-icon.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-color: rgba(255,255,255,0.4);
        }
        .social-row a.social-icon.whatsapp:hover  { background: #25D366; border-color: #25D366; }

        /* Versión compacta para el modal "Sobre la empresa" */
        .about-social-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 12px 0 4px;
            flex-wrap: wrap;
        }
        .about-social-row a.social-icon {
            width: 38px;
            height: 38px;
        }
        .about-social-row a.social-icon svg {
            width: 19px;
            height: 19px;
        }

        /* Modal "Imagen Gráfica" - descargas del logo en PNG/PDF */
        .brand-backdrop {
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
        .brand-backdrop.is-open { display: flex; }
        .brand-modal {
            background: linear-gradient(160deg, #1b1530 0%, #0d001e 100%);
            color: #fff;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 460px;
            position: relative;
            padding: 22px 24px 24px;
            animation: aboutSlide .3s ease;
        }
        .brand-modal-close {
            position: absolute;
            top: 10px; right: 10px;
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.08);
            color: #fff; cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .15s ease;
        }
        .brand-modal-close:hover { background: rgba(255,255,255,0.18); }
        .brand-modal .bw-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0.95;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            padding-bottom: 12px;
            margin: 0 0 16px;
        }
        .brand-modal .bw-title .material-symbols-outlined { font-size: 18px; }
        .brand-modal .bw-subtitle {
            text-align: center;
            font-size: 12.5px;
            opacity: 0.78;
            margin: -6px 0 14px;
        }
        .brand-modal .bw-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: stretch;
        }
        .brand-modal a.bw-btn {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1 1 0;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            color: #fff;
            text-decoration: none;
            padding: 16px 12px;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s ease, transform .15s ease, border-color .15s ease;
        }
        .brand-modal a.bw-btn:hover {
            background: rgba(255,255,255,0.18);
            border-color: rgba(255,255,255,0.32);
            transform: translateY(-2px);
        }
        .brand-modal a.bw-btn .bw-ico-wrap {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 6px 14px rgba(0,0,0,0.35);
        }
        .brand-modal a.bw-btn .bw-ico-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .brand-modal a.bw-btn .bw-fmt {
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        .brand-modal a.bw-btn .bw-dl {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .brand-modal a.bw-btn .bw-dl .material-symbols-outlined { font-size: 14px; }

        /* Link en el footer para abrir el modal */
        .footer .brand-link {
            color: #fff;
            text-decoration: none;
            border-bottom: 1px dashed rgba(255,255,255,0.45);
            cursor: pointer;
            font-weight: 600;
            transition: color .15s ease, border-color .15s ease;
        }
        .footer .brand-link:hover {
            color: #fff;
            border-bottom-color: #fff;
        }

        @media (max-width: 640px) {
            .brand-modal a.bw-btn .bw-ico-wrap { width: 60px; height: 60px; }
            .brand-modal a.bw-btn { padding: 12px 8px; }
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
        <header class="topbar" role="banner">
            <a href="<?= Url::to(['/site/index']) ?>" class="brand" rel="home" aria-label="<?= Html::encode($companyName) ?> — Inicio">
                <?php if ($logoPath): ?>
                    <img src="<?= Html::encode($logoPath) ?>" alt="<?= Html::encode($companyName) ?>">
                <?php endif; ?>
                <span><?= Html::encode($companyName) ?></span>
            </a>
            <nav class="nav-actions" aria-label="Navegación principal">
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
                <a href="<?= Url::to(['/site/login']) ?>" class="btn-pill" title="Iniciar sesión administrativa" style="padding:7px 12px;" aria-label="Iniciar sesión administrativa">
                    <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                </a>
            </nav>
        </header>

        <main class="hero" role="main">
            <article class="hero-card" itemscope itemtype="https://schema.org/AutoRental">
                <meta itemprop="name" content="<?= Html::encode($companyName) ?>">
                <meta itemprop="url" content="<?= Html::encode($canonicalUrl) ?>">
                <?php if ($ogImageUrl !== ''): ?>
                    <meta itemprop="image" content="<?= Html::encode($ogImageUrl) ?>">
                <?php endif; ?>

                <?php if ($logoPath): ?>
                    <div class="logo-wrap">
                        <img src="<?= Html::encode($logoPath) ?>" alt="Logo de <?= Html::encode($companyName) ?>" itemprop="logo">
                    </div>
                <?php endif; ?>

                <div class="date-pill">
                    <span class="material-symbols-outlined" style="font-size:16px;">calendar_today</span>
                    <?= Html::encode($today) ?>
                </div>

                <h1 itemprop="legalName"><?= Html::encode($companyName) ?></h1>
                <p class="tagline" itemprop="slogan">Renta de vehículos en Costa Rica · Tu próxima aventura empieza aquí</p>

                <section class="features" aria-labelledby="srv-heading">
                    <h2 id="srv-heading" class="visually-hidden">Por qué elegirnos</h2>
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">verified</span></div>
                        <h3 class="label">Disponibilidad inmediata</h3>
                    </div>
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">support_agent</span></div>
                        <h3 class="label">Atención personalizada</h3>
                    </div>
                    <div class="feature">
                        <div class="icon"><span class="material-symbols-outlined" style="font-size:28px;">paid</span></div>
                        <h3 class="label">Precios competitivos</h3>
                    </div>
                </section>

                <nav class="cta" aria-label="Acciones rápidas">
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
                </nav>

                <address class="contact-row" aria-label="Datos de contacto">
                    <a href="tel:<?= Html::encode($companyPhoneE164) ?>" itemprop="telephone">
                        <span class="material-symbols-outlined" style="font-size:16px;">call</span>
                        <?= Html::encode($companyPhoneRaw) ?>
                    </a>
                    <a href="<?= Html::encode($waLink) ?>" target="_blank" rel="noopener" title="Consulte Disponibilidad por WhatsApp">
                        <span class="wa-logo-sm" aria-hidden="true">
                            <svg viewBox="0 0 32 32" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#25D366" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4z"/>
                                <path fill="#fff" d="M23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                            </svg>
                        </span>
                        <?= Html::encode($simpemovilDisplay) ?>
                    </a>
                    <a href="https://www.factorentacar.com" target="_blank" rel="noopener" itemprop="url">
                        <span class="material-symbols-outlined" style="font-size:16px;">language</span>
                        factorentacar.com
                    </a>
                </address>

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
            </article>

            <section class="visually-hidden" aria-label="Información adicional para buscadores">
                <h2>Renta de vehículos en Costa Rica con <?= Html::encode($companyName) ?></h2>
                <p>
                    <?= Html::encode($razonSocial !== '' ? $razonSocial : $companyName) ?> es una empresa costarricense
                    dedicada al alquiler y renta de vehículos en Costa Rica. Ofrecemos automóviles sedán, SUV, pickup 4x4,
                    camionetas, busetas y vehículos comerciales para alquileres diarios, semanales y mensuales en
                    San Ramón, Alajuela y todo el territorio nacional.
                </p>
                <h3>Reserve hoy mismo</h3>
                <ul>
                    <li>WhatsApp directo: <?= Html::encode($simpemovilDisplay) ?> (reservas inmediatas)</li>
                    <li>Teléfono de oficina: <?= Html::encode($companyPhoneRaw) ?></li>
                    <?php if ($companyEmail !== ''): ?>
                        <li>Correo electrónico: <?= Html::encode($companyEmail) ?></li>
                    <?php endif; ?>
                    <li>Sitio web oficial: factorentacar.com</li>
                    <?php if ($companyAddress !== ''): ?>
                        <li>Ubicación: <?= Html::encode($companyAddress) ?></li>
                    <?php endif; ?>
                </ul>
                <h3>Servicios destacados</h3>
                <ul>
                    <li>Alquiler de vehículos por día, semana o mes</li>
                    <li>Membresía de cliente recurrente con tarifas preferenciales</li>
                    <li>Solicitudes de alquiler en línea sin volver a registrarse</li>
                    <li>Pagos por SINPE Móvil y transferencia (BN, BCR, BAC)</li>
                </ul>
            </section>
        </main>

        <footer class="footer" role="contentinfo">
            &copy; <?= $year ?> <?= Html::encode($companyName) ?>. Todos los derechos reservados.
            <?php if ($brandHasPng || $brandHasPdf): ?>
                <span class="divider">·</span>
                <a href="#" class="brand-link" data-open-brand="1" title="Descargar logotipo oficial">
                    <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">image</span>
                    Imagen Gráfica
                </a>
            <?php endif; ?>
            <span class="divider">·</span>
            Desarrollado por Ing. Ronald Rojas Castro
        </footer>
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
    <?php
        $brandPngFile = 'logo-facto.png';
        $brandPdfFile = 'logo-facto.pdf';
        $brandPngAbs  = Yii::getAlias('@webroot/img/brand/' . $brandPngFile);
        $brandPdfAbs  = Yii::getAlias('@webroot/img/brand/' . $brandPdfFile);
        $brandPngUrl  = Url::to('@web/img/brand/' . $brandPngFile) . (is_file($brandPngAbs) ? ('?v=' . filemtime($brandPngAbs)) : '');
        $brandPdfUrl  = Url::to('@web/img/brand/' . $brandPdfFile) . (is_file($brandPdfAbs) ? ('?v=' . filemtime($brandPdfAbs)) : '');
        $brandHasPng  = is_file($brandPngAbs);
        $brandHasPdf  = is_file($brandPdfAbs);
    ?>
    <?php if ($brandHasPng || $brandHasPdf): ?>
        <div class="brand-backdrop" id="brand-backdrop" role="dialog" aria-modal="true" aria-labelledby="brand-title" aria-hidden="true">
            <div class="brand-modal" id="brand-modal">
                <button type="button" class="brand-modal-close" id="brand-close" aria-label="Cerrar">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
                <h2 class="bw-title" id="brand-title">
                    <span class="material-symbols-outlined" aria-hidden="true">image</span>
                    Imagen Gráfica
                </h2>
                <p class="bw-subtitle">Descargue el logotipo oficial para uso autorizado.</p>
                <div class="bw-row">
                    <?php if ($brandHasPng): ?>
                        <a href="<?= Html::encode($brandPngUrl) ?>" download="logo-facto.png" class="bw-btn" title="Descargar logo en PNG">
                            <span class="bw-ico-wrap">
                                <img src="<?= Html::encode($brandPngUrl) ?>" alt="Logo PNG">
                            </span>
                            <span class="bw-fmt">PNG</span>
                            <span class="bw-dl">
                                <span class="material-symbols-outlined">download</span>
                                Descargar
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if ($brandHasPdf): ?>
                        <a href="<?= Html::encode($brandPdfUrl) ?>" download="logo-facto.pdf" class="bw-btn" title="Descargar logo vectorial en PDF">
                            <span class="bw-ico-wrap">
                                <?php if ($brandHasPng): ?>
                                    <img src="<?= Html::encode($brandPngUrl) ?>" alt="Logo PDF">
                                <?php else: ?>
                                    <span class="material-symbols-outlined" style="font-size:32px;color:#c0392b;">picture_as_pdf</span>
                                <?php endif; ?>
                            </span>
                            <span class="bw-fmt">PDF</span>
                            <span class="bw-dl">
                                <span class="material-symbols-outlined">download</span>
                                Descargar
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

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

                <nav class="about-social-row social-row" aria-label="Síguenos en redes sociales">
                    <a href="<?= Html::encode($socialLinks['facebook']) ?>" target="_blank" rel="noopener" class="social-icon facebook" title="Facebook" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.13 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.51 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.77l-.44 2.89h-2.33V22c4.78-.75 8.44-4.88 8.44-9.94z"/>
                        </svg>
                    </a>
                    <a href="<?= Html::encode($socialLinks['instagram']) ?>" target="_blank" rel="noopener" class="social-icon instagram" title="Instagram" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.77.13 4.9.34 4.14.63c-.79.31-1.46.72-2.13 1.39C1.34 2.69.93 3.36.63 4.14.34 4.9.13 5.77.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.28.27 2.15.56 2.91.31.79.72 1.46 1.39 2.13.67.67 1.34 1.08 2.13 1.39.76.29 1.63.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.28-.06 2.15-.27 2.91-.56.79-.31 1.46-.72 2.13-1.39.67-.67 1.08-1.34 1.39-2.13.29-.76.5-1.63.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.28-.27-2.15-.56-2.91-.31-.79-.72-1.46-1.39-2.13C21.31 1.34 20.64.93 19.86.63 19.1.34 18.23.13 16.95.07 15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1 0 12 18.16 6.16 6.16 0 0 0 12 5.84zm0 10.16A4 4 0 1 1 12 8a4 4 0 0 1 0 8zm6.41-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>
                        </svg>
                    </a>
                    <a href="<?= Html::encode($socialLinks['whatsapp']) ?>" target="_blank" rel="noopener" class="social-icon whatsapp" title="WhatsApp" aria-label="WhatsApp">
                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill="currentColor" d="M16 .4C7.4.4.5 7.3.5 15.8c0 2.8.7 5.5 2.1 7.9L.3 31.6l8.1-2.1c2.3 1.2 4.9 1.9 7.6 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.4 16 .4zM23.7 19.3c-.3-.2-2-1-2.3-1.1-.3-.1-.6-.2-.8.2s-.9 1.1-1.1 1.4c-.2.3-.4.3-.7.1-2-1-3.3-1.8-4.6-4-.4-.6.4-.6 1-1.9.1-.2 0-.4 0-.6s-.8-1.9-1.1-2.6c-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.3 2.4 3.6 5.8 5.1.8.3 1.4.5 1.9.7.8.3 1.6.2 2.1.1.7-.1 2-.8 2.3-1.6.3-.8.3-1.5.2-1.6-.1-.2-.3-.3-.5-.4z"/>
                        </svg>
                    </a>
                </nav>

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
                                <div class="bank-logo <?= $logoClass ?>" aria-hidden="true" style="background:#fff;">
                                    <?php
                                        $logoUrl = '';
                                        if ($logoFile !== '') {
                                            $logoUrl = Url::to('@web/img/banks/' . $logoFile);
                                            $logoAbs = Yii::getAlias('@webroot/img/banks/' . $logoFile);
                                            if (is_file($logoAbs)) {
                                                $logoUrl .= '?v=' . filemtime($logoAbs);
                                            }
                                        }
                                    ?>
                                    <?php if ($logoUrl !== ''): ?>
                                        <img src="<?= Html::encode($logoUrl) ?>" alt="<?= Html::encode($bankFullName) ?>" style="background:#fff;">
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

                        <?php if ($simpemovilDisplay !== ''):
                            $sinpeFile = 'sinpe-movil.png';
                            $sinpeUrl  = Url::to('@web/img/banks/' . $sinpeFile);
                            $sinpeAbs  = Yii::getAlias('@webroot/img/banks/' . $sinpeFile);
                            if (is_file($sinpeAbs)) { $sinpeUrl .= '?v=' . filemtime($sinpeAbs); }
                        ?>
                            <div class="bank-card">
                                <div class="bank-logo sinpe" aria-hidden="true" style="background:#fff;">
                                    <img src="<?= Html::encode($sinpeUrl) ?>" alt="SINPE Móvil" style="background:#fff;">
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

        (function () {
            const brandBackdrop = document.getElementById('brand-backdrop');
            const brandClose    = document.getElementById('brand-close');
            const brandTriggers = document.querySelectorAll('[data-open-brand]');
            if (!brandBackdrop) return;

            function openBrand(e) {
                if (e) e.preventDefault();
                brandBackdrop.classList.add('is-open');
                brandBackdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function closeBrand() {
                brandBackdrop.classList.remove('is-open');
                brandBackdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            brandTriggers.forEach(function (t) { t.addEventListener('click', openBrand); });
            if (brandClose) brandClose.addEventListener('click', closeBrand);
            brandBackdrop.addEventListener('click', function (e) {
                if (e.target === brandBackdrop) closeBrand();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && brandBackdrop.classList.contains('is-open')) closeBrand();
            });

            // Cerrar el modal después de hacer clic en una descarga (UX limpia).
            brandBackdrop.querySelectorAll('a.bw-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setTimeout(closeBrand, 400);
                });
            });
        })();
    </script>

    <?php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'AutoRental',
            'name'     => $companyName,
            'legalName' => $razonSocial !== '' ? $razonSocial : $companyName,
            'url'      => $canonicalUrl,
            'description' => $seoDescription,
            'telephone' => $companyPhoneE164,
            'priceRange' => '$$',
            'areaServed' => [
                '@type' => 'Country',
                'name'  => 'Costa Rica',
            ],
        ];
        if ($ogImageUrl !== '') {
            $jsonLd['image'] = $ogImageUrl;
            $jsonLd['logo']  = $ogImageUrl;
        }
        if ($companyEmail !== '') {
            $jsonLd['email'] = $companyEmail;
        }
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
                '@type'       => 'ContactPoint',
                'telephone'   => $companyPhoneE164,
                'contactType' => 'customer service',
                'availableLanguage' => ['es', 'en'],
                'areaServed'  => 'CR',
            ],
            [
                '@type'       => 'ContactPoint',
                'telephone'   => '+' . $waFullNumber,
                'contactType' => 'reservations',
                'contactOption' => 'WhatsApp',
                'availableLanguage' => ['es', 'en'],
                'areaServed'  => 'CR',
            ],
        ];
        $jsonLd['sameAs'] = array_values(array_filter([
            $socialLinks['facebook']  ?? null,
            $socialLinks['instagram'] ?? null,
            $waLink,
            'https://www.factorentacar.com',
        ]));
    ?>
    <script type="application/ld+json">
    <?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
</body>
</html>
