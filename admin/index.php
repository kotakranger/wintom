<?php
// admin/index.php — Admin Dashboard Wintom Curtain
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$db = get_db();
$total_products = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_gallery  = (int)$db->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
$categories     = $db->query("SELECT category, COUNT(*) as cnt FROM products GROUP BY category")->fetchAll();
$recent_products = $db->query("SELECT name, category, price, created_at FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll();

function fmt_price(int $price): string { return 'Rp ' . number_format($price, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — Wintom Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{playfair:['"Playfair Display"','serif'],jakarta:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{DEFAULT:'#741122',dark:'#5a0d1a'},dark:'#222220',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
  <style>*{box-sizing:border-box;}</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-[220px] shrink-0 bg-white border-r border-border flex flex-col min-h-screen">
  <div class="p-6 border-b border-border">
    <a href="../" class="flex flex-col">
      <span class="font-playfair font-bold text-brand text-[22px] leading-[28px]">wintom</span>
      <span class="font-jakarta font-semibold text-dark text-[8px] tracking-[2.5px] uppercase">CURTAIN ADMIN</span>
    </a>
  </div>
  <nav class="flex flex-col p-4 gap-1 flex-1">
    <?php
    $nav = [
      ['href'=>'index.php','label'=>'Dashboard','active'=>true],
      ['href'=>'products.php','label'=>'Produk','active'=>false],
      ['href'=>'gallery.php','label'=>'Galeri Portofolio','active'=>false],
    ];
    foreach($nav as $n):
    ?>
    <a href="<?= $n['href'] ?>"
       class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium transition-colors duration-200
              <?= $n['active'] ? 'bg-brand/10 text-brand' : 'text-muted hover:bg-cream hover:text-dark' ?>">
      <?= $n['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 border-t border-border">
    <a href="logout.php" class="flex items-center gap-2 px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors">
      Logout
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">
  <div class="mb-8">
    <h1 class="font-playfair font-semibold text-dark text-[28px] leading-[36px]">Dashboard</h1>
    <p class="font-jakarta text-muted text-[14px] mt-1">Selamat datang di panel admin Wintom Curtain.</p>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-3 gap-5 mb-8">
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Total Produk</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= $total_products ?></p>
    </div>
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Foto Galeri</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= $total_gallery ?></p>
    </div>
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Kategori</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= count($categories) ?></p>
    </div>
  </div>

  <!-- Recent Products -->
  <div class="bg-white border border-border rounded-[6px] overflow-hidden">
    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
      <h2 class="font-jakarta font-semibold text-dark text-[15px]">Produk Terbaru</h2>
      <a href="products.php" class="font-jakarta text-brand text-[12px] tracking-[1px] uppercase hover:underline">Kelola Semua →</a>
    </div>
    <table class="w-full">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-5 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Nama</th>
          <th class="px-5 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Kategori</th>
          <th class="px-5 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Harga</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($recent_products as $p): ?>
        <tr class="border-t border-border hover:bg-cream transition-colors">
          <td class="px-5 py-3.5 font-jakarta text-dark text-[13px]"><?= htmlspecialchars($p['name']) ?></td>
          <td class="px-5 py-3.5 font-jakarta text-muted text-[12px]"><?= htmlspecialchars($p['category']) ?></td>
          <td class="px-5 py-3.5 font-playfair font-semibold text-dark text-[14px] text-right"><?= fmt_price($p['price']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
