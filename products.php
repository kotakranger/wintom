<?php
// products.php — Katalog Produk & Koleksi Arsitektural Wintom
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/product-card.php';

$page_title = 'Katalog Koleksi Gorden & Blinds';
$active_nav = 'products';

// Ambil parameter filter & query
$raw_cat      = trim($_GET['category'] ?? '');
$search_query = trim($_GET['q'] ?? '');
$sort_by      = trim($_GET['sort'] ?? 'newest');

// Normalisasi kategori (mendukung slug homepage dan nama asli)
$cat_aliases = [
    'curtain'                 => 'Curtain',
    'gorden'                  => 'Curtain',
    'roller-blind'            => 'Roller Blind',
    'roller'                  => 'Roller Blind',
    'zebra'                   => 'Zebra Blind',
    'zebra-blind'             => 'Zebra Blind',
    'roman'                   => 'Roman Blind',
    'roman-blind'             => 'Roman Blind',
    'vertical'                => 'Vertical Blind',
    'vertical-blind'          => 'Vertical Blind',
    'motorized'               => 'Motorized Curtain Track',
    'motorized-curtain-track' => 'Motorized Curtain Track',
];

$normalized_key = strtolower(str_replace([' ', '&', '_'], ['-', '', '-'], $raw_cat));
$selected_cat = $cat_aliases[$normalized_key] ?? $raw_cat;

// Load categories from database (dynamic)
$pdo = get_db();
$db_cats = $pdo->query("SELECT name FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll();
$categories = ['Semua Koleksi' => ''];
foreach ($db_cats as $c) {
    $categories[$c['name']] = $c['name'];
}

$products = [];
$error_db = null;

try {
    // Query builder — only show active products on public catalog
    $sql = "SELECT p.*, c.name AS category, c.price_per_meter FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1";
    $params = [];

    if (!empty($selected_cat)) {
        $sql .= " AND c.name = ?";
        $params[] = $selected_cat;
    }

    if (!empty($search_query)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.ref_code LIKE ? OR p.features LIKE ?)";
        $wildcard = '%' . $search_query . '%';
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    // Sorting
    switch ($sort_by) {
        case 'price_asc':
            $sql .= " ORDER BY " . PRODUCT_PRICE_SQL . " ASC, p.name ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY " . PRODUCT_PRICE_SQL . " DESC, p.name ASC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.name ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY p.id DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $error_db = $e->getMessage();
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO HEADER KATALOG ===== -->
<section class="relative bg-cream-dark/60 border-b border-border py-12 md:py-20">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 md:px-12">
    <div class="max-w-3xl">
      <!-- Eyebrow -->
      <div class="wt-in flex items-center gap-3 mb-4" style="--d:60ms">
        <span class="font-jakarta font-semibold text-[11px] tracking-widest-4 uppercase text-brand">
          KATALOG EKSKLUSIF WINTOM
        </span>
      </div>

      <!-- Main Heading -->
      <h1 class="wt-in font-playfair text-3xl md:text-5xl font-bold text-dark leading-tight mb-5" style="--d:160ms">
        Kurasi Tirai & Blinds Arsitektural
      </h1>

      <!-- Description -->
      <p class="wt-in font-jakarta text-[15px] md:text-[16px] text-muted leading-relaxed mb-8" style="--d:260ms">
        Kombinasi material berstandar internasional, presisi hemming atelier, dan sistem rel silent mekanik bergaransi. Sempurna untuk hunian residensial mewah, apartemen, maupun ruang komersial.
      </p>

      <!-- Search & Sort Filter Bar -->
      <form action="<?= BASE_URL ?>/products.php" method="GET" class="wt-in flex flex-col sm:flex-row gap-3" style="--d:360ms">
        <?php if (!empty($selected_cat)): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($selected_cat) ?>">
        <?php endif; ?>

        <div class="relative flex-1">
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($search_query) ?>"
            placeholder="Cari kain linen, blackout, motorized, kode seri..."
            class="w-full bg-white border border-border rounded-[3px] px-4 py-3 pl-11 text-[16px] sm:text-[13px] font-jakarta focus:outline-none focus:border-brand transition-colors text-dark"
          />
          <div class="absolute left-4 top-1/2 -translate-y-1/2 text-muted">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>

        <div class="flex gap-2">
          <select
            name="sort"
            onchange="this.form.submit()"
            class="flex-1 sm:flex-none min-w-0 bg-white border border-border rounded-[3px] px-4 py-3 text-[16px] sm:text-[13px] font-jakarta focus:outline-none focus:border-brand text-dark cursor-pointer"
          >
            <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Koleksi Terbaru</option>
            <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Harga: Terendah</option>
            <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Harga: Tertinggi</option>
            <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Nama: A — Z</option>
          </select>

          <button
            type="submit"
            class="bg-dark hover:bg-brand text-white px-5 py-3 rounded-[3px] font-jakarta text-[12px] font-semibold tracking-wider uppercase transition-colors shrink-0"
          >
            CARI
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- ===== CATEGORY FILTER TABS ===== -->
<section class="bg-cream sticky top-[80px] z-30 border-b border-border shadow-sm">
  <div class="max-w-[1280px] mx-auto">
    <div class="flex items-center gap-2 md:gap-3 overflow-x-auto py-3.5 px-4 sm:px-6 md:px-12 no-scrollbar">
      <?php foreach ($categories as $label => $val):
        $isActive = ($selected_cat === $val);
        $catUrl = BASE_URL . '/products.php';
        $queryArr = [];
        if (!empty($val)) $queryArr['category'] = $val;
        if (!empty($search_query)) $queryArr['q'] = $search_query;
        if ($sort_by !== 'newest') $queryArr['sort'] = $sort_by;
        if (!empty($queryArr)) $catUrl .= '?' . http_build_query($queryArr);
      ?>
        <a
          href="<?= htmlspecialchars($catUrl) ?>"
          class="whitespace-nowrap px-4 py-2 rounded-[3px] text-[12px] font-semibold tracking-wider uppercase transition-all duration-200 <?= $isActive ? 'bg-brand text-white shadow-sm' : 'bg-white text-dark hover:text-brand border border-border' ?>"
        >
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== PRODUCT LISTING GRID ===== -->
<section class="py-10 md:py-24 bg-cream">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 md:px-12">

    <!-- Active Filters Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-6 md:pb-8 md:mb-8 border-b border-border">
      <div class="text-[13px] text-muted">
        Menampilkan <span class="font-bold text-dark"><?= count($products) ?></span> koleksi
        <?php if (!empty($selected_cat)): ?>
          untuk kategori <span class="text-brand font-semibold">"<?= htmlspecialchars($selected_cat) ?>"</span>
        <?php endif; ?>
        <?php if (!empty($search_query)): ?>
          dengan kata kunci <span class="text-brand font-semibold">"<?= htmlspecialchars($search_query) ?>"</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($selected_cat) || !empty($search_query) || $sort_by !== 'newest'): ?>
        <a href="<?= BASE_URL ?>/products.php" class="text-[12px] text-brand hover:underline font-semibold flex items-center gap-1.5 self-start sm:self-auto">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Reset Filter
        </a>
      <?php endif; ?>
    </div>

    <!-- Empty State -->
    <?php if (empty($products)): ?>
      <div class="bg-white border border-border rounded-[4px] p-12 text-center max-w-lg mx-auto my-12">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-cream flex items-center justify-center text-muted">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="font-playfair text-2xl font-bold text-dark mb-2">Koleksi Tidak Ditemukan</h3>
        <p class="font-jakarta text-[14px] text-muted mb-6 leading-relaxed">
          Tidak ada produk yang cocok dengan pencarian Anda. Coba gunakan kata kunci lain atau lihat seluruh koleksi kami.
        </p>
        <a href="<?= BASE_URL ?>/products.php" class="inline-block bg-brand text-white px-6 py-2.5 rounded-[3px] text-[12px] font-semibold tracking-wider uppercase hover:bg-brand-dark transition-colors">
          Lihat Semua Koleksi
        </a>
      </div>

    <!-- Product Grid -->
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <?php foreach ($products as $i => $prod) {
            render_product_card($prod, ($i % 3) * 100);
        } ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- ===== ARCHITECTURAL ON-SITE SURVEY BANNER ===== -->
<section class="bg-dark text-white py-14 md:py-20 relative overflow-hidden">
  <!-- Subtle decorative grid pattern -->
  <div class="absolute inset-0 opacity-5 pointer-events-none bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px]"></div>

  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 md:px-12 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
      <div class="lg:col-span-8" data-reveal>
        <div class="flex items-center gap-3 mb-3">
            <span class="font-jakarta font-semibold text-[11px] tracking-widest-4 uppercase text-cream-dark">
            LAYANAN ATELIER LANGSUNG KE LOKASI
          </span>
        </div>
        <h2 class="font-playfair text-2xl md:text-4xl font-bold leading-tight mb-4">
          Bingung Menentukan Material & Ukuran Jendela?
        </h2>
        <p class="font-jakarta text-[14px] md:text-[15px] text-gray-300 leading-relaxed max-w-2xl">
          Tim spesialis Wintom siap datang ke hunian atau kantor Anda dengan membawa ratusan katalog swatch kain fisik asli. Kami melakukan pengukuran presisi laser millimeter dan memberikan estimasi transparan di tempat — 100% Bebas Biaya Survei Jabodetabek.
        </p>
      </div>

      <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3.5" data-reveal style="--d:120ms">
        <a
          href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+reservasi+jadwal+survei+lokasi+dan+cek+sampel+kain+ke+alamat+saya"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center justify-center gap-2.5 bg-brand hover:bg-[#6a041c] text-white font-jakarta font-semibold text-[12px] tracking-widest-3 uppercase px-8 py-4 rounded-[3px] shadow-lg transition-all duration-200 text-center"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.448.74 0.96 1.196.659.587 1.215.769 1.388.856.173.086.274.072.375-.044.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/>
          </svg>
          RESERVASI SURVEI GRATIS
        </a>

        <a
          href="<?= BASE_URL ?>/about.php#contact"
          class="inline-flex items-center justify-center border border-white/30 hover:border-white text-white font-jakarta font-semibold text-[12px] tracking-widest-3 uppercase px-6 py-3.5 rounded-[3px] transition-colors text-center"
        >
          KUNJUNGI SHOWROOM
        </a>
      </div>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
