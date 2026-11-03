<?php
// sitemap.php — sitemap.xml dinamis (diakses lewat /sitemap.xml, lihat .htaccess).
// Memuat beranda, katalog, tiap kategori, tiap produk aktif, galeri dan halaman tentang.
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$urls = [
    ['/',        null, 'weekly',  '1.0'],
    ['/katalog', null, 'weekly',  '0.9'],
];
foreach ($db->query('SELECT name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll() as $c) {
    $urls[] = [route_catalog($c['name']), null, 'weekly', '0.8'];
}
foreach ($db->query('SELECT slug, created_at FROM products WHERE is_active = 1 ORDER BY id ASC')->fetchAll() as $p) {
    $urls[] = [route_product($p['slug']), substr((string)$p['created_at'], 0, 10) ?: null, 'monthly', '0.7'];
}
$urls[] = ['/galeri',  null, 'monthly', '0.6'];
$urls[] = ['/tentang', null, 'yearly',  '0.5'];

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');   // file sitemap sendiri tidak perlu muncul di hasil pencarian
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as [$route, $lastmod, $freq, $prio]) {
    echo "  <url>\n    <loc>", htmlspecialchars(abs_url($route), ENT_XML1), "</loc>\n";
    if ($lastmod) echo "    <lastmod>$lastmod</lastmod>\n";
    echo "    <changefreq>$freq</changefreq>\n    <priority>$prio</priority>\n  </url>\n";
}
echo "</urlset>\n";
