<?php
// admin/index.php — Admin Dashboard Wintom Curtain
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/admin-table.php';
require_login();

$db = get_db();
$total_products = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_gallery  = (int)$db->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
$category_count = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$new_inquiries  = (int)$db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
$recent_inquiries = $db->query("SELECT id, full_name, whatsapp, curtain_type, status, created_at FROM inquiries ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
$category_rows = $db->query("
    SELECT c.id, c.name, c.price_per_meter, COUNT(p.id) AS product_count,
           SUM(p.is_active = 1) AS active_count
    FROM categories c LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id, c.name, c.price_per_meter, c.sort_order
    ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();
$recent_products = $db->query("SELECT p.name, c.name AS category, c.price_per_meter, p.price, p.price_unit, p.use_category_price, p.created_at FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

function fmt_price(int $price): string { return 'Rp ' . number_format($price, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
  <style>*{box-sizing:border-box;}</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- SIDEBAR -->
<?php admin_sidebar('dashboard'); ?>

<!-- MAIN -->
<main class="ml-[220px] flex-1 p-8 min-w-0">
  <div class="mb-8">
    <h1 class="font-playfair font-semibold text-dark text-[28px] leading-[36px]">Dashboard</h1>
    <p class="font-jakarta text-muted text-[14px] mt-1">Selamat datang di panel admin Wintom Curtain.</p>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-4 gap-5 mb-8">
    <a href="inquiries.php?status=new" class="bg-white border border-border rounded-[6px] p-6 hover:border-brand/40 transition-colors">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Inquiry Baru</p>
      <p class="font-playfair font-semibold <?= $new_inquiries > 0 ? 'text-brand' : 'text-dark' ?> text-[32px]"><?= $new_inquiries ?></p>
    </a>
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Total Produk</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= $total_products ?></p>
    </div>
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Proyek Portofolio</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= $total_gallery ?></p>
    </div>
    <div class="bg-white border border-border rounded-[6px] p-6">
      <p class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.5px] uppercase mb-2">Kategori</p>
      <p class="font-playfair font-semibold text-dark text-[32px]"><?= $category_count ?></p>
    </div>
  </div>

  <!-- Recent Inquiries -->
  <div class="bg-white border border-border rounded-[6px] overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
      <h2 class="font-jakarta font-semibold text-dark text-[15px]">Inquiry Terbaru</h2>
      <a href="inquiries.php" class="font-jakarta text-brand text-[12px] tracking-[1px] uppercase hover:underline">Lihat Semua →</a>
    </div>
    <table class="w-full">
      <tbody>
        <?php foreach($recent_inquiries as $q): [$t1, $t2] = admin_time_lines($q['created_at']); ?>
        <tr class="border-t border-border first:border-t-0 hover:bg-cream transition-colors cursor-pointer" onclick="location.href='inquiries.php'">
          <td class="px-5 py-3.5 <?= $q['status'] === 'new' ? 'shadow-[inset_3px_0_0_#f59e0b]' : '' ?>">
            <p class="font-jakarta font-semibold text-dark text-[13px]"><?= htmlspecialchars($q['full_name']) ?></p>
            <p class="font-jakarta text-muted text-[12px] tabular-nums"><?= htmlspecialchars(admin_phone($q['whatsapp'])) ?></p>
          </td>
          <td class="px-5 py-3.5"><span class="inline-block rounded-[4px] border border-border bg-cream-dark px-2 py-0.5 font-jakarta font-semibold text-[12px] text-dark"><?= htmlspecialchars($q['curtain_type']) ?></span></td>
          <td class="px-5 py-3.5"><?= admin_status_pill($q['status']) ?></td>
          <td class="px-5 py-3.5 text-right whitespace-nowrap">
            <p class="font-jakarta font-semibold text-dark text-[12px]"><?= htmlspecialchars($t1) ?></p>
            <p class="font-jakarta text-muted text-[11px]"><?= htmlspecialchars($t2) ?></p>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recent_inquiries): ?><?php admin_empty_row(4, 'Belum ada inquiry', 'Inquiry dari form “Minta Penawaran” akan muncul di sini.'); ?><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Kategori: harga per meter & jumlah produk -->
  <div class="bg-white border border-border rounded-[6px] overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
      <h2 class="font-jakarta font-semibold text-dark text-[15px]">Kategori Produk</h2>
      <a href="categories.php" class="font-jakarta text-brand text-[12px] tracking-[1px] uppercase hover:underline">Kelola Kategori →</a>
    </div>
    <table class="w-full">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-5 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Kategori</th>
          <th class="px-5 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Harga / meter</th>
          <th class="px-5 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Jumlah Produk</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($category_rows as $c): ?>
        <tr class="border-t border-border hover:bg-cream transition-colors">
          <td class="px-5 py-3.5 font-jakarta text-dark text-[13px]"><?= htmlspecialchars($c['name']) ?></td>
          <td class="px-5 py-3.5 text-right">
            <?php if ((int)$c['price_per_meter'] > 0): ?>
            <span class="font-playfair font-semibold text-dark text-[14px]"><?= fmt_price((int)$c['price_per_meter']) ?></span>
            <?php else: ?>
            <span class="font-jakarta text-amber-700 text-[12px]">Belum diatur</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3.5 font-jakarta text-dark text-[13px] text-right">
            <?= (int)$c['product_count'] ?>
            <?php if ((int)$c['product_count'] !== (int)$c['active_count']): ?>
            <span class="text-muted text-[11px]">(<?= (int)$c['active_count'] ?> aktif)</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$category_rows): ?><?php admin_empty_row(3, 'Belum ada kategori'); ?><?php endif; ?>
      </tbody>
    </table>
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
          <td class="px-5 py-3.5 font-playfair font-semibold text-dark text-[14px] text-right"><?php $pp = product_price($p); ?><?= $pp['amount'] > 0 ? fmt_price($pp['amount']) . ' <span class="font-jakarta font-normal text-muted text-[11px]">' . htmlspecialchars($pp['unit']) . ($pp['custom'] ? ' · custom' : '') . '</span>' : '<span class="font-jakarta font-normal text-amber-700 text-[12px]">Belum diatur</span>' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
