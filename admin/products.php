<?php
// admin/products.php — Manajemen Produk CRUD (Variant System Overhaul)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$db  = get_db();
$msg = '';
$err = '';

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

function upload_image(array $file, string $subdir): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('img_', true) . '.' . strtolower($ext);
    $dir  = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return '/uploads/' . $subdir . '/' . $name;
}

function delete_file(string $url): void {
    if (str_starts_with($url, '/uploads/')) {
        $path = __DIR__ . '/..' . $url;
        if (file_exists($path)) unlink($path);
    }
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
        if ($row) delete_file($row['cover_image']);
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
   ACTION: DELETE GALLERY IMAGE (via GET)
   ============================================================ */
if (isset($_GET['del_img'])) {
    $del_img_id = (int)$_GET['del_img'];
    $pid_for_del = (int)($_GET['edit'] ?? 0);
    $r = $db->prepare("SELECT image_url FROM product_images WHERE id=?");
    $r->execute([$del_img_id]); $row = $r->fetch();
    if ($row) {
        delete_file($row['image_url']);
        $db->prepare("DELETE FROM product_images WHERE id=?")->execute([$del_img_id]);
    }
    header("Location: products.php?edit=$pid_for_del&msg=Foto+dihapus"); exit;
}

/* ============================================================
   ACTION: SET COVER (via GET)
   ============================================================ */
if (isset($_GET['set_cover'])) {
    $img_id = (int)$_GET['set_cover'];
    $pid_for_cover = (int)($_GET['edit'] ?? 0);
    if ($pid_for_cover > 0 && $img_id > 0) {
        // Unset all covers for this product
        $db->prepare("UPDATE product_images SET is_cover = 0 WHERE product_id = ?")->execute([$pid_for_cover]);
        // Set the selected one
        $db->prepare("UPDATE product_images SET is_cover = 1 WHERE id = ? AND product_id = ?")->execute([$img_id, $pid_for_cover]);
        // Update cover_image in products table for backward compat
        $img_row = $db->prepare("SELECT image_url FROM product_images WHERE id = ?");
        $img_row->execute([$img_id]);
        $img_data = $img_row->fetch();
        if ($img_data) {
            $db->prepare("UPDATE products SET cover_image = ? WHERE id = ?")->execute([$img_data['image_url'], $pid_for_cover]);
        }
    }
    header("Location: products.php?edit=$pid_for_cover&msg=Cover+berhasil+diubah"); exit;
}

/* ============================================================
   EDITING STATE
   ============================================================ */
$editing   = null;
$edit_id   = (int)($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $s = $db->prepare("SELECT * FROM products WHERE id = ?");
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
    $price        = (int)preg_replace('/\D/', '', $_POST['price'] ?? '0');
    $orig_price   = (int)preg_replace('/\D/', '', $_POST['original_price'] ?? '0');
    $price_unit   = trim($_POST['price_unit'] ?? '/ meter');
    $description  = trim($_POST['description'] ?? '');
    $features_raw = trim($_POST['features'] ?? '');
    $features     = implode('|', array_filter(array_map('trim', explode("\n", $features_raw))));
    $is_active    = (int)($_POST['is_active'] ?? 1);
    $stock        = trim($_POST['stock'] ?? '') === '' ? null : (int)$_POST['stock'];

    if (!$name || !$category || !$price) {
        $err = 'Nama, kategori, dan harga wajib diisi.';
    } else {
        $slug = slugify($name);
        if ($pid === 0) {
            $base_slug = $slug; $i = 2;
            while ($db->query("SELECT id FROM products WHERE slug='$slug'")->fetchColumn()) {
                $slug = $base_slug . '-' . $i++;
            }
        }

        // Cover image (for new products)
        $cover_url = '';
        if (!empty($_FILES['cover_image']['name'])) {
            $cover_url = upload_image($_FILES['cover_image'], 'products');
            if (!$cover_url) $err = 'Gagal upload cover image (maks 5MB, JPG/PNG/WEBP).';
        }

        if (!$err) {
            if ($pid === 0) {
                // INSERT
                if (!$cover_url) { $err = 'Cover image wajib untuk produk baru.'; }
                else {
                    $ins = $db->prepare("INSERT INTO products (slug,name,category,badge,ref_code,price,original_price,price_unit,description,features,cover_image,is_active,stock) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $ins->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$cover_url,$is_active,$stock]);
                    $pid = (int)$db->lastInsertId();
                    // Also add cover as first gallery image
                    $db->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_cover) VALUES (?,?,0,1)")->execute([$pid, $cover_url]);
                    $msg = 'Produk berhasil ditambahkan.';
                }
            } else {
                // UPDATE
                if ($cover_url) {
                    $old = $db->prepare("SELECT cover_image FROM products WHERE id=?");
                    $old->execute([$pid]); $r = $old->fetch();
                    if ($r) delete_file($r['cover_image']);
                    $db->prepare("UPDATE products SET slug=?,name=?,category=?,badge=?,ref_code=?,price=?,original_price=?,price_unit=?,description=?,features=?,cover_image=?,is_active=?,stock=? WHERE id=?")
                       ->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$cover_url,$is_active,$stock,$pid]);
                } else {
                    $db->prepare("UPDATE products SET slug=?,name=?,category=?,badge=?,ref_code=?,price=?,original_price=?,price_unit=?,description=?,features=?,is_active=?,stock=? WHERE id=?")
                       ->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$is_active,$stock,$pid]);
                }
                $msg = 'Produk berhasil diperbarui.';
            }
        }

        // Upload additional gallery images
        if ($pid > 0 && !$err && !empty($_FILES['gallery_images']['name'][0])) {
            $ins_img = $db->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?,?,?)");
            $existing_count = (int)$db->query("SELECT COUNT(*) FROM product_images WHERE product_id=$pid")->fetchColumn();
            foreach ($_FILES['gallery_images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $single = [
                    'name'     => $_FILES['gallery_images']['name'][$i],
                    'type'     => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $tmp,
                    'error'    => $_FILES['gallery_images']['error'][$i],
                    'size'     => $_FILES['gallery_images']['size'][$i],
                ];
                $url = upload_image($single, 'products');
                if ($url) $ins_img->execute([$pid, $url, $existing_count + $i]);
            }
        }

        // =====================================================
        // SAVE VARIANTS (from dynamic JS builder)
        // =====================================================
        if ($pid > 0 && !$err) {
            // Delete existing variants for clean re-insert
            $db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$pid]);

            $variant_labels = $_POST['variant_label'] ?? [];
            $variant_image_linked = $_POST['variant_image_linked'] ?? [];
            $variant_options = $_POST['variant_options'] ?? [];

            foreach ($variant_labels as $vi => $label) {
                $label = trim($label);
                if (!$label) continue;
                $is_img_linked = in_array((string)$vi, $variant_image_linked) ? 1 : 0;
                $ins_var = $db->prepare("INSERT INTO product_variants (product_id, label, is_image_linked, sort_order) VALUES (?, ?, ?, ?)");
                $ins_var->execute([$pid, $label, $is_img_linked, $vi]);
                $vid = (int)$db->lastInsertId();

                $options = $variant_options[$vi] ?? [];
                foreach ($options as $oi => $opt_val) {
                    $opt_val = trim($opt_val);
                    if (!$opt_val) continue;
                    // Check for linked image
                    $linked_img_id = null;
                    $link_key = "variant_opt_image_{$vi}_{$oi}";
                    if (isset($_POST[$link_key]) && (int)$_POST[$link_key] > 0) {
                        $linked_img_id = (int)$_POST[$link_key];
                    }
                    $ins_opt = $db->prepare("INSERT INTO product_variant_options (variant_id, value, linked_image_id, sort_order) VALUES (?, ?, ?, ?)");
                    $ins_opt->execute([$vid, $opt_val, $linked_img_id, $oi]);
                }
            }
        }

        // Refresh editing
        if ($pid > 0 && !$err) {
            $s = $db->prepare("SELECT * FROM products WHERE id=?");
            $s->execute([$pid]); $editing = $s->fetch(); $edit_id = $pid;
        }
    }
}

if (isset($_GET['msg'])) $msg = htmlspecialchars($_GET['msg']);

/* ============================================================
   LOAD PRODUCTS LIST
   ============================================================ */
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['"Playfair Display"','serif'],jakarta:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{DEFAULT:'#741122',dark:'#5a0d1a'},dark:'#222220',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>
*{box-sizing:border-box;} textarea,input,select{outline:none;}
.label{display:block;font-family:'Plus Jakarta Sans',sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;}
.input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:#222220;background:#fff;transition:border-color 0.15s;}
.input:focus{border-color:#741122;}
select.input{appearance:auto;}
.variant-card{border:1px solid #ece8e1;border-radius:6px;background:#fff;padding:16px;margin-bottom:12px;}
.variant-card.is-size{border-left:3px solid #741122;}
.opt-row{display:flex;gap:8px;align-items:center;margin-top:6px;}
.opt-input{flex:1;border:1px solid #ece8e1;border-radius:3px;padding:6px 10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;color:#222220;background:#fff;}
.opt-input:focus{border-color:#741122;}
.btn-sm{padding:4px 10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:11px;letter-spacing:0.5px;text-transform:uppercase;border-radius:3px;cursor:pointer;transition:all 0.15s;}
.cover-badge{position:absolute;top:4px;left:4px;background:#741122;color:#fff;font-size:9px;letter-spacing:1px;text-transform:uppercase;padding:2px 6px;border-radius:2px;font-weight:600;}
</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- ===== SIDEBAR ===== -->
<aside class="w-[220px] shrink-0 bg-white border-r border-border flex flex-col min-h-screen fixed top-0 left-0 h-full z-20">
  <div class="p-6 border-b border-border">
    <a href="../" target="_blank" class="flex flex-col group">
      <span class="font-playfair font-bold text-brand text-[22px] leading-[28px]">wintom</span>
      <span class="font-jakarta font-semibold text-muted text-[8px] tracking-[2.5px] uppercase group-hover:text-brand transition-colors">CURTAIN ADMIN</span>
    </a>
  </div>
  <nav class="flex flex-col p-4 gap-1 flex-1">
    <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Dashboard</a>
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Produk</a>
    <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Kategori</a>
    <a href="gallery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Galeri Portofolio</a>
  </nav>
  <div class="p-4 border-t border-border">
    <a href="logout.php" class="block px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors">Logout</a>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<main class="ml-[220px] flex-1 p-8 min-h-screen">

  <!-- Toast -->
  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= $msg ?></div><?php endif; ?>
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
            <select name="category" class="input" required>
              <option value="">— Pilih Kategori —</option>
              <?php foreach($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($editing['category'] ?? '') === $cat['name'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
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

      <!-- Pricing -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Harga</h2>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="label">Harga Jual <span class="text-red-500">*</span></label>
            <input type="number" name="price" required min="0"
                   value="<?= $editing['price'] ?? '' ?>" class="input" placeholder="385000"/>
          </div>
          <div>
            <label class="label">Harga Coret</label>
            <input type="number" name="original_price" min="0"
                   value="<?= $editing['original_price'] ?? '' ?>" class="input" placeholder="450000"/>
          </div>
          <div>
            <label class="label">Satuan Harga</label>
            <input type="text" name="price_unit" value="<?= htmlspecialchars($editing['price_unit'] ?? '/ meter') ?>" class="input" placeholder="/ meter"/>
          </div>
        </div>
        <?php if (!empty($editing['price']) && !empty($editing['original_price']) && $editing['original_price'] > $editing['price']): ?>
        <div class="bg-green-50 border border-green-200 rounded-[4px] px-4 py-2">
          <p class="font-jakarta text-green-700 text-[12px]">
            Diskon: <strong><?= round((1 - $editing['price']/$editing['original_price'])*100) ?>%</strong>
            (hemat <?= fmt_price($editing['original_price'] - $editing['price']) ?>)
          </p>
        </div>
        <?php endif; ?>
      </div>

      <!-- ==========================================
           VARIANT BUILDER (Dynamic JS)
           ========================================== -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <h2 class="font-jakarta font-semibold text-dark text-[14px]">Varian Produk</h2>
          <span class="font-jakarta text-muted text-[11px]" id="variant-count-label">Maksimal 4 varian (1 ukuran + 3 kustom)</span>
        </div>

        <div id="variants-container">
          <!-- Rendered by JS -->
        </div>

        <button type="button" id="add-variant-btn" onclick="addVariant()"
                class="btn-sm border border-dashed border-brand/40 text-brand hover:bg-brand/5 w-full py-2.5 font-semibold">
          + TAMBAH VARIAN KUSTOM
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
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="is_active" value="1" <?= ($editing['is_active'] ?? 1) == 1 ? 'checked' : '' ?> class="accent-[#741122]"/>
              <span class="font-jakarta text-[13px] text-dark">Aktif <span class="text-green-600 text-[11px]">●</span></span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="is_active" value="0" <?= ($editing['is_active'] ?? 1) == 0 ? 'checked' : '' ?> class="accent-[#741122]"/>
              <span class="font-jakarta text-[13px] text-dark">Nonaktif <span class="text-red-500 text-[11px]">●</span></span>
            </label>
          </div>
          <p class="font-jakarta text-muted text-[10px] mt-1.5">Nonaktif = tersembunyi dari katalog publik</p>
        </div>

        <div>
          <label class="label">Stok (Internal)</label>
          <input type="number" name="stock" min="0"
                 value="<?= $editing['stock'] ?? '' ?>" class="input" placeholder="Kosongkan jika tidak ingin tracking"/>
          <p class="font-jakarta text-muted text-[10px] mt-1.5">Hanya terlihat di admin — tidak ditampilkan ke customer</p>
        </div>
      </div>

      <!-- Cover Image -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-3">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Cover Image <?= !$editing ? '<span class="text-red-500">*</span>' : '' ?></h2>
        <?php if ($editing && $editing['cover_image']): ?>
        <div class="aspect-[3/4] rounded-[4px] overflow-hidden bg-cream-dark border border-border">
          <img src="<?= htmlspecialchars($editing['cover_image']) ?>" alt="Cover" class="w-full h-full object-cover"/>
        </div>
        <p class="font-jakarta text-muted text-[11px]">Upload baru untuk mengganti cover, atau pilih dari galeri di bawah.</p>
        <?php endif; ?>
        <div class="border-2 border-dashed border-border rounded-[4px] p-4 text-center hover:border-brand/40 transition-colors cursor-pointer" onclick="document.getElementById('cover_image').click()">
          <svg class="size-8 text-muted mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
          <p class="font-jakarta text-muted text-[12px]">Klik untuk upload cover</p>
          <p class="font-jakarta text-muted text-[11px] mt-1">JPG, PNG, WEBP — maks 5MB</p>
        </div>
        <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden" onchange="previewCover(event)"/>
        <div id="cover-preview" class="hidden aspect-[3/4] rounded-[4px] overflow-hidden border border-border">
          <img id="cover-preview-img" src="" class="w-full h-full object-cover"/>
        </div>
      </div>

      <!-- Gallery Images with Cover Picker -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-3">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Foto Galeri Detail</h2>
        <?php if (!empty($edit_images)): ?>
        <div class="grid grid-cols-2 gap-2">
          <?php foreach($edit_images as $gi): ?>
          <div class="relative group aspect-square rounded-[3px] overflow-hidden border <?= $gi['is_cover'] ? 'border-2 border-brand' : 'border-border' ?>">
            <img src="<?= htmlspecialchars($gi['image_url']) ?>" class="w-full h-full object-cover"/>
            <?php if ($gi['is_cover']): ?>
            <div class="cover-badge">COVER</div>
            <?php endif; ?>
            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
              <?php if (!$gi['is_cover']): ?>
              <a href="products.php?edit=<?= $editing['id'] ?>&set_cover=<?= $gi['id'] ?>"
                 class="text-white text-[10px] tracking-[0.8px] uppercase font-semibold bg-brand/80 hover:bg-brand px-3 py-1.5 rounded-[2px] transition-colors">
                ⭐ Jadikan Cover
              </a>
              <?php endif; ?>
              <a href="products.php?edit=<?= $editing['id'] ?>&del_img=<?= $gi['id'] ?>"
                 onclick="return confirm('Hapus foto ini?')"
                 class="text-white text-[10px] tracking-[0.8px] uppercase font-semibold bg-red-500/80 hover:bg-red-600 px-3 py-1.5 rounded-[2px] transition-colors">
                🗑️ Hapus
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="border-2 border-dashed border-border rounded-[4px] p-4 text-center hover:border-brand/40 transition-colors cursor-pointer" onclick="document.getElementById('gallery_images').click()">
          <svg class="size-6 text-muted mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <p class="font-jakarta text-muted text-[12px]">Tambah foto galeri (bisa multiple)</p>
        </div>
        <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple class="hidden"/>
        <div id="gallery-preview" class="grid grid-cols-2 gap-2"></div>
      </div>

      <!-- Save Button -->
      <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3.5 rounded-[4px] hover:bg-[#5a0d1a] transition-colors shadow-sm">
        <?= $editing ? 'SIMPAN PERUBAHAN' : 'TAMBAH PRODUK' ?>
      </button>
      <?php if ($editing): ?>
      <a href="/product-detail.php?slug=<?= htmlspecialchars($editing['slug']) ?>" target="_blank"
         class="w-full border border-brand text-brand font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[4px] hover:bg-brand/5 transition-colors text-center block">
        LIHAT DI HALAMAN DEPAN ↗
      </a>
      <?php endif; ?>
    </div><!-- /RIGHT -->

  </form>

  <?php else: ?>
  <!-- ===================================================
       PRODUCT LIST
       =================================================== -->
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Manajemen Produk</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= count($products) ?> produk terdaftar</p>
    </div>
    <a href="products.php?add=1" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#5a0d1a] transition-colors">
      + TAMBAH PRODUK
    </a>
  </div>

  <div class="bg-white border border-border rounded-[6px] overflow-hidden">
    <table class="w-full">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-16">Cover</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama Produk</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase hidden lg:table-cell">Kategori</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20">Status</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20 hidden lg:table-cell">Stok</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Harga</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-44">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors <?= !$p['is_active'] ? 'opacity-60' : '' ?>">
          <td class="px-4 py-3">
            <div class="size-12 rounded-[3px] overflow-hidden border border-border bg-cream-dark shrink-0">
              <img src="<?= htmlspecialchars($p['cover_image']) ?>" alt="" class="w-full h-full object-cover"/>
            </div>
          </td>
          <td class="px-4 py-3">
            <p class="font-jakarta font-semibold text-dark text-[13px] leading-[18px]"><?= htmlspecialchars($p['name']) ?></p>
            <?php if ($p['badge']): ?>
            <span class="inline-block mt-1 bg-brand/10 text-brand font-jakarta text-[10px] tracking-[0.5px] uppercase px-1.5 py-0.5 rounded-[2px]"><?= htmlspecialchars($p['badge']) ?></span>
            <?php endif; ?>
            <p class="font-jakarta text-muted text-[11px] mt-0.5">REF: <?= htmlspecialchars($p['ref_code']) ?> · slug: <?= htmlspecialchars($p['slug']) ?></p>
          </td>
          <td class="px-4 py-3 hidden lg:table-cell">
            <span class="font-jakarta text-muted text-[12px]"><?= htmlspecialchars($p['category']) ?></span>
          </td>
          <td class="px-4 py-3 text-center">
            <form method="POST" class="inline">
              <input type="hidden" name="_action" value="toggle_active"/>
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
              <button type="submit" title="<?= $p['is_active'] ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' ?>"
                      class="font-jakarta text-[10px] tracking-[0.5px] uppercase px-2 py-1 rounded-full border transition-colors
                             <?= $p['is_active'] ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100' ?>">
                <?= $p['is_active'] ? '● Aktif' : '○ Nonaktif' ?>
              </button>
            </form>
          </td>
          <td class="px-4 py-3 text-center hidden lg:table-cell">
            <span class="font-jakarta text-muted text-[12px]"><?= $p['stock'] !== null ? $p['stock'] : '—' ?></span>
          </td>
          <td class="px-4 py-3 text-right">
            <p class="font-playfair font-semibold text-dark text-[14px]"><?= fmt_price($p['price']) ?></p>
            <?php if ($p['original_price']): ?>
            <p class="font-jakarta text-muted text-[11px] line-through"><?= fmt_price($p['original_price']) ?></p>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-2">
              <a href="products.php?edit=<?= $p['id'] ?>"
                 class="font-jakarta text-[11px] tracking-[0.5px] uppercase text-brand border border-brand/30 px-3 py-1.5 rounded-[3px] hover:bg-brand/5 transition-colors">
                Edit
              </a>
              <a href="/product-detail.php?slug=<?= htmlspecialchars($p['slug']) ?>" target="_blank"
                 class="font-jakarta text-[11px] text-muted border border-border px-3 py-1.5 rounded-[3px] hover:border-dark/30 transition-colors">
                View
              </a>
              <form method="POST" onsubmit="return confirm('Hapus produk ini? Semua foto akan ikut terhapus.')">
                <input type="hidden" name="_action" value="delete"/>
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
                <button type="submit" class="font-jakarta text-[11px] text-red-500 border border-red-200 px-3 py-1.5 rounded-[3px] hover:bg-red-50 transition-colors">Del</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="7" class="px-4 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada produk. <a href="products.php?add=1" class="text-brand hover:underline">Tambah sekarang →</a></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>

<script>
/* ==========================================================
   VARIANT BUILDER — Dynamic JS
   ========================================================== */
const MAX_CUSTOM_VARIANTS = 3;
let variantIndex = 0;

// Gallery images for image-linking dropdown
const galleryImages = <?= json_encode(array_map(fn($img) => ['id' => $img['id'], 'url' => $img['image_url']], $edit_images)) ?>;

// Existing variants for edit mode
const existingVariants = <?= json_encode(array_map(fn($v) => [
    'label' => $v['label'],
    'is_image_linked' => (bool)$v['is_image_linked'],
    'options' => array_map(fn($o) => ['value' => $o['value'], 'linked_image_id' => $o['linked_image_id']], $v['options']),
], $edit_variants)) ?>;

function getVariantCount() {
    return document.querySelectorAll('.variant-card').length;
}

function updateAddButton() {
    const btn = document.getElementById('add-variant-btn');
    const count = getVariantCount();
    if (count >= MAX_CUSTOM_VARIANTS + 1) { // +1 for Ukuran
        btn.style.display = 'none';
    } else {
        btn.style.display = 'block';
    }
    document.getElementById('variant-count-label').textContent =
        `${count}/4 varian digunakan (1 ukuran + ${Math.max(0, count - 1)} kustom)`;
}

function createOptionRow(vi, oi, value = '', linkedImageId = null) {
    const row = document.createElement('div');
    row.className = 'opt-row';
    row.dataset.oi = oi;

    let imgSelect = '';
    if (galleryImages.length > 0) {
        imgSelect = `
            <select name="variant_opt_image_${vi}_${oi}" class="opt-input" style="max-width:140px;" title="Link ke gambar galeri">
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
    if (!isSize && getVariantCount() >= MAX_CUSTOM_VARIANTS + 1) return;

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
                           onchange="handleImageLinkToggle(this)" class="accent-[#741122]"/>
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
}

function addOption(vi) {
    const container = document.querySelector(`.options-container[data-vi="${vi}"]`);
    const oi = container.children.length;
    container.appendChild(createOptionRow(vi, oi));
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
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Initialize variants on page load
document.addEventListener('DOMContentLoaded', () => {
    if (existingVariants.length > 0) {
        existingVariants.forEach((v, i) => {
            const isSize = v.label === 'Ukuran' || v.label === 'Panjang Track';
            addVariant(v.label, isSize && i === 0, v.is_image_linked, v.options);
        });
    } else {
        // Default: add "Ukuran" as first variant
        addVariant('Ukuran', true, false, []);
    }
});

/* ==========================================================
   IMAGE PREVIEWS
   ========================================================== */
function previewCover(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    document.getElementById('cover-preview').classList.remove('hidden');
    document.getElementById('cover-preview-img').src = ev.target.result;
  };
  reader.readAsDataURL(file);
}

document.getElementById('gallery_images')?.addEventListener('change', function(e) {
  const container = document.getElementById('gallery-preview');
  container.innerHTML = '';
  Array.from(e.target.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = ev => {
      const div = document.createElement('div');
      div.className = 'aspect-square rounded-[3px] overflow-hidden border border-border';
      div.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover"/>`;
      container.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
});
</script>
</body>
</html>
