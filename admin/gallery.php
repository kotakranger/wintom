<?php
// admin/gallery.php — Manajemen Portofolio Proyek (tabel + modal tambah/edit)
// Kategori proyek = kategori produk (tabel categories), agar portofolio menunjukkan hasil nyata produk yang dijual.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/admin-modal.php';
require_once __DIR__ . '/../includes/admin-actions.php';
require_once __DIR__ . '/../includes/image-upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

$db  = get_db();
$msg = '';
$err = '';
$reopen = null;   // id modal yang dibuka ulang setelah aksi (mis. kelola foto / validasi gagal)

const MAX_PROJECT_PHOTOS = 12;

$product_categories = $db->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);

/** Simpan foto proyek (validasi + kompres standar). Alasan gagal dikumpulkan di $errors. */
function upload_gallery_img(array $file, array &$errors): string|false {
    $url = store_uploaded_image($file, 'gallery', $e, 'gal_');
    if (!$url && $e) $errors[] = $file['name'] . ': ' . $e;
    return $url;
}

function delete_gal_file(string $url): void {
    if (str_starts_with($url, '/uploads/')) {
        $base = realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR;
        $path = realpath(__DIR__ . '/..' . $url);
        if ($path && str_starts_with($path, $base)) image_delete_with_variants($path);
    }
}

$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['_action'] ?? '') : '';
$posted_cat = (int)($_POST['category_id'] ?? 0);
$valid_cat  = isset($product_categories[$posted_cat]);

/* ============================================================
   ACTION: TAMBAH PROYEK (foto pertama = cover)
   ============================================================ */
if ($action === 'upload') {
    $title = trim($_POST['title'] ?? '');
    if (!$title || !$valid_cat) {
        $err = 'Judul dan kategori wajib diisi.';
    } elseif (!has_uploaded_file($_FILES['photo'] ?? null)) {
        $err = 'Pilih minimal satu foto proyek.';
    } else {
        $urls = []; $upload_errors = [];
        foreach (array_slice(uploaded_files($_FILES['photo']), 0, MAX_PROJECT_PHOTOS) as $f) {
            if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
            $url = upload_gallery_img($f, $upload_errors);
            if ($url) $urls[] = $url;
        }
        if ($urls) {
            $db->prepare("INSERT INTO galleries (title, category_id, image_url) VALUES (?,?,?)")->execute([$title, $posted_cat, $urls[0]]);
            $gid = (int)$db->lastInsertId();
            $ins = $db->prepare("INSERT INTO gallery_images (gallery_id, image_url, sort_order) VALUES (?,?,?)");
            foreach (array_slice($urls, 1) as $k => $u) $ins->execute([$gid, $u, $k]);
            $msg = 'Proyek ditambahkan dengan ' . count($urls) . ' foto.' . ($upload_errors ? ' Dilewati: ' . implode('; ', $upload_errors) : '');
        } else {
            $err = 'Gagal upload foto. ' . implode('; ', $upload_errors);
        }
    }
    if ($err) $reopen = 'add-project';
}

/* ============================================================
   ACTION: UBAH JUDUL / KATEGORI
   ============================================================ */
if ($action === 'update') {
    $gid   = (int)($_POST['gallery_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($gid && $title && $valid_cat) {
        $db->prepare("UPDATE galleries SET title=?, category_id=? WHERE id=?")->execute([$title, $posted_cat, $gid]);
        $msg = 'Proyek berhasil diperbarui.';
    } else {
        $err = 'Judul dan kategori wajib diisi.';
        $reopen = 'edit-' . $gid;
    }
}

/* ============================================================
   ACTION: HAPUS PROYEK (beserta semua file foto)
   ============================================================ */
if ($action === 'delete') {
    $gid = (int)($_POST['gallery_id'] ?? 0);
    $r = $db->prepare("SELECT image_url FROM galleries WHERE id=?");
    $r->execute([$gid]);
    if ($cover = $r->fetchColumn()) {
        delete_gal_file($cover);
        $ex = $db->prepare("SELECT image_url FROM gallery_images WHERE gallery_id=?");
        $ex->execute([$gid]);
        foreach ($ex->fetchAll(PDO::FETCH_COLUMN) as $u) delete_gal_file($u);
        $db->prepare("DELETE FROM galleries WHERE id=?")->execute([$gid]);
        $msg = 'Proyek berhasil dihapus.';
    }
}

/* ============================================================
   ACTION: TAMBAH FOTO KE PROYEK
   ============================================================ */
if ($action === 'add_photos') {
    $gid = (int)($_POST['gallery_id'] ?? 0);
    $reopen = 'edit-' . $gid;
    $exists = $db->prepare("SELECT COUNT(*) FROM galleries WHERE id=?");
    $exists->execute([$gid]);
    if ($exists->fetchColumn() && has_uploaded_file($_FILES['photo'] ?? null)) {
        $cnt = $db->prepare("SELECT COUNT(*), COALESCE(MAX(sort_order), -1) FROM gallery_images WHERE gallery_id=?");
        $cnt->execute([$gid]);
        [$have, $max_sort] = $cnt->fetch(PDO::FETCH_NUM);
        $room = MAX_PROJECT_PHOTOS - 1 - (int)$have;   // -1 untuk cover
        $ins = $db->prepare("INSERT INTO gallery_images (gallery_id, image_url, sort_order) VALUES (?,?,?)");
        $added = 0; $upload_errors = [];
        foreach (uploaded_files($_FILES['photo']) as $f) {
            if ($added >= $room) break;
            if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
            $url = upload_gallery_img($f, $upload_errors);
            if ($url) { $ins->execute([$gid, $url, (int)$max_sort + 1 + $added]); $added++; }
        }
        if ($added) $msg = "$added foto ditambahkan ke proyek." . ($upload_errors ? ' Dilewati: ' . implode('; ', $upload_errors) : '');
        else $err = $room <= 0 ? 'Maksimal ' . MAX_PROJECT_PHOTOS . ' foto per proyek.' : 'Gagal upload foto. ' . implode('; ', $upload_errors);
    }
}

/* ============================================================
   ACTION: HAPUS SATU FOTO / JADIKAN COVER
   ============================================================ */
if ($action === 'del_photo' || $action === 'make_cover') {
    $pid = (int)($_POST['photo_id'] ?? 0);
    $r = $db->prepare("SELECT gi.id, gi.gallery_id, gi.image_url, g.image_url AS cover FROM gallery_images gi JOIN galleries g ON g.id = gi.gallery_id WHERE gi.id=?");
    $r->execute([$pid]);
    if ($row = $r->fetch()) {
        $reopen = 'edit-' . $row['gallery_id'];
        if ($action === 'del_photo') {
            delete_gal_file($row['image_url']);
            $db->prepare("DELETE FROM gallery_images WHERE id=?")->execute([$pid]);
            $msg = 'Foto dihapus dari proyek.';
        } else {
            // Tukar: foto ini jadi cover, cover lama jadi foto tambahan
            $db->prepare("UPDATE galleries SET image_url=? WHERE id=?")->execute([$row['image_url'], $row['gallery_id']]);
            $db->prepare("UPDATE gallery_images SET image_url=? WHERE id=?")->execute([$row['cover'], $pid]);
            $msg = 'Cover proyek diperbarui.';
        }
    }
}

/* ============================================================
   LOAD DATA
   ============================================================ */
$filter = (string)($_GET['cat'] ?? '');   // '' = semua, 'none' = tanpa kategori, angka = id kategori
$sql = "SELECT g.*, c.name AS category_name FROM galleries g LEFT JOIN categories c ON c.id = g.category_id";
$params = [];
if ($filter === 'none') {
    $sql .= " WHERE g.category_id IS NULL";
} elseif ($filter !== '' && ctype_digit($filter)) {
    $sql .= " WHERE g.category_id = ?";
    $params[] = (int)$filter;
}
$sql .= " ORDER BY g.created_at DESC, g.id DESC";
$st = $db->prepare($sql);
$st->execute($params);
$items = $st->fetchAll();

$extras = [];
if ($items) {
    $ids = implode(',', array_map('intval', array_column($items, 'id')));
    foreach ($db->query("SELECT * FROM gallery_images WHERE gallery_id IN ($ids) ORDER BY sort_order, id") as $row) {
        $extras[$row['gallery_id']][] = $row;
    }
}

$counts = [];
foreach ($db->query("SELECT COALESCE(category_id, 0) cid, COUNT(*) c FROM galleries GROUP BY cid") as $row) {
    $counts[(int)$row['cid']] = (int)$row['c'];
}
$total_all = array_sum($counts);
$uncategorized = $counts[0] ?? 0;

/** <select> kategori produk */
function category_select(array $cats, ?int $selected, string $id): string {
    $h = '<select id="' . $id . '" name="category_id" class="input" required><option value="">— Pilih kategori produk —</option>';
    foreach ($cats as $cid => $name) {
        $h .= '<option value="' . (int)$cid . '"' . ($selected === (int)$cid ? ' selected' : '') . '>' . htmlspecialchars($name) . '</option>';
    }
    return $h . '</select>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Galeri Portofolio — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>*{box-sizing:border-box;} .label{display:block;font-family:Arial,Helvetica,sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;} .input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#242424;background:#fff;transition:border-color 0.15s;} .input:focus{border-color:#860524;outline:none;} select.input{appearance:auto;}</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<?php admin_sidebar('gallery'); ?>

<!-- ===== MAIN ===== -->
<main class="ml-[220px] flex-1 p-8 min-h-screen min-w-0">

  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Galeri Portofolio</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= $total_all ?> proyek · dikelompokkan per kategori produk</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="../gallery.php" target="_blank" class="font-jakarta text-[12px] text-brand tracking-[1px] uppercase border border-brand/30 px-4 py-2.5 rounded-[4px] hover:bg-brand/5 transition-colors">
        Lihat Galeri Publik ↗
      </a>
      <button type="button" data-modal-open="add-project"
              class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-2.5 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">
        + Tambah Proyek
      </button>
    </div>
  </div>

  <?php if ($uncategorized): ?>
  <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]">
    <?= $uncategorized ?> proyek belum punya kategori produk dan hanya tampil di tab "Semua Proyek" pada galeri publik.
    <a href="gallery.php?cat=none" class="underline font-semibold">Lihat &amp; atur kategorinya</a>
  </div>
  <?php endif; ?>

  <!-- Filter per kategori produk -->
  <div class="flex flex-wrap gap-2 mb-5">
    <?php
    $tabs = ['' => ['Semua', $total_all]];
    foreach ($product_categories as $cid => $name) $tabs[(string)$cid] = [$name, $counts[$cid] ?? 0];
    if ($uncategorized) $tabs['none'] = ['Tanpa kategori', $uncategorized];
    foreach ($tabs as $key => [$label, $n]):
        $active = ($filter === (string)$key);
    ?>
    <a href="gallery.php<?= $key !== '' ? '?cat=' . urlencode((string)$key) : '' ?>"
       class="px-3.5 py-1.5 rounded-full border font-jakarta text-[12px] font-medium transition-colors <?= $active ? 'bg-brand text-white border-brand' : 'bg-white text-muted border-border hover:border-brand/40' ?>">
      <?= htmlspecialchars($label) ?> <span class="opacity-70"><?= $n ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Tabel proyek -->
  <div class="bg-white border border-border rounded-[6px] overflow-x-auto">
    <table class="w-full min-w-[760px]">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-[96px]">Cover</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Judul Proyek</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-52">Kategori Produk</th>
          <th class="px-4 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-20">Foto</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-32">Ditambahkan</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-40">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item):
            $n_photos = 1 + count($extras[$item['id']] ?? []);
        ?>
        <tr class="border-t border-border hover:bg-cream/50 transition-colors">
          <td class="px-4 py-3">
            <img src="<?= htmlspecialchars(img_src($item['image_url'], 480)) ?>" alt="" loading="lazy"
                 class="w-16 h-12 object-cover rounded-[3px] border border-border bg-cream-dark"/>
          </td>
          <td class="px-4 py-3 font-jakarta text-dark text-[13px] font-medium"><?= htmlspecialchars($item['title']) ?></td>
          <td class="px-4 py-3 font-jakarta text-[12px]">
            <?php if ($item['category_name']): ?>
            <span class="inline-block bg-cream-dark border border-border text-dark px-2 py-0.5 rounded-[2px]"><?= htmlspecialchars($item['category_name']) ?></span>
            <?php else: ?>
            <span class="inline-block bg-amber-50 border border-amber-200 text-amber-800 px-2 py-0.5 rounded-[2px]">Belum diatur</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 font-jakarta text-dark text-[13px] text-center"><?= $n_photos ?></td>
          <td class="px-4 py-3 font-jakarta text-muted text-[12px] whitespace-nowrap"><?= htmlspecialchars(date('d M Y', strtotime($item['created_at']))) ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <?php admin_btn_edit(['modal' => 'edit-' . (int)$item['id']]); ?>
              <?php admin_btn_delete('Hapus proyek "' . $item['title'] . '"?', 'Proyek beserta semua fotonya akan dihapus permanen dan tidak bisa dikembalikan.', ['_action' => 'delete', 'gallery_id' => (int)$item['id']]); ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?>
        <tr><td colspan="6" class="px-5 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada proyek<?= $filter !== '' ? ' di kategori ini' : '' ?>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== MODAL: TAMBAH PROYEK ===== -->
  <?php admin_modal_start('add-project', 'Tambah Proyek Portofolio', 'max-w-[520px]'); ?>
    <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="upload"/>
      <div>
        <label class="label" for="add-title">Judul Proyek <span class="text-red-500">*</span></label>
        <input id="add-title" type="text" name="title" class="input" required maxlength="255"
               value="<?= htmlspecialchars($reopen === 'add-project' ? ($_POST['title'] ?? '') : '') ?>"
               placeholder="cth. Ripple Fold Linen — Grand Penthouse PIK"/>
      </div>
      <div>
        <label class="label" for="add-cat">Kategori Produk <span class="text-red-500">*</span></label>
        <?= category_select($product_categories, $reopen === 'add-project' ? $posted_cat : null, 'add-cat') ?>
        <p class="font-jakarta text-muted text-[10px] mt-1.5">Produk yang dipasang di proyek ini. Kategori baru dibuat di menu <a href="categories.php" class="underline">Kategori</a>.</p>
      </div>
      <div>
        <label class="label">Foto Proyek <span class="text-red-500">*</span></label>
        <?= image_dropzone(['name' => 'photo', 'multiple' => true, 'required' => true, 'max_files' => MAX_PROJECT_PHOTOS]) ?>
        <p class="font-jakarta text-muted text-[10px] mt-1.5">Foto pertama jadi cover proyek.</p>
      </div>
      <div class="flex gap-2 pt-1">
        <button type="button" data-modal-close class="flex-1 border border-border text-muted font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase py-3 rounded-[4px] hover:text-dark transition-colors">Batal</button>
        <button type="submit" class="flex-[2] bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors shadow-sm">Simpan Proyek</button>
      </div>
    </form>
  <?php admin_modal_end(); ?>

  <!-- ===== MODAL: EDIT PROYEK (satu per baris) ===== -->
  <?php foreach ($items as $item):
      $gid = (int)$item['id'];
      $photos = array_merge(
          [['id' => null, 'image_url' => $item['image_url']]],
          $extras[$gid] ?? []
      );
  ?>
  <?php admin_modal_start('edit-' . $gid, 'Edit Proyek', 'max-w-[640px]'); ?>
    <!-- Info proyek -->
    <form method="POST" class="flex flex-col gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="update"/>
      <input type="hidden" name="gallery_id" value="<?= $gid ?>"/>
      <div>
        <label class="label" for="edit-title-<?= $gid ?>">Judul Proyek <span class="text-red-500">*</span></label>
        <input id="edit-title-<?= $gid ?>" type="text" name="title" class="input" required maxlength="255" value="<?= htmlspecialchars($item['title']) ?>"/>
      </div>
      <div>
        <label class="label" for="edit-cat-<?= $gid ?>">Kategori Produk <span class="text-red-500">*</span></label>
        <?= category_select($product_categories, $item['category_id'] !== null ? (int)$item['category_id'] : null, 'edit-cat-' . $gid) ?>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-6 py-2.5 rounded-[4px] hover:bg-[#6a041c] transition-colors">Simpan Info</button>
      </div>
    </form>

    <!-- Foto proyek (form terpisah, tidak bersarang) -->
    <div class="border-t border-border mt-6 pt-5">
      <p class="label">Foto Proyek (<?= count($photos) ?>/<?= MAX_PROJECT_PHOTOS ?>)</p>
      <?php if (count($photos) < MAX_PROJECT_PHOTOS): ?>
        <form method="POST" enctype="multipart/form-data" class="mb-3">
          <?= csrf_field() ?>
          <input type="hidden" name="_action" value="add_photos"/>
          <input type="hidden" name="gallery_id" value="<?= $gid ?>"/>
          <?= image_dropzone(['name' => 'photo', 'multiple' => true, 'max_files' => MAX_PROJECT_PHOTOS - count($photos), 'auto_submit' => true, 'compact' => true, 'title' => 'Tambah foto — seret & lepas atau klik']) ?>
        </form>
      <?php endif; ?>
      <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
        <?php foreach ($photos as $k => $ph): ?>
        <div class="relative aspect-[4/3] rounded-[3px] overflow-hidden border <?= $k === 0 ? 'border-2 border-brand' : 'border-border' ?> group">
          <img src="<?= htmlspecialchars(img_src($ph['image_url'], 480)) ?>" alt="" loading="lazy" class="w-full h-full object-cover"/>
          <?php if ($k === 0): ?>
          <span class="absolute top-1 left-1 bg-brand text-white font-jakarta font-semibold text-[9px] tracking-[1px] uppercase px-1.5 py-0.5 rounded-[2px]">Cover</span>
          <?php else: ?>
          <div class="absolute inset-0 bg-black/55 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1.5">
            <form method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="_action" value="make_cover"/>
              <input type="hidden" name="photo_id" value="<?= (int)$ph['id'] ?>"/>
              <button type="submit" class="font-jakarta text-white text-[10px] uppercase tracking-[0.5px] bg-brand/90 hover:bg-brand px-2.5 py-1 rounded-[2px]">Jadikan Cover</button>
            </form>
            <form method="POST" onsubmit="return confirm('Hapus foto ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="_action" value="del_photo"/>
              <input type="hidden" name="photo_id" value="<?= (int)$ph['id'] ?>"/>
              <button type="submit" class="font-jakarta text-white text-[10px] uppercase tracking-[0.5px] bg-red-500/90 hover:bg-red-600 px-2.5 py-1 rounded-[2px]">Hapus</button>
            </form>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="font-jakarta text-muted text-[11px] mt-2.5">Foto cover tampil pertama di galeri; foto lain bisa digeser (slider). Untuk mengganti cover, arahkan kursor ke foto lalu pilih "Jadikan Cover".</p>
    </div>
  <?php admin_modal_end(); ?>
  <?php endforeach; ?>

</main>

<?php image_upload_script(); ?>
<?php admin_modal_script($reopen); ?>
<?php admin_actions_assets(); ?>
</body>
</html>
