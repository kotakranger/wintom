<?php

// Wintom Curtain — Global Configuration
// SSoT: update nilai di sini sebelum deploy ke production

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('WA_NUMBER', '6282258877769');   // Format: 628xxxxxxxxxx (tanpa +). Nomor utama
define('WA_NUMBER_2', '6282258877759'); // Nomor kedua
define('WA_URL', 'https://wa.me/' . WA_NUMBER);
define('WA_URL_2', 'https://wa.me/' . WA_NUMBER_2);

define('SITE_NAME', 'Wintom Curtain');
define('SITE_TAGLINE', 'Gorden & Roller Blind — Ready Stock atau Custom');
define('SITE_EMAIL', 'wintomconceptstore@gmail.com');
define('SITE_PHONE', '0822-5887-7769');
define('SITE_PHONE_RAW', '+6282258877769');
define('SITE_PHONE_2', '0822-5887-7759');
define('SITE_PHONE_2_RAW', '+6282258877759');
define('SITE_ADDRESS', 'Ruko Soho Ebony, Jl. Laksamana Yos Sudarso, Jl. Pantai Indah Kapuk No.2 Blok A12, Kamal Muara, Kec. Penjaringan, DKI Jakarta 14470');
define('SITE_ADDRESS_SHORT', 'Ruko Soho Ebony, PIK, Jakarta Utara');
define('SITE_HOURS', 'Senin – Sabtu, 09.00 – 18.00 WIB · Minggu tutup');
define('SITE_INSTAGRAM', 'https://www.instagram.com/wintom.id/');
define('SITE_TIKTOK', 'https://www.tiktok.com/@wintom.id');
define('SITE_FACEBOOK', 'https://www.facebook.com/wintom.curtain/');

// Auto-detect BASE_URL: kosong untuk root, atau otomatis mendeteksi '/wintom' saat dijalankan via MAMP htdocs
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $cleanPath = preg_replace('#/(admin|includes)$#', '', $scriptDir);
    $detectedBase = ($cleanPath !== '/' && $cleanPath !== '.' && $cleanPath !== '') ? rtrim($cleanPath, '/') : '';
    define('BASE_URL', $detectedBase);
}

function asset_url(string $url): string
{
    return preg_match('#^https?://#i', $url) ? $url : BASE_URL . $url;
}

define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Database (MySQL / MariaDB). Override per-machine lewat includes/config.local.php
// (tidak di-commit) atau environment variable WINTOM_DB_*.
if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}
foreach ([
    'DB_HOST' => ['WINTOM_DB_HOST', '127.0.0.1'],
    'DB_PORT' => ['WINTOM_DB_PORT', '3306'],
    'DB_NAME' => ['WINTOM_DB_NAME', 'wintom'],
    'DB_USER' => ['WINTOM_DB_USER', 'root'],
    'DB_PASS' => ['WINTOM_DB_PASS', ''],
] as $const => [$env, $default]) {
    if (!defined($const)) {
        $val = getenv($env);
        define($const, $val !== false ? $val : $default);
    }
}
