<?php
// admin/gallery.php — Manajemen Galeri Portofolio
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$db  = get_db();
$msg = '';
$err = '';

function upload_gallery_img(array $file): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 8 * 1024 * 1024) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('gal_', true) . '.' . strtolower($ext);
    $dest = __DIR__ . '/../uploads/gallery/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return '/uploads/gallery/' . $name;
}

function delete_gal_file(string $url): void {
    if (str_starts_with($url, '/uploads/')) {
        $path = __DIR__ . '/..' . $url;
        if (file_exists($path)) unlink($path);
    }
}

/* ============================================================
   ACTION: UPLOAD NEW GALLERY PHOTO
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'upload') {
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (!$title || !$category) {
        $err = 'Judul dan kategori wajib diisi.';
    } elseif (empty($_FILES['photo']['name'])) {
        $err = 'Pilih file foto untuk diupload.';
    } else {
        // Multiple file support
        $files = $_FILES['photo'];
        $is_multi = is_array($files['name']);
        $count_ok = 0;

        if ($is_multi) {
            $ins = $db->prepare("INSERT INTO galleries (title, category, image_url) VALUES (?,?,?)");
            foreach ($files['tmp_name'] as $i => $tmp) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $single = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $tmp,
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $url = upload_gallery_img($single);
                if ($url) { $ins->execute([$title, $category, $url]); $count_ok++; }
            }
        } else {
            $url = upload_gallery_img($files);
            if ($url) {
                $db->prepare("INSERT INTO galleries (title, category, image_url) VALUES (?,?,?)")->execute([$title, $category, $url]);
                $count_ok = 1;
            }
        }

        if ($count_ok > 0) $msg = "$count_ok foto berhasil diupload ke galeri.";
        else $err = 'Gagal upload foto. Pastikan format JPG/PNG/WEBP dan ukuran maks 8MB.';
    }
}

/* ============================================================
   ACTION: DELETE GALLERY PHOTO
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $gid = (int)($_POST['gallery_id'] ?? 0);
    if ($gid > 0) {
        $r = $db->prepare("SELECT image_url FROM galleries WHERE id=?");
        $r->execute([$gid]); $row = $r->fetch();
        if ($row) { delete_gal_file($row['image_url']); }
        $db->prepare("DELETE FROM galleries WHERE id=?")->execute([$gid]);
        $msg = 'Foto berhasil dihapus dari galeri.';
    }
}

/* ============================================================
   ACTION: EDIT TITLE / CATEGORY
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $gid      = (int)($_POST['gallery_id'] ?? 0);
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    if ($gid && $title && $category) {
        $db->prepare("UPDATE galleries SET title=?, category=? WHERE id=?")->execute([$title, $category, $gid]);
        $msg = 'Info foto berhasil diperbarui.';
    }
}

/* ============================================================
   LOAD DATA
   ============================================================ */
$filter_cat  = $_GET['cat'] ?? 'Semua';
$categories  = ['Living Room','Penthouse Sanctuary','Dining & Atelier','Office & Commercial','Hotel & Private Villa'];

if ($filter_cat === 'Semua') {
    $items = $db->query("SELECT * FROM galleries ORDER BY created_at DESC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM galleries WHERE category=? ORDER BY created_at DESC");
    $stmt->execute([$filter_cat]);
    $items = $stmt->fetchAll();
}

$total_by_cat = [];
foreach ($categories as $cat) {
    $total_by_cat[$cat] = (int)$db->prepare("SELECT COUNT(*) FROM galleries WHERE category=?")->execute([$cat]) ? $db->query("SELECT COUNT(*) FROM galleries WHERE category='$cat'")->fetchColumn() : 0;
}
$total_all = (int)$db->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Galeri Portofolio — Wintom Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['"Playfair Display"','serif'],jakarta:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{DEFAULT:'#741122',dark:'#5a0d1a'},dark:'#222220',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>*{box-sizing:border-box;} .label{display:block;font-family:'Plus Jakarta Sans',sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;} .input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:#222220;background:#fff;transition:border-color 0.15s;} .input:focus{border-color:#741122;outline:none;} select.input{appearance:auto;}</style>
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
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Produk</a>
    <a href="gallery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Galeri Portofolio</a>
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

  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Galeri Portofolio</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= $total_all ?> foto tersimpan</p>
    </div>
    <a href="../gallery.php" target="_blank" class="font-jakarta text-[12px] text-brand tracking-[1px] uppercase border border-brand/30 px-4 py-2 rounded-[4px] hover:bg-brand/5 transition-colors">
      Lihat Galeri Publik ↗
    </a>
  </div>

  <div class="grid grid-cols-12 gap-6">

    <!-- LEFT: Upload Form -->
    <div class="col-span-12 lg:col-span-4">
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4 sticky top-6">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Upload Foto Baru</h2>

        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
          <input type="hidden" name="_action" value="upload"/>

          <div>
            <label class="label">Judul Foto <span class="text-red-500">*</span></label>
            <input type="text" name="title" class="input" required
                   placeholder="cth. Ripple Fold Linen — Grand Penthouse PIK"/>
          </div>

          <div>
            <label class="label">Kategori <span class="text-red-500">*</span></label>
            <select name="category" class="input" required>
              <option value="">— Pilih Kategori —</option>
              <?php foreach($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="label">File Foto <span class="text-red-500">*</span></label>
            <div class="border-2 border-dashed border-border rounded-[4px] p-5 text-center hover:border-brand/40 transition-colors cursor-pointer" onclick="document.getElementById('photo-input').click()">
              <svg class="size-8 text-muted mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75L7.409 10.591a2.25 2.25 0 013.182 0l5.159 5.159M14.25 12.75l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 19.5h16.5M6.75 6.75h.008v.008H6.75V6.75z"/></svg>
              <p class="font-jakarta text-muted text-[12px]">Klik untuk pilih foto</p>
              <p class="font-jakarta text-muted text-[10px] mt-1">JPG, PNG, WEBP · maks 8MB · bisa multiple</p>
            </div>
            <input type="file" id="photo-input" name="photo[]" accept="image/*" multiple class="hidden" onchange="previewPhotos(event)"/>
          </div>

          <!-- Preview -->
          <div id="photo-previews" class="grid grid-cols-3 gap-2 hidden"></div>

          <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[4px] hover:bg-[#5a0d1a] transition-colors">
            UPLOAD FOTO
          </button>
        </form>

        <!-- Category Stats -->
        <div class="border-t border-border pt-4 mt-2">
          <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase mb-3">Statistik Kategori</p>
          <div class="flex flex-col gap-2">
            <?php foreach($categories as $cat):
              $cnt = (int)$db->query("SELECT COUNT(*) FROM galleries WHERE category='".addslashes($cat)."'")->fetchColumn();
              $pct = $total_all > 0 ? round($cnt/$total_all*100) : 0;
            ?>
            <div>
              <div class="flex justify-between mb-1">
                <span class="font-jakarta text-dark text-[11px]"><?= $cat ?></span>
                <span class="font-jakarta text-muted text-[11px]"><?= $cnt ?></span>
              </div>
              <div class="h-1 bg-border rounded-full"><div class="h-1 bg-brand rounded-full" style="width:<?= $pct ?>%"></div></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Gallery Grid -->
    <div class="col-span-12 lg:col-span-8">

      <!-- Filter Tabs -->
      <div class="bg-white border border-border rounded-[6px] overflow-x-auto mb-5">
        <div class="flex items-center">
          <?php
          $tabs = array_merge(['Semua'], $categories);
          foreach($tabs as $tab):
            $is_active = ($filter_cat === $tab || ($tab === 'Semua' && $filter_cat === 'Semua'));
            $tab_count = $tab === 'Semua' ? $total_all : (int)$db->query("SELECT COUNT(*) FROM galleries WHERE category='".addslashes($tab)."'")->fetchColumn();
          ?>
          <a href="gallery.php?cat=<?= urlencode($tab) ?>"
             class="shrink-0 font-jakarta font-semibold text-[11px] tracking-[1.2px] uppercase whitespace-nowrap px-4 py-3.5 border-b-2 transition-all duration-200
                    <?= $is_active ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-dark' ?>">
            <?= $tab ?> <span class="text-[10px] opacity-60 ml-1">(<?= $tab_count ?>)</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Photo Grid -->
      <?php if (empty($items)): ?>
      <div class="bg-white border border-border rounded-[6px] py-16 text-center">
        <p class="font-playfair text-dark text-[20px] mb-2">Belum ada foto</p>
        <p class="font-jakarta text-muted text-[13px]">Upload foto baru menggunakan form di samping.</p>
      </div>
      <?php else: ?>
      <div class="columns-2 lg:columns-3 gap-4">
        <?php foreach($items as $item): ?>
        <div class="break-inside-avoid mb-4 bg-white border border-border rounded-[4px] overflow-hidden group">
          <!-- Image -->
          <div class="relative overflow-hidden">
            <img src="<?= htmlspecialchars($item['image_url']) ?>"
                 alt="<?= htmlspecialchars($item['title']) ?>"
                 class="w-full object-cover transition-transform duration-500 group-hover:scale-105"/>
            <!-- Hover overlay with delete -->
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
              <form method="POST" onsubmit="return confirm('Hapus foto ini dari galeri?')" class="z-10">
                <input type="hidden" name="_action" value="delete"/>
                <input type="hidden" name="gallery_id" value="<?= $item['id'] ?>"/>
                <button type="submit" class="bg-red-500 text-white font-jakarta font-semibold text-[11px] tracking-[1px] uppercase px-4 py-2 rounded-[3px] hover:bg-red-600 transition-colors">
                  Hapus
                </button>
              </form>
            </div>
          </div>
          <!-- Info + Quick Edit -->
          <div class="p-3">
            <p class="font-jakarta text-dark text-[12px] font-semibold leading-[16px] mb-1"><?= htmlspecialchars($item['title']) ?></p>
            <span class="inline-block bg-cream-dark border border-border font-jakarta text-muted text-[10px] tracking-[0.5px] uppercase px-2 py-0.5 rounded-[2px]"><?= htmlspecialchars($item['category']) ?></span>
            <!-- Quick edit toggle -->
            <button onclick="toggleEdit(<?= $item['id'] ?>)" class="block mt-2 font-jakarta text-brand text-[11px] tracking-[0.5px] uppercase hover:underline">Edit Info</button>
            <form method="POST" id="edit-form-<?= $item['id'] ?>" class="hidden mt-2 flex flex-col gap-2">
              <input type="hidden" name="_action" value="update"/>
              <input type="hidden" name="gallery_id" value="<?= $item['id'] ?>"/>
              <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" class="input text-[12px] py-1.5" required/>
              <select name="category" class="input text-[12px] py-1.5" required>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="bg-brand text-white font-jakarta text-[11px] tracking-[1px] uppercase py-1.5 rounded-[3px] hover:bg-[#5a0d1a] transition-colors">Simpan</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="text-center font-jakarta text-muted text-[11px] tracking-[1px] uppercase mt-4">Menampilkan <?= count($items) ?> foto</p>
      <?php endif; ?>

    </div>
  </div>
</main>

<script>
function previewPhotos(e) {
  const container = document.getElementById('photo-previews');
  container.innerHTML = '';
  container.classList.remove('hidden');
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
}
function toggleEdit(id) {
  const form = document.getElementById('edit-form-' + id);
  form.classList.toggle('hidden');
}
</script>
</body>
</html>
