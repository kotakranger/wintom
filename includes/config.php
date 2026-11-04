<?php

// Wintom Curtain — Global Configuration
// SSoT: update nilai di sini sebelum deploy ke production

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}
date_default_timezone_set('Asia/Jakarta');   // situs untuk Indonesia: tanggal/jam tampil dalam WIB
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('WA_NUMBER', '6282260132823');   // Format: 628xxxxxxxxxx (tanpa +). Nomor utama
define('WA_NUMBER_2', '6282258877759'); // Nomor kedua
define('WA_URL', 'https://wa.me/' . WA_NUMBER);
define('WA_URL_2', 'https://wa.me/' . WA_NUMBER_2);

define('SITE_NAME', 'Wintom Curtain');
define('SITE_TAGLINE', 'Gorden & Roller Blind — Ready Stock atau Custom');
define('SITE_EMAIL', 'wintomconceptstore@gmail.com');
define('SITE_PHONE', '0822-6013-2823');
define('SITE_PHONE_RAW', '+6282260132823');
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

// Alamat utama situs (absolut, tanpa slash akhir): dipakai canonical, Open Graph, sitemap & data terstruktur.
// Produksi = domain resmi; di localhost otomatis mengikuti alamat yang sedang dibuka. Bisa di-override di config.local.php.
if (!defined('SITE_URL')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = $host !== '' && (preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/', $host) || preg_match('/\.(localhost|test)(:\d+)?$/', $host));
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('SITE_URL', $isLocal ? $scheme . '://' . $host . BASE_URL : 'https://wintom.co.id');
}

// ---------------------------------------------------------------------------
// URL bersih. "route_*" = path relatif situs (tanpa BASE_URL); "url_for" menambah BASE_URL
// untuk link di halaman; "abs_url" menambah SITE_URL untuk canonical/sitemap/Open Graph.
// Aturan rewrite-nya ada di .htaccess.
// ---------------------------------------------------------------------------
function cat_slug(string $name): string
{
    $s = strtolower(trim(str_replace('&', '', $name)));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}
function route_catalog(?string $category = null): string
{
    $slug = $category !== null && $category !== '' ? cat_slug($category) : '';
    return $slug !== '' ? '/katalog/' . $slug : '/katalog';
}
function route_product(string $slug): string { return '/produk/' . rawurlencode($slug); }
function url_for(string $route): string { return BASE_URL . $route; }
function abs_url(string $route): string { return SITE_URL . $route; }
/** URL absolut untuk gambar (upload lokal atau sudah http). */
function abs_asset(string $url): string
{
    if (preg_match('#^https?://#i', $url)) return $url;
    return SITE_URL . '/' . ltrim($url, '/');
}

/** True bila halaman dibuka lewat alamat teknis lama (mis. /products.php, /index.html), bukan URL bersih. */
function is_legacy_url(): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    return (bool)preg_match('/\.(php|html?)$/i', $path);
}
/** Alihkan permanen (301) ke URL bersih, membawa query yang tersisa. */
function redirect_clean(string $route, array $query = []): void
{
    $query = array_filter($query, fn($v) => $v !== '' && $v !== null);
    header('Location: ' . BASE_URL . $route . ($query ? '?' . http_build_query($query) : ''), true, 301);
    exit;
}

// Google Analytics 4 — Measurement ID "G-XXXXXXXXXX" (Admin GA › Aliran data › Web).
// Kosong = GA tidak dimuat. Bisa juga di-override di config.local.php.
if (!defined('GA_MEASUREMENT_ID')) define('GA_MEASUREMENT_ID', 'G-ZKZ23JC4PW');
