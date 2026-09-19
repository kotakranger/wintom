<?php
// admin/products.php — Manajemen Produk CRUD
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
    $dest = __DIR__ . '/../uploads/' . $subdir . '/' . $name;
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
   ACTION: DELETE PRODUCT
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id > 0) {
        // Delete physical images
        $imgs = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll() as $img) delete_file($img['image_url']);
        $cover = $db->prepare("SELECT cover_image FROM products WHERE id = ?");
        $cover->execute([$id]);
        $row = $cover->fetch();
        if ($row) delete_file($row['cover_image']);
        // Delete DB rows (CASCADE handles product_images)
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        $msg = 'Produk berhasil dihapus.';
    }
}

/* ============================================================
   ACTION: ADD / EDIT PRODUCT
   ============================================================ */
$editing   = null;
$edit_id   = (int)($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $s = $db->prepare("SELECT * FROM products WHERE id = ?");
    $s->execute([$edit_id]);
    $editing = $s->fetch();
    if (!$editing) { $edit_id = 0; }
}

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
    $features     = trim($_POST['features'] ?? '');

    // Options JSON
    $colors_raw = array_filter(array_map('trim', explode("\n", $_POST['colors'] ?? '')));
    $stitch_raw = array_filter(array_map('trim', explode("\n", $_POST['stitch'] ?? '')));
    $options_json = json_encode(['colors' => array_values($colors_raw), 'stitch' => array_values($stitch_raw)]);

    if (!$name || !$category || !$price) {
        $err = 'Nama, kategori, dan harga wajib diisi.';
    } else {
        $slug = slugify($name);
        // Ensure unique slug (on add)
        if ($pid === 0) {
            $base_slug = $slug; $i = 2;
            while ($db->query("SELECT id FROM products WHERE slug='$slug'")->fetchColumn()) {
                $slug = $base_slug . '-' . $i++;
            }
        }

        // Cover image
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
                    $ins = $db->prepare("INSERT INTO products (slug,name,category,badge,ref_code,price,original_price,price_unit,description,features,options_json,cover_image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                    $ins->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$options_json,$cover_url]);
                    $pid = (int)$db->lastInsertId();
                    $msg = 'Produk berhasil ditambahkan.';
                }
            } else {
                // UPDATE
                if ($cover_url) {
                    // Delete old cover
                    $old = $db->prepare("SELECT cover_image FROM products WHERE id=?");
                    $old->execute([$pid]); $r = $old->fetch();
                    if ($r) delete_file($r['cover_image']);
                    $db->prepare("UPDATE products SET slug=?,name=?,category=?,badge=?,ref_code=?,price=?,original_price=?,price_unit=?,description=?,features=?,options_json=?,cover_image=? WHERE id=?")
                       ->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$options_json,$cover_url,$pid]);
                } else {
                    $db->prepare("UPDATE products SET slug=?,name=?,category=?,badge=?,ref_code=?,price=?,original_price=?,price_unit=?,description=?,features=?,options_json=? WHERE id=?")
                       ->execute([$slug,$name,$category,$badge,$ref_code,$price,$orig_price ?: null,$price_unit,$description,$features,$options_json,$pid]);
                }
                $msg = 'Produk berhasil diperbarui.';
            }
        }

        // Additional gallery images (multi-upload)
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

        // Delete individual gallery image
        if (($_POST['_action'] ?? '') === 'save') {
            $del_img_id = (int)($_POST['del_img'] ?? 0);
            if ($del_img_id > 0) {
                $r = $db->prepare("SELECT image_url FROM product_images WHERE id=?");
                $r->execute([$del_img_id]); $row = $r->fetch();
                if ($row) { delete_file($row['image_url']); $db->prepare("DELETE FROM product_images WHERE id=?")->execute([$del_img_id]); }
            }
        }

        // Refresh editing
        if ($pid > 0 && !$err) {
            $s = $db->prepare("SELECT * FROM products WHERE id=?");
            $s->execute([$pid]); $editing = $s->fetch(); $edit_id = $pid;
        }
    }
}

// Handle delete gallery image via GET (small AJAX-like link)
if (isset($_GET['del_img'])) {
    $del_img_id = (int)$_GET['del_img'];
    $pid_for_del = (int)($_GET['edit'] ?? 0);
    $r = $db->prepare("SELECT image_url FROM product_images WHERE id=?");
    $r->execute([$del_img_id]); $row = $r->fetch();
    if ($row) { delete_file($row['image_url']); $db->prepare("DELETE FROM product_images WHERE id=?")->execute([$del_img_id]); }
    header("Location: products.php?edit=$pid_for_del&msg=Foto+dihapus"); exit;
}

if (isset($_GET['msg'])) $msg = htmlspecialchars($_GET['msg']);

/* ============================================================
   LOAD PRODUCTS LIST
   ============================================================ */
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$categories = ['Curtain','Vitrase & Sheer','Roller Blind','Motorized Smart System','Wood & Bamboo Blind'];

// Load gallery images for editing product
$edit_images = [];
if ($editing) {
    $gi = $db->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC");
    $gi->execute([$editing['id']]);
    $edit_images = $gi->fetchAll();
    $edit_opts = $editing['options_json'] ? json_decode($editing['options_json'], true) : [];
    $edit_colors = implode("\n", $edit_opts['colors'] ?? []);
    $edit_stitch = implode("\n", $edit_opts['stitch'] ?? []);
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
<style>*{box-sizing:border-box;} textarea,input,select{outline:none;} .tab-active{border-bottom:2px solid #741122;color:#741122;}</style>
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

  <form method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-6">
    <input type="hidden" name="_action" value="save"/>
    <input type="hidden" name="product_id" value="<?= $editing['id'] ?? 0 ?>"/>

    <!-- LEFT: Main Info -->
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-5">

      <!-- Basic Info -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Informasi Dasar</h2>

        <div>
          <label class="label">Nama Produk <span class="text-red-500">*</span></label>
          <input type="text" name="name" id="name" required
                 value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                 class="input" placeholder="cth. Aura Drape — French Linen Blend"/>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Kategori <span class="text-red-500">*</span></label>
            <select name="category" class="input">
              <?php foreach($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= ($editing['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
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
            <label class="label">Harga Coret (Original)</label>
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

      <!-- Variants -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Varian Produk</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Warna Tersedia</label>
            <p class="text-muted text-[11px] mb-1.5">Satu warna per baris</p>
            <textarea name="colors" rows="5" class="input resize-none font-mono text-[12px]" placeholder="Oatmeal Cream&#10;Warm Sand&#10;Slate Charcoal"><?= htmlspecialchars($edit_colors ?? '') ?></textarea>
          </div>
          <div>
            <label class="label">Gaya Jahitan (Stitch)</label>
            <p class="text-muted text-[11px] mb-1.5">Satu gaya per baris</p>
            <textarea name="stitch" rows="5" class="input resize-none font-mono text-[12px]" placeholder="Ripple Fold 2.2x&#10;Double Pinch Pleat&#10;Eyelet Minimalist"><?= htmlspecialchars($edit_stitch ?? '') ?></textarea>
          </div>
        </div>
      </div>

    </div><!-- /LEFT -->

    <!-- RIGHT: Images -->
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-5">

      <!-- Cover Image -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-3">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Cover Image <?= !$editing ? '<span class="text-red-500">*</span>' : '' ?></h2>
        <?php if ($editing && $editing['cover_image']): ?>
        <div class="aspect-[3/4] rounded-[4px] overflow-hidden bg-cream-dark border border-border">
          <img src="<?= htmlspecialchars($editing['cover_image']) ?>" alt="Cover" class="w-full h-full object-cover"/>
        </div>
        <p class="font-jakarta text-muted text-[11px]">Upload baru untuk mengganti cover.</p>
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

      <!-- Gallery Images -->
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-3">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Foto Galeri Detail</h2>
        <?php if (!empty($edit_images)): ?>
        <div class="grid grid-cols-2 gap-2">
          <?php foreach($edit_images as $gi): ?>
          <div class="relative group aspect-square rounded-[3px] overflow-hidden border border-border">
            <img src="<?= htmlspecialchars($gi['image_url']) ?>" class="w-full h-full object-cover"/>
            <a href="products.php?edit=<?= $editing['id'] ?>&del_img=<?= $gi['id'] ?>"
               onclick="return confirm('Hapus foto ini?')"
               class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[11px] tracking-[1px] uppercase font-semibold">
              Hapus
            </a>
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
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Harga</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors">
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
        <tr><td colspan="5" class="px-4 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada produk. <a href="products.php?add=1" class="text-brand hover:underline">Tambah sekarang →</a></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>

<style>
.label  { display:block; font-family:'Plus Jakarta Sans',sans-serif; font-weight:500; font-size:12px; letter-spacing:0.6px; text-transform:uppercase; color:#686561; margin-bottom:6px; }
.input  { display:block; width:100%; border:1px solid #ece8e1; border-radius:4px; padding:9px 12px; font-family:'Plus Jakarta Sans',sans-serif; font-size:13px; color:#222220; background:#fff; transition:border-color 0.15s; }
.input:focus { border-color:#741122; }
select.input { appearance:auto; }
</style>
<script>
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
// Auto-convert features: newlines → pipe for submission
document.querySelector('form')?.addEventListener('submit', function() {
  const feat = document.querySelector('[name="features"]');
  if (feat) feat.value = feat.value.split('\n').map(s=>s.trim()).filter(Boolean).join('|');
});
</script>
</body>
</html>
