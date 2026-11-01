<?php
// admin/categories.php — Manajemen Kategori Produk
// Kategori = master data untuk produk & portofolio: harga per meter (kalkulator), foto & tagline (kartu di landing page).
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-actions.php';
require_once __DIR__ . '/../includes/admin-table.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
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
        if ($path && str_starts_with($path, $base)) image_delete_with_variants($path);
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
    $wa_raw  = trim((string)($_POST['whatsapp'] ?? ''));
    $wa      = $wa_raw === '' ? null : normalize_wa_number($wa_raw);   // kosong = pakai nomor utama
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
    } elseif ($wa_raw !== '' && $wa === null) {
        $err = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
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
            $db->prepare('INSERT INTO categories (name, price_per_meter, image_url, tagline, whatsapp, sort_order) VALUES (?, ?, ?, ?, ?, ?)')
               ->execute([$name, $ppm, $image_url, $tagline ?: null, $wa, $sort]);
            $msg = 'Kategori "' . $name . '" berhasil ditambahkan.';
        } else {
            // Produk & portofolio merujuk ke category_id, jadi nama/harga baru langsung berlaku.
            $db->prepare('UPDATE categories SET name = ?, price_per_meter = ?, image_url = ?, tagline = ?, whatsapp = ?, sort_order = ? WHERE id = ?')
               ->execute([$name, $ppm, $image_url, $tagline ?: null, $wa, $sort, $cat_id]);
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
$pg = admin_paginate((int)$db->query('SELECT COUNT(*) FROM categories')->fetchColumn());
$categories = $db->query('
    SELECT c.*, COALESCE(p_cnt.cnt, 0) AS product_count,
           (SELECT COUNT(*) FROM galleries g WHERE g.category_id = c.id) AS gallery_count
    FROM categories c
    LEFT JOIN (SELECT category_id, COUNT(*) AS cnt FROM products GROUP BY category_id) p_cnt
      ON p_cnt.category_id = c.id
    ORDER BY c.sort_order ASC, c.name ASC
    LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'])->fetchAll();

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
        <label class="<?= $label ?>">WhatsApp Admin Penjualan <span class="normal-case tracking-normal font-normal">(opsional)</span></label>
        <input type="tel" name="whatsapp" inputmode="tel" maxlength="25" placeholder="Contoh: 0812 3456 7890"
               value="<?= htmlspecialchars((string)($repost ? ($_POST['whatsapp'] ?? '') : (!empty($c['whatsapp']) ? '+' . $c['whatsapp'] : ''))) ?>" class="<?= $field ?>"/>
        <p class="font-jakarta text-muted text-[10.5px] mt-1">Tujuan form penawaran &amp; tombol WhatsApp di produk kategori ini. Kosongkan untuk memakai nomor utama (<?= htmlspecialchars(SITE_PHONE) ?>).</p>
      </div>
      <div class="col-span-2">
        <label class="<?= $label ?>">Tagline <span class="normal-case tracking-normal font-normal">(opsional)</span></label>
        <input type="text" name="tagline" maxlength="120" placeholder="cth. Elevate your every space" value="<?= htmlspecialchars((string)$v('tagline')) ?>" class="<?= $field ?>"/>
        <p class="font-jakarta text-muted text-[10.5px] mt-1">Teks kecil di bawah nama kategori pada kartu landing page.</p>
      </div>
      <div class="col-span-2">
        <label class="<?= $label ?>">Foto Kategori <span class="normal-case tracking-normal font-normal">(untuk kartu landing page)</span></label>
        <?php if ($c && !empty($c['image_url'])): ?>
        <div class="flex items-center gap-3 mb-2.5">
          <img src="<?= htmlspecialchars(img_src($c['image_url'], 480)) ?>" alt="" class="w-24 h-16 object-cover rounded-[3px] border border-border"/>
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
<?php admin_sidebar('categories'); ?>

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
    <table class="w-full min-w-[960px]">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-12">#</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-[96px]">Foto</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama Kategori</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Harga / meter</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-40">WhatsApp</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20">Produk</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-24">Portofolio</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-40">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $i => $cat): ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors">
          <td class="px-4 py-3 font-jakarta text-muted text-[12px]">
            <?= $pg['offset'] + $i + 1 ?>
            <?php if ($pg['offset'] + $i < LANDING_CATEGORY_SLOTS): ?><span class="block text-[9px] uppercase tracking-[0.5px] text-brand" title="Tampil di landing page">Landing</span><?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <?php if ($cat['image_url']): ?>
            <img src="<?= htmlspecialchars(img_src($cat['image_url'], 480)) ?>" alt="" loading="lazy" class="w-16 h-12 object-cover rounded-[3px] border border-border"/>
            <?php else: ?>
            <span class="w-16 h-12 flex items-center justify-center rounded-[3px] border border-dashed border-amber-300 bg-amber-50 font-jakarta text-amber-700 text-[9px] text-center leading-tight">Belum ada foto</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <p class="font-jakarta font-medium text-dark text-[13px]"><?= htmlspecialchars($cat['name']) ?></p>
            <?php if ($cat['tagline']): ?><p class="font-jakarta text-muted text-[11px] mt-0.5"><?= htmlspecialchars($cat['tagline']) ?></p><?php endif; ?>
          </td>
          <td class="px-4 py-3 text-right font-jakarta text-dark text-[13px] whitespace-nowrap">Rp <?= number_format((int) $cat['price_per_meter'], 0, ',', '.') ?></td>
          <td class="px-4 py-3 font-jakarta text-[12px] whitespace-nowrap">
            <?php if ($cat['whatsapp']): ?>
            <a href="https://wa.me/<?= htmlspecialchars($cat['whatsapp']) ?>" target="_blank" rel="noopener" class="text-[#128c4a] hover:underline">+<?= htmlspecialchars($cat['whatsapp']) ?></a>
            <?php else: ?>
            <span class="text-muted">Nomor utama</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-center font-jakarta text-dark text-[13px]"><?= (int)$cat['product_count'] ?></td>
          <td class="px-4 py-3 text-center font-jakarta text-dark text-[13px]"><?= (int)$cat['gallery_count'] ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <?php admin_btn_edit(['modal' => 'edit-cat-' . (int)$cat['id']]); ?>
              <?php if ((int) $cat['product_count'] === 0): ?>
              <?php admin_btn_delete('Hapus kategori "' . $cat['name'] . '"?',
                  (int)$cat['gallery_count'] > 0 ? $cat['gallery_count'] . ' proyek portofolio akan menjadi "tanpa kategori".' : 'Kategori ini akan dihapus permanen.',
                  ['_action' => 'delete', 'cat_id' => (int)$cat['id']]); ?>
              <?php else: ?>
              <span class="adm-act adm-act-icon !cursor-not-allowed !text-muted/50 hover:!bg-white hover:!border-border" data-tip="Tidak bisa dihapus — masih ada <?= (int)$cat['product_count'] ?> produk" aria-label="Tidak bisa dihapus">
                <svg class="size-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 118 0v3"/></svg>
              </span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <?php admin_empty_row(8, 'Belum ada kategori', 'Tambahkan kategori pertama lewat tombol di kanan atas.'); ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php admin_pagination($pg); ?>
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
<?php admin_actions_assets(); ?>
</body>
</html>
