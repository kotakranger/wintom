<?php
// product-detail.php — Halaman Detail Produk & Spesifikasi Material Wintom
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$slug = trim($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);

$product = null;
$gallery_images = [];
$related_products = [];

try {
    $pdo = get_db();

    if (!empty($slug)) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
    } elseif ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    }

    if ($product) {
        // Ambil galeri foto tambahan
        $stmt_img = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt_img->execute([$product['id']]);
        $gallery_images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

        // Jika galeri kosong, masukkan cover_image sebagai item pertama
        if (empty($gallery_images)) {
            $gallery_images = [$product['cover_image']];
        } else {
            // Pastikan cover image ada di urutan pertama jika belum
            if (!in_array($product['cover_image'], $gallery_images)) {
                array_unshift($gallery_images, $product['cover_image']);
            }
        }

        // Ambil 3 produk terkait dari kategori sama
        $stmt_rel = $pdo->prepare("SELECT * FROM products WHERE id != ? ORDER BY (category = ?) DESC, id DESC LIMIT 3");
        $stmt_rel->execute([$product['id'], $product['category']]);
        $related_products = $stmt_rel->fetchAll();
    }
} catch (Exception $e) {
    // Fail-safe handling
}

// Jika produk tidak ditemukan
if (!$product) {
    $page_title = 'Produk Tidak Ditemukan';
    include_once __DIR__ . '/includes/header.php';
    ?>
    <section class="py-24 bg-cream min-h-[60vh] flex items-center justify-center">
      <div class="max-w-md mx-auto px-6 text-center">
        <h1 class="font-playfair text-3xl font-bold text-dark mb-3">Produk Tidak Ditemukan</h1>
        <p class="font-jakarta text-muted text-[14px] mb-8 leading-relaxed">
          Koleksi yang Anda cari mungkin telah diperbarui kodenya atau belum tersedia dalam katalog saat ini.
        </p>
        <a href="<?= BASE_URL ?>/products.php" class="inline-block bg-brand hover:bg-[#5a0d1a] text-white px-8 py-3 rounded-[3px] font-jakarta text-[12px] font-semibold tracking-wider uppercase transition-colors">
          Kembali ke Katalog
        </a>
      </div>
    </section>
    <?php
    include_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = $product['name'];
$active_nav = 'products';

// Parse options (warna, jahitan, sistem motor)
$options = [];
if (!empty($product['options_json'])) {
    $options = json_decode($product['options_json'], true) ?: [];
}

// Parse features
$feature_items = [];
if (!empty($product['features'])) {
    $feature_items = explode('|', $product['features']);
}

// WhatsApp URL Generator
$wa_custom_msg = urlencode("Halo Wintom Curtain, saya tertarik memesan/berkonsultasi mengenai koleksi {$product['name']} (Kode: {$product['ref_code']}). Boleh dibantu info estimasi dan penjadwalan survei lokasi?");
$product_wa_link = WA_URL . "?text=" . $wa_custom_msg;

// Diskon
$has_discount = (!empty($product['original_price']) && $product['original_price'] > $product['price']);
$discount_pct = $has_discount ? round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) : 0;

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== BREADCRUMB ===== -->
<div class="bg-cream-dark/40 border-b border-border py-4">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12 flex items-center gap-2 text-[12px] font-jakarta text-muted">
    <a href="<?= BASE_URL ?>/" class="hover:text-brand transition-colors">Home</a>
    <span>/</span>
    <a href="<?= BASE_URL ?>/products.php" class="hover:text-brand transition-colors">Katalog Koleksi</a>
    <span>/</span>
    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category']) ?>" class="hover:text-brand transition-colors">
      <?= htmlspecialchars($product['category']) ?>
    </a>
    <span>/</span>
    <span class="text-dark font-medium truncate max-w-[200px] md:max-w-none"><?= htmlspecialchars($product['name']) ?></span>
  </div>
</div>

<!-- ===== PRODUCT DETAIL HERO SECTION ===== -->
<section class="py-12 md:py-16 bg-cream">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

      <!-- LEFT: PHOTO GALLERY (INTERACTIVE) -->
      <div class="lg:col-span-6 space-y-4">
        <!-- Main Large Photo -->
        <div class="relative aspect-[4/5] bg-white border border-border rounded-[4px] overflow-hidden shadow-sm">
          <img
            id="main-product-img"
            src="<?= htmlspecialchars($gallery_images[0] ?? $product['cover_image']) ?>"
            alt="<?= htmlspecialchars($product['name']) ?>"
            class="w-full h-full object-cover transition-all duration-300"
          />

          <!-- Badge Kiri Atas -->
          <div class="absolute top-4 left-4 flex flex-col gap-1.5 items-start">
            <span class="bg-white/95 backdrop-blur-[4px] text-dark font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase px-3 py-1 rounded-[2px] shadow-sm">
              <?= htmlspecialchars($product['category']) ?>
            </span>
            <?php if (!empty($product['badge'])): ?>
              <span class="bg-brand text-white font-jakarta font-semibold text-[10px] tracking-widest-2 uppercase px-2.5 py-0.5 rounded-[2px] shadow-sm">
                <?= htmlspecialchars($product['badge']) ?>
              </span>
            <?php endif; ?>
          </div>

          <?php if ($has_discount): ?>
            <div class="absolute top-4 right-4">
              <span class="bg-dark/95 text-white font-jakarta font-bold text-[11px] tracking-wider px-3 py-1 rounded-[2px] shadow-sm">
                HEMAT <?= $discount_pct ?>%
              </span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Thumbnails Row (Jika ada lebih dari 1 foto) -->
        <?php if (count($gallery_images) > 1): ?>
          <div class="grid grid-cols-4 gap-3">
            <?php foreach ($gallery_images as $idx => $img_url): ?>
              <button
                type="button"
                onclick="changeProductImage('<?= htmlspecialchars($img_url) ?>', this)"
                class="thumb-btn relative aspect-square rounded-[3px] overflow-hidden border-2 <?= $idx === 0 ? 'border-brand' : 'border-border' ?> hover:border-brand/70 transition-all"
              >
                <img src="<?= htmlspecialchars($img_url) ?>" alt="Thumbnail <?= $idx+1 ?>" class="w-full h-full object-cover" />
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Guarantee Value Badges -->
        <div class="grid grid-cols-3 gap-3 pt-4 border-t border-border/70 text-center">
          <div class="p-3 bg-white border border-border rounded-[3px]">
            <span class="block font-jakarta font-bold text-[11px] uppercase tracking-wider text-dark mb-0.5">Garansi 5 Tahun</span>
            <span class="text-[10px] text-muted">Komponen & Mekanisme</span>
          </div>
          <div class="p-3 bg-white border border-border rounded-[3px]">
            <span class="block font-jakarta font-bold text-[11px] uppercase tracking-wider text-dark mb-0.5">Survei Gratis</span>
            <span class="text-[10px] text-muted">Area Jabodetabek</span>
          </div>
          <div class="p-3 bg-white border border-border rounded-[3px]">
            <span class="block font-jakarta font-bold text-[11px] uppercase tracking-wider text-dark mb-0.5">Memory Hemming</span>
            <span class="text-[10px] text-muted">Jahitan Selalu Rapi</span>
          </div>
        </div>
      </div>

      <!-- RIGHT: PRODUCT EDITORIAL & ORDER SPECS -->
      <div class="lg:col-span-6 space-y-6">

        <!-- Header Info -->
        <div>
          <div class="flex items-center gap-3 mb-2.5">
            <span class="font-mono text-[11px] tracking-wider text-muted bg-white border border-border px-2.5 py-0.5 rounded-[2px]">
              KODE REF: <?= htmlspecialchars($product['ref_code'] ?? 'WNT-SERIES') ?>
            </span>
            <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
            <span class="text-[11px] font-jakarta tracking-widest-2 uppercase text-brand font-semibold">
              Koleksi Atelier
            </span>
          </div>

          <h1 class="font-playfair text-3xl md:text-4xl font-bold text-dark leading-tight mb-4">
            <?= htmlspecialchars($product['name']) ?>
          </h1>

          <!-- Price Box -->
          <div class="p-4 bg-white border border-border rounded-[4px] flex items-baseline justify-between">
            <div>
              <span class="text-[11px] uppercase tracking-wider font-jakarta text-muted block mb-1">Estimasi Biaya Material</span>
              <div class="flex items-baseline gap-2">
                <span class="font-playfair font-bold text-3xl md:text-4xl text-brand">
                  Rp <?= number_format($product['price'], 0, ',', '.') ?>
                </span>
                <span class="text-[13px] font-jakarta text-muted">
                  <?= htmlspecialchars($product['price_unit'] ?? '/ meter') ?>
                </span>
              </div>
            </div>
            <?php if ($has_discount): ?>
              <div class="text-right">
                <span class="text-[11px] uppercase tracking-wider text-muted block mb-0.5">Harga Normal</span>
                <span class="text-[14px] font-jakarta text-muted line-through">
                  Rp <?= number_format($product['original_price'], 0, ',', '.') ?>
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h3 class="font-jakarta font-semibold text-[13px] uppercase tracking-widest-2 text-dark mb-2">
            DESKRIPSI MATERIAL & KARAKTERISTIK
          </h3>
          <p class="font-jakarta text-[14px] text-muted leading-relaxed">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
          </p>
        </div>

        <!-- Features Checklist -->
        <?php if (!empty($feature_items)): ?>
          <div class="bg-white border border-border rounded-[4px] p-5">
            <h3 class="font-jakarta font-semibold text-[12px] uppercase tracking-widest-2 text-dark mb-3">
              FITUR & SPESIFIKASI TEKNIS
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <?php foreach ($feature_items as $feat): ?>
                <div class="flex items-start gap-2.5 text-[12px] font-jakarta text-dark">
                  <svg class="w-4 h-4 text-brand shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <span><?= htmlspecialchars(trim($feat)) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Options Variasi Swatch / Stitching jika ada -->
        <?php if (!empty($options)): ?>
          <div class="space-y-4 pt-2">
            <?php foreach ($options as $opt_key => $opt_vals): ?>
              <div>
                <span class="block font-jakarta font-semibold text-[11px] uppercase tracking-wider text-dark mb-2">
                  Pilihan <?= ucfirst(htmlspecialchars($opt_key)) ?>:
                </span>
                <div class="flex flex-wrap gap-2">
                  <?php foreach ((array)$opt_vals as $v): ?>
                    <span class="px-3 py-1.5 bg-white border border-border rounded-[3px] text-[11px] font-jakarta text-dark">
                      <?= htmlspecialchars($v) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- PRIMARY CTA BUTTONS -->
        <div class="pt-4 space-y-3">
          <a
            href="<?= $product_wa_link ?>"
            target="_blank"
            rel="noopener"
            class="w-full flex items-center justify-center gap-3 bg-brand hover:bg-[#5a0d1a] text-white font-jakarta font-semibold text-[13px] tracking-widest-3 uppercase py-4 px-6 rounded-[3px] shadow-md hover:shadow-lg transition-all"
          >
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
              <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.448.74 0.96 1.196.659.587 1.215.769 1.388.856.173.086.274.072.375-.044.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/>
            </svg>
            KONSULTASI & CEK ESTIMASI VIA WHATSAPP
          </a>

          <p class="text-center text-[11px] font-jakarta text-muted">
            * Konsultasi langsung dengan konsultan arsitektural Wintom. Respon cepat 7 hari seminggu.
          </p>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ===== PROTOKOL 4 LANGKAH PEMESANAN WINTOM ===== -->
<section class="py-16 md:py-20 bg-cream-dark/50 border-y border-border">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="font-jakarta font-semibold text-[11px] tracking-widest-4 uppercase text-brand block mb-2">
        STANDAR PELAYANAN ATELIER
      </span>
      <h2 class="font-playfair text-2xl md:text-3xl font-bold text-dark mb-3">
        4 Tahap Pengerjaan Presisi Wintom
      </h2>
      <p class="font-jakarta text-[13px] md:text-[14px] text-muted">
        Dari konsultasi pemilihan sampel fisik hingga pemasangan rapi bergaransi tanpa repot.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-[4px] border border-border relative">
        <span class="font-playfair text-4xl font-bold text-brand/20 absolute top-4 right-4">01</span>
        <h4 class="font-playfair text-[17px] font-bold text-dark mb-2">Konsultasi Desain</h4>
        <p class="font-jakarta text-[12px] text-muted leading-relaxed">
          Diskusikan konsep interior, tingkat privasi yang diinginkan, serta perkiraan anggaran jendela Anda.
        </p>
      </div>

      <div class="bg-white p-6 rounded-[4px] border border-border relative">
        <span class="font-playfair text-4xl font-bold text-brand/20 absolute top-4 right-4">02</span>
        <h4 class="font-playfair text-[17px] font-bold text-dark mb-2">Survei & Swatch Fisik</h4>
        <p class="font-jakarta text-[12px] text-muted leading-relaxed">
          Tim spesialis mengukur jendela menggunakan laser presisi dan membawa katalog kain asli ke lokasi Anda.
        </p>
      </div>

      <div class="bg-white p-6 rounded-[4px] border border-border relative">
        <span class="font-playfair text-4xl font-bold text-brand/20 absolute top-4 right-4">03</span>
        <h4 class="font-playfair text-[17px] font-bold text-dark mb-2">Penjahitan Atelier</h4>
        <p class="font-jakarta text-[12px] text-muted leading-relaxed">
          Kain dijahit khusus dengan standar hemming lipatan bergelombang simetris dan quality check ketat.
        </p>
      </div>

      <div class="bg-white p-6 rounded-[4px] border border-border relative">
        <span class="font-playfair text-4xl font-bold text-brand/20 absolute top-4 right-4">04</span>
        <h4 class="font-playfair text-[17px] font-bold text-dark mb-2">Instalasi & Garansi</h4>
        <p class="font-jakarta text-[12px] text-muted leading-relaxed">
          Teknisi berpengalaman memasang rel dan tirai tanpa debu, disertai garansi mekanisme hingga 5 tahun.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ===== RELATED PRODUCTS ===== -->
<?php if (!empty($related_products)): ?>
<section class="py-16 md:py-20 bg-cream">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="flex items-end justify-between mb-10 pb-4 border-b border-border">
      <div>
        <span class="font-jakarta font-semibold text-[11px] tracking-widest-4 uppercase text-brand block mb-1">
          REKOMENDASI ALTERNATIF
        </span>
        <h2 class="font-playfair text-2xl md:text-3xl font-bold text-dark">
          Koleksi Pilihan Lainnya
        </h2>
      </div>
      <a href="<?= BASE_URL ?>/products.php" class="font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase text-brand hover:underline">
        SEMUA KOLEKSI &rarr;
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <?php foreach ($related_products as $rel):
        $rel_url = BASE_URL . '/product-detail.php?slug=' . urlencode($rel['slug']);
      ?>
        <div class="group bg-white border border-border rounded-[4px] overflow-hidden flex flex-col hover:border-brand/40 hover:shadow-lg transition-all duration-300">
          <a href="<?= $rel_url ?>" class="relative aspect-[3/4] overflow-hidden bg-cream block">
            <img
              src="<?= htmlspecialchars($rel['cover_image']) ?>"
              alt="<?= htmlspecialchars($rel['name']) ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
              loading="lazy"
            />
            <div class="absolute top-3 left-3">
              <span class="bg-white/95 backdrop-blur-[4px] text-dark font-jakarta font-semibold text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-[2px] shadow-sm">
                <?= htmlspecialchars($rel['category']) ?>
              </span>
            </div>
          </a>
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
              <h4 class="font-playfair text-[17px] font-bold text-dark group-hover:text-brand transition-colors mb-1">
                <a href="<?= $rel_url ?>"><?= htmlspecialchars($rel['name']) ?></a>
              </h4>
              <p class="font-jakarta text-[12px] text-muted line-clamp-2 mb-3">
                <?= htmlspecialchars($rel['description']) ?>
              </p>
            </div>
            <div class="flex items-baseline justify-between pt-3 border-t border-border/70">
              <span class="font-playfair font-bold text-lg text-brand">
                Rp <?= number_format($rel['price'], 0, ',', '.') ?>
              </span>
              <a href="<?= $rel_url ?>" class="text-[11px] font-jakarta font-semibold uppercase tracking-wider text-dark hover:text-brand">
                Detail &rarr;
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
  function changeProductImage(newSrc, btnElement) {
    const mainImg = document.getElementById('main-product-img');
    if (!mainImg) return;
    mainImg.style.opacity = '0.3';
    setTimeout(() => {
      mainImg.src = newSrc;
      mainImg.style.opacity = '1';
    }, 150);

    // Update active thumb border
    document.querySelectorAll('.thumb-btn').forEach(btn => {
      btn.classList.remove('border-brand');
      btn.classList.add('border-border');
    });
    btnElement.classList.remove('border-border');
    btnElement.classList.add('border-brand');
  }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
