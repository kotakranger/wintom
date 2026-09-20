<?php
// products.php — Katalog Produk & Koleksi Arsitektural Wintom
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Katalog Koleksi Gorden & Blinds';
$active_nav = 'products';

// Ambil parameter filter & query
$raw_cat      = trim($_GET['category'] ?? '');
$search_query = trim($_GET['q'] ?? '');
$sort_by      = trim($_GET['sort'] ?? 'newest');

// Normalisasi kategori (mendukung slug homepage dan nama asli)
$cat_aliases = [
    'curtain'                => 'Curtain',
    'vitrase'                => 'Vitrase & Sheer',
    'sheer'                  => 'Vitrase & Sheer',
    'vitrase-sheer'          => 'Vitrase & Sheer',
    'roller-blind'           => 'Roller Blind',
    'roller'                 => 'Roller Blind',
    'motorized'              => 'Motorized Smart System',
    'motorized-smart-system' => 'Motorized Smart System',
    'wood'                   => 'Wood & Bamboo Blind',
    'wood-blind'             => 'Wood & Bamboo Blind',
    'wood-bamboo-blind'      => 'Wood & Bamboo Blind'
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
    $sql = "SELECT * FROM products WHERE is_active = 1";
    $params = [];

    if (!empty($selected_cat)) {
        $sql .= " AND category = ?";
        $params[] = $selected_cat;
    }

    if (!empty($search_query)) {
        $sql .= " AND (name LIKE ? OR description LIKE ? OR ref_code LIKE ? OR features LIKE ?)";
        $wildcard = '%' . $search_query . '%';
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }

    // Sorting
    switch ($sort_by) {
        case 'price_asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY price DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY name ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY id DESC";
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
<section class="relative bg-cream-dark/60 border-b border-border py-16 md:py-20">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="max-w-3xl">
      <!-- Eyebrow -->
      <div class="flex items-center gap-3 mb-4">
        <span class="w-6 h-[1.5px] bg-brand"></span>
        <span class="font-jakarta font-semibold text-[11px] tracking-widest-4 uppercase text-brand">
          KATALOG EKSKLUSIF WINTOM
        </span>
      </div>

      <!-- Main Heading -->
      <h1 class="font-playfair text-3xl md:text-5xl font-bold text-dark leading-tight mb-5">
        Kurasi Tirai & Blinds Arsitektural
      </h1>

      <!-- Description -->
      <p class="font-jakarta text-[15px] md:text-[16px] text-muted leading-relaxed mb-8">
        Kombinasi material berstandar internasional, presisi hemming atelier, dan sistem rel silent mekanik bergaransi. Sempurna untuk hunian residensial mewah, apartemen, maupun ruang komersial.
      </p>

      <!-- Search & Sort Filter Bar -->
      <form action="<?= BASE_URL ?>/products.php" method="GET" class="flex flex-col sm:flex-row gap-3">
        <?php if (!empty($selected_cat)): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($selected_cat) ?>">
        <?php endif; ?>

        <div class="relative flex-1">
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($search_query) ?>"
            placeholder="Cari kain linen, blackout, motorized, kode seri..."
            class="w-full bg-white border border-border rounded-[3px] px-4 py-3 pl-11 text-[13px] font-jakarta focus:outline-none focus:border-brand transition-colors text-dark"
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
            class="bg-white border border-border rounded-[3px] px-4 py-3 text-[13px] font-jakarta focus:outline-none focus:border-brand text-dark cursor-pointer"
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
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="flex items-center gap-2 md:gap-3 overflow-x-auto py-3.5 no-scrollbar">
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
          href="<?= $catUrl ?>"
          class="whitespace-nowrap px-4 py-2 rounded-[3px] text-[12px] font-semibold tracking-wider uppercase transition-all duration-200 <?= $isActive ? 'bg-brand text-white shadow-sm' : 'bg-white text-dark hover:text-brand border border-border' ?>"
        >
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== PRODUCT LISTING GRID ===== -->
<section class="py-16 md:py-24 bg-cream">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">

    <!-- Active Filters Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 mb-8 border-b border-border">
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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($products as $prod):
          $detail_url = BASE_URL . '/product-detail.php?slug=' . urlencode($prod['slug']);
          $wa_msg = urlencode("Halo Wintom Curtain, saya tertarik berkonsultasi mengenai produk: {$prod['name']} (REF: {$prod['ref_code']}). Mohon info estimasi harga dan jadwal survei gratis.");
          $wa_product_url = WA_URL . "?text=" . $wa_msg;

          // Hitung persen diskon jika ada
          $has_discount = (!empty($prod['original_price']) && $prod['original_price'] > $prod['price']);
          $discount_pct = $has_discount ? round((($prod['original_price'] - $prod['price']) / $prod['original_price']) * 100) : 0;

          // Parse features (max 2 untuk kartu produk)
          $feat_list = [];
          if (!empty($prod['features'])) {
              $feat_list = array_slice(explode('|', $prod['features']), 0, 2);
          }
        ?>
          <div class="group bg-white border border-border rounded-[4px] overflow-hidden flex flex-col hover:border-brand/40 hover:shadow-xl transition-all duration-300">
            
            <!-- Image Card Container (Aspect 3/4) -->
            <a href="<?= $detail_url ?>" class="relative aspect-[3/4] overflow-hidden bg-cream block">
              <img
                src="<?= htmlspecialchars($prod['cover_image']) ?>"
                alt="<?= htmlspecialchars($prod['name']) ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                loading="lazy"
              />

              <!-- Gradient overlay subtle on hover -->
              <div class="absolute inset-0 bg-gradient-to-t from-dark/60 via-transparent to-black/10 opacity-60 group-hover:opacity-80 transition-opacity"></div>

              <!-- Top Left: Category Badge -->
              <div class="absolute top-3.5 left-3.5 flex flex-col gap-1.5 items-start">
                <span class="bg-white/95 backdrop-blur-[4px] text-dark font-jakarta font-semibold text-[10px] tracking-[1.2px] uppercase px-2.5 py-1 rounded-[2px] shadow-sm">
                  <?= htmlspecialchars($prod['category']) ?>
                </span>
                <?php if (!empty($prod['badge'])): ?>
                  <span class="bg-brand text-white font-jakarta font-semibold text-[9px] tracking-[1.2px] uppercase px-2 py-0.5 rounded-[2px] shadow-sm">
                    <?= htmlspecialchars($prod['badge']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Top Right: Discount Pill -->
              <?php if ($has_discount): ?>
                <div class="absolute top-3.5 right-3.5">
                  <span class="bg-dark/90 text-white font-jakarta font-bold text-[10px] tracking-wider px-2 py-1 rounded-[2px] shadow-sm">
                    HEMAT <?= $discount_pct ?>%
                  </span>
                </div>
              <?php endif; ?>

              <!-- Bottom Over Image: Ref Code -->
              <div class="absolute bottom-3.5 left-3.5 right-3.5 flex items-center justify-between text-white/90 text-[11px] font-mono">
                <span class="bg-dark/70 backdrop-blur-sm px-2 py-0.5 rounded-[2px]">
                  REF: <?= htmlspecialchars($prod['ref_code'] ?? 'WNT-PROD') ?>
                </span>
                <span class="flex items-center gap-1 text-[10px] uppercase font-jakarta tracking-wider font-semibold opacity-0 group-hover:opacity-100 transition-opacity text-white">
                  Detail Produk
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </span>
              </div>
            </a>

            <!-- Card Body Content -->
            <div class="p-6 flex-1 flex flex-col justify-between">
              <div>
                <!-- Title -->
                <h3 class="font-playfair text-[19px] md:text-[20px] font-bold text-dark leading-snug mb-2 group-hover:text-brand transition-colors">
                  <a href="<?= $detail_url ?>">
                    <?= htmlspecialchars($prod['name']) ?>
                  </a>
                </h3>

                <!-- Short Description -->
                <p class="font-jakarta text-[13px] text-muted leading-relaxed line-clamp-2 mb-4">
                  <?= htmlspecialchars($prod['description']) ?>
                </p>

                <!-- Features Bullets -->
                <?php if (!empty($feat_list)): ?>
                  <div class="space-y-1.5 mb-5 pb-5 border-b border-border/70">
                    <?php foreach ($feat_list as $f): ?>
                      <div class="flex items-center gap-2 text-[11px] text-dark/80 font-jakarta">
                        <svg class="w-3.5 h-3.5 text-brand shrink-0" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="truncate"><?= htmlspecialchars(trim($f)) ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Price & CTA Action Buttons -->
              <div>
                <div class="mb-4">
                  <div class="text-[11px] font-jakarta tracking-wider uppercase text-muted mb-0.5">Estimasi Material Mulai</div>
                  <div class="flex items-baseline gap-2">
                    <span class="font-playfair font-bold text-2xl text-brand">
                      Rp <?= number_format($prod['price'], 0, ',', '.') ?>
                    </span>
                    <span class="text-[11px] font-jakarta text-muted">
                      <?= htmlspecialchars($prod['price_unit'] ?? '/ meter') ?>
                    </span>
                    <?php if ($has_discount): ?>
                      <span class="text-[12px] font-jakarta text-muted line-through ml-auto">
                        Rp <?= number_format($prod['original_price'], 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                  <a
                    href="<?= $detail_url ?>"
                    class="w-full text-center py-2.5 border border-dark/30 hover:border-brand hover:text-brand text-dark font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase rounded-[3px] transition-colors"
                  >
                    DETAIL
                  </a>
                  <a
                    href="<?= $wa_product_url ?>"
                    target="_blank"
                    rel="noopener"
                    class="w-full text-center py-2.5 bg-brand hover:bg-[#5a0d1a] text-white font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase rounded-[3px] transition-colors flex items-center justify-center gap-1.5"
                  >
                    <span>KONSULTASI</span>
                  </a>
                </div>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- ===== ARCHITECTURAL ON-SITE SURVEY BANNER ===== -->
<section class="bg-dark text-white py-16 md:py-20 relative overflow-hidden">
  <!-- Subtle decorative grid pattern -->
  <div class="absolute inset-0 opacity-5 pointer-events-none bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px]"></div>

  <div class="max-w-[1280px] mx-auto px-6 md:px-12 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
      <div class="lg:col-span-8">
        <div class="flex items-center gap-3 mb-3">
          <span class="w-6 h-[1.5px] bg-brand"></span>
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

      <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3.5">
        <a
          href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+reservasi+jadwal+survei+lokasi+dan+cek+sampel+kain+ke+alamat+saya"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center justify-center gap-2.5 bg-brand hover:bg-[#5a0d1a] text-white font-jakarta font-semibold text-[12px] tracking-widest-3 uppercase px-8 py-4 rounded-[3px] shadow-lg transition-all duration-200 text-center"
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
