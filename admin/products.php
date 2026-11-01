<?php
// admin/products.php — Manajemen Produk CRUD (Variant System Overhaul)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/image-upload.php';
require_once __DIR__ . '/../includes/admin-actions.php';
require_once __DIR__ . '/../includes/admin-table.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

$db  = get_db();
$msg = '';
$err = '';

const MAX_EXTRA_VARIANTS = 5;   // varian selain Ukuran (Warna, Kontrol, dll.)

/* ============================================================
   HELPERS
   ============================================================ */
function fmt_price(int $p): string { return 'Rp ' . number_format($p, 0, ',', '.'); }

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function delete_file(string $url): void {
    if (str_starts_with($url, '/uploads/')) {
        $path = __DIR__ . '/..' . $url;
        $real = realpath($path);
        $base = realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR;
        if ($real && strpos($real, $base) === 0) image_delete_with_variants($real);
    }
}

/**
 * Cover produk = foto galeri bertanda is_cover. Jika belum ada (produk baru / cover dihapus),
 * foto pertama otomatis jadi cover. products.cover_image disinkronkan untuk kartu & daftar produk.
 */
function sync_cover(PDO $db, int $pid): void {
    $cur = $db->prepare("SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC LIMIT 1");
    $cur->execute([$pid]);
    $img = $cur->fetch();
    $db->prepare("UPDATE product_images SET is_cover = (id = ?) WHERE product_id = ?")->execute([$img['id'] ?? 0, $pid]);
    $db->prepare("UPDATE products SET cover_image = ? WHERE id = ?")->execute([$img['image_url'] ?? null, $pid]);
}

/* ============================================================
   LOAD CATEGORIES FROM DB
   ============================================================ */
$categories = $db->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();

/* ============================================================
   ACTION: DELETE PRODUCT
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id > 0) {
        $imgs = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll() as $img) delete_file($img['image_url']);
        $cover = $db->prepare("SELECT cover_image FROM products WHERE id = ?");
        $cover->execute([$id]);
        $row = $cover->fetch();
        if ($row && $row['cover_image']) delete_file($row['cover_image']);   // produk hasil impor bisa belum punya foto
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $msg = 'Produk berhasil dihapus.';
    }
}

/* ============================================================
   ACTION: TOGGLE is_active
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'toggle_active') {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id > 0) {
        $cur = $db->prepare("SELECT is_active FROM products WHERE id = ?");
        $cur->execute([$id]);
        $r = $cur->fetch();
        if ($r) {
            $new_status = $r['is_active'] ? 0 : 1;
            $db->prepare("UPDATE products SET is_active = ? WHERE id = ?")->execute([$new_status, $id]);
            $msg = 'Status produk berhasil diubah.';
        }
    }
}

/* ============================================================
   ACTION: DELETE GALLERY IMAGE (via POST)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'del_img') {
    $del_img_id = (int)($_POST['image_id'] ?? 0);
    $pid_for_del = (int)($_POST['product_id'] ?? 0);
    if ($del_img_id > 0 && $pid_for_del > 0) {
        $r = $db->prepare("SELECT image_url FROM product_images WHERE id=? AND product_id=?");
        $r->execute([$del_img_id, $pid_for_del]); $row = $r->fetch();
        if ($row) {
            $url = $row['image_url'];
            delete_file($url);
            $db->prepare("DELETE FROM product_images WHERE id=?")->execute([$del_img_id]);
            sync_cover($db, $pid_for_del);   // cover terhapus -> foto berikutnya jadi cover
        }
    }
    header("Location: products.php?edit=$pid_for_del&msg=img_deleted"); exit;
}

/* ============================================================
   ACTION: SET COVER (via POST)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'set_cover') {
    $img_id = (int)($_POST['image_id'] ?? 0);
    $pid_for_cover = (int)($_POST['product_id'] ?? 0);
    if ($pid_for_cover > 0 && $img_id > 0) {
        // Unset all covers for this product
        $db->prepare("UPDATE product_images SET is_cover = 0 WHERE product_id = ?")->execute([$pid_for_cover]);
        // Set the selected one
        $db->prepare("UPDATE product_images SET is_cover = 1 WHERE id = ? AND product_id = ?")->execute([$img_id, $pid_for_cover]);
        // Update cover_image in products table for backward compat
        $img_row = $db->prepare("SELECT image_url FROM product_images WHERE id = ? AND product_id = ?");
        $img_row->execute([$img_id, $pid_for_cover]);
        $img_data = $img_row->fetch();
        if ($img_data) {
            $db->prepare("UPDATE products SET cover_image = ? WHERE id = ?")->execute([$img_data['image_url'], $pid_for_cover]);
        }
    }
    header("Location: products.php?edit=$pid_for_cover&msg=cover_set"); exit;
}

/* ============================================================
   EDITING STATE
   ============================================================ */
$editing   = null;
$edit_id   = (int)($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $s = $db->prepare("SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?");
    $s->execute([$edit_id]);
    $editing = $s->fetch();
    if (!$editing) { $edit_id = 0; }
}

/* ============================================================
   ACTION: SAVE PRODUCT (ADD / EDIT)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'save') {
    $pid          = (int)($_POST['product_id'] ?? 0);
    $name         = trim($_POST['name'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $badge        = trim($_POST['badge'] ?? '');
    $ref_code     = trim($_POST['ref_code'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $features_raw = trim($_POST['features'] ?? '');
    $features     = implode('|', array_filter(array_map('trim', explode("\n", $features_raw))));
    $is_active    = (int)($_POST['is_active'] ?? 1);
    // Harga: ikut kategori (default) atau custom per produk
    $use_cat_price = isset($_POST['use_category_price']) ? 1 : 0;
    $custom_price  = (int)preg_replace('/\D/', '', (string)($_POST['custom_price'] ?? ''));
    $custom_unit   = trim(ltrim((string)($_POST['custom_unit'] ?? ''), "/ \t")) ?: 'unit';   // "/" ditampilkan otomatis
    $stock        = trim($_POST['stock'] ?? '') === '' ? null : (int)$_POST['stock'];

    // Harga tidak lagi per produk: yang dipakai harga per meter kategori (admin › Kategori).
    if (!$name || !$category) {
        $err = 'Nama dan kategori wajib diisi.';
    } elseif (!$use_cat_price && $custom_price <= 0) {
        $err = 'Isi harga custom (lebih dari 0), atau centang "Ikuti harga kategori".';
    } elseif (mb_strlen($custom_unit) > 50) {
        $err = 'Satuan harga maksimal 50 karakter.';
    } elseif ($pid === 0 && !has_uploaded_file($_FILES['gallery_images'] ?? null)) {
        $err = 'Upload minimal satu foto produk (foto pertama otomatis jadi cover).';
    } else {
        $slug = slugify($name);
        if ($pid === 0) {
            $base_slug = $slug; $i = 2;
            $chk = $db->prepare("SELECT id FROM products WHERE slug=?");
            while ($chk->execute([$slug]) && $chk->fetchColumn()) {
                $slug = $base_slug . '-' . $i++;
            }
        } else {
            // Saat edit, slug lama dipertahankan agar URL produk (link yang sudah dibagikan) tidak berubah
            $cur_slug = $db->prepare("SELECT slug FROM products WHERE id=?");
            $cur_slug->execute([$pid]);
            $slug = (string)$cur_slug->fetchColumn() ?: $slug;
        }

        // Validate category exists
        if (!$err) {
            $cat_chk = $db->prepare("SELECT id FROM categories WHERE name=?");
            $cat_chk->execute([$category]);
            $category_id = (int)$cat_chk->fetchColumn();
            if ($category_id === 0) {
                $err = 'Kategori tidak ditemukan.';
            }
        }

        // Validate is_active (only 0 or 1)
        if (!$err && !in_array($is_active, [0, 1])) {
            $err = 'Status produk tidak valid.';
        }

        // Validate stock (non-negative if set)
        if (!$err && $stock !== null && $stock < 0) {
            $stock = 0;
        }

        // Start transaction for all db operations
        if (!$err) {
            $db->beginTransaction();
            try {

        if (!$err) {
            if ($pid === 0) {
                // INSERT — kolom harga lama diisi nilai netral (tidak ditampilkan lagi)
                $ins = $db->prepare("INSERT INTO products (slug,name,category_id,badge,ref_code,price,original_price,price_unit,use_category_price,description,features,cover_image,is_active,stock) VALUES (?,?,?,?,?,?,NULL,?,?,?,?,NULL,?,?)");
                $ins->execute([$slug,$name,$category_id,$badge,$ref_code,
                               $use_cat_price ? 0 : $custom_price, $use_cat_price ? '/ meter' : $custom_unit, $use_cat_price,
                               $description,$features,$is_active,$stock]);
                $pid = (int)$db->lastInsertId();
                $msg = 'Produk berhasil ditambahkan.';
            } else {
                $db->prepare("UPDATE products SET slug=?,name=?,category_id=?,badge=?,ref_code=?,description=?,features=?,is_active=?,stock=?,use_category_price=? WHERE id=?")
                   ->execute([$slug,$name,$category_id,$badge,$ref_code,$description,$features,$is_active,$stock,$use_cat_price,$pid]);
                if (!$use_cat_price) {   // harga lama disimpan apa adanya saat kembali ke harga kategori
                    $db->prepare("UPDATE products SET price=?, original_price=NULL, price_unit=? WHERE id=?")->execute([$custom_price, $custom_unit, $pid]);
                }
                $msg = 'Produk berhasil diperbarui.';
            }
        }

        // Upload additional gallery images
        $upload_errors = [];
        if ($pid > 0 && !$err && has_uploaded_file($_FILES['gallery_images'] ?? null)) {
            $ins_img = $db->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?,?,?)");
            $next = (int)$db->query("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id=$pid")->fetchColumn();
            foreach (uploaded_files($_FILES['gallery_images']) as $f) {
                if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
                $url = store_uploaded_image($f, 'products', $e);   // validasi 5 MB + kompres standar
                if ($url) $ins_img->execute([$pid, $url, $next++]);
                elseif ($e) $upload_errors[] = $f['name'] . ': ' . $e;
            }
        }

        // Cover dari foto galeri (foto pertama bila belum dipilih)
        if ($pid > 0 && !$err) {
            sync_cover($db, $pid);
            $has_img = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
            $has_img->execute([$pid]);
            if (!$has_img->fetchColumn() && $is_active) {
                // Produk tanpa foto boleh disimpan selama Nonaktif (mis. hasil impor CSV); aktif butuh minimal 1 foto
                $err = 'Produk aktif butuh minimal satu foto. Tambahkan foto, atau ubah status menjadi Nonaktif.'
                     . ($upload_errors ? ' (' . implode('; ', $upload_errors) . ')' : '');
            }
        }

        // =====================================================
        // SAVE VARIANTS
        // 1) "Ukuran" — parameter tetap: daftar lebar × tinggi (cm). "Custom" otomatis di halaman produk.
        // 2) Varian lain (Warna, Kontrol, dll.) — maksimal MAX_EXTRA_VARIANTS.
        // =====================================================
        if ($pid > 0 && !$err) {
            $db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$pid]);

            // --- Ukuran ---
            $sizes = [];
            $size_w = $_POST['size_w'] ?? [];
            $size_h = $_POST['size_h'] ?? [];
            foreach ($size_w as $si => $w_raw) {
                $w_raw = trim(str_replace(',', '.', (string)$w_raw));
                $h_raw = trim(str_replace(',', '.', (string)($size_h[$si] ?? '')));
                if ($w_raw === '' && $h_raw === '') continue;
                $ok = fn($v) => preg_match('/^\d{1,5}(\.\d)?$/', $v) && (float)$v > 0 && (float)$v <= 10000;
                if (!$ok($w_raw) || !$ok($h_raw)) {
                    $err = 'Ukuran #' . ($si + 1) . ': lebar & tinggi harus angka 1–10.000 cm.';
                    break;
                }
                $key = (float)$w_raw . 'x' . (float)$h_raw;
                $sizes[$key] = [(float)$w_raw, (float)$h_raw];   // duplikat otomatis diabaikan
            }
            if (!$err && $sizes) {
                $db->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, is_size, sort_order) VALUES (?, 'Ukuran', 0, 1, 0)")
                   ->execute([$pid]);
                $size_vid = (int)$db->lastInsertId();
                $ins_size = $db->prepare("INSERT INTO product_variant_options (variant_id, value, width_cm, height_cm, sort_order) VALUES (?, ?, ?, ?, ?)");
                $so = 0;
                foreach ($sizes as [$w, $h]) {
                    $ins_size->execute([$size_vid, format_size_label($w, $h), $w, $h, $so++]);
                }
            }

            // --- Varian lain ---
            $variant_labels = $_POST['variant_label'] ?? [];
            $variant_image_linked = $_POST['variant_image_linked'] ?? [];
            $variant_options = $_POST['variant_options'] ?? [];
            $filled = array_filter($variant_labels, fn($l) => trim((string)$l) !== '');

            if (!$err && count($filled) > MAX_EXTRA_VARIANTS) {
                $err = 'Maksimum ' . MAX_EXTRA_VARIANTS . ' varian selain Ukuran.';
            } elseif (!$err) {
                $valid_images = $db->prepare("SELECT id FROM product_images WHERE product_id = ?");
                $valid_images->execute([$pid]);
                $valid_img_ids = array_column($valid_images->fetchAll(), 'id');
                $ins_var = $db->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, is_size, sort_order) VALUES (?, ?, ?, 0, ?)");
                $ins_opt = $db->prepare("INSERT INTO product_variant_options (variant_id, value, linked_image_id, sort_order) VALUES (?, ?, ?, ?)");
                $sort = 1;

                foreach ($variant_labels as $vi => $label) {
                    $label = trim($label);
                    if (!$label) continue;
                    if (in_array(mb_strtolower($label), ['ukuran', 'size'], true)) {
                        $err = 'Label "' . $label . '" dipakai varian Ukuran tetap. Gunakan nama lain.';
                        break;
                    }
                    $is_img_linked = in_array((string)$vi, $variant_image_linked) ? 1 : 0;
                    $ins_var->execute([$pid, $label, $is_img_linked, $sort++]);
                    $vid = (int)$db->lastInsertId();

                    $options = $variant_options[$vi] ?? [];
                    foreach ($options as $oi => $opt_val) {
                        $opt_val = trim($opt_val);
                        if (!$opt_val) continue;
                        $linked_img_id = null;
                        $link_val = $is_img_linked ? (int)($_POST['variant_opt_image'][$vi][$oi] ?? 0) : 0;
                        if ($link_val > 0 && in_array($link_val, $valid_img_ids)) {
                            $linked_img_id = $link_val;
                        }
                        $ins_opt->execute([$vid, $opt_val, $linked_img_id, $oi]);
                    }
                }
            }
        }

        // Refresh editing
        if ($pid > 0 && !$err) {
            $s = $db->prepare("SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id=?");
            $s->execute([$pid]); $editing = $s->fetch(); $edit_id = $pid;
        }

            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                error_log($e->getMessage());
                $err = 'Gagal menyimpan produk. Coba lagi.';
            }
            if ($db->inTransaction()) { $err ? $db->rollBack() : $db->commit(); }
            if ($err) $msg = '';
            elseif (!empty($upload_errors)) $msg .= ' Foto dilewati: ' . implode('; ', $upload_errors);
        }
    }
}

if (isset($_GET['msg'])) $msg = ['img_deleted' => 'Foto dihapus', 'cover_set' => 'Cover berhasil diubah'][$_GET['msg']] ?? '';

/* ============================================================
   LOAD PRODUCTS LIST
   ============================================================ */
$pg = admin_paginate((int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn());
$products = $db->query("SELECT p.*, c.name AS category, c.price_per_meter FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC, p.id DESC LIMIT " . (int)$pg['per'] . " OFFSET " . (int)$pg['offset'])->fetchAll();

// Load gallery images and variants for editing product
$edit_images   = [];
$edit_variants = [];
if ($editing) {
    $gi = $db->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC");
    $gi->execute([$editing['id']]);
    $edit_images = $gi->fetchAll();

    // Load variants + options
    $vq = $db->prepare("SELECT * FROM product_variants WHERE product_id=? ORDER BY sort_order ASC");
    $vq->execute([$editing['id']]);
    $vars = $vq->fetchAll();
    foreach ($vars as $v) {
        $oq = $db->prepare("SELECT * FROM product_variant_options WHERE variant_id=? ORDER BY sort_order ASC");
        $oq->execute([$v['id']]);
        $v['options'] = $oq->fetchAll();
        $edit_variants[] = $v;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= $editing ? 'Edit Produk' : 'Manajemen Produk' ?> — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>
*{box-sizing:border-box;} textarea,input,select{outline:none;}
.label{display:block;font-family:Arial,Helvetica,sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;}
.input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#242424;background:#fff;transition:border-color 0.15s;}
.input:focus{border-color:#860524;}
select.input{appearance:auto;}
.peer:checked + .sw-track{background:#10b981;}
.peer:checked + .sw-track .sw-knob{transform:translateX(20px);}
.variant-card{border:1px solid #ece8e1;border-radius:6px;background:#fff;padding:16px;margin-bottom:12px;}
.variant-card.is-size{border-left:3px solid #860524;}
.opt-row{display:flex;gap:8px;align-items:center;margin-top:6px;}
.opt-input{flex:1;border:1px solid #ece8e1;border-radius:3px;padding:6px 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#242424;background:#fff;}
.opt-input:focus{border-color:#860524;}
.btn-sm{padding:4px 10px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.5px;text-transform:uppercase;border-radius:3px;cursor:pointer;transition:all 0.15s;}
.cover-badge{position:absolute;top:4px;left:4px;background:#860524;color:#fff;font-size:9px;letter-spacing:1px;text-transform:uppercase;padding:2px 6px;border-radius:2px;font-weight:600;}
</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<?php admin_sidebar('products'); ?>

<!-- ===== MAIN ===== -->
<main class="ml-[220px] flex-1 p-8 min-h-screen">

  <!-- Toast -->
  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <?php if ($editing || isset($_GET['add'])): ?>
  <!-- ===================================================
       FORM: ADD / EDIT PRODUCT
       =================================================== -->
  <div class="mb-6 flex items-center gap-3">
    <a href="products.php" class="text-muted hover:text-brand font-jakarta text-[13px] transition-colors">← Kembali ke Daftar</a>
    <span class="text-muted">/</span>
    <h1 class="font-playfair font-semibold text-dark text-[22px]"><?= $editing ? 'Edit Produk' : 'Tambah Produk Baru' ?></h1>
  </div>

  <form method="POST" enctype="multipart/form-data" id="product-form" class="grid grid-cols-12 gap-6">
    <input type="hidden" name="_action" value="save"/>
    <input type="hidden" name="product_id" value="<?= $editing['id'] ?? 0 ?>"/>
    <?= csrf_field() ?>

    <!-- LEFT: Main Info (8 cols) -->
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-5">

      <!-- Basic Info -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Informasi Dasar</h2>

        <div>
          <label class="label">Nama Produk <span class="text-red-500">*</span></label>
          <input type="text" name="name" required
                 value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                 class="input" placeholder="cth. Aura Drape — French Linen Blend"/>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Kategori <span class="text-red-500">*</span></label>
            <select name="category" id="product-category" class="input" required onchange="showCategoryPrice()">
              <option value="">— Pilih Kategori —</option>
              <?php foreach($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['name']) ?>" data-ppm="<?= (int)$cat['price_per_meter'] ?>" <?= ($editing['category'] ?? '') === $cat['name'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label">Badge / Label</label>
            <input type="text" name="badge" value="<?= htmlspecialchars($editing['badge'] ?? '') ?>" class="input" placeholder="cth. Curator Choice, Bestseller"/>
          </div>
        </div>

        <div>
          <label class="label">REF Code</label>
          <input type="text" name="ref_code" value="<?= htmlspecialchars($editing['ref_code'] ?? '') ?>" class="input" placeholder="cth. WNT-CR-01"/>
        </div>

        <div>
          <label class="label">Deskripsi Editorial</label>
          <textarea name="description" rows="4" class="input resize-none" placeholder="Deskripsi produk yang menarik dan informatif..."><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
        </div>

        <div>
          <label class="label">Fitur / Spesifikasi</label>
          <p class="text-muted text-[11px] mb-1.5">Satu fitur per baris — pisahkan dengan Enter</p>
          <textarea name="features" rows="4" class="input resize-none font-mono text-[12px]" placeholder="Material 65% Belgian Linen&#10;S-Fold Wave Hemming&#10;Semi-Blackout (80%)"><?= htmlspecialchars(str_replace('|', "\n", $editing['features'] ?? '')) ?></textarea>
        </div>
      </div>

      <!-- Harga: ikut harga per meter kategori (default) atau harga custom produk -->
      <?php
      $is_save_post = ($_POST['_action'] ?? '') === 'save' && $err;   // tampilkan ulang isian saat validasi gagal
      $f_use_cat = $is_save_post ? isset($_POST['use_category_price']) : (int)($editing['use_category_price'] ?? 1) === 1;
      $f_price   = $is_save_post ? (string)($_POST['custom_price'] ?? '') : (!$f_use_cat && !empty($editing['price']) ? (string)(int)$editing['price'] : '');
      $f_unit    = ltrim($is_save_post ? (string)($_POST['custom_unit'] ?? 'unit') : (!$f_use_cat ? (string)($editing['price_unit'] ?? 'unit') : 'unit'), "/ \t");
      ?>
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Harga</h2>

        <label class="flex items-start gap-2.5 cursor-pointer select-none">
          <input type="checkbox" name="use_category_price" id="use-category-price" value="1" <?= $f_use_cat ? 'checked' : '' ?>
                 onchange="togglePriceMode()" class="mt-0.5 size-4 accent-[#860524]"/>
          <span>
            <span class="block font-jakarta text-dark text-[13px] font-medium">Ikuti harga kategori</span>
            <span class="block font-jakarta text-muted text-[11px] mt-0.5">Hapus centang untuk memasang harga custom khusus produk ini.</span>
          </span>
        </label>

        <!-- Mode kategori -->
        <div id="price-mode-category" class="bg-cream border border-border rounded-[4px] px-4 py-3">
          <div class="flex flex-wrap items-baseline justify-between gap-3">
            <p class="font-jakarta text-muted text-[12px]">Harga per meter kategori <strong id="cat-price-name" class="text-dark">—</strong></p>
            <p id="cat-price-value" class="font-playfair font-semibold text-dark text-[20px]">—</p>
          </div>
          <p class="font-jakarta text-muted text-[11px] mt-1">Dipakai kartu, detail produk &amp; kalkulator harga. Ubah lewat menu <a href="categories.php" class="text-brand underline">Kategori</a>.</p>
        </div>

        <!-- Mode custom -->
        <div id="price-mode-custom" class="flex flex-col gap-3">
          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
              <label class="label" for="custom-price">Harga Custom (Rp) <span class="text-red-500">*</span></label>
              <input type="number" id="custom-price" name="custom_price" min="1" step="1" value="<?= htmlspecialchars($f_price) ?>" class="input" placeholder="Contoh: 2750000"/>
            </div>
            <div>
              <label class="label" for="custom-unit">Satuan</label>
              <div class="flex items-stretch">
                <span class="inline-flex items-center px-3 border border-r-0 border-border rounded-l-[4px] bg-cream font-jakarta text-muted text-[13px]" aria-hidden="true">/</span>
                <input type="text" id="custom-unit" name="custom_unit" maxlength="48" list="price-units" value="<?= htmlspecialchars($f_unit) ?>" class="input !rounded-l-none" placeholder="unit"/>
              </div>
              <datalist id="price-units"><option value="unit"><option value="set"><option value="set track"><option value="meter"><option value="m²"></datalist>
            </div>
          </div>
          <p class="font-jakarta text-amber-700 text-[11px] bg-amber-50 border border-amber-200 rounded-[4px] px-3 py-2">Produk berharga custom tidak menampilkan kalkulator harga di halaman produk. Pengunjung tetap bisa memilih ukuran &amp; minta penawaran.</p>
        </div>
      </div>

      <!-- ==========================================
           VARIANT BUILDER (Dynamic JS)
           ========================================== -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <h2 class="font-jakarta font-semibold text-dark text-[14px]">Varian Produk</h2>
          <span class="font-jakarta text-muted text-[11px]" id="variant-count-label">Ukuran + maksimal <?= MAX_EXTRA_VARIANTS ?> varian lain</span>
        </div>

        <!-- Ukuran: parameter tetap (tidak bisa diganti nama / dihapus) -->
        <div class="variant-card is-size">
          <div class="flex items-start justify-between gap-3 mb-1">
            <div>
              <p class="font-jakarta font-semibold text-dark text-[13px]">Ukuran <span class="font-normal text-muted text-[11px]">· parameter tetap</span></p>
              <p class="font-jakarta text-muted text-[11px] mt-0.5 leading-[16px]">Lebar × tinggi dalam cm. Di halaman produk, ukuran yang dipilih customer langsung mengisi kalkulator harga. Opsi <strong>Custom</strong> ditambahkan otomatis.</p>
            </div>
          </div>
          <div id="size-rows"></div>
          <button type="button" onclick="addSizeRow()" class="btn-sm border border-brand/30 text-brand hover:bg-brand/5 mt-2 w-full py-1.5">
            + Tambah Ukuran
          </button>
        </div>

        <div id="variants-container">
          <!-- Rendered by JS -->
        </div>

        <button type="button" id="add-variant-btn" onclick="addVariant()"
                class="btn-sm border border-dashed border-brand/40 text-brand hover:bg-brand/5 w-full py-2.5 font-semibold">
          + TAMBAH VARIAN
        </button>
      </div>

    </div><!-- /LEFT -->

    <!-- RIGHT: Images + Status (4 cols) -->
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-5">

      <!-- Status & Stock -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Status & Stok</h2>

        <div>
          <label class="label">Status Produk</label>
          <input type="hidden" name="is_active" value="0"/>
          <label class="flex items-center gap-3 cursor-pointer select-none rounded-[6px] border border-border bg-cream/60 px-4 py-3">
            <input type="checkbox" name="is_active" value="1" <?= ($editing['is_active'] ?? 1) == 1 ? 'checked' : '' ?> class="peer sr-only"/>
            <span class="sw-track relative inline-block h-[24px] w-[44px] shrink-0 rounded-full bg-[#cfcac2] transition-colors duration-200 peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40">
              <span class="sw-knob absolute top-[3px] left-[3px] size-[18px] rounded-full bg-white shadow transition-transform duration-200"></span>
            </span>
            <span class="font-jakarta font-semibold text-[13px] text-muted leading-tight peer-checked:hidden">Nonaktif <span class="block font-normal text-[11px] text-muted">Disembunyikan dari katalog</span></span>
            <span class="hidden font-jakarta font-semibold text-[13px] text-emerald-700 leading-tight peer-checked:inline">Aktif <span class="block font-normal text-[11px] text-muted">Tampil di katalog</span></span>
          </label>
          <p class="font-jakarta text-muted text-[10px] mt-1.5">Nonaktif = tersembunyi dari katalog publik</p>
        </div>

        <div>
          <label class="label">Stok (Internal)</label>
          <input type="number" name="stock" min="0"
                 value="<?= $editing['stock'] ?? '' ?>" class="input" placeholder="Kosongkan jika tidak ingin tracking"/>
          <p class="font-jakarta text-muted text-[10px] mt-1.5">Hanya terlihat di admin — tidak ditampilkan ke customer</p>
        </div>
      </div>

      <!-- Gallery Images with Cover Picker -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-3">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Foto Produk <?= !$editing ? '<span class="text-red-500">*</span>' : '' ?></h2>
        <p class="font-jakarta text-muted text-[11px] leading-[16px]">Foto bertanda <strong class="text-brand">COVER</strong> tampil di katalog. <?= $editing ? 'Arahkan kursor ke foto lain lalu pilih "Jadikan Cover" untuk menggantinya.' : 'Foto pertama yang diupload otomatis jadi cover.' ?></p>
        <?php if (!empty($edit_images)): ?>
        <div class="grid grid-cols-2 gap-2">
          <?php foreach($edit_images as $gi): ?>
          <div class="relative group aspect-square rounded-[3px] overflow-hidden border <?= $gi['is_cover'] ? 'border-2 border-brand' : 'border-border' ?>">
            <img src="<?= htmlspecialchars(img_src($gi['image_url'], 480)) ?>" class="w-full h-full object-cover"/>
            <?php if ($gi['is_cover']): ?>
            <div class="cover-badge">COVER</div>
            <?php endif; ?>
            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
              <!-- Tombol ini mengirim form terpisah di luar #product-form (form tidak boleh bersarang) -->
              <?php if (!$gi['is_cover']): ?>
              <button type="submit" form="img-cover-<?= (int)$gi['id'] ?>" class="text-white text-[10px] tracking-[0.8px] uppercase font-semibold bg-brand/80 hover:bg-brand px-3 py-1.5 rounded-[2px] transition-colors border-0 cursor-pointer">
                ⭐ Jadikan Cover
              </button>
              <?php endif; ?>
              <button type="submit" form="img-del-<?= (int)$gi['id'] ?>" class="text-white text-[10px] tracking-[0.8px] uppercase font-semibold bg-red-500/80 hover:bg-red-600 px-3 py-1.5 rounded-[2px] transition-colors border-0 cursor-pointer">
                🗑️ Hapus
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?= image_dropzone(['name' => 'gallery_images', 'multiple' => true, 'required' => !$editing, 'max_files' => 12, 'compact' => (bool)$editing,
                            'title' => $editing ? 'Tambah foto — seret & lepas atau klik' : 'Seret & lepas foto produk, atau klik untuk memilih']) ?>
      </div>

      <!-- Save Button -->
      <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3.5 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">
        <?= $editing ? 'SIMPAN PERUBAHAN' : 'TAMBAH PRODUK' ?>
      </button>
      <?php if ($editing): ?>
      <a href="<?= BASE_URL ?>/product-detail.php?slug=<?= htmlspecialchars($editing['slug']) ?>" target="_blank"
         class="w-full border border-brand text-brand font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[4px] hover:bg-brand/5 transition-colors text-center block">
        LIHAT DI HALAMAN DEPAN ↗
      </a>
      <?php endif; ?>
    </div><!-- /RIGHT -->

  </form>

  <?php // Form aksi foto: di luar #product-form agar tidak ikut terkirim saat "Simpan Perubahan" ?>
  <?php foreach ($edit_images as $gi): ?>
  <form method="POST" id="img-cover-<?= (int)$gi['id'] ?>" class="hidden">
    <input type="hidden" name="_action" value="set_cover"/>
    <input type="hidden" name="product_id" value="<?= (int)$editing['id'] ?>"/>
    <input type="hidden" name="image_id" value="<?= (int)$gi['id'] ?>"/>
    <?= csrf_field() ?>
  </form>
  <form method="POST" id="img-del-<?= (int)$gi['id'] ?>" class="hidden" onsubmit="return confirm('Hapus foto ini?')">
    <input type="hidden" name="_action" value="del_img"/>
    <input type="hidden" name="product_id" value="<?= (int)$editing['id'] ?>"/>
    <input type="hidden" name="image_id" value="<?= (int)$gi['id'] ?>"/>
    <?= csrf_field() ?>
  </form>
  <?php endforeach; ?>

  <?php else: ?>
  <!-- ===================================================
       PRODUCT LIST
       =================================================== -->
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Manajemen Produk</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5">Kelola semua produk yang tampil di katalog.</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="products-import.php" class="bg-white text-brand border border-brand/40 font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-brand/5 transition-colors">
        ⬆ IMPOR CSV
      </a>
      <a href="products.php?add=1" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">
        + TAMBAH PRODUK
      </a>
    </div>
  </div>

  <div class="bg-white border border-border rounded-[6px] overflow-hidden">
    <table class="w-full">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-16">Cover</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama Produk</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase hidden lg:table-cell">Kategori</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Status</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20 hidden lg:table-cell">Stok</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Harga</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-48">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors <?= !$p['is_active'] ? '[&>td:not(:last-child)]:opacity-60' : '' ?>">
          <td class="px-4 py-3">
            <div class="size-12 rounded-[3px] overflow-hidden border border-border bg-cream-dark shrink-0 flex items-center justify-center">
              <?php if ($p['cover_image']): ?>
              <img src="<?= htmlspecialchars(img_src((string)$p['cover_image'], 480)) ?>" alt="" class="w-full h-full object-cover"/>
              <?php else: ?>
              <span class="font-jakarta text-[9px] leading-[11px] text-center text-muted px-1">Tanpa foto</span>
              <?php endif; ?>
            </div>
          </td>
          <td class="px-4 py-3">
            <p class="font-jakarta font-semibold text-dark text-[13px] leading-[18px]"><?= htmlspecialchars($p['name']) ?></p>
            <?php if ($p['badge']): ?>
            <span class="inline-block mt-1 bg-brand/10 text-brand font-jakarta text-[10px] tracking-[0.5px] uppercase px-1.5 py-0.5 rounded-[2px]"><?= htmlspecialchars($p['badge']) ?></span>
            <?php endif; ?>
            <p class="font-jakarta text-muted text-[11px] mt-0.5">REF: <?= htmlspecialchars($p['ref_code']) ?></p>
            <?php if (!$p['cover_image']): ?>
            <p class="font-jakarta text-amber-700 text-[11px] mt-0.5">⚠ Belum ada foto — klik Edit untuk menambahkan</p>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 hidden lg:table-cell">
            <span class="font-jakarta text-muted text-[12px]"><?= htmlspecialchars($p['category']) ?></span>
          </td>
          <td class="px-4 py-3 text-center">
            <form method="POST" class="inline-flex">
              <input type="hidden" name="_action" value="toggle_active"/>
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
              <?= csrf_field() ?>
              <button type="submit" role="switch" aria-checked="<?= $p['is_active'] ? 'true' : 'false' ?>"
                      title="<?= $p['is_active'] ? 'Aktif — klik untuk menyembunyikan dari katalog' : 'Nonaktif — klik untuk menampilkan di katalog' ?>"
                      class="group inline-flex items-center gap-2.5 whitespace-nowrap rounded-full py-1 pl-1 pr-3 transition-colors hover:bg-cream-dark">
                <span class="relative inline-block h-[22px] w-[40px] shrink-0 rounded-full transition-colors duration-200 <?= $p['is_active'] ? 'bg-emerald-500' : 'bg-[#cfcac2]' ?>">
                  <span class="absolute top-[3px] left-[3px] size-4 rounded-full bg-white shadow transition-transform duration-200 <?= $p['is_active'] ? 'translate-x-[18px]' : '' ?>"></span>
                </span>
                <span class="font-jakarta font-semibold text-[12px] <?= $p['is_active'] ? 'text-emerald-700' : 'text-muted' ?>"><?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
              </button>
            </form>
          </td>
          <td class="px-4 py-3 text-center hidden lg:table-cell">
            <span class="font-jakarta text-muted text-[12px]"><?= $p['stock'] !== null ? $p['stock'] : '—' ?></span>
          </td>
          <td class="px-4 py-3 text-right">
            <?php $pp = product_price($p); ?>
            <?php if ($pp['amount'] > 0): ?>
            <p class="font-playfair font-semibold text-dark text-[14px] whitespace-nowrap"><?= fmt_price($pp['amount']) ?> <span class="font-jakarta font-normal text-muted text-[11px]"><?= htmlspecialchars($pp['unit']) ?></span></p>
            <?php else: ?>
            <p class="font-jakarta text-amber-700 text-[12px]">Belum diatur</p>
            <?php endif; ?>
            <p class="font-jakarta text-[10px] <?= $pp['custom'] ? 'text-brand' : 'text-muted' ?>"><?= $pp['custom'] ? 'harga custom' : 'dari kategori' ?></p>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-1.5">
              <?php admin_btn_edit(['href' => 'products.php?edit=' . (int)$p['id']]); ?>
              <?php admin_btn_view(BASE_URL . '/product-detail.php?slug=' . $p['slug'], $p['is_active'] ? 'Lihat di situs' : 'Pratinjau (produk nonaktif)'); ?>
              <?php admin_btn_delete('Hapus "' . $p['name'] . '"?', 'Produk beserta semua fotonya akan dihapus permanen dan tidak bisa dikembalikan.', ['_action' => 'delete', 'product_id' => $p['id']]); ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <?php admin_empty_row(7, 'Belum ada produk', 'Tambahkan satu per satu, atau impor banyak sekaligus lewat CSV.', 'products.php?add=1', 'Tambah produk'); ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php admin_pagination($pg); ?>
  </div>
  <?php endif; ?>
</main>

<script>
/* ==========================================================
   VARIANT BUILDER — Dynamic JS
   ========================================================== */
const MAX_CUSTOM_VARIANTS = <?= MAX_EXTRA_VARIANTS ?>;
let variantIndex = 0;

// Gallery images for image-linking dropdown
const galleryImages = <?= json_encode(array_map(fn($img) => ['id' => $img['id'], 'url' => $img['image_url']], $edit_images), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

// Varian tersimpan: Ukuran (lebar × tinggi) dipisah dari varian lain
<?php
$size_variant  = current(array_filter($edit_variants, fn($v) => !empty($v['is_size']))) ?: null;
$extra_variants = array_values(array_filter($edit_variants, fn($v) => empty($v['is_size'])));
?>
const existingSizes = <?= json_encode($size_variant ? array_map(fn($o) => ['w' => (float)$o['width_cm'], 'h' => (float)$o['height_cm']], $size_variant['options']) : [], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const existingVariants = <?= json_encode(array_map(fn($v) => [
    'label' => $v['label'],
    'is_image_linked' => (bool)$v['is_image_linked'],
    'options' => array_map(fn($o) => ['value' => $o['value'], 'linked_image_id' => $o['linked_image_id']], $v['options']),
], $extra_variants), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

function getVariantCount() {
    return document.querySelectorAll('#variants-container .variant-card').length;
}

function updateAddButton() {
    const btn = document.getElementById('add-variant-btn');
    const count = getVariantCount();
    btn.style.display = count >= MAX_CUSTOM_VARIANTS ? 'none' : 'block';
    document.getElementById('variant-count-label').textContent =
        `Ukuran + ${count}/${MAX_CUSTOM_VARIANTS} varian lain`;
}

function addSizeRow(w = '', h = '') {
    const row = document.createElement('div');
    row.className = 'opt-row';
    row.innerHTML = `
        <input type="number" name="size_w[]" value="${escHtml(w)}" min="1" max="10000" step="0.1" class="opt-input" placeholder="Lebar (cm)" inputmode="decimal"/>
        <span class="font-jakarta text-muted text-[12px] shrink-0">×</span>
        <input type="number" name="size_h[]" value="${escHtml(h)}" min="1" max="10000" step="0.1" class="opt-input" placeholder="Tinggi (cm)" inputmode="decimal"/>
        <span class="font-jakarta text-muted text-[11px] shrink-0">cm</span>
        <button type="button" onclick="this.closest('.opt-row').remove()" class="btn-sm border border-red-200 text-red-500 hover:bg-red-50 shrink-0">×</button>
    `;
    document.getElementById('size-rows').appendChild(row);
}

function createOptionRow(vi, oi, value = '', linkedImageId = null) {
    const row = document.createElement('div');
    row.className = 'opt-row';
    row.dataset.oi = oi;

    let imgSelect = '';
    if (galleryImages.length > 0) {
        imgSelect = `
            <select name="variant_opt_image[${vi}][]" class="opt-input img-link-select" style="max-width:140px;" title="Link ke gambar galeri">
                <option value="">— Tanpa link —</option>
                ${galleryImages.map(img => `<option value="${img.id}" ${img.id == linkedImageId ? 'selected' : ''}>Foto #${img.id}</option>`).join('')}
            </select>
        `;
    }

    row.innerHTML = `
        <input type="text" name="variant_options[${vi}][]" value="${escHtml(value)}" class="opt-input" placeholder="cth. Oatmeal Cream" required/>
        ${imgSelect}
        <button type="button" onclick="this.closest('.opt-row').remove()" class="btn-sm border border-red-200 text-red-500 hover:bg-red-50 shrink-0">×</button>
    `;
    return row;
}

function addVariant(label = '', isSize = false, isImageLinked = false, options = []) {
    if (getVariantCount() >= MAX_CUSTOM_VARIANTS) return;

    const vi = variantIndex++;
    const card = document.createElement('div');
    card.className = `variant-card ${isSize ? 'is-size' : ''}`;
    card.dataset.vi = vi;

    const labelPlaceholder = isSize ? 'Ukuran (fixed)' : 'cth. Warna, Motif, Kontrol';
    const labelValue = label || (isSize ? 'Ukuran' : '');
    const labelReadonly = isSize ? 'readonly' : '';

    card.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3 flex-1">
                <input type="text" name="variant_label[${vi}]" value="${escHtml(labelValue)}" ${labelReadonly}
                       class="opt-input font-semibold" style="max-width:200px;" placeholder="${labelPlaceholder}" required/>
                ${!isSize ? `
                <label class="flex items-center gap-1.5 cursor-pointer shrink-0" title="Link varian ini ke gambar galeri">
                    <input type="checkbox" name="variant_image_linked[]" value="${vi}" ${isImageLinked ? 'checked' : ''}
                           onchange="handleImageLinkToggle(this)" class="accent-[#860524]"/>
                    <span class="font-jakarta text-muted text-[11px]">🔗 Link gambar</span>
                </label>
                ` : ''}
            </div>
            ${!isSize ? `<button type="button" onclick="removeVariant(this)" class="btn-sm border border-red-200 text-red-500 hover:bg-red-50">Hapus Varian</button>` : ''}
        </div>
        <div class="options-container" data-vi="${vi}">
            <!-- option rows here -->
        </div>
        <button type="button" onclick="addOption(${vi})" class="btn-sm border border-brand/30 text-brand hover:bg-brand/5 mt-2 w-full py-1.5">
            + Tambah Item
        </button>
    `;

    document.getElementById('variants-container').appendChild(card);

    // Add initial options
    const container = card.querySelector('.options-container');
    if (options.length > 0) {
        options.forEach((opt, oi) => {
            container.appendChild(createOptionRow(vi, oi, opt.value || opt, opt.linked_image_id || null));
        });
    } else {
        // Add one empty row
        container.appendChild(createOptionRow(vi, 0));
    }

    updateAddButton();
    syncImageLinkSelects();
}

function addOption(vi) {
    const container = document.querySelector(`.options-container[data-vi="${vi}"]`);
    const oi = container.children.length;
    container.appendChild(createOptionRow(vi, oi));
    syncImageLinkSelects();
}

function removeVariant(btn) {
    btn.closest('.variant-card').remove();
    updateAddButton();
}

function handleImageLinkToggle(checkbox) {
    if (checkbox.checked) {
        // Uncheck all other image-link checkboxes (only 1 allowed)
        document.querySelectorAll('input[name="variant_image_linked[]"]').forEach(cb => {
            if (cb !== checkbox) cb.checked = false;
        });
    }
    syncImageLinkSelects();
}

// Dropdown "link ke foto" hanya tampil pada varian yang dicentang "Link gambar"
function syncImageLinkSelects() {
    document.querySelectorAll('#variants-container .variant-card').forEach(card => {
        const cb = card.querySelector('input[name="variant_image_linked[]"]');
        const on = !!(cb && cb.checked);
        card.querySelectorAll('.img-link-select').forEach(sel => {
            sel.style.display = on ? '' : 'none';
            sel.disabled = !on;   // tidak ikut terkirim bila tidak aktif
        });
    });
}

function escHtml(str) {
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'};
    return String(str).replace(/[&<>"']/g, c => map[c]);
}

// Initialize variants on page load
document.addEventListener('DOMContentLoaded', () => {
    if (existingSizes.length) existingSizes.forEach(sz => addSizeRow(sz.w, sz.h));
    else addSizeRow();
    existingVariants.forEach(v => addVariant(v.label, false, v.is_image_linked, v.options));
    updateAddButton();
});

/* ==========================================================
   IMAGE PREVIEWS
   ========================================================== */
// Harga produk = harga per meter kategori yang dipilih
function togglePriceMode() {
  const useCat = document.getElementById('use-category-price').checked;
  document.getElementById('price-mode-category').style.display = useCat ? '' : 'none';
  document.getElementById('price-mode-custom').style.display = useCat ? 'none' : '';
  const price = document.getElementById('custom-price');
  price.required = !useCat;
  if (!useCat && !price.value) price.focus();
}
document.getElementById('use-category-price') && (function () {
  // inisialisasi tanpa memindahkan fokus
  const useCat = document.getElementById('use-category-price').checked;
  document.getElementById('price-mode-category').style.display = useCat ? '' : 'none';
  document.getElementById('price-mode-custom').style.display = useCat ? 'none' : '';
  document.getElementById('custom-price').required = !useCat;
})();

function showCategoryPrice() {
  const sel = document.getElementById('product-category');
  if (!sel) return;
  const opt = sel.selectedOptions[0];
  const ppm = opt && opt.value ? parseInt(opt.dataset.ppm, 10) || 0 : null;
  document.getElementById('cat-price-name').textContent = opt && opt.value ? opt.value : '—';
  document.getElementById('cat-price-value').textContent =
    ppm === null ? '—' : (ppm > 0 ? 'Rp ' + ppm.toLocaleString('id-ID') + ' / meter' : 'Belum diatur');
}
showCategoryPrice();
</script>
<?php image_upload_script(); ?>
<?php admin_actions_assets(); ?>
</body>
</html>
