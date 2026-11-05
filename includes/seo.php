<?php
// includes/seo.php — meta SEO per halaman: description, canonical, Open Graph, Twitter Card, JSON-LD.
// Halaman mengisi variabel ini SEBELUM include header.php (semuanya opsional):
//   $meta_description  string   deskripsi unik halaman (dipotong ±155 karakter)
//   $canonical_route   string   path bersih relatif situs, mis. '/katalog/roller-blind' (kosong = tanpa canonical)
//   $og_image          string   gambar pratinjau (relatif atau http); default foto hero
//   $og_type           string   'website' (default) atau 'product'
//   $noindex           bool     true = jangan diindeks Google
//   $schema            array    daftar objek JSON-LD (lihat seo_local_business(), seo_breadcrumbs())

const SEO_DEFAULT_DESCRIPTION = 'Wintom Curtain: spesialis gorden premium, roller blind, zebra blind & motorized smart curtain di PIK, Jakarta Utara. Ready stock atau custom, survei dan pasang se-Jabodetabek.';

/** Potong teks jadi deskripsi meta: tanpa HTML/baris baru, berakhir di batas kata. */
function seo_excerpt(string $text, int $max = 155): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $max) return $text;
    $cut = mb_substr($text, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $max * 0.6) $cut = mb_substr($cut, 0, $space);
    return rtrim($cut, " ,.;:–—-") . '…';
}

/** Profil bisnis lokal (dipakai di beranda & halaman tentang). */
function seo_local_business(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type'    => ['LocalBusiness', 'HomeGoodsStore'],
        '@id'      => abs_url('/#business'),
        'name'     => SITE_NAME,
        'description' => SEO_DEFAULT_DESCRIPTION,
        'url'      => abs_url('/'),
        'logo'     => abs_asset('/assets/images/wintom-logo.png'),
        'image'    => abs_asset('/assets/images/hero/hero-1280.webp'),
        'telephone' => SITE_PHONE_RAW,
        'email'    => SITE_EMAIL,
        'address'  => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'Ruko Soho Ebony, Jl. Laksamana Yos Sudarso, Pantai Indah Kapuk No.2 Blok A12',
            'addressLocality' => 'Kamal Muara, Penjaringan',
            'addressRegion'   => 'DKI Jakarta',
            'postalCode'      => '14470',
            'addressCountry'  => 'ID',
        ],
        'openingHoursSpecification' => [[
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'opens'     => '09:00',
            'closes'    => '18:00',
        ]],
        'areaServed' => 'Jabodetabek',
        'sameAs'     => [SITE_INSTAGRAM, SITE_TIKTOK, SITE_FACEBOOK],
    ];
}

/** BreadcrumbList dari daftar [nama => route]. Item terakhir boleh tanpa route. */
function seo_breadcrumbs(array $trail): array
{
    $items = []; $i = 1;
    foreach ($trail as $name => $route) {
        $item = ['@type' => 'ListItem', 'position' => $i++, 'name' => $name];
        if ($route !== null) $item['item'] = abs_url($route);
        $items[] = $item;
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** Cetak semua tag SEO di <head>. */
function seo_head(): void
{
    global $meta_description, $canonical_route, $og_image, $og_type, $noindex, $schema, $page_title;

    $desc  = seo_excerpt((string)($meta_description ?? '') ?: SEO_DEFAULT_DESCRIPTION);
    $title = ($page_title ?? SITE_NAME) . ' | ' . SITE_NAME;
    $type  = $og_type ?? 'website';
    $image = abs_asset((string)($og_image ?? '') ?: '/assets/images/hero/hero-1280.webp');
    $canon = !empty($canonical_route) ? abs_url($canonical_route) : null;
    $e = fn(string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

    echo '<meta name="description" content="', $e($desc), '" />', "\n";
    if (!empty($noindex) || isset($_GET['copy'])) echo '  <meta name="robots" content="noindex,follow" />', "\n";
    if ($canon) echo '  <link rel="canonical" href="', $e($canon), '" />', "\n";

    echo '  <meta property="og:site_name" content="', $e(SITE_NAME), '" />', "\n";
    echo '  <meta property="og:locale" content="id_ID" />', "\n";
    echo '  <meta property="og:type" content="', $e($type), '" />', "\n";
    echo '  <meta property="og:title" content="', $e($title), '" />', "\n";
    echo '  <meta property="og:description" content="', $e($desc), '" />', "\n";
    if ($canon) echo '  <meta property="og:url" content="', $e($canon), '" />', "\n";
    echo '  <meta property="og:image" content="', $e($image), '" />', "\n";
    echo '  <meta name="twitter:card" content="summary_large_image" />', "\n";
    echo '  <meta name="twitter:title" content="', $e($title), '" />', "\n";
    echo '  <meta name="twitter:description" content="', $e($desc), '" />', "\n";
    echo '  <meta name="twitter:image" content="', $e($image), '" />', "\n";

    foreach ((array)($schema ?? []) as $obj) {
        echo '  <script type="application/ld+json">',
             json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP),
             '</script>', "\n";
    }
}
