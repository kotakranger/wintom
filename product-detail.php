<?php
// product-detail.php — Detail Produk Wintom Curtain
// Variant System Overhaul: loads from product_variants tables, accordion UI, image linking
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/product-card.php';

$slug = $_GET['slug'] ?? '';
$db = get_db();

if (!$slug) {
    http_response_code(404);
    include_once __DIR__ . '/includes/header.php';
    ?>
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 py-16 text-center">
      <h1 class="font-playfair text-4xl font-bold text-dark mb-4">Produk Tidak Ditemukan</h1>
      <p class="font-jakarta text-muted text-lg mb-8">Maaf, produk yang Anda cari tidak ada atau telah dihapus.</p>
      <a href="<?= BASE_URL ?>/products.php" class="inline-block bg-brand text-white px-6 py-3 rounded hover:bg-[#6a041c]">Kembali ke Katalog</a>
    </div>
    <?php
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $db->prepare("SELECT p.*, c.name AS category, c.price_per_meter FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.is_active = 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) {
    http_response_code(404);
    include_once __DIR__ . '/includes/header.php';
    ?>
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 py-16 text-center">
      <h1 class="font-playfair text-4xl font-bold text-dark mb-4">Produk Tidak Ditemukan</h1>
      <p class="font-jakarta text-muted text-lg mb-8">Maaf, produk yang Anda cari tidak ada atau telah dihapus.</p>
      <a href="<?= BASE_URL ?>/products.php" class="inline-block bg-brand text-white px-6 py-3 rounded hover:bg-[#6a041c]">Kembali ke Katalog</a>
    </div>
    <?php
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch gallery images
$img_stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$img_stmt->execute([$product['id']]);
$gallery_images = $img_stmt->fetchAll();
$gallery_urls = !empty($gallery_images) ? array_column($gallery_images, 'image_url') : [$product['cover_image']];

// Fetch variants from new tables
$var_stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY is_size DESC, sort_order ASC");
$var_stmt->execute([$product['id']]);
$variants = $var_stmt->fetchAll();

foreach ($variants as &$v) {
    $opt_stmt = $db->prepare("SELECT * FROM product_variant_options WHERE variant_id = ? ORDER BY sort_order ASC");
    $opt_stmt->execute([$v['id']]);
    $v['options'] = $opt_stmt->fetchAll();
}
unset($v);

// Features
$features = $product['features'] ? explode('|', $product['features']) : [];

// Related products (same category, active only, excluding current)
$related_stmt = $db->prepare("SELECT p.*, c.name AS category, c.price_per_meter FROM products p JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.slug != ? AND p.is_active = 1 LIMIT 3");
$related_stmt->execute([$product['category_id'], $slug]);
$related = $related_stmt->fetchAll();

// WhatsApp message
$product_url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
             . BASE_URL . '/product-detail.php?slug=' . urlencode($product['slug']);
// Pesan dasar (tanpa JS). Dengan JS, pesan dilengkapi varian, ukuran & estimasi saat tombol diklik.
$wa_msg = rawurlencode("Halo Wintom, saya tertarik dengan produk *{$product['name']}* ({$product['category']}). Boleh minta info lebih lanjut?\n\n{$product_url}");

$page_title = $product['name'];
$active_nav  = 'products';

include_once __DIR__ . '/includes/header.php';

function fmt_price(int $price): string {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Build gallery image map for JS (id → url) for variant-image linking
$gallery_map = [];
foreach ($gallery_images as $gi) {
    $gallery_map[$gi['id']] = $gi['image_url'];
}
?>

<style>
/* Accordion animations */
.variant-accordion { border-bottom: 1px solid #e2ddd5; }
.variant-accordion:last-child { border-bottom: none; }
.variant-header { cursor: pointer; user-select: none; transition: background-color 0.15s; }
.variant-header:hover { background-color: rgba(245,243,240,0.5); }
.variant-body { max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding 0.2s ease; padding: 0 17px; }
.variant-body.open { max-height: 500px; padding: 0 17px 14px; }
.variant-chevron { transition: transform 0.3s ease; }
.variant-chevron.rotated { transform: rotate(180deg); }
.option-pill { transition: all 0.2s ease; }
.option-pill:hover { border-color: rgba(115,25,36,0.4); }
.option-pill.active { border-color: #860524; color: #860524; font-weight: 600; background: #fff; border-width: 2px; }
</style>

<!-- ===== BREADCRUMB ===== -->
<div class="bg-[#f5f3f0] border-b border-[#e2ddd5] px-4 sm:px-6 lg:px-12 py-[12px]">
  <div class="max-w-[1280px] mx-auto flex items-center gap-2 min-w-0 overflow-hidden whitespace-nowrap text-[11px] tracking-[0.44px] text-[#5f5e5a] font-jakarta">
    <a href="<?= BASE_URL ?>/" class="hover:text-[#860524] transition-colors">Beranda</a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <a href="<?= BASE_URL ?>/products.php" class="hover:text-[#860524] transition-colors">Produk</a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category']) ?>" class="hover:text-[#860524] transition-colors"><?= htmlspecialchars($product['category']) ?></a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <span class="font-semibold text-[#242424] truncate min-w-0"><?= htmlspecialchars($product['name']) ?></span>
  </div>
</div>

<!-- ===== MAIN PRODUCT SPLIT (50:50) ===== -->
<section class="bg-[#fbf9f6] px-4 sm:px-6 lg:px-12 py-6 md:py-10">
  <div class="max-w-[1280px] mx-auto grid grid-cols-12 lg:gap-x-12 gap-y-8 lg:gap-y-12">

    <!-- ===== LEFT: MEDIA GALLERY (6 cols) ===== -->
    <div class="col-span-12 lg:col-span-6 flex flex-col gap-4 min-w-0 lg:sticky lg:top-[100px] lg:self-start" data-reveal>

      <!-- Main Viewport -->
      <div class="bg-[#efeeeb] border border-[#e2ddd5] rounded-[2px] overflow-hidden relative" id="main-viewport">
        <img id="main-img"
             src="<?= htmlspecialchars($gallery_urls[0]) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="w-full aspect-[3/4] object-cover transition-opacity duration-300" />
      </div>

      <!-- Thumbnail Strip -->
      <div class="flex gap-3 overflow-x-auto no-scrollbar -mx-1 px-1 pb-1" id="thumbnail-strip">
        <?php foreach ($gallery_urls as $i => $url): ?>
        <button onclick="switchImg(<?= htmlspecialchars(json_encode($url), ENT_QUOTES) ?>, this)"
                data-img-url="<?= htmlspecialchars($url) ?>"
                class="thumbnail-btn shrink-0 bg-[#efeeeb] border rounded-[2px] overflow-hidden p-[3px] transition-all duration-200 <?= $i === 0 ? 'border-2 border-[#860524]' : 'border border-[#e2ddd5]' ?>"
                style="width: calc(25% - 9px); min-width: 72px">
          <img src="<?= htmlspecialchars($url) ?>" alt="Photo <?= $i+1 ?>" class="w-full aspect-square object-cover rounded-[2px]" />
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Spesifikasi material (dari admin › fitur produk) -->
      <?php if (!empty($features)): ?>
      <div class="bg-[#f5f3f0] border border-[#e2ddd5] rounded-[2px] px-4 sm:px-5 py-4 mt-1 flex flex-col gap-3">
        <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[1.65px] uppercase m-0">SPESIFIKASI MATERIAL</p>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-2 list-none p-0 m-0">
          <?php foreach ($features as $feat): ?>
          <li class="flex items-start gap-2 min-w-0">
            <svg class="size-4 text-[#860524] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z"/></svg>
            <span class="font-jakarta text-[#564242] text-[13px] leading-[20px] tracking-[0.13px]"><?= htmlspecialchars($feat) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <!-- ===== RIGHT: PRODUCT INFO + CTA (6 cols) ===== -->
    <div class="col-span-12 lg:col-span-6 flex flex-col gap-6 min-w-0" data-reveal style="--d:120ms">

      <!-- Header & Title Block -->
      <div class="flex flex-col gap-1.5">
        <!-- Category -->
        <span class="font-jakarta font-semibold text-[#860524] text-[11px] tracking-[1.1px] uppercase"><?= htmlspecialchars(strtoupper($product['category'])) ?></span>
        <!-- Product Name H1 -->
        <h1 class="font-playfair font-medium text-[#242424] text-[28px] sm:text-[36px] leading-[1.2] tracking-[-0.5px] sm:tracking-[-0.9px] break-words">
          <?= htmlspecialchars(strtoupper($product['name'])) ?>
        </h1>
      </div>

      <!-- Description Editorial -->
      <div class="font-jakarta text-[#564242] text-[14px] leading-[22.75px] tracking-[0.14px]">
        <?= nl2br(htmlspecialchars($product['description'])) ?>
      </div>

      <!-- ==========================================
           VARIANT SELECTOR BOX (Accordion)
           ========================================== -->
      <?php if (!empty($variants)): ?>
      <div class="bg-white border border-[#e2ddd5] rounded-[8px] drop-shadow-[0px_1px_1px_rgba(0,0,0,0.05)] flex flex-col overflow-hidden">

        <?php foreach ($variants as $vi => $variant):
          $is_size = !empty($variant['is_size']);
          $first_option = $variant['options'][0]['value'] ?? '';
          $has_image_link = (bool)$variant['is_image_linked'];
        ?>
        <div class="variant-accordion" data-variant-id="<?= $variant['id'] ?>" data-is-image-linked="<?= $has_image_link ? '1' : '0' ?>">
          <!-- Header (click to expand) -->
          <div class="variant-header flex items-center justify-between px-[17px] py-[13px]"
               onclick="toggleAccordion(this)">
            <div>
              <p class="font-jakarta font-medium text-[#5f5e5a] text-[12px] tracking-[0.6px] uppercase leading-[22px]">
                <?= htmlspecialchars(strtoupper($variant['label'])) ?>
              </p>
              <p class="font-playfair font-semibold text-[#242424] text-[16px] tracking-[0.16px] leading-[26px] variant-selected-label"
                 data-variant-idx="<?= $vi ?>">
                <?= htmlspecialchars($first_option ?: 'Pilih ' . $variant['label']) ?>
              </p>
            </div>
            <svg class="variant-chevron size-[10px] text-[#5f5e5a] shrink-0" fill="none" viewBox="0 0 10 6.2">
              <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>

          <!-- Body (collapsible) -->
          <div class="variant-body">
            <?php if ($is_size): ?>
            <!-- Size: Grid pills -->
            <div class="bg-[rgba(245,243,240,0.5)] border-t border-[rgba(226,221,213,0.6)] rounded-[2px] py-[9px] px-[10px] flex flex-col gap-1.5">
              <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[0.44px] uppercase leading-[16px]">PILIHAN DIMENSI:</p>
              <div class="grid grid-cols-2 gap-1.5">
                <?php foreach ($variant['options'] as $oi => $opt): ?>
                <button type="button" data-size-w="<?= (float)$opt['width_cm'] ?>" data-size-h="<?= (float)$opt['height_cm'] ?>"
                        onclick="selectOption(this, <?= htmlspecialchars(json_encode($opt['value']), ENT_QUOTES) ?>, <?= $vi ?>, <?= $opt['linked_image_id'] ?? 'null' ?>)"
                        class="option-pill <?= $oi === 0 ? 'active' : '' ?> h-[32px] border rounded-[2px] px-[11px] text-left font-jakarta text-[12px] tracking-[0.24px] border-[#e2ddd5] text-[#564242] bg-white">
                  <?= htmlspecialchars($opt['value']) ?>
                </button>
                <?php endforeach; ?>
                <!-- Custom: otomatis aktif saat ukuran di kalkulator diubah manual -->
                <button type="button" data-size-custom
                        onclick="selectOption(this, 'Custom', <?= $vi ?>, null)"
                        class="option-pill h-[32px] border rounded-[2px] px-[11px] text-left font-jakarta text-[12px] tracking-[0.24px] border-[#e2ddd5] text-[#564242] bg-white">
                  Custom
                </button>
              </div>
            </div>
            <?php else: ?>
            <!-- Non-size: Inline pills -->
            <div class="flex flex-wrap gap-2 pt-2">
              <?php foreach ($variant['options'] as $oi => $opt): ?>
              <button type="button"
                      onclick="selectOption(this, <?= htmlspecialchars(json_encode($opt['value']), ENT_QUOTES) ?>, <?= $vi ?>, <?= $opt['linked_image_id'] ?? 'null' ?>)"
                      class="option-pill <?= $oi === 0 ? 'active' : '' ?> border rounded-[2px] px-3 py-1.5 font-jakarta text-[12px] tracking-[0.24px] border-[#e2ddd5] text-[#564242] bg-white">
                <?= htmlspecialchars($opt['value']) ?>
              </button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
      <?php endif; ?>

      <!-- ZERO-CART WHATSAPP CONVERSION BOX -->
      <div class="bg-[rgba(229,226,220,0.4)] border border-[#e2ddd5] rounded-[2px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] flex flex-col gap-4 p-4 sm:p-[25px]">

        <!-- Price block -->
        <div class="border-b border-[rgba(226,221,213,0.8)] pb-4 flex flex-wrap items-end justify-between gap-3">
          <!-- Harga: per meter kategori (default, dipakai kalkulator) atau harga custom produk (tanpa kalkulator) -->
          <?php
          $pp  = product_price($product);
          $ppm = $pp['custom'] ? 0 : $pp['amount'];
          ?>
          <div>
            <p class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.55px] uppercase leading-[16px]"><?= $pp['custom'] ? 'Harga' : 'Harga per meter · ' . htmlspecialchars($product['category']) ?></p>
            <?php if ($pp['amount'] > 0): ?>
            <div class="flex flex-wrap items-baseline gap-x-2">
              <span class="font-playfair font-semibold text-[#860524] text-[30px] sm:text-[36px] leading-[1.2] tracking-[1px] sm:tracking-[1.44px]"><?= fmt_price($pp['amount']) ?></span>
              <span class="font-jakarta text-[#564242] text-[13px]"><?= htmlspecialchars($pp['unit']) ?></span>
            </div>
            <?php else: ?>
            <p class="font-playfair font-semibold text-[#242424] text-[20px] leading-[1.3] mt-1">Harga dikonfirmasi saat konsultasi</p>
            <?php endif; ?>
          </div>
          <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.275px] uppercase leading-[16px] text-right" id="conv-summary">
            <!-- Updated by JS -->
          </div>
        </div>

        <?php if ($pp['custom']): ?>
        <!-- Harga custom: tanpa kalkulator; ukuran yang dipilih tetap terbawa ke form penawaran -->
        <p class="border-b border-[rgba(226,221,213,0.8)] pb-4 font-jakarta text-[#5f5e5a] text-[12px] leading-[18px]">
          Harga final menyesuaikan ukuran &amp; pilihan varian. Pilih ukuran di atas, lalu klik <em>Minta Penawaran</em> untuk mendapatkan harga resmi dari tim kami.
        </p>
        <?php else: ?>
        <!-- Kalkulator Estimasi Harga -->
        <div id="price-calc" class="border-b border-[rgba(226,221,213,0.8)] pb-5" data-ppm="<?= $ppm ?>">
          <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 mb-3">
            <h3 class="font-jakarta font-semibold text-[#242424] text-[12px] tracking-[1.1px] uppercase">Kalkulator Estimasi Harga</h3>
          </div>

          <?php if ($ppm > 0): ?>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label for="calc-w" class="block font-jakarta font-medium text-[#5f5e5a] text-[11px] mb-1">Lebar (cm)</label>
              <input id="calc-w" type="number" inputmode="decimal" min="1" max="10000" step="any" placeholder="150"
                     class="w-full bg-white border border-[#e2ddd5] rounded-[2px] px-3 py-2.5 font-jakarta text-[16px] sm:text-[14px] text-[#242424] focus:outline-none focus:border-[#860524]">
            </div>
            <div>
              <label for="calc-h" class="block font-jakarta font-medium text-[#5f5e5a] text-[11px] mb-1">Tinggi (cm)</label>
              <input id="calc-h" type="number" inputmode="decimal" min="1" max="10000" step="any" placeholder="200"
                     class="w-full bg-white border border-[#e2ddd5] rounded-[2px] px-3 py-2.5 font-jakarta text-[16px] sm:text-[14px] text-[#242424] focus:outline-none focus:border-[#860524]">
            </div>
            <div>
              <label for="calc-q" class="block font-jakarta font-medium text-[#5f5e5a] text-[11px] mb-1">Jumlah</label>
              <input id="calc-q" type="number" inputmode="numeric" min="1" max="999" step="1" value="1"
                     class="w-full bg-white border border-[#e2ddd5] rounded-[2px] px-3 py-2.5 font-jakarta text-[16px] sm:text-[14px] text-[#242424] focus:outline-none focus:border-[#860524]">
            </div>
          </div>

          <p class="mt-2 font-jakarta text-[#5f5e5a] text-[12px]">Total luas: <strong id="calc-area" class="text-[#242424]">—</strong></p>
          <p id="calc-error" role="alert" class="hidden mt-2 font-jakarta text-[12px] text-red-600"></p>

          <div id="calc-result" class="mt-4 bg-white border border-[#e2ddd5] rounded-[2px] px-4 py-3" aria-live="polite">
            <p id="calc-placeholder" class="font-jakarta text-[#5f5e5a] text-[12px]">Masukkan lebar &amp; tinggi untuk melihat estimasi harga.</p>
            <div id="calc-output" class="hidden">
              <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                <span class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.55px] uppercase">Total Estimasi</span>
                <span id="calc-total" class="font-playfair font-semibold text-[#860524] text-[22px] sm:text-[26px] leading-[32px]">Rp 0</span>
              </div>
            </div>
          </div>
          <p class="font-jakarta text-[#5f5e5a] text-[10.5px] leading-[15px] mt-2">*Estimasi awal, belum termasuk ongkos pasang &amp; pilihan varian. Harga final dikonfirmasi tim kami setelah ukur di lokasi.</p>
          <?php else: ?>
          <p class="font-jakarta text-[#5f5e5a] text-[12px]">Harga dasar per meter untuk kategori ini belum diatur. Klik <em>Minta Penawaran</em> di bawah untuk mendapatkan estimasi.</p>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Get a Quote CTA (modal global; kalkulator mengisi ukuran otomatis) -->
        <div class="flex flex-col gap-2">
          <button type="button" data-open-quote id="quote-cta-btn"
                  data-quote-category="<?= (int)$product['category_id'] ?>"
                  data-quote-notes="<?= htmlspecialchars('Produk: ' . $product['name']) ?>"
                  class="group flex items-center justify-center gap-2 bg-[#860524] text-white font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase px-4 py-[15px] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] hover:bg-[#6a041c] transition-colors duration-200">
            MINTA PENAWARAN GRATIS
            <span class="transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
          </button>
          <a href="<?= WA_URL ?>?text=<?= $wa_msg ?>" target="_blank" rel="noopener" id="wa-cta-btn"
             data-product-name="<?= htmlspecialchars($product['name']) ?>"
             data-product-cat="<?= htmlspecialchars($product['category']) ?>"
             data-product-url="<?= htmlspecialchars($product_url) ?>"
             data-product-price="<?= $pp['custom'] && $pp['amount'] > 0 ? htmlspecialchars(fmt_price($pp['amount']) . ' ' . $pp['unit']) : '' ?>"
             class="flex items-center justify-center gap-2 bg-white border border-[#25d366] text-[#128c4a] font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase px-4 py-[14px] rounded-[2px] hover:bg-[#25d366] hover:text-white transition-colors duration-200">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/></svg>
            CHAT VIA WHATSAPP
          </a>
        </div>
      </div>

    </div><!-- /RIGHT COL -->
  </div>
</section>

<!-- ===== CARA PESAN (komponen bersama, sama dengan landing page) ===== -->
<?php $order_steps_bg = 'bg-[#fbf9f6] border-t border-[#e2ddd5]'; include __DIR__ . '/includes/order-steps.php'; ?>

<!-- ===== RELATED ARCHITECTURAL COLLECTIONS ===== -->
<?php if (!empty($related)): ?>
<section class="bg-[#f5f3f0] border-t border-[#e2ddd5] px-4 sm:px-6 lg:px-12 py-12 md:py-16">
  <div class="max-w-[1280px] mx-auto flex flex-col gap-6">
    <!-- Header row -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3" data-reveal>
      <div class="flex flex-col gap-1">
        <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[1.65px] uppercase">KOLEKSI PENDAMPING</p>
        <h2 class="font-playfair font-medium text-[#242424] text-[24px] leading-[32px] tracking-[1.2px]">Eksplorasi Kurasi Tekstil Lainnya</h2>
      </div>
      <a href="<?= BASE_URL ?>/products.php" class="flex items-center gap-1.5 font-jakarta font-semibold text-[#860524] text-[12px] tracking-[1.2px] uppercase hover:gap-2.5 transition-all duration-200">
        LIHAT SELURUH KATALOG
        <svg class="size-[10px]" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6h8M7 3l3 3-3 3"/></svg>
      </a>
    </div>

    <!-- Product cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php foreach ($related as $i => $rel) {
          render_product_card($rel, $i * 100);
      } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
/* ==========================================================
   GALLERY IMAGE MAP (for variant-image linking)
   ========================================================== */
const galleryMap = <?= json_encode($gallery_map, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

/* ==========================================================
   ACCORDION TOGGLE
   ========================================================== */
function toggleAccordion(header) {
  const accordion = header.closest('.variant-accordion');
  const body = accordion.querySelector('.variant-body');
  const chevron = accordion.querySelector('.variant-chevron');

  if (body.classList.contains('open')) {
    body.classList.remove('open');
    chevron.classList.remove('rotated');
  } else {
    body.classList.add('open');
    chevron.classList.add('rotated');
  }
}

// Auto-open first variant on load
document.addEventListener('DOMContentLoaded', () => {
  const first = document.querySelector('.variant-accordion .variant-header');
  if (first) toggleAccordion(first);
});

/* ==========================================================
   SWITCH MAIN IMAGE
   ========================================================== */
function switchImg(url, btn) {
  const img = document.getElementById('main-img');
  img.style.opacity = '0';
  setTimeout(() => { img.src = url; img.style.opacity = '1'; }, 200);
  document.querySelectorAll('.thumbnail-btn').forEach(b => {
    b.className = b.className.replace('border-2 border-[#860524]', 'border border-[#e2ddd5]');
  });
  if (btn) {
    btn.className = btn.className.replace('border border-[#e2ddd5]', 'border-2 border-[#860524]');
  }
}

/* ==========================================================
   SELECT VARIANT OPTION
   ========================================================== */
const selectedOptions = {};

function selectOption(btn, value, variantIdx, linkedImageId) {
  // Update active state within same accordion
  const accordion = btn.closest('.variant-accordion') || btn.closest('.variant-body')?.parentElement;
  if (accordion) {
    accordion.querySelectorAll('.option-pill').forEach(p => p.classList.remove('active'));
  }
  btn.classList.add('active');

  // Update label text
  const label = document.querySelector(`.variant-selected-label[data-variant-idx="${variantIdx}"]`);
  if (label) label.textContent = value;

  // Store selection
  selectedOptions[variantIdx] = value;

  // Ukuran -> kalkulator: preset mengisi lebar & tinggi; "Custom" mengarahkan ke input kalkulator
  if (window.WintomCalc && !window.WintomCalc.syncing) {
    if (btn.dataset.sizeW) window.WintomCalc.setSize(btn.dataset.sizeW, btn.dataset.sizeH);
    else if (btn.hasAttribute('data-size-custom')) window.WintomCalc.focus();
  } else if (!window.WintomCalc) {
    // Tanpa kalkulator (harga custom / harga kategori belum diatur): ukuran preset langsung ke form penawaran
    const quoteBtn = document.getElementById('quote-cta-btn');
    if (quoteBtn && btn.dataset.sizeW) {
      quoteBtn.dataset.quoteWidth = btn.dataset.sizeW;
      quoteBtn.dataset.quoteHeight = btn.dataset.sizeH;
    } else if (quoteBtn && btn.hasAttribute('data-size-custom')) {
      delete quoteBtn.dataset.quoteWidth;
      delete quoteBtn.dataset.quoteHeight;
    }
  }

  // Image linking: if linkedImageId, switch to that gallery image
  if (linkedImageId && galleryMap[linkedImageId]) {
    const targetUrl = galleryMap[linkedImageId];
    // Find matching thumbnail button
    const thumbBtn = document.querySelector(`.thumbnail-btn[data-img-url="${CSS.escape(targetUrl)}"]`);
    switchImg(targetUrl, thumbBtn);
  }

  // Update conversion summary
  updateConvSummary();
}

function updateConvSummary() {
  const summary = document.getElementById('conv-summary');
  if (!summary) return;
  const lines = Object.values(selectedOptions).map(v => v.toUpperCase());
  summary.replaceChildren();
  lines.forEach(l => {
    const p = document.createElement('p');
    p.textContent = l;
    summary.appendChild(p);
  });
}
</script>

<script>
// Kalkulator estimasi: (lebar × tinggi / 10.000) × harga per meter kategori × jumlah
(function () {
  const root = document.getElementById('price-calc');
  if (!root || !root.querySelector('#calc-w')) return;
  const ppm = parseInt(root.dataset.ppm, 10) || 0;
  const wEl = root.querySelector('#calc-w');
  const hEl = root.querySelector('#calc-h');
  const qEl = root.querySelector('#calc-q');
  const errEl = root.querySelector('#calc-error');
  const placeholder = root.querySelector('#calc-placeholder');
  const output = root.querySelector('#calc-output');
  const totalEl = root.querySelector('#calc-total');
  const areaEl = root.querySelector('#calc-area');
  const quoteBtn = document.getElementById('quote-cta-btn');
  const MAX_DIM = 10000, MAX_QTY = 999;
  const api = { result: null };   // diisi setSize/focus di bawah; result dipakai pesan WhatsApp
  const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  const num = n => n.toLocaleString('id-ID', { maximumFractionDigits: 2 });

  // Cegah karakter yang membuat angka negatif / eksponen
  [wEl, hEl, qEl].forEach(function (el) {
    el.addEventListener('keydown', function (e) {
      if (['-', '+', 'e', 'E'].includes(e.key)) e.preventDefault();
    });
  });
  qEl.addEventListener('keydown', function (e) { if (e.key === '.' || e.key === ',') e.preventDefault(); });

  function show(msg) {
    errEl.textContent = msg || '';
    errEl.classList.toggle('hidden', !msg);
  }
  function reset() {
    areaEl.textContent = '—';
    api.result = null;
    output.classList.add('hidden');
    placeholder.classList.remove('hidden');
    if (quoteBtn) { delete quoteBtn.dataset.quoteWidth; delete quoteBtn.dataset.quoteHeight; delete quoteBtn.dataset.quoteQty; }
  }

  function calc() {
    const wRaw = wEl.value.trim(), hRaw = hEl.value.trim(), qRaw = qEl.value.trim();
    const w = parseFloat(wRaw), h = parseFloat(hRaw), q = Number(qRaw);

    // Belum diisi lengkap: tunggu, tanpa pesan error
    if (wRaw === '' || hRaw === '') { show(''); reset(); return; }

    let msg = '';
    if (!(w > 0)) msg = 'Lebar harus lebih dari 0 cm.';
    else if (!(h > 0)) msg = 'Tinggi harus lebih dari 0 cm.';
    else if (w > MAX_DIM || h > MAX_DIM) msg = 'Ukuran maksimal ' + MAX_DIM.toLocaleString('id-ID') + ' cm.';
    else if (qRaw === '' || !Number.isInteger(q) || q < 1) msg = 'Jumlah minimal 1 dan harus bilangan bulat.';
    else if (q > MAX_QTY) msg = 'Jumlah maksimal ' + MAX_QTY + '.';

    if (msg) { show(msg); reset(); return; }
    show('');

    const perUnit = Math.round((w * h / 10000) * ppm);
    const total = perUnit * q;
    totalEl.textContent = rp(total);
    const area = (w * h / 10000) * q;
    areaEl.textContent = num(area) + ' m²';
    api.result = { w: w, h: h, q: q, area: area, total: total };
    placeholder.classList.add('hidden');
    output.classList.remove('hidden');

    // Bawa ukuran kalkulator ke form Get a Quote
    if (quoteBtn) {
      quoteBtn.dataset.quoteWidth = String(Math.round(w * 10) / 10);
      quoteBtn.dataset.quoteHeight = String(Math.round(h * 10) / 10);
      quoteBtn.dataset.quoteQty = String(q);
    }
  }

  ['input', 'change'].forEach(function (ev) {
    [wEl, hEl, qEl].forEach(function (el) { el.addEventListener(ev, calc); });
  });

  // Kalkulator -> pilihan ukuran: ketikan manual memilih preset yang sama persis, selain itu "Custom".
  // (Event 'input' hanya terpicu oleh ketikan pengguna, bukan oleh setSize().)
  const sizePills = Array.from(document.querySelectorAll('[data-size-w]'));
  const customPill = document.querySelector('[data-size-custom]');
  function syncSizePill() {
    if (!customPill) return;
    const w = parseFloat(wEl.value), h = parseFloat(hEl.value);
    const match = sizePills.find(function (b) { return parseFloat(b.dataset.sizeW) === w && parseFloat(b.dataset.sizeH) === h; });
    const target = match || customPill;
    if (target.classList.contains('active')) return;
    api.syncing = true;
    target.click();
    api.syncing = false;
  }
  [wEl, hEl].forEach(function (el) { el.addEventListener('input', syncSizePill); });

  Object.assign(api, {
    syncing: false,
    setSize: function (w, h) {
      wEl.value = String(parseFloat(w));
      hEl.value = String(parseFloat(h));
      calc();
    },
    focus: function () { wEl.focus({ preventScroll: false }); wEl.select(); }
  });
  window.WintomCalc = api;

  // Ukuran preset pertama (aktif secara default) langsung dihitung
  const initial = sizePills.find(function (b) { return b.classList.contains('active'); });
  if (initial) api.setSize(initial.dataset.sizeW, initial.dataset.sizeH);
  else calc();
})();
</script>

<script>
// Tanpa kalkulator: ukuran preset yang aktif saat halaman dibuka langsung terbawa ke form penawaran
(function () {
  if (window.WintomCalc) return;
  const active = document.querySelector('[data-size-w].active');
  const quoteBtn = document.getElementById('quote-cta-btn');
  if (active && quoteBtn) {
    quoteBtn.dataset.quoteWidth = active.dataset.sizeW;
    quoteBtn.dataset.quoteHeight = active.dataset.sizeH;
  }
})();
</script>

<script>
// Tombol "Chat via WhatsApp": susun pesan dari pilihan pengunjung saat diklik
(function () {
  const btn = document.getElementById('wa-cta-btn');
  if (!btn) return;
  const base = btn.getAttribute('href').split('?')[0];
  const num = n => Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });
  const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  const cap = t => t.charAt(0).toUpperCase() + t.slice(1).toLowerCase();

  btn.addEventListener('click', function () {
    const d = btn.dataset;
    const calc = window.WintomCalc && window.WintomCalc.result;
    const lines = ['Halo Wintom, saya tertarik dengan produk ini:', '', '*' + d.productName + '*', 'Kategori: ' + d.productCat];

    // Varian terpilih (ukuran diambil dari kalkulator bila ada, agar sesuai angka yang diisi)
    document.querySelectorAll('.variant-accordion').forEach(function (acc) {
      const label = acc.querySelector('.variant-header p');
      const value = acc.querySelector('.variant-selected-label');
      if (!label || !value) return;
      const isSize = !!acc.querySelector('[data-size-w], [data-size-custom]');
      if (isSize && (calc || value.textContent.trim() === 'Custom')) return;
      lines.push(cap(label.textContent.trim()) + ': ' + value.textContent.trim());
    });

    if (calc) {
      lines.push('Ukuran: ' + num(calc.w) + ' × ' + num(calc.h) + ' cm | Qty: ' + calc.q + ' set');
      lines.push('Total luas: ' + num(calc.area) + ' m²');
      lines.push('Estimasi harga: ' + rp(calc.total));
    } else if (d.productPrice) {
      lines.push('Harga: ' + d.productPrice);
    }

    lines.push('', 'Link produk: ' + d.productUrl, '', 'Boleh minta info lebih lanjut?');
    btn.setAttribute('href', base + '?text=' + encodeURIComponent(lines.join('\n')));
  });
})();
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
