<?php

// Wintom Curtain — Global Configuration
// SSoT: update nilai di sini sebelum deploy ke production

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('WA_NUMBER', '6289602052229');  // Format: 628xxxxxxxxxx (tanpa +)
define('WA_URL', 'https://wa.me/' . WA_NUMBER);

define('SITE_NAME', 'Wintom Curtain');
define('SITE_TAGLINE', 'Window Dressing & Motorized Blind');
define('SITE_EMAIL', 'info@wintom.co.id');
define('SITE_PHONE', '089-602-052-229');
define('SITE_PHONE_RAW', '089602052229');
define('SITE_ADDRESS', 'PIK 2, Jakarta Utara');
define('SITE_HOURS', 'Senin – Sabtu, 09:00 – 18:00 WIB');

// Auto-detect BASE_URL: kosong untuk root, atau otomatis mendeteksi '/wintom' saat dijalankan via MAMP htdocs
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $cleanPath = preg_replace('#/(admin|includes)$#', '', $scriptDir);
    $detectedBase = ($cleanPath !== '/' && $cleanPath !== '.' && $cleanPath !== '') ? rtrim($cleanPath, '/') : '';
    define('BASE_URL', $detectedBase);
}

// Stored upload paths are root-relative ('/uploads/...'); external URLs pass through
function asset_url(string $url): string
{
    return preg_match('#^https?://#i', $url) ? $url : BASE_URL . $url;
}
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('DB_PATH', __DIR__ . '/../database/curtains.sqlite');
