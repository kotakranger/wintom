<?php
// admin/categories.php — Manajemen Kategori Produk
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$db  = get_db();
$msg = '';
$err = '';

/* ============================================================
   ACTION: ADD CATEGORY
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    if (!$name) {
        $err = 'Nama kategori wajib diisi.';
    } else {
        $check = $db->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetch()) {
            $err = 'Kategori "' . htmlspecialchars($name) . '" sudah ada.';
        } else {
            $max_sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0) FROM categories")->fetchColumn();
            $ins = $db->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)");
            $ins->execute([$name, $max_sort + 1]);
            $msg = 'Kategori "' . htmlspecialchars($name) . '" berhasil ditambahkan.';
        }
    }
}

/* ============================================================
   ACTION: DELETE CATEGORY
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $cat_id = (int)($_POST['cat_id'] ?? 0);
    if ($cat_id > 0) {
        // Check if any products use this category
        $cat_row = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $cat_row->execute([$cat_id]);
        $cat_data = $cat_row->fetch();
        if ($cat_data) {
            $count = $db->prepare("SELECT COUNT(*) FROM products WHERE category = ?");
            $count->execute([$cat_data['name']]);
            $product_count = (int)$count->fetchColumn();
            if ($product_count > 0) {
                $err = "Kategori \"{$cat_data['name']}\" masih digunakan oleh {$product_count} produk. Pindahkan atau hapus produk terlebih dahulu.";
            } else {
                $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$cat_id]);
                $msg = 'Kategori "' . htmlspecialchars($cat_data['name']) . '" berhasil dihapus.';
            }
        }
    }
}

/* ============================================================
   ACTION: RENAME CATEGORY
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'rename') {
    $cat_id   = (int)($_POST['cat_id'] ?? 0);
    $new_name = trim($_POST['new_name'] ?? '');
    if ($cat_id > 0 && $new_name) {
        // Get old name
        $old = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $old->execute([$cat_id]);
        $old_data = $old->fetch();
        if ($old_data) {
            // Check duplicate
            $dup = $db->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $dup->execute([$new_name, $cat_id]);
            if ($dup->fetch()) {
                $err = 'Kategori dengan nama tersebut sudah ada.';
            } else {
                $db->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$new_name, $cat_id]);
                // Also update products that use the old category name
                $db->prepare("UPDATE products SET category = ? WHERE category = ?")->execute([$new_name, $old_data['name']]);
                $msg = 'Kategori berhasil diubah dari "' . htmlspecialchars($old_data['name']) . '" menjadi "' . htmlspecialchars($new_name) . '".';
            }
        }
    }
}

if (isset($_GET['msg'])) $msg = htmlspecialchars($_GET['msg']);

/* ============================================================
   LOAD CATEGORIES + PRODUCT COUNT
   ============================================================ */
$categories = $db->query("
    SELECT c.*, COALESCE(p_cnt.cnt, 0) AS product_count
    FROM categories c
    LEFT JOIN (SELECT category, COUNT(*) AS cnt FROM products GROUP BY category) p_cnt
      ON p_cnt.category = c.name
    ORDER BY c.sort_order ASC, c.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Kategori Produk — Wintom Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['"Playfair Display"','serif'],jakarta:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{DEFAULT:'#741122',dark:'#5a0d1a'},dark:'#222220',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}};</script>
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
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Produk</a>
    <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Kategori</a>
    <a href="gallery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Galeri Portofolio</a>
  </nav>
  <div class="p-4 border-t border-border">
    <a href="logout.php" class="block px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors">Logout</a>
  </div>
</aside>

<!-- MAIN -->
<main class="ml-[220px] flex-1 p-8 min-h-screen">

  <!-- Toast -->
  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= $msg ?></div><?php endif; ?>
  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="mb-6">
    <h1 class="font-playfair font-semibold text-dark text-[26px]">Manajemen Kategori</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= count($categories) ?> kategori terdaftar</p>
  </div>

  <div class="grid grid-cols-12 gap-6">

    <!-- LEFT: Category List -->
    <div class="col-span-12 lg:col-span-8">
      <div class="bg-white border border-border rounded-[6px] overflow-hidden">
        <table class="w-full">
          <thead class="bg-cream-dark">
            <tr>
              <th class="px-5 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-10">#</th>
              <th class="px-5 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama Kategori</th>
              <th class="px-5 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-32">Produk</th>
              <th class="px-5 py-3 text-center font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-44">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($categories as $i => $cat): ?>
            <tr class="border-t border-border hover:bg-cream/50 transition-colors group" id="cat-row-<?= $cat['id'] ?>">
              <td class="px-5 py-3.5 font-jakarta text-muted text-[12px]"><?= $i + 1 ?></td>
              <td class="px-5 py-3.5">
                <!-- Display mode -->
                <div class="cat-display flex items-center gap-2" id="display-<?= $cat['id'] ?>">
                  <span class="font-jakarta font-medium text-dark text-[13px]"><?= htmlspecialchars($cat['name']) ?></span>
                </div>
                <!-- Rename mode (hidden) -->
                <form method="POST" class="cat-rename hidden flex items-center gap-2" id="rename-<?= $cat['id'] ?>">
                  <input type="hidden" name="_action" value="rename"/>
                  <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>"/>
                  <input type="text" name="new_name" value="<?= htmlspecialchars($cat['name']) ?>"
                         class="border border-border rounded-[3px] px-2.5 py-1.5 font-jakarta text-[13px] text-dark w-full focus:border-brand transition-colors"/>
                  <button type="submit" class="shrink-0 bg-brand text-white px-3 py-1.5 rounded-[3px] font-jakarta text-[11px] tracking-[0.5px] uppercase hover:bg-brand-dark transition-colors">Simpan</button>
                  <button type="button" onclick="cancelRename(<?= $cat['id'] ?>)" class="shrink-0 text-muted hover:text-dark font-jakarta text-[11px] tracking-[0.5px] uppercase px-2 py-1.5 transition-colors">Batal</button>
                </form>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center bg-cream-dark border border-border rounded-full px-3 py-0.5 font-jakarta font-semibold text-dark text-[12px]">
                  <?= $cat['product_count'] ?>
                </span>
              </td>
              <td class="px-5 py-3.5">
                <div class="flex items-center justify-center gap-2">
                  <button type="button" onclick="startRename(<?= $cat['id'] ?>)"
                     class="font-jakarta text-[11px] tracking-[0.5px] uppercase text-brand border border-brand/30 px-3 py-1.5 rounded-[3px] hover:bg-brand/5 transition-colors">
                    Ubah
                  </button>
                  <?php if ((int)$cat['product_count'] === 0): ?>
                  <form method="POST" onsubmit="return confirm('Hapus kategori &quot;<?= htmlspecialchars($cat['name']) ?>&quot;?')" class="inline">
                    <input type="hidden" name="_action" value="delete"/>
                    <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>"/>
                    <button type="submit" class="font-jakarta text-[11px] text-red-500 border border-red-200 px-3 py-1.5 rounded-[3px] hover:bg-red-50 transition-colors">Hapus</button>
                  </form>
                  <?php else: ?>
                  <span class="font-jakarta text-[10px] text-muted/60 italic px-3 py-1.5" title="Tidak bisa dihapus — masih ada produk">Terkunci</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <tr><td colspan="4" class="px-5 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada kategori.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RIGHT: Add Category -->
    <div class="col-span-12 lg:col-span-4">
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4 sticky top-8">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Tambah Kategori Baru</h2>
        <form method="POST" class="flex flex-col gap-3">
          <input type="hidden" name="_action" value="add"/>
          <div>
            <label class="block font-jakarta font-medium text-[12px] tracking-[0.6px] uppercase text-muted mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" name="name" required placeholder="cth. Roman Blind"
                   class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-[13px] text-dark focus:border-brand transition-colors"/>
          </div>
          <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[4px] hover:bg-[#5a0d1a] transition-colors shadow-sm">
            + TAMBAH KATEGORI
          </button>
        </form>

        <div class="mt-2 bg-cream border border-border rounded-[4px] px-4 py-3">
          <p class="font-jakarta text-muted text-[11px] leading-[16px]">
            <strong class="text-dark">Info:</strong> Kategori yang sudah digunakan oleh produk tidak bisa dihapus.
            Ubah nama kategori akan otomatis memperbarui semua produk terkait.
          </p>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
function startRename(id) {
  document.getElementById('display-' + id).classList.add('hidden');
  document.getElementById('rename-' + id).classList.remove('hidden');
  document.getElementById('rename-' + id).querySelector('input[name="new_name"]').focus();
}
function cancelRename(id) {
  document.getElementById('rename-' + id).classList.add('hidden');
  document.getElementById('display-' + id).classList.remove('hidden');
}
</script>
</body>
</html>
