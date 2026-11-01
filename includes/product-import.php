<?php
// includes/product-import.php — Impor produk massal dari CSV (admin › Produk › Impor CSV).
//
// Alur: parse CSV -> validasi tiap baris (pratinjau, belum menyimpan apa pun) -> setelah dikonfirmasi,
// simpan per produk (foto diunduh & dikompres, varian dibuat). Satu baris = satu produk.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/image-upload.php';

const PIMPORT_MAX_FILE   = 1048576;   // 1 MB
const PIMPORT_MAX_ROWS   = 100;
const PIMPORT_MAX_PHOTOS = 8;
const PIMPORT_MAX_VARS   = 5;

/** Kolom template (urutan = urutan di file template). */
const PIMPORT_COLUMNS = [
    'nama_produk', 'kategori', 'badge', 'kode_ref', 'deskripsi', 'fitur', 'status', 'stok',
    'harga_custom', 'satuan_harga', 'ukuran', 'varian_1', 'varian_2', 'varian_3', 'varian_4', 'varian_5', 'foto',
];

/** Nama kolom lain yang dikenali -> nama baku. */
const PIMPORT_ALIASES = [
    'nama' => 'nama_produk', 'name' => 'nama_produk', 'produk' => 'nama_produk',
    'category' => 'kategori', 'kode' => 'kode_ref', 'ref' => 'kode_ref', 'ref_code' => 'kode_ref',
    'description' => 'deskripsi', 'features' => 'fitur', 'spesifikasi' => 'fitur',
    'stock' => 'stok', 'harga' => 'harga_custom', 'price' => 'harga_custom', 'satuan' => 'satuan_harga',
    'size' => 'ukuran', 'sizes' => 'ukuran', 'foto_url' => 'foto', 'gambar' => 'foto', 'photos' => 'foto', 'images' => 'foto',
];

function product_slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/* ------------------------------------------------------------------ TEMPLATE */

/** Isi file template CSV (titik koma, UTF-8 + BOM agar Excel Indonesia membukanya rapi). */
function pimport_template_csv(array $category_names): string {
    $c1 = $category_names[0] ?? 'Curtain';
    $c2 = $category_names[1] ?? $c1;
    $rows = [
        PIMPORT_COLUMNS,
        ['[CONTOH] Gorden Linen Putih', $c1, 'Terlaris', 'WT-001', 'Gorden linen tebal, jatuh lembut dan tahan lama.',
         'Bahan linen 100%|Tidak mudah luntur|Bisa dicuci mesin', 'aktif', '20', '', '',
         '100x200|150x220|200x250', 'Warna: Putih|Krem|Abu-abu', 'Kontrol: Manual|Motorized', '', '', '',
         'https://images.unsplash.com/photo-1513694203232-719a280e022f'],
        ['[CONTOH] Roller Blind Sunscreen', $c2, '', 'WT-002', 'Blind sunscreen untuk ruang kerja.',
         'Kain sunscreen 5%|Mekanisme rantai', 'aktif', '', '450000', 'meter',
         '120x180', 'Warna: Hitam|Abu-abu', '', '', '', '',
         'https://images.unsplash.com/photo-1507652313519-d4e9174996dd|https://images.unsplash.com/photo-1505691938895-1758d7feb511'],
    ];
    $fh = fopen('php://temp', 'r+');
    foreach ($rows as $r) fputcsv($fh, $r, ';', '"', '');
    rewind($fh);
    return "\xEF\xBB\xBF" . stream_get_contents($fh);
}

/* ------------------------------------------------------------------ PARSE */

/**
 * Baca CSV (pemisah , ; atau tab otomatis; UTF-8 atau Windows-1252) -> daftar baris asosiatif.
 * @return array{rows: array<int,array>, error: ?string}
 */
function pimport_parse_csv(string $path): array {
    $raw = (string)@file_get_contents($path);
    if ($raw === '') return ['rows' => [], 'error' => 'File kosong atau tidak bisa dibaca.'];
    if (str_starts_with($raw, "\xEF\xBB\xBF")) $raw = substr($raw, 3);
    if (!mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);

    $first = strtok($raw, "\n") ?: '';
    $delim = ';';
    $best  = -1;
    foreach ([';', ',', "\t"] as $d) {
        $n = substr_count($first, $d);
        if ($n > $best) { $best = $n; $delim = $d; }
    }

    $fh = fopen('php://temp', 'r+');
    fwrite($fh, $raw);
    rewind($fh);

    $header = null; $map = []; $rows = []; $line = 0;
    while (($r = fgetcsv($fh, 0, $delim, '"', '')) !== false) {
        $line++;
        if (!array_filter($r, fn($v) => trim((string)$v) !== '')) continue;   // baris kosong
        if ($header === null) {
            foreach ($r as $i => $h) {
                $k = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$h)));
                $k = trim($k, '_');
                $k = PIMPORT_ALIASES[$k] ?? $k;
                if (in_array($k, PIMPORT_COLUMNS, true) && !in_array($k, $map, true)) $map[$i] = $k;
            }
            $header = true;
            foreach (['nama_produk', 'kategori'] as $must) {
                if (!in_array($must, $map, true)) {
                    return ['rows' => [], 'error' => 'Kolom "' . $must . '" tidak ditemukan di baris pertama. Gunakan file template terbaru.'];
                }
            }
            continue;
        }
        $row = array_fill_keys(PIMPORT_COLUMNS, '');
        foreach ($map as $i => $k) $row[$k] = trim((string)($r[$i] ?? ''));
        $row['_line'] = $line;
        $rows[] = $row;
        if (count($rows) > PIMPORT_MAX_ROWS) {
            return ['rows' => [], 'error' => 'Maksimal ' . PIMPORT_MAX_ROWS . ' produk per impor. Bagi file menjadi beberapa bagian.'];
        }
    }
    if (!$rows) return ['rows' => [], 'error' => 'Tidak ada baris produk di file ini.'];
    return ['rows' => $rows, 'error' => null];
}

/* ------------------------------------------------------------------ VALIDASI */

function pimport_split(string $v): array {
    return array_values(array_filter(array_map('trim', explode('|', $v)), fn($x) => $x !== ''));
}

/**
 * Validasi satu baris. Hasil: ['line', 'name', 'status' => ok|warn|error|skip, 'messages' => [...], 'data' => [...]].
 * @param array<string,int> $cats   nama kategori huruf kecil => id
 * @param array<string,bool> $taken slug yang sudah ada (diperbarui agar duplikat dalam file juga terdeteksi)
 */
function pimport_validate_row(array $r, array $cats, array $cat_names, array &$taken): array {
    $out = ['line' => $r['_line'], 'name' => $r['nama_produk'], 'status' => 'ok', 'messages' => [], 'data' => null];
    $err = [];  $warn = [];

    if (stripos($r['nama_produk'], '[contoh]') === 0) {
        $out['status'] = 'skip'; $out['messages'][] = 'Baris contoh dari template (dilewati).';
        return $out;
    }

    $name = $r['nama_produk'];
    if ($name === '') $err[] = 'Nama produk kosong.';
    elseif (mb_strlen($name) > 255) $err[] = 'Nama produk maksimal 255 karakter.';

    $cat_id = $cats[mb_strtolower($r['kategori'])] ?? 0;
    if ($r['kategori'] === '') $err[] = 'Kategori kosong.';
    elseif (!$cat_id) $err[] = 'Kategori "' . $r['kategori'] . '" tidak ada. Pilihan: ' . implode(', ', $cat_names) . '.';

    $slug = product_slug($name);
    $nkey = 'n:' . mb_strtolower($name);
    if (!$err && $slug === '') $err[] = 'Nama produk harus mengandung huruf atau angka.';
    if (!$err && (isset($taken[$slug]) || isset($taken[$nkey]))) {
        $in_db = ($taken[$slug] ?? $taken[$nkey]) === 'db';
        $out['status'] = 'skip';
        $out['messages'][] = $in_db ? 'Produk dengan nama ini sudah ada (dilewati).' : 'Nama produk dobel di file ini (dilewati).';
        return $out;
    }

    if (mb_strlen($r['badge']) > 100) $err[] = 'Badge maksimal 100 karakter.';
    if (mb_strlen($r['kode_ref']) > 50) $err[] = 'Kode ref maksimal 50 karakter.';

    // Status
    $st = mb_strtolower($r['status']);
    if ($st === '' || in_array($st, ['aktif', 'active', '1', 'ya', 'y', 'yes'], true)) $active = 1;
    elseif (in_array($st, ['nonaktif', 'non-aktif', 'tidak aktif', 'inactive', '0', 'tidak', 'n', 'no'], true)) $active = 0;
    else { $active = 1; $err[] = 'Status "' . $r['status'] . '" tidak dikenal (isi: aktif atau nonaktif).'; }

    // Stok
    $stock = null;
    if ($r['stok'] !== '') {
        if (ctype_digit($r['stok'])) $stock = (int)$r['stok'];
        else $err[] = 'Stok harus bilangan bulat 0 atau lebih.';
    }

    // Harga custom (kosong = ikut harga per meter kategori)
    $use_cat = 1; $price = 0; $unit = '/ meter';
    if ($r['harga_custom'] !== '') {
        $price = (int)preg_replace('/\D/', '', preg_replace('/[,.]\d{1,2}$/', '', $r['harga_custom']));   // "Rp 450.000" -> 450000
        if ($price <= 0) $err[] = 'Harga custom harus angka lebih dari 0 (atau kosongkan agar ikut harga kategori).';
        else {
            $use_cat = 0;
            $unit = trim(ltrim($r['satuan_harga'], "/ \t")) ?: 'unit';
            if (mb_strlen($unit) > 50) $err[] = 'Satuan harga maksimal 50 karakter.';
        }
    } elseif ($r['satuan_harga'] !== '') {
        $warn[] = 'Satuan harga diabaikan karena harga custom kosong.';
    }

    // Ukuran: 100x200|150x220
    $sizes = [];
    foreach (pimport_split($r['ukuran']) as $s) {
        if (!preg_match('/^(\d{1,5}(?:[.,]\d)?)\s*[x×*]\s*(\d{1,5}(?:[.,]\d)?)(?:\s*cm)?$/iu', $s, $m)) {
            $err[] = 'Ukuran "' . $s . '" tidak valid (contoh: 100x200, dalam cm).'; continue;
        }
        $w = (float)str_replace(',', '.', $m[1]); $h = (float)str_replace(',', '.', $m[2]);
        if ($w <= 0 || $h <= 0 || $w > 10000 || $h > 10000) { $err[] = 'Ukuran "' . $s . '" harus 1–10.000 cm.'; continue; }
        $sizes[$w . 'x' . $h] = [$w, $h];
    }

    // Varian: "Warna: Putih|Krem|Abu-abu"
    $variants = [];
    for ($i = 1; $i <= PIMPORT_MAX_VARS; $i++) {
        $v = $r['varian_' . $i];
        if ($v === '') continue;
        if (!str_contains($v, ':')) { $err[] = 'Varian ' . $i . ' harus berformat "Nama: pilihan1|pilihan2".'; continue; }
        [$label, $opts] = array_map('trim', explode(':', $v, 2));
        $opts = pimport_split($opts);
        if ($label === '' || !$opts) { $err[] = 'Varian ' . $i . ' butuh nama dan minimal satu pilihan.'; continue; }
        if (in_array(mb_strtolower($label), ['ukuran', 'size'], true)) { $err[] = 'Varian ' . $i . ': "Ukuran" diisi lewat kolom ukuran.'; continue; }
        if (mb_strlen($label) > 100 || array_filter($opts, fn($o) => mb_strlen($o) > 255)) { $err[] = 'Varian ' . $i . ' terlalu panjang.'; continue; }
        $variants[] = ['label' => $label, 'options' => array_values(array_unique($opts))];
    }

    // Foto: URL dipisah |
    $photos = [];
    foreach (preg_split('/[|\s]+/', $r['foto'], -1, PREG_SPLIT_NO_EMPTY) as $u) {
        $ok = filter_var($u, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $u) && strlen($u) <= 500;
        if (!$ok) { $err[] = 'Alamat foto tidak valid: ' . mb_strimwidth($u, 0, 60, '…'); continue; }
        $photos[] = $u;
    }
    if (count($photos) > PIMPORT_MAX_PHOTOS) {
        $warn[] = 'Hanya ' . PIMPORT_MAX_PHOTOS . ' foto pertama yang dipakai.';
        $photos = array_slice($photos, 0, PIMPORT_MAX_PHOTOS);
    }
    if (!$photos && !$err) {
        $warn[] = 'Tanpa foto: produk diimpor sebagai Nonaktif. Tambahkan foto lewat Edit, lalu aktifkan.';
        $active = 0;
    }

    if ($err) { $out['status'] = 'error'; $out['messages'] = $err; return $out; }

    $taken[$slug] = $taken[$nkey] = 'file';
    $out['status']   = $warn ? 'warn' : 'ok';
    $out['messages'] = $warn;
    $out['data'] = [
        'slug' => $slug, 'name' => $name, 'category_id' => $cat_id, 'badge' => $r['badge'], 'ref_code' => $r['kode_ref'],
        'description' => $r['deskripsi'], 'features' => implode('|', pimport_split($r['fitur'])),
        'is_active' => $active, 'stock' => $stock, 'use_cat_price' => $use_cat, 'price' => $price, 'unit' => $unit,
        'sizes' => array_values($sizes), 'variants' => $variants, 'photos' => $photos,
    ];
    return $out;
}

/** Validasi seluruh isi file. */
function pimport_validate(PDO $db, array $rows): array {
    $cats = []; $names = [];
    foreach ($db->query('SELECT id, name FROM categories ORDER BY sort_order, name')->fetchAll() as $c) {
        $cats[mb_strtolower($c['name'])] = (int)$c['id'];
        $names[] = $c['name'];
    }
    $taken = [];
    foreach ($db->query('SELECT slug, name FROM products')->fetchAll() as $p) {
        $taken[$p['slug']] = 'db';
        $taken['n:' . mb_strtolower($p['name'])] = 'db';
    }
    $out = [];
    foreach ($rows as $r) $out[] = pimport_validate_row($r, $cats, $names, $taken);
    return $out;
}

/* ------------------------------------------------------------------ UNDUH FOTO */

/** IP publik? (tolak jaringan internal/lokal agar fitur ini tidak bisa dipakai memindai server) */
function pimport_public_ip(string $ip): bool {
    return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

/**
 * Unduh satu foto dari URL, kompres, simpan ke /uploads/products. Unsplash dipakai langsung (tanpa unduh).
 * @return string|null URL relatif atau URL Unsplash; $error terisi bila gagal
 */
function pimport_fetch_photo(string $url, ?string &$error = null): ?string {
    $error = null;
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    if ($host === 'images.unsplash.com') return $url;

    $tmp = tempnam(sys_get_temp_dir(), 'wtimp_');
    try {
        $cur = $url;
        for ($hop = 0; $hop < 4; $hop++) {
            $p = parse_url($cur);
            $h = strtolower($p['host'] ?? '');
            $scheme = strtolower($p['scheme'] ?? '');
            if (!in_array($scheme, ['http', 'https'], true) || $h === '') { $error = 'alamat tidak valid'; return null; }
            $port = $p['port'] ?? ($scheme === 'https' ? 443 : 80);
            $ips  = filter_var($h, FILTER_VALIDATE_IP) ? [$h] : (gethostbynamel($h) ?: []);
            if (!$ips || array_filter($ips, fn($ip) => !pimport_public_ip($ip))) { $error = 'alamat tidak diizinkan'; return null; }

            $fh = fopen($tmp, 'w+'); $size = 0; $over = false;
            $ch = curl_init($cur);
            curl_setopt_array($ch, [
                CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 12, CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'WintomImporter/1.0', CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => [$h . ':' . $port . ':' . $ips[0]],   // kunci ke IP yang sudah diperiksa
                CURLOPT_HEADER => false,
                CURLOPT_WRITEFUNCTION => function ($c, $chunk) use ($fh, &$size, &$over) {
                    $size += strlen($chunk);
                    if ($size > IMAGE_MAX_BYTES) { $over = true; return 0; }
                    return fwrite($fh, $chunk);
                },
            ]);
            curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $loc  = (string)curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            $cerr = curl_error($ch);
            curl_close($ch); fclose($fh);

            if ($over) { $error = 'lebih dari 5 MB'; return null; }
            if ($code >= 300 && $code < 400 && $loc !== '') { $cur = $loc; continue; }
            if ($code !== 200) { $error = $cerr ?: ('HTTP ' . $code); return null; }
            break;
        }
        if ($code !== 200) { $error = 'terlalu banyak pengalihan'; return null; }

        $stored = store_uploaded_image(['name' => basename($p['path'] ?? 'foto'), 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => filesize($tmp)], 'products', $e);
        if (!$stored) { $error = $e ?: 'bukan gambar yang valid'; return null; }
        return $stored;
    } finally {
        @unlink($tmp);
    }
}

/* ------------------------------------------------------------------ SIMPAN */

/**
 * Simpan satu produk hasil validasi.
 * @return array{id:int, warnings:string[]}
 * @throws Throwable bila database gagal (foto yang sempat diunduh dibersihkan)
 */
function pimport_save(PDO $db, array $d): array {
    $warnings = []; $saved = []; $local = [];
    foreach ($d['photos'] as $i => $u) {
        $url = pimport_fetch_photo($u, $e);
        if ($url) { $saved[] = $url; if (str_starts_with($url, '/uploads/')) $local[] = $url; }
        else $warnings[] = 'Foto #' . ($i + 1) . ' gagal (' . $e . ').';
    }
    $active = $d['is_active'];
    if (!$saved && $d['photos']) { $active = 0; $warnings[] = 'Semua foto gagal diunduh: produk dibuat Nonaktif.'; }

    $db->beginTransaction();
    try {
        $slug = $d['slug']; $n = 2;
        $chk = $db->prepare('SELECT 1 FROM products WHERE slug = ?');
        while ($chk->execute([$slug]) && $chk->fetchColumn()) $slug = $d['slug'] . '-' . $n++;

        $db->prepare('INSERT INTO products (slug,name,category_id,badge,ref_code,price,original_price,price_unit,use_category_price,description,features,cover_image,is_active,stock)
                      VALUES (?,?,?,?,?,?,NULL,?,?,?,?,?,?,?)')
           ->execute([$slug, $d['name'], $d['category_id'], $d['badge'], $d['ref_code'], $d['use_cat_price'] ? 0 : $d['price'],
                      $d['unit'], $d['use_cat_price'], $d['description'], $d['features'], $saved[0] ?? null, $active, $d['stock']]);
        $pid = (int)$db->lastInsertId();

        $ins = $db->prepare('INSERT INTO product_images (product_id, image_url, sort_order, is_cover) VALUES (?,?,?,?)');
        foreach ($saved as $i => $u) $ins->execute([$pid, $u, $i, $i === 0 ? 1 : 0]);

        $insVar = $db->prepare('INSERT INTO product_variants (product_id, label, is_image_linked, is_size, sort_order) VALUES (?,?,0,?,?)');
        $insOpt = $db->prepare('INSERT INTO product_variant_options (variant_id, value, width_cm, height_cm, sort_order) VALUES (?,?,?,?,?)');
        if ($d['sizes']) {
            $insVar->execute([$pid, 'Ukuran', 1, 0]);
            $vid = (int)$db->lastInsertId();
            foreach ($d['sizes'] as $i => [$w, $h]) $insOpt->execute([$vid, format_size_label($w, $h), $w, $h, $i]);
        }
        foreach ($d['variants'] as $vi => $v) {
            $insVar->execute([$pid, $v['label'], 0, $vi + 1]);
            $vid = (int)$db->lastInsertId();
            foreach ($v['options'] as $oi => $o) $insOpt->execute([$vid, $o, null, null, $oi]);
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        foreach ($local as $u) { $p = img_upload_path($u); if ($p) image_delete_with_variants($p); }
        throw $e;
    }
    return ['id' => $pid, 'warnings' => $warnings];
}
