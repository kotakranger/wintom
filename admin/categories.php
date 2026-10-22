<?php
// admin/categories.php — Manajemen Kategori Produk
// Kategori = master data untuk produk & portofolio: harga per meter (kalkulator), foto & tagline (kartu di landing page).
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-modal.php';
require_once __DIR__ . '/../includes/image-upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$db = get_db();

const LANDING_CATEGORY_SLOTS = 6;   // jumlah kartu kategori di landing page (urutan teratas)

/** "100.000" / "100000" -> 100000; anything negative or non-numeric -> null. */
function parse_price($raw): ?int {
    $digits = preg_replace('/[.\s,]/', '', trim((string) $raw));
    if ($digits === '' ) return 0;
    return ctype_digit($digits) && strlen($digits) <= 9 ? (int) $digits : null;
}

function delete_category_image(?string $url): void {
    if ($url && str_starts_with($url, '/uploads/')) {
        $base = realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR;
        $path = realpath(__DIR__ . '/..' . $url);
        if ($path && str_starts_with($path, $base)) @unlink($path);
    }
}

$msg = '';
$err = '';
$reopen = null;
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['_action'] ?? '') : '';

/* ============================================================
   ACTION: ADD / UPDATE CATEGORY
   ============================================================ */
if ($action === 'add' || $action === 'update') {
    $cat_id  = (int) ($_POST['cat_id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $ppm     = parse_price($_POST['price_per_meter'] ?? '0');
    $tagline = trim(preg_replace('/\s+/u', ' ', (string)($_POST['tagline'] ?? '')));
    $sort    = (int) ($_POST['sort_order'] ?? 0);
    $reopen  = $action === 'add' ? 'add-category' : 'edit-cat-' . $cat_id;

    $current = null;
    if ($action === 'update') {
        $st = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $st->execute([$cat_id]);
        $current = $st->fetch();
    }

    if ($action === 'update' && !$current) {
        $err = 'Kategori tidak ditemukan.';
    } elseif (!$name) {
        $err = 'Nama kategori wajib diisi.';
    } elseif ($ppm === null) {
        $err = 'Harga per meter harus berupa angka (0 atau lebih).';
    } elseif (mb_strlen($tagline) > 120) {
        $err = 'Tagline maksimal 120 karakter.';
    } else {
        $dup = $db->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
        $dup->execute([$name, $cat_id]);
        if ($dup->fetch()) {
            $err = 'Kategori "' . $name . '" sudah ada.';
        }
    }

    // Foto (opsional): validasi + kompres standar
    $image_url = $current['image_url'] ?? null;
    $new_image = null;
    if (!$err && has_uploaded_file($_FILES['image'] ?? null)) {
        $new_image = store_uploaded_image(uploaded_files($_FILES['image'])[0], 'categories', $upload_err, 'cat_');
        if (!$new_image) $err = 'Foto kategori: ' . $upload_err;
    }

    if (!$err) {
        if ($new_image) {
            delete_category_image($image_url);
            $image_url = $new_image;
        } elseif ($action === 'update' && !empty($_POST['remove_image'])) {
            delete_category_image($image_url);
            $image_url = null;
        }
        if ($action === 'add') {
            $sort = (int) $db->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM categories')->fetchColumn();
            $db->prepare('INSERT INTO categories (name, price_per_meter, image_url, tagline, sort_order) VALUES (?, ?, ?, ?, ?)')
               ->execute([$name, $ppm, $image_url, $tagline ?: null, $sort]);
            $msg = 'Kategori "' . $name . '" berhasil ditambahkan.';
        } else {
            // Produk & portofolio merujuk ke category_id, jadi nama/harga baru langsung berlaku.
            $db->prepare('UPDATE categories SET name = ?, price_per_meter = ?, image_url = ?, tagline = ?, sort_order = ? WHERE id = ?')
               ->execute([$name, $ppm, $image_url, $tagline ?: null, $sort, $cat_id]);
            $msg = 'Kategori "' . $name . '" berhasil diperbarui.';
        }
        $reopen = null;
    } elseif ($new_image) {
        delete_category_image($new_image);   // jangan tinggalkan file yatim saat validasi lain gagal
    }
}

/* ============================================================
   ACTION: DELETE CATEGORY
   ============================================================ */
if ($action === 'delete') {
    $cat_id = (int) ($_POST['cat_id'] ?? 0);
    $cat_row = $db->prepare('SELECT name, image_url FROM categories WHERE id = ?');
    $cat_row->execute([$cat_id]);
    if ($cat_data = $cat_row->fetch()) {
        $count = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $count->execute([$cat_id]);
        $product_count = (int) $count->fetchColumn();
        if ($product_count > 0) {
            $err = "Kategori \"{$cat_data['name']}\" masih digunakan oleh {$product_count} produk. Pindahkan atau hapus produk terlebih dahulu.";
        } else {
            $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$cat_id]);
            delete_category_image($cat_data['image_url']);
            $msg = 'Kategori "' . $cat_data['name'] . '" berhasil dihapus.';
        }
    }
}

/* ============================================================
   LOAD CATEGORIES + PRODUCT COUNT
   ============================================================ */
$categories = $db->query('
    SELECT c.*, COALESCE(p_cnt.cnt, 0) AS product_count,
           (SELECT COUNT(*) FROM galleries g WHERE g.category_id = c.id) AS gallery_count
    FROM categories c
    LEFT JOIN (SELECT category_id, COUNT(*) AS cnt FROM products GROUP BY category_id) p_cnt
      ON p_cnt.category_id = c.id
    ORDER BY c.sort_order ASC, c.name ASC
')->fetchAll();

$field = 'w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-[13px] text-dark focus:border-brand transition-colors';
$label = 'block font-jakarta font-medium text-[12px] tracking-[0.6px] uppercase text-muted mb-1.5';

/** Isi form kategori (dipakai modal tambah & edit). */
function category_fields(?array $c, string $field, string $label, bool $repost): void {
    $v = fn(string $k, $default = '') => $repost ? ($_POST[$k] ?? $default) : ($c[$k] ?? $default);
    ?>
    <div class="grid grid-cols-2 gap-4">
      <div class="col-span-2">
        <label class="<?= $label ?>">Nama Kategori <span class="text-red-500">*</span></label>
        <input type="text" name="name" required maxlength="120" placeholder="cth. Roman Blind" value="<?= htmlspecialchars((string)$v('name')) ?>" class="<?= $field ?>"/>
      </div>
      <div>
        <label class="<?= $label ?>">Harga per Meter (Rp)</label>
        <input type="number" name="price_per_meter" min="0" step="1" value="<?= (int)$v('price_per_meter', 0) ?>" class="<?= $field ?>"/>
      </div>
      <?php if ($c): ?>
      <div>
        <label class="<?= $label ?>">Urutan</label>
        <input type="number" name="sort_order" step="1" value="<?= (int)$v('sort_order', 0) ?>" class="<?= $field ?>"/>
      </div>
      <?php endif; ?>
      <div class="col-span-2">
        <label class="<?= $label ?>">Tagline <span class="normal-case tracking-normal font-normal">(opsional)</span></label>
        <input type="text" name="tagline" maxlength="120" placeholder="cth. Elevate your every space" value="<?= htmlspecialchars((string)$v('tagline')) ?>" class="<?= $field ?>"/>
        <p class="font-jakarta text-muted text-[10.5px] mt-1">Teks kecil di bawah nama kategori pada kartu landing page.</p>
      </div>
      <div class="col-span-2">
        <label class="<?= $label ?>">Foto Kategori <span class="normal-case tracking-normal font-normal">(untuk kartu landing page)</span></label>
        <?php if ($c && !empty($c['image_url'])): ?>
        <div class="flex items-center gap-3 mb-2.5">
          <img src="<?= htmlspecialchars(asset_url($c['image_url'])) ?>" alt="" class="w-24 h-16 object-cover rounded-[3px] border border-border"/>
          <label class="inline-flex items-center gap-2 font-jakarta text-[12px] text-red-600 cursor-pointer">
            <input type="checkbox" name="remove_image" value="1" class="accent-red-600"/> Hapus foto ini
          </label>
        </div>
        <?php endif; ?>
        <?= image_dropzone(['name' => 'image', 'title' => $c && !empty($c['image_url']) ? 'Ganti foto — seret & lepas atau klik' : 'Seret & lepas foto, atau klik untuk memilih']) ?>
      </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Kategori Produk — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}};</script>
<style>*{box-sizing:border-box;} input{outline:none;}</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-[220px] shrink-0 bg-white border-r border-border flex flex-col min-h-screen fixed top-0 left-0 h-full z-20">
  <div class="p-6 border-b border-border">
    <a href="../" target="_blank" class="flex flex-col group">
      <span class="font-playfair font-bold text-brand text-[22px] leading-[28px]">wintom</span>
      <span class="font-jakarta font-semibold text-muted text-[8px] tracking-[2.5px] uppercase group-hover:text-brand transition-colors">CURTAIN ADMIN</span>
    </a>
  </div>
  <nav class="flex flex-col p-4 gap-1 flex-1">
    <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Dashboard</a>
    <a href="inquiries.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Inquiry</a>
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Produk</a>
    <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Kategori</a>
    <a href="gallery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Galeri Portofolio</a>
    <a href="videos.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Konten Video</a>
  </nav>
  <div class="p-4 border-t border-border">
    <form method="POST" action="logout.php" class="inline">
      <?= csrf_field() ?>
      <button type="submit" class="block px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors bg-none border-none cursor-pointer">Logout</button>
    </form>
  </div>
</aside>

<!-- MAIN -->
<main class="ml-[220px] flex-1 p-8 min-h-screen min-w-0">

  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Manajemen Kategori</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= count($categories) ?> kategori · <?= LANDING_CATEGORY_SLOTS ?> urutan teratas tampil sebagai kartu di landing page</p>
    </div>
    <button type="button" data-modal-open="add-category"
            class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-2.5 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">
      + Tambah Kategori
    </button>
  </div>

  <div class="bg-white border border-border rounded-[6px] overflow-x-auto">
    <table class="w-full min-w-[820px]">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-12">#</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-[96px]">Foto</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama Kategori</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Harga / meter</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20">Produk</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-24">Portofolio</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-40">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $i => $cat): ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors">
          <td class="px-4 py-3 font-jakarta text-muted text-[12px]">
            <?= $i + 1 ?>
            <?php if ($i < LANDING_CATEGORY_SLOTS): ?><span class="block text-[9px] uppercase tracking-[0.5px] text-brand" title="Tampil di landing page">Landing</span><?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <?php if ($cat['image_url']): ?>
            <img src="<?= htmlspecialchars(asset_url($cat['image_url'])) ?>" alt="" loading="lazy" class="w-16 h-12 object-cover rounded-[3px] border border-border"/>
            <?php else: ?>
            <span class="w-16 h-12 flex items-center justify-center rounded-[3px] border border-dashed border-amber-300 bg-amber-50 font-jakarta text-amber-700 text-[9px] text-center leading-tight">Belum ada foto</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <p class="font-jakarta font-medium text-dark text-[13px]"><?= htmlspecialchars($cat['name']) ?></p>
            <?php if ($cat['tagline']): ?><p class="font-jakarta text-muted text-[11px] mt-0.5"><?= htmlspecialchars($cat['tagline']) ?></p><?php endif; ?>
          </td>
          <td class="px-4 py-3 text-right font-jakarta text-dark text-[13px] whitespace-nowrap">Rp <?= number_format((int) $cat['price_per_meter'], 0, ',', '.') ?></td>
          <td class="px-4 py-3 text-center font-jakarta text-dark text-[13px]"><?= (int)$cat['product_count'] ?></td>
          <td class="px-4 py-3 text-center font-jakarta text-dark text-[13px]"><?= (int)$cat['gallery_count'] ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <button type="button" data-modal-open="edit-cat-<?= (int)$cat['id'] ?>"
                      class="font-jakarta text-[11px] tracking-[0.5px] uppercase text-brand border border-brand/30 px-3 py-1.5 rounded-[3px] hover:bg-brand/5 transition-colors">Edit</button>
              <?php if ((int) $cat['product_count'] === 0): ?>
              <form method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode('Hapus kategori "' . $cat['name'] . '"?' . ((int)$cat['gallery_count'] > 0 ? "\n" . $cat['gallery_count'] . ' proyek portofolio akan menjadi "tanpa kategori".' : '')), ENT_QUOTES) ?>)">
                <?= csrf_field() ?>
                <input type="hidden" name="_action" value="delete"/>
                <input type="hidden" name="cat_id" value="<?= (int)$cat['id'] ?>"/>
                <button type="submit" class="font-jakarta text-[11px] text-red-500 border border-red-200 px-3 py-1.5 rounded-[3px] hover:bg-red-50 transition-colors">Hapus</button>
              </form>
              <?php else: ?>
              <span class="font-jakarta text-[10px] text-muted/60 italic px-2" title="Tidak bisa dihapus — masih ada produk">Terkunci</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="7" class="px-5 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada kategori.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <p class="mt-3 font-jakarta text-muted text-[11px]">Kategori yang masih dipakai produk tidak bisa dihapus. Nama, harga, foto & tagline langsung berlaku untuk semua produk, portofolio, dan landing page.</p>

  <!-- ===== MODAL: TAMBAH ===== -->
  <?php admin_modal_start('add-category', 'Tambah Kategori Baru', 'max-w-[560px]'); ?>
    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="add"/>
      <?php category_fields(null, $field, $label, $reopen === 'add-category'); ?>
      <div class="flex gap-2 pt-1">
        <button type="button" data-modal-close class="flex-1 border border-border text-muted font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase py-3 rounded-[4px] hover:text-dark transition-colors">Batal</button>
        <button type="submit" class="flex-[2] bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">Simpan Kategori</button>
      </div>
    </form>
  <?php admin_modal_end(); ?>

  <!-- ===== MODAL: EDIT (satu per baris) ===== -->
  <?php foreach ($categories as $cat): ?>
  <?php admin_modal_start('edit-cat-' . (int)$cat['id'], 'Edit Kategori', 'max-w-[560px]'); ?>
    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="update"/>
      <input type="hidden" name="cat_id" value="<?= (int)$cat['id'] ?>"/>
      <?php category_fields($cat, $field, $label, $reopen === 'edit-cat-' . (int)$cat['id']); ?>
      <div class="flex gap-2 pt-1">
        <button type="button" data-modal-close class="flex-1 border border-border text-muted font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase py-3 rounded-[4px] hover:text-dark transition-colors">Batal</button>
        <button type="submit" class="flex-[2] bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">Simpan Perubahan</button>
      </div>
    </form>
  <?php admin_modal_end(); ?>
  <?php endforeach; ?>
</main>

<?php image_upload_script(); ?>
<?php admin_modal_script($reopen); ?>
</body>
</html>
