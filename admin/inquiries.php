<?php
// admin/inquiries.php — Inquiry masuk dari form "Kontak Kami"
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$db  = get_db();
$msg = '';
$err = '';

$statuses = [
    'new'       => ['Baru',        'bg-amber-50 text-amber-800 border-amber-200'],
    'contacted' => ['Dihubungi',   'bg-blue-50 text-blue-800 border-blue-200'],
    'closed'    => ['Selesai',     'bg-green-50 text-green-800 border-green-200'],
];

/* ACTION: UPDATE STATUS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    if ($id > 0 && isset($statuses[$status])) {
        $db->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
        $msg = 'Status inquiry #' . $id . ' diperbarui.';
    } else {
        $err = 'Status tidak valid.';
    }
}

/* ACTION: DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        $msg = 'Inquiry #' . $id . ' dihapus.';
    }
}

$filter = (string)($_GET['status'] ?? '');
if ($filter !== '' && !isset($statuses[$filter])) {
    $filter = '';
}

$counts = array_fill_keys(array_keys($statuses), 0);
foreach ($db->query('SELECT status, COUNT(*) c FROM inquiries GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int)$row['c'];
}
$total = array_sum($counts);

$sql    = 'SELECT * FROM inquiries';
$params = [];
if ($filter !== '') {
    $sql .= ' WHERE status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 200';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Inquiry — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}};</script>
<style>*{box-sizing:border-box;}</style>
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
    <a href="inquiries.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Inquiry</a>
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Produk</a>
    <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Kategori</a>
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

  <div class="mb-6">
    <h1 class="font-playfair font-semibold text-dark text-[26px]">Inquiry Masuk</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5">Dari form “Kontak Kami” — <?= $total ?> total, <?= $counts['new'] ?> belum dihubungi</p>
  </div>

  <!-- Filter tabs -->
  <div class="flex flex-wrap gap-2 mb-5">
    <?php
    $tabs = ['' => ['Semua', $total]];
    foreach ($statuses as $k => $v) { $tabs[$k] = [$v[0], $counts[$k]]; }
    foreach ($tabs as $k => [$label, $n]):
        $active = ($filter === (string)$k);
    ?>
    <a href="inquiries.php<?= $k !== '' ? '?status=' . $k : '' ?>"
       class="px-3.5 py-1.5 rounded-full border font-jakarta text-[12px] font-medium transition-colors <?= $active ? 'bg-brand text-white border-brand' : 'bg-white text-muted border-border hover:border-brand/40' ?>">
      <?= $label ?> <span class="opacity-70"><?= $n ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="bg-white border border-border rounded-[6px] overflow-x-auto">
    <table class="w-full min-w-[900px]">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Waktu</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Pelanggan</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Jenis &amp; Ukuran</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Kebutuhan</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-44">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inquiries as $q):
            [$st_label, $st_class] = $statuses[$q['status']] ?? [$q['status'], 'bg-gray-50 text-gray-700 border-gray-200'];
            $reply = 'https://wa.me/' . $q['whatsapp'] . '?text=' . rawurlencode('Halo ' . $q['full_name'] . ', terima kasih sudah menghubungi Wintom Curtain. ');
        ?>
        <tr class="border-t border-border align-top hover:bg-cream/50 transition-colors">
          <td class="px-4 py-3.5 font-jakarta text-muted text-[12px] whitespace-nowrap">
            #<?= (int)$q['id'] ?><br><?= htmlspecialchars(date('d M Y', strtotime($q['created_at']))) ?><br><?= htmlspecialchars(date('H:i', strtotime($q['created_at']))) ?>
          </td>
          <td class="px-4 py-3.5">
            <p class="font-jakarta font-semibold text-dark text-[13px]"><?= htmlspecialchars($q['full_name']) ?></p>
            <a href="<?= htmlspecialchars($reply) ?>" target="_blank" rel="noopener"
               class="font-jakarta text-[12px] text-[#128c4a] hover:underline">+<?= htmlspecialchars($q['whatsapp']) ?> ↗</a>
          </td>
          <td class="px-4 py-3.5 font-jakarta text-[12px]">
            <p class="font-medium text-dark"><?= htmlspecialchars($q['curtain_type']) ?></p>
            <p class="text-muted mt-0.5"><?= $q['estimated_size'] ? htmlspecialchars($q['estimated_size']) : '—' ?></p>
          </td>
          <td class="px-4 py-3.5 font-jakarta text-muted text-[12px] max-w-[280px] whitespace-pre-line break-words"><?= $q['notes'] ? htmlspecialchars($q['notes']) : '—' ?></td>
          <td class="px-4 py-3.5">
            <form method="POST" class="flex items-center gap-2">
              <?= csrf_field() ?>
              <input type="hidden" name="_action" value="status">
              <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
              <select name="status" onchange="this.form.submit()"
                      class="border rounded-[3px] px-2 py-1.5 font-jakarta text-[12px] font-medium <?= $st_class ?>">
                <?php foreach ($statuses as $k => [$lbl]): ?>
                <option value="<?= $k ?>" <?= $q['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </form>
            <form method="POST" class="mt-2" onsubmit="return confirm('Hapus inquiry ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
              <button type="submit" class="font-jakarta text-[11px] text-red-500 hover:underline">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$inquiries): ?>
        <tr><td colspan="5" class="px-5 py-12 text-center font-jakarta text-muted text-[14px]">Belum ada inquiry<?= $filter !== '' ? ' dengan status ini' : '' ?>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (count($inquiries) === 200): ?>
  <p class="mt-3 font-jakarta text-muted text-[12px]">Menampilkan 200 inquiry terbaru.</p>
  <?php endif; ?>
</main>
</body>
</html>
