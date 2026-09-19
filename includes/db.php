<?php
// Wintom Curtain — Database Connection & Initialization

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
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            badge TEXT,
            ref_code TEXT,
            price INTEGER NOT NULL,
            original_price INTEGER,
            price_unit TEXT DEFAULT '/ meter',
            description TEXT,
            features TEXT,
            options_json TEXT,
            cover_image TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            image_url TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS galleries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            image_url TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Buat default admin jika belum ada
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    if ((int)$stmt->fetchColumn() === 0) {
        $hash = password_hash('wintom2024', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)")
            ->execute(['admin', $hash]);
    }

    // Seed default curated products jika tabel masih kosong
    $prod_count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($prod_count === 0) {
        $sample_products = [
            [
                'slug' => 'aura-drape-french-linen',
                'name' => 'Aura Drape — French Linen Blend',
                'category' => 'Curtain',
                'badge' => 'Curator Choice',
                'ref_code' => 'WNT-CR-01',
                'price' => 385000,
                'original_price' => 450000,
                'price_unit' => '/ meter',
                'description' => 'Tirai drapery mewah berbahan perpaduan linen Belgia dengan tenunan berpola rapat yang jatuh dengan anggun. Memberikan nuansa hangat, tekstur alami organik, dan redaman akustik ruangan yang optimal.',
                'features' => "Material 65% Belgian Linen, 35% Poly-Loom|S-Fold Wave / Ripple Fold Hemming Presisi|Semi-Blackout (80% Light Reduction)|Termasuk Rel Arsitektural Silent-Glide",
                'options_json' => json_encode(['colors' => ['Oatmeal Cream', 'Warm Sand', 'Slate Charcoal', 'Muted Olive'], 'stitch' => ['Ripple Fold 2.2x', 'Double Pinch Pleat', 'Eyelet Minimalist']]),
                'cover_image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',
                    'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85',
                    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85'
                ]
            ],
            [
                'slug' => 'velour-noir-100-blackout',
                'name' => 'Velour Noir — 100% Total Blackout',
                'category' => 'Curtain',
                'badge' => '100% Blackout',
                'ref_code' => 'WNT-CR-02',
                'price' => 495000,
                'original_price' => 580000,
                'price_unit' => '/ meter',
                'description' => 'Solusi kegelapan total mutlak untuk master bedroom dan private home cinema. Dilapisi 3-pass acrylic thermal layer yang meredam suhu panas sinar matahari dan kebisingan jalan perkotaan.',
                'features' => "100% Zero-Light Transmittance Tested|Thermal Barrier (Menurunkan panas ruangan hingga 4°C)|Lapisan Anti-Statik Debu & Tungau|Double Weight Lead-Chain Bottom Hem",
                'options_json' => json_encode(['colors' => ['Midnight Navy', 'Deep Espresso', 'Anthracite Dark Grey', 'Ivory Cashmere'], 'stitch' => ['Ripple Fold 2.5x Fullness', 'Deep Pinch Pleat']]),
                'cover_image' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',
                    'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=1200&q=85'
                ]
            ],
            [
                'slug' => 'lumiere-sheer-ripple-fold',
                'name' => 'Lumière Sheer — Swiss Voile Vitrase',
                'category' => 'Vitrase & Sheer',
                'badge' => 'Bestseller',
                'ref_code' => 'WNT-SH-01',
                'price' => 265000,
                'original_price' => 310000,
                'price_unit' => '/ meter',
                'description' => 'Vitrase voile tenun halus bermotif mikroskopis lembut asal Swiss yang meneruskan cahaya alami lembut ke dalam hunian tanpa menyilaukan mata, sekaligus menjaga privasi ruang keluarga di siang hari.',
                'features' => "Ultra-Light Swiss Weave Micro-fiber|Difusi Cahaya Alami Matahari yang Lembut|Gelombang Lipatan Ripple Fold Sempurna & Simetris|Tahan Cuci & Anti-Kusut",
                'options_json' => json_encode(['colors' => ['Pure Snow White', 'Warm Alabaster', 'Champagne Tint', 'Soft Greige'], 'stitch' => ['Continuous S-Fold Wave', 'Tailored Box Pleat']]),
                'cover_image' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',
                    'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85'
                ]
            ],
            [
                'slug' => 'aero-roller-blind-sunscreen',
                'name' => 'Aero Blind — Minimalist Sunscreen 5%',
                'category' => 'Roller Blind',
                'badge' => 'Modern Minimalist',
                'ref_code' => 'WNT-RB-01',
                'price' => 320000,
                'original_price' => 380000,
                'price_unit' => '/ m²',
                'description' => 'Roller blind berestetika arsitektural bersih dengan keterbukaan tenun 5%. Menghalau silau matahari dan 95% radiasi UV berbahaya sembari tetap mempertahankan pemandangan ke luar jendela secara jernih.',
                'features' => "95% UV Rejection & Glare Filter|Komponen Heavy-Duty Spring & Tube Aluminium 38mm|Sistem Tarikan Rantai Stainless Steel Anti-Macet|Cocok untuk Ruang Kerja & High-Rise Apartment",
                'options_json' => json_encode(['colors' => ['Mono White', 'Ash Grey', 'Dark Bronze Charcoal', 'Sand Dune'], 'control' => ['Manual Stainless Chain', 'Cordless Spring Assist']]),
                'cover_image' => 'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',
                    'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85'
                ]
            ],
            [
                'slug' => 'zenith-motorized-somfy-system',
                'name' => 'Zenith Smart Motorized Curtain System',
                'category' => 'Motorized Smart System',
                'badge' => 'Smart Home Ready',
                'ref_code' => 'WNT-SM-01',
                'price' => 2750000,
                'original_price' => 3200000,
                'price_unit' => '/ set track',
                'description' => 'Sistem motorisasi tirai kelas dunia bertenaga Somfy / Tuya Ultra-Quiet Motor (< 30dB). Terintegrasi penuh dengan Apple HomeKit, Google Home, remote multi-channel, dan sensor matahari otomatis.',
                'features' => "Motor Whisper-Quiet (<30dB) dengan Soft-Start & Stop|Kompatibel Apple HomeKit, Google Assistant, Tuya|Touch Motion Assist (Tarik lembut kain langsung jalan)|Beban Maksimum Tirai hingga 60 Kilogram",
                'options_json' => json_encode(['motor_brand' => ['Somfy France WireFree', 'Tuya Zigbee Smart Ultra', 'Dooya RS485 Pro'], 'track_type' => ['Lurus (Straight)', 'L-Shape Curved 90°', 'Double Track (Drape + Sheer)']]),
                'cover_image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',
                    'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85'
                ]
            ],
            [
                'slug' => 'komorebi-paulownia-wood-blind',
                'name' => 'Komorebi Natural Paulownia Wood Blind',
                'category' => 'Wood & Bamboo Blind',
                'badge' => 'Eco-Natural Wood',
                'ref_code' => 'WNT-WB-01',
                'price' => 540000,
                'original_price' => 620000,
                'price_unit' => '/ m²',
                'description' => 'Bilah kayu Paulownia solid 50mm yang ringan dan tidak membebani konstruksi jendela. Memberikan siluet bayangan matahari geometris tropis khas resor bintang lima dengan pernis tahan kelembapan.',
                'features' => "Kayu Solid Paulownia 50mm Ringan & Anti-Melengkung|Pernis UV 5-Layer Tahan Cuaca Tropis Lembab|Pilihan Pita Fabric Ladder Tape Elegan|Headrail Profil Baja Kokoh Tertutup Valance Kayu",
                'options_json' => json_encode(['colors' => ['Natural Oak', 'Warm Teak', 'Ebony Charcoal', 'Nordic White'], 'tape' => ['Matching Fabric Tape (38mm)', 'Hidden Cord Minimalist']]),
                'cover_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',
                'gallery' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',
                    'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85'
                ]
            ]
        ];

        $ins_prod = $pdo->prepare("
            INSERT INTO products (slug, name, category, badge, ref_code, price, original_price, price_unit, description, features, options_json, cover_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)");

        foreach ($sample_products as $p) {
            $ins_prod->execute([
                $p['slug'], $p['name'], $p['category'], $p['badge'], $p['ref_code'],
                $p['price'], $p['original_price'], $p['price_unit'], $p['description'],
                $p['features'], $p['options_json'], $p['cover_image']
            ]);
            $new_id = (int)$pdo->lastInsertId();
            if (!empty($p['gallery'])) {
                foreach ($p['gallery'] as $idx => $g_url) {
                    $ins_img->execute([$new_id, $g_url, $idx]);
                }
            }
        }
    }
}

