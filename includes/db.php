<?php
// Wintom Curtain — Database Connection & Schema Initialization (MySQL / MariaDB)

require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = connect_db();
        ensure_schema($pdo);
        seed_defaults($pdo);
    }
    return $pdo;
}

/**
 * Open the MySQL connection. If the database does not exist yet (error 1049)
 * it is created once so a fresh machine only needs a running MySQL server.
 */
function connect_db(): PDO {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $dsn = fn(bool $withDb) => 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
         . ($withDb ? ';dbname=' . DB_NAME : '') . ';charset=utf8mb4';

    try {
        return new PDO($dsn(true), DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if ((int)$e->getCode() !== 1049) {
            throw $e;
        }
        $root = new PDO($dsn(false), DB_USER, DB_PASS, $options);
        $root->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        return new PDO($dsn(true), DB_USER, DB_PASS, $options);
    }
}

/**
 * CREATE TABLE IF NOT EXISTS for every table. The information_schema check
 * keeps this to a single cheap query per request once the schema exists.
 */
function ensure_schema(PDO $pdo): void {
    // Satu query murah per request: jumlah tabel + kolom hasil upgrade yang wajib ada.
    [$have, $upgrades] = array_map('intval', $pdo->query(
        "SELECT
           (SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name IN ('admins','categories','products','product_images','product_variants',
                                  'product_variant_options','galleries','gallery_images','inquiries','content_videos')),
           (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND ((table_name = 'inquiries' AND column_name = 'width_cm')
                 OR (table_name = 'product_variants' AND column_name = 'is_size')
                 OR (table_name = 'product_variant_options' AND column_name = 'width_cm')
                 OR (table_name = 'galleries' AND column_name = 'category_id')
                 OR (table_name = 'products' AND column_name = 'use_category_price')
                 OR (table_name = 'categories' AND column_name = 'image_url')))"
    )->fetch(PDO::FETCH_NUM));
    if ($have === 10 && $upgrades === 6) {
        return;
    }

    $tables = [
        "CREATE TABLE IF NOT EXISTS admins (
            id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // price_per_meter: harga dasar per meter, diwarisi semua produk di kategori ini
        "CREATE TABLE IF NOT EXISTS categories (
            id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name            VARCHAR(120) NOT NULL UNIQUE,
            price_per_meter INT UNSIGNED NOT NULL DEFAULT 0,
            image_url       VARCHAR(500) DEFAULT NULL,   -- foto kartu kategori di landing page
            tagline         VARCHAR(120) DEFAULT NULL,   -- teks kecil di bawah nama kategori
            sort_order      INT NOT NULL DEFAULT 0,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS products (
            id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            slug           VARCHAR(190) NOT NULL UNIQUE,
            name           VARCHAR(255) NOT NULL,
            category_id    INT UNSIGNED NOT NULL,
            badge          VARCHAR(100) DEFAULT NULL,
            ref_code       VARCHAR(50)  DEFAULT NULL,
            price          INT UNSIGNED NOT NULL,
            original_price INT UNSIGNED DEFAULT NULL,
            price_unit     VARCHAR(50)  DEFAULT '/ meter',
            description    TEXT,
            features       TEXT,
            options_json   TEXT,
            cover_image    VARCHAR(500) DEFAULT NULL,
            use_category_price TINYINT(1) NOT NULL DEFAULT 1,   -- 1 = harga/meter kategori, 0 = price + price_unit
            is_active      TINYINT(1) NOT NULL DEFAULT 1,
            stock          INT DEFAULT NULL,
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_products_category (category_id),
            CONSTRAINT fk_products_category FOREIGN KEY (category_id)
                REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS product_images (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            image_url  VARCHAR(500) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_cover   TINYINT(1) NOT NULL DEFAULT 0,
            KEY idx_images_product (product_id),
            CONSTRAINT fk_images_product FOREIGN KEY (product_id)
                REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS product_variants (
            id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_id      INT UNSIGNED NOT NULL,
            label           VARCHAR(100) NOT NULL,
            is_image_linked TINYINT(1) NOT NULL DEFAULT 0,
            is_size         TINYINT(1) NOT NULL DEFAULT 0,
            sort_order      INT NOT NULL DEFAULT 0,
            KEY idx_variants_product (product_id),
            CONSTRAINT fk_variants_product FOREIGN KEY (product_id)
                REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS product_variant_options (
            id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            variant_id      INT UNSIGNED NOT NULL,
            value           VARCHAR(255) NOT NULL,
            linked_image_id INT UNSIGNED DEFAULT NULL,
            width_cm        DECIMAL(7,1) UNSIGNED DEFAULT NULL,
            height_cm       DECIMAL(7,1) UNSIGNED DEFAULT NULL,
            sort_order      INT NOT NULL DEFAULT 0,
            KEY idx_options_variant (variant_id),
            CONSTRAINT fk_options_variant FOREIGN KEY (variant_id)
                REFERENCES product_variants(id) ON DELETE CASCADE,
            CONSTRAINT fk_options_image FOREIGN KEY (linked_image_id)
                REFERENCES product_images(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS galleries (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title       VARCHAR(255) NOT NULL,
            category_id INT UNSIGNED DEFAULT NULL,   -- kategori produk (portofolio = hasil pemasangan produk)
            category    VARCHAR(120) NOT NULL DEFAULT '',   -- lama (teks ruangan), tidak dipakai lagi
            image_url   VARCHAR(500) NOT NULL,
            created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_gallery_category (category_id),
            CONSTRAINT fk_gallery_category FOREIGN KEY (category_id)
                REFERENCES categories(id) ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Foto tambahan per proyek portofolio (foto utama/cover tetap di galleries.image_url)
        "CREATE TABLE IF NOT EXISTS gallery_images (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            gallery_id INT UNSIGNED NOT NULL,
            image_url  VARCHAR(500) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_gallery_images (gallery_id, sort_order),
            CONSTRAINT fk_gallery_images FOREIGN KEY (gallery_id)
                REFERENCES galleries(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Inquiry dari form "Kontak Kami". curtain_type menyimpan nama kategori saat submit
        // (snapshot), category_id dipakai untuk relasi selama kategorinya masih ada.
        "CREATE TABLE IF NOT EXISTS inquiries (
            id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            full_name      VARCHAR(120) NOT NULL,
            whatsapp       VARCHAR(25)  NOT NULL,
            category_id    INT UNSIGNED DEFAULT NULL,
            curtain_type   VARCHAR(120) NOT NULL,
            estimated_size VARCHAR(100) DEFAULT NULL,
            width_cm       DECIMAL(7,1) UNSIGNED DEFAULT NULL,
            height_cm      DECIMAL(7,1) UNSIGNED DEFAULT NULL,
            quantity       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            notes          TEXT,
            status         ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
            ip_address     VARCHAR(45)  DEFAULT NULL,
            created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_inquiries_status (status, created_at),
            CONSTRAINT fk_inquiries_category FOREIGN KEY (category_id)
                REFERENCES categories(id) ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Video/Shorts YouTube untuk section konten di homepage (dikelola dari admin)
        "CREATE TABLE IF NOT EXISTS content_videos (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title      VARCHAR(200) DEFAULT NULL,
            youtube_id CHAR(11)     NOT NULL UNIQUE,
            is_short   TINYINT(1)   NOT NULL DEFAULT 0,
            sort_order INT          NOT NULL DEFAULT 0,
            is_active  TINYINT(1)   NOT NULL DEFAULT 1,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_videos_order (is_active, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    $has_col = function (string $table, string $col) use ($pdo): bool {
        $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns
                             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
        $st->execute([$table, $col]);
        return (int)$st->fetchColumn() > 0;
    };

    // Upgrade: ukuran terstruktur (lebar × tinggi × jumlah) pada inquiry lama
    if (!$has_col('inquiries', 'width_cm')) {
        $pdo->exec("ALTER TABLE inquiries
            ADD COLUMN width_cm  DECIMAL(7,1) UNSIGNED DEFAULT NULL AFTER estimated_size,
            ADD COLUMN height_cm DECIMAL(7,1) UNSIGNED DEFAULT NULL AFTER width_cm,
            ADD COLUMN quantity  SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER height_cm");
    }

    // Upgrade: varian "Ukuran" jadi parameter tetap dengan lebar × tinggi terstruktur
    if (!$has_col('product_variants', 'is_size')) {
        $pdo->exec("ALTER TABLE product_variants ADD COLUMN is_size TINYINT(1) NOT NULL DEFAULT 0 AFTER is_image_linked");
    }
    if (!$has_col('product_variant_options', 'width_cm')) {
        $pdo->exec("ALTER TABLE product_variant_options
            ADD COLUMN width_cm  DECIMAL(7,1) UNSIGNED DEFAULT NULL AFTER linked_image_id,
            ADD COLUMN height_cm DECIMAL(7,1) UNSIGNED DEFAULT NULL AFTER width_cm");
        migrate_size_variants($pdo);
    }

    // Upgrade: kategori portofolio dihubungkan ke kategori produk
    if (!$has_col('galleries', 'category_id')) {
        $pdo->exec("ALTER TABLE galleries
            ADD COLUMN category_id INT UNSIGNED DEFAULT NULL AFTER title,
            MODIFY category VARCHAR(120) NOT NULL DEFAULT '',
            ADD KEY idx_gallery_category_id (category_id),
            ADD CONSTRAINT fk_gallery_category FOREIGN KEY (category_id)
                REFERENCES categories(id) ON UPDATE CASCADE ON DELETE SET NULL");
        migrate_gallery_categories($pdo);
    }

    // Upgrade: harga produk mengikuti kategori (default) atau harga custom per produk
    if (!$has_col('products', 'use_category_price')) {
        $pdo->exec("ALTER TABLE products ADD COLUMN use_category_price TINYINT(1) NOT NULL DEFAULT 1 AFTER cover_image");
    }

    // Upgrade: foto & tagline kategori (kartu kategori di landing page)
    if (!$has_col('categories', 'image_url')) {
        $pdo->exec("ALTER TABLE categories
            ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER price_per_meter,
            ADD COLUMN tagline   VARCHAR(120) DEFAULT NULL AFTER image_url");
        // Pindahkan isi kartu landing page lama (sebelumnya hardcode) ke data kategori
        $pdo->exec("UPDATE categories SET tagline = 'Sentuhan elegan di setiap ruang',
                    image_url = 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1000&q=80&fit=crop'
                    WHERE name = 'Curtain'");
        $pdo->exec("UPDATE categories SET tagline = 'Praktis dioperasikan, nyaman setiap hari',
                    image_url = 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1000&q=80&fit=crop'
                    WHERE name = 'Roller Blind'");
    }
}

/**
 * Harga efektif produk untuk ditampilkan.
 * Butuh kolom: use_category_price, price, price_unit, price_per_meter (dari JOIN categories).
 * @return array{amount:int, unit:string, custom:bool}  amount 0 = belum diatur
 */
function product_price(array $p): array {
    if (isset($p['use_category_price']) && (int)$p['use_category_price'] === 0) {
        // Satuan disimpan tanpa "/" (mis. "set"); data lama "/ set" tetap didukung.
        $unit = trim(ltrim((string)($p['price_unit'] ?? ''), "/ \t")) ?: 'unit';
        return ['amount' => (int)$p['price'], 'unit' => '/ ' . $unit, 'custom' => true];
    }
    return ['amount' => (int)($p['price_per_meter'] ?? 0), 'unit' => '/ meter', 'custom' => false];
}

/** SQL harga efektif (alias tabel p = products, c = categories), untuk urutan katalog. */
const PRODUCT_PRICE_SQL = "CASE WHEN p.use_category_price = 1 THEN c.price_per_meter ELSE p.price END";

/**
 * Data lama memakai kategori ruangan (Living Room, dll.). Tebak kategori produk dari judul proyek;
 * yang tidak jelas dibiarkan NULL ("Tanpa kategori") untuk dipilih admin.
 */
function migrate_gallery_categories(PDO $pdo): void {
    $cats = $pdo->query("SELECT LOWER(name), id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
    $rules = [   // urutan penting: yang paling spesifik dulu
        'motorized curtain track' => '/motori[sz]ed|smart curtain|somfy/i',
        'roller blind'            => '/roller|sunscreen/i',
        'zebra blind'             => '/zebra/i',
        'roman blind'             => '/roman/i',
        'vertical blind'          => '/vertical/i',
        'curtain'                 => '/curtain|gorden|drape|draping|linen|sheer|voile|velour|blackout|ripple|s-fold|wave|pleat/i',
    ];
    $upd = $pdo->prepare("UPDATE galleries SET category_id = ? WHERE id = ?");
    foreach ($pdo->query("SELECT id, title FROM galleries")->fetchAll() as $g) {
        foreach ($rules as $cat => $re) {
            if (isset($cats[$cat]) && preg_match($re, $g['title'])) {
                $upd->execute([$cats[$cat], $g['id']]);
                break;
            }
        }
    }
}

/**
 * Data lama: varian pertama berlabel "Ukuran"/"Panjang Track" berisi teks "120×160cm".
 * Tandai sebagai varian ukuran, isi lebar/tinggi dari teks, buang opsi "Custom" (kini otomatis di halaman produk).
 */
function migrate_size_variants(PDO $pdo): void {
    $vars = $pdo->query("SELECT id FROM product_variants
                         WHERE label IN ('Ukuran','Panjang Track')
                           AND sort_order = (SELECT MIN(v2.sort_order) FROM (SELECT * FROM product_variants) v2
                                             WHERE v2.product_id = product_variants.product_id)")->fetchAll(PDO::FETCH_COLUMN);
    $mark = $pdo->prepare("UPDATE product_variants SET is_size = 1, label = 'Ukuran' WHERE id = ?");
    $opts = $pdo->prepare("SELECT id, value FROM product_variant_options WHERE variant_id = ?");
    $set  = $pdo->prepare("UPDATE product_variant_options SET width_cm = ?, height_cm = ?, value = ? WHERE id = ?");
    $del  = $pdo->prepare("DELETE FROM product_variant_options WHERE id = ?");
    foreach ($vars as $vid) {
        $mark->execute([$vid]);
        $opts->execute([$vid]);
        foreach ($opts->fetchAll() as $o) {
            if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:×|x|X|\*)\s*(\d+(?:[.,]\d+)?)/u', $o['value'], $m)) {
                $w = (float)str_replace(',', '.', $m[1]);
                $h = (float)str_replace(',', '.', $m[2]);
                $set->execute([$w, $h, format_size_label($w, $h), $o['id']]);
            } else {
                $del->execute([$o['id']]);   // "Custom (WA)" & teks bebas lain
            }
        }
    }
}

/** Label opsi ukuran standar: "120×160cm". */
function format_size_label(float $w, float $h): string {
    $n = fn($v) => rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.');
    return $n($w) . '×' . $n($h) . 'cm';
}

/**
 * Format ukuran inquiry: "150cm x 200cm | Qty: 1 set". Tanpa lebar/tinggi -> "- | Qty: N set".
 */
function format_inquiry_size($width, $height, $qty): string {
    $n = fn($v) => rtrim(rtrim(number_format((float)$v, 1, '.', ''), '0'), '.');
    $dims = ($width !== null && $height !== null) ? $n($width) . 'cm x ' . $n($height) . 'cm' : '-';
    return $dims . ' | Qty: ' . max(1, (int)$qty) . ' set';
}

function seed_defaults(PDO $pdo): void {

    // =====================================================================
    // SEED: Default admin
    // =====================================================================
    if ((int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn() === 0) {
        // SECURITY: Change this default password immediately after first login
        $hash = password_hash('wintom2024', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")
            ->execute(['admin', $hash]);
    }

    // =====================================================================
    // SEED: Categories + harga dasar per meter (placeholder — edit di admin)
    // =====================================================================
    if ((int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() === 0) {
        $default_cats = [
            ['Curtain',                 150000],
            ['Roller Blind',            100000],
            ['Zebra Blind',             100000],
            ['Roman Blind',             120000],
            ['Vertical Blind',          100000],
            ['Motorized Curtain Track', 750000],
        ];
        $ins_cat = $pdo->prepare("INSERT INTO categories (name, price_per_meter, sort_order) VALUES (?, ?, ?)");
        foreach ($default_cats as $i => [$cat, $ppm]) {
            $ins_cat->execute([$cat, $ppm, $i]);
        }
    }

    // =====================================================================
    // SEED: Sample products (only if table is empty)
    // =====================================================================
    if ((int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() > 0) {
        // Products exist — only run variant migration for existing products
        migrate_variants_from_options_json($pdo);
        return;
    }

    $cat_ids = $pdo->query("SELECT name, id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);

    $sample_products = [
        [
            'slug'           => 'aura-drape-french-linen',
            'name'           => 'Aura Drape — French Linen Blend',
            'category'       => 'Curtain',
            'badge'          => 'Curator Choice',
            'ref_code'       => 'WNT-CR-01',
            'price'          => 385000,
            'original_price' => 450000,
            'price_unit'     => '/ meter',
            'description'    => 'Tirai drapery mewah berbahan perpaduan linen Belgia dengan tenunan berpola rapat yang jatuh dengan anggun. Memberikan nuansa hangat, tekstur alami organik, dan redaman akustik ruangan yang optimal.',
            'features'       => 'Material 65% Belgian Linen, 35% Poly-Loom|S-Fold Wave / Ripple Fold Hemming Presisi|Semi-Blackout (80% Light Reduction)|Termasuk Rel Arsitektural Silent-Glide',
            'cover_image'    => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
                'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85',
                'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Ukuran',        'is_size' => true,  'is_image_linked' => false, 'options' => ['120×160cm','120×240cm','150×160cm','150×240cm','Custom (WA)']],
                ['label' => 'Warna',         'is_size' => false, 'is_image_linked' => true,  'options' => ['Oatmeal Cream','Warm Sand','Slate Charcoal','Muted Olive']],
                ['label' => 'Gaya Jahitan',  'is_size' => false, 'is_image_linked' => false, 'options' => ['Ripple Fold 2.2x','Double Pinch Pleat','Eyelet Minimalist']],
            ],
        ],
        [
            'slug'           => 'velour-noir-100-blackout',
            'name'           => 'Velour Noir — 100% Total Blackout',
            'category'       => 'Curtain',
            'badge'          => '100% Blackout',
            'ref_code'       => 'WNT-CR-02',
            'price'          => 495000,
            'original_price' => 580000,
            'price_unit'     => '/ meter',
            'description'    => 'Solusi kegelapan total mutlak untuk master bedroom dan private home cinema. Dilapisi 3-pass acrylic thermal layer yang meredam suhu panas sinar matahari dan kebisingan jalan perkotaan.',
            'features'       => '100% Zero-Light Transmittance Tested|Thermal Barrier (Menurunkan panas ruangan hingga 4°C)|Lapisan Anti-Statik Debu & Tungau|Double Weight Lead-Chain Bottom Hem',
            'cover_image'    => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',
                'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Ukuran',       'is_size' => true,  'is_image_linked' => false, 'options' => ['120×160cm','120×240cm','150×160cm','150×240cm','Custom (WA)']],
                ['label' => 'Warna',        'is_size' => false, 'is_image_linked' => false, 'options' => ['Midnight Navy','Deep Espresso','Anthracite Dark Grey','Ivory Cashmere']],
                ['label' => 'Gaya Jahitan', 'is_size' => false, 'is_image_linked' => false, 'options' => ['Ripple Fold 2.5x Fullness','Deep Pinch Pleat']],
            ],
        ],
        [
            'slug'           => 'lumiere-sheer-ripple-fold',
            'name'           => 'Lumière Sheer — Swiss Voile Vitrase',
            'category'       => 'Curtain',
            'badge'          => 'Bestseller',
            'ref_code'       => 'WNT-SH-01',
            'price'          => 265000,
            'original_price' => 310000,
            'price_unit'     => '/ meter',
            'description'    => 'Vitrase voile tenun halus bermotif mikroskopis lembut asal Swiss yang meneruskan cahaya alami lembut ke dalam hunian tanpa menyilaukan mata, sekaligus menjaga privasi ruang keluarga di siang hari.',
            'features'       => 'Ultra-Light Swiss Weave Micro-fiber|Difusi Cahaya Alami Matahari yang Lembut|Gelombang Lipatan Ripple Fold Sempurna & Simetris|Tahan Cuci & Anti-Kusut',
            'cover_image'    => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Ukuran',       'is_size' => true,  'is_image_linked' => false, 'options' => ['100×200cm','120×200cm','150×200cm','Custom (WA)']],
                ['label' => 'Warna',        'is_size' => false, 'is_image_linked' => false, 'options' => ['Pure Snow White','Warm Alabaster','Champagne Tint','Soft Greige']],
                ['label' => 'Gaya Jahitan', 'is_size' => false, 'is_image_linked' => false, 'options' => ['Continuous S-Fold Wave','Tailored Box Pleat']],
            ],
        ],
        [
            'slug'           => 'aero-roller-blind-sunscreen',
            'name'           => 'Aero Blind — Minimalist Sunscreen 5%',
            'category'       => 'Roller Blind',
            'badge'          => 'Modern Minimalist',
            'ref_code'       => 'WNT-RB-01',
            'price'          => 320000,
            'original_price' => 380000,
            'price_unit'     => '/ m²',
            'description'    => 'Roller blind berestetika arsitektural bersih dengan keterbukaan tenun 5%. Menghalau silau matahari dan 95% radiasi UV berbahaya sembari tetap mempertahankan pemandangan ke luar jendela secara jernih.',
            'features'       => '95% UV Rejection & Glare Filter|Komponen Heavy-Duty Spring & Tube Aluminium 38mm|Sistem Tarikan Rantai Stainless Steel Anti-Macet|Cocok untuk Ruang Kerja & High-Rise Apartment',
            'cover_image'    => 'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',
                'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Ukuran',  'is_size' => true,  'is_image_linked' => false, 'options' => ['60×120cm','90×150cm','120×200cm','160×200cm','Custom (WA)']],
                ['label' => 'Warna',   'is_size' => false, 'is_image_linked' => false, 'options' => ['Mono White','Ash Grey','Dark Bronze Charcoal','Sand Dune']],
                ['label' => 'Kontrol', 'is_size' => false, 'is_image_linked' => false, 'options' => ['Manual Stainless Chain','Cordless Spring Assist']],
            ],
        ],
        [
            'slug'           => 'zenith-motorized-somfy-system',
            'name'           => 'Zenith Smart Motorized Curtain System',
            'category'       => 'Motorized Curtain Track',
            'badge'          => 'Smart Home Ready',
            'ref_code'       => 'WNT-SM-01',
            'price'          => 2750000,
            'original_price' => 3200000,
            'price_unit'     => '/ set track',
            'description'    => 'Sistem motorisasi tirai kelas dunia bertenaga Somfy / Tuya Ultra-Quiet Motor (< 30dB). Terintegrasi penuh dengan Apple HomeKit, Google Home, remote multi-channel, dan sensor matahari otomatis.',
            'features'       => 'Motor Whisper-Quiet (<30dB) dengan Soft-Start & Stop|Kompatibel Apple HomeKit, Google Assistant, Tuya|Touch Motion Assist (Tarik lembut kain langsung jalan)|Beban Maksimum Tirai hingga 60 Kilogram',
            'cover_image'    => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Panjang Track', 'is_size' => true,  'is_image_linked' => false, 'options' => ['100cm','150cm','200cm','300cm','Custom (WA)']],
                ['label' => 'Motor Brand',   'is_size' => false, 'is_image_linked' => false, 'options' => ['Somfy France WireFree','Tuya Zigbee Smart Ultra','Dooya RS485 Pro']],
                ['label' => 'Tipe Track',    'is_size' => false, 'is_image_linked' => false, 'options' => ['Lurus (Straight)','L-Shape Curved 90°','Double Track (Drape + Sheer)']],
            ],
        ],
        [
            'slug'           => 'komorebi-paulownia-wood-blind',
            'name'           => 'Komorebi Natural Paulownia Wood Blind',
            'category'       => 'Vertical Blind',
            'badge'          => 'Eco-Natural Wood',
            'ref_code'       => 'WNT-WB-01',
            'price'          => 540000,
            'original_price' => 620000,
            'price_unit'     => '/ m²',
            'description'    => 'Bilah kayu Paulownia solid 50mm yang ringan dan tidak membebani konstruksi jendela. Memberikan siluet bayangan matahari geometris tropis khas resor bintang lima dengan pernis tahan kelembapan.',
            'features'       => 'Kayu Solid Paulownia 50mm Ringan & Anti-Melengkung|Pernis UV 5-Layer Tahan Cuaca Tropis Lembab|Pilihan Pita Fabric Ladder Tape Elegan|Headrail Profil Baja Kokoh Tertutup Valance Kayu',
            'cover_image'    => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',
            'is_active'      => 1,
            'gallery'        => [
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',
                'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',
            ],
            'variants' => [
                ['label' => 'Ukuran', 'is_size' => true,  'is_image_linked' => false, 'options' => ['60×120cm','90×150cm','120×180cm','Custom (WA)']],
                ['label' => 'Warna',  'is_size' => false, 'is_image_linked' => false, 'options' => ['Natural Oak','Warm Teak','Ebony Charcoal','Nordic White']],
                ['label' => 'Tape',   'is_size' => false, 'is_image_linked' => false, 'options' => ['Matching Fabric Tape 38mm','Hidden Cord Minimalist']],
            ],
        ],
    ];

    $ins_prod = $pdo->prepare("
        INSERT INTO products (slug, name, category_id, badge, ref_code, price, original_price,
                              price_unit, description, features, cover_image, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ins_img  = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_cover) VALUES (?, ?, ?, ?)");
    $ins_var  = $pdo->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, sort_order) VALUES (?, ?, ?, ?)");
    $ins_opt  = $pdo->prepare("INSERT INTO product_variant_options (variant_id, value, sort_order) VALUES (?, ?, ?)");

    foreach ($sample_products as $p) {
        $ins_prod->execute([
            $p['slug'], $p['name'], $cat_ids[$p['category']], $p['badge'], $p['ref_code'],
            $p['price'], $p['original_price'], $p['price_unit'], $p['description'],
            $p['features'], $p['cover_image'], $p['is_active'],
        ]);
        $pid = (int)$pdo->lastInsertId();

        // Insert gallery images (first image = cover)
        foreach ($p['gallery'] as $idx => $url) {
            $ins_img->execute([$pid, $url, $idx, $idx === 0 ? 1 : 0]);
        }

        // Insert variants + options
        foreach ($p['variants'] as $vi => $var) {
            $ins_var->execute([$pid, $var['label'], $var['is_image_linked'] ? 1 : 0, $vi]);
            $vid = (int)$pdo->lastInsertId();
            foreach ($var['options'] as $oi => $opt) {
                $ins_opt->execute([$vid, $opt, $oi]);
            }
        }
    }
}

/**
 * One-time migration: for products that have options_json but no variants yet,
 * create variant rows from the JSON. Safe to call repeatedly — checks first.
 */
function migrate_variants_from_options_json(PDO $pdo): void {
    // Find products with options_json but no variant rows yet
    $rows = $pdo->query("
        SELECT p.id, p.options_json
        FROM products p
        WHERE p.options_json IS NOT NULL
          AND p.options_json != ''
          AND NOT EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id)
    ")->fetchAll();

    if (empty($rows)) return;

    $ins_var = $pdo->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, sort_order) VALUES (?, ?, 0, ?)");
    $ins_opt = $pdo->prepare("INSERT INTO product_variant_options (variant_id, value, sort_order) VALUES (?, ?, ?)");

    // Also ensure is_cover flag on first product_image
    $set_cover = $pdo->prepare("
        UPDATE product_images SET is_cover = 1
        WHERE id = (SELECT id FROM (SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1) first_img)
    ");

    foreach ($rows as $row) {
        $opts = json_decode($row['options_json'], true);
        if (!is_array($opts)) continue;

        $vi = 0;
        // Always create "Ukuran" as first variant if not present
        $ins_var->execute([$row['id'], 'Ukuran', $vi]);
        $vid = (int)$pdo->lastInsertId();
        $default_sizes = ['120×160cm','120×240cm','150×160cm','150×240cm','Custom (WA)'];
        foreach ($default_sizes as $oi => $sz) {
            $ins_opt->execute([$vid, $sz, $oi]);
        }
        $vi++;

        // Map old keys to new variant rows (skip 'sizes' key if present)
        $label_map = ['colors' => 'Warna', 'stitch' => 'Gaya Jahitan', 'control' => 'Kontrol', 'tape' => 'Tape', 'motor_brand' => 'Motor Brand', 'track_type' => 'Tipe Track'];
        foreach ($opts as $key => $values) {
            if (!is_array($values) || empty($values)) continue;
            $label = $label_map[$key] ?? ucfirst($key);
            $ins_var->execute([$row['id'], $label, $vi]);
            $vid2 = (int)$pdo->lastInsertId();
            foreach ($values as $oi => $val) {
                $ins_opt->execute([$vid2, $val, $oi]);
            }
            $vi++;
            if ($vi >= 4) break; // max 3 custom + 1 ukuran
        }

        $set_cover->execute([$row['id']]);
    }
}
