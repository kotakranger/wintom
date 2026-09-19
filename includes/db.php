<?php
// Wintom Curtain — Database Connection & Schema Initialization
// Updated: 2026-09-19 — Variant System Overhaul

require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        init_db($pdo);
    }
    return $pdo;
}

function init_db(PDO $pdo): void {

    // =====================================================================
    // CORE TABLES (CREATE IF NOT EXISTS — safe to run repeatedly)
    // =====================================================================
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      TEXT    UNIQUE NOT NULL,
            password_hash TEXT    NOT NULL
        );

        CREATE TABLE IF NOT EXISTS categories (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT    NOT NULL UNIQUE,
            sort_order INTEGER DEFAULT 0,
            created_at TEXT    DEFAULT (datetime('now','localtime'))
        );

        CREATE TABLE IF NOT EXISTS products (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            slug           TEXT    UNIQUE NOT NULL,
            name           TEXT    NOT NULL,
            category       TEXT    NOT NULL,
            badge          TEXT,
            ref_code       TEXT,
            price          INTEGER NOT NULL,
            original_price INTEGER,
            price_unit     TEXT    DEFAULT '/ meter',
            description    TEXT,
            features       TEXT,
            options_json   TEXT,
            cover_image    TEXT    NOT NULL,
            is_active      INTEGER DEFAULT 1,
            stock          INTEGER DEFAULT NULL,
            created_at     TEXT    DEFAULT (datetime('now','localtime'))
        );

        CREATE TABLE IF NOT EXISTS product_images (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
            image_url  TEXT    NOT NULL,
            sort_order INTEGER DEFAULT 0,
            is_cover   INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS product_variants (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id       INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
            label            TEXT    NOT NULL,
            is_image_linked  INTEGER DEFAULT 0,
            sort_order       INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS product_variant_options (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            variant_id      INTEGER NOT NULL REFERENCES product_variants(id) ON DELETE CASCADE,
            value           TEXT    NOT NULL,
            linked_image_id INTEGER DEFAULT NULL REFERENCES product_images(id) ON DELETE SET NULL,
            sort_order      INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS galleries (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            title      TEXT    NOT NULL,
            category   TEXT    NOT NULL,
            image_url  TEXT    NOT NULL,
            created_at TEXT    DEFAULT (datetime('now','localtime'))
        );
    ");

    // =====================================================================
    // MIGRATIONS — safely add columns to existing databases (ALTER TABLE)
    // SQLite doesn't support IF NOT EXISTS on ALTER, so we catch errors.
    // =====================================================================
    $migrations = [
        "ALTER TABLE products       ADD COLUMN is_active INTEGER DEFAULT 1",
        "ALTER TABLE products       ADD COLUMN stock     INTEGER DEFAULT NULL",
        "ALTER TABLE product_images ADD COLUMN is_cover  INTEGER DEFAULT 0",
    ];
    foreach ($migrations as $sql) {
        try { $pdo->exec($sql); } catch (\PDOException) { /* column already exists — skip */ }
    }

    // =====================================================================
    // SEED: Default admin
    // =====================================================================
    if ((int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn() === 0) {
        $hash = password_hash('wintom2024', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")
            ->execute(['admin', $hash]);
    }

    // =====================================================================
    // SEED: Categories (dynamic — replaces hardcoded array in admin)
    // =====================================================================
    if ((int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() === 0) {
        $default_cats = [
            'Curtain', 'Vitrase & Sheer', 'Roller Blind',
            'Motorized Smart System', 'Wood & Bamboo Blind',
        ];
        $ins_cat = $pdo->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)");
        foreach ($default_cats as $i => $cat) {
            $ins_cat->execute([$cat, $i]);
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
            'category'       => 'Vitrase & Sheer',
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
            'category'       => 'Motorized Smart System',
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
            'category'       => 'Wood & Bamboo Blind',
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
        INSERT INTO products (slug, name, category, badge, ref_code, price, original_price,
                              price_unit, description, features, cover_image, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ins_img  = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_cover) VALUES (?, ?, ?, ?)");
    $ins_var  = $pdo->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, sort_order) VALUES (?, ?, ?, ?)");
    $ins_opt  = $pdo->prepare("INSERT INTO product_variant_options (variant_id, value, sort_order) VALUES (?, ?, ?)");

    foreach ($sample_products as $p) {
        $ins_prod->execute([
            $p['slug'], $p['name'], $p['category'], $p['badge'], $p['ref_code'],
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
        WHERE id = (SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1)
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
