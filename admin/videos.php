<?php
// admin/videos.php — Konten video YouTube untuk homepage
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/youtube.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$db  = get_db();
$msg = '';
$err = '';
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['_action'] ?? '') : '';

/* ADD */
if ($action === 'add') {
    $url   = trim((string)($_POST['url'] ?? ''));
    $title = trim((string)($_POST['title'] ?? ''));
    $yt    = youtube_parse($url);
    if (!$yt) {
        $err = 'Link YouTube tidak valid. Gunakan link video (youtube.com/watch?v=…, youtu.be/…) atau Shorts (youtube.com/shorts/…).';
    } elseif (mb_strlen($title) > 200) {
        $err = 'Judul maksimal 200 karakter.';
    } else {
        $dup = $db->prepare('SELECT id FROM content_videos WHERE youtube_id = ?');
        $dup->execute([$yt['id']]);
        if ($dup->fetch()) {
            $err = 'Video ini sudah ada di daftar.';
        } else {
            $next = (int)$db->query('SELECT COALESCE(MAX(sort_order),0) + 1 FROM content_videos')->fetchColumn();
            $db->prepare('INSERT INTO content_videos (title, youtube_id, is_short, sort_order) VALUES (?, ?, ?, ?)')
               ->execute([$title !== '' ? $title : null, $yt['id'], $yt['is_short'] ? 1 : 0, $next]);
            $msg = 'Video ditambahkan.';
        }
    }
}

/* SAVE (title / order / visibility) */
if ($action === 'save') {
    $id    = (int)($_POST['id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $sort  = (int)($_POST['sort_order'] ?? 0);
    $on    = isset($_POST['is_active']) ? 1 : 0;
    if ($id > 0 && mb_strlen($title) <= 200) {
        $db->prepare('UPDATE content_videos SET title = ?, sort_order = ?, is_active = ? WHERE id = ?')
           ->execute([$title !== '' ? $title : null, $sort, $on, $id]);
        $msg = 'Perubahan disimpan.';
    } else {
        $err = 'Data tidak valid.';
    }
}

/* DELETE */
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->prepare('DELETE FROM content_videos WHERE id = ?')->execute([$id]);
        $msg = 'Video dihapus.';
    }
}

$videos = $db->query('SELECT * FROM content_videos ORDER BY sort_order ASC, id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Konten Video — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}};</script>
<style>*{box-sizing:border-box;} input{outline:none;}</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- SIDEBAR -->
<?php admin_sidebar('videos'); ?>

<!-- MAIN -->
<main class="ml-[220px] flex-1 p-8 min-h-screen min-w-0">

  <?php if ($msg): ?><div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="mb-6">
    <h1 class="font-playfair font-semibold text-dark text-[26px]">Konten Video</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5">Video / Shorts YouTube yang tampil di section “Konten Wintom” pada homepage. <?= count($videos) ?> video terdaftar.</p>
  </div>

  <div class="grid grid-cols-12 gap-6">

    <!-- LIST -->
    <div class="col-span-12 lg:col-span-8 space-y-3">
      <?php foreach ($videos as $v): ?>
      <form method="POST" class="bg-white border border-border rounded-[6px] p-4 flex gap-4 items-start">
        <?= csrf_field() ?>
        <input type="hidden" name="_action" value="save">
        <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
        <a href="https://www.youtube.com/<?= $v['is_short'] ? 'shorts/' : 'watch?v=' ?><?= htmlspecialchars($v['youtube_id']) ?>" target="_blank" rel="noopener"
           class="shrink-0 block bg-border rounded-[4px] overflow-hidden <?= $v['is_short'] ? 'w-[72px] h-[128px]' : 'w-[160px] h-[90px]' ?>">
          <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($v['youtube_id']) ?>/hqdefault.jpg" alt="" class="w-full h-full object-cover" loading="lazy">
        </a>
        <div class="flex-1 min-w-0 space-y-3">
          <div class="flex items-center gap-2">
            <span class="inline-block bg-cream-dark border border-border rounded-[2px] px-2 py-0.5 font-jakarta text-[10px] tracking-[0.5px] uppercase text-muted"><?= $v['is_short'] ? 'Shorts' : 'Video' ?></span>
            <span class="font-jakarta text-[11px] text-muted truncate"><?= htmlspecialchars($v['youtube_id']) ?></span>
          </div>
          <input type="text" name="title" value="<?= htmlspecialchars((string)$v['title']) ?>" maxlength="200" placeholder="Judul (opsional)"
                 class="w-full border border-border rounded-[3px] px-3 py-2 font-jakarta text-[13px] focus:border-brand">
          <div class="flex flex-wrap items-center gap-4">
            <label class="flex items-center gap-2 font-jakarta text-[12px] text-muted">Urutan
              <input type="number" name="sort_order" value="<?= (int)$v['sort_order'] ?>" class="w-20 border border-border rounded-[3px] px-2 py-1.5 text-[13px] text-dark focus:border-brand">
            </label>
            <label class="flex items-center gap-2 font-jakarta text-[12px] text-muted cursor-pointer">
              <input type="checkbox" name="is_active" value="1" <?= $v['is_active'] ? 'checked' : '' ?> class="accent-[#860524]"> Tampilkan di homepage
            </label>
            <button type="submit" class="bg-brand text-white font-jakarta text-[11px] tracking-[0.5px] uppercase px-3.5 py-2 rounded-[3px] hover:bg-brand-dark transition-colors">Simpan</button>
            <button type="submit" form="del-<?= (int)$v['id'] ?>" class="font-jakarta text-[11px] text-red-500 hover:underline">Hapus</button>
          </div>
        </div>
      </form>
      <form id="del-<?= (int)$v['id'] ?>" method="POST" onsubmit="return confirm('Hapus video ini dari daftar?')" class="hidden">
        <?= csrf_field() ?><input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
      </form>
      <?php endforeach; ?>
      <?php if (!$videos): ?>
      <div class="bg-white border border-border rounded-[6px] px-5 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada video. Tambahkan link YouTube di panel kanan.</div>
      <?php endif; ?>
    </div>

    <!-- ADD -->
    <div class="col-span-12 lg:col-span-4">
      <div class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4 sticky top-8">
        <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Tambah Video / Shorts</h2>
        <form method="POST" class="flex flex-col gap-3">
          <?= csrf_field() ?>
          <input type="hidden" name="_action" value="add">
          <div>
            <label class="block font-jakarta font-medium text-[12px] tracking-[0.6px] uppercase text-muted mb-1.5">Link YouTube <span class="text-red-500">*</span></label>
            <input type="url" name="url" required placeholder="https://www.youtube.com/shorts/…"
                   class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-[13px] focus:border-brand">
          </div>
          <div>
            <label class="block font-jakarta font-medium text-[12px] tracking-[0.6px] uppercase text-muted mb-1.5">Judul (opsional)</label>
            <input type="text" name="title" maxlength="200" placeholder="cth. Pemasangan roller blind di PIK"
                   class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-[13px] focus:border-brand">
          </div>
          <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[4px] hover:bg-brand-dark transition-colors">+ TAMBAH VIDEO</button>
        </form>
        <div class="bg-cream border border-border rounded-[4px] px-4 py-3">
          <p class="font-jakarta text-muted text-[11px] leading-[16px]">
            <strong class="text-dark">Info:</strong> Mendukung link video biasa, youtu.be, dan Shorts. Shorts otomatis tampil vertikal (9:16).
            Urutan terkecil tampil paling kiri. Section di homepage otomatis tersembunyi jika tidak ada video aktif.
          </p>
        </div>
      </div>
    </div>

  </div>
</main>
</body>
</html>
