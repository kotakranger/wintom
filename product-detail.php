<?php
// product-detail.php — Detail Produk Wintom Curtain
// Variant System Overhaul: loads from product_variants tables, accordion UI, image linking
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: /products.php'); exit; }

$db = get_db();
$stmt = $db->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header('Location: /products.php'); exit; }

// Fetch gallery images
$img_stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$img_stmt->execute([$product['id']]);
$gallery_images = $img_stmt->fetchAll();
$gallery_urls = !empty($gallery_images) ? array_column($gallery_images, 'image_url') : [$product['cover_image']];

// Fetch variants from new tables
$var_stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC");
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
$related_stmt = $db->prepare("SELECT * FROM products WHERE category = ? AND slug != ? AND is_active = 1 LIMIT 3");
$related_stmt->execute([$product['category'], $slug]);
$related = $related_stmt->fetchAll();

// WhatsApp message
$wa_msg = rawurlencode("Halo Wintom, saya tertarik dengan produk *{$product['name']}* (REF: {$product['ref_code']}). Boleh minta info lebih lanjut dan estimasi biaya?");

$page_title = $product['name'];
$active_nav  = 'products';

include_once __DIR__ . '/includes/header.php';

function fmt_price(int $price): string {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
$discount_pct = 0;
if ($product['original_price'] && $product['original_price'] > $product['price']) {
    $discount_pct = round((1 - $product['price'] / $product['original_price']) * 100);
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
.option-pill.active { border-color: #731924; color: #731924; font-weight: 600; background: #fff; border-width: 2px; }
</style>

<!-- ===== BREADCRUMB ===== -->
<div class="bg-[#f5f3f0] border-b border-[#e2ddd5] px-12 py-[12px]">
  <div class="max-w-[1280px] mx-auto flex items-center gap-2 text-[11px] tracking-[0.44px] text-[#5f5e5a] font-jakarta">
    <a href="/" class="hover:text-[#731924] transition-colors">Home</a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <a href="/products.php" class="hover:text-[#731924] transition-colors">Products</a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <a href="/products.php?cat=<?= urlencode($product['category']) ?>" class="hover:text-[#731924] transition-colors"><?= htmlspecialchars($product['category']) ?></a>
    <svg class="size-[5px] shrink-0" viewBox="0 0 4 7" fill="none"><path d="M1 1l2.5 2.5L1 6" stroke="#5f5e5a" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <span class="font-semibold text-[#1e1e1e]"><?= htmlspecialchars($product['name']) ?></span>
  </div>
</div>

<!-- ===== MAIN PRODUCT SPLIT (50:50) ===== -->
<section class="bg-[#fbf9f6] px-12 py-10">
  <div class="max-w-[1280px] mx-auto grid grid-cols-12 gap-x-12 gap-y-12">

    <!-- ===== LEFT: MEDIA GALLERY (6 cols) ===== -->
    <div class="col-span-12 lg:col-span-6 flex flex-col gap-4">

      <!-- Main Viewport -->
      <div class="bg-[#efeeeb] border border-[#e2ddd5] rounded-[2px] overflow-hidden relative" id="main-viewport">
        <img id="main-img"
             src="<?= htmlspecialchars($gallery_urls[0]) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="w-full aspect-[3/4] object-cover transition-opacity duration-300" />

        <!-- OEKO Badge -->
        <div class="absolute top-4 left-4 backdrop-blur-[6px] bg-[rgba(251,249,246,0.9)] border border-[#e2ddd5] rounded-[2px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] flex items-center gap-2 px-[13px] py-[7px]">
          <div class="size-2 rounded-full bg-[#731924] shrink-0"></div>
          <span class="font-jakarta font-semibold text-[#1e1e1e] text-[11px] tracking-[1.1px] uppercase whitespace-nowrap">OEKO-TEX® CERTIFIED · 100% PREMIUM WEAVE</span>
        </div>

        <!-- Zoom indicator -->
        <div class="absolute bottom-4 right-4 bg-[rgba(30,30,30,0.8)] backdrop-blur-[2px] rounded-[2px] flex items-center gap-1.5 px-3 py-1">
          <svg class="size-[11px] text-white" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="1.5"><circle cx="5" cy="5" r="3.5"/><path d="M8 8l2.5 2.5" stroke-linecap="round"/></svg>
          <span class="font-jakarta text-white text-[11px] tracking-[0.44px]">10x Macro Weave Inspection</span>
        </div>
      </div>

      <!-- Thumbnail Strip -->
      <div class="flex gap-3" id="thumbnail-strip">
        <?php foreach ($gallery_urls as $i => $url): ?>
        <button onclick="switchImg('<?= htmlspecialchars($url) ?>', this)"
                data-img-url="<?= htmlspecialchars($url) ?>"
                class="thumbnail-btn shrink-0 bg-[#efeeeb] border rounded-[2px] overflow-hidden p-[3px] transition-all duration-200 <?= $i === 0 ? 'border-2 border-[#731924]' : 'border border-[#e2ddd5]' ?>"
                style="width: calc(25% - 9px)">
          <img src="<?= htmlspecialchars($url) ?>" alt="Photo <?= $i+1 ?>" class="w-full aspect-square object-cover rounded-[2px]" />
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Micro Atelier Guarantee Strip -->
      <div class="bg-[#f5f3f0] border border-[#e2ddd5] rounded-[2px] flex items-center justify-between px-[13px] py-[13px] mt-1">
        <div class="flex items-center gap-1.5">
          <svg class="size-3 text-[#5f5e5a] shrink-0" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M2 7l3.5 3.5L12 3.5"/></svg>
          <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] leading-[16px]">
            <p>Jahitan Khusus Double</p><p>Blindstitch</p>
          </div>
        </div>
        <div class="w-px h-3 bg-[#e2ddd5] shrink-0"></div>
        <div class="flex items-center gap-1.5">
          <svg class="size-3 text-[#5f5e5a] shrink-0" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M7 1v6M7 7l3.5 3.5"/><circle cx="7" cy="10" r="3"/></svg>
          <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] leading-[16px]">
            <p>Akurasi Laser</p><p>1mm</p>
          </div>
        </div>
        <div class="w-px h-3 bg-[#e2ddd5] shrink-0"></div>
        <div class="flex items-center gap-1.5">
          <svg class="size-3 text-[#5f5e5a] shrink-0" fill="none" viewBox="0 0 14 14" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M2 2h10v8H2z"/><path stroke-linecap="round" d="M5 10v2M9 10v2M3 12h8"/></svg>
          <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] leading-[16px]">
            <p>Pengantaran &amp; Pasang Tim</p><p>Internal</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== RIGHT: PRODUCT INFO + CTA (6 cols) ===== -->
    <div class="col-span-12 lg:col-span-6 flex flex-col gap-6">

      <!-- Header & Title Block -->
      <div class="flex flex-col gap-1.5">
        <!-- Category + REF row -->
        <div class="flex items-center gap-2">
          <span class="font-jakarta font-semibold text-[#731924] text-[11px] tracking-[1.1px] uppercase"><?= htmlspecialchars(strtoupper($product['category'])) ?></span>
          <div class="size-[6px] rounded-full bg-[#e2ddd5] shrink-0"></div>
          <span class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] uppercase">REF. #<?= htmlspecialchars($product['ref_code']) ?></span>
        </div>
        <!-- Product Name H1 -->
        <h1 class="font-playfair font-medium text-[#1e1e1e] text-[36px] leading-[44px] tracking-[-0.9px]">
          <?= htmlspecialchars(strtoupper($product['name'])) ?>
        </h1>
        <!-- Estimasi Harga -->
        <div class="flex items-baseline gap-2 pt-1">
          <span class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] uppercase">ESTIMASI BAHAN MULAI:</span>
          <span class="font-playfair font-semibold text-[#540011] text-[18px] tracking-[1.08px]"><?= fmt_price($product['price']) ?></span>
          <span class="font-jakarta text-[#564242] text-[12px] tracking-[0.24px]"><?= htmlspecialchars($product['price_unit']) ?> (termasuk ongkos jahit atelier)</span>
        </div>
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
          $is_size = ($variant['label'] === 'Ukuran' || $variant['label'] === 'Panjang Track');
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
              <p class="font-playfair font-semibold text-[#1e1e1e] text-[16px] tracking-[0.16px] leading-[26px] variant-selected-label"
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
                <button type="button"
                        onclick="selectOption(this, '<?= htmlspecialchars($opt['value']) ?>', <?= $vi ?>, <?= $opt['linked_image_id'] ?? 'null' ?>)"
                        class="option-pill <?= $oi === 0 ? 'active' : '' ?> h-[32px] border rounded-[2px] px-[11px] text-left font-jakarta text-[12px] tracking-[0.24px] border-[#e2ddd5] text-[#564242] bg-white">
                  <?= htmlspecialchars($opt['value']) ?>
                </button>
                <?php endforeach; ?>
              </div>
            </div>
            <?php else: ?>
            <!-- Non-size: Inline pills -->
            <div class="flex flex-wrap gap-2 pt-2">
              <?php foreach ($variant['options'] as $oi => $opt): ?>
              <button type="button"
                      onclick="selectOption(this, '<?= htmlspecialchars($opt['value']) ?>', <?= $vi ?>, <?= $opt['linked_image_id'] ?? 'null' ?>)"
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
      <div class="bg-[rgba(229,226,220,0.4)] border border-[#e2ddd5] rounded-[2px] shadow-[0px_1px_2px_0px_rgba(0,0,0,0.05)] flex flex-col gap-4 p-[25px]">

        <!-- Badges row -->
        <div class="flex items-center justify-between">
          <div class="bg-[#731924] text-white font-jakarta font-semibold text-[11px] tracking-[0.55px] uppercase px-2 py-[2px] rounded-[2px]">PENAWARAN TERBATAS</div>
          <?php if ($discount_pct > 0): ?>
          <div class="bg-white border border-[#e2ddd5] flex items-center gap-1.5 px-[11px] py-[3px] rounded-[2px]">
            <svg class="size-[11px] text-[#731924]" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M1 6l5-5 5 5M6 1v10"/></svg>
            <span class="font-jakarta font-semibold text-[#731924] text-[11px] tracking-[0.44px]">Hemat <?= $discount_pct ?>% (<?= fmt_price($product['original_price'] - $product['price']) ?>/m)</span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Price block -->
        <div class="border-b border-[rgba(226,221,213,0.8)] pb-4 flex items-end justify-between">
          <div>
            <div class="flex items-baseline gap-3">
              <span class="font-playfair font-semibold text-[#540011] text-[36px] leading-[44px] tracking-[1.44px]"><?= fmt_price($product['price']) ?></span>
              <?php if ($product['original_price']): ?>
              <span class="font-jakarta text-[#5f5e5a] text-[14px] leading-[22px] line-through"><?= fmt_price($product['original_price']) ?></span>
              <?php endif; ?>
            </div>
            <p class="font-jakarta text-[#564242] text-[12px] tracking-[0.24px] leading-[18px]"><?= htmlspecialchars($product['price_unit']) ?> kain jadi</p>
          </div>
          <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.275px] uppercase leading-[16px] text-right" id="conv-summary">
            <!-- Updated by JS -->
          </div>
        </div>

        <!-- WhatsApp CTA Button -->
        <a href="<?= WA_URL ?>?text=<?= $wa_msg ?>"
           target="_blank" rel="noopener" id="wa-cta-btn"
           class="flex items-center justify-center gap-2 bg-[#731924] text-white font-jakarta font-semibold text-[12px] tracking-[0.6px] uppercase px-4 py-[14px] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] hover:bg-[#5a0d1a] transition-colors duration-200">
          KONSULTASI &amp; CEK ESTIMASI WHATSAPP
          <svg class="size-3" viewBox="0 0 12 12" fill="white"><path d="M6 0a6 6 0 0 0-5.27 8.82L0 12l3.28-.7A6 6 0 1 0 6 0Z"/><path d="M4.3 3.8c.1-.25.38-.5.75-.5.3 0 .5.15.63.3l.63 1c.12.25.02.5-.13.65l-.25.25c.25.45.68.88 1.13 1.13l.25-.25c.15-.15.4-.25.65-.13l1 .63c.15.13.3.33.3.63 0 .38-.25.65-.5.75C8.25 8.5 6.75 8.75 5.25 7.25S4 4.25 4.1 3.8Z" fill="rgba(255,255,255,0.9)"/></svg>
        </a>

        <!-- Trust Guarantees -->
        <div class="border-t border-[#e2ddd5] pt-[9px] flex items-start justify-between gap-3">
          <?php
          $trusts = [
            ['icon'=>'M9 12l-4 4-4-4M5 8v8M3 3h10a2 2 0 0 1 2 2v4H1V5a2 2 0 0 1 2-2Z', 'line1'=>'Koper 150+ sampel kain ke', 'line2'=>'rumah'],
            ['icon'=>'M2 2h10v2H2zM3 4v8l2 1 2-1 2 1 2-1V4', 'line1'=>'Ukur laser milimeter', 'line2'=>'digital'],
            ['icon'=>'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z', 'line1'=>'Garansi pengerjaan &amp; rel 3', 'line2'=>'tahun'],
          ];
          foreach($trusts as $t):
          ?>
          <div class="flex items-start gap-1">
            <svg class="size-[14px] text-[#731924] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $t['icon'] ?>"/></svg>
            <div class="font-jakarta text-[#5f5e5a] text-[11px] tracking-[0.44px] leading-[16px]">
              <p><?= $t['line1'] ?></p>
              <p><?= $t['line2'] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Features list -->
      <?php if (!empty($features)): ?>
      <div class="flex flex-col gap-2">
        <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[1.65px] uppercase">SPESIFIKASI MATERIAL</p>
        <ul class="flex flex-col gap-2">
          <?php foreach ($features as $feat): ?>
          <li class="flex items-start gap-2">
            <svg class="size-4 text-[#731924] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z"/></svg>
            <span class="font-jakarta text-[#564242] text-[13px] leading-[20px] tracking-[0.13px]"><?= htmlspecialchars($feat) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div><!-- /RIGHT COL -->
  </div>
</section>

<!-- ===== STEP-BY-STEP BESPOKE PROCESS RIBBON ===== -->
<section class="bg-[#fbf9f6] border-t border-[#e2ddd5] px-12 py-[41px]">
  <div class="max-w-[1280px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="text-center flex flex-col gap-[5.5px]">
      <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[1.65px] uppercase">PROTOKOL PEMESANAN TANPA REPOT</p>
      <h2 class="font-playfair font-medium text-[#1e1e1e] text-[24px] leading-[32px] tracking-[1.2px]">4 Langkah Mewujudkan Tirai Sempurna</h2>
    </div>
    <!-- Steps grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $steps = [
        ['num'=>'01','title'=>'Konsultasi Awal','desc'=>'Kirimkan estimasi ukuran jendela atau denah arsitek via WhatsApp untuk gambaran perkiraan biaya bahan & rel.'],
        ['num'=>'02','title'=>'Survei & Swatch','desc'=>'Konsultan kami datang membawa koper sampel kain langsung ke hunian Anda dan melakukan ukur laser milimeter.'],
        ['num'=>'03','title'=>'Penjahitan Atelier','desc'=>'Kain diproses dengan standard jahitan double blindstitch dan proses steam setting bentuk lipatan selama 7–10 hari kerja.'],
        ['num'=>'04','title'=>'Pemasangan Rapi','desc'=>'Teknisi resmi Wintom memasang rel presisi tanpa debu kotor, melakukan final steaming, dan pelatihan remote smart motor.'],
      ];
      foreach($steps as $step):
      ?>
      <div class="bg-[#f5f3f0] border border-[#e2ddd5] rounded-[2px] p-[17px] flex flex-col gap-2">
        <p class="font-playfair font-medium text-[#e2ddd5] text-[36px] leading-[44px] tracking-[1.44px]"><?= $step['num'] ?></p>
        <h3 class="font-playfair font-semibold text-[#1e1e1e] text-[18px] leading-[26px] tracking-[1.08px] -mt-2"><?= $step['title'] ?></h3>
        <p class="font-jakarta text-[#564242] text-[12px] leading-[18px] tracking-[0.24px]"><?= htmlspecialchars($step['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== RELATED ARCHITECTURAL COLLECTIONS ===== -->
<?php if (!empty($related)): ?>
<section class="bg-[#f5f3f0] border-t border-[#e2ddd5] px-12 py-[41px]">
  <div class="max-w-[1280px] mx-auto flex flex-col gap-6">
    <!-- Header row -->
    <div class="flex items-end justify-between">
      <div class="flex flex-col gap-1">
        <p class="font-jakarta font-semibold text-[#5f5e5a] text-[11px] tracking-[1.65px] uppercase">KOLEKSI PENDAMPING</p>
        <h2 class="font-playfair font-medium text-[#1e1e1e] text-[24px] leading-[32px] tracking-[1.2px]">Eksplorasi Kurasi Tekstil Lainnya</h2>
      </div>
      <a href="/products.php" class="flex items-center gap-1.5 font-jakarta font-semibold text-[#731924] text-[12px] tracking-[1.2px] uppercase hover:gap-2.5 transition-all duration-200">
        LIHAT SELURUH KATALOG
        <svg class="size-[10px]" fill="none" viewBox="0 0 12 12" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 6h8M7 3l3 3-3 3"/></svg>
      </a>
    </div>

    <!-- Product cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach($related as $rel):
        $rel_discount = 0;
        if ($rel['original_price'] && $rel['original_price'] > $rel['price']) {
          $rel_discount = round((1 - $rel['price'] / $rel['original_price']) * 100);
        }
        $rel_wa = rawurlencode("Halo Wintom, saya tertarik dengan produk *{$rel['name']}* (REF: {$rel['ref_code']}). Boleh minta info lebih lanjut?");
        // Count variants for this related product
        $rel_var_stmt = $db->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
        $rel_var_stmt->execute([$rel['id']]);
        $rel_var_count = (int)$rel_var_stmt->fetchColumn();
        $rel_opt_stmt = $db->prepare("SELECT COUNT(*) FROM product_variant_options pvo JOIN product_variants pv ON pvo.variant_id = pv.id WHERE pv.product_id = ?");
        $rel_opt_stmt->execute([$rel['id']]);
        $rel_opt_count = (int)$rel_opt_stmt->fetchColumn();
      ?>
      <div class="bg-white border border-[#e2ddd5] rounded-[4px] overflow-hidden flex flex-col group">
        <!-- Image -->
        <div class="bg-[#efeeeb] relative overflow-hidden">
          <img src="<?= htmlspecialchars($rel['cover_image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>"
               class="w-full aspect-[4/3] object-cover transition-transform duration-700 group-hover:scale-105" />
          <div class="absolute top-3 left-3 backdrop-blur-[2px] bg-[rgba(255,255,255,0.9)] border border-[rgba(226,221,213,0.6)] rounded-[2px] px-[10px] py-[2px]">
            <span class="font-jakarta font-semibold text-[#1e1e1e] text-[11px] tracking-[0.55px] uppercase"><?= htmlspecialchars($rel['category']) ?></span>
          </div>
        </div>
        <!-- Info -->
        <div class="p-4 flex flex-col gap-1.5">
          <p class="font-jakarta font-medium text-[#5f5e5a] text-[11px] tracking-[0.275px] uppercase leading-[22px]">
            <?= $rel_var_count ?> VARIAN | <?= $rel_opt_count ?> OPSI
          </p>
          <h3 class="font-playfair font-semibold text-[#1e1e1e] text-[16px] leading-[22px] tracking-[0.14px]"><?= htmlspecialchars($rel['name']) ?></h3>
        </div>
        <!-- Pricing + Buttons -->
        <div class="px-4 pb-4 border-t border-[rgba(226,221,213,0.6)] pt-[9px] flex flex-col gap-3 mt-auto">
          <div>
            <div class="flex items-center gap-2">
              <?php if ($rel['original_price']): ?>
              <span class="font-jakarta text-[#5f5e5a] text-[12px] line-through"><?= fmt_price($rel['original_price']) ?></span>
              <?php if ($rel_discount > 0): ?>
              <span class="bg-[rgba(115,25,36,0.1)] text-[#731924] font-jakarta font-semibold text-[11px] px-1.5 py-0.5 rounded-[2px]"><?= $rel_discount ?>%</span>
              <?php endif; ?>
              <?php endif; ?>
            </div>
            <p class="font-playfair font-semibold text-[#540011] text-[18px] leading-[22px] tracking-[0.14px]"><?= fmt_price($rel['price']) ?></p>
          </div>
          <div class="flex gap-2 pt-1">
            <a href="/product-detail.php?slug=<?= urlencode($rel['slug']) ?>"
               class="flex-1 bg-white border border-[#e2ddd5] flex items-center justify-center py-[9px] rounded-[2px] font-jakarta text-[#1e1e1e] text-[11px] tracking-[0.55px] uppercase hover:border-[#731924]/40 transition-colors">
              LIHAT DETAIL
            </a>
            <a href="<?= WA_URL ?>?text=<?= $rel_wa ?>" target="_blank" rel="noopener"
               class="flex-1 bg-[#731924] flex items-center justify-center gap-1.5 py-[9px] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] hover:bg-[#5a0d1a] transition-colors">
              <svg class="size-3" viewBox="0 0 12 12" fill="white"><path d="M6 0a6 6 0 0 0-5.27 8.82L0 12l3.28-.7A6 6 0 1 0 6 0Z"/></svg>
              <span class="font-jakarta text-white text-[11px] tracking-[0.55px] uppercase">WHATSAPP</span>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
/* ==========================================================
   GALLERY IMAGE MAP (for variant-image linking)
   ========================================================== */
const galleryMap = <?= json_encode($gallery_map) ?>;

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
    b.className = b.className.replace('border-2 border-[#731924]', 'border border-[#e2ddd5]');
  });
  if (btn) {
    btn.className = btn.className.replace('border border-[#e2ddd5]', 'border-2 border-[#731924]');
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
  summary.innerHTML = lines.map(l => `<p>${l}</p>`).join('');
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
